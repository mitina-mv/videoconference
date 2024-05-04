<script setup>
import { ref, onMounted } from "vue";
import AuthenticatedLayout from "@/Layouts/AuthenticatedLayout.vue";
import { Head, usePage } from "@inertiajs/vue3";
import UserTable from "@/Components/Tables/UserTable.vue";
import axios from "axios";
import labels from '@/locales/ru.js';
import LoadingSpinner from "@/Components/Common/LoadingSpinner.vue";

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
    },
];
const tableData = ref(null);

onMounted(async () => {
    await fetchData()
});

const fetchData = async () => {
    await axios
        .get("/api/questions/?include=answers")
        .then((response) => {
            tableData.value = response.data.data;
            // addToast(`получили`)
        })
        .catch((error) => {
            // addToast(`Неудачно`)
        });
};
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
                    ></user-table>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>