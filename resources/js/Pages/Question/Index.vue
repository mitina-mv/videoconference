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

const props = defineProps({
    disciplines: [Array, Object]
})

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
const totalPage = ref(null);
const disciplines = ref(props?.disciplines || null);
const activeDiscipline = ref(null);

onMounted(async () => {
    await fetchData();
});

const fetchData = async () => {
    let params = {
        includes: [
            {"relation" : "answers"}, 
            {"relation" : "theme"}
        ],
    }

    if (activeDiscipline.value) {
        params.filters = [{ field: "theme.discipline_id", operator: "=", value: activeDiscipline.value }]
    }

    try {
        const response = await axios.post(`/api/questions/search`, params);
        tableData.value = response.data.data;
        processTableData(tableData.value);
        totalPage.value = response.data.meta.total;
    } catch (error) {
        toastService.showInfoToast("Заголовок", "Текст");
    }
};

const fetchPageData = async (page, limit) => {
    let params = {
        page: page + 1,
        limit: limit,
        includes: [
            {"relation" : "answers"}, 
            {"relation" : "theme"}
        ],
    }

    if (activeDiscipline.value) {
        params.filters = [{ field: "theme.discipline_id", operator: "=", value: activeDiscipline.value }]
    }

    try {
        const response = await axios.post(`/api/questions/search`, params);
        tableData.value = response.data.data;
        processTableData(tableData.value);
    } catch (error) {
        toastService.showInfoToast("Заголовок", "Текст");
    }
};

const processTableData = (data) => {
    data.forEach((element, index) => {
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
    });
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
                            :includeParamFrom="false"
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
