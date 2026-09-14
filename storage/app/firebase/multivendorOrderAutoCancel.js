const admin = require('firebase-admin');
const serviceAccount = require('./credentials.json');
const projectId = serviceAccount.project_id;

admin.initializeApp({
    credential: admin.credential.cert(serviceAccount),
    databaseURL: `https://${projectId}-default-rtdb.firebaseio.com`
});

const firestore = admin.firestore();
const messaging = admin.messaging();

async function autoCancelMultivendorOrderCron() {
    try {
        const currentTimestamp = admin.firestore.Timestamp.now();

        const timingSnapshot = await firestore.collection('settings').doc("DriverNearBy").get();
        if (!timingSnapshot.exists) {
            console.log("No notification timing settings found.");
            return;
        }

        const timingData = timingSnapshot.data();
        const orderAutoCancelDuration = timingData.orderAutoCancelDuration || 5;
        const batch = firestore.batch();
        const refundPromises = [];

        const orderPlacedSnapshot = await firestore.collection('vendor_orders').where('status', '==', 'Order Placed').get();
        for (const doc of orderPlacedSnapshot.docs) {
            const order=doc.data();
           
            const sectionDoc = await firestore.collection('sections').doc(order.vendor.section_id).get();
            const service_type = sectionDoc.data()?.serviceTypeFlag;
            if (service_type === 'ecommerce-service') {
                continue; 
            }
            const createdAt = order.createdAt;
            console.log('order scheduleTime--->'+order.scheduleTime)
          
            const scheduleTime = order.scheduleTime?.toDate?.();
            const baseTime = scheduleTime || createdAt.toDate();
            const expirationTime = new Date(baseTime.getTime() + orderAutoCancelDuration * 60000);
            const expirationTimestamp = admin.firestore.Timestamp.fromDate(expirationTime);

            if (currentTimestamp.toMillis() > expirationTimestamp.toMillis()) {
                console.log(`Cancelling order: ${doc.id}`);
                batch.update(doc.ref, { status: "Order Cancelled" });
                refundPromises.push(getCustomerRefund({ ...order, id: doc.id }));
            }
        }

        await batch.commit();
        await Promise.all(refundPromises);

        const ordersSnapshot = await firestore
            .collection('vendor_orders')
            .where('status', '==', 'Order Accepted')
            .where('orderAutoCancelAt', '<', currentTimestamp)
            .get();

        for (const doc of ordersSnapshot.docs) {
            const orderData = doc.data();
            if(!orderData) continue;
            
            const sectionId = orderData?.vendor?.section_id;
            if (!sectionId || typeof sectionId !== "string") {
                console.error("Invalid section_id:", sectionId);
                return;
            }

            const sectionDoc = await firestore.collection('sections').doc(sectionId).get();
            const service_type = sectionDoc.data()?.serviceTypeFlag;

            if (service_type === 'ecommerce-service') {
                continue; 
            }
            console.log(`Cancelling accepted order: ${orderData.id}`);
            await doc.ref.update({ status: "Order Cancelled" });
            await getRefund({ ...orderData, id: doc.id });
        }

    } catch (error) {
        console.error("Error in autoCancelMultivendorOrderCron:", error);
    }
}

