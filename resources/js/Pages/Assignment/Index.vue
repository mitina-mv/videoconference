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
            type: 'select',
            options: props.tests,
            field: 'test_id',
            label: 'name',
            value: 'id',
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
            type: 'select',
            options: props.studgroups,
            value: 'id',
            field: 'testlogs.user.studgroup_id',
            label: 'name',
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
            type: 'calendar',
            field: 'date',
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
            type: 'select',
            options: props.themes,
            value: 'id',
            label: 'name',
            field: 'test.theme_id',
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
const loadData = ref(false)

onMounted(async () => {
    if (years.value != null) {
        years.value.map((item) => {
            item.label = item.year + ` (${item.count_test})`;
        });
        activeYear.value = years.value[0].year;
    }
    await fetchData();
});

const fetchData = async (filters = null) => {
    let params = {
        includes: [
            { relation: "test" },
            { relation: "test.theme" },
        ],
    };

    console.log(filters);

    if (filters) {
        params.filters = filters
    }

    // if (activeYear.value) {
    //     params.filters = [
    //         {
    //             field: "test.theme.discipline_id",
    //             operator: "=",
    //             value: activeYear.value,
    //         },
    //     ];
    // }

    try {
        const response = await axios.post(`/api/assignments/search`, params);
        tableData.value = response.data.data;
        processTableData(tableData.value);
        totalPage.value = response.data.meta.total;
        
        loadData.value = true
    } catch (error) {
        console.error(error);
        toastService.showErrorToast("Заголовок", "Текст");
    }
};

const fetchPageData = async (page, limit) => {
    let params = {
        page: page + 1,
        limit: limit,
        includes: [{ relation: "theme" }],
    };

    if (activeYear.value) {
        params.filters = [
            {
                field: "theme.discipline_id",
                operator: "=",
                value: activeYear.value,
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
        data[index].test_id = element.test.name
        data[index].theme_id = element.test.theme.name

        if(element.studgroups.length > 0) {
            data[index].studgroup_id = element.studgroups.map(a => a.name).join(', ')
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
                <div class="p-4 sm:p-8 bg-white shadow sm:rounded-lg">
                    <loading-spinner v-if="!loadData"></loading-spinner>
                    <template v-else>
                        <reference-filter
                            :items="years"
                            :active="activeYear"
                            @toggleItem="toggleYear"
                            idField="year"
                            label="label"
                        ></reference-filter>
                        <filter-table
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
                        ></filter-table>
                    </template>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
