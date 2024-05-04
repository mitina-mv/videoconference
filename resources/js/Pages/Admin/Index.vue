<script setup>
import { ref, onMounted } from "vue";
import AuthenticatedLayout from "@/Layouts/AuthenticatedLayout.vue";
import { Head, usePage } from "@inertiajs/vue3";
import UserTable from "@/Components/Tables/UserTable.vue";
import axios from "axios";
import StudgroupsFilter from "@/Components/Admin/StudgroupsFilter.vue";
import { addToast } from '@/modules/toast';
import LoadingSpinner from "@/Components/Common/LoadingSpinner.vue";
import labels from '@/locales/ru.js';

const students = ref(null);
const teachers = ref(null);
const studgroups = ref(null);
const activeStudgroup = ref(null);

const studentsColumns = [
    {
        title: labels.user_fields.full_name.title,
        sort: true,
        code: "full_name",
        style: {
            width: '45%',
        }
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
            width: '25%',
        }
    }
]

onMounted(async () => {
    await fetchStudgroups();
    fetchStudents();
    fetchTeachers();
});

const toggleStudgroup = (id) => {
    activeStudgroup.value = id;
    fetchStudents()
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

const fetchStudents = () => {
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
        })
        .then((response) => {
            students.value = response.data.data;
        })
        .catch((error) => {});
};

const fetchTeachers = () => {
    axios
        .post("/api/users/search", {
            filters: [{ field: "role_id", operator: "=", value: "2" }],
            sort: [{ field: "lastname", direction: "asc" }],
            includes: [{"relation" : "studgroups"}]
        })
        .then((response) => {
            teachers.value = response.data.data;

            teachers.value.forEach((element, index) => {
                let namesString = element.studgroups.map((sg) => sg.name).join(', ');
                teachers.value[index].studgroups = namesString;
            });
        })
        .catch((error) => {});
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
                <div class="p-4 sm:p-8 bg-white shadow sm:rounded-lg">
                    <loading-spinner v-if="studgroups == null || students == null"></loading-spinner>
                    <div v-else>
                        <studgroups-filter
                            :studgroups="studgroups"
                            :active="activeStudgroup"
                            @toggleStudgroup="toggleStudgroup"
                        ></studgroups-filter>
                        <user-table
                            v-if="students"
                            :tableData="students"
                            :routeName="'api.users'"
                            :columns="studentsColumns"
                            :labelgroup="'students'"
                            @fetchData="fetchStudents"
                        ></user-table>
                    </div>
                </div>
            </div>

            <div class="content__container">
                <div class="p-4 sm:p-8 bg-white shadow sm:rounded-lg">
                    <loading-spinner v-if="teachers == null"></loading-spinner>
                    <user-table
                        v-else
                        :tableData="teachers"
                        :routeName="'api.users'"
                        :columns="teacterColumns"
                        :labelgroup="'teachers'"
                        @fetchData="fetchTeachers"
                    ></user-table>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
