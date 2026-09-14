const admin = require("firebase-admin");
const serviceAccount = require('./credentials.json');
const projectId = serviceAccount.project_id;

// ── Environment Setup ──
const ENV = 'default';

const DB_CONFIG = {
    default: {
        databaseId: '(default)',
        label: 'Default'
    }
};

const currentConfig = DB_CONFIG[ENV] || DB_CONFIG['default'];

// ── Firebase Init ──
const app = admin.initializeApp({
    credential: admin.credential.cert(serviceAccount),
    databaseURL: `https://${projectId}-default-rtdb.firebaseio.com`
});

// ── Firestore with selected DB ──
const firestore = admin.firestore(app);
firestore.settings({ databaseId: currentConfig.databaseId });

console.log(`Starting cabScheduleRide and Connected to Firestore: ${currentConfig.label} DB`);

// ════════════════════════════════════════════
// MAIN FUNCTION
// ════════════════════════════════════════════
async function cabScheduleRide() {
    try {
        
        const driverNearByData = await getDriverNearByData();
        let minimumDepositToRideAccept = parseInt(driverNearByData?.minimumDepositToRideAccept || 0);
        let orderAcceptRejectDuration = parseInt(driverNearByData?.driverOrderAcceptRejectDuration || 0);
        let kDistanceRadiusForDispatchInMiles = parseInt(driverNearByData?.driverRadios || 50);

        const ridesSnapshot = await firestore
            .collection('rides')
            .where('scheduleDateTime', '<=', admin.firestore.Timestamp.now())
            .get();

        if (ridesSnapshot.empty) {
            console.log("No scheduled rides found.");
            return;
        }

        for (const rideDoc of ridesSnapshot.docs) {
            const orderData = rideDoc.data();
            if (!orderData) continue;

            if (orderData.status === "Order Cancelled") {
                console.log(`Order #${orderData.id} is cancelled. Skipping.`);
                continue;
            }

            if (!["Order Placed", "Order Accepted", "Driver Rejected"].includes(orderData.status)) continue;

            console.log(`Finding driver for Order #${orderData.id}`);

            const rejectedByDrivers = orderData.rejectedByDrivers || [];

            const driversSnapshot = await firestore
                .collection("users")
                .where('role', '==', "driver")
                .where('serviceTypes', 'array-contains', 'cab-service')
                .where('isActive', '==', true)
                .get();

            let found = false;

            for (const driverDoc of driversSnapshot.docs) {
                if (found) break;

                const driver = driverDoc.data();

                // ── Section Check ──
                if (!driver.sectionIds?.includes(orderData.sectionId)) {
                    console.log(`Driver ${driver.id} skipped (section mismatch)`);
                    continue;
                }

                // ── Vehicle Check ──
                const sectionVehicle = (driver.vehicleDetails || {})[orderData.sectionId];
                if (!sectionVehicle || sectionVehicle.vehicleId !== orderData.vehicleId) {
                    console.log(`Driver ${driver.id} skipped (vehicle mismatch)`);
                    continue;
                }

                // ── Ride Type Check ──
                const driverRideType = sectionVehicle.rideType || driver.rideType || '';
                if (!(driverRideType === "both" || driverRideType === orderData.rideType)) {
                    console.log(`Driver ${driver.id} skipped (ride type mismatch)`);
                    continue;
                }

                // ── Rejected Driver List Check ──
                if (rejectedByDrivers.includes(driver.id)) {
                    console.log(`Driver ${driver.id} skipped (previously rejected)`);
                    continue;
                }

                // ── Wallet Check ──
                let walletToCompare = parseFloat(driver.wallet_amount || 0);
                if (driver.ownerId) {
                    const ownerDoc = await firestore.collection("users").doc(driver.ownerId).get();
                    if (ownerDoc.exists) {
                        const ownerWallet = parseFloat(ownerDoc.data()?.wallet_amount || 0);
                        walletToCompare = Math.max(walletToCompare, ownerWallet);
                        console.log(`Driver ${driver.id} checked against owner wallet.`);
                    }
                }
                if (walletToCompare < minimumDepositToRideAccept) {
                    console.log(`Driver ${driver.id} skipped (wallet too low: ${walletToCompare})`);
                    continue;
                }

                // ── Availability Check ──
                if (!driver.location) continue;
                if (driver.ordercabRequestData) continue;
                if (driver.inProgressOrderID && driver.inProgressOrderID.length > 0) continue;
                if (driver.orderRequestData && driver.orderRequestData.length > 0) continue;

                // ── Zone Check ──
                if (driver.zoneId) {
                    const zoneDoc = await firestore.collection('zone').doc(driver.zoneId).get();
                    if (zoneDoc.exists) {
                        const zoneData = zoneDoc.data();
                        const vertices_x = zoneData.area.map(p => p.longitude);
                        const vertices_y = zoneData.area.map(p => p.latitude);

                        const isDriverInside = is_in_polygon(vertices_x, vertices_y, driver.location.longitude, driver.location.latitude);
                        const isOrderInside = is_in_polygon(vertices_x, vertices_y, orderData.sourceLocation.longitude, orderData.sourceLocation.latitude);

                        if (!isDriverInside || !isOrderInside) {
                            console.log(`Driver ${driver.id} skipped (outside zone)`);
                            continue;
                        }
                    }
                }

                const distance = distanceRadiusride(
                    driver.location.latitude, driver.location.longitude,
                    orderData.sourceLocation.latitude, orderData.sourceLocation.longitude
                );

                // ── Distance Check ──
                if (distance >= kDistanceRadiusForDispatchInMiles) {
                    console.log(`Driver ${driver.id} skipped (too far: ${distance.toFixed(2)} miles)`);
                    continue;
                }

                found = true;

                // ── Send FCM Notification ──
                if (driver.fcmToken) {
                    const time = Math.floor(orderAcceptRejectDuration / 60) + ":" + (orderAcceptRejectDuration % 60).toString().padStart(2, '0');
                    const message = {
                        notification: {
                            title: 'New ride request received',
                            body: `Please accept in ${time} mins`
                        },
                        token: driver.fcmToken
                    };
                    await admin.messaging().send(message).then(console.log).catch(console.log);
                }

                await firestore.collection('rides').doc(orderData.id).update({ status: "Driver Pending" });
                await firestore.collection('users').doc(driver.id).update({ ordercabRequestData: orderData });

                console.log("Order sent to driver #" + driver.id + " for order #" + orderData.id + " with distance " + distance);

                 // Re-check after time limit
                if (orderAcceptRejectDuration > 0) {
                    setTimeout(async () => {
                        const rideRef = await firestore.collection('rides').doc(orderData.id).get();
                        const rideDataCheck = rideRef.data();
                        if (rideDataCheck.status === "Driver Pending") {
                            await firestore.collection('users').doc(driver.id).update({ ordercabRequestData: null });
                            rejectedByDrivers.push(driver.id);
                            await firestore.collection('rides').doc(orderData.id).update({
                                status: 'Order Accepted',
                                rejectedByDrivers: rejectedByDrivers
                            });
                            console.log(`Driver ${driver.id} did not accept order #${orderData.id}, searching next driver.`);
                        }
                    }, orderAcceptRejectDuration * 1000);
                }
            }

            if (!found) console.log("Could not find an available driver for order #" + orderData.id);
        }
    } catch (error) {
        console.error("Error during execution:", error);
    }
}

