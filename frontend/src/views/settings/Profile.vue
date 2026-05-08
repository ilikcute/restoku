<template>
  <AppPage :title="$t('settings.profile_settings')" :breadcrumb="[$t('common.settings'), $t('sidebar.profile')]" no-card>
    <div class="grid gap-6 xl:grid-cols-2">
      <!-- Profile Information -->
      <Card class="!rounded-3xl border-none shadow-sm overflow-hidden">
        <template #title>
          <div class="flex items-center gap-3 px-2 pt-2">
            <div class="w-10 h-10 rounded-xl bg-emerald-50 text-emerald-600 flex items-center justify-center">
              <i class="pi pi-user text-xl"></i>
            </div>
            <span class="text-lg font-bold text-slate-800">{{ $t('settings.personal_info') }}</span>
          </div>
        </template>
        <template #content>
          <div class="p-2 space-y-6">
            <!-- Avatar Upload -->
            <div class="flex flex-col items-center gap-4 py-2">
              <div class="relative group">
                <img :src="profile.avatar_url" class="w-32 h-32 rounded-3xl object-cover border-4 border-white shadow-md transition-transform group-hover:scale-105" />
                <div class="absolute inset-0 flex items-center justify-center bg-black/40 rounded-3xl opacity-0 group-hover:opacity-100 transition-opacity cursor-pointer">
                  <i class="pi pi-camera text-white text-2xl"></i>
                  <input type="file" class="absolute inset-0 opacity-0 cursor-pointer" @change="onAvatarSelect" accept="image/*" />
                </div>
              </div>
              <p class="text-xs text-slate-400">{{ $t('settings.click_to_change') }} ({{ $t('settings.max_size') }})</p>
            </div>

            <div class="flex flex-col gap-2">
              <label class="text-xs font-bold text-slate-400 uppercase tracking-wider ml-1">{{ $t('common.name') }}</label>
              <InputText v-model="profile.name" class="!rounded-xl !bg-slate-50 !border-slate-100 focus:!ring-emerald-500" :placeholder="$t('common.name')" />
            </div>
            <div class="flex flex-col gap-2">
              <label class="text-xs font-bold text-slate-400 uppercase tracking-wider ml-1">{{ $t('auth.email') }}</label>
              <InputText v-model="profile.email" class="!rounded-xl !bg-slate-50 !border-slate-100 focus:!ring-emerald-500" :placeholder="$t('auth.email')" />
            </div>
            <div class="pt-2">
              <Button :label="$t('settings.update_profile')" icon="pi pi-check" class="!rounded-xl !px-6" :loading="savingProfile" @click="saveProfile" />
            </div>
          </div>
        </template>
      </Card>

      <!-- Change Password -->
      <Card class="!rounded-3xl border-none shadow-sm overflow-hidden">
        <template #title>
          <div class="flex items-center gap-3 px-2 pt-2">
            <div class="w-10 h-10 rounded-xl bg-amber-50 text-amber-600 flex items-center justify-center">
              <i class="pi pi-lock text-xl"></i>
            </div>
            <span class="text-lg font-bold text-slate-800">{{ $t('settings.security_password') }}</span>
          </div>
        </template>
        <template #content>
          <div class="p-2 space-y-5">
            <div class="flex flex-col gap-2">
              <label class="text-xs font-bold text-slate-400 uppercase tracking-wider ml-1">{{ $t('settings.current_password') }}</label>
              <Password v-model="password.current_password" :feedback="false" class="w-full" inputClass="w-full !rounded-xl !bg-slate-50 !border-slate-100" placeholder="••••••••" toggleMask />
            </div>
            <div class="flex flex-col gap-2">
              <label class="text-xs font-bold text-slate-400 uppercase tracking-wider ml-1">{{ $t('settings.new_password') }}</label>
              <Password v-model="password.password" class="w-full" inputClass="w-full !rounded-xl !bg-slate-50 !border-slate-100" placeholder="••••••••" toggleMask />
            </div>
            <div class="flex flex-col gap-2">
              <label class="text-xs font-bold text-slate-400 uppercase tracking-wider ml-1">{{ $t('settings.confirm_password') }}</label>
              <Password v-model="password.password_confirmation" :feedback="false" class="w-full" inputClass="w-full !rounded-xl !bg-slate-50 !border-slate-100" placeholder="••••••••" toggleMask />
            </div>
            <div class="pt-2">
              <Button :label="$t('settings.change_password')" icon="pi pi-shield" severity="warning" class="!rounded-xl !px-6" :loading="savingPassword" @click="savePassword" />
            </div>
          </div>
        </template>
      </Card>

      <!-- Language Settings -->
      <Card class="!rounded-3xl border-none shadow-sm overflow-hidden">
        <template #title>
          <div class="flex items-center gap-3 px-2 pt-2">
            <div class="w-10 h-10 rounded-xl bg-blue-50 text-blue-600 flex items-center justify-center">
              <i class="pi pi-globe text-xl"></i>
            </div>
            <span class="text-lg font-bold text-slate-800">{{ $t('settings.language') }}</span>
          </div>
        </template>
        <template #content>
          <div class="p-2 space-y-5">
            <div class="flex flex-col gap-2">
              <label class="text-xs font-bold text-slate-400 uppercase tracking-wider ml-1">{{ $t('settings.select_language') }}</label>
              <Select v-model="currentLocale" :options="locales" optionLabel="label" optionValue="value" class="w-full !rounded-xl !bg-slate-50 !border-slate-100" @change="changeLanguage" />
              <p class="text-xs text-slate-400 mt-1">{{ $t('settings.language_desc') }}</p>
            </div>
          </div>
        </template>
      </Card>
    </div>
  </AppPage>
