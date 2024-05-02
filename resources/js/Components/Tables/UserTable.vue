<script setup>
import { onMounted, ref } from "vue";
import DataTable from "primevue/datatable";
import Column from "primevue/column";
import Button from "primevue/button";
import { addToast } from '@/modules/toast';
import InputText from "primevue/inputtext";
import labels from '@/locales/ru.js';
import DeleteDialog from '@/Components/Dialogs/DeleteDialog.vue'
import Dialog from "primevue/dialog";

const props = defineProps({
    tableData: {
        type: Object,
    },
    routeName: {
        type: String
    },
    columns: {
        type: Array,
    },
    labelgroup: {
        type: String
    },
    includeCrudActions: {
        type: Boolean,
        default: true
    }
})

const editingRows = ref([]);
const tableData = ref(props.tableData)
const columns = ref(props.columns)
const deleteRow = ref(null)
const deleteDialog = ref(false)

onMounted(() => {
    console.log(labels);
})

const onRowEditSave = (event) => {
    let { newData, index } = event;
    let rowData = tableData.value[index];

    if(rowData == newData) return;

    axios
        .put(
            route(props.routeName + ".update", { id: newData.id }),
            newData
        )
        .then((response) => {
            addToast(`Обновление ${labels[labelgroup].case[1]}`, 
                `Данные успешно обновлены`,
                "info"
            )
            tableData.value[index] = response.data;
        })
        .catch((error) => {
            addToast(`Обновление ${labels[labelgroup].case[1]}`)
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
        .delete(
            route(props.routeName +".destroy", { id: deleteRow.value.id })
        )
        .then((response) => {
            addToast(`Удаление ${labels.case[1]}`, 
                `Данные успешно удалены`,
                "success"
            )

            tableData.value = tableData.value.filter(
                (val) => val.id !== deleteRow.value.id
            );

            deleteDialog.value = false;
            deleteRow.value = null;
        })
        .catch((error) => {
            addToast(`Удаление ${labels[props.labelgroup].case[1]}`)
        });
};

</script>

<template>
    <DataTable
        v-model:editingRows="editingRows"
        editMode="row"
        dataKey="id"
        @row-edit-save="onRowEditSave"
        tableClass="editable-cells-table"
        :value="tableData"
        class="p-datatable-small"
        showGridlines 
    >
        <Column
            v-for="(column, index) in columns"
            :key="index"
            :field="column.code"
            sortable
            :header="(labels.user_fields[column.code].title)"
            :style="column?.style"
        >
            <template #editor="{ data, field }">
                <InputText v-model="data[field]" /> 
            </template>
        </Column>

        <Column
            :rowEditor="true"
            v-if="includeCrudActions"
            style="width: 10%; min-width: 8rem"
            bodyStyle="text-align:center"
        ></Column>

        <Column :exportable="false" v-if="includeCrudActions">
            <template #body="row">
                <Button
                    icon="pi pi-trash"
                    severity="danger"
                    @click="confirmDelete(row.data)"
                ></Button>
            </template>
        </Column>
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
            <span>Вы уверены, что хотите удалить этого {{ labels[labelgroup].case[1] }}</span>
        </div>
        <template #footer>
            <Button
                label="Нет"
                icon="pi pi-times"
                severity="danger"
                text
                @click="hideDeleteDialog"
            />
            <Button
                label="Да"
                icon="pi pi-check"
                text
                @click="deleteItem"
            />
        </template>
    </Dialog>
</template>
