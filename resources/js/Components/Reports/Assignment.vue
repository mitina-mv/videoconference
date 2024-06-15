<script setup>
import { computed } from "vue";
import labels from "@/locales/ru.js";

const props = defineProps({
    test: [Object, null],
    assignment: [Object, null],
    test_settings: [Object, null],
    groups: [Object, null],
    user: [Object, null],
    metrics: [Object, null],
    inctuleHeader: {
        type: Boolean,
        default: true,
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

const settingsFields = computed(() => {
    let res = [];
    labels.test_fields.settings.values.forEach((element) => {
        if (props.test_settings.hasOwnProperty(element.id)) {
            let value = props.test_settings[element.id];
            if (element.type == "bool") {
                value = value ? "Да" : "Нет";
            }
            res.push({
                label: element.name,
                value: value,
            });
        }
    });

    return res;
});
</script>

<template>
    <h2 v-if="inctuleHeader">Результаты тестирования: {{ test.name }}</h2>
    <div class="d-grid grid-col-2 mb-3 gap-2">
        <div class="assignment-info">
            <h3>Основные данные</h3>
            <p v-for="(field, index) in testInfoFields" :key="index">
                <b>{{ field.label }}: </b>
                <span>{{ field.value }}</span>
            </p>
        </div>
        <div class="test-settings">
            <h3>Настройки тестирования</h3>
            <p v-for="(field, index) in settingsFields" :key="index">
                <b>{{ field.label }}: </b>
                <span>{{ field.value }}</span>
            </p>
        </div>
    </div>
    <h3>Сводная таблица</h3>
    <table class="mt-3" style="width: fit-content">
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
                    <td width="300px">
                        <a
                            :href="
                                route('report.detail', {
                                    testlog_id: student.testlog_id,
                                })
                            "
                            style="text-decoration: underline;"
                        >
                            {{ student.full_name }}
                        </a>
                    </td>
                    <td width="100">{{ student.mark }}</td>
                </tr>
            </template>
        </tbody>
    </table>
</template>
