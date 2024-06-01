<script setup>
import { ref, onMounted } from "vue";
import AuthenticatedLayout from "@/Layouts/AuthenticatedLayout.vue";
import { Head, usePage } from "@inertiajs/vue3";
import toastService from "@/Services/toastService";
import LoadingSpinner from "@/Components/Common/LoadingSpinner.vue";
import Tests from "@/Components/Forms/Tests.vue";

const props = defineProps({
    id: {
        type: String,
        req: false,
    },
    disciplines: Array,
});

const id = props?.id || null;
const data = ref(null);
const userId = usePage().props.auth.user.id;

onMounted(() => {
    if (id) {
        axios
            .get("/api/tests/" + id + "?include=theme")
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
</script>

<template>
    <Head :title="id == null ? 'Добавление ' : 'Редактирование '" />

    <AuthenticatedLayout>
        <template #header>
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                {{ id == null ? "Добавление " : "Редактирование " }}
            </h2>
        </template>

        <div class="d-grid gap-4 content">
            <div class="content__container">
                <Tests
                    v-if="(id && data && disciplines) || (!id && disciplines)"
                    :data="data"
                    :disciplines="disciplines"
                    :userId="userId"
                ></Tests>
                <loading-spinner v-else></loading-spinner>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
