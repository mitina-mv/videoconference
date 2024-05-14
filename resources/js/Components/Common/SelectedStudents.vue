<template>
    <Accordion :activeIndex="active" @tab-click="tabClick" @tab-open="null" @tab-close="null" multiple>
        <AccordionTab
            v-for="(group, index) in groups"
            :key="index"
        >
            <template #header>
                <div class="d-flex gap-2">
                    <Checkbox
                        :modelValue="isAllSelected(group.students)"
                        :binary="true"
                        @update:modelValue="updateSelectedStudents(group.students)"
                    />
                    <label>{{ group.name }}</label>
                    <Button label="tyk" @click="toggleAccordion(index)"></Button>
                </div>                
            </template>    
            <div class="d-flex gap-2 ml-4" v-for="item in group.students" :key="item.id">
                <Checkbox
                    v-model="selectedStudents"
                    :value="item.id"
                />
                <label>{{ item.full_name }}</label>
            </div>
        </AccordionTab>
    </Accordion>
</template>

<script setup>
import { ref, watchEffect } from "vue";
import Checkbox from 'primevue/checkbox';
import Accordion from 'primevue/accordion';
import AccordionTab from 'primevue/accordiontab';
import Button from "primevue/button";

const props = defineProps({
    groups: {
        type: Array,
        required: true,
    },
});

const selectedStudents = ref([]);

const updateSelectedStudents = (students) => {
    let flag = students.every(student => selectedStudents.value.includes(student.id));
    const selectedStudentIds = students.map(student => student.id);
    if (flag) {
        selectedStudents.value = selectedStudents.value.filter(id => !selectedStudentIds.includes(id));
    } else {
        selectedStudents.value.push(...selectedStudentIds);
    }
};

const isAllSelected = (students) => {
    if (students.length == 0 || students == null) return false;
    return students.every(student => selectedStudents.value.includes(student.id));
};
const active = ref([]);

const tabClick = (e) => {
    return null
}

const toggleAccordion = (index) => {
    console.log(index);
    active.value.push(index);
};
const emit = defineEmits(['update:modelValue']);
watchEffect(() => {
    emit('update:modelValue', selectedStudents.value);
});
</script>
