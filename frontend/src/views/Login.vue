<template>
  <div class="min-h-screen bg-app-gradient flex flex-col">
    <!-- Header / Branding -->
    <div class="pt-6 pb-4 px-6 flex items-center justify-between md:justify-center">
      <div class="flex items-center gap-3">
        <!-- Logo PNG -->
        <img src="/images/logo-restoku.png" alt="Restoku" class="h-20 w-auto drop-shadow-md">
      </div>
    </div>

    <!-- Main Login Card -->
    <div class="flex-1 flex items-center justify-center px-4 pb-8 md:pb-12">
      <div class="w-full max-w-md md:max-w-lg lg:max-w-xl bg-white rounded-3xl shadow-2xl overflow-hidden">
        <div class="p-8 md:p-10 lg:p-12">

          <!-- Title -->
          <div class="text-center mb-8">
            <h1 class="text-3xl font-bold text-gray-900">{{ $t('auth.login_title') }}</h1>
            <p class="text-gray-600 mt-2 text-base leading-relaxed">
              {{ $t('auth.login_subtitle') }}
            </p>
          </div>

          <form @submit.prevent="handleLogin" class="space-y-6">
            <div class="space-y-5">
              <!-- Email -->
              <div>
                <label class="block text-sm font-medium text-gray-700 mb-1.5">{{ $t('auth.email') }}</label>
                <InputText v-model="form.email" type="email" placeholder="contoh@email.com" fluid autocomplete="email"
                  :invalid="!!authStore.error" />
              </div>

              <!-- Password -->
              <div>
                <label class="block text-sm font-medium text-gray-700 mb-1.5">{{ $t('auth.password') }}</label>
                <Password v-model="form.password" :placeholder="$t('auth.password_placeholder')" fluid :feedback="false" toggleMask
                  autocomplete="current-password" :invalid="!!authStore.error" />
              </div>

              <!-- Remember Me -->
              <div class="flex items-center justify-between">
                <div class="flex items-center gap-2">
                  <Checkbox v-model="form.remember" inputId="remember" :binary="true" />
                  <label for="remember" class="text-sm text-gray-600 cursor-pointer">Ingat saya</label>
                </div>
                <!-- Lupa Password (Optional) -->
                <!-- <router-link to="/forgot-password" class="text-emerald-600 hover:text-emerald-700 text-sm font-medium transition-colors">
                  Lupa Password?
                </router-link> -->
              </div>
            </div>

            <!-- Lupa Password
            <div class="flex justify-end">
              <router-link to="/forgot-password"
                class="text-emerald-600 hover:text-emerald-700 text-sm font-medium transition-colors">
                Lupa Password?
              </router-link>
            </div> -->

            <!-- Login Button -->
            <Button type="submit" :label="$t('auth.login_button')" :loading="authStore.loading" class="w-full !bg-emerald-600 hover:!bg-emerald-700 active:!bg-emerald-800 
                     text-white font-semibold py-3.5 text-base rounded-2xl shadow-md transition-all" size="large" />

            <!-- Error Message -->
            <div v-if="authStore.error"
              class="text-red-600 text-sm text-center bg-red-50 p-4 rounded-2xl border border-red-100" role="alert">
              {{ authStore.error }}
            </div>
          </form>

          <!-- Divider -->
          <div class="flex items-center gap-4 my-8">
            <div class="flex-1 h-px bg-gray-200"></div>
            <span class="text-gray-400 text-sm font-medium px-3">{{ $t('auth.or') }}</span>
            <div class="flex-1 h-px bg-gray-200"></div>
          </div>

          <!-- Google Login
          <Button label="Login dengan Google (Khusus Owner)" severity="secondary" outlined
            class="w-full rounded-2xl py-3" @click="handleGoogleLogin">
            <template #icon>
              <img src="https://www.google.com/images/branding/googleg/1x/googleg_standard_color_24dp.png" alt="Google"
                class="w-5 h-5">
            </template>
</Button> -->

          <!-- Register Link -->
          <div class="text-center mt-8 text-gray-600 text-sm">
            {{ $t('auth.no_account') }}
            <router-link to="/register" class="text-emerald-600 font-semibold hover:underline">
              {{ $t('auth.register_here') }}
            </router-link>
          </div>
        </div>
      </div>
    </div>

    <!-- Footer -->
    <div class="pb-8 text-center text-white/75 text-sm">
      {{ $t('auth.available_on') }}
    </div>
  </div>
</template>

<script setup>
import { reactive } from 'vue';
import { useRouter } from 'vue-router';
import { useAuthStore } from '@/stores/auth';

import InputText from 'primevue/inputtext';
import Password from 'primevue/password';
import Checkbox from 'primevue/checkbox';
import Button from 'primevue/button';
import { useI18n } from 'vue-i18n';
const { t: $t } = useI18n();

const router = useRouter();
const authStore = useAuthStore();

const form = reactive({
  email: '',
  password: '',
  remember: false,
  device_name: 'Web Browser'
});

const handleLogin = async () => {
  if (!form.email || !form.password) return;

  try {
    await authStore.login(form);
    router.push('/dashboard');
  } catch (err) {
    console.error('Login error:', err);
  }
};

// const handleGoogleLogin = () => {
//   // TODO: Implement Google OAuth later
//   console.log('Google Login clicked');
// };
</script>