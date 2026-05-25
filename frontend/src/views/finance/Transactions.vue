<template>
  <AppPage title="Keuangan" :breadcrumb="['Manajemen', 'Transaksi']" no-card>
    <template #actions>
      <Button label="Tambah Transaksi" icon="pi pi-plus"
        class="!rounded-2xl !px-6 !bg-emerald-600 !border-none shadow-lg shadow-emerald-200/50" @click="openDialog" />
    </template>
    <div class="space-y-6">
      <!-- Main Table Card -->
      <Card class="!rounded-3xl border-none shadow-sm overflow-hidden bg-white">
        <template #content>
          <AppDataTable :value="rows" :loading="loading" paginator :rows="20" class="app-table"
            responsiveLayout="scroll">
            <Column :header="$t('common.date')" style="width: 150px">
              <template #body="{ data }">
                <div class="flex flex-col">
                  <span class="text-sm font-bold text-slate-700">{{ formatDate(data.transaction_date) }}</span>
                  <span class="text-[10px] text-slate-400 uppercase font-bold tracking-widest">{{
                    data.transaction_number }}</span>
                </div>
              </template>
            </Column>
            <Column :header="$t('finance.type')" style="width: 120px">
              <template #body="{ data }">
                <div :class="[
                  'px-3 py-1 rounded-full text-[10px] font-black uppercase tracking-widest inline-flex items-center gap-1.5',
                  data.type === 'income' ? 'bg-emerald-50 text-emerald-600 border border-emerald-100' : 'bg-rose-50 text-rose-600 border border-rose-100'
                ]">
                  <span
                    :class="['w-1.5 h-1.5 rounded-full', data.type === 'income' ? 'bg-emerald-500' : 'bg-rose-500']"></span>
                  {{ data.type === 'income' ? 'Masuk' : 'Keluar' }}
                </div>
              </template>
            </Column>
            <Column :header="$t('common.category')">
              <template #body="{ data }">
                <div class="flex items-center gap-2">
                  <div class="w-8 h-8 rounded-lg flex items-center justify-center bg-slate-50 text-slate-500">
                    <i
                      :class="['pi', data.type === 'income' ? 'pi-arrow-down-left' : 'pi-arrow-up-right', 'text-xs']"></i>
                  </div>
                  <span class="font-medium text-slate-700">{{ data.type === 'income' ? data.income_category?.name :
                    data.expense_category?.name }}</span>
                </div>
              </template>
            </Column>
            <Column :header="$t('purchasing.account')">
              <template #body="{ data }">
                <div class="flex items-center gap-2 text-slate-500">
                  <i class="pi pi-wallet text-xs"></i>
                  <span class="text-sm">{{ data.account?.name }}</span>
                </div>
              </template>
            </Column>
            <Column field="description" :header="$t('common.description')" />
            <Column field="amount" :header="$t('common.total')" style="width: 180px">
              <template #body="{ data }">
                <span :class="[
                  'text-lg font-black tracking-tight',
                  data.type === 'income' ? 'text-emerald-600' : 'text-slate-800'
                ]">
                  <span class="text-xs font-bold mr-0.5">{{ data.type === 'income' ? '+' : '-' }}</span>
                  <span class="text-xs font-bold mr-1">Rp</span>
                  {{ money(data.amount) }}
                </span>
              </template>
            </Column>
          </AppDataTable>
        </template>
      </Card>

      <!-- Transaction Modal -->
      <Dialog v-model:visible="dialogOpen" :style="{ width: '500px' }"
        :header="form.id ? 'Edit Transaksi' : 'Transaksi Baru'" :modal="true"
        class="p-fluid !rounded-3xl overflow-hidden shadow-2xl" pt:root:class="!rounded-3xl"
        pt:header:class="!bg-slate-50 !p-6 !border-b !border-slate-100" pt:content:class="!p-6">
        <div class="space-y-6">
          <!-- Form Header Info -->
          <div class="flex items-start gap-4 p-4 rounded-2xl border transition-colors duration-300"
            :class="form.type === 'income' ? 'bg-emerald-50 border-emerald-100' : 'bg-rose-50 border-rose-100'">
            <div class="w-10 h-10 rounded-xl flex items-center justify-center shrink-0 shadow-sm"
              :class="form.type === 'income' ? 'bg-emerald-500 text-white' : 'bg-rose-500 text-white'">
              <i :class="['pi', form.type === 'income' ? 'pi-plus-circle' : 'pi-minus-circle', 'text-xl']"></i>
            </div>
            <div>
              <p class="text-sm font-bold" :class="form.type === 'income' ? 'text-emerald-900' : 'text-rose-900'">Input
                Kas {{
                  form.type === 'income' ? 'Masuk' : 'Keluar' }}</p>
              <p class="text-[11px] leading-relaxed font-medium"
                :class="form.type === 'income' ? 'text-emerald-700/70' : 'text-rose-700/70'">Pencatatan keuangan yang
                akurat
                membantu Anda menganalisa performa bisnis dengan lebih baik.</p>
            </div>
          </div>

          <div class="space-y-4">
            <!-- Type Selector -->
            <div class="flex flex-col gap-2">
              <label class="text-xs font-bold text-slate-400 uppercase tracking-widest ml-1">Tipe Transaksi</label>
              <SelectButton v-model="form.type" :options="types" optionLabel="label" optionValue="value"
                class="!rounded-xl overflow-hidden border border-slate-100"
                pt:button:class="!py-3 !text-xs !font-bold !border-none" />
            </div>

            <div class="grid grid-cols-2 gap-4">
              <!-- Account -->
              <div class="flex flex-col gap-2">
                <label class="text-xs font-bold text-slate-400 uppercase tracking-widest ml-1">Sumber Dana</label>
                <Select v-model="form.account_id" :options="accounts" optionLabel="name" optionValue="id"
                  class="!rounded-xl !bg-slate-50 !border-slate-100" pt:input:class="!p-3 !text-sm" />
              </div>
              <!-- Date -->
              <div class="flex flex-col gap-2">
                <label class="text-xs font-bold text-slate-400 uppercase tracking-widest ml-1">Tanggal</label>
                <DatePicker v-model="dateValue" dateFormat="yy-mm-dd" showIcon iconDisplay="input"
                  class="!rounded-xl overflow-hidden" pt:input:class="!bg-slate-50 !border-slate-100 !p-3 !text-sm" />
              </div>
            </div>

            <!-- Category -->
            <div class="flex flex-col gap-2">
              <label class="text-xs font-bold text-slate-400 uppercase tracking-widest ml-1">Kategori {{ form.type ===
                'income' ? 'Pemasukan' : 'Pengeluaran' }}</label>
              <Select v-if="form.type === 'income'" v-model="form.income_category_id" :options="incomeCategories"
                optionLabel="name" optionValue="id" placeholder="Pilih Kategori Pemasukan"
                class="!rounded-xl !bg-slate-50 !border-slate-100" pt:input:class="!p-3 !text-sm" />
              <Select v-else v-model="form.expense_category_id" :options="expenseCategories" optionLabel="name"
                optionValue="id" placeholder="Pilih Kategori Pengeluaran"
                class="!rounded-xl !bg-slate-50 !border-slate-100" pt:input:class="!p-3 !text-sm" />
            </div>

            <!-- Amount -->
            <div class="flex flex-col gap-2">
              <label class="text-xs font-bold text-slate-400 uppercase tracking-widest ml-1">Jumlah (Nominal)</label>
              <div class="relative">
                <span class="absolute left-4 top-1/2 -translate-y-1/2 text-slate-400 font-bold text-sm">Rp</span>
                <InputNumber v-model="form.amount" mode="decimal" :minFractionDigits="0" placeholder="0"
                  class="!rounded-xl overflow-hidden !border-slate-100"
                  pt:input:class="!bg-slate-50 !pl-10 !p-4 !text-xl !font-black !text-slate-800" />
              </div>
            </div>

            <!-- Description -->
            <div class="flex flex-col gap-2">
              <label class="text-xs font-bold text-slate-400 uppercase tracking-widest ml-1">Catatan / Deskripsi</label>
              <Textarea v-model="form.description" rows="2" placeholder="Tulis keterangan transaksi di sini..."
                class="!rounded-xl !bg-slate-50 !border-slate-100 focus:!ring-blue-500 !p-3 !text-sm" autoResize />
            </div>
          </div>
        </div>

        <template #footer>
          <div class="flex gap-3 p-2">
            <Button label="Batal" icon="pi pi-times" text class="!rounded-xl !text-slate-400 hover:!bg-slate-100"
              @click="dialogOpen = false" />
            <Button label="Simpan Transaksi" icon="pi pi-check" :loading="saving"
              :class="['!rounded-xl !border-none !px-8 h-12 font-bold shadow-lg', form.type === 'income' ? '!bg-emerald-600 shadow-emerald-100' : '!bg-blue-600 shadow-blue-100']"
              @click="save" />
          </div>
        </template>
      </Dialog>
    </div>
  </AppPage>
