<script setup>
import { ref, onMounted, computed, watch } from "vue";
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
import FormField from "@/Components/Common/FormField.vue"

const props = defineProps({
    data: {
        type: Object,
        req: false,
    },
    themes: Array,
    disciplines: Array,
});

const id = ref(props?.data?.id || null);
const errors = ref({});
const fieldData = ref({
    text: {
        value: props?.data?.text || null,
        label: labels.questions_fields.text.title,
        type: "text",
        class: 'grid-self-col-1'
    },
    discipline_id: {
        value: props?.data?.theme?.discipline_id || null,
        type: "dropdown",
        label: labels.questions_fields.discipline_id.title,
        options: props.disciplines,
    },
    theme_id: {
        value: props?.data?.theme_id || null,
        type: "dropdown",
        label: labels.questions_fields.theme_id.title,
        options: props.themes,
    },
    type: {
        value: props?.data?.type || null,
        type: "dropdown",
        label: labels.questions_fields.type.title,
        options: labels.questions_fields.type.values,
    },
    mark: {
        value: Number(props?.data?.mark) || 1,
        type: "number",
        label: labels.questions_fields.mark.title,
        min: 1,
    },
    is_private: {
        label: labels.questions_fields.is_private.title,
        value: props?.data?.is_private || false,
        type: "bool",
    },
});

const answersData = ref([]);
let answerNull = {
    status: false,
    name: null,
};
const answerField = {
    status: {
        code: "status",
        title: labels.answers_fields.status.title,
        type: "bool",
    },
    name: {
        code: "name",
        title: labels.answers_fields.name.title,
        type: "text",
    },
};

onMounted(() => {
    if (props.data && props.data.answers) {
        answersData.value = props.data.answers;
    }
});

const sendData = () => {
    if (
        !fieldData.value.text.value ||
        !fieldData.value.theme_id.value ||
        !fieldData.value.type.value
    ) {
        toastService.showWarnToast(
            `Сохранение данных`,
            "Не все обязательные поля заполнены"
        );
        return;
    }

    let data = {};
    for (let code in fieldData.value) {
        data[code] = fieldData.value[code].value;
    }

    const url = "/api/questions" + (id.value ? `/${id.value}` : "");

    axios({
        method: id.value != null ? "put" : "post",
        url: url,
        data: data,
    })
        .then((response) => {
            toastService.showSuccessToast(
                `Сохранение данных`,
                "Успешно сохранили!"
            );
            id.value = response.data.data.id;
            errors.value = [];
        })
        .catch((error) => {
            toastService.showErrorToast(
                `Сохранение данных`,
                "Ошибка при отправке данных. Ознакомьтесь с ошибками и попробуйте заново."
            );
            errors.value = error.response.data.errors;
        });
};

const addAnswerForm = () => {
    if (!id.value) {
        toastService.showWarnToast(
            `Создание вопроса`,
            "Нельзя создать ответ для несохранненого вопроса"
        );
        return;
    }
    answersData.value.push({
        ...answerNull,
        question_id: id.value,
    });
};

const deleteAnswer = (index) => {
    let item = answersData.value[index];
    if (item.id) {
        axios
            .delete("/api/answers/" + item.id)
            .then((response) => {
                answersData.value.splice(index, 1);
            })
            .catch((error) => {
                toastService.showErrorToast(
                    `Удаление ответа`,
                    "Ошибка при отправке данных. Ознакомьтесь с ошибками и попробуйте заново."
                );
                errors.value = error.response.data.errors;
            });
    } else {
        answersData.value.splice(index, 1);
    }
};

const showErrorMessage = computed(() => {
    const correctAnswersCount = answersData.value.filter(
        (answer) => answer.status === true
    ).length;

    if (fieldData.value.type.value === "single") {
        return correctAnswersCount > 1 || correctAnswersCount == 0;
    } else if (fieldData.value.type.value === "text") {
        return correctAnswersCount != answersData.value.length
    } else {
        return correctAnswersCount == 0
    }
});

const filterThemes = () => {
    if(fieldData.value.discipline_id.value) {
        let id = fieldData.value.discipline_id.value;
        let themes = []
        props.themes.forEach(a => {
            if (a.discipline_id == id) {
                themes.push(a)
            }
        });

        fieldData.value.theme_id.options = themes;
        fieldData.value.theme_id.value = themes.length > 0 ? themes[0].id : null;
    }
}

