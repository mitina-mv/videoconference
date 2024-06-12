<template>
    <div class="navigation-buttons d-flex gap-3">
        <Button
            label="Предыдущий"
            icon="pi pi-arrow-left"
            @click="previousQuestion"
            severity="secondary"
            :disabled="curQuestionIndex === 0 || permissionSwitchQuestions == false"
        />
        <Button
            label="Следующий"
            icon="pi pi-arrow-right"
            iconPos="right"
            severity="secondary"
            @click="nextQuestion"
            :disabled="curQuestionIndex === questions.length - 1"
        />
        <Button label="Завершить" @click="finishTest" />
    </div>
</template>

<script setup>
import { ref, computed } from "vue";
import Button from "primevue/button";

const props = defineProps({
    curQuestionIndex: Number,
    questions: Array,
    permissionSwitchQuestions: Boolean,
});

const emit = defineEmits(["navigate", "finish"]);

const previousQuestion = () => {
    if (props.curQuestionIndex > 0) {
        emit("navigate", props.curQuestionIndex - 1);
    }
};

const nextQuestion = () => {
    console.log(props.curQuestionIndex);
    if (props.curQuestionIndex < props.questions.length - 1) {
        emit("navigate", props.curQuestionIndex + 1);
    }
};

const finishTest = () => {
    emit("finish");
};
</script>
