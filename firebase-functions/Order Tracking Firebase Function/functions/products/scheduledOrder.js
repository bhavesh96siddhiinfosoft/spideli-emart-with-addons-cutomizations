// scheduledOrder.js
const { onSchedule } = require('firebase-functions/v2/scheduler');
const { getFirestore, FieldValue } = require('firebase-admin/firestore');
const logger = require('firebase-functions/logger');
const { usableToken, currentRingtoneKey, templateText, send } = require('./notifications');

const ORDER_PLACED = 'Order Placed';
const SENT = 'scheduledNotificationSent';
const SENT_AT = 'scheduledNotificationAt';
const SKIPPED = 'scheduledNotificationSkipped';
const PUSH_TYPE = 'scheduled_order_due';   // the Store app routes this to New
const TEMPLATE = 'schedule_order';

/** Lead time in ms, read exactly like the Store app. */
function leadMs(notifyTime, timeUnit) {
  const n = Number.parseInt(String(notifyTime ?? '').trim(), 10);
  const value = Number.isFinite(n) ? n : 0;
  const unit = String(timeUnit ?? '').trim();
  const ms = unit === 'minute' ? value * 60e3 : unit === 'hour' ? value * 3600e3 : unit === 'day' ? value * 86400e3 : 60e3;
  return ms < 0 ? 0 : ms;
}

function toMillis(v) {
  if (v === null || v === undefined) return null;
  if (typeof v === 'number') return v;
  if (typeof v.toMillis === 'function') return v.toMillis();
  if (typeof v.toDate === 'function') return v.toDate().getTime();
  if (v instanceof Date) return v.getTime();
  return null;
}

/** done | skip | wait | notify — run on the query result AND again inside the transaction. */
function decide(order, now, lead) {
  if (!order || order[SENT] === true) return { kind: 'done' };
  if (order.status !== ORDER_PLACED) return { kind: 'skip', status: String(order.status ?? '') };
  const at = toMillis(order.scheduleTime);
  if (at !== null && at - lead > now) return { kind: 'wait' };
  return { kind: 'notify' };
}

/** The push to the store owner. */
function storeMessage(token, orderId, text, key) {
  const data = { type: PUSH_TYPE, orderId };
  if (!text) {
    // No template text: data-only, nothing empty is shown; the Store app
    // still moves the order to New when it runs.
    return {
      token,
      data,
      android: { priority: 'high' },
      apns: { headers: { 'apns-priority': '5', 'apns-push-type': 'background' }, payload: { aps: { 'content-available': 1 } } },
    };
  }
  return {
    token,
    notification: { title: text.title, body: text.body },
    data,
    android: {
      priority: 'high',
      notification: { channelId: key ? `new_order_rt_${key}` : 'new_order', sound: 'order_alert' },
    },
    apns: {
      headers: { 'apns-priority': '10' },
      payload: { aps: { sound: key ? `order_ringtone_${key}.caf` : 'order_alert.caf' } },
    },
  };
}

/** The store owner's uid: order.vendor.author, else vendors/{vendorID}.author. */
async function ownerOf(db, order) {
  const author = String(order?.vendor?.author ?? '').trim();
  if (author) return author;
  const vendorId = String(order?.vendorID ?? '').trim();
  if (!vendorId) return null;
  const store = await db.collection('vendors').doc(vendorId).get();
  const a = String(store.get('author') ?? '').trim();
  return a || null;
}

const scheduledOrderNotifier = onSchedule(
  // Use the same region as the project's other functions.
  { schedule: 'every 1 minutes', region: 'us-central1', timeZone: 'Etc/UTC', retryCount: 0, maxInstances: 1, timeoutSeconds: 120, memory: '256MiB' },
  async () => {
    const db = getFirestore();
    const now = Date.now();
    const settings = await db.doc('settings/scheduleOrderNotification').get();
    const lead = settings.exists ? leadMs(settings.get('notifyTime'), settings.get('timeUnit')) : 0;

    // One equality filter: covered by Firestore's automatic index.
    const pending = await db.collection('vendor_orders').where(SENT, '==', false).get();
    if (pending.empty) return null;

    let text;   // template, read once per run when first needed
    let key;    // ringtone key, read once per run when first needed
    for (const doc of pending.docs) {
      const first = decide(doc.data(), now, lead);
      if (first.kind === 'wait' || first.kind === 'done') continue;
      try {
        // Claim: only one run can mark the order, so it is pushed at most once.
        /* eslint-disable-next-line no-await-in-loop */
        const claim = await db.runTransaction(async (tx) => {
          const snap = await tx.get(doc.ref);
          const d = decide(snap.data(), now, lead);
          if (d.kind === 'notify') {
            tx.update(doc.ref, { [SENT]: true, [SENT_AT]: FieldValue.serverTimestamp() });
            return { kind: 'notify', order: snap.data() };
          }
          if (d.kind === 'skip') {
            // Accepted / cancelled before it was due: mark it, never push.
            tx.update(doc.ref, { [SENT]: true, [SENT_AT]: FieldValue.serverTimestamp(), [SKIPPED]: d.status || 'unknown' });
          }
          return { kind: d.kind };
        });
        if (claim.kind !== 'notify') continue;

        /* eslint-disable-next-line no-await-in-loop */
        const owner = await ownerOf(db, claim.order);
        if (!owner) { logger.warn('scheduled order: no store owner', { orderId: doc.id }); continue; }
        /* eslint-disable-next-line no-await-in-loop */
        const token = (await db.collection('users').doc(owner).get()).get('fcmToken');
        if (!usableToken(token)) { logger.warn('scheduled order: store owner has no FCM token', { orderId: doc.id, owner }); continue; }

        if (text === undefined) {
          /* eslint-disable-next-line no-await-in-loop */
          text = await templateText(TEMPLATE);
        }
        if (key === undefined) {
          /* eslint-disable-next-line no-await-in-loop */
          key = await currentRingtoneKey();
        }
        /* eslint-disable-next-line no-await-in-loop */
        await send(storeMessage(token.trim(), doc.id, text, key), { kind: PUSH_TYPE, orderId: doc.id, owner });
      } catch (e) {
        logger.error('scheduled order: handling failed', { orderId: doc.id, error: String(e) });
      }
    }
    return null;
  },
);

exports.scheduledOrderNotifier = scheduledOrderNotifier;
exports.scheduleOrderNotification = scheduledOrderNotifier;
