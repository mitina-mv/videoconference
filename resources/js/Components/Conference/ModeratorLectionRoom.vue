<template>
    <div class="room" ref="roomContainer">
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
                    @click="checkActive"
                    icon="pi pi-flag"
                    rounded
                    severity="info"
                ></Button>
            </div>
            <div class="d-flex gap-3">
                <Button
                    @click="toggleVideo"
                    :class="videoEnabled ? 'btn_off' : 'btn_on'"
                    rounded
                    icon="pi pi-video"
                />
                <Button
                    @click="toggleAudio"
                    :class="audioEnabled ? 'btn_off' : 'btn_on'"
                    rounded
                    icon="pi pi-microphone"
                />
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
                    @click="endCall"
                    class="btn_leave"
                    rounded
                    icon="pi pi-stop-circle"
                    severity="danger"
                />

                <Button
                    @click="toggleScreenShare"
                    rounded
                    icon="pi pi-desktop"
                    :class="screenPublisher ? 'btn_off' : 'btn_on'"
                />
            </div>
            <div class="d-flex gap-3">
                <Button
                    @click="toggleUserPanel"
                    icon="pi pi-users"
                    rounded
                    :severity="displayUserPanel ? '' : 'secondary'"
                ></Button>
                <Button
                    @click="toggleQuestionPanel"
                    icon="pi pi-question-circle"
                    rounded
                    :severity="displayQuestionPanel ? '' : 'secondary'"
                ></Button>
            </div>
        </div>

        <div
            class="right-sidebar"
            v-show="displayUserPanel || displayQuestionPanel"
        >
            <div v-show="displayUserPanel" class="user-list">
                <h3>Участники</h3>
                <div v-for="(group, index) in students" :key="index">
                    <u>{{ index }}</u>
                    <ul>
                        <li v-for="student in group" :key="student.connectionId" class="d-flex flex-between">
                            <p style="flex:1 auto;">{{ student.username }}</p>
                            <Button icon="pi pi-sign-out" severity="danger" @click="destroyConnection(student.connection)" text></Button>
                        </li>
                    </ul>
                </div>
            </div>

            <div v-show="displayQuestionPanel" class="question-list">
                <h3>Вопросы</h3>
                <ul v-if="questions">
                    <li
                        v-for="question in questions"
                        :key="question.id"
                        class="d-flex flex-between question-list__item"
                    >
                        {{ question.text }}
                        <Button
                            @click="sendQuestion(question)"
                            :icon="question.sent ? 'pi pi-check' : 'pi pi-send'"
                            :disabled="question.sent"
                            text
                        ></Button>
                    </li>
                </ul>
                <p v-else>Вы не добавили теста к этой видеоконференции.</p>
            </div>
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
                        class="chat-message"
                        :class="message.class || ''"
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

        <!-- Поднятие руки -->
        <div class="hand-panel" v-if="hands.length !== 0">
            <header class="d-flex flex-between">
                <h3>Поднятие руки</h3>
                <Button
                    icon="pi pi-times"
                    text
                    @click="toggleHandAction"
                ></Button>
            </header>
            <div class="hand-body">
                <p>
                    Студент(ы) <b>{{ hands.join(', ') }}</b> поднял(и) руку.
                </p>
                <Button
                    icon="pi pi-check"
                    label="Прочитано"
                    severity="secondary"
                    @click="toggleHandAction"
                ></Button>
            </div>
        </div>
    </div>
</template>

<script setup>
import { ref, computed, onMounted, onBeforeUnmount } from "vue";
import { OpenVidu } from "openvidu-browser";
import Button from "primevue/button";
import InputText from "primevue/inputtext";

const props = defineProps({
    sessionId: String,
    token: String,
    tokenScreen: String,
    messages: [Array, null],
    questions: [Array, null],
    user: Object
});

const OV = new OpenVidu();
const OVScreen = new OpenVidu();
const publisher = ref(null);
const videoEnabled = ref(true);
const audioEnabled = ref(true);
const fullScreen = ref(false);
const students = ref([]);
const screenPublisher = ref(null);

const videoContainer = ref(null);
const roomContainer = ref(null);
const session = ref(null);
const sessionScreen  = ref(null);

