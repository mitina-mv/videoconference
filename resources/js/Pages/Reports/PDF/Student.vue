<script setup>
import { ref, onMounted } from "vue";
import AuthenticatedLayout from "@/Layouts/AuthenticatedLayout.vue";
import { Head, usePage } from "@inertiajs/vue3";
import StudentReport from '@/Components/Reports/Student.vue'

const props = defineProps({
    test: [Object, null],
    error: [String, null],
    testlog: [Object, null],
    questions: [Array, null],
    vc: [Object, null],
    user: [Object, null],
});

</script>

<template>
    <div v-if="error">{{ error }}</div>
    <StudentReport
        v-else
        :test="test"
        :testlog="testlog"
        :questions="questions"
        :vc="vc"
        :user="user"
        :inctuleHeader="false"
    ></StudentReport>
</template>
