<script setup>
import { ref, onMounted, computed, watch, onBeforeMount } from "vue";
import labels from "@/locales/ru.js";
import Button from "primevue/button";
import toastService from "@/Services/toastService";
import Toolbar from "primevue/toolbar";
import Message from "primevue/message";
import FormField from "@/Components/Common/FormField.vue";
import SelectedStudents from "@/Components/Common/SelectedStudents.vue";

const props = defineProps({
    data: {
        type: Object,
        req: false,
    },
    tests: Array,
    studgroups: Array,
    userId: Number,
});

const id = ref(props?.data?.id || null);
const errors = ref({});
const selectedStudents = ref([]);

const fieldData = ref({
    date: {
        value: props?.data?.date || null,
        type: "datetime",
        label: labels.assignments_fields.date.title,
        req: true,
    },
    test_id: {
        value: props?.data?.test_id || null,
        type: "dropdown",
        options: props.tests,
        req: true,
        label: labels.assignments_fields.test_id.title,
        header: {
            addRoute: 'tests.new'
        },
    },
});

const sendData = async () => {
    if (!validateFields()) {
        return;
    }

    const url = "/api/assignments" + (id.value ? `/${id.value}` : "");

    try {
        let response;

        if (id.value) {
            response = await axios.patch(url, prepareData());
        } else {
            response = await axios.post(url, prepareData());
        }

        if (response.data.data.id) {
            console.log(response.data.data.id);
            id.value = response.data.data.id;
            await syncStudgroups();
        }

        toastService.showSuccessToast(
            `Сохранение данных`,
            "Данные успешно сохранены!"
        );
    } catch (error) {
        console.error("Ошибка при сохранении данных:", error);
        toastService.showErrorToast(
            `Сохранение данных`,
            "Ошибка при сохранении данных. Пожалуйста, попробуйте еще раз."
        );
    }
}

// Метод для проверки заполненности полей
const validateFields = () => {
    for (const field of Object.values(fieldData.value)) {
        if (field.req && !field.value) {
            errors.value[field.label] = "Поле обязательно для заполнения";
            return false;
        }
    }
    return true;
}

// Метод для подготовки данных перед отправкой на сервер
const prepareData = () => {
    const data = {};
    for (const [code, field] of Object.entries(fieldData.value)) {
        data[code] = field.value;
    }
    // Добавляем выбранных студентов
    // data.selectedStudents = selectedStudents.value;
    return data;
}

// Метод для отправки запроса для текущего отношения
const syncStudgroups = async () => {
    try {
        selectedStudents.value.forEach(async (user_id) => {
            await axios.post(`/api/assignments/${id.value}/testlogs/associate`, { "related_key": user_id });
        })
    } catch (error) {
        console.error("Ошибка при синхронизации студгрупп:", error);
        toastService.showErrorToast(
            `Сохранение данных`,
            "Ошибка при синхронизации студгрупп. Пожалуйста, попробуйте еще раз."
        );
    }
}

</script>

<template>
    <form @submit.prevent="sendData" class="form d-grid gap-3">
        <div class="d-grid grid-col-2 gap-3">
            <div class="studgroups">
                <SelectedStudents :groups="studgroups" v-model="selectedStudents" />
            </div>
            <div>
                <FormField v-for="(field, code) in fieldData"
                    :key="code"
                    :field="field"
                    :errors="errors" />
                <Button @click="sendData" label="Сохранить" class="mt-3" />
            </div>
        </div>
    </form>
</template>