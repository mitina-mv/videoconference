<script setup>
import { Head, Link } from "@inertiajs/vue3";
import { OpenVidu } from "openvidu-browser";
import { ref } from "vue";
import Room from "@/Components/Conference/Room.vue";

</script>

<template>
    <Head title="Welcome" />

    <div
        class=""
    >
        <Room></Room>
    </div>
</template>

<style>

</style>
