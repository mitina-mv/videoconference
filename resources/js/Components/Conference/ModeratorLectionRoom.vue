<template>
    <div class="room" ref="roomContainer">
        <div class="video-container" ref="videoContainer"></div>
        <div class="username">
            {{ username }}
        </div>
        <div class="controls">
            <Button @click="toggleVideo" :class="toggleVideo ? 'btn_off' : 'btn_on'" rounded icon="pi pi-video" />
            <Button @click="toggleAudio" :class="toggleAudio ? 'btn_off' : 'btn_on'" rounded icon="pi pi-microphone" />
            <Button @click="toggleFullScreen" class="btn_screen" rounded :icon="'pi ' + (fullScreen ? 'pi-window-minimize' : 'pi-window-maximize')" />
            <Button @click="endCall" class="btn_leave" rounded icon="pi pi-stop-circle" />
        </div>
        <div class="user-list">
            <h3>Current Users</h3>
            <ul>
                <li v-for="student in students" :key="student.connectionId">
                    {{ student.username }}
                </li>
            </ul>
        </div>
    </div>
</template>

<script setup>
import { ref, computed, onMounted } from "vue";
import { OpenVidu } from "openvidu-browser";
import Button from 'primevue/button';

const props = defineProps({
    sessionId: String,
    token: String,
    serverData: String,
});

const username = computed(() => {
    try {
        const data = JSON.parse(props.serverData);
        return data.username || "Unknown User";
    } catch (e) {
        return "Unknown User";
    }
});

const OV = new OpenVidu();
const videoContainer = ref(null);
const roomContainer = ref(null);
const subscribers = ref([]);
const publisher = ref(null);
const session = ref(null);
const videoEnabled = ref(true);
const audioEnabled = ref(true);
const fullScreen = ref(false);
const students = ref([]);

const joinSession = async () => {
    try {
        session.value.on("streamCreated", ({ stream }) => {
            const subscriber = session.value.subscribe(stream, videoContainer.value, { insertMode: "APPEND" });
            subscribers.value.push(subscriber);
            updateUserList();
        });

        session.value.on("streamDestroyed", ({ stream }) => {
            const index = subscribers.value.findIndex(sub => sub.stream === stream);
            if (index !== -1) subscribers.value.splice(index, 1);
            updateUserList();
        });

        session.value.on("connectionCreated", (event) => {
            updateUserList();
        });

        await session.value.connect(props.token);

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
        updateUserList();
    } catch (error) {
        console.error("Connection error:", error);
    }
};

const updateUserList = () => {
    // console.log(session.value, session.value?.remoteConnections);
    students.value = []
    if(session.value.remoteConnections) {
        session.value.remoteConnections.forEach(connection => {
            const data = JSON.parse(connection.data);
            console.log(data);

            students.value.push({
                connectionId: connection.connectionId,
                username: data.username || "Unknown User",
            })
        })
        
        // .map(connection => {
        //     console.log(connection);
        //     // const data = JSON.parse(connection.data);
        //     // return {
        //     //     connectionId: connection.connectionId,
        //     //     username: data.username || "Unknown User",
        //     // };
        // });
    }
    console.log(students.value);
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
    const container = roomContainer.value;
    if (!fullScreen.value) {
        if (container.requestFullscreen) {
            container.requestFullscreen();
        } else if (container.mozRequestFullScreen) {
            container.mozRequestFullScreen();
        } else if (container.webkitRequestFullscreen) {
            container.webkitRequestFullscreen();
        } else if (container.msRequestFullscreen) {
            container.msRequestFullscreen();
        }
    } else {
        if (document.exitFullscreen) {
            document.exitFullscreen();
        } else if (document.mozCancelFullScreen) {
            document.mozCancelFullScreen();
        } else if (document.webkitExitFullscreen) {
            document.webkitExitFullscreen();
        } else if (document.msExitFullscreen) {
            document.msExitFullscreen();
        }
    }
    fullScreen.value = !fullScreen.value;
};

const endCall = () => {
    if (session.value) {
        session.value.disconnect();
    }
    // Implement logic to completely end the call for all participants if needed
};

onMounted(() => {
    session.value = OV.initSession();
    joinSession();
});
</script>

<style scoped>
/* Ваш CSS код */
.room {
    display: flex;
    flex-direction: column;
    align-items: center;
}

.video-container {
    width: 100%;
    height: 70vh;
    background-color: black;
    margin-bottom: 10px;
}

.username {
    font-size: 1.2em;
    font-weight: bold;
    margin-bottom: 10px;
}

.controls {
    display: flex;
    gap: 10px;
    margin-bottom: 10px;
}

.user-list {
    width: 100%;
    text-align: left;
    margin-top: 20px;
}

.user-list ul {
    list-style: none;
    padding: 0;
}

.user-list li {
    padding: 5px 0;
}
</style>
