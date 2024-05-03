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
import Toolbar from 'primevue/toolbar';

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
const emit = defineEmits(['fetchData'])

const editingRows = ref([]);
const tableData = ref(props.tableData)
const columns = ref(props.columns)
const deleteRow = ref(null)
const deleteDialog = ref(false)

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

const fetchData = (id) => {
    emit('fetchData')
}
</script>

<template>
    <Toolbar class="mb-4">
        <template #start>
            <h4 class="m-0">{{ labels[labelgroup].title }}</h4>
        </template>
        <template #end>
            <a :href="route('admin.new',labelgroup)">
                <Button :label="'Добавить ' + labels[labelgroup].case[1]" icon="pi pi-plus" severity="success" class="mr-2" />
            </a>
            <Button label="Обновить" icon="pi pi-reload" severity="secondary" @click="fetchData"/>
        </template>
    </Toolbar>

    <DataTable
        v-model:editingRows="editingRows"
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
            :header="labels.user_fields[column.code].title"
            :style="column?.style"
        ></Column>

        <Column :exportable="false" v-if="includeCrudActions">
            <template #body="row">
                <a :href="route('admin.edit', row.data.id)">
                    <Button
                        icon="pi pi-pencil"
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
