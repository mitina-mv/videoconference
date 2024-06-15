<script setup>
import { ref, onMounted } from "vue";
import AuthenticatedLayout from "@/Layouts/AuthenticatedLayout.vue";
import { Head, usePage } from "@inertiajs/vue3";
import StudentReport from '@/Components/Reports/Student.vue'

const props = defineProps({
    test: [Object, null],
    error: [String, null],
    testlog: [Object, null],
    questions: [Array, null],
    vc: [Object, null],
    user: [Object, null],
});

</script>

<template>
    <Head :title="`Результаты тестирования: ${test ? test.name : 'ошибка'}`" />

    <AuthenticatedLayout>
        <template #header>
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                Результаты тестирования: {{ test ? test.name : 'ошибка' }}
            </h2>
        </template>

        <div class="d-grid gap-4 content">
            <div class="content__container report report_student">
                <div v-if="error">{{ error }}</div>
                <StudentReport
                    v-else
                    :test="test"
                    :testlog="testlog"
                    :questions="questions"
                    :vc="vc"
                    :user="user"
                    :inctuleHeader="false"
                ></StudentReport>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
