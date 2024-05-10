<template>
    <div class="buttons-group mb-2">
        <Button severity="danger" :disabled="selectedItems == null || selectedItems.length === 0" label="Удалить" @click="deleteSelectedItems" />
        <Button label="Добавить" @click="showAddDialog" />
    </div>

    <DataTable v-model:selection="selectedItems" :value="data" dataKey="id" editMode="row" @row-edit-save="onRowEditSave" v-model:editingRows="editingRows">
        <Column selectionMode="multiple" headerStyle="width: 3rem" :style="{
                width: '3%',
            }"></Column>
        <Column
            v-for="(field, code) in labels.reference_fields"
            :key="code"
            :field="code"
            sortable
            :header="field.title"
            :style="{
                width: '25%',
            }"
        >
            <template #editor="{ data, field }">
                <InputText v-model="data[field]" />
            </template>
        </Column>

        <Column
            v-for="addField in addColumns"
            :key="addField.code"
            :field="addField.code"
            sortable
            :header="addField.title"
            :style="{
                width: '25%',
            }"
        >
            <template #body="{ data }">
                {{ getColumnValue(data, addField) }}
            </template>
            <template #editor="{ data, field }">
                <Dropdown v-if="addField.type && addField.type == 'dropdown'" v-model="data[field]" :options="addField.options" optionLabel="name" optionValue="id" filter  />
                <InputText v-else v-model="data[field]" />
            </template>
        </Column>

        <Column
            :rowEditor="true"
            style="width: 10%; min-width: 8rem"
            bodyStyle="text-align:center"
        ></Column>

    </DataTable>
    <AddItemDialog :visible="addDialogVisible" @close="closeAddDialog" @saveItem="sendData" :addColumns="addColumns" />
</template>

<script setup>
import { ref, onMounted } from "vue";
import InputText from "primevue/inputtext";
import Button from "primevue/button";
import DataTable from "primevue/datatable";
import Column from "primevue/column";
import labels from '@/locales/ru.js';
import AddItemDialog from "@/Components/Dialogs/AddItemDialog.vue";
import Dropdown from "primevue/dropdown";

const props = defineProps({
    data: {
        type: Array
    },
    entity: String,
    addColumns: {
        type: Array,
        req: false,
        default: null
    },
});

const selectedItems = ref(null)
const data = ref(props.data)
const addDialogVisible = ref(false);
const editingRows = ref(null)

const deleteSelectedItems = () => {
    if (selectedItems.value.length === 0) {
        return;
    }

    selectedItems.value.forEach(item => {
        axios.delete(`/api/${props.entity}/${item.id}`)
            .then(response => {
                const index = data.value.findIndex(el => el.id === item.id);
                if (index !== -1) {
                    data.value.splice(index, 1);
                }
            })
            .catch(error => {
                console.error('Ошибка при удалении элемента', error);
            });
    });
};

const showAddDialog = () => {
    addDialogVisible.value = true;
};

const closeAddDialog = () => {
    addDialogVisible.value = false;
};

const sendData = (obj) => {
    axios.post(`/api/${props.entity}`, obj)
        .then(response => {
            data.value.push(response.data.data)
        })
        .catch(error => {
            console.error('Ых', error);
        });
}

const onRowEditSave = (event) => {
    let { newData, index } = event;
    let rowData = data.value[index];

    if(rowData == newData) return;

    axios.put(
            `/api/${props.entity}/${rowData.id}`,
            newData
        )
        .then((response) => {
            data.value[index] = response.data.data;
        })
        .catch((error) => {
        });
};

const getColumnValue = (data, field) => {
    if(field.type && field.type == 'dropdown') {
        let index = field.options.findIndex(el => el.id === data[field.code])
        return field.options[index].name
    } else {
        data[field.code]
    }
}
</script>