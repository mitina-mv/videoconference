<template>
    <div class="room" ref="roomConteiner">
        <div class="video-container" ref="videoContainer"></div>
        <div class="controls">
            <Button @click="toggleVideo" :class="toggleVideo ? 'btn_off' : 'btn_on'" rounded icon="pi pi-video" />
            
            <Button @click="toggleAudio" :class="toggleAudio ? 'btn_off' : 'btn_on'" rounded icon="pi pi-microphone" />

            <Button @click="toggleFullScreen" class="btn_screen" rounded :icon="'pi ' + (fullScreen ? 'pi-window-minimize' : 'pi-window-maximize')" />

            <Button @click="leaveConference" class="btn_leave" rounded icon="pi pi-phone" />
        </div>

        <!-- Карточка с вопросом -->
        <div v-if="currentQuestion" class="question-card">
            <h3>{{ currentQuestion.text }}</h3>
            <div v-if="currentQuestion.type === 'single'">
                <div v-for="answer in currentQuestion.answers" :key="answer.id">
                    <RadioButton :value="answer.id" v-model="userAnswer" />
                    <label>{{ answer.name }}</label>
                </div>
            </div>
            <div v-if="currentQuestion.type === 'multiple'">
                <div v-for="answer in currentQuestion.answers" :key="answer.id">
                    <Checkbox :value="answer.id" v-model="userAnswer" />
                    <label>{{ answer.name }}</label>
                </div>
            </div>
            <div v-if="currentQuestion.type === 'text'">
                <InputText v-model="userAnswerText" />
            </div>
            <Button @click="submitAnswer">Submit</Button>
        </div>
    </div>
</template>

<script setup>
import { ref, onMounted } from "vue";
import axios from "axios";
import { OpenVidu } from "openvidu-browser";
import Button from 'primevue/button';
import RadioButton from 'primevue/radiobutton';
import Checkbox from 'primevue/checkbox';
import InputText from 'primevue/inputtext';

const props = defineProps({
    sessionId: String,
    token: String,
});

const OV = new OpenVidu();
const videoContainer = ref(null);
const roomConteiner = ref(null);
const subscribers = ref([]);
const publisher = ref(null);
const session = ref(null);
const videoEnabled = ref(true);
const audioEnabled = ref(true);
const fullScreen = ref(false);

// Состояние для вопроса и ответа
const currentQuestion = ref(null);
const userAnswer = ref([]);
const userAnswerText = ref('');

const joinSession = async () => {
    console.log(props.token);
    try {
        // const response = await axios.get(`/connection/${mySessionId}`);
        // const connectData = response.data;
        // const myToken = connectData.token;

        session.value.on("streamCreated", ({ stream }) => {
            const subscriber = session.value.subscribe(
                stream,
                videoContainer.value,
                { insertMode: "APPEND" }
            );
            subscribers.value.push(subscriber);
        });

        session.value.on('signal:test', (event) => {
            const question = JSON.parse(event.data);
            currentQuestion.value = question;
            userAnswer.value = [];
            userAnswerText.value = '';
            setTimeout(() => { currentQuestion.value = null; }, 10 * 60 * 1000);
        });

        session.value.on('signal:endCall', (event) => {
            leaveConference();
        });

        await session.value.connect(props.token);

        console.log("Connected to session");

        // publisher.value = OV.initPublisher(videoContainer.value, {
        //     videoSource: undefined,
        //     audioSource: undefined,
        //     publishAudio: audioEnabled.value,
        //     publishVideo: videoEnabled.value,
        //     resolution: "640x480",
        //     insertMode: "APPEND",
        //     mirror: true,
        // });

        // session.value.publish(publisher.value);
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
        window.location.href = '/videoconferences/my'

    }
};

const submitAnswer = async () => {
    try {
        let answer = userAnswer.value;
        if (currentQuestion.value.type === 'text') {
            answer = userAnswerText.value;
        }

        /* await axios.post('/api/submitAnswer', {
            questionId: currentQuestion.value.id,
            answer: answer,
        }); */

        currentQuestion.value = null;
    } catch (error) {
        console.error("Error submitting answer:", error);
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

.btn_off {
    background: var(--green-400);
}
.btn_on {
    background: var(--red-400);
}

.btn_leave {
    background: var(--red-600);
}
.question-card {
    position: absolute;
    top: 10px;
    right: 10px;
    background: white;
    padding: 20px;
    border: 1px solid #ccc;
    border-radius: 5px;
    z-index: 1000;
    width: 300px;
}
</style>
