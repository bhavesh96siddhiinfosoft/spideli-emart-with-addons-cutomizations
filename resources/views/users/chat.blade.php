@extends('layouts.app')
@section('content')
    <link href="https://maxcdn.bootstrapcdn.com/font-awesome/4.7.0/css/font-awesome.min.css" rel="stylesheet" />
    <div class="page-wrapper">
        <div class="container-fluid">
            <div class="card-body pb-2 pt-2 px-0">
                <div class="row clearfix">
                    <div class="col-lg-12">
                        <div class="card">

                            <div class="chat">
                                <div class="chat-header clearfix">
                                    <div class="row">
                                        <div class="col-lg-6">
                                            <div class="chat-about">
                                                <div class="d-flex align-items-center">
                                                    <div id="userProfile" class="profile-image"></div>
                                                    <h5 class="m-b-0 userName ml-2"></h5>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="chat-history" id="chat-box">
                                    <ul class="m-b-0">
                                    </ul>
                                </div>
                                <div class="chat-message clearfix">
                                    <div class="input-group mb-0">
                                        <!-- File input trigger -->
                                        <div class="input-group-prepend">
                                            <label for="fileInput" class="input-group-text" style="cursor: pointer;">
                                                <i class="fa fa-file-image-o"></i>
                                            </label>
                                            <input type="file" id="fileInput" accept="image/*,video/*" style="display: none;" />
                                        </div>

                                        <!-- Message input field -->
                                        <input type="text" id="messageInput" class="form-control" placeholder="{{trans('lang.type_your_message')}}" />

                                        <!-- Send button -->
                                        <div class="input-group-append">
                                            <button class="btn btn-primary" onclick="sendMessage()">
                                                <i class="fa fa-send"></i>
                                            </button>
                                        </div>
                                    </div>
                                </div>

                            </div>
                        </div>
                    </div>
                </div>
                 <div class="form-group col-12 text-center btm-btn">
                    <a href="{!! route('users.support') !!}" class="btn btn-default"><i class="fa fa-undo"></i>{{ trans('lang.cancel') }}</a>
                </div>
            </div>
        </div>
    </div>
@endsection

