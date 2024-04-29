<template>
    <div class="room" ref="roomConteiner">
        <div class="video-container" ref="videoContainer"></div>
        <div class="controls">
            <Button @click="toggleVideo" :class="toggleVideo ? 'btn_off' : 'btn_on'" rounded icon="pi pi-video" />
            <!-- <button @click="toggleVideo" :class="toggleVideo ? 'btn_off' : 'btn_on'">
                <i class="pi pi-video"></i>
            </button> -->
            
            <Button @click="toggleAudio" :class="toggleAudio ? 'btn_off' : 'btn_on'" rounded icon="pi pi-microphone" />
            <!-- <button @click="toggleAudio" :class="toggleAudio ? 'btn_off' : 'btn_on'">
                <i class="pi pi-microphone"></i>
            </button> -->
            <Button @click="toggleFullScreen" class="btn_screen" rounded :icon="'pi ' + (fullScreen ? 'pi-window-minimize' : 'pi-window-maximize')" />
            <!-- <button @click="toggleFullScreen" class="btn_screen">
                <i class="pi" :class="fullScreen ? 'pi-window-minimize' : 'pi-window-maximize'"></i>
            </button> -->
            <Button @click="leaveConference" class="btn_leave" rounded icon="pi pi-stop-circle" />
        </div>
    </div>
</template>

<script setup>
import { ref, onMounted } from "vue";
import axios from "axios";
import { OpenVidu } from "openvidu-browser";
import Button from 'primevue/button';

const OV = new OpenVidu();
const videoContainer = ref(null);
const roomConteiner = ref(null);
const subscribers = ref([]);
const publisher = ref(null);
const session = ref(null);
const videoEnabled = ref(true);
const audioEnabled = ref(true);
const fullScreen = ref(false);
const mySessionId = "ses_EtTToJZqM8";

const joinSession = async () => {
    try {
        const response = await axios.get(`/connection/${mySessionId}`);
        const connectData = response.data;
        const myToken = connectData.token;

        session.value.on("streamCreated", ({ stream }) => {
            const subscriber = session.value.subscribe(
                stream,
                videoContainer.value,
                { insertMode: "APPEND" }
            );
            subscribers.value.push(subscriber);
        });

        await session.value.connect(myToken);

        console.log("Connected to session");

        publisher.value = OV.initPublisher(videoContainer.value, {
            videoSource: undefined,
            audioSource: undefined,
            publishAudio: audioEnabled.value,
            publishVideo: videoEnabled.value,
            resolution: "640x480",
            insertMode: "APPEND",
            mirror: true,
        });

        session.value.publish(publisher.value);
    } catch (error) {
        console.error("Connection error:", error);
    }
};

const toggleVideo = () => {
    videoEnabled.value = !videoEnabled.value;
    if (publisher.value) {
        publisher.value.publishVideo(videoEnabled.value);
    }
};

const toggleAudio = () => {
    audioEnabled.value = !audioEnabled.value;
    if (publisher.value) {
        publisher.value.publishAudio(audioEnabled.value);
    }
};

const toggleFullScreen = () => {
    const container = roomConteiner.value;
    if (!fullScreen.value) {
        if (container.requestFullscreen) {
            container.requestFullscreen();
        } else if (container.mozRequestFullScreen) {
            /* Firefox */
            container.mozRequestFullScreen();
        } else if (container.webkitRequestFullscreen) {
            /* Chrome, Safari & Opera */
            container.webkitRequestFullscreen();
        } else if (container.msRequestFullscreen) {
            /* IE/Edge */
            container.msRequestFullscreen();
        }
    } else {
        if (document.exitFullscreen) {
            document.exitFullscreen();
        } else if (document.mozCancelFullScreen) {
            /* Firefox */
            document.mozCancelFullScreen();
        } else if (document.webkitExitFullscreen) {
            /* Chrome, Safari & Opera */
            document.webkitExitFullscreen();
        } else if (document.msExitFullscreen) {
            /* IE/Edge */
            document.msExitFullscreen();
        }
    }
    fullScreen.value = !fullScreen.value;
};

const leaveConference = () => {
    if (session.value) {
        session.value.disconnect();
    }
};

onMounted(() => {
    session.value = OV.initSession();
    joinSession();
})
</script>

<style scoped>
.room {
    position: relative;
    width: 100%;
    height: 100vh;
}

.video-container {
    position: absolute;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    background-color: black;
    display: grid;
    grid-template-columns: repeat(3, 1fr);
    gap: 1em
}

.controls {
    position: absolute;
    bottom: 20px;
    left: 20px;
    z-index: 1000;
    background: #fff;
    display: flex;
    gap: 1em;
}

/* .btn_off {
    background: var(--primary-50);
}
.btn_on {
    background: var(--primary-600);
} */
.btn_screen{

}
.btn_leave {
    background: var(--red-600);

}
</style>
