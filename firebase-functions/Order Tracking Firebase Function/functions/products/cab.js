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
    document: "rides/{orderID}"
}, async (event) => {
    const firestore = getDb();

    if (!event.data || !event.data.after || !event.data.after.exists) {
        return null;
    }

    const orderData = event.data.after.data();
    const orderId = event.params.orderID;
    const documentRef = event.data.after.ref;

    if (!orderData) {
        console.log("No cab ride data for ID:", orderId);
        return null;
    }

    if (orderData.status === "Order Cancelled" || orderData.status === "Order Rejected") {
        console.log(`Cab ride #${orderId} was cancelled or rejected.`);
        return null;
    }

    // 1. Dispatch Logic: When a ride is placed or driver rejected
    if (orderData.status === "Order Placed" || orderData.status === "Driver Rejected") {
        console.log("Finding a cab driver for ride #" + orderId + " ---");

        const rejectedByDrivers = Array.isArray(orderData.rejectedByDrivers) ? orderData.rejectedByDrivers : [];
        const driverNearByData = await getDriverNearByData(firestore);

        let minimumDepositToRideAccept = 0;
        let orderAcceptRejectDuration = 0;
        let kDistanceRadiusForDispatch = 50;
        let singleOrderReceive = false;

        // Pickup coordinates
        let pickupLat = null;
        let pickupLng = null;

        if (orderData.sourceLocation?.latitude && orderData.sourceLocation?.longitude) {
            pickupLat = parseFloat(orderData.sourceLocation.latitude);
            pickupLng = parseFloat(orderData.sourceLocation.longitude);
        } else if (orderData.sourceLocationLat && orderData.sourceLocationLong) {
            pickupLat = parseFloat(orderData.sourceLocationLat);
            pickupLng = parseFloat(orderData.sourceLocationLong);
        } else if (orderData.author?.location?.latitude && orderData.author?.location?.longitude) {
            pickupLat = parseFloat(orderData.author.location.latitude);
            pickupLng = parseFloat(orderData.author.location.longitude);
        }

        if (driverNearByData) {
            minimumDepositToRideAccept = parseFloat(driverNearByData.minimumDepositToRideAccept || 0);
            orderAcceptRejectDuration = parseInt(driverNearByData.driverOrderAcceptRejectDuration || 0, 10);
            kDistanceRadiusForDispatch = parseFloat(driverNearByData.cabRadius || driverNearByData.driverRadios || 50);
            if (driverNearByData.distanceType === "miles") {
                kDistanceRadiusForDispatch = Math.round(kDistanceRadiusForDispatch * 1.60934);
            }
            singleOrderReceive = Boolean(driverNearByData.singleOrderReceive);
        }

        if (pickupLat === null || pickupLng === null || isNaN(pickupLat) || isNaN(pickupLng)) {
            console.error(`Ride #${orderId} pickup coordinates missing:`, orderData.sourceLocation);
            return null;
        }

        let zone_id = orderData.zoneId || null;
        if (!zone_id) {
            zone_id = await getUserZoneId(firestore, pickupLng, pickupLat);
        }

        // Fetch active drivers
        let snapshot;
        try {
            snapshot = await firestore.collection("users")
                .where("role", "==", "driver")
                .where("isActive", "==", true)
                .get();
        } catch (queryErr) {
            console.error("Error querying drivers for cab:", queryErr);
            return null;
        }

        console.log(`Evaluating ${snapshot.docs.length} drivers for cab ride #${orderId}`);

        let matchedDriver = null;
        let matchedDriverId = null;

        for (const doc of snapshot.docs) {
            const driver = doc.data();
            const driverId = doc.id;

            if (!driver.fcmToken) continue;

            // Service Type Check: Must support cab-service
            const driverServiceTypes = Array.isArray(driver.serviceTypes)
                ? driver.serviceTypes
                : (driver.serviceType ? [driver.serviceType] : []);

            const isCabEligible = driverServiceTypes.includes("cab-service") || driverServiceTypes.length === 0;
            if (!isCabEligible) continue;

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

                const distance = distanceRadius(driverLat, driverLng, pickupLat, pickupLng);

                console.log(`Cab Driver ${driver.email || driverId}: Distance=${distance.toFixed(2)} km (Max=${kDistanceRadiusForDispatch} km)`);

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
            console.log(`Cab Match Found: Driver ${matchedDriver.email || matchedDriverId} assigned to ride #${orderId}`);

            const timeMinutes = Math.max(1, Math.floor(orderAcceptRejectDuration / 60));
            const notificationTitle = "New cab ride request";
            const notificationBody = `You have a new ride booking request. Please accept within ${timeMinutes} min(s).`;

            await sendDriverNotification(matchedDriver.fcmToken, notificationTitle, notificationBody, {
                orderId: orderId,
                id: orderId,
                type: "cab",
                status: "Driver Pending"
            });

            // Update ride
            await documentRef.set({
                status: "Driver Pending",
                driverId: matchedDriverId,
                driverID: matchedDriverId
            }, { merge: true });

            // Update driver request list
            let currentRequests = Array.isArray(matchedDriver.orderRequestData) ? [...matchedDriver.orderRequestData] : [];
            if (!currentRequests.includes(orderId)) {
                currentRequests.push(orderId);
            }
            await firestore.collection("users").doc(matchedDriverId).update({ orderRequestData: currentRequests });
        } else {
            console.log("No cab driver found within radius for ride #" + orderId);
        }
    }

    return null;
});
