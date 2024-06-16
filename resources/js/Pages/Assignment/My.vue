<script setup>
import { ref, onMounted, watch } from "vue";
import AuthenticatedLayout from "@/Layouts/AuthenticatedLayout.vue";
import { Head, usePage } from "@inertiajs/vue3";
import axios from "axios";
import labels from "@/locales/ru.js";
import LoadingSpinner from "@/Components/Common/LoadingSpinner.vue";
import toastService from "@/Services/toastService";
import Calendar from "primevue/calendar";
import Dropdown from "primevue/dropdown";
import FilterTable from "@/Components/Tables/FilterTable.vue";
import Button from "primevue/button";

const props = defineProps({
    disciplines: [Array, null],
});

const tableColumns = ref({
    name: {
        code: "name",
        sort: true,
        title: labels.assignments_fields.themes.title,
        style: {
            width: "20%",
        },
    },
    date: {
        code: "date",
        sort: false,
        title: labels.assignments_fields.date.title,
        style: {
            width: "23%",
        },
    },
    mark: {
        code: "mark_value",
        sort: false,
        title: labels.assignments_fields.mark.title,
        style: {
            width: "3%",
        },
    },
    settings: {
        sort: false,
        code: "settings",
        title: labels.assignments_fields.settings.title,
        type: "html",
        style: {
            width: "45%",
        },
    },
});
const tableData = ref(null);
const totalPage = ref(null);
const loadData = ref(false);
const dateFilter = ref(new Date());
const disciplineFilter = ref(null);

onMounted(async () => {
    disciplineFilter.value = props.disciplines[0].id;
    await fetchData();
});

const fetchData = async (filters = null, page = null, limit = null) => {
    let params = {
        includes: [
            { relation: "assignment.user" },
            { relation: "assignment.test.theme" },
        ],
    };

    if (filters) {
        params.filters = filters;
    }

    if (page !== null && limit !== null) {
        params.page = page + 1;
        params.limit = limit;
    }

    let url = "/api/my-assignments/search";

    if (dateFilter.value) {
        url += `?date=${dateFilter.value.toISOString()}`;
    }

    if (disciplineFilter.value !== null) {
        if (!dateFilter.value) url += "?";
        else url += "&";

        url += `discipline=${disciplineFilter.value}`;
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
        data[index].name = element.assignment.test.theme.name;
        data[index].date = element.assignment.date;
        data[index].mark_value = element.mark == null ? 'Нет' :element.mark;

        let speakerString = `Проводит: ${element.assignment.user.full_name}`;
        data[index].settings = `${speakerString}`;

        let settings = element.assignment.test.settings;

        if (Object.keys(settings).length > 0) {
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
            data[index].settings += ",<br />" + settingsString;
        }
    });
};

watch(dateFilter, () => {
    fetchData();
});

watch(disciplineFilter, () => {
    fetchData();
});
function isFloat(n) {
    return Number(n) === Number(n) && n % 1 !== 0;
}
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
                    <div class="d-grid grid-col-4 gap-4 mb-4">
                        <Dropdown
                            v-model="disciplineFilter"
                            :options="disciplines"
                            optionLabel="name"
                            optionValue="id"
                            filter
                            showClear
                        />
                        <Calendar
                            v-model="dateFilter"
                            showIcon
                            iconDisplay="input"
                            dateFormat="dd.mm.yy"
                            placeholder="Выберите дату"
                            :numberOfMonths="2"
                        />
                    </div>
                    <filter-table
                        :tableData="tableData"
                        :columns="tableColumns"
                        labelgroup="assignments"
                        @fetchData="fetchData"
                        :total="totalPage"
                        routeName="assignments"
                        :includeCrudActions="false"
                    >
                        <template #controls="{ data }">
                            <a
                                :href="route('assignments.testing', data.id)"
                                v-if="data.assignment.is_active && data.mark==null"
                            >
                                <Button icon="pi pi-play" text />
                            </a>
                            <div v-if="(data.assignment.is_old && !data.assignment.is_active && isFloat(data.mark)) || isFloat(data.mark)" >
                                <a :href="route('report.student', {testlog_id: data.id})">
                                    <Button
                                        icon="pi pi-info-circle"
                                        text
                                        severity="info"
                                        size="large"
                                    ></Button>
                                </a>
                            </div>
                            <p class="text-danger" v-if="data.assignment.is_old && !data.assignment.is_active && (!isFloat(data.mark) || data.mark == 0)">Нет данных</p>
                        </template>
                    </filter-table>
                </template>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
