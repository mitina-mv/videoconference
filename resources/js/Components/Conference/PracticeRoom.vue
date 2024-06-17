<template>
    <div class="room" ref="roomContainer">
        <div class="d-grid grid-col-4 gap-2 users-videos">
            <div class="user-block my-block">
                <div
                    class="video"
                    ref="videoContainer"
                ></div>
                <div class="user-info">
                    <span class="username">Мое подключение</span>
                </div>
            </div>
            <div
                v-for="user in paginatedUsers"
                :key="user.id"
                class="user-block"
                :ref="(el) => (userRefs[user.id] = el)"
            >
                <div
                    class="video"
                    :ref="
                        (videoContainerEl) =>
                            (user.videoBlock = videoContainerEl)
                    "
                ></div>
                
                <div class="user-info">
                    <span class="username">{{ user.username }}</span>
                    <div class="user-buttons">
                        <Button icon='pi pi-window-maximize' @click="toggleFullScreen(userRefs[user.id])" severity="secondary" rounded ></Button>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="pagination-controls">
            <button @click="prevPage" :disabled="currentPage === 1">
                Назад
            </button>
            <span>{{ currentPage }} / {{ totalPages }}</span>
            <button @click="nextPage" :disabled="currentPage === totalPages">
                Вперед
            </button>
        </div>
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
                    @click="toggleVideo"
                    :class="videoEnabled ? 'btn_off' : 'btn_on'"
                    rounded
                    icon="pi pi-video"
                    v-if="settings.permission_video"
                />
                <Button
                    @click="toggleAudio"
                    :class="audioEnabled ? 'btn_off' : 'btn_on'"
                    rounded
                    icon="pi pi-microphone"
                    v-if="settings.permission_audio"
                />
                <Button
                    @click="toggleFullScreen(roomContainer)"
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
                <Button
                    @click="toggleScreenShare"
                    rounded
                    icon="pi pi-desktop"
                    :class="screenPublisher ? 'btn_off' : 'btn_on'"
                    v-if="settings.permission_video"
                />
            </div>
            <div></div>
        </div>

        <!-- Карточка с вопросом -->
        <div v-if="currentQuestion" class="question-card">
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
import { ref, reactive, computed, onMounted, onBeforeUnmount } from "vue";
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
    tokenScreen: String,
    settings: [Object, null],
});

const OVScreen = new OpenVidu();
const OV = new OpenVidu();
const videoContainer = ref(null);
const roomContainer = ref(null);
const subscribers = ref([]);
const session = ref(null);
const sessionScreen = ref(null);
const fullScreen = ref(false);
const teacher = ref(null)
const publisher = ref(null);
const screenPublisher = ref(null);

const userRefs = reactive({});
const users = reactive([]);

const videoEnabled = ref(props.settings.permission_video);
const audioEnabled = ref(props.settings.permission_audio);

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

const currentPage = ref(1);
const usersPerPage = 11;

const paginatedUsers = computed(() => {
    const start = (currentPage.value - 1) * usersPerPage;
    const end = start + usersPerPage;
    return users.slice(start, end);
});

const totalPages = computed(() => Math.ceil(users.length / usersPerPage));

const nextPage = () => {
    if (currentPage.value < totalPages.value) {
        currentPage.value++;
    }
};

const prevPage = () => {
    if (currentPage.value > 1) {
        currentPage.value--;
    }
};

