<script setup>
import { Head, Link } from "@inertiajs/vue3";
import ApplicationLogo from "@/Components/ApplicationLogo.vue";

defineProps({
    canLogin: {
        type: Boolean,
    },
    canRegister: {
        type: Boolean,
    },
    laravelVersion: {
        type: String,
        required: true,
    },
    phpVersion: {
        type: String,
        required: true,
    },
});
</script>

<template>
    <Head title="Добро пожаловать" />

    <div class="welcome-bg min-h-screen">
        <div class="overlay w-full h-full flex">
            <div
                class="welcome-block p-4 flex flex-col items-center rounded-lg ml-64 mt-64"
            >
                <h1 class="text-5xl font-semibold text-indigo-600/60 mb-8">
                    Добро пожаловать!
                </h1>
                <i class="text-xl font-semibold text-indigo-500/60 self-start">
                    Distant - Связь на новом уровне
                </i>
                <Link
                    class="mt-8 border-b-2 self-start border-indigo-500/60 font-bold text-2xl px-4 py-2 text-indigo-500/60"
                    :href="route('login')"
                >
                    Войти в систему
                </Link>
            </div>
        </div>
    </div>
</template>

<style>
.welcome-bg {
    background: linear-gradient(
            90deg,
            rgba(255, 255, 255, 1) 0%,
            rgba(255, 255, 255, 0) 100%
        ),
        top -100px right -100px url("/public/welcome-bg.jpg");
    background-size: cover;
}
.overlay {
    height: 100vh;
    backdrop-filter: blur(4px);
}
.welcome-block {
    background: #ffffff42;
    height: fit-content;
    padding: 2em;
}
</style>
