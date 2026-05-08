<template>
    <AppPage
        :title="title"
        :subtitle="`${$t('common.manage')} ${title} ${$t('common.efficiently')}`"
        :breadcrumb="[$t('common.master_data'), title]"
    >
        <template #actions>
            <Button
                :label="createLabel"
                icon="pi pi-plus"
                class="!rounded-2xl !px-6 !bg-emerald-600 !border-none shadow-lg shadow-emerald-200/50"
                @click="openCreate"
            />
        </template>

        <div class="space-y-4">
            <DataTable
                :value="rows"
                paginator
                :rows="10"
                :loading="loading"
                v-model:first="first"
                class="p-datatable-modern"
            >
                <Column header="No" class="w-16 text-center">
                    <template #body="slotProps">
                        <span class="text-slate-400 font-mono text-xs">{{
                            slotProps.index + first + 1
                        }}</span>
                    </template>
                </Column>
                <Column
                    v-for="column in columns"
                    :key="column.field"
                    :field="column.field"
                    :header="column.header"
                >
                    <template #body="{ data, field }">
                        <span
                            v-if="field === 'name'"
                            class="font-bold text-slate-700"
                            >{{ data[field] }}</span
                        >
                        <div v-else-if="field === 'is_pkp'">
                            <Tag
                                v-if="data[field]"
                                value="PKP"
                                severity="info"
                                class="!text-[10px] !bg-sky-50 !text-sky-600 !border-sky-100"
                            />
                            <Tag
                                v-else
                                value="Non-PKP"
                                severity="secondary"
                                class="!text-[10px] !bg-slate-50 !text-slate-400 !border-slate-100"
                            />
                        </div>
                        <span v-else class="text-slate-600">{{
                            data[field]
                        }}</span>
                    </template>
                </Column>
                <Column :header="$t('common.status')" class="w-32">
                    <template #body="{ data }">
                        <div
                            :class="[
                                'px-3 py-1 rounded-full text-[10px] font-black uppercase tracking-widest inline-flex items-center gap-1.5',
                                data.is_active
                                    ? 'bg-emerald-50 text-emerald-600 border border-emerald-100'
                                    : 'bg-rose-50 text-rose-600 border border-rose-100',
                            ]"
                        >
                            <span
                                :class="[
                                    'w-1.5 h-1.5 rounded-full',
                                    data.is_active
                                        ? 'bg-emerald-500'
                                        : 'bg-rose-500',
                                ]"
                            ></span>
                            {{
                                data.is_active
                                    ? $t("common.active")
                                    : $t("common.inactive")
                            }}
                        </div>
                    </template>
                </Column>
                <Column :header="$t('common.actions')" style="width: 120px">
                    <template #body="{ data }">
                        <div class="flex gap-1">
                            <Button
                                icon="pi pi-pencil"
                                text
                                rounded
                                severity="success"
                                class="hover:bg-emerald-50"
                                @click="openEdit(data)"
                            />
                            <Button
                                icon="pi pi-trash"
                                text
                                rounded
                                severity="danger"
                                class="hover:bg-rose-50"
                                @click="remove(data)"
                            />
                        </div>
                    </template>
                </Column>
            </DataTable>

            <Dialog
                v-model:visible="dialogOpen"
                modal
                :header="dialogTitle"
                :style="{ width: '30rem' }"
                pt:root:class="!rounded-3xl shadow-2xl"
                pt:header:class="!bg-slate-50 !p-6 !border-b !border-slate-100"
                pt:content:class="!p-6"
            >
                <div class="space-y-5">
                    <div
                        v-for="field in formFields"
                        :key="field.key"
                        class="space-y-1.5"
                    >
                        <label
                            class="text-[10px] font-black text-slate-400 uppercase tracking-widest ml-1"
                            >{{ field.label }}</label
                        >
                        <InputText
                            v-if="field.type === 'number'"
                            v-model.number="form[field.key]"
                            type="number"
                            class="w-full !rounded-xl !bg-slate-50 !p-3 focus:!ring-emerald-500"
                            :class="
                                errors[field.key]
                                    ? 'p-invalid border-red-500'
                                    : '!border-slate-100'
                            "
                        />
                        <Textarea
                            v-else-if="field.type === 'textarea'"
                            v-model="form[field.key]"
                            rows="3"
                            class="w-full !rounded-xl !bg-slate-50 !p-3 focus:!ring-emerald-500"
                            :class="
                                errors[field.key]
                                    ? 'p-invalid border-red-500'
                                    : '!border-slate-100'
                            "
                        />
                        <div
                            v-else-if="field.type === 'checkbox'"
                            class="flex items-center gap-3 p-3 bg-slate-50 rounded-xl border border-slate-100 transition-colors"
                            :class="{
                                'bg-emerald-50 border-emerald-100':
                                    form[field.key],
                            }"
                        >
                            <Checkbox
                                v-model="form[field.key]"
                                binary
                                :inputId="field.key"
                            />
                            <label
                                :for="field.key"
                                class="text-sm font-bold cursor-pointer"
                                :class="
                                    form[field.key]
                                        ? 'text-emerald-700'
                                        : 'text-slate-500'
                                "
                                >{{ field.checkboxLabel || field.label }}</label
                            >
                        </div>
                        <InputText
                            v-else
                            v-model="form[field.key]"
                            class="w-full !rounded-xl !bg-slate-50 !p-3 focus:!ring-emerald-500"
                            :class="
                                errors[field.key]
                                    ? 'p-invalid border-red-500'
                                    : '!border-slate-100'
                            "
                            :placeholder="field.placeholder || ''"
                        />
                        <small
                            v-if="errors[field.key]"
                            class="text-red-500 font-bold ml-1 text-[10px]"
                            >{{ errors[field.key][0] }}</small
                        >
                    </div>
                    <div
                        class="flex items-center gap-3 p-3 bg-slate-50 rounded-xl border border-slate-100 transition-colors"
                        :class="{
                            'bg-emerald-50 border-emerald-100': form.is_active,
                        }"
                    >
                        <Checkbox
                            v-model="form.is_active"
                            binary
                            inputId="activeField"
                        />
                        <label
                            for="activeField"
                            class="text-sm font-bold cursor-pointer"
                            :class="
                                form.is_active
                                    ? 'text-emerald-700'
                                    : 'text-slate-500'
                            "
                            >{{ $t("common.active") }}</label
                        >
                    </div>
                </div>
                <template #footer>
                    <div class="flex gap-3 p-2 pt-4">
                        <Button
                            :label="$t('common.cancel')"
                            text
                            class="!rounded-xl !text-slate-400 hover:!bg-slate-100"
                            @click="dialogOpen = false"
                        />
                        <Button
                            :label="$t('common.save')"
                            icon="pi pi-check"
                            :loading="saving"
                            class="!rounded-xl !bg-emerald-600 !border-none !px-8 h-12 font-bold shadow-lg shadow-emerald-100"
                            @click="save"
                        />
                    </div>
                </template>
            </Dialog>
        </div>
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
            await props.deleter(row.id);
            toast.add({
                severity: "success",
                summary: $t("common.delete"),
                life: 2000,
            });
            load();
        },
    });
}

load();
</script>
