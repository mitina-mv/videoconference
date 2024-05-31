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
const settings = ref([])

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
        settings.value[item.id] = {
            value: st[item.id] || item.default,
            type: item.type,
            label: item.name,
        };
    });

    console.log(settings.value);
});

const sendData = () => {

}
</script>

<template>
    <form @submit.prevent="sendData" class="form d-grid gap-3">
        <div class="d-grid grid-col-2 gap-3">
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

                <FormField v-for="(field, code) in settings"
                    :key="code"
                    :field="field"
                    class="mt-2"
                    :errors="[]" />  
            </div>
        </div>
        <Button @click="sendData" label="Сохранить" class="mt-3" />
    </form>
</template>