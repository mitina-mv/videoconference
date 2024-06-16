<script setup>
import { ref, onMounted } from "vue";
import AuthenticatedLayout from "@/Layouts/AuthenticatedLayout.vue";
import { Head, usePage } from "@inertiajs/vue3";
import UserTable from "@/Components/Tables/UserTable.vue";
import axios from "axios";
import LoadingSpinner from "@/Components/Common/LoadingSpinner.vue";
import labels from "@/locales/ru.js";
import ReferenceFilter from "@/Components/Admin/ReferenceFilter.vue";
import toastService from "@/Services/toastService";
import Button from "primevue/button";

const students = ref(null);
const studentsTotal = ref(null);
const teachers = ref(null);
const teachersTotal = ref(null);
const studgroups = ref(null);
const activeStudgroup = ref(null);
const admins = ref(null)

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
    fetchNoVerifyAdmins()
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
        })
        .catch((error) => {
            toastService.showErrorToast("Получение данных", "Не удалось получить данные");
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
        .catch((error) => {
            toastService.showErrorToast("Получение данных", "Не удалось получить данные");
        });
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
        .catch((error) => {
            toastService.showErrorToast("Получение данных", "Не удалось получить данные");
        });
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

const fetchNoVerifyAdmins = () => {
    axios.post("/api/users/search", {
        filters: [
            { field: "role_id", operator: "=", value: "1" },
            { field: "is_verify", operator: "=", value: false }
        ],
    })
    .then((response) => {
        admins.value = response.data.data;
    })
    .catch((error) => {
        toastService.showErrorToast("Получение данных", "Не удалось получить данные");
    });

}
const addAdmin = (id) => {
    axios.patch('/api/users/' + id, {
        is_verify: true
    })
    .then((response) => {
        admins.value = admins.value.filter(a => a.id != id);
    })
    .catch((error) => {
        toastService.showErrorToast("Предоставление доступа", "Не удалось предоставить доступ");
    });
}
const deleteAdmin = (id) => {
    axios.delete('/api/users/' + id + '?force=true')
    .then((response) => {
        admins.value = admins.value.filter(a => a.id != id);
    })
    .catch((error) => {
        toastService.showErrorToast("Удаление администратора", "Не удалось удалить администратора");
    });
}
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
            <div class="content__container mb-3" v-if="admins && admins.length > 0">
                <h3>Новые заявки на получение доступа администратора</h3>
                <div class="admins d-grid grid-col-3 gap-3 mt-3">
                    <div class="admin-card" v-for="admin in admins" :key="admin.id">
                        <b>{{ admin.full_name }}</b>
                        <span class="text-gray mb-1">{{ admin.email }}</span>
                        <div class="d-flex gap-2">
                            <Button severity="success" label="Допуск" @click="addAdmin(admin.id)" />
                            <Button severity="danger" label="Удалить" @click="deleteAdmin(admin.id)" />
                        </div>
                    </div>

                </div>
            </div>
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
