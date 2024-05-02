<script setup>
import { ref, onMounted } from "vue";
import InputText from "primevue/inputtext";
import InputNumber from "primevue/inputnumber";
import MultiSelect from "primevue/multiselect";
import Dropdown from "primevue/dropdown";
import labels from '@/locales/ru.js';
import Button from "primevue/button";

const props = defineProps({
    data: {
        type: Object,
        req: false
    }, 
    role: {
        type: String,
        req: false
    },
    studgroups: {
        type: Array,
        req: false
    }
})

const role = props?.data.role_id || props?.role;
const textFields = [
    {
        code: 'name',
        title: labels.user_fields.name.title,
        req: true,
    },
    {
        code: 'lastname',
        title: labels.user_fields.lastname.title,
        req: true,
    },
    {
        code: 'patronymic',
        title: labels.user_fields.patronymic.title,
        req: false,
    },
    {
        code: 'email',
        title: labels.user_fields.patronymic.title,
        req: true,
    },
];
const fieldData = {
    name: props?.data.name || null,
    lastname: props?.data.lastname || null,
    patronymic: props?.data.patronymic || null,
    email: props?.data.email || null,
    studgroup_id: props?.data.role_id || null,
}
const teachStudgroups = ref(props?.data?.studgroups || null)
const errors = ref({});
</script>

<template>
    <form
        @submit.prevent="sendData"
        class="form"
    >

        <template v-if="role == 3">
            <div class="form-control">
                <label for="studgroup_id">{{ labels.user_fields.studgroup.title }}</label>
                <Dropdown v-model="fieldData.studgroup_id" :options="studgroups" optionLabel="name"  optionValue="id" placeholder="Выбор группы" />
                <small class="p-error"
                    >{{ errors.studgroup_id ? errors.studgroup_id[0] : "&nbsp;" }}</small
                >
            </div>
        </template>

        <template v-else-if="role == 2">
            <div class="form-control" style="grid-column: 1/-1;">
                <label>{{ labels.user_fields.studgroups.title }}</label>
                <MultiSelect v-model="teachStudgroups" :options="studgroups" optionLabel="name" optionValue="id" display="chip" :placeholder="labels.user_fields.studgroups.placeholder"
                    :maxSelectedLabels="10" filter />
            </div>
        </template>

        <div class="form-control" v-for="field in textFields" :key="field.code">
            <label :for="field.code + '_input'">{{ field.title }}</label>
            <InputText
                :id="field.code + '_input'"
                v-model="fieldData[field.code]"
                type="text"
                :class="{ 'p-invalid': errors[field.code] }"
            />
            <small class="p-error">{{
                errors[field.code] ? errors[field.code][0] : "&nbsp;"
            }}</small>
        </div>

        

    </form>
</template>