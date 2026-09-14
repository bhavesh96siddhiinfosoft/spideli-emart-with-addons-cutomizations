@extends('layouts.app')
<style>
   #plist {
    overflow: hidden; /* no scrolling here */
}

#chatInbox {
    overflow-y: auto; /* scroll here */
    display: block;   /* make sure UL behaves like a block */
}

</style>
@section('content')
<div class="page-wrapper">
    <div class="container-fluid">
        <div class="row clearfix">
            <div class="col-lg-12">
                <div class="card chat-app">
                    <div id="plist" class="people-list">
                        <ul class="nav nav-pills mb-4" id="chatTabs">
                            <li class="nav-item">
                                <a class="nav-link active" data-type="driver" href="#">{{trans('lang.driver')}}</a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link" data-type="vendor" href="#">{{trans('lang.store')}}</a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link" data-type="customer" href="#">{{trans('lang.customer')}}</a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link" data-type="provider" href="#">{{trans('lang.provider')}}</a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link" data-type="worker" href="#">{{trans('lang.worker')}}</a>
                            </li>
                        </ul>
                        <div class="input-group">
                            <div class="input-group-prepend">
                                <span class="input-group-text"><i class="fa fa-search"></i></span>
                            </div>
                            <input type="text" class="form-control" placeholder="{{trans('lang.search')}}...">
                        </div>
                        <ul class="list-unstyled chat-list" id="chatInbox">                           
                            
                        </ul>
                    </div>
                  
                    <div class="chat">
                        <div class="chat-header clearfix">
                            <div class="row">
                                <div class="col-lg-6">
                                    <div class="chat-about d-flex align-items-center">
                                        <div id="userProfile" class="mr-2"></div>
                                        <h6 class="m-b-0 userName"></h6>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="chat-history" id="chat-box">
                            <ul class="m-b-0"></ul>
                        </div>
                        <div class="chat-message clearfix">
                            <div class="input-group mb-0">
                                <div class="input-group-prepend">
                                    <label for="fileInput" class="input-group-text" style="cursor: pointer;">
                                        <i class="fa fa-file-image-o"></i>
                                    </label>
                                    <input type="file" id="fileInput" accept="image/*,video/*" style="display: none;" />
                                </div>
                                <input type="text" id="messageInput" class="form-control" placeholder="{{trans('lang.type_your_message')}}">
                                <div class="input-group-append">
                                    <button class="btn btn-primary" id="sendBtn"><i class="fa fa-send"></i></button>
                                </div>
                            </div>
                        </div>
                    </div>

                </div>
            </div>
        </div>
    </div>
</div>
@endsection


