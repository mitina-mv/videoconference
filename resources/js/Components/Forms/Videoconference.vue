<script setup>
import { ref, onMounted } from "vue";
import labels from "@/locales/ru.js";
import Button from "primevue/button";
import toastService from "@/Services/toastService";
import FormField from "@/Components/Common/FormField.vue";
import { data } from "autoprefixer";

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
const labelgroup = "videoconferences_fields";
const settingsData = ref({});
const assignment_id = ref(props?.data?.assignment?.id || null);

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
            addRoute: "tests.new",
        },
    },
    studgroups: {
        value: props?.data?.studgroups || null,
        type: "multiselect",
        options: props.studgroups,
        req: true,
        label: labels[labelgroup].studgroups.title,
        header: {
            addRoute: "admin.reference.studgroups",
        },
        max: 4,
    },
});

onMounted(() => {
    let st = props?.data?.settings || {};

    labels[labelgroup].settings.values.forEach((item) => {
        let value = {
            value: Object.hasOwn(st, item.id) ? st[item.id] : item.default,
            type: item.type,
            label: item.name,
        };

        if(value.type == 'dropdown') {
            value.options = item.values
        }

        settingsData.value[item.id] = value
    });

    if (props.data && props.data.studgroups) {
        fieldData.value.studgroups.value = props.data.studgroups.map(
            (a) => a.id
        );
    }

    if (assignment_id.value) {
        fieldData.value.test_id.value = props.data.assignment.test_id;
    }

    console.log(fieldData.value);
});

const sendData = async () => {
    if (!validateFields()) {
        return;
    }

    const url = "/api/videoconferences" + (id.value ? `/${id.value}` : "");

    let data = prepareData();
    data.settings = prepareData(settingsData.value);

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
            await syncAssignmentTest();
        }

        toastService.showSuccessToast(
            `Сохранение данных`,
            "Данные успешно сохранены!"
        );
    } catch (error) {
        let message = error?.response.data.message || error;

        toastService.showErrorToast(
            `Сохранение данных`,
            message ||
                "Ошибка при сохранении данных. Пожалуйста, попробуйте еще раз."
        );
    }
};

const validateFields = () => {
    for (const field of Object.values(fieldData.value)) {
        if (field.req && (!field.value || field.value.length == 0)) {
            toastService.showErrorToast(
                `Сохранение данных`,
                "Необходимо заполнить все обязательные поля!"
            );
            return false;
        }
    }
    return true;
};

const prepareData = (initial = fieldData.value) => {
    const data = {};
    for (const [code, field] of Object.entries(initial)) {
        if (code != "test_id" || code != "studgroups") data[code] = field.value;
    }
    return data;
};

const syncStudgroups = async () => {
    try {
        await axios.patch(`/api/videoconferences/${id.value}/studgroups/sync`, {
            resources: fieldData.value.studgroups.value,
        });
    } catch (error) {
        if (props.data && props.data.studgroups) {
            fieldData.value.studgroups.value = props.data.studgroups.map(
                (a) => a.id
            );
        } else {
            fieldData.value.studgroups.value = null;
        }

        throw new Error("Не удалось сохранить список участвующих групп.");
    }
};

const syncAssignmentTest = async () => {
    if (!fieldData.value.test_id.value) {
        // запрос на удаление
        if (assignment_id.value) {
            try {
                await axios.delete(
                    `/api/assignments/${assignment_id.value}?force=true`
                );
            } catch (error) {
                throw new Error("Не удалось удалить назначение.");
            }
            return;
        } else {
            return;
        }
    }

    const url =
        "/api/assignments" +
        (assignment_id.value ? `/${assignment_id.value}` : "");
    let response;
    const test_data = {
        test_id: fieldData.value.test_id.value,
        date: fieldData.value.date.value,
        vc_id: id.value,
        vc_name: fieldData.value.name.value,
    };

    try {
        // если есть значение и есть старое значение
        if (assignment_id.value) {
            response = await axios.patch(url, test_data);
        } else {
            // если создаем новое назначение
            response = await axios.post(url, test_data);
            assignment_id.value = response.data.data.id;
        }
    } catch (error) {
        throw new Error("Не удалось сохранить тест для видеоконференции.");
    }
};
</script>

<template>
    <form @submit.prevent="sendData" class="form d-grid gap-3">
        <div class="d-grid grid-col-2 gap-4">
            <div>
                <FormField
                    v-for="(field, code) in fieldData"
                    :key="code"
                    :field="field"
                    class="mt-2"
                    :errors="errors"
                />
                <Button @click="sendData" label="Сохранить" class="mt-3" />
            </div>
            <div>
                <h3>
                    {{ labels[labelgroup].settings.title }}
                </h3>
                <FormField
                    v-for="(field, code) in settingsData"
                    :key="code"
                    :field="field"
                    class="mt-2"
                    :errors="[]"
                />
            </div>
        </div>
    </form>
</template>
