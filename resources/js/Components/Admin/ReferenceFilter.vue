<template>
    <div class="buttons-group mb-2">
        <Button
            v-for="item in items"
            :key="item.id"
            :label="item.name"
            outlined
            :severity="activeId && activeId === item.id ? '' : 'secondary'"
            @click="toggleItem(item.id)"
        />
        <a v-if="addRoute" :href="route(addRoute)">
            <Button :label="'Добавить ' + labels[labelgroup].case[3]" severity="success" icon="pi pi-plus" />
        </a>
    </div>
</template>

<script setup>
import { ref } from "vue";
import Button from "primevue/button";
import labels from "@/locales/ru.js";

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
    labelgroup: {
        type: String,
        default: 'studgroups',
    },
});

const emit = defineEmits(["toggleItem"]);
const activeId = ref(props.active)

const toggleItem = (id) => {
    emit("toggleItem", id);
    activeId.value = id
};
</script>