// ════════════════════════════════════════════
// UTILITIES
// ════════════════════════════════════════════
const distanceRadiusride = (lat1, lon1, lat2, lon2) => {
    if (lat1 === lat2 && lon1 === lon2) return 0;
    const radlat1 = Math.PI * lat1 / 180;
    const radlat2 = Math.PI * lat2 / 180;
    const theta = lon1 - lon2;
    let dist = Math.sin(radlat1) * Math.sin(radlat2) + Math.cos(radlat1) * Math.cos(radlat2) * Math.cos(Math.PI * theta / 180);
    dist = Math.min(1, dist);
    dist = Math.acos(dist) * 180 / Math.PI * 60 * 1.1515;
    return dist;
};

async function getDriverNearByData() {
    const snapshot = await firestore.collection("settings").doc('DriverNearBy').get();
    return snapshot.data();
}

function is_in_polygon(vertx, verty, testx, testy) {
    let c = false, j = vertx.length - 1;
    for (let i = 0; i < vertx.length; i++) {
        if ((verty[i] > testy) != (verty[j] > testy) &&
            testx < ((vertx[j] - vertx[i]) * (testy - verty[i]) / (verty[j] - verty[i]) + vertx[i])) {
            c = !c;
        }
        j = i;
    }
    return c;
}

// ── Start and Exit ──
cabScheduleRide().then(async () => {
    console.log("Script finished successfully.");
    process.exit(0);
}).catch(err => {
    console.error("Fatal Error:", err);
    process.exit(1);
});