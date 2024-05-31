<script setup>
import { ref, onMounted, } from "vue";
import labels from "@/locales/ru.js";
import Button from "primevue/button";
import toastService from "@/Services/toastService";
import FormField from "@/Components/Common/FormField.vue";

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
const labelgroup = 'videoconferences_fields'
const settingsData = ref({})

const fieldData = ref({
    name: {
        value: props?.data?.name || null,
        type: "string",
        label: labels[labelgroup].name.title,
        req: true,
    },
    date: {
        value: props?.data?.date || null,
        type: "datetime",
        label: labels[labelgroup].date.title,
        req: true,
    },
    test_id: {
        value: props?.data?.test_id || null,
        type: "dropdown",
        options: props.tests,
        req: false,
        label: labels[labelgroup].test.title,
        header: {
            addRoute: 'tests.new'
        },
    },
    studgroups: {
        value: props?.data?.studgroups || null,
        type: "multiselect",
        options: props.studgroups,
        req: true,
        label: labels[labelgroup].studgroups.title,
        header: {
            addRoute: 'admin.reference.studgroups'
        },
        max: 4,
    },
});

onMounted(() => {
    let st = props.data ? JSON.parse(props.data.settings) : {};

    labels[labelgroup].settings.values.forEach((item) => {
        settingsData.value[item.id] = {
            value: st[item.id] || item.default,
            type: item.type,
            label: item.name,
        };
    });

    console.log(settingsData.value);
});

const sendData = async () => {
    if (!validateFields()) {
        return;
    }

    const url = "/api/videoconferences" + (id.value ? `/${id.value}` : "");

    let data = prepareData();
    data.settings = JSON.stringify(prepareData(settingsData.value));

    console.log(data);
    try {
        let response;

        if (id.value) {
            response = await axios.patch(url, data);
        } else {
            response = await axios.post(url, data);
        }

        if (response.data.data.id) {
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
            toastService.showErrorToast(
                `Сохранение данных`,
                "Необходимо заполнить все обязательные поля!"
            );
            return false;
        }
    }
    return true;
}

const prepareData = (initial = fieldData.value) => {
    const data = {};
    for (const [code, field] of Object.entries(initial)) {
        if(code != 'test_id' || code != 'studgroups')
        data[code] = field.value;
    }
    return data;
}

const syncStudgroups = async () => {
    await axios.patch(`/api/videoconferences/${id.value}/studgroups/sync`, {resources: fieldData.value.studgroups.value})
}
</script>

<template>
    <form @submit.prevent="sendData" class="form d-grid gap-3">
        <div class="d-grid grid-col-2 gap-4">
            <div>
                <FormField v-for="(field, code) in fieldData"
                    :key="code"
                    :field="field"
                    class="mt-2"
                    :errors="errors" />    
            </div>
            <div>
                <h3>
                    {{ labels[labelgroup].settings.title }}
                </h3>
                <FormField v-for="(field, code) in settingsData"
                    :key="code"
                    :field="field"
                    class="mt-2"
                    :errors="[]" />  
            </div>
        </div>
        <Button @click="sendData" label="Сохранить" class="mt-3" />
    </form>
</template>