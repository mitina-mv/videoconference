<script setup>
import { onMounted, ref } from "vue";
import axios from "axios";
import ReferenceTable from "@/Components/Tables/ReferenceTable.vue";
import AuthenticatedLayout from "@/Layouts/AuthenticatedLayout.vue";
import { Head, usePage } from "@inertiajs/vue3";
import labels from '@/locales/ru.js';

const props = defineProps({
    entity: String
})

const data = ref(null)
onMounted(() => {
    axios.get(`/api/${props.entity}/`)
    .then((response) => {
        data.value = response.data.data
        // addToast(`получили`)
    })
    .catch((error) => {
        // addToast(`Неудачно`)
    })
})
</script>

<template>    
    <Head :title="labels.page_titles[`reference_${entity}`]" />

    <AuthenticatedLayout>
        <template #header>
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                {{ labels.page_titles[`reference_${entity}`] }}
            </h2>
        </template>

        <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">
                <div class="p-4 sm:p-8 bg-white shadow sm:rounded-lg">
                    <ReferenceTable v-if="data" :data="data" :entity="entity" />
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>