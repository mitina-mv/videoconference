<script setup>
import { ref, onMounted, computed, watch, onBeforeMount } from "vue";
import InputText from "primevue/inputtext";
import InputNumber from "primevue/inputnumber";
import labels from "@/locales/ru.js";
import Button from "primevue/button";
import toastService from "@/Services/toastService";
import InputSwitch from "primevue/inputswitch";
import Message from "primevue/message";
import Checkbox from "primevue/checkbox";
import FormField from "@/Components/Common/FormField.vue"

const props = defineProps({
    data: {
        type: Object,
        req: false,
    },
    disciplines: Array,
    userId: Number,
});

const id = ref(props?.data?.id || null);
const errors = ref({});
const questions = ref([]);
const questionSelected = ref([]);
const initQuestionSelected = ref([]);
const fieldData = ref({
    name: {
        value: props?.data?.name || null,
        class: "grid-self-col-1",
        label: labels.test_fields.name.title,
    },
    description: {
        value: props?.data?.description || null,
        class: "grid-self-col-1",
        type: "text",
        label: labels.test_fields.description.title,
    },
    discipline_id: {
        value: props?.data?.theme.discipline_id || null,
        type: "dropdown",
        label: labels.test_fields.discipline_id.title,
        options: props.disciplines,
        header: {
            addRoute: 'admin.reference.disciplines'
        },
    },
    theme_id: {
        value: props?.data?.theme_id || null,
        type: "dropdown",
        label: labels.test_fields.theme_id.title,
        options: [],
        header: {
            addRoute: 'admin.reference.themes'
        },
    },
});

const settingsData = ref({
    count_questions: {},
    is_random: {},
    fixed_questions: {},
});

onBeforeMount(() => {
    let settings = props?.data?.settings || {};

    labels.test_fields.settings.values.forEach((item) => {
        settingsData.value[item.id] = {
            value: Object.hasOwn(settings, item.id) ? settings[item.id] : item.default,
            type: item.type,
            title: item.name,
        };
    });
    questionSelected.value = settings?.question_ids || null;
    initQuestionSelected.value = settings?.question_ids || null;
});

onMounted(() => {
    if (fieldData.value.discipline_id.value) {
        fetchThemes();
    }
});

const sendData = async () => {
    const { name, description, theme_id } = fieldData.value;

    if (!name.value || !theme_id.value) {
        toastService.showWarnToast(
            `Сохранение данных`,
            "Не все обязательные поля заполнены"
        );
        return;
    }

    let stData = JSON.parse(JSON.stringify(settingsData.value));

    if(stData.time_limit.value == 0 || stData.time_limit.value == '' || stData.time_limit.value == null) {
        delete stData.time_limit;
    }

    const settings = Object.fromEntries(
        Object.entries(stData).map(([code, item]) => [
            code,
            item.value,
        ])
    );

    let themeIndex = fieldData.value.theme_id.options.findIndex(
        (t) => t.id == theme_id.value
    );
    let theme = fieldData.value.theme_id.options[themeIndex];

    if (
        (theme.questions_count < settings.count_questions &&
            !stData.fixed_questions.value) ||
        theme.questions_count == 0
    ) {
        toastService.showWarnToast(
            `Сохранение данных`,
            "Вопросов по данной теме недостаточно!"
        );
        return;
    }

    if (stData.fixed_questions.value) {
        if (questionSelected.value.length == 0) {
            toastService.showWarnToast(
                `Сохранение данных`,
                "Не выбрано ни одного вопроса"
            );
            return;
        }

        settings.question_ids = questionSelected.value;
        settings.count_questions = questionSelected.value.length;
    }

    const data = {
        name: name.value,
        description: description.value,
        theme_id: theme_id.value,
        settings: settings,
    };

    const url = `/api/tests${id.value ? `/${id.value}` : ""}`;

    try {
        const method = id.value ? "put" : "post";
        const response = await axios[method](url, data);
        toastService.showSuccessToast(
            `Сохранение данных`,
            "Успешно сохранили!"
        );
        id.value = response.data.data.id;
        errors.value = [];
    } catch (error) {
        toastService.showErrorToast(
            `Сохранение данных`,
            "Ошибка при отправке данных. Ознакомьтесь с ошибками и попробуйте заново."
        );
        errors.value = error.response.data.errors;
    }
};

