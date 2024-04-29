<script setup>
import { defineProps, computed } from 'vue';
import OvVideo from "@/Components/OvVideo.vue"

const props = defineProps({
    streamManager: Object,
});

const getConnectionData = () => {
    const { connection } = props.streamManager.stream;
    return JSON.parse(connection.data);
};

const clientData = computed(() => {
    const { clientData } = getConnectionData();
    return clientData;
});
</script>

<template>
  <div v-if="streamManager">
    <ov-video :stream-manager="streamManager"/>
    <div><p>{{ clientData }}</p></div>
  </div>
</template>