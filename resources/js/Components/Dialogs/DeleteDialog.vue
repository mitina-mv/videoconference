<template>
    <Dialog
        :visible="visible"
        @update:visible="updateVisible"
        :style="{ width: '450px' }"
        :header="'Удаление ' + labels[labelgroup].case[1]"
        :modal="true"
    >
        <div class="confirmation-content">
            <i
                class="pi pi-exclamation-triangle mr-3"
                style="font-size: 2rem; color: var(--yellow-500)"
            />
            <span>Вы уверены, что хотите удалить этого {{ labels[labelgroup].case[1] }}</span>
        </div>
        <template #footer>
            <Button
                label="Нет"
                icon="pi pi-times"
                severity="danger"
                text
                @click="updateVisible"
            />
            <Button
                label="Да"
                icon="pi pi-check"
                text
                @click.enter="confirmDelete"
            />
        </template>
    </Dialog>
</template>

<script setup>
import { defineProps, ref } from 'vue';
import Button from "primevue/button";
import Dialog from "primevue/dialog";
import labels from "@/locales/ru.js";

const props = defineProps({
    visible: Boolean,
    labels: Object,
    labelgroup: String
});

const emit = defineEmits(['close', 'delete'])

const updateVisible = (value) => {
    emit('close', value)
};

const confirmDelete = () => {
    emit('delete');
};

const handleVisibleUpdate = (newValue) => {
    visible.value = newValue;
};
</script>
