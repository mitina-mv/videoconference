<script setup>
import { ref, onMounted } from "vue";
import AuthenticatedLayout from "@/Layouts/AuthenticatedLayout.vue";
import { Head, usePage } from "@inertiajs/vue3";
import axios from "axios";
import labels from "@/locales/ru.js";
import LoadingSpinner from "@/Components/Common/LoadingSpinner.vue";
import toastService from "@/Services/toastService";
import ReferenceFilter from "@/Components/Admin/ReferenceFilter.vue";
import FilterTable from "@/Components/Tables/FilterTable.vue";
import Button from "primevue/button";

const props = defineProps({
    years: [Array, Object],
    studgroups: Array,
    tests: Array,
    themes: Array,
});

const tableColumns = ref({
    test_id: {
        code: "test_id",
        sort: false,
        filter: {
            type: "select",
            options: props.tests,
            field: "test_id",
            label: "name",
            value: "id",
        },
        title: labels.assignments_fields.test_id.title,
        style: {
            width: "20%",
        },
    },
    studgroup_id: {
        code: "studgroup_id",
        sort: false,
        filter: {
            type: "select",
            options: props.studgroups,
            value: "id",
            field: "testlogs.user.studgroup_id",
            label: "name",
        },
        title: labels.assignments_fields.studgroups.title,
        style: {
            width: "20%",
        },
    },
    date: {
        code: "date",
        sort: false,
        filter: {
            type: "calendar",
            field: "date",
            options: null,
        },
        title: labels.assignments_fields.date.title,
        style: {
            width: "20%",
        },
    },
    theme_id: {
        code: "theme_id",
        sort: false,
        filter: {
            type: "select",
            options: props.themes,
            value: "id",
            label: "name",
            field: "test.theme_id",
        },
        title: labels.assignments_fields.themes.title,
        style: {
            width: "20%",
        },
    },
});
const tableData = ref(null);
const totalPage = ref(null);
const years = ref(props?.years || null);
const activeYear = ref(null);
const loadData = ref(false);

onMounted(async () => {
    if (years.value != null && years.value.length > 0) {
        years.value.map((item) => {
            item.label = item.year + ` (${item.count_test})`;
        });
        activeYear.value = years.value[0].year;
    }
    await fetchData();
});

const fetchData = async (filters = null, page = null, limit = null) => {
    let params = {
        includes: [{ relation: "test" }, { relation: "test.theme" }],
        
        sort: [
            { field: "date", direction: "desc" },
        ],
    };

    if (filters) {
        params.filters = filters;
    }

    if (page !== null && limit !== null) {
        params.page = page + 1;
        params.limit = limit;
    }

    let url = "/api/assignments/search";

    if (activeYear.value) {
        url = url + `?year=${activeYear.value}`;
    }

    try {
        const response = await axios.post(url, params);
        tableData.value = response.data.data;
        processTableData(tableData.value);
        totalPage.value = response.data.meta.total;

        loadData.value = true;
    } catch (error) {
        console.error(error);
        toastService.showErrorToast("Заголовок", "Текст");
    }
};

const processTableData = (data) => {
    data.forEach((element, index) => {
        data[index].test_id = element.test.name;
        data[index].theme_id = element.test.theme.name;

        if (element.studgroups.length > 0) {
            data[index].studgroup_id = element.studgroups
                .map((a) => a.name)
                .join(", ");
        }
    });
};

const toggleYear = (id) => {
    activeYear.value = id;
    fetchData();
};
</script>

<template>
    <Head :title="labels.page_titles.assignments" />

    <AuthenticatedLayout>
        <template #header>
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                {{ labels.page_titles.assignments }}
            </h2>
        </template>

        <div class="d-grid gap-4 content">
            <div class="content__container">
                <loading-spinner v-if="!loadData"></loading-spinner>
                <template v-else>
                    <reference-filter
                        :items="years"
                        :active="activeYear"
                        @toggleItem="toggleYear"
                        idField="year"
                        label="label"
                        addRoute="assignments.new"
                        labelgroup="assignments"
                    ></reference-filter>
                    <filter-table
                        :tableData="tableData"
                        :columns="tableColumns"
                        :labelgroup="'tests'"
                        @fetchData="fetchData"
                        :total="totalPage"
                        routeName="assignments"
                    >
                        <template #controls="{ data }">
                            <div v-if="data.is_old && !data.is_active">
                                <a :href="route('report.assignment', {assignment_id: data.id})">
                                    <Button
                                        icon="pi pi-info-circle"
                                        text
                                        severity="info"
                                        size="large"
                                    ></Button>
                                </a>
                                <a v-if="data.moodle_code" :href="route('get_csv', {assignment_id: data.id})">
                                    <Button
                                        icon="pi pi-file-excel"
                                        text
                                        severity="success"
                                        size="large"
                                    ></Button>
                                </a>
                            </div>                            
                        </template>
                    </filter-table>
                </template>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
