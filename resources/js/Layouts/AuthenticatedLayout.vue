<script setup>
import { ref } from 'vue';
import ApplicationLogo from '@/Components/ApplicationLogo.vue';
import Dropdown from '@/Components/Dropdown.vue';
import DropdownLink from '@/Components/DropdownLink.vue';
import NavLink from '@/Components/NavLink.vue';
import ResponsiveNavLink from '@/Components/ResponsiveNavLink.vue';
import { Link } from '@inertiajs/vue3';
import Toast from "primevue/toast";

const showingNavigationDropdown = ref(false);
</script>

<template>
    <div class="container">
        <aside class="sidebar">
            <div class="logo">
                <Link :href="route('dashboard')">
                    <ApplicationLogo class="block h-9 w-auto fill-current text-gray-800" />
                </Link>
            </div>
            <div class="user-info">
                <div class="name">{{ $page.props.auth.user.name }}</div>
                <div class="email">{{ $page.props.auth.user.email }}</div>
            </div>
            <ul class="menu">
                <li class="menu-item">
                    <NavLink :href="route('dashboard')" :active="route().current('dashboard')">
                        Dashboard
                    </NavLink>
                </li>
                <li class="menu-item">
                    <NavLink :href="route('profile.edit')" :active="route().current('profile.edit')">
                        Profile
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
                    <a class="back-button" :href="route($page.props.backLink)">Back</a>
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
