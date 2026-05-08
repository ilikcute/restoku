<template>
  <AppPage :title="$t('settings.printer_settings')" :breadcrumb="[$t('common.settings'), $t('settings.printer_settings')]" no-card>
    <div class="grid gap-6 xl:grid-cols-[1.1fr_0.9fr]">
      <Card class="!rounded-3xl border-none shadow-sm overflow-hidden">
        <template #title>
          <div class="flex items-center gap-3 px-2 pt-2">
            <div class="w-10 h-10 rounded-xl bg-amber-50 text-amber-600 flex items-center justify-center">
              <i class="pi pi-print text-xl"></i>
            </div>
            <div>
              <div class="text-lg font-bold text-slate-800">{{ $t('settings.printer_settings') }}</div>
              <p class="text-sm text-slate-400 font-normal">{{ $t('settings.printer_settings_desc') }}</p>
            </div>
          </div>
        </template>
        <template #content>
          <div class="p-2 space-y-6">
            <div class="rounded-2xl border border-slate-200 bg-slate-50 p-4">
              <div class="flex items-start gap-3">
                <Checkbox v-model="form.use_default" :binary="true" inputId="useDefaultPrinter" />
                <div class="space-y-1">
                  <label for="useDefaultPrinter" class="font-bold text-slate-800 cursor-pointer">Gunakan default server</label>
                  <p class="text-sm text-slate-500">
                    Jika aktif, sistem memakai nilai dari `config/printer.php` dan `.env`. Jika nonaktif, gunakan printer khusus tenant ini.
                  </p>
                </div>
              </div>
            </div>

            <div class="grid gap-4 md:grid-cols-2">
              <div class="flex flex-col gap-2">
                <label class="text-xs font-bold text-slate-400 uppercase tracking-wider ml-1">Tipe Koneksi</label>
                <Select
                  v-model="form.connection_type"
                  :options="connectionTypes"
                  optionLabel="label"
                  optionValue="value"
                  class="w-full !rounded-xl !bg-slate-50 !border-slate-100"
                  pt:input:class="!p-3"
                  :disabled="form.use_default"
                  placeholder="Pilih tipe koneksi"
                />
              </div>

              <div v-if="!form.use_default && form.connection_type === 'windows'" class="flex flex-col gap-2">
                <label class="text-xs font-bold text-slate-400 uppercase tracking-wider ml-1">Printer Ready Terdeteksi</label>
                <div class="flex gap-2">
                  <Select
                    v-model="selectedDetectedPrinter"
                    :options="detectedPrinters"
                    optionLabel="label"
                    optionValue="value"
                    class="w-full !rounded-xl !bg-slate-50 !border-slate-100"
                    pt:input:class="!p-3"
                    placeholder="Pilih printer dari Windows"
                    :disabled="scanning"
                    @change="applyDetectedPrinter"
                  />
                  <Button icon="pi pi-refresh" severity="secondary" outlined class="!rounded-xl shrink-0" :loading="scanning" @click="scanPrinters" />
                </div>
                <p class="text-xs text-slate-400">
                  Menampilkan printer Windows yang terdeteksi dalam kondisi siap/online. Jika kosong, Anda masih bisa isi manual.
                </p>
              </div>

              <div class="flex flex-col gap-2">
                <label class="text-xs font-bold text-slate-400 uppercase tracking-wider ml-1">Alamat / Nama Printer</label>
                <InputText
                  v-model="form.address"
                  class="!rounded-xl !bg-slate-50 !border-slate-100"
                  :disabled="form.use_default"
                  :placeholder="addressPlaceholder"
                />
              </div>
            </div>

            <div v-if="form.connection_type === 'network'" class="grid gap-4 md:grid-cols-2">
              <div class="flex flex-col gap-2">
                <label class="text-xs font-bold text-slate-400 uppercase tracking-wider ml-1">Port</label>
                <InputNumber
                  v-model="form.port"
                  :min="1"
                  :max="65535"
                  class="w-full"
                  :disabled="form.use_default"
                  pt:input:class="!rounded-xl !bg-slate-50 !p-3 !border-slate-100"
                />
              </div>
            </div>

            <div class="flex flex-wrap gap-3 pt-2 border-t border-slate-50">
              <Button :label="$t('common.save')" icon="pi pi-check" class="!rounded-xl !px-8 h-12 font-bold" :loading="saving" @click="save" />
              <Button label="Test Print" icon="pi pi-send" severity="secondary" outlined class="!rounded-xl !px-6 h-12 font-bold" :loading="testing" @click="testPrinter" />
            </div>
          </div>
        </template>
      </Card>

      <Card class="!rounded-3xl border-none shadow-sm overflow-hidden">
        <template #title>
          <div class="flex items-center gap-3 px-2 pt-2">
            <div class="w-10 h-10 rounded-xl bg-sky-50 text-sky-600 flex items-center justify-center">
              <i class="pi pi-server text-xl"></i>
            </div>
            <span class="text-lg font-bold text-slate-800">Resolusi Konfigurasi</span>
          </div>
        </template>
        <template #content>
          <div class="p-2 space-y-4">
            <div class="rounded-2xl border border-dashed border-slate-200 bg-white p-5 space-y-3">
              <div class="flex justify-between items-center text-sm">
                <span class="text-slate-500">Mode aktif</span>
                <span class="font-black" :class="form.use_default ? 'text-sky-600' : 'text-emerald-600'">
                  {{ form.use_default ? 'Default Server' : 'Custom Tenant' }}
                </span>
              </div>
              <div class="flex justify-between items-center text-sm">
                <span class="text-slate-500">Tipe koneksi</span>
                <span class="font-bold text-slate-900">{{ resolved.connection_type || '-' }}</span>
              </div>
              <div class="flex justify-between items-center text-sm">
                <span class="text-slate-500">Alamat</span>
                <span class="font-bold text-slate-900">{{ resolved.address || '-' }}</span>
              </div>
              <div class="flex justify-between items-center text-sm">
                <span class="text-slate-500">Port</span>
                <span class="font-bold text-slate-900">{{ resolved.connection_type === 'network' ? resolved.port : '-' }}</span>
              </div>
            </div>

            <div class="rounded-2xl bg-slate-900 text-white p-5 space-y-2">
              <div class="text-[10px] uppercase tracking-[0.25em] text-slate-400 font-bold">Default Server</div>
              <div class="text-sm">Tipe: <span class="font-bold">{{ defaults.connection_type || '-' }}</span></div>
              <div class="text-sm">Alamat: <span class="font-bold">{{ defaults.address || '-' }}</span></div>
              <div class="text-sm">Port: <span class="font-bold">{{ defaults.port || '-' }}</span></div>
            </div>

            <div class="rounded-2xl bg-amber-50 border border-amber-100 p-4 text-sm text-amber-900">
              Untuk printer Windows, isi nama printer persis seperti yang terdaftar di mesin server.
              Untuk printer Network, isi IP printer dan port-nya.
            </div>
          </div>
        </template>
      </Card>
    </div>
  </AppPage>
