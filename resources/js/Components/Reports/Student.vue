<script setup>
import labels from "@/locales/ru.js";

const props = defineProps({
    test: Object,
    testlog: Object,
    questions: Array,
    vc: Object,
    user: Object,
    inctuleHeader: {
        type: Boolean,
        default: true
    },
    includeCorrectAnswers: {
        type: Boolean,
        default: false
    },
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
    <h2 v-if="inctuleHeader">Результаты тестирования: {{ test.name }}</h2>
    <div class="test-info">
        <h3>Основные данные</h3>
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
                <th>{{includeCorrectAnswers ? 'Ответ студента' : 'Ваш ответ'}}</th>
                <th v-if="includeCorrectAnswers">Правильный ответ</th>
                <th>Стоимость</th>
                <th>Оценка</th>
            </tr>
        </thead>
        <tbody>
            <tr v-for="(q, ind) in questions" :key="ind">
                <td :width="includeCorrectAnswers ? '15%' : '35%'">{{ q.question }}</td>
                <td :width="includeCorrectAnswers ? '22.5%' : '45%'">
                    <p v-if="q.answers == null">Нет ответа</p>
                    <template v-if="includeCorrectAnswers">
                        <p
                            v-for="(a, i) in q.answers"
                            :key="ind + '_' + i"
                            :class="a.is_correct ? 'text-success' : 'text-danger'"
                        >
                            {{ a.text }}
                        </p>
                    </template>
                    <template v-else>
                        <p
                            v-for="(a, i) in q.answers"
                            :key="ind + '_' + i"
                        >
                            {{ a.text }}
                        </p>
                    </template>
                    
                </td>
                <td :width="includeCorrectAnswers ? '22.5%' : '45%'" v-if="includeCorrectAnswers">
                    <p
                        v-for="(a, i) in q.correct"
                        :key="ind + '_' + i"
                    >
                        {{ a }}
                    </p>
                </td>
                <td width="8%">{{ Number(q.total_mark) }}</td>
                <td width="8%">{{ Number(q.mark) }}</td>
            </tr>
        </tbody>
    </table>
</template>
