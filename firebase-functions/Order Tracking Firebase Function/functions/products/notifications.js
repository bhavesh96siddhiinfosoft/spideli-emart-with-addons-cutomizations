// notifications.js — notification helpers shared by the functions below.
const { getFirestore } = require('firebase-admin/firestore');
const { getMessaging } = require('firebase-admin/messaging');
const logger = require('firebase-functions/logger');

/** A token that can be sent to (the apps used to write "" or "null"). */
function usableToken(token) {
  if (typeof token !== 'string') return false;
  const t = token.trim();
  return t.length > 0 && t !== 'null' && t !== 'undefined';
}

/**
 * The admin ringtone key: 32-bit FNV-1a of the trimmed URL, 8 hex digits.
 * Must stay identical to the apps' OrderRingtone.keyFor.
 * Test vector: 'https://example.com/ring.mp3' -> '955470e2'.
 */
function ringtoneKey(url) {
  const s = (url || '').trim();
  if (!/^https?:\/\//i.test(s)) return '';
  let h = 0x811c9dc5;
  for (const b of Buffer.from(s, 'utf8')) {
    h ^= b;
    h = Math.imul(h, 0x01000193) >>> 0;
  }
  return h.toString(16).padStart(8, '0');
}

/** The current admin ringtone key ('' when none is set). Read on every send. */
async function currentRingtoneKey() {
  try {
    const snap = await getFirestore().doc('settings/globalSettings').get();
    return ringtoneKey(snap.get('order_ringtone_url'));
  } catch (e) {
    logger.warn('notifications: could not read the ringtone setting', { error: String(e) });
    return '';
  }
}

/**
 * Title and body from the admin's dynamic_notification template, or null.
 * No text is written in code: the admin owns the wording.
 */
async function templateText(type) {
  try {
    const snap = await getFirestore().collection('dynamic_notification').where('type', '==', type).limit(1).get();
    if (snap.empty) return null;
    const d = snap.docs[0].data();
    const title = String(d.subject ?? '').trim();
    const body = String(d.message ?? '').trim();
    return title || body ? { title, body } : null;
  } catch (e) {
    logger.error('notifications: reading the template failed', { type, error: String(e) });
    return null;
  }
}

/** FCM errors meaning the token is dead (the app saves a new one on its next start). */
function isDeadToken(code) {
  return code === 'messaging/registration-token-not-registered' || code === 'messaging/invalid-registration-token';
}

/** Sends one message; never throws, never logs the token. */
async function send(message, context) {
  try {
    await getMessaging().send(message);
    logger.info('notification sent', context);
    return true;
  } catch (e) {
    const code = e && e.code;
    if (isDeadToken(code)) logger.warn('notification: recipient token no longer registered', { ...context, code });
    else logger.error('notification failed', { ...context, code, error: String(e && e.message).replace(/[A-Za-z0-9_\-:]{60,}/g, '<redacted>') });
    return false;
  }
}

module.exports = { usableToken, ringtoneKey, currentRingtoneKey, templateText, send };
