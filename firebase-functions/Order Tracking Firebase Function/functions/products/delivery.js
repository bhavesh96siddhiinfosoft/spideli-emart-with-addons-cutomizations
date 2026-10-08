const { onDocumentWritten } = require("firebase-functions/v2/firestore");
const { getFirestore } = require("firebase-admin/firestore");
const admin = require("firebase-admin");
const {
    distanceRadius,
    getDriverNearByData,
    getUserZoneId,
    sendDriverNotification
} = require("./helper");

// Initialize Admin SDK once
if (admin.apps.length === 0) {
    admin.initializeApp();
}

const getDb = () => getFirestore();

exports.dispatch = onDocumentWritten({
    document: "vendor_orders/{orderID}"
}, async (event) => {
    const firestore = getDb();

    if (!event.data || !event.data.after || !event.data.after.exists) {
        return null;
    }

    const orderData = event.data.after.data();
    const beforeData = event.data.before && event.data.before.exists ? event.data.before.data() : null;
    const orderId = event.params.orderID;
    const documentRef = event.data.after.ref;

    // 1. Guard Clauses & Skip Logic
    if (!orderData) {
        console.log("No order data found for ID:", orderId);
        return null;
    }

    if (beforeData && orderData) {
        const keysChanged = Object.keys(orderData).filter(
            key => JSON.stringify(orderData[key]) !== JSON.stringify(beforeData[key])
        );
        if (keysChanged.length === 1 && keysChanged.includes("orderAutoCancelAt")) {
            console.log("orderAutoCancelAt update detected, skipping dispatch logic.");
            return null;
        }
    }

    if (orderData.status === "Order Cancelled" || orderData.status === "Order Rejected") {
        console.log(`Order #${orderId} was cancelled or rejected.`);
        return null;
    }

    if (orderData.status === "Order Placed") {
        console.log(`Order #${orderId} was sent to vendor for approval.`);
        return null;
    }

    if (orderData.takeAway === true) {
        console.log(`Order #${orderId} is takeAway, no driver dispatch needed.`);
        return null;
    }

    // 2. Dispatch Logic (When vendor accepts the order or previous driver rejected)
    if (orderData.status === "Order Accepted" || orderData.status === "Driver Rejected") {
        console.log("Finding a driver for order #" + orderId + " ---");

        const rejectedByDrivers = Array.isArray(orderData.rejectedByDrivers) ? orderData.rejectedByDrivers : [];
        const driverNearByData = await getDriverNearByData(firestore);

        let minimumDepositToRideAccept = 0;
        let orderAcceptRejectDuration = 0;
        let orderAutoCancelDuration = 0;
        let kDistanceRadiusForDispatch = 50;
        let singleOrderReceive = false;

        let zone_id = null;
        const vendorLoc = orderData.vendor || {};
        const vendorLat = parseFloat(vendorLoc.latitude || vendorLoc.lat);
        const vendorLng = parseFloat(vendorLoc.longitude || vendorLoc.lng);

        if (orderData.address?.location?.longitude && orderData.address?.location?.latitude) {
            zone_id = await getUserZoneId(
                firestore,
                parseFloat(orderData.address.location.longitude),
                parseFloat(orderData.address.location.latitude)
            );
            console.log("Zone id by address:", zone_id);
        }

        if (driverNearByData) {
            minimumDepositToRideAccept = parseFloat(driverNearByData.minimumDepositToRideAccept || 0);
            orderAcceptRejectDuration = parseInt(driverNearByData.driverOrderAcceptRejectDuration || 0, 10);
            orderAutoCancelDuration = parseInt(driverNearByData.orderAutoCancelDuration || 0, 10);
            kDistanceRadiusForDispatch = parseFloat(driverNearByData.driverRadios || 50);
            if (driverNearByData.distanceType === "miles") {
                kDistanceRadiusForDispatch = Math.round(kDistanceRadiusForDispatch * 1.60934);
            }
            singleOrderReceive = Boolean(driverNearByData.singleOrderReceive);
        }

        console.log(`Config: minDeposit=${minimumDepositToRideAccept}, acceptDuration=${orderAcceptRejectDuration}, radius=${kDistanceRadiusForDispatch}km`);

        if (isNaN(vendorLat) || isNaN(vendorLng)) {
            console.error(`Order #${orderId} vendor location is missing or invalid coordinates: lat=${vendorLoc.latitude}, lng=${vendorLoc.longitude}`);
            return null;
        }

        // Fetch active drivers (safe query without composite index error)
        let snapshot;
        try {
            snapshot = await firestore.collection("users")
                .where("role", "==", "driver")
                .where("isActive", "==", true)
                .get();
        } catch (queryErr) {
            console.error("Error querying drivers from Firestore:", queryErr);
            return null;
        }

        console.log(`Found ${snapshot.docs.length} active drivers to evaluate.`);

        let matchedDriver = null;
        let matchedDriverId = null;

        for (const doc of snapshot.docs) {
            const driver = doc.data();
            const driverId = doc.id;

            // Must have valid FCM token and not tied to another specific vendor
            if (!driver.fcmToken || driver.vendorID) continue;

            // Service Type Check: Delivery or Ecommerce
            const driverServiceTypes = Array.isArray(driver.serviceTypes)
                ? driver.serviceTypes
                : (driver.serviceType ? [driver.serviceType] : []);

            const isDeliveryEligible = driverServiceTypes.includes("delivery-service") ||
                                       driverServiceTypes.includes("ecommerce-service") ||
                                       driverServiceTypes.length === 0; // fallback if unassigned
            if (!isDeliveryEligible) {
                continue;
            }

            // Minimum Wallet Check
            const driverWallet = parseFloat(driver.wallet_amount || 0);
            if (driverWallet < minimumDepositToRideAccept) {
                continue;
            }

            // Section Check (only restrict if driver has an explicit sectionIds list)
            if (Array.isArray(driver.sectionIds) && driver.sectionIds.length > 0) {
                const orderSection = orderData.section_id || orderData.sectionId;
                if (orderSection && !driver.sectionIds.includes(orderSection)) {
                    console.log(`Driver ${driverId} skipped: section mismatch`);
                    continue;
                }
            }

            // Zone Check
            if (driver.zoneId && zone_id !== null && driver.zoneId !== zone_id) {
                continue;
            }

            // Check Rejections and Distance Proximity
            if (driver.location && !rejectedByDrivers.includes(driverId)) {
                const driverLat = parseFloat(driver.location.latitude || driver.location.lat);
                const driverLng = parseFloat(driver.location.longitude || driver.location.lng);

                if (isNaN(driverLat) || isNaN(driverLng)) continue;

                const distance = distanceRadius(driverLat, driverLng, vendorLat, vendorLng);

                console.log(`Checking Driver ${driver.email || driverId}: Distance=${distance.toFixed(2)} km (Max=${kDistanceRadiusForDispatch} km)`);

                if (distance <= kDistanceRadiusForDispatch) {
                    if (singleOrderReceive === true) {
                        const hasPendingOrder = Array.isArray(driver.orderRequestData) && driver.orderRequestData.length > 0;
                        const hasAcceptedOrder = Array.isArray(driver.inProgressOrderID) && driver.inProgressOrderID.length > 0;
                        if (hasPendingOrder || hasAcceptedOrder) {
                            console.log(`Driver ${driver.email || driverId} is currently busy with another order.`);
                            continue;
                        }
                    }

                    matchedDriver = driver;
                    matchedDriverId = driverId;
                    break;
                }
            }
        }

        if (matchedDriver && matchedDriverId) {
            console.log(`Match Found: Driver ${matchedDriver.email || matchedDriverId} assigned to order #${orderId}`);

            // 1. Notify Driver via FCM (Push notification + Data payload)
            const timeMinutes = Math.max(1, Math.floor(orderAcceptRejectDuration / 60));
            const notificationTitle = "New delivery order received";
            const notificationBody = `You have a new delivery order. Please accept within ${timeMinutes} min(s).`;

            await sendDriverNotification(matchedDriver.fcmToken, notificationTitle, notificationBody, {
                orderId: orderId,
                id: orderId,
                type: "order",
                status: "Driver Pending"
            });

            // 2. Update Order Status
            await documentRef.set({
                status: "Driver Pending",
                driverId: matchedDriverId
            }, { merge: true });

            // 3. Assign order to driver's orderRequestData
            let currentRequests = Array.isArray(matchedDriver.orderRequestData) ? [...matchedDriver.orderRequestData] : [];
            if (!currentRequests.includes(orderId)) {
                currentRequests.push(orderId);
            }
            await firestore.collection("users").doc(matchedDriverId).update({ orderRequestData: currentRequests });
        } else {
            const futureTime = new Date(Date.now() + (orderAutoCancelDuration || 10) * 60 * 1000);
            await firestore.collection("vendor_orders").doc(orderId).update({
                orderAutoCancelAt: admin.firestore.Timestamp.fromDate(futureTime)
            });
            console.log("No available driver found within radius for order #" + orderId);
        }
    }

    if (orderData.status === "Driver Accepted") {
        await documentRef.set({ status: "Order Shipped" }, { merge: true });
        console.log(`Order #${orderId} changed to Order Shipped on Driver Accepted`);
    }

    return null;
});