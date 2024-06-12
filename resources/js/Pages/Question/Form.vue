<script setup>
import { ref, onMounted, computed } from "vue";
import AuthenticatedLayout from "@/Layouts/AuthenticatedLayout.vue";
import { Head, usePage } from "@inertiajs/vue3";
import toastService from "@/Services/toastService";
import Question from "@/Components/Forms/Question.vue";
import LoadingSpinner from "@/Components/Common/LoadingSpinner.vue";
import labels from "@/locales/ru.js";

const props = defineProps({
    id: {
        type: String,
        req: false,
    },
    themes: Array,
    disciplines: Array,
});

const id = props?.id || null;
const data = ref(null);

onMounted(() => {
    if (id) {
        axios
            .get("/api/questions/" + id + "?include=answers,theme")
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

    return str + labels.questions.case[1]
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
                <question
                    v-if="(id && data && themes) || (!id && themes)"
                    :data="data"
                    :themes="themes"
                    :disciplines="disciplines"
                ></question>
                <loading-spinner v-else></loading-spinner>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
