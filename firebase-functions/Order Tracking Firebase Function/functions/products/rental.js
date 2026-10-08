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
    document: "rental_orders/{orderID}"
}, async (event) => {
    const firestore = getDb();

    if (!event.data || !event.data.after || !event.data.after.exists) {
        return null;
    }

    const orderData = event.data.after.data();
    const orderId = event.params.orderID;
    const documentRef = event.data.after.ref;

    if (!orderData) {
        console.log("No rental order data for ID:", orderId);
        return null;
    }

    if (orderData.status === "Order Cancelled" || orderData.status === "Order Rejected") {
        console.log(`Rental order #${orderId} was cancelled or rejected.`);
        return null;
    }

    // 1. Dispatch Logic: Trigger when new rental order is placed or previous driver rejected
    if (orderData.status === "Order Placed" || orderData.status === "Driver Rejected") {
        console.log("Finding a rental driver for rental order #" + orderId + " ---");

        const rejectedByDrivers = Array.isArray(orderData.rejectedByDrivers) ? orderData.rejectedByDrivers : [];
        const driverNearByData = await getDriverNearByData(firestore);

        let minimumDepositToRideAccept = 0;
        let orderAcceptRejectDuration = 0;
        let kDistanceRadiusForDispatch = 50;
        let singleOrderReceive = false;

        // Pickup coordinates
        let pickupLat = null;
        let pickupLng = null;

        if (orderData.sourceLocation?.latitude !== undefined && orderData.sourceLocation?.longitude !== undefined) {
            pickupLat = parseFloat(orderData.sourceLocation.latitude);
            pickupLng = parseFloat(orderData.sourceLocation.longitude);
        } else if (orderData.sourceLocation?.lat !== undefined && orderData.sourceLocation?.lng !== undefined) {
            pickupLat = parseFloat(orderData.sourceLocation.lat);
            pickupLng = parseFloat(orderData.sourceLocation.lng);
        } else if (orderData.pickUpLatLong?.latitude !== undefined && orderData.pickUpLatLong?.longitude !== undefined) {
            pickupLat = parseFloat(orderData.pickUpLatLong.latitude);
            pickupLng = parseFloat(orderData.pickUpLatLong.longitude);
        } else if (orderData.sourcePoint?.geopoint?.latitude !== undefined && orderData.sourcePoint?.geopoint?.longitude !== undefined) {
            pickupLat = parseFloat(orderData.sourcePoint.geopoint.latitude);
            pickupLng = parseFloat(orderData.sourcePoint.geopoint.longitude);
        } else if (orderData.author?.location?.latitude !== undefined && orderData.author?.location?.longitude !== undefined) {
            pickupLat = parseFloat(orderData.author.location.latitude);
            pickupLng = parseFloat(orderData.author.location.longitude);
        }

        if (driverNearByData) {
            minimumDepositToRideAccept = parseFloat(driverNearByData.minimumDepositToRideAccept || 0);
            orderAcceptRejectDuration = parseInt(driverNearByData.driverOrderAcceptRejectDuration || 0, 10);
            kDistanceRadiusForDispatch = parseFloat(driverNearByData.rentalRadius || driverNearByData.driverRadios || 50);
            if (driverNearByData.distanceType === "miles") {
                kDistanceRadiusForDispatch = Math.round(kDistanceRadiusForDispatch * 1.60934);
            }
            singleOrderReceive = Boolean(driverNearByData.singleOrderReceive);
        }

        if (pickupLat === null || pickupLng === null || isNaN(pickupLat) || isNaN(pickupLng)) {
            console.error(`Rental order #${orderId} pickup coordinates missing:`, orderData.sourceLocation);
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
            console.error("Error querying drivers for rental:", queryErr);
            return null;
        }

        console.log(`Evaluating ${snapshot.docs.length} drivers for rental order #${orderId}`);

        let matchedDriver = null;
        let matchedDriverId = null;

        for (const doc of snapshot.docs) {
            const driver = doc.data();
            const driverId = doc.id;

            if (!driver.fcmToken) continue;

            // Service Type Check: Must support rental-service
            const driverServiceTypes = Array.isArray(driver.serviceTypes)
                ? driver.serviceTypes
                : (driver.serviceType ? [driver.serviceType] : []);

            const isRentalEligible = driverServiceTypes.includes("rental-service") ||
                                     driverServiceTypes.includes("rental_service") ||
                                     driverServiceTypes.length === 0;
            if (!isRentalEligible) continue;

            // Optional Section Check if both specify sectionId
            if (orderData.sectionId && driver.sectionId && orderData.sectionId !== driver.sectionId) {
                continue;
            }

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

                console.log(`Rental Driver ${driver.email || driverId}: Distance=${distance.toFixed(2)} km (Max=${kDistanceRadiusForDispatch} km)`);

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
            console.log(`Rental Match Found: Driver ${matchedDriver.email || matchedDriverId} assigned to rental #${orderId}`);

            const timeMinutes = Math.max(1, Math.floor(orderAcceptRejectDuration / 60));
            const notificationTitle = "New rental booking request";
            const notificationBody = `You have a new rental booking request. Please accept within ${timeMinutes} min(s).`;

            await sendDriverNotification(matchedDriver.fcmToken, notificationTitle, notificationBody, {
                orderId: orderId,
                id: orderId,
                type: "rental",
                status: "Driver Pending"
            });

            // Update rental order
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
            console.log("No rental driver found within radius for rental order #" + orderId);
        }
    }

    return null;
});