</template>

<script setup>
import { reactive, ref, computed, onMounted } from 'vue';
import AppDataTable from '@/components/AppDataTable.vue';
import StatusBadge from '@/components/StatusBadge.vue';
import { useI18n } from 'vue-i18n';
import { useToast } from 'primevue/usetoast';
import { financeApi } from '@/api/finance';
import { unwrapCollection } from '@/utils/api';
import Button from 'primevue/button';
import AppPage from '@/components/layout/AppPage.vue';
import Column from 'primevue/column';
import Dialog from 'primevue/dialog';
import Select from 'primevue/select';
import SelectButton from 'primevue/selectbutton';
import InputNumber from 'primevue/inputnumber';
import InputText from 'primevue/inputtext';
import DatePicker from 'primevue/datepicker';
import Tag from 'primevue/tag';
import Card from 'primevue/card';
import Textarea from 'primevue/textarea';

const { t: $t } = useI18n();
const toast = useToast();
const loading = ref(false);
const saving = ref(false);
const rows = ref([]);
const accounts = ref([]);
const expenseCategories = ref([]);
const incomeCategories = ref([]);
const dialogOpen = ref(false);
const dateValue = ref(new Date());

const types = computed(() => [
  { label: 'Pemasukan', value: 'income' },
  { label: 'Pengeluaran', value: 'expense' }
]);

