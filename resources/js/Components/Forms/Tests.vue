<script setup>
import { ref, onMounted, computed, watch, onBeforeMount } from "vue";
import InputText from "primevue/inputtext";
import InputNumber from "primevue/inputnumber";
import MultiSelect from "primevue/multiselect";
import Dropdown from "primevue/dropdown";
import labels from "@/locales/ru.js";
import Button from "primevue/button";
import toastService from "@/Services/toastService";
import InputSwitch from "primevue/inputswitch";
import Textarea from "primevue/textarea";
import Toolbar from "primevue/toolbar";
import Message from "primevue/message";
import Checkbox from "primevue/checkbox";

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
const fieldData = ref({
    name: {
        value: props?.data?.name || null,
        class: "grid-self-col-1",
    },
    description: {
        value: props?.data?.description || null,
        class: "grid-self-col-1",
        type: "text",
    },
    discipline_id: {
        value: props?.data?.theme.discipline_id || null,
        type: "dropdown",
        options: props.disciplines,
    },
    theme_id: {
        value: props?.data?.theme_id || null,
        type: "dropdown",
        options: [],
    },
});

const settingsData = ref({
    count_questions: {},
    is_random: {},
    fixed_questions: {},
});

onBeforeMount(() => {
    let settings = JSON.parse(props.data?.settings) || null;
    labels.test_fields.settings.values.forEach((item) => {
        settingsData.value[item.id] = {
            value: settings[item.id] || item.default,
            type: item.type,
            title: item.name,
        };
    });
});

onMounted(() => {
    if (fieldData.value.discipline_id.value) {
        fetchThemes();
    }
    console.log(fieldData.value);
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

    const settings = Object.fromEntries(
        Object.entries(settingsData.value).map(([code, item]) => [
            code,
            item.value,
        ])
    );

    const data = {
        name: name.value,
        description: description.value,
        theme_id: theme_id.value,
        settings: JSON.stringify(settings),
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
    } catch (error) {
        console.error("Error fetching questions:", error);
    }
};

watch(() => fieldData.value.discipline_id.value, fetchThemes);

watch(
    () => settingsData.value.fixed_questions.value,
    (newValue) => {
        if (newValue) {
            fetchQuestions();
        } else {
            questions.value = [];
        }
    }
);
</script>

<template>
    <form @submit.prevent="sendData" class="form d-grid gap-3">
        <div class="main-block-form d-grid gap-3 grid-col-2">
            <div
                class="form-control"
                v-for="(field, code) in fieldData"
                :key="code"
                :class="field.class || ''"
            >
                <label :for="code + '_input'">{{
                    labels.test_fields[code].title
                }}</label>

                <Textarea
                    v-if="field.type == 'text'"
                    v-model="fieldData[code].value"
                />

                <Dropdown
                    :id="field.code"
                    v-else-if="field.type && field.type == 'dropdown'"
                    v-model="fieldData[code].value"
                    :options="field.options"
                    optionLabel="name"
                    optionValue="id"
                    filter
                />

                <InputText
                    v-else
                    :id="code + '_input'"
                    v-model="fieldData[code].value"
                    type="text"
                    :class="{ 'p-invalid': errors[`${code}`] }"
                />
                <small class="p-error" v-if="errors[code]">{{
                    errors[code] ? errors[code][0] : "&nbsp;"
                }}</small>
            </div>
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
                <h3>Выберите вопросы:</h3>
                <div class="flex align-items-center" 
                        v-for="question in questions"
                        :key="question.id">
                    <Checkbox
                        v-model="question.selected"
                        :inputId="`question_${question.id}`"
                        :value="question.id"
                    />
                    <label
                        :for="`question_${question.id}`"
                        >{{ question.text }}</label
                    >
                </div>
            </div>
        </div>
        <div class="form-footer mt-2">
            <Button @click="sendData" label="Сохранить"> </Button>
        </div>
    </form>
</template>
