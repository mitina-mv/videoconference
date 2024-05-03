<script setup>
import { ref, onMounted } from "vue";
import AuthenticatedLayout from "@/Layouts/AuthenticatedLayout.vue";
import { Head, usePage } from "@inertiajs/vue3";
import UserTable from "@/Components/Tables/UserTable.vue";
import axios from "axios";
import StudgroupsFilter from '@/Components/Admin/StudgroupsFilter.vue'

const students = ref(null)
const teachers = ref(null)
const studgroups = ref(null)
const activeStudgroup = ref(null)

const studentsColumns = [
    {
        code: 'full_name',
    },
    {
        code: 'email',
    },
]

onMounted(() => {
    axios.post('/api/users/search', {
        "filters" : [
            {"field" : "role_id", "operator" : "=", "value" : "2"},
        ],        
        "sort" : [
            {"field" : "lastname", "direction" : "asc"},
        ],
    }, )
    .then((response) => {
        students.value = response.data.data
    })
    .catch((error) => {
    })

    axios.post('/api/users/search', {
        "filters" : [
            {"field" : "role_id", "operator" : "=", "value" : "3"},
        ],        
        "sort" : [
            {"field" : "lastname", "direction" : "asc"},
        ],
    }, )
    .then((response) => {
        teachers.value = response.data.data
    })
    .catch((error) => {
    })
    
    fetchStudgroups();

})

const toggleStudgroup = (id) => {
    activeStudgroup.value = id
}

const fetchStudgroups = () => {
    axios.get('/api/studgroups/')
    .then((response) => {
        studgroups.value = response.data.data
        if(response.data.data)
            activeStudgroup.value = response.data.data[0]
        // addToast(`получили`)
    })
    .catch((error) => {
        // addToast(`Неудачно`)
    })
}
</script>

<template>
    <Head title="Profile" />

    <AuthenticatedLayout>
        <template #header>
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                Profile
            </h2>
        </template>

        <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">
                <div class="p-4 sm:p-8 bg-white shadow sm:rounded-lg">
                    <studgroups-filter :studgroups="studgroups" :active="activeStudgroup" ></studgroups-filter>
                    <user-table v-if="students" :tableData="students" :routeName="'api.users'" :columns="studentsColumns" :labelgroup="'students'"></user-table>
                </div>
            </div>

            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">
                <div class="p-4 sm:p-8 bg-white shadow sm:rounded-lg">
                    <user-table v-if="teachers" :tableData="teachers" :routeName="'api.users'" :columns="studentsColumns" :labelgroup="'teachers'"></user-table>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
