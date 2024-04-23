<script setup>
import { Head, Link } from '@inertiajs/vue3';
import { OpenVidu } from 'openvidu-browser';
import { ref, onMounted } from 'vue';
import UserVideo from '@/Components/UserVideo.vue'

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

const mySessionId = 'ses_KbtRqyPshV'
const myUserName = "Participant" + Math.floor(Math.random() * 100)

const OV = new OpenVidu();
const session = ref(null);
const publisher = ref(null);
const subscribers = ref([]);

// Инициализация OpenVidu сессии
const joinSession = () => {
  fetch(`/сonnect/${mySessionId}`, {
      method: 'GET',
      headers: {
          'Content-Type': 'application/json',
      },
  })
  .then(response => response.json())
  .then(connectData => {
      const mytoken = connectData.token;

        session.value = OV.initSession();

        session.value.on('streamCreated', ({ stream }) => {
            let subscriber = session.value.subscribe(stream, undefined);
            subscribers.value.push(subscriber);
        });

        session.value.on('streamDestroyed', (event) => {

            // Remove the stream from 'subscribers' array
            const index = subscribers.value.indexOf(stream.streamManager, 0);
            if (index >= 0) {
                subscribers.value.splice(index, 1);
            }
        });


      session.value.connect(mytoken, { clientData: myUserName })
        .then(() => {
          console.log("Connected to session");

          publisher.value = OV.initPublisher(undefined, {
            videoSource: undefined,
            audioSource: undefined,
            publishAudio: true,
            publishVideo: true,
            resolution: "640x480",
            mirror: true
          });

        //   publisher.value.once('accessAllowed', () => {
        //     const videoElement = document.createElement('video');
        //     videoElement.autoplay = true;
        //     videoElement.muted = true;
        //     videoElement.srcObject = publisher.value.stream.getMediaStream();
        //     document.getElementById('video-container').appendChild(videoElement);
        //   });

          session.value.publish(publisher.value);
        })
        .catch(error => {
          console.error("Error connecting to session:", error);
        });
  })
  .catch(error => {
      console.error('Connection error:', error);
  });
};

// session.value.on('streamCreated', (event) => {
//     const subscriber = session.value.subscribe(event.stream, undefined);
//     const subscriberVideo = document.createElement('video');
//     subscriberVideo.autoplay = true;
//     subscriberVideo.srcObject = subscriber.stream.getMediaStream();
//     document.getElementById('video-container').appendChild(subscriberVideo);
//     });

const disconnectFromSession = () => {
    // Отключаемся от сессии и выполняем другие необходимые действия
    if (session.value) {
        session.value.disconnect();
    }
    // Дополнительные действия, если требуется
};

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
        <button @click="joinSession">Join Session</button>
        
        
        <div id="video-container" class="col-md-6">
            <user-video :stream-manager="publisher" />
            <user-video v-for="sub in subscribers" :key="sub.stream.connection.connectionId" :stream-manager="sub" />
        </div>
    
    </div>
</template>

<style>
#video-container {
  display: grid;
  grid-template-columns: repeat(3, 1fr);
}
</style>
