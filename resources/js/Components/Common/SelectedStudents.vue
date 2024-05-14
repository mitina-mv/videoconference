<template>
    <Accordion multiple>
        <AccordionTab
            v-for="(group, index) in groups"
            :key="index"
        >
            <template #header>
                <div class="d-flex gap-2" @click.stop>
                    <Checkbox
                        :modelValue="isAllSelected(group.students)"
                        :binary="true"
                        @update:modelValue="updateSelectedStudents(group.students)"
                        
                    />
                    <label>{{ group.name }} ({{ group.students.length }})</label>
                    <Badge :value="getSelectedStudentsCount(group.students)" v-if="getSelectedStudentsCount(group.students) > 0" class="ml-4"></Badge>
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
import Badge from 'primevue/badge';

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

const getSelectedStudentsCount = (students) => {
    return students.filter(student => selectedStudents.value.includes(student.id)).length;
};

const emit = defineEmits(['update:modelValue']);
watchEffect(() => {
    emit('update:modelValue', selectedStudents.value);
});
</script>
