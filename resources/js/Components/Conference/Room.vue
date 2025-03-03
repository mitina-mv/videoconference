<template>
    <div class="room" ref="roomConteiner">
        <div class="video-container" ref="videoContainer"></div>
        <div class="controls">
            <div class="d-flex gap-3">
                <Button
                    @click="toggleChatPanel"
                    icon="pi pi-comments"
                    rounded
                    :severity="displayChatPanel ? '' : 'secondary'"
                ></Button>
                <Button
                    @click="handAction"
                    class="hand-btn"
                    rounded
                    severity="info"
                ><font-awesome-icon :icon="['far', 'hand']" /></Button>
            </div>
            <div class="d-flex gap-3">
                <Button
                    @click="toggleFullScreen"
                    class="btn_screen"
                    rounded
                    :icon="
                        'pi ' +
                        (fullScreen
                            ? 'pi-window-minimize'
                            : 'pi-window-maximize')
                    "
                    severity="secondary"
                />

                <Button
                    @click="leaveConference"
                    class="btn_leave"
                    rounded
                    icon="pi pi-phone"
                    severity="danger"
                />
            </div>
            <div></div>
        </div>

        <!-- Карточка с вопросом -->
        <div v-if="currentQuestion" class="question-card">
            <img :src="currentQuestion.path_full" v-if="currentQuestion.path_full" class="main-image">
            <h3 class="mb-2">{{ currentQuestion.text }}</h3>
            <div v-if="currentQuestion.type === 'single'">
                <div
                    v-for="answer in currentQuestion.answers"
                    :key="answer.id"
                    class="mb-1"
                >
                    <RadioButton
                        :inputId="'ans_' + answer.id"
                        :value="answer.id"
                        v-model="userAnswer"
                        class="mr-2"
                    />
                    <label :for="'ans_' + answer.id">{{ answer.name }}</label>
                </div>
            </div>
            <div v-if="currentQuestion.type === 'multiple'">
                <div
                    v-for="answer in currentQuestion.answers"
                    :key="answer.id"
                    class="mb-1"
                >
                    <Checkbox
                        :inputId="'ans_' + answer.id"
                        :value="answer.id"
                        v-model="userAnswer"
                        class="mr-2"
                    />
                    <label :for="'ans_' + answer.id">{{ answer.name }}</label>
                </div>
            </div>
            <div v-if="currentQuestion.type === 'text'">
                <InputText v-model="userAnswerText" />
            </div>
            <Button @click="submitAnswer" class="mt-3">Ответить</Button>
        </div>

        <!-- Чат -->
        <div class="chat-panel" v-show="displayChatPanel">
            <header>
                <h3>Чат</h3>
                <Button
                    icon="pi pi-times"
                    text
                    @click="toggleChatPanel"
                ></Button>
            </header>
            <div class="chat-body">
                <div class="chat-messages">
                    <div
                        v-for="message in messages"
                        :key="message.timestamp"
                        :class="message.class || ''"
                        class="chat-message"
                    >
                        <strong>{{ message.username }}:</strong>
                        {{ message.text }}
                    </div>
                </div>
                <div class="chat-input d-flex gap-3">
                    <InputText
                        v-model="chatMessage"
                        placeholder="Введите сообщение..."
                        @keyup.enter="sendMessage"
                    />
                    <Button
                        @click="sendMessage"
                        icon="pi pi-send"
                        text
                    ></Button>
                </div>
            </div>
        </div>

        <!-- Окно подтверждения присутствия -->
        <div
            v-if="showPresencePrompt"
            class="presence-prompt"
            :style="presencePromptStyle"
        >
            <p>Подтвердите ваше присутствие</p>
            <Button
                @click="confirmPresence"
                label="Подтвердить"
                icon="pi pi-check"
                severity="success"
            ></Button>
        </div>
    </div>
</template>

<script setup>
import { ref, onMounted, onBeforeUnmount } from "vue";
import axios from "axios";
import { OpenVidu } from "openvidu-browser";
import Button from "primevue/button";
import RadioButton from "primevue/radiobutton";
import Checkbox from "primevue/checkbox";
import InputText from "primevue/inputtext";
import { FontAwesomeIcon } from '@fortawesome/vue-fontawesome'

