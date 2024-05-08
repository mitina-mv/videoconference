<script setup>
import { ref, onMounted } from "vue";
import InputText from "primevue/inputtext";
import InputNumber from "primevue/inputnumber";
import MultiSelect from "primevue/multiselect";
import Dropdown from "primevue/dropdown";
import labels from '@/locales/ru.js';
import Button from "primevue/button";
import toastService from "@/Services/toastService";

const props = defineProps({
    data: {
        type: Object,
        req: false
    },
})

const id = ref(props?.data?.id || null);
const errors = ref({});
const fieldData = ref({
    is_private: props?.data?.is_private || null,
    text: props?.data?.text || null,
    theme_id: props?.data?.theme_id || null,
    type: props?.data?.type || null,
    mark: props?.data?.mark || null,
})

const sendData = () => {
    if (!fieldData.value.name || !fieldData.value.lastname || !fieldData.value.email) {
        toastService.showInfoToast(`Сохранение данных`, "Не все обязательные поля заполнены")
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
    </form>
</template>