</template>

<script setup>
import { computed, onMounted, reactive, ref } from 'vue';
import { useI18n } from 'vue-i18n';
import { useToast } from 'primevue/usetoast';
import AppPage from '@/components/layout/AppPage.vue';
import Card from 'primevue/card';
import Button from 'primevue/button';
import Checkbox from 'primevue/checkbox';
import InputNumber from 'primevue/inputnumber';
import InputText from 'primevue/inputtext';
import Select from 'primevue/select';
import { tenantApi } from '@/api/tenant';

const { t: $t } = useI18n();
const toast = useToast();
const saving = ref(false);
const testing = ref(false);
const scanning = ref(false);
const selectedDetectedPrinter = ref(null);
const detectedPrinters = ref([]);

const connectionTypes = [
  { label: 'Windows Printer', value: 'windows' },
  { label: 'Network Printer', value: 'network' },
  { label: 'File / LPT', value: 'file' },
];

const form = reactive({
  use_default: true,
  connection_type: 'windows',
  address: '',
  port: 9100,
});

const defaults = reactive({
  connection_type: 'windows',
  address: '',
  port: 9100,
});

const resolved = computed(() => ({
  connection_type: form.use_default ? defaults.connection_type : form.connection_type,
  address: form.use_default ? defaults.address : form.address,
  port: form.use_default ? defaults.port : form.port,
}));

const addressPlaceholder = computed(() => {
  if (form.connection_type === 'network') return 'Contoh: 192.168.1.50';
  if (form.connection_type === 'file') return 'Contoh: LPT1';
  return 'Contoh: POS-58 / EPSON TM-T82';
});

