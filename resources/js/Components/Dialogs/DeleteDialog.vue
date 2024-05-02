<template>
    <Dialog
        v-model:visible="visible"
        @update:visible="handleVisibleUpdate"
        :style="{ width: '450px' }"
        :header="'Удаление ' + labels.case[1]"
        :modal="true"
    >
        <div class="confirmation-content">
            <i
                class="pi pi-exclamation-triangle mr-3"
                style="font-size: 2rem; color: var(--yellow-500)"
            />
            <span>Вы уверены, что хотите удалить этого {{ labels.case[1] }}</span>
        </div>
        <template #footer>
            <Button
                label="Нет"
                icon="pi pi-times"
                severity="danger"
                text
                @click="hideDeleteDialog"
            />
            <Button
                label="Да"
                icon="pi pi-check"
                text
                @click="confirmDelete"
            />
        </template>
    </Dialog>
</template>

<script setup>
import { defineProps, ref } from 'vue';
import Button from "primevue/button";
import Dialog from "primevue/dialog";

const props = defineProps({
    visible: Boolean,
    labels: Object
});

const visible = ref(props.visible);

const hideDeleteDialog = () => {
    emit('update:visible', false);
};

const confirmDelete = () => {
    emit('delete-item');
    hideDeleteDialog();
};

const handleVisibleUpdate = (newValue) => {
    visible.value = newValue;
};
</script>
