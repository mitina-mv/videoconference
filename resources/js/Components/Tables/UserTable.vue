<script setup>
import { ref } from "vue";
import DataTable from "primevue/datatable";
import Column from "primevue/column";
import Button from "primevue/button";

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
    labels: {
        type: Object,
        req: false
    }
})

const editingRows = ref([]);
const tableData = ref(props.tableData)
const columns = ref(props.columns)

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
            // toast.add({
            //     severity: "info",
            //     summary: `Обновление ${props.elementName[1]}`,
            //     detail: `${props.elementName[0]} "${response.data.name}", данные обновлены.`,
            //     life: 3000,
            //     position: "bottom-right"
            // });
            tableData.value[index] = response.data;
        })
        .catch((error) => {
            // toast.add({
            //     severity: "error",
            //     summary: `Обновление ${props.elementName[1]}`,
            //     detail: `Ошибка при отправке запроса, попробуйте позже.`,
            //     life: 3000,
            //     position: "bottom-right"
            // });
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
            :header="column.label"
            :style="column?.style"
        >
            <template #editor="{ data, field }">
                <InputText v-model="data[field]" /> </template
        ></Column>
    </DataTable>
</template>
