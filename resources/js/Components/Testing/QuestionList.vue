<template>
    <div class="question-list">
        <header>Список вопросов</header>
        <div
            v-for="question in questions"
            :key="question.id"
            @click="selectQuestion(question)"
            :class="{ active: currentQuestion.id === question.id }"
        >
            {{ question.text }}
        </div>
    </div>
</template>

<script setup>
const props = defineProps({
    questions: Array,
    currentQuestion: Object,
});

const emit = defineEmits(["select"]);

const selectQuestion = (question) => {
    emit("select", question);
};
</script>

<style scoped>
.active {
    font-weight: 500;
    color: var(--primary-500);
}
</style>
