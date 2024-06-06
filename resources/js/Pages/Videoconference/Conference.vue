<script setup>
import { ref, onMounted } from "vue";
import AuthenticatedLayout from "@/Layouts/AuthenticatedLayout.vue";
import { Head, usePage } from "@inertiajs/vue3";
import Room from "@/Components/Conference/Room.vue";
import labels from "@/locales/ru.js";
import ModeratorLectionRoom from "@/Components/Conference/ModeratorLectionRoom.vue";

const props = defineProps({
    sessionId: String,
    token: String,
    error: String,
    type: String,
    role: String,
    questions: [Array, null]
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
                    {{ error }}
                </div>
                <template v-else>
                    <ModeratorLectionRoom v-if="role == 'MODERATOR'" :sessionId="sessionId" :token="token" :questions="questions"></ModeratorLectionRoom>
                    <Room :sessionId="sessionId" :token="token" v-else></Room>
                </template>
            </div>
        </div>
    </AuthenticatedLayout>
</template>