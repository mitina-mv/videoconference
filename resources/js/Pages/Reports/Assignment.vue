<script setup>
import { ref, onMounted } from "vue";
import AuthenticatedLayout from "@/Layouts/AuthenticatedLayout.vue";
import { Head, usePage } from "@inertiajs/vue3";
import labels from "@/locales/ru.js";
import AssignmentReport from "@/Components/Reports/Assignment.vue";

const props = defineProps({
    test: [Object, null],
    assignment: [Object, null],
    error: [String, null],
    test_settings: [Object, null],
    groups: [Array, null],
    user: [Object, null],
    metrics: [Object, null],
});
</script>

<template>
    <Head :title="`Результаты тестирования: ${test ? test.name : 'ошибка'}`" />

    <AuthenticatedLayout>
        <template #header>
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                Результаты тестирования: {{ test ? test.name : 'ошибка' }}
            </h2>
        </template>

        <div class="d-grid gap-4 content">
            <div class="content__container report report_assignment">
                <div v-if="error">{{ error }}</div>
                <AssignmentReport
                    v-else
                    :test="test"
                    :assignment="assignment"
                    :test_settings="test_settings"
                    :groups="groups"
                    :user="user"
                    :inctuleHeader="false"
                    :metrics="metrics"
                ></AssignmentReport>
            </div>
        </div>
    </AuthenticatedLayout>
</template>