<script setup>
import { ref, onMounted, } from "vue";
import labels from "@/locales/ru.js";
import Button from "primevue/button";
import toastService from "@/Services/toastService";

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

</script>

<template>
    <form @submit.prevent="sendData" class="form d-grid gap-3">
        <div class="d-grid grid-col-2 gap-3">
            <div class="studgroups">
                <h3>Участники</h3>
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