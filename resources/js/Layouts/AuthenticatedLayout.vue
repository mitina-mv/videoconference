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

const token = usePage().props.auth.user.token
axios.defaults.headers.common['Authorization'] = `Bearer ${token}`

const showingNavigationDropdown = ref(false);
const links = [
    {
        name: labels.page_titles.users,
        route: "admin.index",
        icon: 'pi-user',
        roles: [1]
    },
    {
        name: labels.page_titles.reference_studgroups,
        route: "admin.reference.studgroups",
        icon: 'pi-users',
        roles: [1, 2]
    },
    {
        name: labels.page_titles.reference_disciplines,
        route: "admin.reference.disciplines",
        icon: 'pi-book',
        roles: [1, 2]
    },
    {
        name: labels.page_titles.reference_themes,
        route: "admin.reference.themes",
        icon: 'pi-bookmark',
        roles: [1, 2]
    },
    {
        name: labels.page_titles.questions,
        route: "questions.index",
        icon: 'pi-question',
        roles: [2]
    },
    {
        name: labels.page_titles.tests,
        route: "tests.index",
        icon: 'pi-objects-column',
        roles: [2]
    },
    {
        name: labels.page_titles.assignments,
        route: "assignments.index",
        icon: 'pi-calculator',
        roles: [2]
    },
    {
        name: labels.page_titles.videoconferences,
        route: "videoconferences.index",
        icon: 'pi-video',
        roles: [2]
    },
    {
        name: labels.page_titles.videoconferences,
        route: "videoconferences.my",
        icon: 'pi-video',
        roles: [3]
    },
    {
        name: labels.page_titles.assignments,
        route: "assignments.my",
        icon: 'pi-calculator',
        roles: [3]
    },
];

const userRole = usePage().props.auth.user.role_id;

const filteredLinks = computed(() => {
    return links.filter((link) => link.roles.includes(userRole));
});
</script>

<template>
    <div class="body">
        <aside class="sidebar">
            <div class="logo">
                <ApplicationLogo />
            </div>
            <div class="user-info">
                <div class="user-avatar">
                    <!-- {{ $page.props.auth.user.initials }} -->
                    <i class="pi pi-user" style="font-size: 1.5rem"></i>
                </div>
                <div>
                    <div class="name">
                        <a :href="route('profile.edit')">
                            {{ $page.props.auth.user.full_name }}
                        </a>
                    </div>
                    <!-- <div class="email">{{ $page.props.auth.user.email }}</div> -->
                </div>

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
                        <i class="pi" :class="link.icon" style="font-size: 1rem"></i>
                        {{ link.name }}
                    </NavLink>
                </li>
            </ul>
            <div class="logout">
                <DropdownLink :href="route('logout')" method="post" as="button">
                    <i class="pi pi-sign-out"></i>
                    Выйти
                </DropdownLink>
            </div>
        </aside>

        <div class="main-content">
            <header class="header" v-if="$slots.header">
                <div v-if="$page.props.backLink && !$page.props.backLink.includes('http')">
                    <a class="back-button" :href="route($page.props.backLink)"
                        ><i class="pi pi-arrow-left"></i
                    ></a>
                </div>
                <div v-if="$page.props.backLink && $page.props.backLink.includes('http')">
                    <a class="back-button" :href="$page.props.backLink"
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
