<script setup>
// import { ref, onMounted } from "vue";

const props = defineProps({
    test: [Object, null],
    vc: Object,
    groups: Object,
    inctuleHeader: {
        type: Boolean,
        default: true
    },
});

const vcInfoFields = [
    {
        label: "Название",
        value: props.vc.name,
    },
    {
        label: "Дата проведения",
        value: props.vc.date,
    },
    {
        label: "Тип",
        value: props.vc.type,
    },
    {
        label: "Преподаватель",
        value: props.vc.user,
    },
    {
        label: "Количество проверок присуствия",
        value: props.vc.count_check,
    },
    {
        label: "Группы",
        value: props.vc.studgroups.join(", "),
    },
]


const testInfoFields = [
    {
        label: "Название тесат",
        value: props.test.name,
    },
    {
        label: "Дисциплина",
        value: props.test.discipline,
    },
    {
        label: "Тема",
        value: props.test.theme,
    },
    {
        label: "Средняя оценка",
        value: props.test.avg_mark,
    },
    {
        label: "Станндартное отклонение баллов",
        value: props.test.deviation_mark,
    },
];
</script>

<template>
    <h2 v-if="inctuleHeader">Результаты конференции: {{ vc ? vc.name : 'ошибка' }}</h2>
    <div class="d-grid grid-col-2 mb-3 gap-2">
        <div class="assignment-info">
            <h3>Основные данные</h3>
            <p v-for="(field, index) in vcInfoFields" :key="index">
                <b>{{ field.label }}: </b>
                <span>{{ field.value }}</span>
            </p>
        </div>
        <div class="test-settings">
            <h3>Настройки тестирования</h3>
            <div  v-if="test">
                <p v-for="(field, index) in testInfoFields" :key="index">
                    <b>{{ field.label }}: </b>
                    <span>{{ field.value }}</span>
                </p>
            </div>
            <div v-else>Тестирование не проводилось</div>
        </div>
    </div>
    <h3>Сводная таблица</h3>
    <table class="mt-3" style="width: fit-content;">
        <thead>
            <tr>
                <th>ФИО студента</th>
                <th>Оценка</th>
                <th>Коэф. вовлеченности</th>
            </tr>
        </thead>
        <tbody>
            <template v-for="(group, g_name) in groups" :key="g_name">
                <tr>
                    <th
                        colspan="3"
                        style="text-align: left; background-color: #ebeced"
                    >
                        {{ g_name }}
                    </th>
                </tr>
                <tr v-for="(student, ind) in group" :key="ind">
                    <td width="300px">{{ student.full_name }}</td>
                    <td width="100">{{ student.mark }}</td>
                    <td>{{ student.engagement }}</td>
                </tr>
            </template>
        </tbody>
    </table>
</template>