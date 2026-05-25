<template>
    <AppPage :title="title" :breadcrumb="[$t('common.master_data'), title]">
        <template #actions>
            <Button :label="createLabel" icon="pi pi-plus" class="!rounded-3xl !px-7 !py-3 !bg-emerald-600 !border-none shadow-xl shadow-emerald-500/20 hover:shadow-emerald-600/30 transition-all duration-200 hover:-translate-y-0.5" @click="openCreate" />
        </template>

        <!-- Modern Card Container -->
        <div class="modern-card">
            <DataTable :value="rows" paginator :rows="10" :loading="loading" v-model:first="first" class="modern-table"
                :pt="{
                    wrapper: { class: 'border-none' },
                    header: { class: 'border-b border-slate-100' }
                }">
                <!-- Kolom No -->
                <Column header="No" class="w-12 text-center">
                    <template #body="slotProps">
                        <span class="font-mono text-xs text-slate-400">
                            {{ slotProps.index + first + 1 }}
                        </span>
                    </template>
                </Column>

                <!-- Dynamic Columns -->
                <Column v-for="column in columns" :key="column.field" :field="column.field" :header="column.header">
                    <template #body="{ data, field }">
                        <span v-if="field === 'name'" class="font-semibold text-slate-800">
                            {{ data[field] }}
                        </span>
                        <div v-else-if="field === 'is_pkp'">
                            <Tag v-if="data[field]" value="PKP" severity="info" class="modern-tag" />
                            <Tag v-else value="Non-PKP" severity="secondary" class="modern-tag" />
                        </div>
                        <span v-else class="text-slate-600">{{ data[field] }}</span>
                    </template>
                </Column>

                <!-- Status -->
                <Column :header="$t('common.status')" class="w-28">
                    <template #body="{ data }">
                        <div :class="[
                            'inline-flex items-center gap-2 px-3 py-1.5 rounded-2xl text-xs font-bold uppercase tracking-widest border',
                            data.is_active ? 'bg-emerald-50 text-emerald-500 border-emerald-200' : 'bg-red-50 text-red-500 border-red-200'
                        ]">
                            <span :class="['inline-block w-[7px] h-[7px] rounded-full', data.is_active ? 'bg-emerald-500' : 'bg-red-500']"></span>
                            {{ data.is_active ? $t("common.active") : $t("common.inactive") }}
                        </div>
                    </template>
                </Column>

                <!-- Actions -->
                <Column :header="$t('common.actions')" class="w-28">
                    <template #body="{ data }">
                        <div class="flex items-center gap-2">
                            <Button icon="pi pi-pencil" outlined class="!w-9 !h-9 !rounded-2xl transition-all !text-slate-500 !border-slate-200 hover:!bg-slate-50 hover:!border-slate-300"
                                @click="openEdit(data)" />
                            <Button icon="pi pi-trash" outlined class="!w-9 !h-9 !rounded-2xl transition-all !text-red-500 !border-slate-200 hover:!bg-red-50 hover:!border-red-200"
                                @click="remove(data)" />
                        </div>
                    </template>
                </Column>
            </DataTable>
        </div>

        <!-- Modern Dialog -->
        <Dialog v-model:visible="dialogOpen" modal :header="dialogTitle" :style="{ width: '380px' }"
            pt:root:class="!rounded-[24px] shadow-[0_25px_50px_-12px_rgb(0,0,0,0.25)]" pt:header:class="!bg-slate-50 !border-b !border-slate-200 !px-7 !py-6 !rounded-t-[24px]"
            pt:content:class="!p-7">

            <div class="space-y-6">
                <div v-for="field in formFields" :key="field.key" class="space-y-2">
                    <label class="text-xs font-bold text-slate-500 uppercase tracking-widest ml-1 mb-1 block">
                        {{ field.label }}
                    </label>

                    <!-- Input variants -->
                    <InputText v-if="field.type === 'number'" v-model.number="form[field.key]" type="number"
                        class="w-full rounded-2xl bg-slate-50 border border-slate-200 focus:border-emerald-500 focus:ring-4 focus:ring-emerald-100 transition-all px-5 py-3.5 text-slate-700" :class="{ 'p-invalid': errors[field.key] }" />

                    <Textarea v-else-if="field.type === 'textarea'" v-model="form[field.key]" rows="4"
                        class="w-full rounded-2xl bg-slate-50 border border-slate-200 focus:border-emerald-500 focus:ring-4 focus:ring-emerald-100 transition-all px-5 py-3.5 text-slate-700" :class="{ 'p-invalid': errors[field.key] }" />

                    <div v-else-if="field.type === 'checkbox'" class="flex items-center gap-3 bg-slate-50 border border-slate-100 hover:border-slate-200 px-5 py-4 rounded-2xl transition-all">
                        <Checkbox v-model="form[field.key]" binary :inputId="field.key" />
                        <label :for="field.key" class="cursor-pointer">{{ field.checkboxLabel || field.label }}</label>
                    </div>

                    <InputText v-else v-model="form[field.key]" class="w-full rounded-2xl bg-slate-50 border border-slate-200 focus:border-emerald-500 focus:ring-4 focus:ring-emerald-100 transition-all px-5 py-3.5 text-slate-700"
                        :class="{ 'p-invalid': errors[field.key] }" />

                    <small v-if="errors[field.key]" class="text-red-500 text-xs font-medium ml-1 mt-1 block">
                        {{ errors[field.key][0] }}
                    </small>
                </div>

                <!-- Active Toggle -->
                <div class="flex items-center gap-3 bg-slate-50 border border-slate-100 hover:border-slate-200 px-5 py-4 rounded-2xl transition-all">
                    <Checkbox v-model="form.is_active" binary inputId="activeField" />
                    <label for="activeField" class="cursor-pointer font-medium">
                        {{ $t("common.active") }}
                    </label>
                </div>
            </div>

            <template #footer>
                <div class="flex gap-3">
                    <Button :label="$t('common.cancel')" text class="!rounded-3xl !text-slate-500 hover:!bg-slate-100" @click="dialogOpen = false" />
                    <Button :label="$t('common.save')" icon="pi pi-check" :loading="saving" class="!rounded-3xl !h-12 !px-8 !bg-emerald-600 !border-none shadow-lg shadow-emerald-500/25 font-semibold"
                        @click="save" />
                </div>
            </template>
        </Dialog>
    </AppPage>
