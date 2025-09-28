importScripts('https://www.gstatic.com/firebasejs/8.3.2/firebase-app.js');
importScripts('https://www.gstatic.com/firebasejs/8.3.2/firebase-messaging.js');

firebase.initializeApp({
    apiKey: "AIzaSyDhzr85jPExzZs3cpyF9R-xA_h0f783zmA",
    authDomain: "ditokokuid-a2ff4.firebaseapp.com",
    projectId: "ditokokuid-a2ff4",
    storageBucket: "ditokokuid-a2ff4.firebasestorage.app",
    messagingSenderId: "268802509862",
    appId: "1:268802509862:web:8e688673b4b3ff61adaae7",
    measurementId: "G-EHQ1MQ0QNR"
});

const messaging = firebase.messaging();
messaging.setBackgroundMessageHandler(function (payload) {
    return self.registration.showNotification(payload.data.title, {
        body: payload.data.body ? payload.data.body : '',
        icon: payload.data.icon ? payload.data.icon : ''
    });
});