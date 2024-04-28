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

      session.value.connect(mytoken, { clientData: myUserName })
        .then(() => {
          console.log("Connected to session");

          // Подписываемся на события после успешного подключения к сессии
          session.value.on('streamCreated', ({ stream }) => {
              console.log("tyk", stream);
          });

          session.value.on('streamDestroyed', (event) => {
              // Удаление потока из списка подписчиков
              const index = subscribers.value.findIndex(sub => sub.stream.streamId === event.stream.streamId);
              if (index >= 0) {
                  subscribers.value.splice(index, 1);
              }
          });

          publisher.value = OV.initPublisher(undefined, {
            videoSource: undefined,
            audioSource: undefined,
            publishAudio: true,
            publishVideo: true,
            resolution: "640x480",
            mirror: true
          });

          publisher.value.once('accessAllowed', () => {
            const videoElement = document.createElement('video');
            videoElement.autoplay = true;
            videoElement.muted = true;
            videoElement.srcObject = publisher.value.stream.getMediaStream();
            document.getElementById('video-container').appendChild(videoElement);
          });

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


const disconnectFromSession = () => {
    // Отключаемся от сессии и выполняем другие необходимые действия
    if (session.value) {
        session.value.disconnect();
    }
    // Дополнительные действия, если требуется
};

</script>

<template>
    <Head title="Welcome" />

    <div
        class="relative sm:flex sm:justify-center sm:items-center min-h-screen bg-dots-darker bg-center bg-gray-100 dark:bg-dots-lighter dark:bg-gray-900 selection:bg-red-500 selection:text-white"
    >
        <button @click="joinSession">Join Session</button>
        
        
        <div id="video-container">
            
        </div>

        <video v-for="sub in subscribers" :key="sub.stream.streamId" :srcObject="sub.stream.getMediaStream()" autoplay />
    
    </div>
</template>

<style>
#video-container {
  display: grid;
  grid-template-columns: repeat(3, 1fr);
}
</style>