const form = reactive({
  type: 'expense',
  account_id: null,
  expense_category_id: null,
  income_category_id: null,
  amount: 0,
  description: '',
  transaction_date: ''
});

function openDialog() {
  form.type = 'expense';
  form.account_id = accounts.value[0]?.id || null;
  form.expense_category_id = null;
  form.income_category_id = null;
  form.amount = 0;
  form.description = '';
  dateValue.value = new Date();
  dialogOpen.value = true;
}

function money(value) {
  return Number(value || 0).toLocaleString('id-ID');
}

function formatDate(date) {
  if (!date) return '-';
  return new Date(date).toLocaleDateString('id-ID', {
    day: 'numeric',
    month: 'short',
    year: 'numeric'
  });
}

async function loadRows() {
  loading.value = true;
  try {
    const response = await financeApi.getTransactions();
    const unwrapped = unwrapCollection(response);
    rows.value = unwrapped.items || unwrapped;
  } finally {
    loading.value = false;
  }
}

async function bootstrap() {
  try {
    const [accountRes, expCatRes, incCatRes] = await Promise.all([
      financeApi.getAccounts(),
      financeApi.getExpenseCategories(),
      financeApi.getIncomeCategories()
    ]);
    accounts.value = accountRes?.data?.data || [];
    expenseCategories.value = expCatRes?.data?.data || [];
    incomeCategories.value = incCatRes?.data?.data || [];
  } catch (e) {
    console.error('Failed to load finance options', e);
  }
}

async function save() {
  if (!form.account_id || !form.amount || !form.description) {
    toast.add({ severity: 'error', summary: 'Error', detail: 'Mohon lengkapi semua field wajib.', life: 3000 });
    return;
  }

  saving.value = true;
  try {
    // Format date as YYYY-MM-DD local time
    const d = dateValue.value;
    const offset = d.getTimezoneOffset();
    form.transaction_date = new Date(d.getTime() - (offset * 60 * 1000)).toISOString().split('T')[0];

    await financeApi.createTransaction(form);
    dialogOpen.value = false;
    toast.add({ severity: 'success', summary: $t('common.save'), detail: 'Transaksi berhasil disimpan', life: 2000 });
    loadRows();
  } catch (error) {
    toast.add({ severity: 'error', summary: 'Gagal', detail: error?.response?.data?.message || 'Terjadi kesalahan sistem', life: 3000 });
  } finally {
    saving.value = false;
  }
}

onMounted(() => {
  bootstrap();
  loadRows();
});
</script>
