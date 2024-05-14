<script setup>
import { ref, onMounted, } from "vue";
import labels from "@/locales/ru.js";
import Button from "primevue/button";
import toastService from "@/Services/toastService";
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

onMounted(() => {
    let testlogs = props.data?.testlogs || null;
    if(testlogs) {
        testlogs.forEach(element => {
            selectedStudents.value.push(element.user_id)
        });
    }
})

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
        toastService.showErrorToast(
            `Сохранение данных`,
            error.response.data.message || "Ошибка при сохранении данных. Пожалуйста, попробуйте еще раз."
        );
    }
}

const validateFields = () => {
    for (const field of Object.values(fieldData.value)) {
        if (field.req && !field.value) {
            errors.value[field.label] = "Поле обязательно для заполнения";
            return false;
        }
    }
    return true;
}

const prepareData = () => {
    const data = {};
    for (const [code, field] of Object.entries(fieldData.value)) {
        data[code] = field.value;
    }
    return data;
}

const syncStudgroups = async () => {
    try {
        let testlogs = props.data?.testlogs || [];

        let testlogIds = testlogs.map(testlog => testlog.user_id);
        let selectedStudentIds = selectedStudents.value;

        let idsToDelete = testlogIds.filter(id => !selectedStudentIds.includes(id));
        let idsToAdd = selectedStudentIds.filter(id => !testlogIds.includes(id));

        let testlogsToDelete = testlogs.filter(testlog => idsToDelete.includes(testlog.user_id));
        let testlogIdsDelete = testlogsToDelete.map(testlog => testlog.id);

        testlogIdsDelete.forEach(async (id) => {
            await axios.delete(`/api/testlogs/${id}`);
        })

        let requestData = idsToAdd.map(user_id => ({ user_id }));
        if(requestData.length > 0)
            await axios.post(`/api/assignments/${id.value}/testlogs/batch`, { resources: requestData });

    } catch (error) {
        toastService.showErrorToast(
            `Сохранение данных`,
            "Ошибка при синхронизации участников. Пожалуйста, попробуйте еще раз."
        );
    }
}

</script>

<template>
    <form @submit.prevent="sendData" class="form d-grid gap-3">
        <div class="d-grid grid-col-2 gap-3">
            <div class="studgroups">
                <h3>Участники</h3>
                <SelectedStudents :groups="studgroups" v-model="selectedStudents" />
            </div>
            <div>
                <FormField v-for="(field, code) in fieldData"
                    :key="code"
                    :field="field"
                    class="mt-2"
                    :errors="errors" />
                <Button @click="sendData" label="Сохранить" class="mt-3" />
            </div>
        </div>
    </form>
</template>