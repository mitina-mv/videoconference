<template>
    <Head :title="labels.page_titles.testing" />

    <AuthenticatedLayout>
        <template #header>
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                {{ labels.page_titles.testing }}
            </h2>
        </template>

        <div class="d-grid gap-4 content">
            <div class="content__container">
                <div v-if="timeLimit" class="timer">
                    Время до конца: {{ formattedTime }}
                </div>
                <div v-if="error">{{ error }}</div>
                <div class="test" v-else-if="curQuestion && !finishFlag">
                    
                    <div class="test-main">
                        <Question :question="curQuestion" :answers="answers" />
                        <TestNavigation
                            :curQuestionIndex="curQuestionIndex"
                            :questions="questions"
                            :permissionSwitchQuestions="
                                permissionSwitchQuestions
                            "
                            @navigate="navigate"
                            @finish="finishTest"
                        />
                    </div>

                    <QuestionList
                        v-if="permissionSwitchQuestions"
                        :questions="questions"
                        :currentQuestion="curQuestion"
                        @select="setCurrentQuestion"
                    />
                </div>
                <Finish
                    v-else-if="finishFlag"
                    :questions="questions"
                    :answers="answers"
                    @cancel="cancelFinish"
                    @send="sendAnswers"
                />
            </div>
        </div>
    </AuthenticatedLayout>
</template>

<script setup>
import { ref, onMounted, onUnmounted, computed } from "vue";
import { Head, usePage } from "@inertiajs/vue3";
import Question from "@/Components/Testing/Question.vue";
import QuestionList from "@/Components/Testing/QuestionList.vue";
import TestNavigation from "@/Components/Testing/TestNavigation.vue";
import AuthenticatedLayout from "@/Layouts/AuthenticatedLayout.vue";
import labels from "@/locales/ru.js";
import Finish from "@/Components/Testing/Finish.vue";
import toastService from "@/Services/toastService";

const props = defineProps({
    error: [String, null],
    questions: [Array, null],
    settings: [Object, null],
    answerlogs: [Object, null],
    testlog_id: Number,
});

const answers = ref({});
const curQuestion = ref(null);
const curQuestionIndex = ref(0);
const permissionSwitchQuestions = props.settings?.permission_switch_questions || true;
const questions = ref(props.questions);
const finishFlag = ref(false);

const setCurrentQuestion = (question) => {
    curQuestion.value = question;
    curQuestionIndex.value = questions.value.findIndex(
        (q) => q.id === question.id
    );
};

const navigate = (index) => {
    curQuestion.value = questions.value[index];
    curQuestionIndex.value = index;
};

const finishTest = () => {
    finishFlag.value = true;
};

const cancelFinish = () => {
    finishFlag.value = false;
};

const sendAnswers = async () => {
    try {
        await axios.post(
            route("api.my-assignments.saveAnswer", {
                testlog_id: props.testlog_id,
            }),
            {
                testlog_id: props.testlog_id,
                answers: answers.value,
            }
        );

        toastService.showSuccessToast(
            "Сохранение ответов",
            "Ответы успешно сохранены"
        );
        window.location = route('report.student', {testlog_id: props.testlog_id})
    } catch (error) {
        toastService.showErrorToast(
            "Сохранение ответов",
            error.response.data.error
        );
    }
};
// Таймер
const timeLimit = props.settings && props.settings.time_limit ? props.settings.time_limit * 60 : null;
const remainingTime = ref(timeLimit);
const intervalId = ref(null);

const formattedTime = computed(() => {
    const minutes = Math.floor(remainingTime.value / 60);
    const seconds = remainingTime.value % 60;
    return `${minutes}:${seconds < 10 ? '0' : ''}${seconds}`;
});

const startTimer = () => {
    intervalId.value = setInterval(() => {
        if (remainingTime.value > 0) {
            remainingTime.value -= 1;
        } else {
            clearInterval(intervalId.value);
            finishTest();
            sendAnswers();
        }
    }, 1000);
};

const stopTimer = () => {
    if (intervalId.value) {
        clearInterval(intervalId.value);
        intervalId.value = null;
    }
};

onMounted(() => {
    if (props.answerlogs) {
        props.questions.forEach((q) => {
            answers.value[q.id] = {
                answerlog_id: props.answerlogs[q.id],
                value: null,
            };
        });

        curQuestion.value = props.questions[0];
    }
    if (timeLimit) {
        startTimer();
    }
});

onUnmounted(() => {
    stopTimer();
});
</script>
