const functions = require('firebase-functions');
const admin = require('firebase-admin');

const serviceAccount = require("./serviceAccountKey.json");
if (admin.apps.length === 0) {
    admin.initializeApp({
        credential: admin.credential.cert(serviceAccount),
        databaseURL: "https://spideli-870b0-default-rtdb.firebaseio.com"
    });
}

const delivery = require('./products/delivery');
const parcel = require('./products/parcel');
const cab = require('./products/cab');
const rental = require('./products/rental');
const scheduledOrder = require('./products/scheduledOrder');

// Multivendor & E-commerce delivery service function
exports.deliveryDispatch = delivery.dispatch;

// Scheduled order notification function for stores
exports.scheduleOrderNotification = scheduledOrder.scheduleOrderNotification;

// Parcel delivery service function
exports.parcelDispatch = parcel.dispatch;

// Cab ride booking service function
exports.cabDispatch = cab.dispatch;

// Rental vehicle service function
exports.rentalDispatch = rental.dispatch;

// Delete auth user function
exports.deleteUser = functions.https.onCall(async (data, context) => {
    try {
        await admin.auth().deleteUser(data.uid);
        return { result: 'user successfully deleted' };
    } catch (error) {
        throw new functions.https.HttpsError('failed-precondition', 'The function must be called while authenticated.');
    }
});