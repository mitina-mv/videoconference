<script setup>
import { Head, Link } from '@inertiajs/vue3';
import { OpenVidu } from 'openvidu-browser';
import { ref, onMounted } from 'vue';

defineProps({
    canLogin: {
        type: Boolean,
    },
    canRegister: {
        type: Boolean,
    },
    laravelVersion: {
        type: String,
        required: true,
    },
    phpVersion: {
        type: String,
        required: true,
    },
});

const mySessionId = 'ses_KnGw9s04cI'
const myUserName = "Participant" + Math.floor(Math.random() * 100)
const myConnectionId = "con_H6uS6TJ2xi"
const mytoken = "wss://conference.otisnth.ru:443?sessionId=ses_GOzynfU5WH&token=tok_P8vNLqP9BnW78G6m&secret="

const OV = new OpenVidu();
const session = ref(null);
const publisher = ref(null);

// Инициализация OpenVidu сессии
const initSession = () => {
    console.log("we in func initSession");
    session.value = OV.initSession();
    session.value.connect(mytoken, { clientData: myUserName } )
        .then(() => {
            console.log("Connected to session");

            // Получение видеопотока с веб-камеры
            publisher.value = OV.initPublisher(undefined, {
                videoSource: undefined,
                audioSource: undefined,
                publishAudio: true,
                publishVideo: true,
                mirror: true
            });

            console.log(publisher.value);

            // Отображение видеопотока
            publisher.value.once('accessAllowed', () => {
                const videoElement = document.createElement('video');
                videoElement.autoplay = true;
                videoElement.muted = true; // Можно отключить звук во избежание эха
                videoElement.srcObject = publisher.value.stream.getMediaStream();
                document.getElementById('video-container').appendChild(videoElement);
            });

            // Отображение видеопотока
            session.value.publish(publisher.value);
        })
        .catch(error => {
            console.log("we have error");
            console.error("Error connecting to session:", error);
        });
};

// Публикация видеопотока
const publishVideo = () => {
    session.value.publish(publisher.value);
};

onMounted(() => {
    initSession();
});

/* // Создание сессии
fetch('/create-session', {
    method: 'POST',
    headers: {
        'Content-Type': 'application/json',
        // Другие заголовки, если необходимо
    },
    // Тело запроса, если необходимо передать данные
    body: JSON.stringify({
        // Данные, если необходимо
    })
})
.then(response => response.json())
.then(data => {
    // Обработка успешного ответа
    console.log('Session created:', data);

    // let sessionId = 'ses_Xi6VhOjbFi';
    // "con_YAqGEegcch"
    // Подключение к сессии
    fetch(`/сonnect/${data.sessionId}`, {
        method: 'GET',
        headers: {
            'Content-Type': 'application/json',
            // Другие заголовки, если необходимо
        },
    })
    .then(response => response.json())
    .then(connectData => {
        // Обработка успешного подключения
        console.log('Connected to session:', connectData);
    })
    .catch(error => {
        // Обработка ошибки подключения
        console.error('Connection error:', error);
    });
})
.catch(error => {
    // Обработка ошибки создания сессии
    console.error('Session creation error:', error);
}); */
</script>

<template>
    <Head title="Welcome" />

    <div
        class="relative sm:flex sm:justify-center sm:items-center min-h-screen bg-dots-darker bg-center bg-gray-100 dark:bg-dots-lighter dark:bg-gray-900 selection:bg-red-500 selection:text-white"
    >
        <div id="video-container"></div>
    
    </div>
</template>

<style>
.bg-dots-darker {
    background-image: url("data:image/svg+xml,%3Csvg width='30' height='30' viewBox='0 0 30 30' fill='none' xmlns='http://www.w3.org/2000/svg'%3E%3Cpath d='M1.22676 0C1.91374 0 2.45351 0.539773 2.45351 1.22676C2.45351 1.91374 1.91374 2.45351 1.22676 2.45351C0.539773 2.45351 0 1.91374 0 1.22676C0 0.539773 0.539773 0 1.22676 0Z' fill='rgba(0,0,0,0.07)'/%3E%3C/svg%3E");
}
@media (prefers-color-scheme: dark) {
    .dark\:bg-dots-lighter {
        background-image: url("data:image/svg+xml,%3Csvg width='30' height='30' viewBox='0 0 30 30' fill='none' xmlns='http://www.w3.org/2000/svg'%3E%3Cpath d='M1.22676 0C1.91374 0 2.45351 0.539773 2.45351 1.22676C2.45351 1.91374 1.91374 2.45351 1.22676 2.45351C0.539773 2.45351 0 1.91374 0 1.22676C0 0.539773 0.539773 0 1.22676 0Z' fill='rgba(255,255,255,0.07)'/%3E%3C/svg%3E");
    }
}
</style>
