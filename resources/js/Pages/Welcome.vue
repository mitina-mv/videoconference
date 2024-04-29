<script setup>
import { Head, Link } from "@inertiajs/vue3";
import { OpenVidu } from "openvidu-browser";
import { ref } from "vue";
import UserVideo from "@/Components/UserVideo.vue";

const mySessionId = "ses_Is1xZYKN0n";
const myUserName = "Participant" + Math.floor(Math.random() * 100);

const OV = new OpenVidu();
const session = OV.initSession();
const publisher = ref(null);
const subscribers = ref([]);

session.on("streamCreated", ({stream}) => {
    const subscriber = session.subscribe(
        stream, 
        document.getElementById("video-container"), 
        {insertMode: 'APPEND'}
    );
    subscribers.value.push(subscriber);
    console.log(stream);
});

session.on("exception", ({ exception }) => {
    console.warn('my exception', exception);
});

const joinSession = () => {
    fetch(`/сonnect/${mySessionId}`, {
        method: "GET",
        headers: {
            "Content-Type": "application/json",
        },
    })
    .then((response) => response.json())
    .then((connectData) => {
        const mytoken = connectData.token;

        session.on("connectionCreated", (event) => {
            console.log('Connection ' + event.connection.connectionId + ' created');
        });

        session.on("videoElementCreated", (event) => {
            console.log("videoElementCreated", event);
        });

        session.on("connectionPropertyChanged", (event) => {
            console.log("connectionPropertyChanged", event);
        });

        session.on("streamDestroyed", (event) => {
            const index = subscribers.value.findIndex(
                (sub) => sub.stream.streamId === event.stream.streamId
            );
            if (index >= 0) {
                subscribers.value.splice(index, 1);
            }
            console.log("streamDestroyed", event);
        });

        session
            .connect(mytoken, { clientData: myUserName })
            .then(() => {
                console.log("Connected to session");

                publisher.value = OV.initPublisher(undefined, {
                    videoSource: undefined,
                    audioSource: undefined,
                    publishAudio: true,
                    publishVideo: true,
                    resolution: "640x480",
                    insertMode: "APPEND",
                    mirror: true,
                })

                session.publish(publisher.value);
                const videoElement = publisher.value.createVideoElement(document.getElementById("video-container"))
                publisher.value.addVideoElement(videoElement);

                publisher.value.on("streamPropertyChanged", (event) => {
                    console.log("streamPropertyChanged",  event);
                });

                // const subscriber = session.subscribe(publisher.value.stream);
                // subscribers.value.push(subscriber);
            })
            .catch((error) => {
                console.error("Error connecting to session:", error);
            });
    })
    .catch((error) => {
        console.error("Connection error:", error);
    });
};

const leaveSession = () => {
    if (session) {
        session.disconnect();
    }
};
</script>

<template>
    <Head title="Welcome" />

    <div
        class=""
    >
        <button @click="joinSession">Join Session</button>

        <div id="video-container"></div>
        <p>{{ myUserName }}</p>
        <button @click="leaveSession">Leave Session</button>
        
        <div class="grid">
            <video
                v-for="sub in subscribers"
                :key="sub.stream.streamId"
                :srcObject="sub.stream.getMediaStream()"
                :id="sub.stream.streamId"
                autoplay
            />
        </div>
        
    </div>
</template>

<style>
#video-container, .grid {
    display: grid;
    grid-template-columns: repeat(3, 1fr);
    gap: 1em;
}
</style>
