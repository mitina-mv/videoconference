<template>
    <div class="room" ref="roomConteiner">
        <div class="video-container" ref="videoContainer"></div>
        <div class="controls">
            <Button
                @click="toggleVideo"
                :class="toggleVideo ? 'btn_off' : 'btn_on'"
                rounded
                icon="pi pi-video"
            />

            <Button
                @click="toggleAudio"
                :class="toggleAudio ? 'btn_off' : 'btn_on'"
                rounded
                icon="pi pi-microphone"
            />

            <Button
                @click="toggleFullScreen"
                class="btn_screen"
                rounded
                :icon="
                    'pi ' +
                    (fullScreen ? 'pi-window-minimize' : 'pi-window-maximize')
                "
            />

            <Button
                @click="leaveConference"
                class="btn_leave"
                rounded
                icon="pi pi-phone"
            />

            <Button @click="toggleChatPanel" icon="pi pi-comments" rounded :severity="displayChatPanel ? '' : 'secondary'"></Button>
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
            <Button @click="submitAnswer">Ответить</Button>
        </div>

        <!-- Чат -->
        <div class="chat-panel" v-show="displayChatPanel">
            <header>
                <h3>Чат</h3>
                <Button icon="pi pi-times" text @click="toggleChatPanel"></Button>
            </header>
            <div class="chat-messages">
                <div v-for="message in messages" :key="message.timestamp" class="chat-message">
                    <strong>{{ message.username }}:</strong> {{ message.text }}
                </div>
            </div>
            <div class="chat-input d-flex gap-3">
                <InputText v-model="chatMessage" placeholder="Введите сообщение..." @keyup.enter="sendMessage" />
                <Button @click="sendMessage" icon="pi pi-send" text></Button>
            </div>
        </div>

        <!-- Окно подтверждения присутствия -->
        <div v-if="showPresencePrompt" class="presence-prompt" :style="presencePromptStyle">
            <p>Подтвердите ваше присутствие</p>
            <Button @click="confirmPresence" label="Подтвердить" icon="pi pi-check" severity="success"></Button>
        </div>
    </div>
</template>

<script setup>
import { ref, onMounted } from "vue";
import axios from "axios";
import { OpenVidu } from "openvidu-browser";
import Button from "primevue/button";
import RadioButton from "primevue/radiobutton";
import Checkbox from "primevue/checkbox";
import InputText from "primevue/inputtext";

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
const userAnswerText = ref("");

// Состояние для окна подтверждения присутствия
const showPresencePrompt = ref(false);
const presencePromptStyle = ref({
    top: '50%',
    left: '50%',
    transform: 'translate(-50%, -50%)'
});

// Чат
const messages = ref([]);
const chatMessage = ref("");
const displayChatPanel = ref(false);

const joinSession = async () => {
    console.log(props.token);
    try {
        session.value.on("streamCreated", ({ stream }) => {
            const subscriber = session.value.subscribe(
                stream,
                videoContainer.value,
                { insertMode: "APPEND" }
            );
            subscribers.value.push(subscriber);
        });

        session.value.on("signal:test", (event) => {
            const question = JSON.parse(event.data);
            currentQuestion.value = question;
            userAnswer.value = [];
            userAnswerText.value = "";
            setTimeout(() => {
                currentQuestion.value = null;
            }, 10 * 60 * 1000);
        });

        session.value.on("signal:active", (event) => {
            showPresencePrompt.value = true;
            movePresencePrompt();
        });

        session.value.on("signal:chat", (event) => {
            const message = JSON.parse(event.data);
            messages.value.push(message);
        });

        session.value.on("signal:endCall", (event) => {
            leaveConference();
        });

        await session.value.connect(props.token);

        console.log("Connected to session");
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
        window.location.href = "/videoconferences/my";
    }
};

const submitAnswer = async () => {
    try {
        let answer = userAnswer.value;
        if (currentQuestion.value.type === "text") {
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

const sendMessage = () => {
    if (chatMessage.value.trim() !== '') {
        const message = {
            username: 'You',
            text: chatMessage.value,
            timestamp: Date.now()
        };

        session.value.signal({
            data: JSON.stringify(message),
            to: [],
            type: 'chat'
        }).then(() => {
            console.log('Chat message sent');
        }).catch(error => {
            console.error('Error sending chat message:', error);
        });

        chatMessage.value = '';
    }
};

const confirmPresence = () => {
    showPresencePrompt.value = false;
};

const toggleChatPanel = () => {
    displayChatPanel.value = !displayChatPanel.value;
};

const movePresencePrompt = () => {
    const roomWidth = roomConteiner.value.clientWidth;
    const roomHeight = roomConteiner.value.clientHeight;
    const newTop = Math.random() * (roomHeight - 100) + 'px';
    const newLeft = Math.random() * (roomWidth - 200) + 'px';

    presencePromptStyle.value.top = newTop;
    presencePromptStyle.value.left = newLeft;
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
    grid-template-columns: repeat(3, 1fr);
    gap: 1em;
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
.chat-panel {
    position: absolute;
    bottom: 6em;
    left: 20px;
    width: 20vw;
    background: #fff;
    padding: 1em;
    box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
    z-index: 1000;
    min-width: 300px;
    border-radius: 6px;
}

.chat-panel header {
    display: flex;
    align-items: center;
    justify-content: space-between;
}
.chat-panel header h3 {
    font-weight: bold;
}
.chat-messages {
    max-height: 200px;
    overflow-y: auto;
    margin-bottom: 1em;
}

.chat-message {
    margin-bottom: 0.5em;
}

.chat-input {
    display: flex;
    gap: 0.5em;
}

.presence-prompt {
    position: absolute;
    background: #fff;
    padding: 1em;
    box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
    z-index: 1000;
    border-radius: 6px;
    display: flex;
    gap: 1em;
    flex-direction: column;
    align-items: center;
}
</style>
