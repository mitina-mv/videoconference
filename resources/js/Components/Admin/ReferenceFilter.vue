<template>
    <div class="buttons-group mb-2">
        <template v-if="activeId">
            <Button
                v-for="item in items"
                :key="item[idField]"
                :label="item[label]"
                outlined
                :severity="activeId && activeId === item[idField] ? '' : 'secondary'"
                @click="toggleItem(item[idField])"
            />            
        </template>

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
    idField: {
        type: String,
        default: 'id'
    },
    label: {
        type: String,
        default: 'name'
    }
});

const emit = defineEmits(["toggleItem"]);
const activeId = ref(props.active)

const toggleItem = (id) => {
    emit("toggleItem", id);
    activeId.value = id
};
</script>
