<script setup>
import AuthenticatedLayout from "@/Layouts/AuthenticatedLayout.vue";
import DeleteUserForm from "./Partials/DeleteUserForm.vue";
import UpdatePasswordForm from "./Partials/UpdatePasswordForm.vue";
import UpdateProfileInformationForm from "./Partials/UpdateProfileInformationForm.vue";
import { Head } from "@inertiajs/vue3";

defineProps({
    status: {
        type: String,
    },
});
</script>

<template>
    <Head title="Профиль" />

    <AuthenticatedLayout>
        <template #header>
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                Профиль
            </h2>
        </template>

        <div class="d-grid gap-4 content">
            <div class="content__container">
                <UpdateProfileInformationForm
                    :status="status"
                    class="max-w-xl"
                />
            </div>

            <div class="content__container">
                <UpdatePasswordForm class="max-w-xl" />
            </div>

            <div class="content__container">
                <DeleteUserForm class="max-w-xl" />
            </div>
        </div>
    </AuthenticatedLayout>
</template>
