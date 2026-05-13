<template>
  <AppPage 
    title="Rekening Kas" 
    subtitle="Kelola akun bank, kas kecil, dan saldo keuangan operasional Restoku."
    accent="emerald"
    :breadcrumb="['Manajemen', 'Rekening']"
  >
    <template #actions>
      <Button label="Tambah Rekening" icon="pi pi-plus" class="!rounded-2xl !px-6 !bg-emerald-600 !border-none shadow-lg shadow-emerald-200/50" @click="openDialog" />
    </template>

    <DataTable :value="rows" :loading="loading" paginator :rows="20" class="p-datatable-modern" responsiveLayout="scroll">
      <Column field="name" :header="$t('common.name')" style="width: 250px">
        <template #body="{ data }">
          <div class="flex items-center gap-3">
            <div class="w-10 h-10 rounded-xl bg-slate-50 text-slate-500 flex items-center justify-center shadow-sm">
              <i class="pi pi-wallet text-lg"></i>
            </div>
            <span class="font-bold text-slate-700">{{ data.name }}</span>
          </div>
        </template>
      </Column>
      <Column field="account_number" :header="$t('finance.account_number')">
        <template #body="{ data }">
          <span class="font-mono text-slate-500">{{ data.account_number || '-' }}</span>
        </template>
      </Column>
      <Column field="balance" :header="$t('finance.balance')" style="width: 200px">
        <template #body="{ data }">
          <span class="text-lg font-black text-slate-800 tracking-tight">
            <span class="text-xs font-bold mr-1 text-slate-400">Rp</span>
            {{ money(data.balance) }}
          </span>
        </template>
      </Column>
      <Column field="is_active" :header="$t('common.status')" style="width: 120px">
        <template #body="{ data }">
          <div :class="[
            'px-3 py-1 rounded-full text-[10px] font-black uppercase tracking-widest inline-flex items-center gap-1.5',
            data.is_active ? 'bg-emerald-50 text-emerald-600 border border-emerald-100' : 'bg-rose-50 text-rose-600 border border-rose-100'
          ]">
            <span :class="['w-1.5 h-1.5 rounded-full', data.is_active ? 'bg-emerald-500' : 'bg-rose-500']"></span>
            {{ data.is_active ? 'Aktif' : 'Nonaktif' }}
          </div>
        </template>
      </Column>
      <Column :exportable="false" style="width: 120px" header="Aksi">
        <template #body="slotProps">
          <div class="flex gap-2">
            <Button icon="pi pi-pencil" text rounded severity="success" class="hover:bg-emerald-50" @click="editItem(slotProps.data)" v-tooltip.top="'Edit'" />
            <Button icon="pi pi-trash" text rounded severity="danger" class="hover:bg-rose-50" @click="confirmDelete(slotProps.data)" v-tooltip.top="'Hapus'" />
          </div>
        </template>
      </Column>
    </DataTable>

    <Dialog v-model:visible="dialogOpen" :style="{width: '450px'}" :header="form.id ? 'Edit Rekening' : 'Tambah Rekening Baru'" :modal="true" class="p-fluid !rounded-3xl overflow-hidden shadow-2xl" pt:root:class="!rounded-3xl" pt:header:class="!bg-slate-50 !p-6 !border-b !border-slate-100" pt:content:class="!p-6">
      <div class="space-y-6">
        <div class="flex items-start gap-4 p-4 bg-emerald-50 rounded-2xl border border-emerald-100">
          <div class="w-10 h-10 rounded-xl bg-emerald-500 text-white flex items-center justify-center shrink-0">
            <i class="pi pi-wallet text-xl"></i>
          </div>
          <div>
            <p class="text-sm font-bold text-emerald-900">Informasi Rekening</p>
            <p class="text-[11px] text-emerald-700/70 leading-relaxed font-medium">Rekening ini akan digunakan sebagai sumber dana atau penampung pendapatan dalam transaksi keuangan.</p>
          </div>
        </div>

        <div class="space-y-4">
          <div class="flex flex-col gap-2">
            <label for="name" class="text-xs font-bold text-slate-400 uppercase tracking-widest ml-1">Nama Rekening / Kas</label>
            <InputText id="name" v-model.trim="form.name" required="true" autofocus placeholder="Contoh: Kas Utama, Bank BCA, dll" class="!rounded-xl !bg-slate-50 !border-slate-100 focus:!ring-emerald-500 !p-3" />
          </div>

          <div class="flex flex-col gap-2">
            <label for="account_number" class="text-xs font-bold text-slate-400 uppercase tracking-widest ml-1">Nomor Rekening (Opsional)</label>
            <InputText id="account_number" v-model="form.account_number" placeholder="Nomor rekening atau identitas lainnya" class="!rounded-xl !bg-slate-50 !border-slate-100 focus:!ring-emerald-500 !p-3" />
          </div>

          <div class="flex flex-col gap-2">
            <label for="balance" class="text-xs font-bold text-slate-400 uppercase tracking-widest ml-1">Saldo</label>
            <div class="relative">
              <span class="absolute left-4 top-1/2 -translate-y-1/2 text-slate-400 font-bold text-sm">Rp</span>
              <InputNumber id="balance" v-model="form.balance" mode="decimal" :minFractionDigits="0" placeholder="0" class="!rounded-xl overflow-hidden !border-slate-100" pt:input:class="!bg-slate-50 !pl-10 !p-3 !font-bold" />
            </div>
          </div>

          <div class="flex items-center gap-3 p-3 bg-slate-50 rounded-xl border border-slate-100 transition-colors" :class="{'bg-emerald-50 border-emerald-100': form.is_active}">
            <Checkbox id="is_active" v-model="form.is_active" :binary="true" />
            <label for="is_active" class="text-sm font-bold cursor-pointer" :class="form.is_active ? 'text-emerald-700' : 'text-slate-500'">Status Rekening Aktif</label>
          </div>
        </div>
      </div>

      <template #footer>
        <div class="flex gap-3 p-2">
          <Button label="Batal" icon="pi pi-times" text class="!rounded-xl !text-slate-400 hover:!bg-slate-100" @click="dialogOpen = false" />
          <Button label="Simpan Rekening" icon="pi pi-check" :loading="saving" class="!rounded-xl !bg-emerald-600 !border-none !px-8 h-12 font-bold shadow-lg shadow-emerald-100" @click="save" />
        </div>
      </template>
    </Dialog>
  </AppPage>