const props = defineProps({
    sessionId: String,
    token: String,
    user: Object,
    messages: [Array, null],
    testlog: [Number, null],
});

const OV = new OpenVidu();
const videoContainer = ref(null);
const roomConteiner = ref(null);
const subscribers = ref([]);
const session = ref(null);
const fullScreen = ref(false);
const teacher = ref(null)

// Состояние для вопроса и ответа
const currentQuestion = ref(null);
const userAnswer = ref([]);
const userAnswerText = ref("");

// Состояние для окна подтверждения присутствия
const showPresencePrompt = ref(false);
const presencePromptStyle = ref({
    top: "50%",
    left: "50%",
    transform: "translate(-50%, -50%)",
});

// Чат
const messages = ref(props.messages || []);
const chatMessage = ref("");
const displayChatPanel = ref(false);

const joinSession = async () => {
    try {
        session.value.on("streamCreated", ({stream}) => {
            videoContainer.value.innerHTML = ''
            const subscriber = session.value.subscribe(
                stream,
                videoContainer.value,
                { insertMode: "APPEND" }
            );
            subscribers.value.push(subscriber);
            teacher.value = stream.connection
        });

        session.value.on("sessionDisconnected", (event) => {
            window.location.href = "/videoconferences/my";
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
        } else if (currentQuestion.value.type === "single") {
            answer = [answer]
        }

        await axios.post(route('api.videoconferences.answer', {session: props.sessionId}), 
        {
            testlog_id: props.testlog,
            question_id: currentQuestion.value.id,
            answer: answer,
        });

        currentQuestion.value = null;
    } catch (error) {
        console.error("Error submitting answer:", error);
    }
};

const sendMessage = () => {
    if (chatMessage.value.trim() !== "") {
        const message = {
            username: props.user.lastname + ' ' + `(${props.user.sg_name})`,
            text: chatMessage.value,
            timestamp: Date.now(),
        };

        session.value
            .signal({
                data: JSON.stringify(message),
                to: [],
                type: "chat",
            })
            .then(() => {
                axios.post(
                    route('api.videoconferences.chat', {session: props.sessionId}),
                    {message: message}
                )
                axios.post(route('api.videoconferences.action', {session: props.sessionId}), {
                    user_id: props.user.id,
                    action: 'message'
                })
            })
            .catch((error) => {
                console.error("Error sending chat message:", error);
            });

        chatMessage.value = "";
    }
};

const confirmPresence = () => {
    showPresencePrompt.value = false;
    
    axios.post(route('api.videoconferences.action', {session: props.sessionId}), {
        user_id: props.user.id,
        action: 'check'
    })
};

const toggleChatPanel = () => {
    displayChatPanel.value = !displayChatPanel.value;
};

const movePresencePrompt = () => {
    const roomWidth = roomConteiner.value.clientWidth;
    const roomHeight = roomConteiner.value.clientHeight;
    const newTop = Math.random() * (roomHeight - 100) + "px";
    const newLeft = Math.random() * (roomWidth - 200) + "px";

    presencePromptStyle.value.top = newTop;
    presencePromptStyle.value.left = newLeft;
};

const handAction = () => {
    console.warn(teacher.value);
    if(session.value) {
        session.value
            .signal({
                data: JSON.stringify({
                    username: `${props.user.lastname} (${props.user.sg_name})`
                }),
                to: [teacher.value],
                type: "hand",
            })
            .then(() => {
                axios.post(route('api.videoconferences.action', {session: props.sessionId}), {
                    user_id: props.user.id,
                    action: 'hand'
                })
            })
            .catch((error) => {
                console.error("Error sending hand active:", error);
            });
    }
}

onMounted(() => {
    session.value = OV.initSession();
    joinSession();
});
onBeforeUnmount(() => {
    if (session.value) {
        session.value.disconnect();
    }
});
</script>
