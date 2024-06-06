<template>
    <div class="room" ref="roomContainer">
        <div class="video-container" ref="videoContainer"></div>
        <div class="controls">
            <div>

            </div>
            <div class="d-flex gap-3">
                <Button @click="toggleVideo" :class="toggleVideo ? 'btn_off' : 'btn_on'" rounded icon="pi pi-video" />
                <Button @click="toggleAudio" :class="toggleAudio ? 'btn_off' : 'btn_on'" rounded icon="pi pi-microphone" />
                <Button @click="toggleFullScreen" class="btn_screen" rounded :icon="'pi ' + (fullScreen ? 'pi-window-minimize' : 'pi-window-maximize')" severity="secondary" />
                <Button @click="endCall" class="btn_leave" rounded icon="pi pi-stop-circle" severity="danger" />
            </div>
            <div class="d-flex gap-3">
                <Button @click="toggleUserPanel" icon="pi pi-users" rounded></Button>
                <Button @click="toggleQuestionPanel" icon="pi pi-question-circle" rounded></Button>
            </div>      
        </div>

        <div class="right-sidebar" v-show="displayUserPanel || displayQuestionPanel">
            <div v-show="displayUserPanel" class="user-list">
                <h3>Участники</h3>
                <ul>
                    <li v-for="student in students" :key="student.connectionId">
                        {{ student.username }}
                    </li>
                </ul>
            </div>

            <div v-show="displayQuestionPanel" class="question-list">
                <h3>Вопросы</h3>
                <ul>
                    <li v-for="question in questions" :key="question.id">
                        {{ question.text }}
                        <Button @click="sendQuestion(question)" icon="pi pi-send" text></Button>
                    </li>
                </ul>
            </div>
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
    questions: Array
});

const OV = new OpenVidu();
const videoContainer = ref(null);
const roomContainer = ref(null);
const publisher = ref(null);
const session = ref(null);
const videoEnabled = ref(true);
const audioEnabled = ref(true);
const fullScreen = ref(false);
const students = ref([]);
const displayUserPanel = ref(false)
const displayQuestionPanel = ref(false)

const joinSession = async () => {
    try {
        session.value.on("connectionDestroyed", (event) => {
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
            resolution: "1200x980",
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

const sendQuestion = (question) => {
    session.value.signal({
      data: JSON.stringify(question),
      to: [],
      type: 'test'
    })
    .then(() => {
        console.log('Message successfully sent');
    })
    .catch(error => {
        console.error(error);
    });
}

const toggleUserPanel = () => {
    displayUserPanel.value = !displayUserPanel.value
}

const toggleQuestionPanel = () => {
    displayQuestionPanel.value = !displayQuestionPanel.value
}

onMounted(() => {
    session.value = OV.initSession();
    joinSession();
});
</script>

<style scoped>
.room {
    position: relative;
    width: 100%;
    height: calc(100vh - 6.75em);
}

.video-container {
    position: absolute;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    background-color: black;
    display: grid;
    gap: 1em
}

.controls {
    position: absolute;
    bottom: 20px;
    z-index: 1000;
    display: flex;
    gap: 1em;
    justify-content: space-between;
    width: 100%;
    box-sizing: border-box;
    padding: 0 20px;
}

.right-sidebar {
    text-align: left;
    margin-top: 20px;
    z-index: 1000;
    background: #fff;
    position: absolute;
    width: 20vw;
    right: 10px;
    height: 80%;
    border-radius: 6px;
    padding: 1em;
    bottom: 5em;
}

.user-list ul {
    list-style: decimal;
    padding: 0;
    padding-left: 1em;
}

.user-list li {
    padding: 5px 0;
}

.right-sidebar h3 {
    font-weight: bold;
}
</style>