@section('scripts')
    <script src="https://cdnjs.cloudflare.com/ajax/libs/printThis/1.15.0/printThis.js"></script>
    <script>
        var id = "{{ $id }}";
        var senderId = "admin";
        var receiverId = '';
        var database = firebase.firestore();
        var defaultUser = "{{ asset('images/default_user.png') }}"
        var userFcm = '';
        database.collection('users').doc(id).get().then(async function(userSnapshot) {
            if (userSnapshot.exists) {
                var userData = userSnapshot.data();
                if (userData.profilePic != null && userData.profilePic != '') {
                    $('#userProfile').html('<img src="' + userData.profilePic + '" style="max-width: 50px;">')
                } else {
                    $('#userProfile').html('<img src="' + defaultUser + '" style="max-width: 50px;">')

                }
                if (userData.hasOwnProperty('fcmToken') && userData.fcmToken != null && userData.fcmToken != '') {
                    userFcm = userData.fcmToken;
                }
                $('.userName').html(userData.firstName + ' ' + userData.lastName);
                receiverId = userData.id;
            }
        })

        var threadRef = database.collection('chat').doc(id).collection("thread").orderBy("createdAt");
     
        threadRef.onSnapshot(snapshot => {
            const chatBox = document.querySelector("#chat-box ul");
            chatBox.innerHTML = '';
            lastMessageDate = null; 

            snapshot.forEach(doc => {
                const data = doc.data();
                if (data.senderId !== "admin" && !data.seen) {
                    doc.ref.update({ seen: true });
                }

                const isAdmin = data.senderId === "admin";
                let messageContent = "";
                
                if (data.messageType === "text") {
                    messageContent = data.message;
                } else if (data.messageType === "image" && data.url?.url) {
                    messageContent = `
                        <a href="${data.url.url}" data-lightbox="chat-images">
                            <img src="${data.url.url}" alt="Image" style="max-width:100px;border-radius:8px;" />
                        </a>`;
                } else if (data.messageType === "video" && data.url?.url) {
                    messageContent = `
                        <video controls style="max-width:150px;border-radius:8px;">
                            <source src="${data.url.url}" type="${data.url.mime}">
                        </video>`;
                }
            
                let timeText = "";
                let currentDateStr = "";

                if (data.createdAt?.toDate) {
                    const dateObj = data.createdAt.toDate();
                    
                    const day = String(dateObj.getDate()).padStart(2, "0");
                    const month = dateObj.toLocaleString("en-US", { month: "short" });
                    const year = dateObj.getFullYear();
                    currentDateStr = `${day} ${month} ${year}`;
                    
                    let hours = dateObj.getHours();
                    const minutes = String(dateObj.getMinutes()).padStart(2, "0");
                    const ampm = hours >= 12 ? "PM" : "AM";
                    hours = hours % 12 || 12;
                    timeText = `${hours}:${minutes} ${ampm}`;
                }

                if (currentDateStr && currentDateStr !== lastMessageDate) {
                    chatBox.insertAdjacentHTML("beforeend", `
                        <li class="date-separator text-center">
                            <span class="badge badge-light">${currentDateStr}</span>
                        </li>
                    `);
                    lastMessageDate = currentDateStr;
                }

                const messageHtml = `
                <li class="clearfix">
                    <div class="message ${isAdmin ? "other-message float-right" : "my-message"}">
                        ${messageContent}
                        <span class="message-data-time">${timeText}</span>
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


                chatBox.insertAdjacentHTML("beforeend", messageHtml);
            });
            chatBox.parentElement.scrollTop = chatBox.parentElement.scrollHeight;
        });

        async function sendMessage() {
            const message = document.getElementById("messageInput").value;
            if (!message) return;

            database.collection("chat").doc(id).collection("thread").add({
                id: database.collection("tmp").doc().id,
                message: message,
                senderId: senderId,
                receiverId: receiverId,
                messageType: "text",
                url: null,
                videoThumbnail: "",
                seen: false,
                createdAt: firebase.firestore.FieldValue.serverTimestamp(),
            });

            // Update last message in main chat doc

            const chatDocRef = database.collection("chat").doc(id);

            chatDocRef.get().then(async (doc) => {
                const dataToSet = {
                    lastMessage: message,
                    lastSenderId: "admin",
                    receiverId: receiverId,
                    senderId: "admin",
                    lastMessageType : "text",
                    createdAt: firebase.firestore.FieldValue.serverTimestamp(),
                    sender_receiver_id: ["admin", receiverId] 
                };

                if (!doc.exists) {

                    const userDoc = await database.collection('users').doc(id).get();
                    const userData = userDoc.data();

                    Object.assign(dataToSet, {
                        'receiverId': id,
                        'senderId': "admin",                      
                        'lastSenderId' : "admin",
                        'createdAt': firebase.firestore.FieldValue.serverTimestamp(),
                        'lastMessage': message,
                        'chatType' : "customer",
                        'type': "adminchat",
                        'lastMessageType' : "text",
                        sender_receiver_id: ["admin", id] 
                    });
                }

                // Create or merge fields
                chatDocRef.set(dataToSet, {
                    merge: true
                }).then(async () => {
                    var title = '{{ trans('lang.new_message_from_admin') }}';
                    var body = '{{ trans('lang.you_have_received_new_message_from_admin') }}';
                    var fcmtoken = userFcm;
                    var data = {
                        'type': 'admin_chat',
                        'userId': receiverId
                    }
                    var sent = await sendNotification(fcmtoken, title, body, data);
                    if (sent) {
                        console.log('notification sent');
                    }
                });
            });

            document.getElementById("messageInput").value = '';
        }
        document.getElementById('fileInput').addEventListener('change', async function(e) {

            jQuery("#overlay").show();
            const file = e.target.files[0];
            if (!file) return;

            const storageRef = firebase.storage().ref();
            const filePath = `chat_uploads/${Date.now()}_${file.name}`;
            const uploadTask = storageRef.child(filePath).put(file);

            uploadTask.on('state_changed', null,
                function(error) {
                    console.error("Upload failed:", error);
                },
                async function() {
                    const downloadURL = await uploadTask.snapshot.ref.getDownloadURL();
                    const mimeType = file.type;
                    const messageType = mimeType.startsWith("image") ? "image" : "video";

                    const senderId = "admin";
                    const messageId = database.collection("tmp").doc().id;
                    var conversationMessage = '';
                    if (mimeType.includes("image")) {
                        conversationMessage = "sent an image";
                    } else if (mimeType.includes("video")) {
                        conversationMessage = "sent a video";
                    } else if (mimeType.includes("audio")) {
                        conversationMessage = "sent a voice message";
                    }                

                    let messageData = {
                        message: conversationMessage,
                        messageType: messageType,
                        senderId: senderId,
                        receiverId: receiverId,
                        createdAt: firebase.firestore.FieldValue.serverTimestamp(),
                        id: messageId,
                        seen: false,
                        url: {
                            mime: mimeType,
                            url: downloadURL
                        }
                    };

                    if (messageType === "video") {
                        const thumbnailBlob = await generateVideoThumbnail(file);
                        const thumbPath = `chat_uploads/thumbnails/${Date.now()}_thumb.jpg`;
                        const thumbSnapshot = await storageRef.child(thumbPath).put(thumbnailBlob);
                        const thumbnailURL = await thumbSnapshot.ref.getDownloadURL();
                        messageData.videoThumbnail = thumbnailURL;
                    }

                    // Save message
                    await database.collection("chat").doc(id).collection("thread").add(messageData);

                    // Handle chat doc creation or update
                    const chatDocRef = database.collection("chat").doc(id);
                    const doc = await chatDocRef.get();

                    const dataToSet = {
                        lastMessage: conversationMessage,
                        lastSenderId: "admin",
                        createdAt: firebase.firestore.FieldValue.serverTimestamp(),                      
                        receiverId: receiverId,
                        senderId: "admin",
                        lastMessageType : messageType,
                        sender_receiver_id: ["admin", receiverId] 
                    };

                    if (!doc.exists) {
                        try {
                            const userDoc = await database.collection("users").doc(id).get();
                            const userData = userDoc.data();
                            console.log(mimeType);
                            if (userData) {
                                Object.assign(dataToSet, {                                  
                                    'receiverId': id,
                                    'senderId': "admin",                      
                                    'lastSenderId' : "admin",
                                    'createdAt': firebase.firestore.FieldValue.serverTimestamp(),
                                    'lastMessage': conversationMessage,
                                    'chatType' : "customer",
                                    'type': "adminchat",
                                    'lastMessageType' : messageType,
                                    sender_receiver_id: ["admin", id] 
                                });
                            } else {
                                console.warn("data not found for id:", userData.id);
                            }

                        } catch (err) {
                            console.error("Error while fetching  data:", err);
                        }
                    }

                    await chatDocRef.set(dataToSet, {
                        merge: true
                    }).then(async () => {
                        var title = '{{ trans('lang.new_message_from_admin') }}';
                        var body = '{{ trans('lang.you_have_received_new_message_from_admin') }}';
                        var fcmtoken = userFcm;
                        var data = {
                            'type': 'admin_chat',
                            'userId': receiverId
                        }
                        var sent = await sendNotification(fcmtoken, title, body, data);
                        if (sent) {
                            console.log('notification sent');
                        }

                    });
                    jQuery("#overlay").hide();
                }
            );

        });

        document.getElementById("messageInput").addEventListener("keydown", function(event) {
            if (event.key === "Enter") {
                event.preventDefault();
                sendMessage();
            }
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
                        else reject(new Error("Thumbnail generation failed"));
                    }, 'image/jpeg', 0.75);
                });

                video.addEventListener('error', (e) => {
                    reject(e);
                });
            });
        }
   
    </script>
@endsection
