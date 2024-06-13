<script setup>
import labels from "@/locales/ru.js";

const props = defineProps({
    test: [Object, null],
    assignment: [Object, null],
    test_settings: [Object, null],
    groups: [Array, null],
    user: [Object, null],
    metrics: [Object, null],
    inctuleHeader: {
        type: Boolean,
        default: true
    },
});
const testInfoFields = [
    {
        label: "Дисциплина",
        value: props.test.discipline,
    },
    {
        label: "Тема",
        value: props.test.theme,
    },
    {
        label: "Группы",
        value: Object.keys(props.groups).join(", "),
    },
    {
        label: "Дата проведения",
        value: props.assignment.date,
    },
    {
        label: "Преподаватель",
        value: props.user.full_name,
    },
    {
        label: "Средняя оценка",
        value: props.metrics.avg_mark,
    },
    {
        label: "Станндартное отклонение баллов",
        value: props.metrics.deviation_mark,
    },
];
</script>

<template>
    <h2 v-if="inctuleHeader">Результаты тестирования: {{ test.name }}</h2>
    <div class="d-grid grid-col-2 mb-3">
        <div class="assignment-info">
            <p v-for="(field, index) in testInfoFields" :key="index">
                <b>{{ field.label }}: </b>
                <span>{{ field.value }}</span>
            </p>
        </div>
        <div class="test-settings">
            <h4>Настройки тестирования</h4>
        </div>
    </div>
    <table class="mt-3" style="width: fit-content;">
        <thead>
            <tr>
                <th>ФИО студента</th>
                <th>Оценка</th>
            </tr>
        </thead>
        <tbody>
            <template v-for="(group, g_name) in groups" :key="g_name">
                <tr>
                    <th
                        colspan="2"
                        style="text-align: left; background-color: #ebeced"
                    >
                        {{ g_name }}
                    </th>
                </tr>
                <tr v-for="(student, ind) in group" :key="ind">
                    <td width="300px">{{ student.full_name }}</td>
                    <td width="100">{{ student.mark }}</td>
                </tr>
            </template>
        </tbody>
    </table>
</template>
