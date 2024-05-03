<script setup>
import { ref, onMounted } from "vue";
import AuthenticatedLayout from "@/Layouts/AuthenticatedLayout.vue";
import { Head, usePage } from "@inertiajs/vue3";
import axios from "axios";
import { addToast } from '@/modules/toast';
import UserForm from '@/Components/Forms/User.vue'
import labels from '@/locales/ru.js';

const props = defineProps({
    labelgroup: String,
    id: {
        type: String,
        req: false
    },
    role: {
        type: Number,
        req: false
    },
})

const id = props?.id || null
const data = ref(null)
const studgroups = ref(null)

onMounted(() => {
    if(id) {
        axios.get('/api/users/' + id)
            .then((response) => {
                data.value = response.data.data
                // addToast(`получили`)
            })
            .catch((error) => {
                // addToast(`Неудачно`)
            })

            console.log(props.role);

        if(props.role == 2) {
            axios.get('/api/users/' + id + '/studgroups')
            .then((response) => {
                data.value.studgroups = []
                response.data.data.forEach(element => {
                    data.value.studgroups.push(element.id)
                });
                // addToast(`получили`)
            })
            .catch((error) => {
                // addToast(`Неудачно`)
            })
        }
    }

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
    <Head :title="(id == null ? 'Добавление ' : 'Редактирование ') + labels[labelgroup].case[1]" />

    <AuthenticatedLayout>
        <template #header>
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                {{ (id == null ? 'Добавление ' : 'Редактирование ') + labels[labelgroup].case[1] }}
            </h2>
        </template>

        <div class="d-grid gap-4 content">
            <div class="content__container">
                <div class="p-4 sm:p-8 bg-white shadow sm:rounded-lg">
                    <UserForm v-if="(id && data && studgroups) || (!id && studgroups)" :data="data" :studgroups="studgroups" :labelgroup="labelgroup" :role="role" ></UserForm>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