</template>

<script setup>
import { ref, reactive, onMounted } from 'vue';
import { useI18n } from 'vue-i18n';
import { useToast } from 'primevue/usetoast';
import { financeApi } from '@/api/finance';
import AppPage from '@/components/layout/AppPage.vue';
import DataTable from 'primevue/datatable';
import Column from 'primevue/column';
import Tag from 'primevue/tag';
import Button from 'primevue/button';
import Dialog from 'primevue/dialog';
import InputText from 'primevue/inputtext';
import InputNumber from 'primevue/inputnumber';
import Checkbox from 'primevue/checkbox';
import Card from 'primevue/card';

const { t: $t } = useI18n();
const toast = useToast();
const loading = ref(false);
const saving = ref(false);
const rows = ref([]);
const dialogOpen = ref(false);

const form = reactive({
  id: null,
  name: '',
  account_number: '',
  balance: 0,
  is_active: true
});

function money(value) {
  return Number(value || 0).toLocaleString('id-ID');
}

async function load() {
  loading.value = true;
  try {
    const response = await financeApi.getAccounts();
    rows.value = response?.data?.data || [];
  } finally {
    loading.value = false;
  }
}

function openDialog() {
  form.id = null;
  form.name = '';
  form.account_number = '';
  form.balance = 0;
  form.is_active = true;
  dialogOpen.value = true;
}

function editItem(data) {
  form.id = data.id;
  form.name = data.name;
  form.account_number = data.account_number;
  form.balance = data.balance;
  form.is_active = !!data.is_active;
  dialogOpen.value = true;
}

async function save() {
  if (!form.name) {
    toast.add({ severity: 'error', summary: 'Error', detail: 'Nama rekening wajib diisi.', life: 3000 });
    return;
  }

  saving.value = true;
  try {
    if (form.id) {
        await financeApi.updateAccount(form.id, form);
        toast.add({ severity: 'success', summary: 'Berhasil', detail: 'Rekening diperbarui', life: 2000 });
    } else {
        await financeApi.createAccount(form);
        toast.add({ severity: 'success', summary: 'Berhasil', detail: 'Rekening ditambahkan', life: 2000 });
    }
    dialogOpen.value = false;
    load();
  } catch (error) {
    toast.add({ severity: 'error', summary: 'Gagal', detail: error?.response?.data?.message || 'Gagal menyimpan data', life: 3000 });
  } finally {
    saving.value = false;
  }
}

async function confirmDelete(data) {
    if (confirm(`Apakah Anda yakin ingin menghapus rekening "${data.name}"?`)) {
        try {
            await financeApi.deleteAccount(data.id);
            toast.add({ severity: 'success', summary: 'Berhasil', detail: 'Rekening dihapus', life: 2000 });
            load();
        } catch (error) {
            toast.add({ severity: 'error', summary: 'Gagal', detail: error?.response?.data?.message || 'Gagal menghapus data', life: 3000 });
        }
    }
}

onMounted(() => {
  load();
});
</script>

<style scoped>
@reference "@/style.css";
</style>
