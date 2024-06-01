<template>
    <div class="form-control" :class="field.class || ''">
        <div class="d-flex flex-between">
            <label :for="field.id">{{ field.label }}</label>
            <a
                v-if="field.header"
                :href="route(field.header.addRoute)"
                class="text-gray"
                >Добавить</a
            >
        </div>

        <Textarea
            v-if="field.type === 'text'"
            v-model="field.value"
            :id="field.id"
            @update:modelValue="handleUpdateModelValue"
        />

        <Calendar
            v-else-if="field.type === 'date' || field.type === 'datetime'"
            dateFormat="dd.mm.yy"
            :minDate="new Date()"
            showIcon
            :showOnFocus="false"
            :showTime="field.type === 'datetime'"
            hourFormat="24"
            v-model="field.value"
            @update:modelValue="handleUpdateModelValue"
        />

        <Dropdown
            v-else-if="field.type === 'dropdown'"
            :id="field.id"
            v-model="field.value"
            :options="field.options"
            optionLabel="name"
            optionValue="id"
            filter
            showClear
            @update:modelValue="handleUpdateModelValue"
        />

        <InputNumber
            v-else-if="field.type == 'number'"
            v-model="field.value"
            @update:modelValue="handleUpdateModelValue"
            inputId="minmax"
            :min="field.min || 1"
            :max="field.max || 100"
        />

        <MultiSelect
            v-else-if="field.type == 'multiselect'"
            v-model="field.value"
            @update:modelValue="handleUpdateModelValue"
            :options="field.options"
            optionLabel="name"
            optionValue="id"
            display="chip"
            :placeholder="field.placeholder || 'Выберите...'"
            :maxSelectedLabels="field.max || 10"
            filter
        />

        <InputSwitch
            v-else-if="field.type == 'bool' || field.type == 'boolean'"
            @update:modelValue="handleUpdateModelValue"
            v-model="field.value"
        />

        <InputText
            v-else
            :id="field.id"
            v-model="field.value"
            type="text"
            :class="{ 'p-invalid': hasError }"
            @update:modelValue="handleUpdateModelValue"
        />

        <small class="p-error" v-if="hasError">{{ errorMessage }}</small>
    </div>
</template>

<script setup>
import { ref, computed, watch } from "vue";
import InputText from "primevue/inputtext";
import Textarea from "primevue/textarea";
import Calendar from "primevue/calendar";
import Dropdown from "primevue/dropdown";
import InputNumber from "primevue/inputnumber";
import MultiSelect from "primevue/multiselect";
import InputSwitch from "primevue/inputswitch";

const props = defineProps({
    field: {
        type: Object,
        required: true,
    },
    errors: {
        type: Object,
        required: false,
    },
});
const emit = defineEmits(["update:modelValue"]);
const value = ref(props.field.value);

const hasError = computed(() => props.errors[props.field.code] !== undefined);
const errorMessage = computed(() =>
    hasError.value ? props.errors[props.field.code][0] : ""
);
const handleUpdateModelValue = (newValue) => {
    value.value = newValue;
    emit("update:modelValue", newValue);
};
watch(
    () => props.field.value,
    (newValue) => {
        value.value = newValue;
    }
);
</script>
