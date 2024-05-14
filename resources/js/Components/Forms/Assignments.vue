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
    },
    test_id: {
        value: props?.data?.test_id || null,
        type: "dropdown",
        options: props.tests,
        label: labels.assignments_fields.test_id.title,
        header: {
            addRoute: 'tests.new'
        },
    },
});

const sendData = () => {
    console.log(fieldData.value);
    console.log(selectedStudents.value);
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