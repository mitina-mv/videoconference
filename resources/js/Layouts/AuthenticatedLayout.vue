<script setup>
import { ref, computed } from "vue";
import ApplicationLogo from "@/Components/ApplicationLogo.vue";
import Dropdown from "@/Components/Dropdown.vue";
import DropdownLink from "@/Components/DropdownLink.vue";
import { Head, usePage } from "@inertiajs/vue3";
import NavLink from "@/Components/NavLink.vue";
import ResponsiveNavLink from "@/Components/ResponsiveNavLink.vue";
import { Link } from "@inertiajs/vue3";
import labels from "@/locales/ru.js";
import Toast from "primevue/toast";

const showingNavigationDropdown = ref(false);
const links = [
    {
        name: labels.page_titles.users,
        route: "admin.index",
        roles: [1]
    },
    {
        name: labels.page_titles.reference_studgroups,
        route: "admin.reference.studgroups",
        roles: [1, 2]
    },
    {
        name: labels.page_titles.reference_disciplines,
        route: "admin.reference.disciplines",
        roles: [1, 2]
    },
    {
        name: labels.page_titles.reference_themes,
        route: "admin.reference.themes",
        roles: [1, 2]
    },
    {
        name: labels.page_titles.questions,
        route: "questions.index",
        roles: [2]
    },
    {
        name: labels.page_titles.tests,
        route: "tests.index",
        roles: [2]
    },
    {
        name: labels.page_titles.assignments,
        route: "assignments.index",
        roles: [2]
    },
    {
        name: labels.page_titles.videoconferences,
        route: "videoconferences.index",
        roles: [2]
    },
];

const userRole = usePage().props.auth.user.role_id;

const filteredLinks = computed(() => {
    return links.filter((link) => link.roles.includes(userRole));
});
</script>

<template>
    <div class="container">
        <aside class="sidebar">
            <div class="logo">
                <Link :href="route('dashboard')">
                    <ApplicationLogo
                        class="block h-9 w-auto fill-current text-gray-800"
                    />
                </Link>
            </div>
            <div class="user-info">
                <div class="name">
                    <a :href="route('profile.edit')">
                        {{ $page.props.auth.user.full_name }}
                    </a>
                </div>
                <div class="email">{{ $page.props.auth.user.email }}</div>
            </div>
            <ul class="menu">
                <li
                    class="menu-item"
                    v-for="link in filteredLinks"
                    :key="link.route"
                >
                    <NavLink
                        :href="route(link.route)"
                        :active="route().current(link.route)"
                    >
                        {{ link.name }}
                    </NavLink>
                </li>
            </ul>
            <div class="logout">
                <DropdownLink :href="route('logout')" method="post" as="button">
                    Log Out
                </DropdownLink>
            </div>
        </aside>

        <div class="main-content">
            <header class="header" v-if="$slots.header">
                <div v-if="$page.props.backLink">
                    <a class="back-button" :href="route($page.props.backLink)"
                        ><i class="pi pi-arrow-left"></i
                    ></a>
                </div>
                <div>
                    <slot name="header" />
                </div>
            </header>

            <main>
                <slot />
                <Toast />
            </main>
        </div>
    </div>
</template>
