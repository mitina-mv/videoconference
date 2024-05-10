<script setup>
import { ref, onMounted, computed } from "vue";
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

const props = defineProps({
    data: {
        type: Object,
        req: false,
    },
    themes: Array,
});

const id = ref(props?.data?.id || null);
const errors = ref({});

const sendData = () => {
    // if (
    //     !fieldData.value.text.value ||
    //     !fieldData.value.theme_id.value ||
    //     !fieldData.value.type.value
    // ) {
    //     toastService.showWarnToast(
    //         `Сохранение данных`,
    //         "Не все обязательные поля заполнены"
    //     );
    //     return;
    // }
}
</script>

<template>
    <form @submit.prevent="sendData" class="form d-grid gap-3 grid-col-2">
    </form>
</template>