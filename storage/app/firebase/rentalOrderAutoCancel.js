const admin = require('firebase-admin');
const serviceAccount = require('./credentials.json');
const projectId = serviceAccount.project_id;

admin.initializeApp({
    credential: admin.credential.cert(serviceAccount),
    databaseURL: `https://${projectId}-default-rtdb.firebaseio.com`
});

const firestore = admin.firestore();
const messaging = admin.messaging();

async function autoCancelRentalOrderCron() {
    try {
        const currentTimestamp = admin.firestore.Timestamp.now();
        const batch = firestore.batch();

        const orderPlacedSnapshot = await firestore.collection('rental_orders')
            .where('status', '==', 'Order Placed')
            .get();

        for (const doc of orderPlacedSnapshot.docs) {
            const order = doc.data();
            const createdAt = order.createdAt;

            // Cancel after 24 hours
            if (createdAt && currentTimestamp.toMillis() > createdAt.toMillis() + (24 * 60 * 60 * 1000)) {
                console.log(`Cancelling rental order: ${doc.id}`);
                batch.update(doc.ref, { status: "Order Cancelled" });

                // Send notification to customer
                await sendNotification(order);
            }
        }

        await batch.commit();
        console.log("All eligible rental orders cancelled successfully.");

    } catch (error) {
        console.error("Error in autoCancelRentalOrderCron:", error);
    }
}

async function sendNotification(orderData) {
    try {
        const customerId = orderData.authorID;
        const customerDoc = await firestore.collection('users').doc(customerId).get();
        if (!customerDoc.exists) return;

        const customerData = customerDoc.data();
        const customerFcm = customerData.fcmToken || '';
        const shortOrderId = orderData.id ? orderData.id.slice(-10) : '';

        const message = {
            notification: {
                title: 'Rental Order Cancelled',
                body: `Your rental order #${shortOrderId} has been automatically cancelled since it was not accepted within 24 hours.`,
            },
            token: customerFcm,
        };

        if (customerFcm) {
            await messaging.send(message);
            console.log(`Notification sent to customer (${customerId}) for order ${orderData.id}`);
        }

    } catch (error) {
        console.error("Error sending notification:", error);
    }
}

autoCancelRentalOrderCron();
