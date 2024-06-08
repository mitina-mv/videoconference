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
    disciplines: [Array, Object],
});

const tableColumns = [
    {
        code: "name",
        style: {
            width: "20%",
        },
        sort: true,
        title: labels.test_fields.name.title,
    },
    {
        code: "description",
        title: labels.test_fields.description.title,
        sort: false,
        style: {
            width: "20%",
        },
    },
    {
        code: "theme",
        sort: true,
        title: labels.test_fields.theme.title,
        style: {
            width: "20%",
        },
    },
    {
        sort: false,
        code: "settings",
        title: labels.test_fields.settings.title,
        type: "html",
        style: {
            width: "25%",
        },
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
        includes: [{ relation: "theme" }],
    };

    if (activeDiscipline.value) {
        params.filters = [
            {
                field: "theme.discipline_id",
                operator: "=",
                value: activeDiscipline.value,
            },
        ];
    }

    try {
        const response = await axios.post(`/api/tests/search`, params);
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
        includes: [{ relation: "theme" }],
    };

    if (activeDiscipline.value) {
        params.filters = [
            {
                field: "theme.discipline_id",
                operator: "=",
                value: activeDiscipline.value,
            },
        ];
    }

    try {
        const response = await axios.post(`/api/tests/search`, params);
        tableData.value = response.data.data;
        processTableData(tableData.value);
    } catch (error) {
        toastService.showInfoToast("Заголовок", "Текст");
    }
};

const processTableData = (data) => {
    data.forEach((element, index) => {
        data[index].theme = element?.theme?.name || "Не указано";

        let settings = element.settings;

        if (settings && Object.keys(settings).length > 0) {
            let settingsString = labels.test_fields.settings.values
                .filter((a) => settings.hasOwnProperty(a.id))
                .map((a) => {
                    let val =
                        a.type === "bool"
                            ? settings[a.id]
                                ? "да"
                                : "нет"
                            : settings[a.id];
                    return `${a.name}: ${val}`;
                })
                .join(",<br />");
            data[index].settings = settingsString;
        }
    });
};

const toggleDiscipline = (id) => {
    activeDiscipline.value = id;
    fetchData();
};
</script>

<template>
    <Head :title="labels.page_titles.tests" />

    <AuthenticatedLayout>
        <template #header>
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                {{ labels.page_titles.tests }}
            </h2>
        </template>

        <div class="d-grid gap-4 content">
            <div class="content__container">
                <loading-spinner v-if="tableData == null"></loading-spinner>
                <template v-else>
                    <reference-filter
                        :items="disciplines"
                        :active="activeDiscipline"
                        @toggleItem="toggleDiscipline"
                        addRoute="admin.reference.disciplines"
                        labelgroup="disciplines"
                    ></reference-filter>
                    <user-table
                        :tableData="tableData"
                        :routeName="'api.tests'"
                        :columns="tableColumns"
                        :labelgroup="'tests'"
                        :includeParamFrom="false"
                        @fetchData="fetchData"
                        @getPage="fetchPageData"
                        :total="totalPage"
                        routeNameForm="tests.new"
                        routeNameEdit="tests.edit"
                    ></user-table>
                </template>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
