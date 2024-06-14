<script setup>
// import { ref, onMounted } from "vue";
// import AuthenticatedLayout from "@/Layouts/AuthenticatedLayout.vue";
import Videoconference from "@/Components/Reports/Videoconference.vue";

const props = defineProps({
    test: [Object, null],
    vc: [Object, null],
    error: [String, null],
    groups: [Object, null],
});
</script>

<template>
<div class="d-grid gap-4 content">
    <div class="content__container report report_assignment">
        <div v-if="error">{{ error }}</div>
        <Videoconference
            :test="test"
            :vc="vc"
            :groups="groups"
            :inctuleHeader="true"
            :includeComments="true"
            :includeHrefDetail="true"
        ></Videoconference>
    </div>
</div>
</template>