<script setup>
import { ref, onMounted } from "vue";
import AuthenticatedLayout from "@/Layouts/AuthenticatedLayout.vue";
import { Head, usePage } from "@inertiajs/vue3";
import Room from "@/Components/Conference/Room.vue";
import labels from "@/locales/ru.js";

const props = defineProps({
    sessionId: String,
    token: String,
    error: String
});

</script>

<template>
    <Head :title="labels.page_titles.videoconferences" />

    <AuthenticatedLayout>
        <template #header>
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                {{ labels.page_titles.videoconferences }}
            </h2>
        </template>

        <div class="d-grid gap-4 content">
            <div class="content__container">
                <div v-if="error">
                {{ error }}</div>
                <Room :sessionId="sessionId" :token="token" v-else></Room>
            </div>
        </div>
    </AuthenticatedLayout>
</template>