</template>

<script setup>
import { reactive, ref } from 'vue';
import { useI18n } from 'vue-i18n';
import { useToast } from 'primevue/usetoast';
import AppPage from '@/components/layout/AppPage.vue';
import { profileApi } from '@/api/settings';
import { useAuthStore } from '@/stores/auth';
import InputText from 'primevue/inputtext';
import Password from 'primevue/password';
import Button from 'primevue/button';
import Card from 'primevue/card';
import Select from 'primevue/select';

const { t: $t, locale } = useI18n();
const toast = useToast();
const authStore = useAuthStore();
const savingProfile = ref(false);
const savingPassword = ref(false);
const profile = reactive({ name: '', email: '' });
const password = reactive({ current_password: '', password: '', password_confirmation: '' });

const currentLocale = ref(locale.value);
const locales = [
  { label: 'Bahasa Indonesia', value: 'id' },
  { label: 'English', value: 'en' }
];

function changeLanguage() {
  locale.value = currentLocale.value;
  localStorage.setItem('locale', currentLocale.value);
  toast.add({ severity: 'success', summary: $t('settings.language'), detail: $t('common.save'), life: 2000 });
}

async function load() {
  const response = await profileApi.get();
  const data = response?.data?.data?.attributes || {};
  profile.name = data.name || '';
  profile.email = data.email || '';
  profile.avatar_url = data.avatar_url || '';
}

function onAvatarSelect(event) {
  const file = event.target.files[0];
  if (file) {
    profile.avatar = file;
    // Local preview
    const reader = new FileReader();
    reader.onload = (e) => {
      profile.avatar_url = e.target.result;
    };
    reader.readAsDataURL(file);
  }
}

async function saveProfile() {
  savingProfile.value = true;
  try {
    const formData = new FormData();
    formData.append('name', profile.name);
    formData.append('email', profile.email);
    if (profile.avatar) {
      formData.append('avatar', profile.avatar);
    }

    await profileApi.update(formData);
    toast.add({ severity: 'success', summary: $t('common.save'), life: 2000 });
    // Update global state to refresh header avatar/name
    await authStore.fetchUser();
    load();
  } catch (error) {
    toast.add({ severity: 'error', summary: $t('common.save'), detail: error?.response?.data?.message, life: 3000 });
  } finally {
    savingProfile.value = false;
  }
}

async function savePassword() {
  savingPassword.value = true;
  try {
    await profileApi.updatePassword(password);
    password.current_password = '';
    password.password = '';
    password.password_confirmation = '';
    toast.add({ severity: 'success', summary: $t('settings.change_password'), life: 2000 });
  } catch (error) {
    toast.add({ severity: 'error', summary: $t('common.save'), detail: error?.response?.data?.message, life: 3000 });
  } finally {
    savingPassword.value = false;
  }
}

load();
</script>
