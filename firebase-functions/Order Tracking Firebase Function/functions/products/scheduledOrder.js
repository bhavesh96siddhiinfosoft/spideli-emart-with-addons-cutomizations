const { onSchedule } = require("firebase-functions/v2/scheduler");
const { getFirestore } = require("firebase-admin/firestore");
const admin = require("firebase-admin");
const { sendStoreNotification } = require("./helper");

if (admin.apps.length === 0) {
    admin.initializeApp();
}

const getDb = () => getFirestore();

/**
 * Processes a single order and dispatches notification if the configured schedule time has arrived.
 */
async function processOrder(doc, firestore, notifyBeforeMs, notifyTime, timeUnit, defaultTitle, defaultMessage, now) {
    const data = doc.data();
    const orderId = doc.id;

    if (!data || !data.scheduleTime || data.scheduleTime === "") {
        return null;
    }

    if (data.notificationSent === true || data.scheduleNotificationSent === true) {
        return null;
    }

    if (data.status !== "Order Placed") {
        return null;
    }

    let scheduleDate = null;
    if (typeof data.scheduleTime.toDate === "function") {
        scheduleDate = data.scheduleTime.toDate();
    } else if (data.scheduleTime instanceof Date) {
        scheduleDate = data.scheduleTime;
    } else {
        scheduleDate = new Date(data.scheduleTime);
    }

    if (!scheduleDate || isNaN(scheduleDate.getTime())) {
        return null;
    }

    const scheduleTimestamp = scheduleDate.getTime();
    const timeRemaining = scheduleTimestamp - now;

    // Skip orders whose scheduled time has passed more than 2 hours ago
    if (scheduleTimestamp < (now - 2 * 60 * 60 * 1000)) {
        return null;
    }

    // Check if within the configured notification window
    if (timeRemaining <= notifyBeforeMs) {
        console.log(`[Scheduled Order] Triggering notification for Order #${orderId}. Scheduled for: ${scheduleDate.toISOString()}, Window: ${notifyTime} ${timeUnit}`);

        // Update notification flag first to prevent duplicate notifications
        try {
            await doc.ref.update({
                notificationSent: true,
                scheduleNotificationSent: true,
                scheduleNotificationSentAt: admin.firestore.FieldValue.serverTimestamp()
            });
        } catch (updateErr) {
            console.error(`[Scheduled Order] Failed to update notificationSent flag for Order #${orderId}:`, updateErr);
            return null;
        }

        // Resolve vendor FCM token
        let vendorFcmToken = null;
        const vendorUserId = data.vendor
            ? (data.vendor.author || data.vendor.authorID || data.vendor.id)
            : null;

        if (vendorUserId) {
            try {
                const userDoc = await firestore.collection("users").doc(vendorUserId).get();
                if (userDoc.exists) {
                    vendorFcmToken = userDoc.data().fcmToken;
                }
            } catch (userErr) {
                console.error(`[Scheduled Order] Error fetching user ${vendorUserId}:`, userErr);
            }
        }

        if (!vendorFcmToken && data.vendor && data.vendor.fcmToken) {
            vendorFcmToken = data.vendor.fcmToken;
        }

        if (!vendorFcmToken) {
            console.warn(`[Scheduled Order] No FCM token found for store/vendor of Order #${orderId}`);
            return null;
        }

        const shortOrderId = orderId.substring(0, 7);
        const dateStr = scheduleDate.toDateString();
        const timeStr = scheduleDate.toLocaleTimeString("en-US");

        const title = defaultTitle.replace("{orderId}", shortOrderId);
        const body = defaultMessage
            .replace("{orderId}", shortOrderId)
            .replace("{date}", dateStr)
            .replace("{time}", timeStr);

        await sendStoreNotification(vendorFcmToken, title, body, {
            orderId: orderId,
            id: orderId,
            type: "schedule_order",
            status: "Order Placed",
            scheduleTime: scheduleDate.toISOString()
        });
    }

    return null;
}

/**
 * Cloud Scheduler function running every minute to notify stores of upcoming scheduled orders.
 */
exports.scheduleOrderNotification = onSchedule({
    schedule: "every 1 minutes",
    timeZone: "UTC",
    retryCount: 0
}, async () => {
    const firestore = getDb();

    // 1. Load notification timing settings from settings/scheduleOrderNotification
    let notifyTime = 30;
    let timeUnit = "minute";

    try {
        const timingSnapshot = await firestore.collection("settings").doc("scheduleOrderNotification").get();
        if (timingSnapshot.exists) {
            const timingData = timingSnapshot.data() || {};
            if (timingData.notifyTime !== undefined && timingData.notifyTime !== null && timingData.notifyTime !== "") {
                notifyTime = parseFloat(timingData.notifyTime);
            }
            if (timingData.timeUnit) {
                timeUnit = timingData.timeUnit;
            }
        }
    } catch (err) {
        console.error("[Scheduled Order] Error reading scheduleOrderNotification settings:", err);
    }

    // Convert lead time to milliseconds
    const unit = String(timeUnit || "minute").toLowerCase();
    let notifyBeforeMs = 0;
    if (unit.startsWith("min")) {
        notifyBeforeMs = notifyTime * 60 * 1000;
    } else if (unit.startsWith("hour") || unit.startsWith("hr")) {
        notifyBeforeMs = notifyTime * 60 * 60 * 1000;
    } else if (unit.startsWith("day")) {
        notifyBeforeMs = notifyTime * 24 * 60 * 60 * 1000;
    } else {
        notifyBeforeMs = notifyTime * 60 * 1000;
    }

    // 2. Load dynamic notification template for 'schedule_order'
    let defaultTitle = "Scheduled Order Reminder";
    let defaultMessage = "You have a scheduled order #{orderId} for {date} at {time}.";

    try {
        const dynamicSnap = await firestore.collection("dynamic_notification")
            .where("type", "==", "schedule_order")
            .limit(1)
            .get();
        if (!dynamicSnap.empty) {
            const dynData = dynamicSnap.docs[0].data();
            if (dynData.subject) {
                defaultTitle = dynData.subject;
            }
            if (dynData.message) {
                defaultMessage = dynData.message;
            }
        }
    } catch (e) {
        console.warn("[Scheduled Order] Could not fetch dynamic_notification template:", e.message);
    }

    // 3. Query active orders placed
    let ordersSnapshot;
    try {
        ordersSnapshot = await firestore.collection("vendor_orders")
            .where("status", "==", "Order Placed")
            .get();
    } catch (queryErr) {
        console.error("[Scheduled Order] Error querying vendor_orders:", queryErr);
        return null;
    }

    if (ordersSnapshot.empty) {
        return null;
    }

    const now = Date.now();

    // 4. Process matching orders
    const tasks = ordersSnapshot.docs.map((doc) =>
        processOrder(doc, firestore, notifyBeforeMs, notifyTime, timeUnit, defaultTitle, defaultMessage, now)
    );

    await Promise.allSettled(tasks);
    return null;
});
