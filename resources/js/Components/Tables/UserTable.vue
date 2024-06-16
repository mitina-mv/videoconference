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
    routeName: {
        type: String,
    },
    columns: {
        type: Array,
    },
    labelgroup: {
        type: String,
    },
    includeParamFrom: {
        type: Boolean,
        default: true,
    },
    includeCrudActions: {
        type: Boolean,
        default: true,
    },
    total: Number,
    defaultCountRecords: {
        type: Number,
        default: 25,
    },
    routeNameForm: {
        type: String,
        default: 'admin.new',
    },
    routeNameEdit: {
        type: String,
        default: 'admin.edit',
    },
});
const emit = defineEmits(["fetchData", "getPage"]);

const editingRows = ref([]);
const currentPage = ref(0);
const total = ref(props.total);
const tableData = ref(props.tableData);
const columns = ref(props.columns);
const deleteRow = ref(null);
const currentPageRow = ref(props.defaultCountRecords);
const currentPageRowOld = ref(null);
const deleteDialog = ref(false);
const dataNotFound = ref(tableData.value.length == 0);

const onRowEditSave = (event) => {
    let { newData, index } = event;
    let rowData = tableData.value[index];

    if (rowData == newData) return;

    axios
        .put(route(props.routeName + ".update", { id: newData.id }), newData)
        .then((response) => {
            toastService.showInfoToast(`Обновление ${labels[props.labelgroup].case[1]}`, "Данные успешно обновлены")
            tableData.value[index] = response.data;
        })
        .catch((error) => {
            // TODO уведомления
            toastService.showInfoToast(`Обновление ${labels[props.labelgroup].case[1]}`, "Одна ошибка и ты ошибся")
        });
};

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
            toastService.showInfoToast(`Удаление ${labels[props.labelgroup].case[1]}`, "Данные успешно удалены")

            tableData.value = tableData.value.filter(
                (val) => val.id !== deleteRow.value.id
            );

            deleteDialog.value = false;
            deleteRow.value = null;
        })
        .catch((error) => {
            toastService.showInfoToast(`Удаление ${labels[props.labelgroup].case[1]}}`, "Ошибка")
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
    <Toolbar class="mb-4">
        <template #start>
            <h4 class="m-0">{{ labels[labelgroup].title }}</h4>
        </template>
        <template #end>
            <a :href="includeParamFrom ? route(routeNameForm, labelgroup) : route(routeNameForm)">
                <Button
                    :label="'Добавить ' + labels[labelgroup].case[3]"
                    icon="pi pi-plus"
                    class="mr-2"
                />
            </a>
            <Button
                label="Обновить"
                icon="pi pi-refresh"
                severity="secondary"
                @click="fetchData"
            />
        </template>
    </Toolbar>

    <DataTable
        v-model:editingRows="editingRows"
        dataKey="id"
        @row-edit-save="onRowEditSave"
        tableClass="editable-cells-table"
        :value="tableData"
        class="p-datatable-small user-table"
        showGridlines
    >
        <template v-for="(column, index) in columns" :key="index">
            <Column
                v-if="column.type && column.type == 'bool'"
                :field="column.code"
                :sortable="column?.sort"
                :header="column.title"
                :style="column?.style"
            >
                <template #body="{ data }">
                    <i
                        class="pi"
                        :class="{
                            'pi-check-circle text-green-500': data[column.code],
                            'pi-times-circle text-red-400': !data[column.code],
                        }"
                    ></i>
                </template>
            </Column>

            <Column
                v-else-if="column.type && column.type == 'html'"
                :field="column.code"
                :sortable="column?.sort"
                :header="column.title"
                :style="column?.style"
            >
                <template #body="{ data }">
                    <div v-html="data[column.code]"></div>
                </template>
            </Column>

            <Column
                v-else
                :field="column.code"
                :sortable="column?.sort"
                :header="column.title"
                :style="column?.style"
            ></Column>
        </template>

        <Column
            :exportable="false"
            v-if="includeCrudActions"
            header="Управление"
            :style="{ width: '3%' }"
        >
            <template #body="row">
                <a :href="route(routeNameEdit, row.data.id)">
                    <Button
                        icon="pi pi-pencil"
                        severity="secondary"
                        text
                    ></Button>
                </a>
                <Button
                    icon="pi pi-trash"
                    severity="danger"
                    text
                    @click="confirmDelete(row.data)"
                ></Button>
            </template>
        </Column>

        <template #empty>
            <div class="table__empty-block">Данные не найдены</div>
        </template>

        <template #footer>
            <Paginator
                :rows="currentPageRow"
                :totalRecords="total"
                :rowsPerPageOptions="[5, 25, 50, 100]"
                v-model:rows="currentPageRow"
                @page="onPage"
            ></Paginator>
        </template>
    </DataTable>

    <Dialog
        v-model:visible="deleteDialog"
        :style="{ width: '450px' }"
        :header="'Удаление ' + labels[labelgroup].case[1]"
        :modal="true"
    >
        <div class="confirmation-content">
            <i
                class="pi pi-exclamation-triangle mr-3"
                style="font-size: 2rem; color: var(--yellow-500)"
            />
            <span
                >Вы уверены, что хотите удалить этого
                {{ labels[labelgroup].case[1] }}</span
            >
        </div>
        <template #footer>
            <Button
                label="Нет"
                icon="pi pi-times"
                severity="danger"
                text
                @click="hideDeleteDialog"
            />
            <Button label="Да" icon="pi pi-check" text @click="deleteItem" />
        </template>
    </Dialog>
</template>
