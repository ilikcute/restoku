<template>
  <AppPage
    title="Database Backup & Restore"
    subtitle="Kelola keamanan data Anda dengan melakukan backup berkala"
    :breadcrumb="[$t('sidebar.settings'), 'Database']"
  >
    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
      <!-- Export Card -->
      <div class="bg-white p-8 rounded-3xl shadow-sm border border-slate-100 flex flex-col items-center text-center space-y-6 transition-all hover:shadow-md">
        <div class="w-20 h-20 bg-emerald-50 rounded-2xl flex items-center justify-center">
          <i class="pi pi-database text-4xl text-emerald-500"></i>
        </div>
        <div class="space-y-2">
          <h3 class="text-xl font-bold text-slate-800">Export Database</h3>
          <p class="text-slate-500 text-sm leading-relaxed">
            Unduh seluruh data master Anda (Produk, Kategori, Supplier, dll) dalam format JSON untuk cadangan.
          </p>
        </div>
        <Button 
          label="Unduh Backup Sekarang" 
          icon="pi pi-download" 
          class="!rounded-2xl !px-8 !py-4 !bg-emerald-600 !border-none shadow-lg shadow-emerald-200"
          :loading="exporting"
          @click="exportDatabase"
        />
      </div>

      <!-- Import Card -->
      <div class="bg-white p-8 rounded-3xl shadow-sm border border-slate-100 flex flex-col items-center text-center space-y-6 transition-all hover:shadow-md">
        <div class="w-20 h-20 bg-amber-50 rounded-2xl flex items-center justify-center">
          <i class="pi pi-upload text-4xl text-amber-500"></i>
        </div>
        <div class="space-y-2">
          <h3 class="text-xl font-bold text-slate-800">Restore Database</h3>
          <p class="text-slate-500 text-sm leading-relaxed">
            Pulihkan data dari file backup sebelumnya. <br>
            <span class="text-rose-500 font-bold">Peringatan:</span> Proses ini akan memperbarui data yang ada.
          </p>
        </div>
        <input 
          type="file" 
          ref="fileInput" 
          class="hidden" 
          accept=".json" 
          @change="handleImport"
        />
        <Button 
          label="Pilih File & Restore" 
          icon="pi pi-refresh" 
          severity="warning"
          class="!rounded-2xl !px-8 !py-4 shadow-lg shadow-amber-200"
          :loading="importing"
          @click="$refs.fileInput.click()"
        />
      </div>
    </div>

    <!-- Info Section -->
    <div class="mt-8 bg-sky-50 p-6 rounded-2xl border border-sky-100 flex gap-4 items-start">
      <i class="pi pi-info-circle text-2xl text-sky-500 mt-1"></i>
      <div class="space-y-1">
        <h4 class="font-bold text-sky-900">Tentang Backup & Restore</h4>
        <p class="text-sky-700 text-sm leading-relaxed">
          Sistem ini melakukan backup pada data master saja. Data transaksi (penjualan) tidak termasuk dalam file ini untuk menjaga ukuran file tetap ringan. Pastikan Anda melakukan backup secara rutin setelah melakukan banyak perubahan pada data produk atau harga.
        </p>
      </div>
    </div>
  </AppPage>
</template>

<script setup>
import { ref } from 'vue';
import { useToast } from 'primevue/usetoast';
import { useConfirm } from 'primevue/useconfirm';
import { databaseApi } from '@/api/settings';
import AppPage from '@/components/layout/AppPage.vue';
import Button from 'primevue/button';

const toast = useToast();
const confirm = useConfirm();
const exporting = ref(false);
const importing = ref(false);
const fileInput = ref(null);

async function exportDatabase() {
  exporting.value = true;
  try {
    const response = await databaseApi.export();
    const url = window.URL.createObjectURL(new Blob([response.data]));
    const link = document.createElement('a');
    link.href = url;
    const date = new Date().toISOString().split('T')[0];
    link.setAttribute('download', `restoku_backup_${date}.json`);
    document.body.appendChild(link);
    link.click();
    link.remove();
    toast.add({ severity: 'success', summary: 'Export Berhasil', detail: 'File backup berhasil diunduh', life: 3000 });
  } catch (error) {
    console.error('Export Error:', error);
    toast.add({ severity: 'error', summary: 'Export Gagal', detail: 'Terjadi kesalahan saat mengekspor data', life: 3000 });
  } finally {
    exporting.value = false;
  }
}

function handleImport(event) {
  const file = event.target.files[0];
  if (!file) return;

  confirm.require({
    message: 'Apakah Anda yakin ingin memulihkan data dari backup ini? Data yang ada akan diperbarui/ditambahkan.',
    header: 'Konfirmasi Restore',
    icon: 'pi pi-exclamation-triangle',
    acceptClass: 'p-button-warning',
    accept: async () => {
      importing.value = true;
      const formData = new FormData();
      formData.append('file', file);

      try {
        await databaseApi.import(formData);
        toast.add({ severity: 'success', summary: 'Restore Berhasil', detail: 'Data Anda telah diperbarui', life: 3000 });
        // Clear input
        event.target.value = '';
      } catch (error) {
        console.error('Import Error:', error);
        toast.add({ 
          severity: 'error', 
          summary: 'Restore Gagal', 
          detail: error.response?.data?.message || 'Gagal memproses file backup', 
          life: 5000 
        });
      } finally {
        importing.value = false;
      }
    },
    reject: () => {
      event.target.value = '';
    }
  });
}
</script>
