<script setup>
import { ref, onMounted } from "vue";
import InputText from "primevue/inputtext";
import InputNumber from "primevue/inputnumber";
import MultiSelect from "primevue/multiselect";
import Dropdown from "primevue/dropdown";
import labels from '@/locales/ru.js';
import Button from "primevue/button";
import toastService from "@/Services/toastService";
import InputSwitch from 'primevue/inputswitch';
import Textarea from 'primevue/textarea';

const props = defineProps({
    data: {
        type: Object,
        req: false
    },
    themes: Array,
})

const id = ref(props?.data?.id || null);
const errors = ref({});
const fieldData = ref({
    is_private: {
        value: props?.data?.is_private || false,
        type: 'bool'
    },
    text: {
        value: props?.data?.text || null,
        type: 'text'
    },
    theme_id: {
        value: props?.data?.theme_id || null,
        type: 'dropdown',
        options: props.themes
    },
    type: {
        value: props?.data?.type || null,
        type: 'dropdown',
        options: labels.questions_fields.type.values
    },
    mark: {
        value: props?.data?.mark || null,
        type: 'number',
        min: 1
    },
})

const sendData = () => {
    if (!fieldData.value.name || !fieldData.value.lastname || !fieldData.value.email) {
        toastService.showWarnToast(`Сохранение данных`, "Не все обязательные поля заполнены")
        return;
    }

    const url = '/api/questions' + (id.value ? `/${id.value}` : '');

    axios({
        method: id.value != null ? 'put' : 'post',
        url: url,
        data: fieldData.value
    })
    .then(response => {

    })
    .catch(error => {
        toastService.showErrorToast(`Сохранение данных`, "Ошибка при отправке данных. Ознакомьтесь с ошибками и попробуйте заново.")
        errors.value = error.response.data.errors
    });
}
</script>

<template>
    <form
        @submit.prevent="sendData"
        class="form d-grid gap-3 grid-col-2"
    >
    <div class="form-control" v-for="(field, code) in fieldData" :key="code">
        <label :for="code + '_input'">{{ labels.questions_fields[code].title }}</label>
        <InputSwitch v-if="field.type == 'bool'" v-model="fieldData[code].value" :invalid='errors[code] ? true : false' />

        <InputNumber v-else-if="field.type == 'number'" v-model="fieldData[code].value" inputId="minmax" :min="field.min" :max="field.min || 100" />

        <Textarea v-else-if="field.type == 'text'" v-model="fieldData[code].value" />

        <Dropdown :id="field.code" v-else-if="field.type && field.type == 'dropdown'" v-model="fieldData[code].value" :options="field.options" optionLabel="name" optionValue="id" filter  />

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

    <div class='form-footer mt-2'>
        <Button @click="sendData" label="Сохранить">
        </Button>
    </div>
    </form>
</template>