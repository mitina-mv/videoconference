<script setup>
import { ref, onMounted } from "vue";
import AuthenticatedLayout from "@/Layouts/AuthenticatedLayout.vue";
import { Head, usePage } from "@inertiajs/vue3";
import UserTable from "@/Components/Tables/UserTable.vue";
import axios from "axios";
import labels from '@/locales/ru.js';
import LoadingSpinner from "@/Components/Common/LoadingSpinner.vue";
import toastService from "@/Services/toastService";

const tableColumns = [
    {
        code: "text",
        style: {
            width: '45%',
        },
        sort: true,
        title: labels.questions_fields.text.title,
    },
    {
        code: "answers",
        title: labels.questions_fields.answers.title,
        type: 'html',
        sort: false,
    },
    {
        code: "mark",
        sort: false,
        title: labels.questions_fields.mark.title,
    },
    {
        sort: true,
        code: "is_private",
        title: labels.questions_fields.is_private.title,
        type: 'bool',
    },
];
const tableData = ref(null);
const totalPage = ref(null);

onMounted(async () => {
    await fetchData()
});

const fetchData = async () => {
    await axios
        .get("/api/questions/?include=answers")
        .then((response) => {
            tableData.value = response.data.data;

            tableData.value.forEach((element, index) => {
                let namesString = element.answers.map((a) => a. status ? `<i class='table-value__green'>${a.name}</i>` : a.name).join(', ');
                tableData.value[index].answers = namesString;
            });
            totalPage.value = response.data.meta.total
            toastService.showInfoToast("Заголовок", "Текст")
        })
        .catch((error) => {
            toastService.showInfoToast("Заголовок", "Текст")
        });
};

const fetchPageData = async (page, limit) => {
    await axios
        .get(`/api/questions/?include=answers&page=${page + 1}&limit=${limit}`,)
        .then((response) => {
            tableData.value = response.data.data;

            tableData.value.forEach((element, index) => {
                let namesString = element.answers.map((a) => a. status ? `<i class='table-value__green'>${a.name}</i>` : a.name).join(', ');
                tableData.value[index].answers = namesString;
            });
            toastService.showInfoToast("Заголовок", "Текст")
        })
        .catch((error) => {
            toastService.showInfoToast("Заголовок", "Текст")
        });
}
</script>

<template>
    <Head :title="labels.page_titles.questions" />

    <AuthenticatedLayout>
        <template #header>
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                {{ labels.page_titles.questions }}
            </h2>
        </template>

        <div class="d-grid gap-4 content">
            <div class="content__container">
                <div class="p-4 sm:p-8 bg-white shadow sm:rounded-lg">
                    <loading-spinner v-if="tableData == null"></loading-spinner>
                    <user-table
                        v-else
                        :tableData="tableData"
                        :routeName="'api.questions'"
                        :columns="tableColumns"
                        :labelgroup="'questions'"
                        @fetchData="fetchData"
                        @getPage="fetchPageData"
                        :total="totalPage"
                        routeNameForm="questions.new"
                        routeNameEdit="questions.edit"
                    ></user-table>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>