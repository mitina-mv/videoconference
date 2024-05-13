<script setup>
import { onMounted, ref, watch } from "vue";
import DataTable from "primevue/datatable";
import Column from "primevue/column";
import Button from "primevue/button";
import toastService from "@/Services/toastService";
import InputText from "primevue/inputtext";
import labels from "@/locales/ru.js";
import DeleteDialog from "@/Components/Dialogs/DeleteDialog.vue";
import Dialog from "primevue/dialog";
import Toolbar from "primevue/toolbar";
import Paginator from "primevue/paginator";

const props = defineProps({
    tableData: {
        type: Object,
    },
    columns: {
        type: Array,
    },
    labelgroup: {
        type: String,
    },
    total: Number,
});
const emit = defineEmits(["fetchData", "getPage"]);

const currentPage = ref(0);
const total = ref(props.total);
const tableData = ref(props.tableData);
const columns = ref(props.columns);
const deleteRow = ref(null);
const currentPageRow = ref(25);
const currentPageRowOld = ref(null);
const deleteDialog = ref(false);
const dataNotFound = ref(tableData.value.length == 0);
const filters = ref({})
const loading = ref(true)

onMounted(() => {
    // перебираем columns для установки filters
})

const confirmDelete = (row) => {
    deleteRow.value = row;
    deleteDialog.value = true;
};
const hideDeleteDialog = () => {
    deleteDialog.value = false;
    deleteRow.value = null;
};

const deleteItem = () => {
    if (deleteRow.value == null) return;

    axios
        .delete(route(props.routeName + ".destroy", { id: deleteRow.value.id }))
        .then((response) => {
            toastService.showInfoToast(
                `Удаление ${labels[props.labelgroup].case[1]}`,
                "Данные успешно удалены"
            );

            tableData.value = tableData.value.filter(
                (val) => val.id !== deleteRow.value.id
            );

            deleteDialog.value = false;
            deleteRow.value = null;
        })
        .catch((error) => {
            toastService.showInfoToast(
                `Удаление ${labels[props.labelgroup].case[1]}}`,
                "Ошибка"
            );
        });
};

const fetchData = (id) => {
    emit("fetchData");
};

const onPage = ({ page }) => {
    if (
        currentPage.value == page &&
        currentPageRowOld.value == currentPageRow.value
    )
        return;
    emit("getPage", page, currentPageRow.value);
    currentPage.value = page;
    currentPageRowOld.value = currentPageRow.value;
};

watch(
    () => props.tableData,
    (newValue) => {
        tableData.value = newValue;

        if (newValue && newValue.length > 0) {
            dataNotFound.value = false;
        } else {
            dataNotFound.value = true;
        }
    }
);
</script>

<template>
    <DataTable
        v-model:filters="filters"
        :value="tableData"
        dataKey="id"
        filterDisplay="row"
        :loading="loading"
        showGridlines
    >
        <template #empty> <div class="table__empty-block">Данные не найдены</div> </template>
        <template #loading> <div class="table__empty-block">Загрузка данных...</div> </template>
    </DataTable>
</template>
