<script setup>
import { ref, onMounted, computed, watch, onBeforeMount } from "vue";
import labels from "@/locales/ru.js";
import Button from "primevue/button";
import toastService from "@/Services/toastService";
import Toolbar from "primevue/toolbar";
import Message from "primevue/message";
import FormField from "@/Components/Common/FormField.vue"

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
}
</script>

<template>
    <div>
      <FormField v-for="(field, code) in fieldData"
            :key="code"
            :field="field"
            :errors="errors" />

        <div class="form-footer mt-2">
            <Button @click="sendData" label="Сохранить"> </Button>
        </div>
    </div>
</template>