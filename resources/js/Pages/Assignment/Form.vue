<script setup>
import { ref, onMounted, computed } from "vue";
import AuthenticatedLayout from "@/Layouts/AuthenticatedLayout.vue";
import { Head, usePage } from "@inertiajs/vue3";
import toastService from "@/Services/toastService";
import LoadingSpinner from "@/Components/Common/LoadingSpinner.vue";
import Assignments from "@/Components/Forms/Assignments.vue";
import labels from "@/locales/ru.js";

const props = defineProps({
    id: {
        type: String,
        req: false,
    },
    tests: Array,
    studgroups: Array,
});

const id = props?.id || null;
const data = ref(null);
const userId = usePage().props.auth.user.id;

onMounted(() => {
    if (id) {
        axios
            .get("/api/assignments/" + id + "?include=test,testlogs")
            .then((response) => {
                data.value = response.data.data;
            })
            .catch((error) => {
                toastService.showErrorToast(
                    `Редактирование данных`,
                    "Не удалось получить данные."
                );
            });
    }
});

const title = computed(() => {
    let str = '';
    if(id == null) str = 'Добавление ';
    else str = 'Редактирование ';

    return str + labels.assignments.case[1]
})
</script>

<template>
    <Head :title="title" />

    <AuthenticatedLayout>
        <template #header>
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                {{ title }}
            </h2>
        </template>

        <div class="d-grid gap-4 content">
            <div class="content__container">
                <Assignments
                    v-if="(id && data && studgroups) || (!id && studgroups)"
                    :data="data"
                    :tests="tests"
                    :studgroups="studgroups"
                    :userId="userId"
                ></Assignments>
                <loading-spinner v-else></loading-spinner>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
