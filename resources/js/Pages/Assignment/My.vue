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

const props = defineProps({
    disciplines: [Array, null],
});

const tableColumns = ref({
    name: {
        code: "name",
        sort: true,
        title: labels.videoconferences_fields.name.title,
        style: {
            width: "20%",
        },
    },
    date: {
        code: "date",
        sort: false,
        title: labels.videoconferences_fields.date.title,
        style: {
            width: "23%",
        },
    },
    settings: {
        sort: false,
        code: "settings",
        title: labels.videoconferences_fields.settings.title,
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

        console.log(totalPage.value);
    } catch (error) {
        console.error(error);
        toastService.showErrorToast("Заголовок", "Текст");
    }
};

const processTableData = (data) => {
    data.forEach((element, index) => {
        data[index].name = element.assignment.test.theme.name;
        data[index].date = element.assignment.date;

        let speakerString = `Проводит: ${element.assignment.user.full_name}`;
        data[index].settings = `${speakerString}`;

        let settings = JSON.parse(element.assignment.test.settings);

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
                    ></filter-table>
                </template>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
