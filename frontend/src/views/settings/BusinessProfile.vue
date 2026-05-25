<template>
  <AppPage :title="$t('settings.business_profile')"
    :breadcrumb="[$t('common.settings'), $t('settings.business_profile')]">
    <div class="grid gap-6 xl:grid-cols-2">
      <!-- Business Information -->
      <Card class="!rounded-3xl border-none shadow-sm overflow-hidden">
        <template #title>
          <div class="flex items-center gap-3 px-2 pt-2">
            <div class="w-10 h-10 rounded-xl bg-emerald-50 text-emerald-600 flex items-center justify-center">
              <i class="pi pi-building text-xl"></i>
            </div>
            <span class="text-lg font-bold text-slate-800">{{ $t('settings.business_info') }}</span>
          </div>
        </template>
        <template #content>
          <div class="p-2 space-y-6">
            <!-- Logo Upload -->
            <div class="flex flex-col items-center gap-4 py-2">
              <label class="text-xs font-bold text-slate-400 uppercase tracking-wider">{{ $t('settings.business_logo')
                }}</label>
              <div class="relative group">
                <div v-if="!form.logo_url"
                  class="w-32 h-32 rounded-3xl bg-slate-100 flex items-center justify-center border-4 border-white shadow-md">
                  <i class="pi pi-image text-slate-300 text-4xl"></i>
                </div>
                <img v-else :src="form.logo_url"
                  class="w-32 h-32 rounded-3xl object-contain bg-white border-4 border-white shadow-md transition-transform group-hover:scale-105" />

                <div
                  class="absolute inset-0 flex items-center justify-center bg-black/40 rounded-3xl opacity-0 group-hover:opacity-100 transition-opacity cursor-pointer">
                  <i class="pi pi-camera text-white text-2xl"></i>
                  <input type="file" class="absolute inset-0 opacity-0 cursor-pointer" @change="onLogoSelect"
                    accept="image/*" />
                </div>
              </div>
              <p class="text-[10px] text-slate-400 uppercase tracking-widest font-bold">{{ $t('settings.max_size') }}
                (2MB)</p>
            </div>

            <div class="grid gap-4">
              <div class="flex flex-col gap-2">
                <label class="text-xs font-bold text-slate-400 uppercase tracking-wider ml-1">{{ $t('common.name')
                  }}</label>
                <InputText v-model="form.name"
                  class="!rounded-xl !bg-slate-50 !border-slate-100 focus:!ring-emerald-500" />
              </div>

              <div class="flex flex-col gap-2">
                <label class="text-xs font-bold text-slate-400 uppercase tracking-wider ml-1">{{
                  $t('settings.business_phone') }}</label>
                <InputText v-model="form.phone"
                  class="!rounded-xl !bg-slate-50 !border-slate-100 focus:!ring-emerald-500" />
              </div>

              <div class="flex flex-col gap-2">
                <label class="text-xs font-bold text-slate-400 uppercase tracking-wider ml-1">{{
                  $t('settings.business_email') }}</label>
                <InputText v-model="form.email"
                  class="!rounded-xl !bg-slate-50 !border-slate-100 focus:!ring-emerald-500" />
              </div>

              <div class="flex flex-col gap-2">
                <label class="text-xs font-bold text-slate-400 uppercase tracking-wider ml-1">{{
                  $t('settings.business_address') }}</label>
                <Textarea v-model="form.address" rows="3"
                  class="!rounded-xl !bg-slate-50 !border-slate-100 focus:!ring-emerald-500" autoResize />
              </div>
            </div>

            <div class="pt-2 border-t border-slate-50">
              <Button :label="$t('common.save')" icon="pi pi-check"
                class="!rounded-xl !px-8 h-12 font-bold shadow-lg shadow-emerald-100" :loading="saving" @click="save" />
            </div>
          </div>
        </template>
      </Card>

      <!-- Receipt Customization -->
      <Card class="!rounded-3xl border-none shadow-sm overflow-hidden">
        <template #title>
          <div class="flex items-center gap-3 px-2 pt-2">
            <div class="w-10 h-10 rounded-xl bg-blue-50 text-blue-600 flex items-center justify-center">
              <i class="pi pi-print text-xl"></i>
            </div>
            <span class="text-lg font-bold text-slate-800">{{ $t('settings.receipt_footer') }}</span>
          </div>
        </template>
        <template #content>
          <div class="p-2 space-y-5">
            <div class="flex flex-col gap-2">
              <label class="text-xs font-bold text-slate-400 uppercase tracking-wider ml-1">{{
                $t('settings.receipt_footer') }}</label>
              <Textarea v-model="form.footer_text" rows="5"
                class="!rounded-xl !bg-slate-50 !border-slate-100 focus:!ring-blue-500"
                :placeholder="$t('settings.receipt_footer_desc')" />
              <p class="text-xs text-slate-400 mt-1 italic">{{ $t('settings.receipt_footer_desc') }}</p>
            </div>

            <!-- Preview Struk Simple -->
            <div class="bg-slate-50 rounded-2xl p-6 border border-dashed border-slate-200">
              <div class="max-w-[250px] mx-auto bg-white shadow-sm p-4 text-[10px] font-mono text-slate-600 space-y-2">
                <div class="text-center border-b border-dashed border-slate-200 pb-2 mb-2">
                  <div v-if="form.logo_url" class="mb-2">
                    <img :src="form.logo_url" class="h-10 mx-auto object-contain" />
                  </div>
                  <p class="font-bold text-xs text-slate-900 uppercase">{{ form.name || 'NAMA OUTLET' }}</p>
                  <p>{{ form.address || 'Alamat Outlet...' }}</p>
                  <p>Telp: {{ form.phone || '-' }}</p>
                </div>
                <div class="flex justify-between">
                  <span>Item x1</span>
                  <span>10.000</span>
                </div>
                <div
                  class="flex justify-between font-bold border-t border-dashed border-slate-200 pt-1 mt-1 text-slate-900">
                  <span>TOTAL</span>
                  <span>10.000</span>
                </div>
                <div class="text-center pt-4 border-t border-dashed border-slate-200 mt-2 italic whitespace-pre-line">
                  {{ form.footer_text || 'Terima kasih atas kunjungan Anda' }}
                </div>
              </div>
            </div>
          </div>
        </template>
      </Card>
    </div>
  </AppPage>
