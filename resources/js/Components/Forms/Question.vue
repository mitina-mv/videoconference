<script setup>
import { ref, onMounted } from "vue";
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

const props = defineProps({
    data: {
        type: Object,
        req: false,
    },
    themes: Array,
});

const id = ref(props?.data?.id || null);
const errors = ref({});
const fieldData = ref({
    text: {
        value: props?.data?.text || null,
        type: "text",
    },
    theme_id: {
        value: props?.data?.theme_id || null,
        type: "dropdown",
        options: props.themes,
    },
    type: {
        value: props?.data?.type || null,
        type: "dropdown",
        options: labels.questions_fields.type.values,
    },
    mark: {
        value: Number(props?.data?.mark) || 1,
        type: "number",
        min: 1,
    },
    is_private: {
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
        type: 'text',
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
    for(let code in fieldData.value) {
        data[code] = fieldData.value[code].value
    }

    const url = "/api/questions" + (id.value ? `/${id.value}` : "");

    axios({
        method: id.value != null ? "put" : "post",
        url: url,
        data: data,
    })
        .then((response) => {
            id.value = response.data.data.id
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
    if(!id.value) {
        toastService.showWarnToast(
                `Создание вопроса`,
                "Нельзя создать ответ для несохранненого вопроса"
            );
        return;
    }
    answersData.value.push({
        ...answerNull,
        question_id: id.value
    })
}

const deleteAnswer = (index) => {
    let item = answersData.value[index]
    if(item.id) {
        axios.delete('/api/answers/' + item.id)
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
}

const sendAnswer = (index) => {
    let item = answersData.value[index]
    if (!item.name) {
        toastService.showWarnToast(
            `Сохранение ответа`,
            "Не все обязательные поля заполнены"
        );
        return;
    }
    const url = "/api/answers" + (item.id ? `/${item.id}` : "");

    axios({
        method: item.id != null ? "put" : "post",
        url: url,
        data: item,
    })
        .then((response) => {
            answersData.value[index].id = response.data.data.id
        })
        .catch((error) => {
            toastService.showErrorToast(
                `Сохранение ответа`,
                "Ошибка при отправке данных. Ознакомьтесь с ошибками и попробуйте заново."
            );
            errors.value = error.response.data.errors;
        });
}
</script>

<template>
    <form @submit.prevent="sendData" class="form d-grid gap-3 grid-col-2">
        <div
            class="form-control"
            v-for="(field, code) in fieldData"
            :key="code"
            :class="field.type == 'text' ? 'grif-self-col-1' : ''"
        >
            <label :for="code + '_input'">{{
                labels.questions_fields[code].title
            }}</label>
            <InputSwitch
                v-if="field.type == 'bool'"
                v-model="fieldData[code].value"
                :invalid="errors[code] ? true : false"
            />

            <InputNumber
                v-else-if="field.type == 'number'"
                v-model="fieldData[code].value"
                inputId="minmax"
                :min="field.min"
                :max="field.max || 100"
            />

            <Textarea
                v-else-if="field.type == 'text'"
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
                    @click="addAnswerForm"
                />
            </template>
        </Toolbar>

        <div class=" d-grid grid-col-3 gap-4" v-if="answersData.length > 0">
        <form v-for="(answer, index) in answersData" :key="index">
            <b>Ответ {{ index + 1 }}</b>
            <div
                class="form-control"
                v-for="(field, code) in answerField"
                :key="code + '_' + index"
            >
                <label :for="code + '_input' + index">{{ field.title }}</label>
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

            <div class="buttons-group mt-3">
                <Button
                    @click="sendAnswer(index)"
                    :label="answer.id ? 'Сохранить' : 'Создать'"
                    size="small"
                    severity="success"
                    outlined
                />
                <Button
                    label="Удалить"
                    size="small"
                    severity="danger"
                    outlined
                    @click="deleteAnswer(index)"
                />
            </div>
        </form>
        </div>
        <div v-else>
            <span v-if="!id">Ответы сможете добавить после создания вопроса</span>
            <span v-else>Добавьте варианты ответа</span>
        </div>
    </div>
</template>
