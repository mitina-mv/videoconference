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
    disciplines: [Array, null]
});

const tableColumns = ref({
    name: {
        code: "name",
        sort: true,
        title: labels.videoconferences_fields.name.title,
        style: {
            width: "10%",
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
            width: "55%",
        },
    },
});
const tableData = ref(null);
const totalPage = ref(null);
const years = ref(props?.years || null);
const loadData = ref(false);
const dateFilter = ref(new Date())
const disciplineFilter = ref(null)

onMounted(async () => {
    disciplineFilter.value = props.disciplines[0].id
    await fetchData();
});

const fetchData = async (filters = null, page = null, limit = null) => {
    let params = {
        includes: [
            { relation: "studgroups" },
            { relation: "user" },
            { relation: "assignment.test" },
        ],
        sort: [
            { field: "date", direction: "asc" },
            { field: "name", direction: "asc" },
        ],
    };

    if (filters) {
        params.filters = filters;
    }

    if (page !== null && limit !== null) {
        params.page = page + 1;
        params.limit = limit;
    }

    let url = "/api/my-videoconferences/search";

    if (dateFilter.value) {
        url += `?date=${dateFilter.value.toISOString()}`
    }

    if (disciplineFilter.value !== null) {
        url += `&discipline=${disciplineFilter.value}`
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
        let sessionString = `${labels.videoconferences_fields.session.title}: ${element.session}`;
        let testString = `,<br />${
            labels.videoconferences_fields.test.title
        }: ${element.assignment?.test.name || "нет"}`;

        data[index].settings = `${sessionString}${testString}`
    });
};

watch(
    dateFilter,
    () => {
        fetchData();
    },
);
</script>

<template>
    <Head :title="labels.page_titles.videoconferences" />

    <AuthenticatedLayout>
        <template #header>
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                {{ labels.page_titles.videoconferences }}
            </h2>
        </template>

        <div class="d-grid gap-4 content">
            <div class="content__container">
                <loading-spinner v-if="!loadData"></loading-spinner>
                <template v-else>
                    
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
                    />
                    <filter-table
                        :tableData="tableData"
                        :columns="tableColumns"
                        labelgroup="videoconferences"
                        @fetchData="fetchData"
                        :total="totalPage"
                        routeName="videoconferences"
                    ></filter-table>
                </template>
            </div>
        </div>
    </AuthenticatedLayout>
</template>