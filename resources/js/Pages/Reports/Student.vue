<script setup>
import { ref, onMounted } from "vue";
import AuthenticatedLayout from "@/Layouts/AuthenticatedLayout.vue";
import { Head, usePage } from "@inertiajs/vue3";

const props = defineProps({
    test: [Object, null],
    error: [String, null],
    testlog: [Object, null],
    questions: [Array, null],
    user: Object,
});

const testInfoFields = [
    {
        label: "Студент",
        value: props.user.full_name,
    },
    {
        label: "Группа",
        value: props.user.sg_name,
    },
    {
        label: "Дата проведения",
        value: props.testlog.assignment.date,
    },
    {
        label: "Проводит",
        value: props.testlog.assignment.user.full_name,
    },
    {},
];
</script>

<template>
    <h2>Результаты тестирования: {{ test.name }}</h2>
    <div class="test-info">
        <p v-for="(field, index) in testInfoFields" :key="index">
            <b>{{ field.label }}: </b> <span>{{ field.value }}</span>
        </p>
    </div>
</template>