</template>

<script setup>
import { reactive, ref, onMounted } from 'vue';
import { useI18n } from 'vue-i18n';
import { useToast } from 'primevue/usetoast';
import AppPage from '@/components/layout/AppPage.vue';
import Card from 'primevue/card';
import InputText from 'primevue/inputtext';
import Textarea from 'primevue/textarea';
import Button from 'primevue/button';
import { tenantApi } from '@/api/tenant';

const { t: $t } = useI18n();
const toast = useToast();
const saving = ref(false);

const form = reactive({
  name: '',
  address: '',
  phone: '',
  email: '',
  footer_text: '',
  logo: null,
  logo_url: null
});

async function load() {
  try {
    const response = await tenantApi.get();
    const data = response.data.data;
    form.name = data.name || '';
    form.address = data.address || '';
    form.phone = data.phone || '';
    form.email = data.email || '';
    form.footer_text = data.footer_text || '';
    form.logo_url = data.logo_url || null;
  } catch (error) {
    toast.add({ severity: 'error', summary: 'Error', detail: 'Gagal memuat data profil bisnis', life: 3000 });
  }
}

function onLogoSelect(event) {
  const file = event.target.files[0];
  if (file) {
    form.logo = file;
    // Local preview
    const reader = new FileReader();
    reader.onload = (e) => {
      form.logo_url = e.target.result;
    };
    reader.readAsDataURL(file);
  }
}

async function save() {
  saving.value = true;
  try {
    const formData = new FormData();
    formData.append('name', form.name);
    formData.append('address', form.address || '');
    formData.append('phone', form.phone || '');
    formData.append('email', form.email || '');
    formData.append('footer_text', form.footer_text || '');
    if (form.logo) {
      formData.append('logo', form.logo);
    }

    await tenantApi.update(formData);
    toast.add({ severity: 'success', summary: $t('common.save'), detail: $t('settings.business_profile'), life: 2000 });
    load(); // Reload to get the new logo_url from server
  } catch (error) {
    const msg = error?.response?.data?.message || 'Terjadi kesalahan saat menyimpan';
    toast.add({ severity: 'error', summary: 'Gagal', detail: msg, life: 3000 });
  } finally {
    saving.value = false;
  }
}

onMounted(() => {
  load();
});
</script>