</template>

<script setup>
import { reactive, ref } from "vue";
import { useI18n } from "vue-i18n";
import { useToast } from "primevue/usetoast";
import { useConfirm } from "primevue/useconfirm";
import Button from "primevue/button";
import DataTable from "primevue/datatable";
import Column from "primevue/column";
import Dialog from "primevue/dialog";
import InputText from "primevue/inputtext";
import Textarea from "primevue/textarea";
import Checkbox from "primevue/checkbox";
import Tag from "primevue/tag";
import AppPage from "@/components/layout/AppPage.vue";

const props = defineProps({
    title: { type: String, required: true },
    createLabel: { type: String, default: "Add" },
    columns: { type: Array, required: true },
    formFields: { type: Array, required: true },
    loader: { type: Function, required: true },
    creator: { type: Function, required: true },
    updater: { type: Function, required: true },
    deleter: { type: Function, required: true },
});

const { t: $t } = useI18n();
const toast = useToast();
const confirm = useConfirm();
const rows = ref([]);
const loading = ref(false);
const saving = ref(false);
const dialogOpen = ref(false);
const dialogTitle = ref("");
const currentId = ref(null);
const errors = ref({});
const first = ref(0);
const form = reactive({ is_active: true });

function resetForm() {
    currentId.value = null;
    form.is_active = true;
    errors.value = {};
    props.formFields.forEach((field) => {
        if (field.key !== "is_active") {
            form[field.key] = field.defaultValue ?? "";
        }
    });
}

function openCreate() {
    resetForm();
    dialogTitle.value = `${$t("common.add")} ${props.title}`;
    dialogOpen.value = true;
}

function openEdit(row) {
    resetForm();
    currentId.value = row.id;
    dialogTitle.value = `${$t("common.edit")} ${props.title}`;
    form.is_active = Boolean(row.is_active);
    props.formFields.forEach((field) => {
        form[field.key] = row[field.key] ?? "";
    });
    dialogOpen.value = true;
}

async function load() {
    loading.value = true;
    try {
        const response = await props.loader();
        rows.value = response?.data?.data || [];
    } finally {
        loading.value = false;
    }
}

async function save() {
    saving.value = true;
    errors.value = {};
    try {
        const payload = { ...form };
        if (!currentId.value) {
            await props.creator(payload);
        } else {
            await props.updater(currentId.value, payload);
        }
        toast.add({
            severity: "success",
            summary: $t("common.save"),
            life: 2000,
        });
        dialogOpen.value = false;
        load();
    } catch (error) {
        if (error.response?.status === 422) {
            errors.value = error.response.data.errors || {};
            toast.add({
                severity: "error",
                summary: $t("common.confirm"),
                detail: "Please fix the errors below",
                life: 3000,
            });
        } else {
            toast.add({
                severity: "error",
                summary: $t("common.save"),
                detail:
                    error?.response?.data?.message || "Please check your input",
                life: 3000,
            });
        }
    } finally {
        saving.value = false;
    }
}

function remove(row) {
    confirm.require({
        message: `${$t("common.delete")} "${row.name}"?`,
        header: $t("common.confirm"),
        acceptClass: "p-button-danger",
        accept: async () => {
            try {
                await props.deleter(row.id);
                toast.add({
                    severity: "success",
                    summary: $t("common.delete"),
                    detail: $t("common.delete_success", "Data berhasil dihapus"),
                    life: 2000,
                });
                load();
            } catch (error) {
                console.error("Delete Error:", error);
                toast.add({
                    severity: "error",
                    summary: $t("common.delete"),
                    detail: error.response?.data?.message || "Gagal menghapus data",
                    life: 5000,
                });
            }
        },
    });
}

load();
</script>


<style scoped>
/* === Modern Card Container === */
.modern-card {
    background: white;
    border-radius: 20px;
    box-shadow: 0 10px 15px -3px rgb(0 0 0 / 0.05),
        0 4px 6px -4px rgb(0 0 0 / 0.05);
    border: 1px solid #f1f5f9;
    overflow: hidden;
    padding: 8px;
}

/* === Modern Table === */
.modern-table :deep(.p-datatable-thead > tr > th) {
    background: #f8fafc;
    color: #64748b;
    font-size: 0.75rem;
    font-weight: 600;
    text-transform: uppercase;
    letter-spacing: 0.04em;
    padding: 1.25rem 0.75rem;
    border-bottom: 1px solid #e2e8f0;
}

.modern-table :deep(.p-datatable-tbody > tr > td) {
    padding: 1.1rem 0.75rem;
    border-bottom: 1px solid #f1f5f9;
    color: #1e2937;
    font-size: 0.925rem;
}

.modern-table :deep(.p-datatable-tbody > tr:hover) {
    background-color: #f8fafc;
}

</style>