@section('scripts')
<script>
    
    var database = firebase.firestore();
    var refData = database.collection('chat').where('type', '==', 'adminchat');
    var placeholderImage = "{{ asset('/images/default_user.png') }}";

    var placeholder = database.collection('settings').doc('placeHolderImage');
    placeholder.get().then(async function (snapshotsimage) {
        var placeholderImageData = snapshotsimage.data();
        placeholderImage = placeholderImageData.image;
    });
    
    let activeChatId = null;
    let receiverId = null;
    let userFcm = '';
    let unsubscribeThread = null;
    let activeChatType = null;
    let lastVisible = null;
    let pageSize = 10;
    let activeChatTab = "driver";
    $(document).ready(function () {
        $('#data-table_processing').show();
        loadChatInbox(true);
        $('#plist input[type="text"]').on('input', function () {
            var searchValue = $(this).val().toLowerCase();
            $("#chatInbox li").filter(function () {
                $(this).toggle($(this).text().toLowerCase().indexOf(searchValue) > -1)
            });
        });
    });    


    let inboxUnsub = null;

    async function loadChatInbox(initial = false) {
        
        let query = refData.where("chatType", "==", activeChatTab).orderBy("createdAt", "desc").limit(pageSize);
        
        if (lastVisible && !initial) {
            query = query.startAfter(lastVisible);
        }

        if (initial) {
            // real-time for first page
            if (inboxUnsub) inboxUnsub(); // cleanup old listener

            inboxUnsub = query.onSnapshot(async (snapshot) => {
                if (!snapshot.empty) {
                    lastVisible = snapshot.docs[snapshot.docs.length - 1];
                    const chatListHTML = await renderInbox(snapshot);
                    $("#chatInbox").html(chatListHTML);

                    if (!activeChatId && $("#chatInbox li").length > 0) {
                        $("#chatInbox li").first().trigger("click");
                    }
                }
                $('#data-table_processing').hide();
            });
        } else {
            // static load for next pages
            const snapshot = await query.get();
            if (!snapshot.empty) {
                lastVisible = snapshot.docs[snapshot.docs.length - 1];
                const chatListHTML = await renderInbox(snapshot);
                $("#chatInbox").append(chatListHTML);
            }
        }
    }

    async function renderInbox(snapshot) {
        let chatListHTML = '';
        await Promise.all(snapshot.docs.map(async (doc) => {
            const data = doc.data();
            const threadSnap = await database.collection("chat")
                .doc(doc.id)
                .collection("thread")
                .orderBy("createdAt", "desc")
                .limit(1)
                .get();

            if (!threadSnap.empty) {
                const lastMsg = threadSnap.docs[0].data();
                let userId = lastMsg.senderId === "admin" ? lastMsg.receiverId : lastMsg.senderId;
                if (!userId) return;
                const userData = await getUserName(userId, data.chatType);
                if (!userData?.userName) return;

                const unreadCount = await countUnreadMessages(userId);

                chatListHTML += buildInboxHTML({
                    userId,
                    userName: userData.userName,
                    profilePictureURL: userData.profilePictureURL || placeholderImage,
                    message: lastMsg.messageType === 'text' ? lastMsg.message : `[${lastMsg.messageType}]`,
                    time: lastMsg.createdAt?.toDate()
                        ? lastMsg.createdAt.toDate().toLocaleTimeString('en-US', {hour: '2-digit', minute: '2-digit'})
                        : '',
                    unreadCount,
                    type: data.chatType
                });

                // real-time badge listener
                listenToUnreadMessages(userId, (count) => {
                    const badge = document.querySelector(`.unread-${userId}`);
                    if (badge) {
                        badge.innerText = count > 0 ? count : '';
                        badge.style.display = count > 0 ? 'inline-block' : 'none';
                    }
                });
                listenToChatUpdates(userId);

            }
        }));

        return chatListHTML;
    }

   
    $(window).on('scroll', function () {
        if ($(window).scrollTop() + $(window).height() >= $(document).height() - 20) {
            loadChatInbox(false);
        }
    });


    async function getUserName(id, type) {
        var userName = '';
        var profilePictureURL = '';
        var snap;
        if (type !== "worker") {
            snap = await database.collection('users').doc(id).get();
        }else{
            snap = await database.collection('providers_workers').doc(id).get();
        }
        if (snap.exists) {
            userName = snap.data().firstName + ' ' + snap.data().lastName;
            profilePictureURL = snap.data().profilePictureURL;
        }
        return { userName, profilePictureURL };
    }

    async function countUnreadMessages(userId) {
        const snapshot = await database.collection('chat').doc(userId).collection("thread")
            .where("seen", "==", false)
            .where("senderId", "!=", "admin")
            .get();
        return snapshot.size;
    }

    function listenToUnreadMessages(userId, callback) {
        return database.collection('chat').doc(userId).collection("thread")
            .where("seen", "==", false)
            .where("senderId", "!=", "admin")
            .onSnapshot(snapshot => callback(snapshot.size));
    }

    function buildInboxHTML(chat) {
        return `
        <li class="clearfix" data-id="${chat.userId}">
            <img src="${chat.profilePictureURL}" alt="avatar">
            <div class="about">
                <div class="name">${chat.userName} 
                    ${chat.unreadCount > 0 ? `<span class="badge badge-danger unread-${chat.userId}">${chat.unreadCount}</span>` : `<span class="badge badge-danger unread-${chat.userId}" style="display:none"></span>`}
                </div>
                <div class="status">${chat.message} <small class="text-muted ml-2">${chat.time}</small></div>
            </div>
        </li>`;
    }

    $(document).on("click", "#chatInbox li", async function () {
        activeChatId = $(this).data("id");
        $(".chat-list li").removeClass("active");
        $(this).addClass("active");
       
        $('#data-table_processing').show();
        const chatDoc = await database.collection("chat").doc(activeChatId).get();
        if (!chatDoc.exists) return;

        const chatData = chatDoc.data();
        activeChatType = chatData.chatType || "customer"; 
        
        let userSnap;  
        var profileRoute="";  
        if (activeChatType !== "worker") {   
            userSnap = await database.collection("users").doc(activeChatId).get();     
        }else{
            userSnap = await database.collection("providers_workers").doc(activeChatId).get();     
        }
        if (userSnap.exists) {
            const userData = userSnap.data();
            if (activeChatType === "customer") {
                profileRoute = "{{ route('users.view', ':id') }}";
                profileRoute = profileRoute.replace(":id", userData.id);
            } else if (activeChatType === "driver") {
                profileRoute = "{{ route('drivers.view', ':id') }}";
                profileRoute = profileRoute.replace(":id", userData.id);
            } else if(activeChatType === "vendor") {
                if(userData.vendorId){
                    profileRoute = "{{ route('stores.view', ':id') }}";
                    profileRoute = profileRoute.replace(":id", userData.vendorId);
                }
            }else if(activeChatType === "provider"){
                profileRoute = "{{ route('providers.view', ':id') }}";
                profileRoute = profileRoute.replace(":id", userData.id);
            }else{
                profileRoute="";
            }

            $('#userProfile').html(
                `<img src="${userData.profilePictureURL || placeholderImage}" style="max-width: 50px;">`
            );
            $(".userName").html(`
                <a href="${profileRoute}" class="">
                    ${userData.firstName || ''} ${userData.lastName || ''}
                </a>
            `);
            receiverId = activeChatId; 
            userFcm = userData.fcmToken || '';
        }
       
        $("#chat-box ul").empty();
        if (unsubscribeThread) unsubscribeThread();

        unsubscribeThread = database.collection("chat")
        .doc(activeChatId)
        .collection("thread")
        .orderBy("createdAt")
        .onSnapshot(snapshot => {
            const chatBox = $("#chat-box ul");
            chatBox.empty();
            snapshot.forEach(doc => {
                const data = doc.data();
                renderMessage(data, chatBox);
                if (data.senderId !== "admin" && !data.seen) {
                    doc.ref.update({ seen: true });
                }
            });
            chatBox.parent().scrollTop(chatBox.parent()[0].scrollHeight);
            $('#data-table_processing').hide();
        });
    });

    let lastMessageDate = null;

    function renderMessage(data, chatBox) {
        const isAdmin = data.senderId === "admin";
        let messageContent = "";
       

        if (data.messageType === "text") {
            messageContent = data.message;
        } else if (data.messageType === "image" && data.url?.url) {
            messageContent = `<a href="${data.url.url}" target="_blank">
                <img src="${data.url.url}" style="max-width:100px;border-radius:8px;" />
            </a>`;
        } else if (data.messageType === "video" && data.url?.url) {
            messageContent = `<video controls style="max-width:150px;border-radius:8px;">
                <source src="${data.url.url}" type="video/mp4">
            </video>`;
        }else if(data.messageType === "voice" && data.url?.url){
            messageContent = `<audio controls>
                <source src="${data.url.url}" type="${data.url.mime}">
            </audio>`;
        }
         else if (data.messageType === "file" && data.url?.url) {
            messageContent = `<a href="${data.url.url}" target="_blank">
                <i class="fa fa-file"></i> Download File
            </a>`;
        } else {
            messageContent = "[Unsupported message type]";
        }

        let timeText = "";
        let currentDateStr = "";

        if (data.createdAt?.toDate) {
            const dateObj = data.createdAt.toDate();

            const day = String(dateObj.getDate()).padStart(2, "0");
            const month = dateObj.toLocaleString("en-US", { month: "short" });
            const year = dateObj.getFullYear();

            let hours = dateObj.getHours();
            const minutes = String(dateObj.getMinutes()).padStart(2, "0");
            const ampm = hours >= 12 ? "PM" : "AM";
            hours = hours % 12 || 12;

            currentDateStr = `${day} ${month} ${year}`;
            timeText = `<span class="message-data-time">${hours}:${minutes} ${ampm}</span>`;
        }

        // Add date separator if day changed
        if (currentDateStr && currentDateStr !== lastMessageDate) {
            chatBox.append(`
                <li class="date-separator text-center">
                    <span class="badge badge-light">${currentDateStr}</span>
                </li>
            `);
            lastMessageDate = currentDateStr;
        }

        const html = `
            <li class="clearfix">
                <div class="message ${isAdmin ? "other-message float-right" : "my-message"}">
                    ${messageContent}
                    ${timeText}
                </div>
                <div class="message-data ${isAdmin ? "text-right float-right w-100" : ""}">
                    ${
                        isAdmin
                            ? `<div class="text-right check-sign">
                                <i class="fa ${data.seen ? "fa-check-double" : "fa-check"}"></i>
                            </div>`
                            : ""
                    }
                </div>
            </li>
        `;
        chatBox.append(html);
    }

    $("#sendBtn").on("click", sendMessage);
    $("#messageInput").on("keydown", e => {
        if (e.key === "Enter") {
            e.preventDefault();
            sendMessage();
        }
    });

    async function sendMessage() {
        const message = $("#messageInput").val().trim();
        if (!message || !activeChatId) return;

        const chatDocRef = database.collection("chat").doc(activeChatId);

        const msgData = {
            id: database.collection("tmp").doc().id,
            message,
            senderId: "admin",
            receiverId,
            messageType: "text",
            url: null,
            seen: false,
            createdAt: firebase.firestore.FieldValue.serverTimestamp()
        };

        await chatDocRef.collection("thread").add(msgData);

        const dataToSet = {
            lastMessage: message,
            lastSenderId: "admin",
            receiverId,
            senderId: "admin",
            lastMessageType: "text",
            createdAt: firebase.firestore.FieldValue.serverTimestamp(),
            chatType: activeChatType,
            type: "adminchat",
            sender_receiver_id: ["admin", receiverId]
        };

        await chatDocRef.set(dataToSet, { merge: true });
       
        try {
            const title = '{{ trans('lang.new_message_from_admin') }}';
            const body = '{{ trans('lang.you_have_received_new_message_from_admin') }}';
            const fcmtoken = userFcm;
            const data = {
                type: 'admin_chat',
                driverId: receiverId
            };

            const sent = await sendNotification(fcmtoken, title, body, data);
            if (sent) {
                console.log('notification sent');
            }
        } catch (err) {
            console.error("Error sending notification:", err);
        }

        $("#messageInput").val("");
    }

    document.getElementById('fileInput').addEventListener('change', async function (e) {
        jQuery("#overlay").show();
        const file = e.target.files[0];
        if (!file || !activeChatId) return;

        const storageRef = firebase.storage().ref();
        const filePath = `chat_uploads/${Date.now()}_${file.name}`;
        const uploadTask = storageRef.child(filePath).put(file);

        uploadTask.on(
            'state_changed',
            null,
            function (error) {
                console.error("Upload failed:", error);
                jQuery("#overlay").hide();
            },
            async function () {
                const downloadURL = await uploadTask.snapshot.ref.getDownloadURL();
                const mimeType = file.type;
                // const messageType = mimeType.startsWith("image")
                //     ? "image"
                //     : mimeType.startsWith("video")
                //     ? "video"
                //     : "file";
                let messageType;
                if (mimeType.startsWith("image") ) {
                    messageType = "image";
                } else if (mimeType.startsWith("video")) {
                    messageType = "video";
                } else {
                    messageType = "file";
                }

                let conversationMessage = "sent a file";
                if (mimeType.includes("image")) conversationMessage = "sent an image";
                if (mimeType.includes("video")) conversationMessage = "sent a video";
                if (mimeType.includes("audio")) conversationMessage = "sent a voice message";

                const messageId = database.collection("tmp").doc().id;
                const messageData = {
                    id: messageId,
                    message: conversationMessage,
                    senderId: "admin",
                    receiverId,
                    messageType,
                    seen: false,
                    createdAt: firebase.firestore.FieldValue.serverTimestamp(),
                    url: {
                        mime: mimeType,
                        url: downloadURL
                    }
                };

                // Handle video thumbnail
                if (messageType === "video") {
                    const thumbnailBlob = await generateVideoThumbnail(file);
                    const thumbPath = `chat_uploads/thumbnails/${Date.now()}_thumb.jpg`;
                    const thumbSnapshot = await storageRef.child(thumbPath).put(thumbnailBlob);
                    const thumbnailURL = await thumbSnapshot.ref.getDownloadURL();
                    messageData.videoThumbnail = thumbnailURL;
                }

                // Save message in thread
                const chatDocRef = database.collection("chat").doc(activeChatId);
                await chatDocRef.collection("thread").add(messageData);

                // Update parent chat document
                const dataToSet = {
                    lastMessage: conversationMessage,
                    lastSenderId: "admin",
                    receiverId,
                    senderId: "admin",
                    lastMessageType: messageType,
                    createdAt: firebase.firestore.FieldValue.serverTimestamp(),
                    chatType: activeChatType,
                    type: "adminchat",
                    sender_receiver_id: ["admin", receiverId]
                };

                await chatDocRef.set(dataToSet, { merge: true });
              
                try {
                    const title = '{{ trans('lang.new_message_from_admin') }}';
                    const body = '{{ trans('lang.you_have_received_new_message_from_admin') }}';
                    const fcmtoken = userFcm; // must be available globally
                    const data = {
                        type: 'admin_chat',
                        driverId: receiverId
                    };

                    const sent = await sendNotification(fcmtoken, title, body, data);
                    if (sent) console.log('notification sent');
                } catch (err) {
                    console.error("Error sending notification:", err);
                }

                jQuery("#overlay").hide();
                $("#fileInput").val(""); // reset input so same file can be re-uploaded
            }
        );
    });
    async function generateVideoThumbnail(videoFile) {
        return new Promise((resolve, reject) => {
            const video = document.createElement('video');
            video.src = URL.createObjectURL(videoFile);
            video.crossOrigin = "anonymous";
            video.muted = true;
            video.playsInline = true;

            video.addEventListener('loadeddata', () => {
                // Ensure the video has enough data
                video.currentTime = 1;
            });

            video.addEventListener('seeked', () => {
                const canvas = document.createElement('canvas');
                canvas.width = video.videoWidth;
                canvas.height = video.videoHeight;
                const ctx = canvas.getContext('2d');
                ctx.drawImage(video, 0, 0, canvas.width, canvas.height);

                canvas.toBlob(blob => {
                    if (blob) resolve(blob);
                    else reject(new Error("{{trans('lang.thumbnail_generation_failed')}}"));
                }, 'image/jpeg', 0.75);
            });

            video.addEventListener('error', (e) => {
                reject(e);
            });
        });
    }
    function listenToChatUpdates(userId) {
        database.collection("chat").doc(userId).onSnapshot((doc) => {
            if (!doc.exists) return;
            const data = doc.data();

            // Update last message + time
            const chatLi = $(`#chatInbox li[data-id='${userId}']`);
            if (chatLi.length) {
                const message =
                    data.lastMessageType === "text"
                        ? data.lastMessage
                        : `[${data.lastMessageType}]`;

                const time = data.createdAt?.toDate
                    ? data.createdAt.toDate().toLocaleTimeString("en-US", {
                        hour: "2-digit",
                        minute: "2-digit",
                    })
                    : "";

                chatLi.find(".status").html(
                    `${message} <small class="text-muted ml-2">${time}</small>`
                );

                // Store latest time as data attribute for easy sorting
                const timestamp = data.createdAt?.toMillis ? data.createdAt.toMillis() : 0;
                chatLi.attr("data-time", timestamp);

                // Reorder the list by latest message time
                const chatList = $("#chatInbox li").get();
                chatList.sort((a, b) => Number($(b).attr("data-time")) - Number($(a).attr("data-time")));
                $("#chatInbox").html(chatList);
            }
        });
    }

    $("#chatTabs .nav-link").click(function (e) {
        e.preventDefault();
        $("#chatTabs .nav-link").removeClass("active");
        $(this).addClass("active");
        activeChatTab = $(this).data("type");
        lastVisible = null;
        activeChatId = null;          
        if (inboxUnsub) inboxUnsub();
        $("#chatInbox").empty();
        $('#data-table_processing').show();
        loadChatInbox(true); 
    });

</script>
@endsection
