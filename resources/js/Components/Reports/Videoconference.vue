<script setup>
import { computed } from "vue";

const props = defineProps({
    test: [Object, null],
    vc: Object,
    groups: Object,
    inctuleHeader: {
        type: Boolean,
        default: true,
    },
    includeComments: {
        type: Boolean,
        default: false,
    },
    includeHrefDetail: {
        type: Boolean,
        default: false,
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
];

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

const comments = computed(() => {
    let comments = [];
    if (props.vc.count_check == 0) {
        comments.push(
            "Вы не использовали проверки присуствия, поэтому оценка вовлеченности не может в полной мере отражать реальные итоги занятия. При расчете баллы за проверки присутствия проставлены на максимум для каждого студента. Проверки присуствия помогают понять, не потеряна ли активная аудитория. Попробуйте в следующий раз!"
        );
    }

    if (props.test == null) {
        comments.push(
            "Вы не использовали интерактиные опросы, поэтому оценка вовлеченности не может в полной мере отражать реальные итоги занятия. При расчете баллы за тестирование проставлены на максимум для каждого студента. Интерактивные опросы позволяют сильнее вовлечь аудиторию в процесс обучения, попробуйте!"
        );
    }

    if (props.vc.flag_mes) {
        comments.push(
            "Вы не использовали чат, поэтому оценка вовлеченности не может в полной мере отражать реальные итоги занятия. При расчете баллы за участие в чате проставлены на максимум для каждого студента. Чат - инструмент для моментального взаимодействия с аудиторией. Может быть очень полезным!"
        );
    }

    return comments;
});
</script>

<template>
    <h2 v-if="inctuleHeader">
        Результаты конференции: {{ vc ? vc.name : "ошибка" }}
    </h2>
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
            <div v-if="test">
                <p v-for="(field, index) in testInfoFields" :key="index">
                    <b>{{ field.label }}: </b>
                    <span>{{ field.value }}</span>
                </p>
            </div>
            <div v-else>Тестирование не проводилось</div>
        </div>
    </div>
    <h3>Сводная таблица</h3>
    <table class="mt-3" style="width: fit-content">
        <thead>
            <tr>
                <th>ФИО студента</th>
                <th>Оценка</th>
                <th>Коэф. вовлеченности</th>
                <th v-if="props.vc.count_check">Кол-во пройд. ПП</th>
            </tr>
        </thead>
        <tbody>
            <template v-for="(group, g_name) in groups" :key="g_name">
                <tr>
                    <th
                        :colspan="props.vc.count_check ? 4 : 3"
                        style="text-align: left; background-color: #ebeced"
                    >
                        {{ g_name }}
                    </th>
                </tr>
                <tr v-for="(student, id) in group" :key="id">
                    <td width="300px">
                        <a
                            v-if="includeHrefDetail"
                            :href="
                                route('report.detail', {
                                    testlog_id: student.testlog_id,
                                })
                            "
                            style="text-decoration: underline;"
                        >
                            {{ student.full_name }}
                        </a>
                        <span v-else> {{ student.full_name }}</span>
                    </td>
                    <td width="100">{{ student.mark }}</td>
                    <td>{{ student.engagement }}</td>
                    <td v-if="props.vc.count_check" width="150">
                        {{ student.count_check }}
                    </td>
                </tr>
            </template>
        </tbody>
    </table>
    <div v-if="comments && includeComments">
        <h3 class="mt-3 mb-2">Комментарии</h3>
        <p v-for="(comment, ind) in comments" :key="ind">
            {{ comment }}
        </p>
    </div>
</template>
