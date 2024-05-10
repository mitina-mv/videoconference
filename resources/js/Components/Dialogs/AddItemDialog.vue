<template>
    <Dialog :visible="visible" @update:visible="updateVisible" header="Добавление записи" :style="{ width: '40vw' }">
        <form class="d-grid gap-2">
            <div class="form-control" v-for="(field, code) in labels.reference_fields"
            :key="code">
                <label for="name">{{ field.title }}</label>
                <InputText
                    id="name"
                    v-model.trim="data[code]"
                    required="true"
                    autofocus
                    :class="{ 'p-invalid': errors && errors[code] }"
                />
                <small class="p-error" v-if="errors && errors[code]"
                    >{{ errors[code][0] }}</small
                >
            </div>

            <div class="form-control" v-for="field in addColumns"
            :key="field.code">
                <label :for="field.code">{{ field.title }}</label>
                <Dropdown :id="field.code" v-if="field.type && field.type == 'dropdown'" v-model="data[field.code]" :options="field.options" optionLabel="name" optionValue="id" filter  />
                <InputText
                    v-else
                    :id="field.code"
                    v-model.trim="data[field.code]"
                    required="true"
                    autofocus
                    :class="{ 'p-invalid': errors && errors[code] }"
                />
                <small class="p-error" v-if="errors && errors[code]"
                    >{{ errors[code][0] }}</small
                >
            </div>
        </form>
        <template #footer>
            <Button
                label="Да"
                icon="pi pi-check"
                severity="success"
                text
                @click="sendData"
            />
            
            <Button
                label="Нет"
                icon="pi pi-times"
                severity="danger"
                text
                @click.enter="updateVisible"
            />
        </template>
    </Dialog>
</template>

<script setup>
import { onMounted, ref } from "vue";
import Dialog from "primevue/dialog";
import labels from '@/locales/ru.js';
import Button from "primevue/button";
import InputText from "primevue/inputtext";
import Dropdown from "primevue/dropdown";

const props = defineProps({
    entity: String,
    visible: Boolean,
    addColumns: {
        type: Array,
        req: false,
        default: null
    },
});

const emit = defineEmits(['close', 'saveItem'])
let dataNull = {
    name: null,
    code: null
}
const data = ref(JSON.parse(JSON.stringify(dataNull)))
const errors = ref(null)

onMounted(() => {
    if(props.addColumns) {
        props.addColumns.forEach(field => {
            
            dataNull[field.code] = null 
        })
    }
})

const updateVisible = (value) => {
    emit('close', value)
};

const sendData = () => {
    emit('saveItem', data.value);
    data.value = JSON.parse(JSON.stringify(dataNull));
    console.log(data.value);
    console.log(dataNull);
    
    updateVisible(false);
};
</script>
