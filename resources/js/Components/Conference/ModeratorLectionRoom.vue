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
                <ul>
                    <li v-for="student in students" :key="student.connectionId">
                        {{ student.username }}
                    </li>
                </ul>
            </div>

            <div v-show="displayQuestionPanel" class="question-list">
                <h3>Вопросы</h3>
                <ul>
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
    </div>
</template>

<script setup>
import { ref, computed, onMounted } from "vue";
import { OpenVidu } from "openvidu-browser";
import Button from "primevue/button";
import InputText from "primevue/inputtext";

const props = defineProps({
    sessionId: String,
    token: String,
    serverData: String,
    questions: Array,
    user: Object
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

const displayUserPanel = ref(false);
const displayQuestionPanel = ref(false);
const displayChatPanel = ref(false);
const checkActiveCount = ref(0);

// Чат
const messages = ref([]);
const chatMessage = ref("");

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
    students.value = [];
    if (session.value.remoteConnections) {
        session.value.remoteConnections.forEach((connection) => {
            const data = JSON.parse(connection.data);
            console.log(data);

            students.value.push({
                connectionId: connection.connectionId,
                username: data.username || "Unknown User",
            });
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
            .then(() => {
                console.log("End call signal sent");
                session.value.disconnect();
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
                console.log("Chat message sent");
            })
            .catch((error) => {
                console.error("Error sending chat message:", error);
            });

        chatMessage.value = "";
    }
};

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
    gap: 1em;
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
    min-width: 300px;
}

.question-list__item {
    display: grid;
    align-items: flex-start;
    justify-content: space-between;
    gap: 0.5em;
    grid-template-columns: 1fr auto;
}

.user-list ul {
    list-style: decimal;
    padding: 0;
    padding-left: 1em;
}

.user-list li {
    padding: 5px 0;
}

.right-sidebar h3,
.chat-panel header h3 {
    font-weight: bold;
}
.btn_off {
    background: var(--green-400);
    border: var(--green-400);
}
.btn_on {
    border: var(--gray-400);
    background: var(--gray-400);
}
.chat-panel {
    position: absolute;
    bottom: 5em;
    left: 20px;
    width: 20vw;
    background: #fff;
    padding: 1em;
    box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
    z-index: 1000;
    min-width: 300px;
    height: 80%;
    border-radius: 6px;
}

.chat-panel header {
    display: flex;
    align-items: center;
    justify-content: space-between;
}

.chat-body {
    display: flex;
    flex-direction: column;
    height: calc(100% - 50px);
}
.chat-messages {
    flex: 1 auto;
    overflow-y: auto;
    overflow-y: auto;
    margin-bottom: 1em;
}

.chat-message {
    margin-bottom: 0.5em;
}
.chat-message strong {
    color: var(--gray-500);
}
.chat-message.moderator strong {
    color: var(--red-600);
}

.chat-input {
    display: grid;
    grid-template-columns: 1fr auto;
    max-width: 100%;
    gap: 0.5em;
}
.chat-input input {
    max-width: 220px;
}
</style>