async function getRefund(orderData) {
    try {
        const orderId       = orderData.id;
        const vendorId      = orderData.vendor?.author;
        const customerId    = orderData.author?.id;
        const paymentMethod = orderData.payment_method;

        if (!vendorId || !customerId) {
            console.warn(`Missing IDs for order ${orderId}`);
            return;
        }

        console.log(`[CANCEL] Processing refund for order ${orderId}`);

        let vendorBase = 0;
        let vendorTax  = 0;
        let totalVendorOriginalCredit = 0;

        const walletSnap = await firestore.collection('wallet')
            .where('user_id', '==', vendorId)
            .where('order_id', '==', orderId)
            .where('isTopUp', '==', true)
            .get();

        walletSnap.forEach(doc => {
            const d = doc.data();
            const amt = parseFloat(d.amount || 0);
            if (d.payment_method === 'tax') {
                vendorTax += amt;
            } else {
                vendorBase += amt;
            }
            totalVendorOriginalCredit += amt;
        });

        console.log(`[VENDOR] Original credit breakdown: base ₹${vendorBase.toFixed(2)} + tax ₹${vendorTax.toFixed(2)} = total ₹${totalVendorOriginalCredit.toFixed(2)}`);

        if (totalVendorOriginalCredit > 0) {
            const vendorRef = firestore.collection('users').doc(vendorId);
            const vendorSnap = await vendorRef.get();

            if (vendorSnap.exists) {
                const vData = vendorSnap.data();
                const current = parseFloat(vData.wallet_amount || 0) || 0;

                await vendorRef.update({
                    wallet_amount: current - totalVendorOriginalCredit
                });

                console.log(`[VENDOR] Deducted ₹${totalVendorOriginalCredit.toFixed(2)} from vendor wallet`);

                const baseRevId = firestore.collection("tmp").doc().id;
                await firestore.collection('wallet').doc(baseRevId).set({
                    amount: vendorBase,
                    date: admin.firestore.Timestamp.now(),
                    id: baseRevId,
                    isTopUp: false,
                    order_id: orderId,
                    payment_method: "Wallet",
                    payment_status: 'success',
                    user_id: vendorId,
                    transactionUser: 'vendor',
                    note: 'Order cancelled - base amount reversed'
                });

                if (vendorTax > 0) {
                    const taxRevId = firestore.collection("tmp").doc().id;
                    await firestore.collection('wallet').doc(taxRevId).set({
                        amount: vendorTax,
                        date: admin.firestore.Timestamp.now(),
                        id: taxRevId,
                        isTopUp: false,
                        order_id: orderId,
                        payment_method: "tax",
                        payment_status: 'success',
                        user_id: vendorId,
                        transactionUser: 'vendor',
                        note: 'Order cancelled - tax amount reversed'
                    });
                }
            }
        }

        let customerFcm = null;
        let vendorFcm   = null;

        const custSnap = await firestore.collection('users').doc(customerId).get();
        if (custSnap.exists) customerFcm = custSnap.data().fcmToken || null;

        const vendSnap = await firestore.collection('users').doc(vendorId).get();
        if (vendSnap.exists) vendorFcm = vendSnap.data().fcmToken || null;

        let customerRefunded = 0;
        if (paymentMethod !== 'cod') {
            customerRefunded = await getCustomerRefund(orderData);
            console.log(`[CUSTOMER] Full refund processed: ₹${customerRefunded.toFixed(2)}`);
        } else {
            console.log(`[CUSTOMER] COD order - no wallet refund`);
        }

        console.log(`[SUMMARY] Order ${orderId} cancellation:`, {
            vendorDebited: totalVendorOriginalCredit.toFixed(2),
            customerCredited: customerRefunded.toFixed(2),
            difference: (customerRefunded - totalVendorOriginalCredit).toFixed(2),
            note: "Difference = delivery/packaging/platform/taxes (platform absorbs loss)"
        });

        await removeRedeemCashback(orderData);

        // For Order Accepted timeout → only 'no driver found'
        await sendNotification(vendorFcm, customerFcm, orderData, 'no driver found');

    } catch (err) {
        console.error(`Refund failed for ${orderData?.id || 'unknown'}:`, err);
    }
}

