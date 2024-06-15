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
import MultiSelect from "primevue/multiselect";
import Calendar from "primevue/calendar";

const props = defineProps({
    tableData: {
        type: Object,
    },
    columns: {
        type: Object,
    },
    labelgroup: {
        type: String,
    },
    total: Number,
    includeControls: {
        type: Boolean,
        default: true,
    },
    includeCrudActions: {
        type: Boolean,
        default: true,
    },
    routeName: {
        type: String,
        default: "admin.new",
    },
});
const emit = defineEmits(["fetchData"]);

const currentPage = ref(0);
const total = ref(props.total);
const tableData = ref(props.tableData);
const columns = ref(props.columns);
const deleteRow = ref(null);
const currentPageRow = ref(25);
const currentPageRowOld = ref(null);
const deleteDialog = ref(false);
const dataNotFound = ref(tableData.value.length == 0);
const filters = ref({});
const loading = ref(true);

onMounted(() => {
    Object.keys(columns.value).forEach((key) => {
        const column = columns.value[key];

        if (column.filter) {
            filters.value[key] = null;
        }
    });

    loading.value = false;
});

const transformFilters = () => {
    const transformed = [];
    for (const [key, value] of Object.entries(filters.value)) {
        if (!value || value.length == 0) continue;

        let field = columns.value[key].filter.field || key;
        let operator = "="

        switch(columns.value[key].filter.type)
        {
            case "select":
                operator = "in"
                break;
            case 'calendar':
                operator = ">="
                break;
        }

        transformed.push({
            field: field,
            operator: operator,
            value: value,
        });
    }
    return transformed;
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
        .delete(
            route("api." + props.routeName + ".destroy", {
                id: deleteRow.value.id,
            })
        )
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

const fetchData = (page = null) => {
    loading.value = true;
    emit("fetchData", transformFilters(), page, currentPageRow.value);
    loading.value = false;
};

const onPage = ({ page }) => {
    if (
        currentPage.value == page &&
        currentPageRowOld.value == currentPageRow.value
    )
        return;

    fetchData(page);

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
watch(
    filters,
    () => {
        fetchData(currentPage.value);
    },
    { deep: true }
);
</script>

<template>
    <DataTable
        :value="tableData"
        dataKey="id"
        :filterDisplay="(Object.keys(filters)['length'] > 0) ? 'row' : null"
        showGridlines
        :loading="loading"
        class="p-datatable-small user-table unvisible-clear-filter-btn"
    >
        <Column
            v-for="(column, key) in columns"
            :key="key"
            :field="column.code"
            :header="column.title"
            :style="column.style"
        >
            <template #filter v-if="column.filter">
                <MultiSelect
                    v-if="column.filter.type == 'select'"
                    v-model="filters[key]"
                    :options="columns[key].filter.options"
                    :optionLabel="columns[key].filter.label || 'name'"
                    :optionValue="columns[key].filter.value || 'id'"
                    :placeholder="column.filter.placeholder || 'Выберите...'"
                    display="chip"
                    filter
                    :maxSelectedLabels="2"
                />
                <Calendar
                    v-if="column.filter.type == 'calendar'"
                    v-model="filters[key]"
                    showIcon iconDisplay="input"
                    :dateFormat="column.filter.format || 'dd.mm.yy'"
                    :placeholder="column.filter.placeholder || 'Выберите дату'"
                />
            </template>

            <template #body="{ data }">
                <i
                    class="pi"
                    :class="{
                        'pi-check-circle text-green-500': data[column.code],
                        'pi-times-circle text-red-400': !data[column.code],
                    }"
                    v-if="column.type && column.type == 'bool'"
                ></i>
                <div
                    v-html="data[column.code]"
                    v-else-if="column.type && column.type == 'html'"
                ></div>
                <div v-else>
                    {{ data[column.code] }}
                </div>
            </template>
        </Column>

        <Column
            :exportable="false"
            v-if="includeControls"
            header="Управление"
            :style="{ width: '2%' }"
        >
            <template #body="row">
                <template v-if="includeCrudActions">
                    <div v-if="(typeof row.data.is_old !== 'undefined' && !row.data.is_old) || (typeof row.data.is_old == 'undefined')">
                        <a :href="route(routeName + '.edit', row.data.id)">
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
                    </div>
                </template>
                <slot name="controls" :data="row.data"></slot>
            </template>
        </Column>

        <template #empty>
            <div class="table__empty-block">Данные не найдены</div>
        </template>

        <template #loading>
            <div class="table__empty-block">Загрузка данных...</div>
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

    <DeleteDialog
        :visible="deleteDialog"
        @close="hideDeleteDialog"
        @delete="deleteItem"
        labelgroup="assignments"
    />
</template>

<style>
.unvisible-clear-filter-btn button.p-column-filter-menu-button.p-link {
    display: none;
    margin: 0;
}

.unvisible-clear-filter-btn
    button.p-column-filter-clear-button.p-link.p-hidden-space {
    display: none;
}
.p-datatable .p-datatable-thead > tr > th {
    background: none;
}
.p-datatable-table {
    background: #fff;
}
</style>
