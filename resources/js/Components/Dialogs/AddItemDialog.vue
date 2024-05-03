<template>
    <Dialog :visible="visible" @update:visible="updateVisible" header="Добавление записи">
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
                @click="updateVisible"
            />
        </template>
    </Dialog>
</template>

<script setup>
import { ref } from "vue";
import Dialog from "primevue/dialog";
import labels from '@/locales/ru.js';
import Button from "primevue/button";
import InputText from "primevue/inputtext";

const props = defineProps({
    entity: String,
    visible: Boolean,
});

const emit = defineEmits(['close', 'saveItem'])
const data = ref({
    name: null,
    code: null
})
const errors = ref(null)

const updateVisible = (value) => {
    emit('close', value)
};

const sendData = () => {
    emit('saveItem', data.value);
    data.value = {
        name: null,
        code: null
    };
    updateVisible(false);
};
</script>
