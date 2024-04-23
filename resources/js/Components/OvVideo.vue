<script setup>
import { onMounted, defineProps } from 'vue';

const props = defineProps({
    streamManager: Object,
});

onMounted(() => {
    props.streamManager.addVideoElement($refs.video);
});
</script>

<template>
  <video ref="video" autoplay />
</template>
