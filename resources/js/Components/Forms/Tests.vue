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
    disciplines: Array,
});

const id = ref(props?.data?.id || null);
const errors = ref({});
const themes = ref([]);
const fieldData = ref({
    name: {
        value: props?.data?.name || null,
    },
    description: {
        value: props?.data?.description || null,
        type: "text",
    },
    disciplines_id: {
        value: props?.data?.theme.disciplines_id || null,
        type: "dropdown",
        options: props.disciplines,
    },
    theme_id: {
        value: props?.data?.theme_id || null,
        type: "dropdown",
        options: themes.value,
    },
    is_random: {
        value: props?.data?.is_private || false,
        type: "bool",
    },
});

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
};
</script>

<template>
    <form @submit.prevent="sendData" class="form d-grid gap-3">
        <div class="main-block-form d-grid gap-3 grid-col-2">
            <div
                class="form-control"
                v-for="(field, code) in fieldData"
                :key="code"
                :class="field.type == 'text' ? 'grid-self-col-1' : ''"
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
        <div class="second-block-form d-grid gap-3 grid-col-2">
            
        </div>
        <div class="form-footer mt-2">
            <Button @click="sendData" label="Сохранить"> </Button>
        </div>
    </form>
</template>
