const admin = require('firebase-admin');
const serviceAccount=require('./credentials.json');
    try {
        admin.initializeApp({
            credential: admin.credential.cert(serviceAccount),
            databaseURL: `https://${serviceAccount.project_id}.firebaseio.com`
        });
        console.log('Firebase initialized successfully');
    } catch (error) {
        console.error('Error initializing Firebase:', error);
        process.exit(1);
    }


const firestore = admin.firestore();
const messaging = admin.messaging();


async function sendScheduleNotification() {

    const timingSnapshot = await firestore.collection('settings').doc("scheduleOrderNotification").get();
    if (!timingSnapshot.exists) {
        console.log("No notification timing settings found.");
        return;
    }

    let timingData = timingSnapshot.data();
    const { timeUnit, notifyTime } = timingData;
    if (timeUnit==null || notifyTime == null) {
        console.log("Incomplete timing settings.");
        return;
    }

    const ordersSnapshot = await firestore.collection('vendor_orders').where('status', '==', 'Order Placed').where('scheduleTime','!=',null).get();

    for (const doc of ordersSnapshot.docs) {
        const data = doc.data();

        if (!data || data.scheduleTime=='') {
            console.log(`Skipping order ${doc.id} due to missing  date/time`);
            continue;
        } 
        else {
                            
            const nowDate = new Date();
            const scheduleDate = data.scheduleTime.toDate();
            const bufferMs = 60 * 1000;
            const diffInMs=scheduleDate-nowDate;
            console.log(`Now: ${nowDate}, Schedule: ${scheduleDate}`);
            let notifyBeforeMs;
            if (timeUnit === 'minute') {
                notifyBeforeMs = notifyTime * 60 * 1000;
            } else if (timeUnit === 'hour') {
                notifyBeforeMs = notifyTime * 60 * 60 * 1000;
            } else {
                    notifyBeforeMs = notifyTime * 24 * 60 * 60 * 1000;
            }
            if(data.notificationSent !== true && diffInMs>0&&Math.abs(diffInMs-notifyBeforeMs)<=bufferMs) {
                await firestore.collection("vendor_orders").doc(data.id).update({
                    notificationSent: true
                })
                await sendNotification(data);

            }
        }
    }
}

async function sendNotification(orderData) {
   
    try {
        const vendorUserId=orderData.vendor.author;
        console.log('vendorUserId--->'+vendorUserId)
        const userTimeZone = process.env.APP_TIMEZONE || 'UTC';
        var scheduleDate = orderData.scheduleTime.toDate().toDateString();
        var time=orderData.scheduleTime.toDate().toLocaleTimeString('en-US',{ timeZone: userTimeZone });
        

        const vendorDoc = await firestore.collection('users').doc(vendorUserId).get();

        const vendorFcm = vendorDoc.exists ? vendorDoc.data().fcmToken : null;
        console.log('fcmtoken--->'+vendorFcm);

        // Send notification to driver
        if (vendorFcm) {

            try {
                const response = await messaging.send({
                    notification: {
                        title: 'Scheduled Order Reminder',
                        body: `Your have scheduled order for ${scheduleDate} at ${time}.`,
                    },
                    token: vendorFcm
                });
                console.log(`Notification sent to driver ${vendorUserId}:`, response);
            } catch (error) {
                console.error(`Error sending to driver ${vendorUserId}:`, error);
            }
        }
    } catch (error) {
        console.error(`Error in sendNotification:`, error);
    }
}

sendScheduleNotification();
