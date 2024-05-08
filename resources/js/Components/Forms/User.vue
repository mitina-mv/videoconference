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
        type: Number,
        req: false
    },
    studgroups: {
        type: Array,
        req: false
    }
})

const role = ref(props?.data?.role_id || props?.role);
const id = ref(props?.data?.id || null);
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
        title: labels.user_fields.email.title,
        req: true,
    },
];
const fieldData = ref({
    name: props?.data?.name || null,
    lastname: props?.data?.lastname || null,
    patronymic: props?.data?.patronymic || null,
    email: props?.data?.email || null,
    studgroup_id: props?.data?.studgroup_id || null,
    role_id: role.value
})
const teachStudgroups = ref(props?.data?.studgroups || null)
const errors = ref({});

const sendData = () => {
    console.log(fieldData.value);
    if (!fieldData.value.name || !fieldData.value.lastname || !fieldData.value.email) {
        console.error('Не все обязательные поля заполнены');
        return;
    }

    if(!id.value)
        fieldData.value.password = fieldData.value.email;

    if (fieldData.value.role_id === 3 && !fieldData.value.studgroup_id) {
        console.error('Не указана группа для студента');
        return;
    }

    const url = id.value ? `/api/users/${id.value}` : '/api/users/';

    axios({
        method: id.value != null ? 'put' : 'post',
        url: url,
        data: fieldData.value
    })
    .then(response => {
        console.log('Данные успешно отправлены');
        if (fieldData.value.role_id === 2 && teachStudgroups.value) {
            axios.patch(`/api/users/${response.data.data.id}/studgroups/sync`, {resources: teachStudgroups.value})
                .then(response => {
                    console.log('Группы студентов успешно синхронизированы');
                    window.location = route('admin.index')
                })
                .catch(error => {
                    console.error('Ошибка при синхронизации групп студентов:', error);
                });
        } else {
            window.location = route('admin.index')
        }
    })
    .catch(error => {
        console.error('Ошибка при отправке данных:', error);
    });
}
</script>

<template>
    <form
        @submit.prevent="sendData"
        class="form d-grid gap-3 grid-col-2"
    >
        <div class="form-control" v-for="field in textFields" :key="field.code">
            <label :for="field.code + '_input'">{{ field.title }}</label>
            <InputText
                :id="field.code + '_input'"
                v-model="fieldData[field.code]"
                type="text"
                :class="{ 'p-invalid': errors[field.code] }"
            />
            <small class="p-error" v-if="errors[field.code]">{{
                errors[field.code] ? errors[field.code][0] : "&nbsp;"
            }}</small>
        </div>

        <template v-if="role == 3">
            <div class="form-control">
                <label for="studgroup_id">{{ labels.user_fields.studgroup.title }}</label>
                <Dropdown v-model="fieldData.studgroup_id" :options="studgroups" optionLabel="name"  optionValue="id" placeholder="Выбор группы" filter  />
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

        <div class='form-footer mt-2'>
            <Button @click="sendData" label="Сохранить">
            </Button>
        </div>

    </form>
</template>