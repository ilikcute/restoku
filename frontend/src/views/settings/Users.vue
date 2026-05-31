<template>
    <AppPage
        :title="$t('sidebar.users')"
        :breadcrumb="[$t('common.settings'), $t('sidebar.users')]"
        no-card
    >
        <template #actions>
            <Button
                :label="$t('settings.add_user')"
                icon="pi pi-plus"
                class="!rounded-2xl !px-6 !bg-emerald-600 !border-none shadow-lg shadow-emerald-200/50"
                @click="openCreate"
            />
        </template>
        <AppDataTable
            framed
            :value="rows"
            :loading="loading"
            paginator
            :rows="20"
        >
            <Column :header="$t('settings.user')">
                <template #body="{ data }">
                    <div class="flex items-center gap-3">
                        <img
                            :src="data.attributes?.avatar_url"
                            class="w-10 h-10 rounded-xl object-cover shadow-sm border border-slate-100"
                        />
                        <div class="flex flex-col">
                            <span class="font-bold text-slate-800">{{
                                data.attributes?.name
                            }}</span>
                            <span class="text-xs text-slate-400">{{
                                data.attributes?.email
                            }}</span>
                        </div>
                    </div>
                </template>
            </Column>

            <Column field="attributes.role" :header="$t('settings.role')">
                <template #body="{ data }">{{
                    data.attributes?.role
                }}</template>
            </Column>
            <Column field="attributes.is_active" :header="$t('common.status')">
                <template #body="{ data }">
                    <Tag
                        :value="
                            data.attributes?.is_active
                                ? $t('common.active')
                                : $t('common.inactive')
                        "
                        :severity="
                            data.attributes?.is_active ? 'success' : 'danger'
                        "
                    />
                </template>
            </Column>
            <Column :header="$t('common.actions')">
                <template #body="{ data }">
                    <Button
                        icon="pi pi-pencil"
                        text
                        rounded
                        class="text-blue-600"
                        @click="openEdit(data)"
                    />
                    <Button
                        icon="pi pi-trash"
                        text
                        rounded
                        severity="danger"
                        class="text-red-600"
                        @click="remove(data)"
                    />
                </template>
            </Column>
        </AppDataTable>

        <Dialog
            v-model:visible="dialogOpen"
            :header="dialogTitle"
            modal
            :style="{ width: '34rem' }"
        >
            <div class="space-y-4">
                <!-- Avatar Section in Dialog -->
                <div class="flex flex-col items-center gap-3 py-2">
                    <div class="relative group">
                        <img
                            :src="
                                form.avatar_url ||
                                'https://ui-avatars.com/api/?name=User&background=f1f5f9&color=94a3b8'
                            "
                            class="w-24 h-24 rounded-2xl object-cover border-2 border-slate-100 shadow-sm"
                        />
                        <div
                            class="absolute inset-0 flex items-center justify-center bg-black/40 rounded-2xl opacity-0 group-hover:opacity-100 transition-opacity cursor-pointer"
                        >
                            <i class="pi pi-camera text-white text-xl"></i>
                            <input
                                type="file"
                                class="absolute inset-0 opacity-0 cursor-pointer"
                                @change="onAvatarSelect"
                                accept="image/*"
                            />
                        </div>
                    </div>
                    <p class="text-[10px] text-slate-400">
                        {{ $t("settings.click_to_change") }}
                    </p>
                </div>

                <div class="space-y-1">
                    <label
                        class="text-xs font-bold text-slate-400 uppercase tracking-wider ml-1"
                        >{{ $t("common.name") }}</label
                    >
                    <InputText
                        v-model="form.name"
                        :placeholder="$t('common.name')"
                        class="w-full"
                    />
                </div>
                <div class="space-y-1">
                    <label
                        class="text-xs font-bold text-slate-400 uppercase tracking-wider ml-1"
                        >{{ $t("auth.email") }}</label
                    >
                    <InputText
                        v-model="form.email"
                        :placeholder="$t('auth.email')"
                        class="w-full"
                    />
                </div>
                <Select
                    v-model="form.role"
                    :options="availableRoles"
                    :placeholder="$t('settings.role')"
                    class="w-full"
                />
                <MultiSelect
                    v-model="form.permissions"
                    :options="availablePermissions"
                    :placeholder="$t('settings.permissions')"
                    display="chip"
                    class="w-full"
                />
                <Password
                    v-model="form.password"
                    :placeholder="$t('auth.password')"
                    toggleMask
                    class="w-full"
                    inputClass="w-full"
                />
                <Password
                    v-model="form.password_confirmation"
                    :feedback="false"
                    :placeholder="$t('settings.confirm_password')"
                    toggleMask
                    class="w-full"
                    inputClass="w-full"
                />
            </div>
            <template #footer>
                <Button
                    :label="$t('common.cancel')"
                    text
                    @click="dialogOpen = false"
                />
                <Button
                    :label="$t('common.save')"
                    :loading="saving"
                    @click="save"
                />
            </template>
        </Dialog>
    </AppPage>