async function getCustomerRefund(orderData) {
    try {
        const customerId = orderData.author?.id;
        if (!customerId) {
            console.warn(`No customer ID in order ${orderData.id}`);
            return 0;
        }

        let itemSubtotal = 0;
        for (const p of (orderData.products || [])) {
            const qty = parseInt(p.quantity || 1, 10);
            const price = parseFloat(p.price || 0);
            const extras = parseFloat(p.extras_price || 0);
            itemSubtotal += (price + extras) * qty;
        }
        itemSubtotal = Math.round(itemSubtotal * 100) / 100;

        let totalDiscount = parseFloat(orderData.discount || 0);
        if (orderData.specialDiscount?.special_discount) {
            totalDiscount += parseFloat(orderData.specialDiscount.special_discount);
        }
        totalDiscount = Math.round(totalDiscount * 100) / 100;

        const subtotalAfterDiscount = Math.max(0, itemSubtotal - totalDiscount);

        const delivery  = parseFloat(orderData.deliveryCharge   || 0);
        const tip       = parseFloat(orderData.tip_amount       || 0);
        const packaging = parseFloat(orderData.vendor?.packagingCharge || 0);
        const platform  = parseFloat(orderData.platformFee      || 0);

        let totalTax = 0;
        
        const calcTax = (tax, base, qty = 1) => {
            if (!tax?.enable) return 0;
            const rate = parseFloat(tax.tax || 0);
            let amount = tax.type === 'percentage'
                ? (rate / 100) * base
                : rate * qty;
            return Math.round(amount * 100) / 100;
        };

        if (orderData.taxScope === "product" || orderData.products?.some(p => p.taxSetting?.length > 0)) {
            for (const p of orderData.products || []) {
                const qty = parseInt(p.quantity || 1, 10);
                const basePrice = parseFloat(
                    p.discountPrice && parseFloat(p.discountPrice) > 0 ? p.discountPrice : p.price || 0
                );
                const gross = (basePrice + parseFloat(p.extras_price || 0)) * qty;
                const discountShare = itemSubtotal > 0 ? (gross / itemSubtotal) * totalDiscount : 0;
                const taxable = Math.max(0, Math.round((gross - discountShare) * 100) / 100);

                for (const tax of (p.taxSetting || [])) {
                    totalTax += calcTax(tax, taxable, qty);
                }
            }
        }

        (orderData.driverDeliveryTax || []).forEach(tax => totalTax += calcTax(tax, delivery));
        (orderData.packagingTax || []).forEach(tax => totalTax += calcTax(tax, packaging));
        (orderData.platformTax || []).forEach(tax => totalTax += calcTax(tax, platform));

        if (totalTax === 0 && orderData.taxSetting?.length > 0) {
            console.warn(`Using fallback order-level taxSetting for ${orderData.id}`);
            for (const tax of orderData.taxSetting) {
                totalTax += calcTax(tax, subtotalAfterDiscount);
            }
        }

        totalTax = Math.round(totalTax * 100) / 100;

        const refundAmount = subtotalAfterDiscount + delivery + tip + packaging + platform + totalTax;
        const finalRefund = Math.round(refundAmount * 100) / 100;

        console.log(`[REFUND CALC] Order ${orderData.id}:`, {
            itemSubtotal:       itemSubtotal.toFixed(2),
            totalDiscount:      totalDiscount.toFixed(2),
            subtotalAfterDisc:  subtotalAfterDiscount.toFixed(2),
            delivery:           delivery.toFixed(2),
            tip:                tip.toFixed(2),
            packaging:          packaging.toFixed(2),
            platform:           platform.toFixed(2),
            totalTax:           totalTax.toFixed(2),
            finalRefund:        finalRefund.toFixed(2)
        });

        const customerRef = firestore.collection('users').doc(customerId);
        const snap = await customerRef.get();

        if (snap.exists) {
            const data = snap.data();
            const wallet = parseFloat(data.wallet_amount || 0) || 0;
            await customerRef.update({ wallet_amount: wallet + finalRefund });

            const wid = firestore.collection("tmp").doc().id;
            await firestore.collection('wallet').doc(wid).set({
                amount: finalRefund,
                date: admin.firestore.Timestamp.now(),
                id: wid,
                isTopUp: true,
                order_id: orderData.id,
                payment_method: "Wallet",
                payment_status: 'success',
                user_id: customerId,
                transactionUser: 'customer',
                note: 'Order Cancelled - Full refund'
            });

            console.log(`→ Customer ${customerId} refunded ₹${finalRefund.toFixed(2)}`);
        }

        let customerFcm = null;
        let vendorFcm   = null;

        const custSnap = await firestore.collection('users').doc(customerId).get();
        if (custSnap.exists) customerFcm = custSnap.data().fcmToken || null;

        const vendSnap = await firestore.collection('users').doc(orderData.vendor?.author).get();
        if (vendSnap.exists) vendorFcm = vendSnap.data().fcmToken || null;

        // For Order Placed timeout (restaurant never accepted) → only 'restaurant did not accept'
        await sendNotification(vendorFcm, customerFcm, orderData, 'not accepted');

        return finalRefund;

    } catch (err) {
        console.error(`Customer refund error for ${orderData?.id || 'unknown'}:`, err);
        return 0;
    }
}

async function sendNotification(vendorFcm, customerFcm, orderData, type) {
    const shortOrderId = orderData.id.slice(-10);
    const vendorMessage = {
        title: 'Order Cancelled',
        body: type === 'no driver found'
            ? `Order #${shortOrderId} has been cancelled due to no driver available.`
            : `Order #${shortOrderId} has been cancelled due to restaurant did not accept the order.`
    };

    const customerMessage = {
        title: 'Order Cancelled',
        body: type === 'no driver found'
            ? `Order #${shortOrderId} has been cancelled due to no driver available.`
            : `Order #${shortOrderId} has been cancelled due to restaurant did not accept the order.`
    };

    try {
        if (vendorFcm) {
            await messaging.send({ notification: vendorMessage, token: vendorFcm });
            console.log(`[NOTIFY] Vendor sent (${type})`);
        }

        if (customerFcm) {
            await messaging.send({ notification: customerMessage, token: customerFcm });
            console.log(`[NOTIFY] Customer sent (${type})`);
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

autoCancelMultivendorOrderCron();
