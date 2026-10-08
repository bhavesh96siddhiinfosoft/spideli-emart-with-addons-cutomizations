const admin = require("firebase-admin");

/**
 * Calculates distance between two coordinates in kilometers.
 */
const distanceRadius = (lat1, lon1, lat2, lon2) => {
    lat1 = parseFloat(lat1);
    lon1 = parseFloat(lon1);
    lat2 = parseFloat(lat2);
    lon2 = parseFloat(lon2);

    if (isNaN(lat1) || isNaN(lon1) || isNaN(lat2) || isNaN(lon2)) return 999999;
    if (lat1 === lat2 && lon1 === lon2) return 0;

    const radlat1 = (Math.PI * lat1) / 180;
    const radlat2 = (Math.PI * lat2) / 180;
    const theta = lon1 - lon2;
    const radtheta = (Math.PI * theta) / 180;

    let dist = Math.sin(radlat1) * Math.sin(radlat2) + Math.cos(radlat1) * Math.cos(radlat2) * Math.cos(radtheta);
    if (dist > 1) dist = 1;
    dist = Math.acos(dist);
    dist = (dist * 180) / Math.PI;
    dist = dist * 60 * 1.1515 * 1.60934;
    return dist;
};

/**
 * Loads DriverNearBy settings document.
 */
async function getDriverNearByData(firestore) {
    try {
        const snapshot = await firestore.collection("settings").doc("DriverNearBy").get();
        return snapshot.data() || {};
    } catch (e) {
        console.error("Error reading DriverNearBy settings:", e);
        return {};
    }
}

/**
 * Finds zone ID for given coordinates.
 */
async function getUserZoneId(firestore, address_lng, address_lat) {
    if (!address_lng || !address_lat) return null;
    let zone_id = null;
    try {
        const snapshots = await firestore.collection("zone").where("publish", "==", true).get();
        for (const doc of snapshots.docs) {
            const zone = doc.data();
            if (!Array.isArray(zone.area)) continue;
            const vertices_x = zone.area.map(p => p.longitude);
            const vertices_y = zone.area.map(p => p.latitude);

            if (is_in_polygon(vertices_x.length, vertices_x, vertices_y, address_lng, address_lat)) {
                zone_id = zone.id;
                break;
            }
        }
    } catch (e) {
        console.error("Error checking zone:", e);
    }
    return zone_id;
}

function is_in_polygon(nvert, vertx, verty, testx, testy) {
    let c = false;
    for (let i = 0, j = nvert - 1; i < nvert; j = i++) {
        if (
            ((verty[i] > testy) !== (verty[j] > testy)) &&
            (testx < ((vertx[j] - vertx[i]) * (testy - verty[i])) / (verty[j] - verty[i]) + vertx[i])
        ) {
            c = !c;
        }
    }
    return c;
}

/**
 * Sends a high-priority, sound-enabled FCM push notification to a driver device.
 * Includes both notification and Flutter-compatible data payload.
 */
async function sendDriverNotification(fcmToken, title, body, dataPayload = {}) {
    if (!fcmToken) return false;

    const message = {
        token: fcmToken,
        notification: {
            title: title,
            body: body,
        },
        data: {
            click_action: "FLUTTER_NOTIFICATION_CLICK",
            title: String(title || ""),
            body: String(body || ""),
            sound: "default",
            ...Object.keys(dataPayload).reduce((acc, key) => {
                acc[key] = String(dataPayload[key] !== undefined && dataPayload[key] !== null ? dataPayload[key] : "");
                return acc;
            }, {})
        },
        android: {
            priority: "high",
            notification: {
                channelId: "spideli",
                sound: "default",
                priority: "high",
                defaultSound: true,
                defaultVibrateTimings: true,
            }
        },
        apns: {
            headers: {
                "apns-priority": "10"
            },
            payload: {
                aps: {
                    sound: "default",
                    contentAvailable: true,
                    badge: 1
                }
            }
        }
    };

    try {
        await admin.messaging().send(message);
        console.log(`Notification sent successfully: "${title}" to token ${fcmToken.substring(0, 15)}...`);
        return true;
    } catch (error) {
        console.error("FCM Send Error:", error.message || error);
        return false;
    }
}

/**
 * Sends a high-priority, sound-enabled FCM push notification to a store/vendor device.
 * Includes both notification and Flutter-compatible data payload.
 */
async function sendStoreNotification(fcmToken, title, body, dataPayload = {}) {
    if (!fcmToken) return false;

    const message = {
        token: fcmToken,
        notification: {
            title: title,
            body: body,
        },
        data: {
            click_action: "FLUTTER_NOTIFICATION_CLICK",
            title: String(title || ""),
            body: String(body || ""),
            sound: "default",
            ...Object.keys(dataPayload).reduce((acc, key) => {
                acc[key] = String(dataPayload[key] !== undefined && dataPayload[key] !== null ? dataPayload[key] : "");
                return acc;
            }, {})
        },
        android: {
            priority: "high",
            notification: {
                channelId: "spideli",
                sound: "default",
                priority: "high",
                defaultSound: true,
                defaultVibrateTimings: true,
            }
        },
        apns: {
            headers: {
                "apns-priority": "10"
            },
            payload: {
                aps: {
                    sound: "default",
                    contentAvailable: true,
                    badge: 1
                }
            }
        }
    };

    try {
        await admin.messaging().send(message);
        console.log(`[Store Notification] Sent successfully: "${title}" to token ${fcmToken.substring(0, 15)}...`);
        return true;
    } catch (error) {
        console.error("[Store Notification] FCM Send Error:", error.message || error);
        return false;
    }
}

module.exports = {
    distanceRadius,
    getDriverNearByData,
    getUserZoneId,
    sendDriverNotification,
    sendStoreNotification
};