const fetchThemes = async () => {
    try {
        const response = await axios.post("/api/themes/search", {
            filters: [
                {
                    field: "discipline_id",
                    operator: "=",
                    value: fieldData.value.discipline_id.value,
                },
            ],
            aggregates: [{ type: "count", relation: "questions" }],
        });
        fieldData.value.theme_id.options = response.data.data;
        fieldData.value.theme_id.value = response.data.data[0]?.id || null;
    } catch (error) {
        console.error("Error fetching themes:", error);
    }
};

const fetchQuestions = async () => {
    if (!fieldData.value.theme_id.value) {
        toastService.showWarnToast(
            `Сохранение данных`,
            "Укажите тему теста, чтобы запросить вопросы."
        );
        return;
    }
    if (!settingsData.value.fixed_questions.value) return;
    try {
        const response = await axios.post(`/api/questions/search`, {
            filters: [
                {
                    type: "or",
                    nested: [
                        {
                            field: "user_id",
                            operator: "=",
                            value: props.userId,
                        },
                        { field: "is_private", operator: "=", value: true },
                        {
                            field: "theme_id",
                            operator: "=",
                            value: fieldData.value.theme_id.value,
                        },
                    ],
                },
                {
                    type: "or",
                    nested: [
                        { field: "is_private", operator: "=", value: false },
                        {
                            field: "theme_id",
                            operator: "=",
                            value: fieldData.value.theme_id.value,
                        },
                    ],
                },
            ],
        });
        questions.value = response.data.data;
        if(props.data && fieldData.value.theme_id.value == props.data.theme_id)
            questionSelected.value = initQuestionSelected.value;
        else 
            questionSelected.value = [];
    } catch (error) {
        console.error("Error fetching questions:", error);
        questions.value = [];
        questionSelected.value = [];
    }
};

const watchFixQuestions = (newValue) => {
    if (newValue) {
        fetchQuestions();
    } else {
        questions.value = [];
        questionSelected.value = [];
    }
};

watch(() => fieldData.value.discipline_id.value, fetchThemes);
watch(() => settingsData.value.fixed_questions.value, watchFixQuestions);
watch(() => fieldData.value.theme_id.value, watchFixQuestions);
</script>

<template>
    <form @submit.prevent="sendData" class="form d-grid gap-3">
        <div class="main-block-form d-grid gap-3 grid-col-2">
            <FormField v-for="(field, code) in fieldData"
                :key="code"
                :field="field"
                :errors="errors" />
        </div>
        <div class="second-block-form d-grid gap-3 grid-col-3">
            <h4 class="grid-self-col-1">
                {{ labels.test_fields.settings.title }}
            </h4>
            <div
                class="form-control"
                v-for="(field, code) in settingsData"
                :key="code"
            >
                <label :for="code + '_input'">{{ field.title }}</label>

                <InputSwitch
                    v-if="field.type == 'bool'"
                    v-model="field.value"
                />

                <InputNumber
                    v-else-if="field.type == 'number'"
                    v-model="field.value"
                    inputId="minmax"
                    :min="field.min || 1"
                    :max="field.max || 100"
                />

                <InputText
                    v-else
                    :id="code + '_input'"
                    v-model="field.value"
                    type="text"
                />
            </div>

            <div
                class="form-control grid-self-col-1"
                v-if="settingsData.fixed_questions.value"
            >
                <h5>Выбор вопросов</h5>

                <Message severity="warn">
                    {{ labels.info_messages.test_fixed_question_warn }}
                </Message>

                <div v-if="questions.length == 0">
                    <b class="text-danger">
                        {{ labels.info_messages.test_fixed_question_error }}
                    </b><br />
                    <a :href="route('questions.new')">
                        <Button label="Добавить вопрос" severity="secondary" text />
                    </a>
                </div>
                <div
                    v-else
                    class="flex align-items-center gap-2"
                    v-for="question in questions"
                    :key="question.id"
                >
                    <Checkbox
                        v-model="questionSelected"
                        :inputId="`question_${question.id}`"
                        :value="question.id"
                        :disabled="question.correct_answers.length == 0"
                    />
                    <label
                        :for="`question_${question.id}`"
                        :class="
                            question.correct_answers.length == 0
                                ? 'text-danger'
                                : ''
                        "
                    >
                            {{ question.text }}
                            <span class="text-gray"> (Сложность {{ question.complexity_percent }})</span>
                    </label>
                </div>
            </div>
        </div>
        <div class="form-footer mt-2">
            <Button @click="sendData" label="Сохранить"> </Button>
        </div>
    </form>
</template>