async function load() {
  try {
    const response = await tenantApi.getPrinter();
    const data = response.data.data;

    form.use_default = data.use_default ?? true;
    form.connection_type = data.connection_type || 'windows';
    form.address = data.address || '';
    form.port = data.port || 9100;

    defaults.connection_type = data.defaults?.connection_type || 'windows';
    defaults.address = data.defaults?.address || '';
    defaults.port = data.defaults?.port || 9100;

    selectedDetectedPrinter.value = form.connection_type === 'windows' ? form.address : null;
  } catch (error) {
    toast.add({ severity: 'error', summary: 'Gagal', detail: 'Tidak dapat memuat pengaturan printer.', life: 3000 });
  }
}

function applyDetectedPrinter() {
  if (selectedDetectedPrinter.value) {
    form.address = selectedDetectedPrinter.value;
    form.connection_type = 'windows';
  }
}

async function scanPrinters() {
  scanning.value = true;
  try {
    const response = await tenantApi.scanReadyPrinters();
    const printers = response.data.data || [];

    detectedPrinters.value = printers.map((printer) => ({
      label: `${printer.name}${printer.share_name ? ` (Share: ${printer.share_name})` : ' (Belum di-share)'}${printer.is_default ? ' (Default)' : ''}${printer.port_name ? ` - ${printer.port_name}` : ''}`,
      value: printer.share_name || printer.name,
    }));

    if (!detectedPrinters.value.length) {
      toast.add({
        severity: 'warn',
        summary: 'Printer',
        detail: 'Tidak ada printer Windows dengan status siap yang terdeteksi.',
        life: 3000,
      });
      return;
    }

    const currentPrinter = printers.find((printer) => [printer.name, printer.share_name].includes(form.address));
    if (currentPrinter) {
      selectedDetectedPrinter.value = currentPrinter.share_name || currentPrinter.name;
      return;
    }

    if (!form.address && !form.use_default && form.connection_type === 'windows') {
      const defaultPrinter = printers.find((printer) => printer.is_default) || printers[0];
      selectedDetectedPrinter.value = defaultPrinter?.share_name || defaultPrinter?.name || null;
      applyDetectedPrinter();
      return;
    }

    selectedDetectedPrinter.value = null;
  } catch (error) {
    const detail = error?.response?.data?.message || 'Gagal membaca printer dari Windows.';
    toast.add({ severity: 'error', summary: 'Gagal', detail, life: 3500 });
  } finally {
    scanning.value = false;
  }
}

async function save() {
  // Validasi data sebelum save
  if (!form.use_default) {
    if (!form.connection_type) {
      toast.add({ severity: 'warn', summary: 'Validasi', detail: 'Tipe koneksi harus dipilih.', life: 3000 });
      return;
    }
    if (!form.address) {
      toast.add({ severity: 'warn', summary: 'Validasi', detail: 'Alamat/Nama printer harus diisi.', life: 3000 });
      return;
    }
    if (form.connection_type === 'network' && !form.port) {
      toast.add({ severity: 'warn', summary: 'Validasi', detail: 'Port harus diisi untuk printer network.', life: 3000 });
      return;
    }
  }

  saving.value = true;
  try {
    const payload = {
      use_default: form.use_default,
      connection_type: form.use_default ? null : form.connection_type,
      address: form.use_default ? null : form.address,
      port: form.use_default ? null : (form.connection_type === 'network' ? form.port : null),
    };

    await tenantApi.updatePrinter(payload);

    toast.add({ severity: 'success', summary: '✓ Tersimpan', detail: 'Pengaturan printer berhasil diperbarui.', life: 2500 });
    await load();
  } catch (error) {
    const detail = error?.response?.data?.message || 'Gagal menyimpan pengaturan printer.';
    toast.add({ severity: 'error', summary: 'Gagal', detail, life: 3000 });
  } finally {
    saving.value = false;
  }
}

async function testPrinter() {
  testing.value = true;
  try {
    await tenantApi.testPrinter();
    toast.add({ severity: 'success', summary: 'Test Print', detail: 'Perintah test print berhasil dikirim.', life: 3000 });
  } catch (error) {
    const detail = error?.response?.data?.message || 'Test print gagal.';
    toast.add({ severity: 'error', summary: 'Gagal', detail, life: 3500 });
  } finally {
    testing.value = false;
  }
}

onMounted(async () => {
  await load();

  if (!form.use_default && form.connection_type === 'windows') {
    scanPrinters();
  }
});
</script>
