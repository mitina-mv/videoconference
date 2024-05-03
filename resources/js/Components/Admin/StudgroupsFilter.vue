<script setup>
import { ref, onMounted } from "vue";
import Button from "primevue/button";

const props = defineProps({
    studgroups: {
        type: Array,
        req: false,
    },
    active: {
        type: Number,
    },
});

const toggleStudgroup = (id) => {
    $emit('toggleStudgroup', id);
}
</script>

<template>
    <Button
        v-for="studgroup in studgroups"
        :key="studgroup.id"
        :label="studgroup.name"
        outlined
        :severity="studgroup.id == active ? '' : 'secondary'"
        @click="toggleStudgroup(studgroup.id)"
    />

    <a :href="route('admin.reference.studgroups')">
        <Button label="Добавить" severity="success" icon="pi pi-plus" />
    </a>
</template>
