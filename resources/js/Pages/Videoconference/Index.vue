<script setup>
import { ref, onMounted } from "vue";
import AuthenticatedLayout from "@/Layouts/AuthenticatedLayout.vue";
import { Head, usePage } from "@inertiajs/vue3";
import axios from "axios";
import labels from "@/locales/ru.js";
import LoadingSpinner from "@/Components/Common/LoadingSpinner.vue";
import toastService from "@/Services/toastService";
import ReferenceFilter from "@/Components/Admin/ReferenceFilter.vue";
import Button from "primevue/button";
import FilterTable from "@/Components/Tables/FilterTable.vue";

const props = defineProps({
    years: [Array, Object],
    studgroups: Array,
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
    studgroup_id: {
        code: "studgroup_id",
        sort: false,
        filter: {
            type: "select",
            options: props.studgroups,
            value: "id",
            field: "studgroups.id",
            label: "name",
        },
        title: labels.videoconferences_fields.studgroups.title,
        style: {
            width: "10%",
        },
    },
    date: {
        code: "date",
        sort: false,
        filter: {
            type: "calendar",
            field: "date",
            options: null,
            showTile: true,
        },
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
        includes: [
            { relation: "studgroups" },
            { relation: "assignment" },
            { relation: "assignment.test" },
        ],
        sort: [
            { field: "date", direction: "desc" },
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

    let url = "/api/videoconferences/search";

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
        toastService.showErrorToast("Получение данных", "Не удалось получить данные");
    }
};

const processTableData = (data) => {
    data.forEach((element, index) => {
        data[index].test_id = element.assignment?.test.name || "-";

        if (element.studgroups.length > 0) {
            data[index].studgroup_id = element.studgroups
                .map((a) => a.name)
                .join(", ");
        } else {
            data[index].studgroup_id = "не указано";
        }

        let settings = element.settings;
        let sessionString = `,<br />${labels.videoconferences_fields.session.title}: ${element.session}`;
        let testString = `,<br />${
            labels.videoconferences_fields.test.title
        }: ${element.assignment?.test.name || "нет"}`;

        if (Object.keys(settings).length > 0) {
            let settingsString = labels.videoconferences_fields.settings.values
                .filter((a) => settings.hasOwnProperty(a.id))
                .map((a) => {
                    let val = settings[a.id];

                    switch (a.type) {
                        case "bool":
                            val = settings[a.id] ? "да" : "нет"
                            break;
                        case 'dropdown':
                            let ind = a.values.findIndex(value => value.id == settings[a.id])
                            val = a.values[ind].name
                            break;
                    }
                    return `${a.name}: ${val}`;
                })
                .join(",<br />");
            data[index].settings =
                settingsString + `${sessionString}${testString}`;
        } else {
            data[index].settings =
                "Настройки: Не установлены" + `${sessionString}${testString}`;
        }
    });
};

const toggleYear = (id) => {
    activeYear.value = id;
    fetchData();
};
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
                    <reference-filter
                        :items="years"
                        :active="activeYear"
                        @toggleItem="toggleYear"
                        idField="year"
                        label="label"
                        addRoute="videoconferences.new"
                        labelgroup="videoconferences"
                        class="mb-4"
                    ></reference-filter>
                    <filter-table
                        :tableData="tableData"
                        :columns="tableColumns"
                        labelgroup="videoconferences"
                        @fetchData="fetchData"
                        :total="totalPage"
                        routeName="videoconferences"
                    >
                        <template #controls="{ data }">
                            <div v-if="data.is_active && !data.is_completed">
                                <a :href="route('videoconferences.room', data.session)">
                                    <Button
                                        icon="pi pi-video"
                                        text
                                    ></Button>
                                </a>
                            </div>
                            <div v-if="data.is_old && data.is_completed">
                                <a v-if="data.path_full" :href="data.path_full">
                                    <Button
                                        icon="pi pi-file-pdf"
                                        text
                                        severity="danger"
                                        size="large"
                                    ></Button>
                                </a>
                                <a :href="route('videoconferences.detail', {vc_id: data.id})">
                                    <Button
                                        icon="pi pi-info-circle"
                                        text
                                        severity="info"
                                        size="large"
                                    ></Button>
                                </a>
                                
                                <a v-if="data.assignment && data.assignment.moodle_code" :href="route('get_csv', {assignment_id: data.assignment})">
                                    <Button
                                        icon="pi pi-file-excel"
                                        text
                                        severity="success"
                                        size="large"
                                    ></Button>
                                </a>
                            </div>
                            <div v-if="data.is_old && !data.is_completed && !data.is_active">
                                <span class="text-danger">Не проведена</span>
                            </div>
                        </template>
                    </filter-table>
                </template>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
