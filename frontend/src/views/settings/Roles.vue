<template>
  <AppPage :title="$t('settings.roles')" :breadcrumb="[$t('common.settings'), $t('settings.roles')]">
    <template #actions>
      <Button :label="$t('settings.add_role')" icon="pi pi-plus" @click="openCreate" />
    </template>
    <AppDataTable framed :value="rows" :loading="loading" paginator :rows="10">
      <Column field="name" :header="$t('common.name')">
        <template #body="{ data }">
          <span class="font-bold text-slate-800">{{ data.name }}</span>
        </template>
      </Column>
      <Column :header="$t('settings.permissions')">
        <template #body="{ data }">
          <div class="flex flex-wrap gap-1 max-w-md">
            <Tag v-for="perm in data.permissions" :key="perm" :value="perm" severity="secondary" class="text-[10px]" />
            <span v-if="!data.permissions.length" class="text-xs text-slate-400 italic">No permissions</span>
          </div>
        </template>
      </Column>
      <Column :header="$t('common.actions')">
        <template #body="{ data }">
          <Button icon="pi pi-pencil" text rounded class="text-blue-600" @click="openEdit(data)" />
          <Button icon="pi pi-trash" text rounded severity="danger" class="text-red-600" @click="remove(data)" 
            :disabled="['admin', 'manager', 'cashier'].includes(data.name)" />
        </template>
      </Column>
    </AppDataTable>

    <Dialog v-model:visible="dialogOpen" :header="dialogTitle" modal :style="{ width: '34rem' }">
      <div class="space-y-4">
        <div class="space-y-1">
          <label class="text-xs font-bold text-slate-400 uppercase tracking-wider ml-1">{{ $t('common.name') }}</label>
          <InputText v-model="form.name" :placeholder="$t('common.name')" class="w-full" :disabled="editId !== null" />
        </div>
        <div class="space-y-1">
          <label class="text-xs font-bold text-slate-400 uppercase tracking-wider ml-1">{{ $t('settings.permissions') }}</label>
          <MultiSelect v-model="form.permissions" :options="availablePermissions" :placeholder="$t('settings.permissions')" 
            display="chip" class="w-full" filter />
        </div>
      </div>
      <template #footer>
        <Button :label="$t('common.cancel')" text @click="dialogOpen = false" />
        <Button :label="$t('common.save')" :loading="saving" @click="save" />
      </template>
    </Dialog>
  </AppPage>
</template>

<script setup>
import { reactive, ref } from 'vue';
import AppDataTable from '@/components/AppDataTable.vue';
import StatusBadge from '@/components/StatusBadge.vue';
import { useI18n } from 'vue-i18n';
import { useToast } from 'primevue/usetoast';
import { useConfirm } from 'primevue/useconfirm';
import { roleApi, userApi } from '@/api/settings';
import Button from 'primevue/button';
import AppPage from '@/components/layout/AppPage.vue';
import Column from 'primevue/column';
import Dialog from 'primevue/dialog';
import InputText from 'primevue/inputtext';
import MultiSelect from 'primevue/multiselect';
import Tag from 'primevue/tag';

const { t: $t } = useI18n();
const toast = useToast();
const confirm = useConfirm();
const rows = ref([]);
const loading = ref(false);
const saving = ref(false);
const dialogOpen = ref(false);
const dialogTitle = ref('');
const editId = ref(null);
const availablePermissions = ref([]);
const form = reactive({ 
  name: '', 
  permissions: []
});

function resetForm() {
  editId.value = null;
  form.name = '';
  form.permissions = [];
}

function openCreate() {
  resetForm();
  dialogTitle.value = $t('settings.add_role');
  dialogOpen.value = true;
}

function openEdit(item) {
  resetForm();
  editId.value = item.id;
  dialogTitle.value = $t('settings.edit_role');
  form.name = item.name;
  form.permissions = [...item.permissions];
  dialogOpen.value = true;
}

async function load() {
  loading.value = true;
  try {
    const response = await roleApi.getAll();
    rows.value = response?.data?.data || [];
  } finally {
    loading.value = false;
  }
}

async function save() {
  saving.value = true;
  try {
    if (!editId.value) {
      await roleApi.create(form);
    } else {
      await roleApi.update(editId.value, form);
    }
    dialogOpen.value = false;
    toast.add({ severity: 'success', summary: $t('common.save'), life: 2000 });
    load();
  } catch (error) {
    toast.add({ severity: 'error', summary: $t('common.save'), detail: error?.response?.data?.message || 'Action failed', life: 3000 });
  } finally {
    saving.value = false;
  }
}

function remove(item) {
  confirm.require({
    header: $t('common.confirm'),
    message: `${$t('common.delete')} ${item.name}?`,
    acceptClass: 'p-button-danger',
    accept: async () => {
      try {
        await roleApi.delete(item.id);
        toast.add({ severity: 'success', summary: $t('common.delete'), life: 2000 });
        load();
      } catch (error) {
        toast.add({ severity: 'error', summary: $t('common.delete'), detail: error?.response?.data?.message || 'Delete failed', life: 3000 });
      }
    }
  });
}

async function loadOptions() {
  try {
    const permRes = await userApi.getPermissions();
    availablePermissions.value = permRes.data?.data || [];
  } catch (error) {
    console.error('Failed to load permissions', error);
  }
}

load();
loadOptions();
</script>
