<template>
  <div class="min-h-screen bg-app-gradient flex flex-col">
    <!-- Header / Branding -->
    <div class="pt-6 pb-4 px-6 flex items-center justify-between md:justify-center">
      <div class="flex items-center gap-3">
        <img src="/images/logo-restoku.png" alt="Restoku" class="h-20 w-auto drop-shadow-md">
      </div>
    </div>

    <!-- Main Register Card -->
    <div class="flex-1 flex items-center justify-center px-4 pb-8 md:pb-12">
      <div class="w-full max-w-md md:max-w-lg lg:max-w-xl bg-white rounded-3xl shadow-2xl overflow-hidden">
        <div class="p-8 md:p-10 lg:p-12">

          <!-- Title -->
          <div class="text-center mb-8">
            <h1 class="text-3xl font-bold text-slate-900">Create your POS account</h1>
            <p class="text-slate-600 mt-2 text-base leading-relaxed">
              Register your store and start managing orders, inventory, and finance in one place.
            </p>
          </div>

          <form @submit.prevent="handleRegister" class="space-y-6">
            <div class="space-y-5">
              <div>
                <label class="block text-sm font-medium text-slate-700 mb-1.5">Full Name</label>
                <InputText v-model="form.name" required fluid class="w-full" />
              </div>
              <div>
                <label class="block text-sm font-medium text-slate-700 mb-1.5">Email address</label>
                <InputText v-model="form.email" type="email" required fluid class="w-full" />
              </div>
              <div>
                <label class="block text-sm font-medium text-slate-700 mb-1.5">Phone (Optional)</label>
                <InputText v-model="form.phone" fluid class="w-full" />
              </div>
              <div>
                <label class="block text-sm font-medium text-slate-700 mb-1.5">Tenant/Store Name</label>
                <InputText v-model="form.tenant_name" required fluid class="w-full" placeholder="e.g. My Awesome Cafe" />
              </div>
              <div>
                <label class="block text-sm font-medium text-slate-700 mb-1.5">Password</label>
                <Password v-model="form.password" required fluid toggleMask class="w-full" />
              </div>
              <div>
                <label class="block text-sm font-medium text-slate-700 mb-1.5">Confirm Password</label>
                <Password v-model="form.password_confirmation" required fluid :feedback="false" toggleMask class="w-full" />
              </div>
            </div>

            <!-- Register Button -->
            <Button type="submit" label="Register" :loading="authStore.loading"
              class="w-full !bg-emerald-600 hover:!bg-emerald-700 active:!bg-emerald-800
                     text-white font-semibold py-3.5 text-base rounded-2xl shadow-md transition-all"
              size="large" />

            <div v-if="authStore.error"
              class="text-red-600 text-sm text-center bg-red-50 p-4 rounded-2xl border border-red-100" role="alert">
              {{ authStore.error }}
            </div>
          </form>

          <!-- Divider -->
          <div class="flex items-center gap-4 my-8">
            <div class="flex-1 h-px bg-slate-200"></div>
            <span class="text-slate-400 text-sm font-medium px-3">or</span>
            <div class="flex-1 h-px bg-slate-200"></div>
          </div>

          <!-- Login Link -->
          <div class="text-center mt-8 text-slate-600 text-sm">
            Already have an account?
            <router-link to="/login" class="text-emerald-600 font-semibold hover:underline">
              Login here
            </router-link>
          </div>
        </div>
      </div>
    </div>

    <!-- Footer -->
    <div class="pb-8 text-center text-white/75 text-sm">
      Powered by Restoku POS
    </div>
  </div>
</template>

<script setup>
import { reactive } from 'vue';
import { useRouter } from 'vue-router';
import { useAuthStore } from '@/stores/auth';
import InputText from 'primevue/inputtext';
import Password from 'primevue/password';
import Button from 'primevue/button';

const router = useRouter();
const authStore = useAuthStore();

const form = reactive({
  name: '',
  email: '',
  phone: '',
  password: '',
  password_confirmation: '',
  tenant_name: '',
  device_name: 'Web Browser'
});

const handleRegister = async () => {
  try {
    await authStore.register(form);
    router.push('/dashboard');
  } catch (err) {
    console.error(err);
  }
};
</script>
