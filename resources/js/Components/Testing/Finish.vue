<script setup>
import { ref } from "vue";
import Button from "primevue/button";

const props = defineProps({
    questions: Array,
    answers: Object,
});

const emit = defineEmits(["cancel", "send"]);

const visibleAnswers = (question) => {
    const answer = props.answers[question.id]?.value;
    let result = []
    if (Array.isArray(answer)) {
        answer.forEach(id => {
            let a = question.answers.find(i => i.id == id);
            result.push(a.name)
        })
    } else if(Number.isInteger(answer)) {
        let a = question.answers.find(i => i.id == answer);
        result.push(a.name)
    } else {
        result.push(answer)
    }
    return result;
};

const cancel = () => {
    emit("cancel");
};

const send = () => {
    emit("send");
};

</script>

<template>
    <div class="finish-block">
        <h3 class="mb-3">Подтвердите Ваши ответы</h3>
        <div class="question mb-1" v-for="question in questions" :key="question.id">
            <h4 class="mb-2">{{ question.text }}</h4>
            <ul>
                <li
                    v-for="(ans, ind) in visibleAnswers(question)"
                    :key="ind"
                >
                    {{ ans }}
                </li>
                <b v-if="!answers[question.id].value" class="text-danger">Нет ответа</b>
            </ul>
        </div>
        <div class="d-flex gap-3 mt-3">
            <Button
                label="Изменить ответы"
                severity="danger"
                outlined
                @click="cancel"
            />
            <Button label="Отправить ответы" @click="send" severity="success"/>
        </div>
    </div>
</template>

<style scoped>
.active {
    font-weight: bold;
    color: var(--primary-500);
}
</style>