const joinSession = async () => {
    console.log(props.token);
    try {
        session.value.on("connectionCreated", (event) => {
            const connection = event.connection;
            console.log('connection', connection);
            const data = JSON.parse(connection.data);
            if(data.user_id == props.user.id) return;

            if(data.role == 'MODERATOR') {
                teacher.value = connection
            }

            addUser(
                connection.connectionId,
                data.username,
                "blue",
                data.user_id
            );
        });
        session.value.on("connectionDestroyed", (event) => {
            const connection = event.connection;
            removeUser(connection.connectionId);
        });
        session.value.on("streamCreated", ({ stream }) => {
            const connectionId = stream.connection.connectionId;
            const user = users.find(
                (u) => u.id === connectionId || u.screenShareId === connectionId
            );
            setTimeout(() => { 
                if (user && user.videoBlock) {
                    const subscriber = session.value.subscribe(
                        stream,
                        user.videoBlock,
                        { insertMode: "APPEND" }
                    );
                    subscribers.value.push(subscriber);
                }
             }, 1000);
        });

        session.value.on("streamDestroyed", ({ stream }) => {
            const subscriber = subscribers.value.find(
                (s) => s.stream === stream
            );
            if (subscriber) {
                session.value.unsubscribe(subscriber);
                subscribers.value = subscribers.value.filter(
                    (s) => s !== subscriber
                );
            }
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

        session.value.on("signal:mute", (event) => {
            audioEnabled.value = false
            if (publisher.value) {
                publisher.value.publishAudio(false);
            }
            if (screenPublisher.value) {
                screenPublisher.value.publishAudio(false);
            }
        });

        session.value.on("signal:chat", (event) => {
            const message = JSON.parse(event.data);
            messages.value.push(message);
        });

        session.value.on("signal:endCall", (event) => {
            leaveConference();
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
        console.log("Connected to session");
        await sessionScreen.value.connect(props.tokenScreen);
    } catch (error) {
        console.error("Connection error:", error);
    }
};
const addUser = (id, username, color, user_id) => {
    const existingUser = users.find((user) => user.user_id === user_id);
    if (existingUser) {
        if (username === "screen") {
            existingUser.screenShareId = id;
        } else {
            existingUser.screenShareId = existingUser.id;
            existingUser.id = id
            existingUser.username = username
        }
        return;
    }
    users.push({ id, username, color, user_id });
};

const removeUser = (id) => {
    const index = users.findIndex((user) => user.id === id);
    if (index !== -1) {
        users.splice(index, 1);
    }
};
const toggleVideo = () => {
    if(screenPublisher.value) return;

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
    
    if (screenPublisher.value) {
        screenPublisher.value.publishAudio(audioEnabled.value);
    }
};
const toggleFullScreen = (element) => {
    if(element != roomContainer.value)
    {
        if(!document.fullscreenElement) {
            if (element.requestFullscreen) {
                element.requestFullscreen();
            } else if (element.mozRequestFullScreen) {
                element.mozRequestFullScreen();
            } else if (element.webkitRequestFullscreen) {
                element.webkitRequestFullscreen();
            } else if (element.msRequestFullscreen) {
                element.msRequestFullscreen();
            }
        } else {
            CancelFullScreen()
        } 
    } else if(!fullScreen.value) {
        if (element.requestFullscreen) {
            element.requestFullscreen();
        } else if (element.mozRequestFullScreen) {
            element.mozRequestFullScreen();
        } else if (element.webkitRequestFullscreen) {
            element.webkitRequestFullscreen();
        } else if (element.msRequestFullscreen) {
            element.msRequestFullscreen();
        }
    }
    else {
        CancelFullScreen()
    } 

    if(element == roomContainer.value)
        fullScreen.value = !fullScreen.value;
};

const CancelFullScreen = () => {
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
    const roomWidth = roomContainer.value.clientWidth;
    const roomHeight = roomContainer.value.clientHeight;
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
const toggleScreenShare = () => {
    if (!screenPublisher.value) {
        startScreenSharing();
    } else {
        stopScreenSharing();
    }
};
const startScreenSharing = () => {
    if (screenPublisher.value) return;

    screenPublisher.value = OVScreen.initPublisher(videoContainer.value, {
        videoSource: "screen",
        publishAudio: audioEnabled.value,
        publishVideo: true,
        resolution: "1200x980",
        mirror: false,
    });

    videoEnabled.value = false;

    screenPublisher.value.on("accessAllowed", () => {
        session.value.unpublish(publisher.value);
        screenPublisher.value.stream
            .getMediaStream()
            .getVideoTracks()[0]
            .addEventListener("ended", () => {
                stopScreenSharing();
            });
        sessionScreen.value.publish(screenPublisher.value);
    });

    screenPublisher.value.on("accessDenied", () => {
        stopScreenSharing();
    });
};

// Остановка публикации экрана
const stopScreenSharing = () => {
    if (!screenPublisher.value) return;
    sessionScreen.value.unpublish(screenPublisher.value);
    screenPublisher.value = null;

    videoEnabled.value = true;
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
};
onMounted(() => {
    session.value = OV.initSession();
    sessionScreen.value = OVScreen.initSession();

    joinSession();
});

onBeforeUnmount(() => {
    if (session.value) {
        session.value.disconnect();
        sessionScreen.value.disconnect();
    }
});
</script>

<style scoped>
.conference-container {
    display: flex;
    flex-wrap: wrap;
}
.users-videos{
    grid-auto-rows: calc(78vh / 3);
    height: calc(100% - 6.5em);
    padding: 1em;
}
.video-container {
    width: 300px;
    height: 300px;
}

.user-block {
    width: 100%;
    border: 1px solid #606060;
    position: relative;
    
    overflow: hidden;
    border-radius: 5px;
}
.user-block .video {
    widows: 100%;
    height: 100%;;
}

.user-info {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 10px;
    min-height: 50px;
    position: absolute;
    width: 100%;
    bottom: 0;
    background: linear-gradient(to top, #000, #3636366e,#0f0f0f05);
    margin: 0;
    color: #fff;
}

.username {
    font-weight: bold;
}

.user-status {
    width: 15px;
    height: 15px;
    border-radius: 50%;
}
.pagination-controls {
    display: flex;
    justify-content: center;
    margin-top: 10px;
}

.pagination-controls button {
    margin: 0 5px;
}
</style>