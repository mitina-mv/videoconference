<script setup>
import { ref, onMounted } from "vue";
import AuthenticatedLayout from "@/Layouts/AuthenticatedLayout.vue";
import { Head, usePage } from "@inertiajs/vue3";
import UserTable from "@/Components/Tables/UserTable.vue";
import axios from "axios";
import labels from "@/locales/ru.js";
import LoadingSpinner from "@/Components/Common/LoadingSpinner.vue";
import toastService from "@/Services/toastService";
import ReferenceFilter from "@/Components/Admin/ReferenceFilter.vue";

const tableColumns = [
    {
        code: "text",
        style: {
            width: "35%",
        },
        sort: true,
        title: labels.questions_fields.text.title,
    },
    {
        code: "answers",
        title: labels.questions_fields.answers.title,
        type: "html",
        sort: false,
        style: {
            width: "25%",
        },
    },
    {
        code: "theme",
        sort: true,
        title: labels.questions_fields.theme.title,
        style: {
            width: "20%",
        },
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
        type: "bool",
    },
];
const tableData = ref(null);
const disciplineIds = ref([]);
const totalPage = ref(null);
const disciplines = ref(null);
const activeDiscipline = ref(null);

onMounted(async () => {
    await fetchData();
});

const fetchData = async () => {
    try {
        const response = await axios.get("/api/questions/?include=answers,theme");
        tableData.value = response.data.data;
        processTableData(tableData.value);
        totalPage.value = response.data.meta.total;
        await fetchDisciplines();
    } catch (error) {
        toastService.showInfoToast("Заголовок", "Текст");
    }
};

const fetchPageData = async (page, limit) => {
    try {
        const response = await axios.get(`/api/questions/?include=answers,theme&page=${page + 1}&limit=${limit}`);
        tableData.value = response.data.data;
        processTableData(tableData.value);
        await fetchDisciplines();
    } catch (error) {
        toastService.showInfoToast("Заголовок", "Текст");
    }
};

const processTableData = (data) => {
    data.forEach((element, index) => {
        const theme = element.theme || {};

        let namesString = element.answers
            .map((a) =>
                a.status
                    ? `<i class='table-value__green'>${a.name}</i>`
                    : a.name
            )
            .join(", ");

        data[index].answers =
            namesString == ""
                ? "<b class='table-value__red'>Нет ответов!</b>"
                : namesString;

        data[index].theme = element?.theme?.name || "Не указано";

        if (theme.discipline_id && !disciplineIds.value.includes(theme.discipline_id)) {
            disciplineIds.value.push(theme.discipline_id);
        }
    });
};

const fetchDisciplines = async () => {
    try {
        const response = await axios.post("/api/disciplines/search", {
            filters: [
                { field: "id", operator: "in", value: disciplineIds.value },
            ],
            sort: [{ field: "name", direction: "asc" }],
        });
        disciplines.value = response.data.data;
    } catch (error) {
        console.error("Error fetching disciplines:", error);
    }
};

const toggleDiscipline = (id) => {
    activeDiscipline.value = id;
    fetchData()
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
                    <template v-else>
                        <reference-filter :items="disciplines"
                            :active="activeDiscipline"
                            @toggleItem="toggleDiscipline"
                            addRoute="admin.reference.disciplines"></reference-filter>
                        <user-table
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
                    </template>
                    
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