</template>

<script setup>
import { reactive, ref } from "vue";
import AppDataTable from "@/components/AppDataTable.vue";
import StatusBadge from "@/components/StatusBadge.vue";
import { useI18n } from "vue-i18n";
import { useToast } from "primevue/usetoast";
import { useConfirm } from "primevue/useconfirm";
import { userApi } from "@/api/settings";
import Button from "primevue/button";
import AppPage from "@/components/layout/AppPage.vue";
import Column from "primevue/column";
import Dialog from "primevue/dialog";
import InputText from "primevue/inputtext";
import Select from "primevue/select";
import MultiSelect from "primevue/multiselect";
import Password from "primevue/password";
import Tag from "primevue/tag";

const { t: $t } = useI18n();
const toast = useToast();
const confirm = useConfirm();
const rows = ref([]);
const loading = ref(false);
const saving = ref(false);
const dialogOpen = ref(false);
const dialogTitle = ref("");
const editId = ref(null);
const availableRoles = ref([]);
const availablePermissions = ref([]);
const form = reactive({
    name: "",
    email: "",
    role: "cashier",
    password: "",
    password_confirmation: "",
    permissions: [],
    avatar: null,
    avatar_url: "",
});

function resetForm() {
    editId.value = null;
    form.name = "";
    form.email = "";
    form.role = "cashier";
    form.permissions = [];
    form.password = "";
    form.password_confirmation = "";
    form.avatar = null;
    form.avatar_url = "";
}

function onAvatarSelect(event) {
    const file = event.target.files[0];
    if (file) {
        form.avatar = file;
        const reader = new FileReader();
        reader.onload = (e) => {
            form.avatar_url = e.target.result;
        };
        reader.readAsDataURL(file);
    }
}

function openCreate() {
    resetForm();
    dialogTitle.value = $t("settings.add_user");
    dialogOpen.value = true;
}

function openEdit(item) {
    resetForm();
    editId.value = item.id;
    dialogTitle.value = $t("settings.edit_user");
    form.name = item.attributes?.name || "";
    form.email = item.attributes?.email || "";
    form.role = item.attributes?.role || "cashier";
    form.permissions = item.attributes?.permissions || [];
    form.avatar_url = item.attributes?.avatar_url || "";
    dialogOpen.value = true;
}

async function load() {
    loading.value = true;
    try {
        const response = await userApi.getAll();
        rows.value = response?.data?.data || [];
    } finally {
        loading.value = false;
    }
}

async function save() {
    saving.value = true;
    try {
        const formData = new FormData();
        Object.keys(form).forEach((key) => {
            if (key === "avatar_url") return;
            if (key === "avatar" && !form[key]) return;
            if (key === "permissions") {
                form[key].forEach((p) => formData.append("permissions[]", p));
            } else if (form[key] !== null && form[key] !== undefined) {
                if (
                    editId.value &&
                    (key === "password" || key === "password_confirmation") &&
                    !form[key]
                ) {
                    // skip empty password on edit
                } else {
                    formData.append(key, form[key]);
                }
            }
        });

        if (!editId.value) {
            await userApi.create(formData);
        } else {
            await userApi.update(editId.value, formData);
        }
        dialogOpen.value = false;
        toast.add({
            severity: "success",
            summary: $t("common.save"),
            life: 2000,
        });
        load();
    } catch (error) {
        toast.add({
            severity: "error",
            summary: $t("common.save"),
            detail: error?.response?.data?.message || "Validation failed",
            life: 3000,
        });
    } finally {
        saving.value = false;
    }
}

function remove(item) {
    confirm.require({
        header: $t("common.confirm"),
        message: `${$t("common.delete")} ${item.attributes?.name}?`,
        acceptClass: "p-button-danger",
        accept: async () => {
            await userApi.delete(item.id);
            toast.add({
                severity: "success",
                summary: $t("common.delete"),
                life: 2000,
            });
            load();
        },
    });
}

async function loadOptions() {
    try {
        const [rolesRes, permRes] = await Promise.all([
            userApi.getRoles(),
            userApi.getPermissions(),
        ]);
        availableRoles.value = rolesRes.data?.data || [];
        availablePermissions.value = permRes.data?.data || [];
    } catch (error) {
        console.error("Failed to load roles and permissions", error);
    }
}

load();
loadOptions();
</script>
