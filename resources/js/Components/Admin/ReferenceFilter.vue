<template>
    <div class="buttons-group mb-2">
        <Button
            v-for="item in items"
            :key="item.id"
            :label="item.name"
            outlined
            :severity="active.id === item.id ? '' : 'secondary'"
            @click="toggleItem(item.id)"
        />
        <a v-if="addRoute" :href="addRoute">
            <Button label="Добавить" severity="success" icon="pi pi-plus" />
        </a>
    </div>
</template>

<script setup>
import { ref } from "vue";
import Button from "primevue/button";

const props = defineProps({
    items: {
        type: [Array, Object],
        required: true,
    },
    active: {
        type: [Number, String, Object],
        default: null,
    },
    addRoute: {
        type: String,
        default: null,
    },
});

const { emit } = defineEmits(["toggleItem"]);

const toggleItem = (id) => {
    emit("toggleItem", id);
};
</script>
