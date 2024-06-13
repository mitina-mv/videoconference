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
</template>