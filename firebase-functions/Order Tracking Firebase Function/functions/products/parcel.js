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
    document: "parcel_orders/{orderID}"
}, async (event) => {
    const firestore = getDb();

    if (!event.data || !event.data.after || !event.data.after.exists) {
        return null;
    }

    const orderData = event.data.after.data();
    const beforeData = event.data.before && event.data.before.exists ? event.data.before.data() : null;
    const orderId = event.params.orderID;
    const documentRef = event.data.after.ref;

    if (!orderData) {
        console.log("No parcel order data for ID:", orderId);
        return null;
    }

    if (orderData.status === "Order Cancelled" || orderData.status === "Order Rejected") {
        console.log(`Parcel order #${orderId} was cancelled or rejected.`);
        return null;
    }

    // 1. Dispatch Logic: Trigger when new parcel is placed or previous driver rejected
    if (orderData.status === "Order Placed" || orderData.status === "Driver Rejected") {
        console.log("Finding a parcel driver for parcel order #" + orderId + " ---");

        const rejectedByDrivers = Array.isArray(orderData.rejectedByDrivers) ? orderData.rejectedByDrivers : [];
        const driverNearByData = await getDriverNearByData(firestore);

        let minimumDepositToRideAccept = 0;
        let orderAcceptRejectDuration = 0;
        let kDistanceRadiusForDispatch = 50;
        let singleOrderReceive = false;

        // Origin coordinates (Sender location)
        let senderLat = null;
        let senderLng = null;

        if (orderData.senderLatLong?.latitude && orderData.senderLatLong?.longitude) {
            senderLat = parseFloat(orderData.senderLatLong.latitude);
            senderLng = parseFloat(orderData.senderLatLong.longitude);
        } else if (orderData.sender?.location?.latitude && orderData.sender?.location?.longitude) {
            senderLat = parseFloat(orderData.sender.location.latitude);
            senderLng = parseFloat(orderData.sender.location.longitude);
        }

        if (driverNearByData) {
            minimumDepositToRideAccept = parseFloat(driverNearByData.minimumDepositToRideAccept || 0);
            orderAcceptRejectDuration = parseInt(driverNearByData.driverOrderAcceptRejectDuration || 0, 10);
            kDistanceRadiusForDispatch = parseFloat(driverNearByData.parcelRadius || driverNearByData.driverRadios || 50);
            if (driverNearByData.distanceType === "miles") {
                kDistanceRadiusForDispatch = Math.round(kDistanceRadiusForDispatch * 1.60934);
            }
            singleOrderReceive = Boolean(driverNearByData.singleOrderReceive);
        }

        if (senderLat === null || senderLng === null || isNaN(senderLat) || isNaN(senderLng)) {
            console.error(`Parcel order #${orderId} sender coordinates missing:`, orderData.senderLatLong);
            return null;
        }

        let zone_id = orderData.senderZoneId || null;
        if (!zone_id) {
            zone_id = await getUserZoneId(firestore, senderLng, senderLat);
        }

        // Fetch active drivers
        let snapshot;
        try {
            snapshot = await firestore.collection("users")
                .where("role", "==", "driver")
                .where("isActive", "==", true)
                .get();
        } catch (queryErr) {
            console.error("Error querying drivers for parcel:", queryErr);
            return null;
        }

        console.log(`Evaluating ${snapshot.docs.length} drivers for parcel order #${orderId}`);

        let matchedDriver = null;
        let matchedDriverId = null;

        for (const doc of snapshot.docs) {
            const driver = doc.data();
            const driverId = doc.id;

            if (!driver.fcmToken) continue;

            // Service Type Check: Must support parcel_delivery
            const driverServiceTypes = Array.isArray(driver.serviceTypes)
                ? driver.serviceTypes
                : (driver.serviceType ? [driver.serviceType] : []);

            const isParcelEligible = driverServiceTypes.includes("parcel_delivery") ||
                                     driverServiceTypes.includes("parcel-service") ||
                                     driverServiceTypes.length === 0;
            if (!isParcelEligible) continue;

            // Wallet check
            const driverWallet = parseFloat(driver.wallet_amount || 0);
            if (driverWallet < minimumDepositToRideAccept) continue;

            // Zone Check
            if (driver.zoneId && zone_id !== null && driver.zoneId !== zone_id) {
                continue;
            }

            // Proximity check
            if (driver.location && !rejectedByDrivers.includes(driverId)) {
                const driverLat = parseFloat(driver.location.latitude || driver.location.lat);
                const driverLng = parseFloat(driver.location.longitude || driver.location.lng);

                if (isNaN(driverLat) || isNaN(driverLng)) continue;

                const distance = distanceRadius(driverLat, driverLng, senderLat, senderLng);

                console.log(`Parcel Driver ${driver.email || driverId}: Distance=${distance.toFixed(2)} km (Max=${kDistanceRadiusForDispatch} km)`);

                if (distance <= kDistanceRadiusForDispatch) {
                    if (singleOrderReceive === true) {
                        const hasPendingOrder = Array.isArray(driver.orderRequestData) && driver.orderRequestData.length > 0;
                        const hasAcceptedOrder = Array.isArray(driver.inProgressOrderID) && driver.inProgressOrderID.length > 0;
                        if (hasPendingOrder || hasAcceptedOrder) continue;
                    }

                    matchedDriver = driver;
                    matchedDriverId = driverId;
                    break;
                }
            }
        }

        if (matchedDriver && matchedDriverId) {
            console.log(`Parcel Match Found: Driver ${matchedDriver.email || matchedDriverId} assigned to parcel #${orderId}`);

            const timeMinutes = Math.max(1, Math.floor(orderAcceptRejectDuration / 60));
            const notificationTitle = "New parcel delivery request";
            const notificationBody = `You have a new parcel order. Please accept within ${timeMinutes} min(s).`;

            await sendDriverNotification(matchedDriver.fcmToken, notificationTitle, notificationBody, {
                orderId: orderId,
                id: orderId,
                type: "parcel",
                status: "Driver Pending"
            });

            // Update parcel order
            await documentRef.set({
                status: "Driver Pending",
                driverId: matchedDriverId
            }, { merge: true });

            // Update driver request list
            let currentRequests = Array.isArray(matchedDriver.orderRequestData) ? [...matchedDriver.orderRequestData] : [];
            if (!currentRequests.includes(orderId)) {
                currentRequests.push(orderId);
            }
            await firestore.collection("users").doc(matchedDriverId).update({ orderRequestData: currentRequests });
        } else {
            console.log("No parcel driver found within radius for parcel order #" + orderId);
        }
    }

    return null;
});
