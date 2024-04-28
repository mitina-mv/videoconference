<script setup>
import { Head, Link } from "@inertiajs/vue3";
import { OpenVidu } from "openvidu-browser";
import { ref, onMounted } from "vue";
import UserVideo from "@/Components/UserVideo.vue";

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

const mySessionId = "ses_U3MbT6TBlv";
const myUserName = "Participant" + Math.floor(Math.random() * 100);

const OV = new OpenVidu();
const session = ref(OV.initSession());
const publisher = ref(null);
const subscribers = ref([]);

// Подписываемся на события после успешного подключения к сессии
session.value.on("streamCreated", (event) => {
    const subscriber = session.value.subscribe(event.stream);
    subscribers.value.push(subscriber);  
    console.warn("streamCreated",  subscribers.value);              
});

// Инициализация OpenVidu сессии
const joinSession = () => {
    fetch(`/сonnect/${mySessionId}`, {
        method: "GET",
        headers: {
            "Content-Type": "application/json",
        },
    })
        .then((response) => response.json())
        .then((connectData) => {
            const mytoken = connectData.token;

            // session.value = OV.initSession();

            session.value.on("connectionCreated", (event) => {
                console.log('Connection ' + event.connection.connectionId + ' created');
            });

            // Подписываемся на события после успешного подключения к сессии
            session.value.on("videoElementCreated", (event) => {
                console.log("videoElementCreated",  event);
            });

            session.value.on("connectionPropertyChanged", (event) => {
                console.log("connectionPropertyChanged",  event);
            });

            session.value.on("streamDestroyed", (event) => {
                // Удаление потока из списка подписчиков
                // const index = subscribers.value.findIndex(
                //     (sub) => sub.stream.streamId === event.stream.streamId
                // );
                // if (index >= 0) {
                //     subscribers.value.splice(index, 1);
                // }

                console.log("streamDestroyed",  event);
            });

            session.value
                .connect(mytoken, { clientData: myUserName })
                .then(() => {
                    console.log("Connected to session");

                    publisher.value = OV.initPublisher(undefined, {
                        videoSource: undefined,
                        audioSource: undefined,
                        publishAudio: true,
                        publishVideo: true,
                        resolution: "640x480",
                        insertMode: "APPEND",
                        mirror: true,
                    })

                    session.value.publish(publisher.value);
                    const videoElement = publisher.value.createVideoElement(document.getElementById("video-container"))
                    publisher.value.addVideoElement(videoElement);

                    // Подписываемся на события после успешного подключения к сессии
                    publisher.value.on("streamPropertyChanged", (event) => {
                        console.log("streamPropertyChanged",  event);
                    });
                })
                .catch((error) => {
                    console.error("Error connecting to session:", error);
                });
        })
        .catch((error) => {
            console.error("Connection error:", error);
        });
};

const leaveSession = () => {
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
        class=""
    >
        <button @click="joinSession">Join Session</button>

        <div id="video-container"></div>
        <p>{{ myUserName }}</p>
        <button @click="leaveSession">Leave Session</button>
        
        <div class="grid">
            <video
                v-for="sub in subscribers"
                :key="sub.stream.streamId"
                :srcObject="sub.stream.getMediaStream()"
                :id="sub.stream.streamId"
                autoplay
            />
        </div>
        
    </div>
</template>

<style>
#video-container, .grid {
    display: grid;
    grid-template-columns: repeat(3, 1fr);
    gap: 1em;
}
</style>
