<script setup>
import { ref, onMounted, watch } from "vue";
import AuthenticatedLayout from "@/Layouts/AuthenticatedLayout.vue";
import { Head, usePage } from "@inertiajs/vue3";
import axios from "axios";
import labels from "@/locales/ru.js";
import LoadingSpinner from "@/Components/Common/LoadingSpinner.vue";
import toastService from "@/Services/toastService";
import Button from "primevue/button";
import RadioButton from "primevue/radiobutton";
import Checkbox from "primevue/checkbox";
import InputText from "primevue/inputtext";

const props = defineProps({
    error: [String, null],
    dd: String,
    questions: [Array, null],
    settings: [Object, null],
    answerlogs: [Object, null],
});

const answers = ref({});
const curQuestion = ref(null)

onMounted(() => {
    if(props.answerlogs) {
        props.questions.forEach(q => {
            answers.value[q.id] = {
                answerlog_id: props.answerlogs[q.id],
                value: null
            }
        })

        curQuestion.value = props.questions[0];
    }
})
</script>

<template>
    <Head :title="labels.page_titles.testing" />

    <AuthenticatedLayout>
        <template #header>
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                {{ labels.page_titles.testing }} {{ dd }}
            </h2>
        </template>

        <div class="d-grid gap-4 content">
            {{ dd }}
            <div class="content__container">

                <div v-if="error">
                    {{ error }}
                </div>
                <template v-else>
                    <div class="test-body" v-if="curQuestion">
                        <h3 class="mb-3">{{ curQuestion.text }}</h3>
                        <div class="answers">
                            <div v-if="curQuestion.type === 'single'">
                                <div
                                    v-for="answer in curQuestion.answers"
                                    :key="answer.id"
                                    class="mb-1"
                                >
                                    <RadioButton
                                        :inputId="'ans_' + answer.id"
                                        :value="answer.id"
                                        v-model="answers[`${curQuestion.id}`].value"
                                        class="mr-2"
                                    />
                                    <label :for="'ans_' + answer.id">{{ answer.name }}</label>
                                </div>
                            </div>
                            <div v-if="curQuestion.type === 'multiple'">
                                <div
                                    v-for="answer in curQuestion.answers"
                                    :key="answer.id"
                                    class="mb-1"
                                >
                                    <Checkbox
                                        :inputId="'ans_' + answer.id"
                                        :value="answer.id"
                                        v-model="answers[`${curQuestion.id}`].value"
                                        class="mr-2"
                                    />
                                    <label :for="'ans_' + answer.id">{{ answer.name }}</label>
                                </div>
                            </div>
                            <div v-if="curQuestion.type === 'text'">
                                <InputText v-model="answers[`${curQuestion.id}`].value" />
                            </div>
                        </div>
                    </div>
                </template>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
