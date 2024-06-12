<template>
    <div class="test-body mb-4">
        <h3 class="mb-3">{{ question.text }}</h3>
        <div class="answers">
            <div v-if="question.type === 'single'">
                <div
                    v-for="answer in question.answers"
                    :key="answer.id"
                    class="mb-1"
                >
                    <RadioButton
                        :inputId="'ans_' + answer.id"
                        :value="answer.id"
                        v-model="answers[`${question.id}`].value"
                        class="mr-2"
                    />
                    <label :for="'ans_' + answer.id">{{ answer.name }}</label>
                </div>
            </div>
            <div v-if="question.type === 'multiple'">
                <div
                    v-for="answer in question.answers"
                    :key="answer.id"
                    class="mb-1"
                >
                    <Checkbox
                        :inputId="'ans_' + answer.id"
                        :value="answer.id"
                        v-model="answers[`${question.id}`].value"
                        class="mr-2"
                    />
                    <label :for="'ans_' + answer.id">{{ answer.name }}</label>
                </div>
            </div>
            <div v-if="question.type === 'text'">
                <InputText v-model="answers[`${question.id}`].value" />
            </div>
        </div>
    </div>
</template>

<script setup>
import { ref } from "vue";
import RadioButton from "primevue/radiobutton";
import Checkbox from "primevue/checkbox";
import InputText from "primevue/inputtext";

const props = defineProps({
    question: Object,
    answers: Object,
});
</script>
