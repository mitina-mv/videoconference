<script setup>
import { ref, onMounted } from "vue";
import AuthenticatedLayout from "@/Layouts/AuthenticatedLayout.vue";
import { Head, usePage } from "@inertiajs/vue3";
import UserTable from "@/Components/Tables/UserTable.vue";
import axios from "axios";
import LoadingSpinner from "@/Components/Common/LoadingSpinner.vue";
import labels from "@/locales/ru.js";
import ReferenceFilter from "@/Components/Admin/ReferenceFilter.vue";

const students = ref(null);
const studentsTotal = ref(null);
const teachers = ref(null);
const teachersTotal = ref(null);
const studgroups = ref(null);
const activeStudgroup = ref(null);

const studentsColumns = [
    {
        title: labels.user_fields.full_name.title,
        sort: true,
        code: "full_name",
        style: {
            width: "45%",
        },
    },
    {
        title: labels.user_fields.email.title,
        sort: true,
        code: "email",
    },
];

const teacterColumns = [
    ...studentsColumns,
    {
        title: labels.user_fields.studgroups.title,
        sort: false,
        code: "studgroups",
        style: {
            width: "25%",
        },
    },
];

onMounted(async () => {
    await fetchStudgroups();
    fetchStudents();
    fetchTeachers();
});

const toggleStudgroup = (id) => {
    activeStudgroup.value = id;
    fetchStudents();
};

const fetchStudgroups = async () => {
    await axios
        .get("/api/studgroups/")
        .then((response) => {
            studgroups.value = response.data.data;
            if (response.data.data)
                activeStudgroup.value = response.data.data[0].id;
            // addToast(`получили`)
        })
        .catch((error) => {
            // addToast(`Неудачно`)
        });
};

const fetchStudents = (params = {}) => {
    axios
        .post("/api/users/search", {
            filters: [
                { field: "role_id", operator: "=", value: "3" },
                {
                    field: "studgroup_id",
                    operator: "=",
                    value: activeStudgroup.value,
                },
            ],
            sort: [{ field: "lastname", direction: "asc" }],
            ...params,
        })
        .then((response) => {
            students.value = response.data.data;
            studentsTotal.value = response.data.meta.total;
        })
        .catch((error) => {});
};

const fetchTeachers = (params = {}) => {
    axios
        .post("/api/users/search", {
            filters: [{ field: "role_id", operator: "=", value: "2" }],
            sort: [{ field: "lastname", direction: "asc" }],
            includes: [{ relation: "studgroups" }],
            ...params,
        })
        .then((response) => {
            teachers.value = response.data.data;

            teachers.value.forEach((element, index) => {
                let namesString = element.studgroups
                    .map((sg) => sg.name)
                    .join(", ");
                teachers.value[index].studgroups = namesString;
            });

            teachersTotal.value = response.data.meta.total;
        })
        .catch((error) => {});
};

const fetchTeachersPageData = (page, limit) => {
    fetchTeachers({
        page: page + 1,
        limit: limit,
    });
};

const fetchStudentsPageData = (page, limit) => {
    fetchStudents({
        page: page + 1,
        limit: limit,
    });
};
</script>

<template>
    <Head title="Админ-панель" />

    <AuthenticatedLayout>
        <template #header>
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                Админ-панель
            </h2>
        </template>

        <div class="d-grid gap-4 content">
            <div class="content__container">
                <loading-spinner
                    v-if="studgroups == null || students == null"
                ></loading-spinner>
                <template v-else>
                    <reference-filter
                        :items="studgroups"
                        :active="activeStudgroup"
                        @toggleItem="toggleStudgroup"
                        addRoute="admin.reference.studgroups"
                    ></reference-filter>
                    <user-table
                        v-if="students"
                        :tableData="students"
                        :routeName="'api.users'"
                        :columns="studentsColumns"
                        :labelgroup="'students'"
                        @fetchData="fetchStudents"
                        @getPage="fetchStudentsPageData"
                        :total="studentsTotal"
                    ></user-table>
                </template>
            </div>

            <div class="content__container">
                <loading-spinner v-if="teachers == null"></loading-spinner>
                <user-table
                    v-else
                    :tableData="teachers"
                    :routeName="'api.users'"
                    :columns="teacterColumns"
                    :labelgroup="'teachers'"
                    @fetchData="fetchTeachers"
                    @getPage="fetchTeachersPageData"
                    :total="teachersTotal"
                ></user-table>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
