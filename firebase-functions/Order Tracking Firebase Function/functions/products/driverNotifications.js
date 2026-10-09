// driverNotifications.js
const { getFirestore } = require('firebase-admin/firestore');
const logger = require('firebase-functions/logger');
const { usableToken, currentRingtoneKey, templateText, send } = require('./notifications');

const TEMPLATE = 'new_delivery_order';   // dynamic_notification type for the text

/**
 * Notifies the driver just assigned to [orderId].
 * [type] is the service: 'order' (food / e-commerce delivery), and — if the
 * same helper is used by the other dispatch functions — 'parcel', 'cab', 'rental'.
 */
async function notifyAssignedDriver(orderId, driverId, type = 'order') {
  if (!orderId || !driverId) return false;
  const driver = await getFirestore().collection('users').doc(driverId).get();
  const token = driver.get('fcmToken');
  if (!usableToken(token)) {
    // The Driver app still finds the offer from orderRequestData / "Driver Pending" when it opens.
    logger.warn('order accepted: assigned driver has no FCM token', { orderId, driverId });
    return false;
  }
  const text = await templateText(TEMPLATE);
  const key = await currentRingtoneKey();
  return send(
    {
      token: token.trim(),
      ...(text ? { notification: { title: text.title, body: text.body } } : {}),
      data: {
        click_action: 'FLUTTER_NOTIFICATION_CLICK',
        type,                      // the Driver app opens its incoming-order popup for this type
        id: orderId,
        orderId,
        status: 'Driver Pending',
      },
      android: {
        priority: 'high',
        notification: { channelId: key ? `driver_jobs_rt_${key}` : 'spideli', sound: 'default' },
      },
      apns: {
        headers: { 'apns-priority': '10' },
        payload: { aps: { sound: key ? `order_ringtone_${key}.caf` : 'default', 'content-available': 1 } },
      },
    },
    { kind: 'order_accepted_driver', orderId, driverId },
  );
}

module.exports = { notifyAssignedDriver };