const saveAnswers = () => {
    let allFilled = answersData.value.every(answer => answer.name && answer.status !== undefined);

    if (!allFilled) {
        toastService.showWarnToast(
            `Сохранение данных`,
            "Не все обязательные поля заполнены"
        );
        return;
    }

    let newAnswers = [];
    let existingAnswers = {};

    answersData.value.forEach(answer => {
        if(!answer.id) {
            newAnswers.push(answer)
        } else {
            existingAnswers[answer.id] = answer
        }
    })

    const requests = [];

    if (newAnswers.length > 0) {
        requests.push(
            axios.post('/api/answers/batch', {resources: newAnswers})
                .then(response => {
                    response.data.data.forEach((newAnswer, index) => {
                        const originalIndex = answersData.value.findIndex(answer => !answer.id && answer.name === newAnswers[index].name);
                        answersData.value[originalIndex] = newAnswer;
                    });
                })
        );
    }

    if (Object.keys(existingAnswers).length > 0) {
        requests.push(
            axios.patch('/api/answers/batch', {resources: existingAnswers})
                .then(response => {
                    console.log(newAnswers, existingAnswers);

                })
        );
    }

    Promise.all(requests)
        .then(() => {
            toastService.showSuccessToast(
                `Сохранение ответов`,
                "Все ответы успешно сохранены!"
            );
        })
        .catch(error => {
            toastService.showErrorToast(
                `Сохранение данных`,
                "Ошибка при отправке данных. Ознакомьтесь с ошибками и попробуйте заново."
            );
            errors.value = error.response.data.errors;
        });
};


watch(() => fieldData.value.discipline_id.value, filterThemes);
</script>

<template>
    <form @submit.prevent="sendData" class="form d-grid gap-3 grid-col-2">
        <FormField v-for="(field, code) in fieldData"
                :key="code"
                :field="field"
                :errors="errors" />

        <div class="form-footer mt-2">
            <Button @click="sendData" label="Сохранить"> </Button>
        </div>
    </form>

    <div class="answers-block mt-4">
        <Toolbar class="mb-4">
            <template #start>
                <h4 class="m-0">{{ labels.page_titles.answers }}</h4>
            </template>
            <template #end>
                <Button
                    :label="'Добавить '"
                    icon="pi pi-plus"
                    class="mr-3"
                    @click="addAnswerForm"
                />
                <Button
                    label="Сохранить все"
                    icon="pi pi-check"
                    severity="success"
                    @click="saveAnswers"
                />
            </template>
        </Toolbar>

        <Message v-if="showErrorMessage" :closable="false" severity="warn"
            >{{ labels.info_messages.answer_error }}</Message
        >

        <div class="d-grid grid-col-3 gap-4" v-if="answersData.length > 0">
            <form v-for="(answer, index) in answersData" :key="index">
                <header class="d-flex flex-between">
                    <b>Ответ {{ index + 1 }}</b>
                    <Button
                        icon="pi pi-times"
                        size="small"
                        severity="danger"
                        outlined
                        rounded
                        @click="deleteAnswer(index)"
                    />
                </header>
                <div
                    class="form-control"
                    v-for="(field, code) in answerField"
                    :key="code + '_' + index"
                >
                    <label :for="code + '_input' + index">{{
                        field.title
                    }}</label>
                    <InputSwitch
                        v-if="field.type == 'bool'"
                        v-model="answer[code]"
                        :invalid="errors[code] ? true : false"
                    />

                    <Textarea
                        v-else-if="field.type == 'text'"
                        v-model="answer[code]"
                    />

                    <InputText
                        v-else
                        :id="code + '_input' + index"
                        v-model="answer[code]"
                        type="text"
                        :class="{ 'p-invalid': errors[`${code}`] }"
                    />
                    <small class="p-error" v-if="errors[code]">{{
                        errors[code] ? errors[code][0] : "&nbsp;"
                    }}</small>
                </div>
            </form>
        </div>
        <div v-else>
            <span v-if="!id"
                >Ответы сможете добавить после создания вопроса</span
            >
            <span v-else>Добавьте варианты ответа</span>
        </div>
    </div>
</template>
