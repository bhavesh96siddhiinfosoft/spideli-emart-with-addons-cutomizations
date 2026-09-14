const admin = require ("firebase-admin");
const serviceAccount = require('./credentials.json'); // Adjust the path if needed
const projectId = serviceAccount.project_id;

admin.initializeApp({
    credential: admin.credential.cert(serviceAccount),
    databaseURL: "https://${projectId}-default-rtdb.firebaseio.com" // Replace with your database URL
});

const db = admin.firestore();
async function autoCancelOnDemandOrderCron() {
    await cancelOldOrders();
}

async function cancelOldOrders() {
    try {
        const ordersRef = db.collection('provider_orders');
        const snapshot = await ordersRef.where('newScheduleDateTime', '<', admin.firestore.Timestamp.now()).where('status', 'in', ['Order Accepted','Order Assigned']).get();
 
        snapshot.forEach(async (doc) => {
            var orderData=doc.data();
            var userId=orderData.authorID;
            var orderId=orderData.id;
            var platformFee = parseFloat(orderData.platformFee || 0);

            /* Calculate refund Amount */
            var price=orderData.provider.price;
            if (orderData.provider.hasOwnProperty('disPrice') && orderData.provider.disPrice !== '0') {
                price = orderData.provider.disPrice;
            }
            var subTotal=0;
            var orderDiscount=0;
            subTotal=subTotal + (parseFloat(price) * parseFloat(orderData.quantity));
            if (orderData.hasOwnProperty('discount') && orderData.discount) {
                orderDiscount = orderData.discount;
            }
            subTotal = parseFloat(subTotal) - parseFloat(orderDiscount);

            if (orderData.adminCommission !== '' && orderData.adminCommission !== null && orderData.adminCommissionType !=='' && orderData.adminCommissionType !== null) {
                var adminCommision = 0;
                if(orderData.adminCommissionType === "percentage"){
                    adminCommision= parseFloat(parseFloat(subTotal) * parseFloat(orderData.adminCommission) / 100);
                }else{
                    adminCommision=parseFloat(orderData.adminCommission);
                }
            }

            let total_tax_amount = 0;
            (orderData.taxSetting || []).forEach(tax => {
                if (tax.enable) {
                    let taxAmount = 0;
                    if (tax.type === "percentage") {
                        taxAmount = (tax.tax / 100) * subtotal;
                    } else {
                        taxAmount = tax.tax;
                    }
                    total_tax_amount += parseFloat(taxAmount);
                }
            });
            
            // Platform taxes
            let otherCharges = [
                {key: 'platform', amount: platformFee, taxes: orderData.platformTax || []},
            ];

            otherCharges.forEach(scope => {
                scope.taxes?.forEach(tax => {
                    if (tax.enable) {
                        let taxAmount = 0;
                        if(scope.amount > 0){
                            if (tax.type === "percentage") {
                                taxAmount = (tax.tax / 100) * scope.amount;
                            } else {
                                taxAmount = tax.tax;
                            }
                        }
                        total_tax_amount += parseFloat(taxAmount);
                    }
                });
            });

            let finalTotal = parseFloat(subTotal) + platformFee + parseFloat(total_tax_amount);

            /* Calculate refund Amount Ends */
            if(orderData.provider.priceUnit !== 'Hourly' && orderData.paymentStatus === true) {
                await refundAmountToWallet(userId, finalTotal,orderId,orderData.payment_method,adminCommision,orderData.provider.author);
            }

            doc.ref.update({status: "Order Cancelled"})

            console.log('Old order id = #'+orderData.id+'  cancelled successfully.');
        });
    

        console.log('Old orders cancelled successfully.');

    } catch (error) {

        console.error('Error cancelling old orders:', error);
    }
}

async function refundAmountToWallet(userId, refundAmount,orderId,payment_method,adminCommision,providerId){
    
    const userRef= await db.collection('users').where('id','==',userId).get();
    
    if(userRef.size > 0) {

        const userData = userRef.docs[0].data();
        userWallet=0;
		
        if(payment_method !=='cod') {
            if (userData.wallet_amount !== null && userData.wallet_amount !== '' && !isNaN(userData.wallet_amount)) {
                userWallet = userData.wallet_amount;
            }

            newWalletAmount = parseFloat(userWallet) + parseFloat(refundAmount)
            db.collection('users').doc(userId).update({'wallet_amount': newWalletAmount}).then(async function (result) {
                var walletId = db.collection("tmp").doc().id;
                await db.collection('wallet').doc(walletId).set({
                    'amount': parseFloat(refundAmount),
                    'date':admin.firestore.Timestamp.now(),
                    'id': walletId,
                    'isTopUp': true,
                    'order_id': orderId,
                    'payment_method': "Wallet",
                    'payment_status': 'success',
                    'serviceType': 'ondemand-service',
                    'user_id': userId,
                    'transactionUser':'customer',
                    'note':'Order amount refund'

                }).then(async function(result) {
                    var walletId = db.collection("tmp").doc().id;
                    var providerAmount=parseFloat(refundAmount)-parseFloat(adminCommision);
                    await db.collection('wallet').doc(walletId).set({
                        'amount': parseFloat(refundAmount),
                        'date': admin.firestore.Timestamp.now(),
                        'id': walletId,
                        'isTopUp': false,
                        'order_id': orderId,
                        'payment_method': "Wallet",
                        'payment_status': 'success',
                        'serviceType': 'ondemand-service',
                        'user_id': providerId,
                        'transactionUser': 'provider',
                        'note': 'Order amount debited'
                    }).then(async function(result) {
                         const providerRef= await db.collection('users').where('id','==',providerId).get();
                        if(providerRef.size > 0) {
                             const providerData = providerRef.docs[0].data();
                             userWallet=0;
                             if (providerData.wallet_amount !== null && providerData.wallet_amount !== '' && !isNaN(providerData.wallet_amount)) {
                                userWallet = providerData.wallet_amount;
                            }
                            newWalletAmount = parseFloat(userWallet) - parseFloat(providerAmount)
                            db.collection('users').doc(providerId).update({'wallet_amount': newWalletAmount}).then(async function (result) {
                                return null
                            }).catch(error => {
                                console.log(error)
                            })
                        }
                        await refundAdminCommision(adminCommision,providerId,orderId);
                        return null
                    })
                    .catch(error => {
                        console.log(error)
                    })
                    return null
                })
                .catch(error => {
                    console.log(error)
                })
                return null
            })
            .catch(error => {
                console.log(error)
            })
            return null
        }else{
            await refundAdminCommision(adminCommision,providerId,orderId);
        }
    }
}

async function refundAdminCommision(adminCommision,providerId,orderId){
    var walletId = db.collection("tmp").doc().id;
    await db.collection('wallet').doc(walletId).set({
        'amount': parseFloat(adminCommision),
        'date': admin.firestore.Timestamp.now(),
        'id': walletId,
        'isTopUp': true,
        'order_id': orderId,
        'payment_method': "Wallet",
        'payment_status': 'success',
        'serviceType': 'ondemand-service',
        'user_id': providerId,
        'transactionUser': 'provider',
        'note': 'Admin commission refund'
    });
    return;
}

autoCancelOnDemandOrderCron();