const displayUserPanel = ref(false);
const displayQuestionPanel = ref(false);
const displayChatPanel = ref(false);
const checkActiveCount = ref(0);

// Чат
const messages = ref(props.messages || []);
const chatMessage = ref("");

// поднятие руки
const hands = ref([]) 

const toggleHandAction = () => {
    hands.value = [];
}

const joinSession = async () => {
    try {
        session.value.on("connectionDestroyed", (event) => {
            updateUserList();
        });

        session.value.on("connectionCreated", (event) => {
            updateUserList();
        });

        session.value.on("signal:chat", (event) => {
            const message = JSON.parse(event.data);
            messages.value.push(message);
        });

        session.value.on("signal:hand", (event) => {
            if(!publisher.value) return;

            const user = JSON.parse(event.data);
            hands.value.push(user.username);
            hands.value = [...new Set(hands.value)]
        });

        sessionScreen.value.on("signal:hand", (event) => {
            if(!screenPublisher.value) return;

            const user = JSON.parse(event.data);
            hands.value.push(user.username);
            hands.value = [...new Set(hands.value)]
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

        // Подключение к sessionScreen
        await sessionScreen.value.connect(props.tokenScreen);
    } catch (error) {
        console.error("Connection error:", error);
    }
};

const updateUserList = () => {
    students.value = {};
    if (session.value.remoteConnections) {
        session.value.remoteConnections.forEach((connection) => {
            const data = JSON.parse(connection.data);


            if(data.username != 'screen') {
                if(!students.value.hasOwnProperty(data.sg_name)) {
                    students.value[data.sg_name] = []
                }
                students.value[data.sg_name].push({
                    connectionId: connection.connectionId,
                    username: `${data.username}` || "Unknown User",
                    connection: connection,
                });
            }
        });
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
        session.value
            .signal({
                data: "",
                to: [],
                type: "endCall",
            })
            .then(async () => {
                console.log("End call signal sent");
                await axios.post(route('api.videoconferences.end', {session: props.sessionId}))
                session.value.disconnect();
                sessionScreen.value.disconnect();
                window.location.href = "/videoconferences";
            })
            .catch((error) => {
                console.error(error);
            });
    }
};

const sendQuestion = (question) => {
    session.value
        .signal({
            data: JSON.stringify(question),
            to: [],
            type: "test",
        })
        .then(() => {
            console.log("Message successfully sent");
            question.sent = true;
        })
        .catch((error) => {
            console.error(error);
        });
};

const toggleUserPanel = () => {
    if (displayUserPanel.value) {
        displayUserPanel.value = false;
    } else {
        displayUserPanel.value = true;
        displayQuestionPanel.value = false;
    }
};

const toggleQuestionPanel = () => {
    if (displayQuestionPanel.value) {
        displayQuestionPanel.value = false;
    } else {
        displayQuestionPanel.value = true;
        displayUserPanel.value = false;
    }
};

const toggleChatPanel = () => {
    displayChatPanel.value = !displayChatPanel.value;
};

const checkActive = () => {
    if (session.value) {
        session.value
            .signal({
                data: "",
                to: [],
                type: "active",
            })
            .then(() => {
                checkActiveCount.value += 1;
                axios.post(route('api.videoconferences.checking', {session: props.sessionId}))
            })
            .catch((error) => {
                console.error(error);
            });
    }
};

const sendMessage = () => {
    if (chatMessage.value.trim() !== "") {
        const message = {
            username: props.user.full_name,
            text: chatMessage.value,
            timestamp: Date.now(),
            class: 'moderator',
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
            })
            .catch((error) => {
                console.error("Error sending chat message:", error);
            });

        chatMessage.value = "";
    }
};

const destroyConnection = (connetcion) => {
    if (session.value) {
        session.value.forceDisconnect(connetcion);
    }
}

const startScreenSharing = () => {
    if (screenPublisher.value) return;

    videoContainer.value.innerHTML = ''

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
    videoContainer.value.innerHTML = ''

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

const toggleScreenShare = () => {
    if (!screenPublisher.value) {
        startScreenSharing()
    } else {
        stopScreenSharing()
    }
}

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
