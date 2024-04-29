<template>
    <div class="room">
        <div class="video-container" ref="videoContainer"></div>
        <div class="controls">
            <button @click="toggleVideo">Включить/выключить видео</button>
            <button @click="toggleAudio">Включить/выключить аудио</button>
            <button @click="toggleFullScreen">На весь экран / Свернуть</button>
            <button @click="leaveConference">Покинуть конференцию</button>
        </div>
    </div>
</template>

<script>
import { OpenVidu } from "openvidu-browser";
import axios from "axios";

export default {
    data() {
        return {
            publisher: null,
            session: null,
            subscribers: [],
            videoEnabled: true,
            audioEnabled: true,
            fullScreen: false,
        };
    },
    methods: {
        toggleVideo() {
            this.videoEnabled = !this.videoEnabled;
            if (this.publisher) {
                this.publisher.publishVideo(this.videoEnabled);
            }
        },
        toggleAudio() {
            this.audioEnabled = !this.audioEnabled;
            if (this.publisher) {
                this.publisher.publishAudio(this.audioEnabled);
            }
        },
        toggleFullScreen() {
            const videoContainer = this.$refs.videoContainer;
            if (!this.fullScreen) {
                if (videoContainer.requestFullscreen) {
                    videoContainer.requestFullscreen();
                } else if (videoContainer.mozRequestFullScreen) {
                    /* Firefox */
                    videoContainer.mozRequestFullScreen();
                } else if (videoContainer.webkitRequestFullscreen) {
                    /* Chrome, Safari & Opera */
                    videoContainer.webkitRequestFullscreen();
                } else if (videoContainer.msRequestFullscreen) {
                    /* IE/Edge */
                    videoContainer.msRequestFullscreen();
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
            this.fullScreen = !this.fullScreen;
        },
        leaveConference() {
            if (this.session) {
                this.session.disconnect();
            }
        },
        joinSession() {
            axios
                .get(`/connect/${mySessionId}`)
                .then((response) => {
                    const connectData = response.data;
                    const myToken = connectData.token;

                    this.session.on("streamCreated", ({ stream }) => {
                        const subscriber = this.session.subscribe(
                            stream,
                            this.$refs.videoContainer,
                            { insertMode: "APPEND" }
                        );
                        this.subscribers.push(subscriber);
                    });

                    this.session
                        .connect(myToken)
                        .then(() => {
                            console.log("Connected to session");

                            this.publisher = OV.initPublisher(undefined, {
                                videoSource: undefined,
                                audioSource: undefined,
                                publishAudio: this.audioEnabled,
                                publishVideo: this.videoEnabled,
                                resolution: "640x480",
                                insertMode: "APPEND",
                                mirror: true,
                            });

                            this.session.publish(this.publisher);
                        })
                        .catch((error) => {
                            console.error(
                                "Error connecting to session:",
                                error
                            );
                        });
                })
                .catch((error) => {
                    console.error("Connection error:", error);
                });
        },
    },
    mounted() {
        // Initialize OpenVidu
        const OV = new OpenVidu();
        this.session = OV.initSession();

        // Join session
        this.joinSession();
    },
};
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
}

.controls {
    position: absolute;
    bottom: 20px;
    left: 20px;
}

.controls button {
    margin-right: 10px;
}
</style>
