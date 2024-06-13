<script setup>
import { ref, onMounted } from "vue";
import AuthenticatedLayout from "@/Layouts/AuthenticatedLayout.vue";
import { Head, usePage } from "@inertiajs/vue3";
import labels from "@/locales/ru.js";

const props = defineProps({
    test: [Object, null],
    error: [String, null],
    testlog: [Object, null],
    questions: [Array, null],
    vc: [Object, null],
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
        label: "Преподаватель",
        value: props.testlog.assignment.user.full_name,
    },
    {
        label: "Оценка",
        value: props.testlog.mark,
    },
];
</script>

<template>
    <Head :title="`Результаты тестирования: ${test.name}`" />

    <AuthenticatedLayout>
        <template #header>
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                Результаты тестирования: {{ test.name }}
            </h2>
        </template>

        <div class="d-grid gap-4 content">
            <div class="content__container report report_student">
                <!-- <h2>Результаты тестирования: {{ test.name }}</h2> -->
                <div class="test-info">
                    <p v-for="(field, index) in testInfoFields" :key="index">
                        <b>{{ field.label }}: </b>
                        <span>{{ field.value }}</span>
                    </p>
                </div>
                <div class="vc-info" v-if="vc">
                    <p><b>Видеоконференция: </b>{{ vc.name }}</p>
                    <p><b>Дата конференции: </b>{{ vc.date }}</p>
                </div>
                <table class="mt-3">
                    <thead>
                        <tr>
                            <th>Вопрос</th>
                            <th>Ваш ответ</th>
                            <th>Стоимость</th>
                            <th>Оценка</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr v-for="(q, ind) in questions" :key="ind">
                            <td width="35%">{{ q.question }}</td>
                            <td width="45%">
                                <span
                                    v-for="(a, i) in q.answers"
                                    :key="ind + '_' + i"
                                    :class="
                                        a.is_correct
                                            ? 'text-success'
                                            : 'text-danger'
                                    "
                                >
                                    {{ a.text }}
                                </span>
                            </td>
                            <td width="10%">{{ Number(q.total_mark) }}</td>
                            <td width="10%">{{ Number(q.mark) }}</td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
