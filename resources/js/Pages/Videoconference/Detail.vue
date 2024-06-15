<script setup>
import { ref, onMounted } from "vue";
import AuthenticatedLayout from "@/Layouts/AuthenticatedLayout.vue";
import Videoconference from "@/Components/Reports/Videoconference.vue";
import { Head, usePage } from "@inertiajs/vue3";

const props = defineProps({
    test: [Object, null],
    vc: [Object, null],
    error: [String, null],
    groups: [Object, null],
});
const vcInfoFields = [
    {
        label: "Название",
        value: props?.vc?.name,
    },
    {
        label: "Дата проведения",
        value: props?.vc?.date,
    },
    {
        label: "Тип",
        value: props?.vc?.type,
    },
    {
        label: "Преподаватель",
        value: props?.vc?.user,
    },
    {
        label: "Количество проверок присуствия",
        value: props?.vc?.count_check,
    },
    {
        label: "Группы",
        value: props?.vc?.studgroups?.join(", "),
    },
];
</script>

<template>
    <Head :title="'Конференция «' + (vc?.name || 'ошибка') + '»'" />

    <AuthenticatedLayout>
        <template #header>
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                {{ "Конференция «" + (vc?.name || "ошибка") + "»" }}
            </h2>
        </template>

        <div class="d-grid gap-4 content">
            <div class="content__container detail-vc">
                <div v-if="error">{{ error }}</div>
                <template v-else>
                    <div class="d-grid grid-col-4 mb-3 gap-2">
                        <div class="d-grid grid-self-col-3">
                            <div class="d-grid grid-col-2 mb-3 gap-2">
                                <div class="assignment-info">
                                    <h3>Основные данные</h3>
                                    <p
                                        v-for="(field, index) in vcInfoFields"
                                        :key="index"
                                    >
                                        <b>{{ field.label }}: </b>
                                        <span>{{ field.value }}</span>
                                    </p>
                                    <p v-if="vc.path_full">
                                        <b>Файл отчета: </b>
                                        <a :href="vc.path_full" class="text-success">
                                            Скачать
                                        </a>
                                    </p>
                                </div>
                                <div class="vc-files">
                                    <h3>Файлы</h3>
                                    <div v-if="vc.files"></div>
                                    <p v-else>Файлы не прикреплены</p>
                                </div>
                            </div>

                            <template v-if="$page.props.auth.user.role_id == 2">
                                <h2>Отчет</h2>
                                <Videoconference
                                    :test="test"
                                    :vc="vc"
                                    :groups="groups"
                                    :inctuleHeader="false"
                                    :includeComments="true"
                                    :includeHrefDetail="true"
                                ></Videoconference>
                            </template>
                        </div>

                        <div class="vc-messages">
                            <h3>Чат</h3>
                            <div v-if="vc.messages">
                                <div
                                    v-for="message in vc.messages"
                                    :key="message.timestamp"
                                    class="chat-message"
                                    :class="message.class || ''"
                                >
                                    <strong>{{ message.username }}:</strong>
                                    {{ message.text }}
                                </div>
                            </div>
                            <p v-else>В чате не было сообщений</p>
                        </div>
                    </div>
                </template>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
