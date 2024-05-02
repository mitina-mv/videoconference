<script setup>
import { ref, onMounted } from "vue";
import AuthenticatedLayout from "@/Layouts/AuthenticatedLayout.vue";
import { Head, usePage } from "@inertiajs/vue3";
import axios from "axios";
import { addToast } from '@/modules/toast';
import UserForm from '@/Components/Forms/User.vue'

const props = defineProps({
    id: String,
    role: {
        type: String,
        req: false
    },
})

const id = props.id
const data = ref(null)
const studgroups = ref(null)

onMounted(() => {
    console.log(id);
    axios.get('/api/users/' + id)
    .then((response) => {
        data.value = response.data.data
        console.log(data.value);
        // addToast(`получили`)
    })
    .catch((error) => {
        // addToast(`Неудачно`)
    })

    axios.get('/api/studgroups/')
    .then((response) => {
        studgroups.value = response.data.data
        // addToast(`получили`)
    })
    .catch((error) => {
        // addToast(`Неудачно`)
    })
})
</script>

<template>
    <Head title="Редактирование" />

    <AuthenticatedLayout>
        <template #header>
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                Редактирование
            </h2>
        </template>

        <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">
                <div class="p-4 sm:p-8 bg-white shadow sm:rounded-lg">
                    <UserForm v-if="data && studgroups" :data="data" :studgroups="studgroups" ></UserForm>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
