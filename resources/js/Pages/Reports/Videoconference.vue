<script setup>
// import { ref, onMounted } from "vue";
import AuthenticatedLayout from "@/Layouts/AuthenticatedLayout.vue";
import Videoconference from "@/Components/Reports/Videoconference.vue";

const props = defineProps({
    test: [Object, null],
    vc: [Object, null],
    error: [String, null],
    groups: [Object, null],
});
</script>

<template>
    <Head :title="`Результаты конференции: ${vc ? vc.name : 'ошибка'}`" />

    <AuthenticatedLayout>
        <template #header>
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                Результаты конференции: {{ vc ? vc.name : 'ошибка' }}
            </h2>
        </template>

        <div class="d-grid gap-4 content">
            <div class="content__container report report_assignment">
                <div v-if="error">{{ error }}</div>
                <Videoconference
                    :test="test"
                    :vc="vc"
                    :groups="groups"
                    :inctuleHeader="false"
                ></Videoconference>
            </div>
        </div>
    </AuthenticatedLayout>
</template>