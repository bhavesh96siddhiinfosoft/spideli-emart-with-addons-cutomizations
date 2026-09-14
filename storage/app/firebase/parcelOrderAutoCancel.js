const admin = require('firebase-admin');
const serviceAccount = require('./credentials.json');
const projectId = serviceAccount.project_id;

admin.initializeApp({
    credential: admin.credential.cert(serviceAccount),
    databaseURL: `https://${projectId}-default-rtdb.firebaseio.com`
});

const firestore = admin.firestore();
const messaging = admin.messaging();

async function autoCancelParcelOrderCron() {
    try {
        const currentTimestamp = admin.firestore.Timestamp.now();
        
        const batch = firestore.batch();
        const refundPromises = [];

        const orderPlacedSnapshot = await firestore.collection('parcel_orders').where('status', '==', 'Order Placed').get();
        for (const doc of orderPlacedSnapshot.docs) {
            const order=doc.data();
           
            const createdAt = order.createdAt;
            
            //cancel after 24 hours
            if (createdAt && currentTimestamp.toMillis() > createdAt.toMillis() + (24 * 60 * 60 * 1000)) {
                console.log(`Cancelling order: ${doc.id}`);
                batch.update(doc.ref, { status: "Order Cancelled" });
                refundPromises.push(getCustomerRefund({ ...order, id: doc.id }));
            }
        }

        await batch.commit();
        await Promise.all(refundPromises);
       

    } catch (error) {
        console.error("Error in autoCancelParcelOrderCron:", error);
    }
}

async function getCustomerRefund(orderData) {
    try {
        const customerId = orderData.authorID;
        console.log('customerId:', customerId);

        const totalPrice = await buildParcelTotal(orderData);
        console.log('TotalPrice (number):', totalPrice, typeof totalPrice);

        const customerDoc = await firestore.collection('users').doc(customerId).get();
        if (customerDoc.exists) {
            const customerData = customerDoc.data();
            const customerFcm = customerData.fcmToken || '';

            if (orderData.payment_method !== 'cod') {
                const customerWallet = parseFloat(customerData.wallet_amount || 0);
                const updatedWallet = customerWallet + parseFloat(totalPrice);

                await customerDoc.ref.update({ wallet_amount: updatedWallet });

                const walletId = firestore.collection("tmp").doc().id;
                await firestore.collection('wallet').doc(walletId).set({
                    amount: totalPrice,
                    date: admin.firestore.Timestamp.now(),
                    id: walletId,
                    isTopUp: true,
                    order_id: orderData.id,
                    payment_method: "Wallet",
                    payment_status: 'success',
                    user_id: customerId,
                    transactionUser: 'customer',
                    note: 'Order amount refund'
                });

                console.log(`Amount refunded: ${totalPrice}, New wallet: ${updatedWallet}`);
            }
        }

        await removeRedeemCashback(orderData);
        await sendNotification(customerFcm, orderData, 'not accepted');

    } catch (error) {
        console.error("Error in getCustomerRefund:", error);
    }
}


async function sendNotification(customerFcm,orderData,type) {
    const shortOrderId = orderData.id.slice(-10);
    const customerMessage = {
        title: 'Order Cancelled',
        body: type === 'no driver found'
            ? `Order #${shortOrderId} has been cancelled due to no driver available.`
            : `Order #${shortOrderId} has been cancelled due to restaurant did not accept the order.`
    };
    try {
     
        if (customerFcm) {
            await messaging.send({ notification: customerMessage, token: customerFcm });
            console.log("Notification sent to customer");
        }

    } catch (error) {
        console.error("Error sending notifications:", error);
    }
}

async function removeRedeemCashback(orderData) {
    const snapshot = await firestore.collection('cashback_redeem')
        .where('orderId', '==', orderData.id)
        .limit(1)
        .get();
    if (!snapshot.empty) {
        const docId = snapshot.docs[0].id;
        await firestore.collection('cashback_redeem').doc(docId).delete();
    }
}

async function buildParcelTotal(order) {

    let discount = parseFloat(order.discount || 0);
    let subTotal = parseFloat(order.subTotal || 0);
    let platformFee  = parseFloat(order.platformFee|| 0);
    let total_price = subTotal - discount;

    // Tax calculation
    let totalTax = 0;
    const calcTax = (tax, base, qty = 1) => {
        if (!tax?.enable) return 0;
        const rate = parseFloat(tax.tax || 0);
        let amount = tax.type === 'percentage'
            ? (rate / 100) * base
            : rate * qty;
        return Math.round(amount * 100) / 100;
    };
   
    if (totalTax === 0 && order.taxSetting?.length > 0) {
        console.warn(`Using fallback order-level taxSetting for ${order.id}`);
        for (const tax of order.taxSetting) {
            totalTax += calcTax(tax, total_price);
        }
    }

    (order.platformTax || []).forEach(tax => totalTax += calcTax(tax, platformFee));

    totalTax = Math.round(totalTax * 100) / 100;
    
    total_price += platformFee;
    
    total_price += totalTax;

    return parseFloat(total_price.toFixed(2)); 
}

autoCancelParcelOrderCron();
