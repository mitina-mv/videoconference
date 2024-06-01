<script setup>
import { onMounted, ref } from "vue";
import axios from "axios";
import ReferenceTable from "@/Components/Tables/ReferenceTable.vue";
import AuthenticatedLayout from "@/Layouts/AuthenticatedLayout.vue";
import { Head, usePage } from "@inertiajs/vue3";
import labels from "@/locales/ru.js";

const props = defineProps({
    addColumns: {
        type: Array,
        req: false,
    },
    entity: String,
});

const data = ref(null);
onMounted(() => {
    axios
        .get(`/api/${props.entity}/`)
        .then((response) => {
            data.value = response.data.data;
            // addToast(`получили`)
        })
        .catch((error) => {
            // addToast(`Неудачно`)
        });
});
</script>

<template>
    <Head :title="labels.page_titles[`reference_${entity}`]" />

    <AuthenticatedLayout>
        <template #header>
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                {{ labels.page_titles[`reference_${entity}`] }}
            </h2>
        </template>

        <div class="d-grid gap-4 content">
            <div class="content__container">
                <ReferenceTable
                    v-if="data"
                    :data="data"
                    :entity="entity"
                    :addColumns="addColumns"
                />
            </div>
        </div>
    </AuthenticatedLayout>
</template>
