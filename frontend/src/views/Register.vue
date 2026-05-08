<template>
  <div class="min-h-screen flex items-center justify-center bg-gray-50 py-12 px-4 sm:px-6 lg:px-8">
    <div class="max-w-md w-full space-y-8 p-10 bg-white rounded-xl shadow-lg">
      <div>
        <h2 class="mt-6 text-center text-3xl font-extrabold text-gray-900">
          Create your POS account
        </h2>
      </div>
      <form class="mt-8 space-y-6" @submit.prevent="handleRegister">
        <div class="rounded-md shadow-sm space-y-4">
          <div>
            <label class="block text-sm font-medium text-gray-700">Full Name</label>
            <InputText v-model="form.name" required class="w-full mt-1" />
          </div>
          <div>
            <label class="block text-sm font-medium text-gray-700">Email address</label>
            <InputText v-model="form.email" type="email" required class="w-full mt-1" />
          </div>
          <div>
            <label class="block text-sm font-medium text-gray-700">Phone (Optional)</label>
            <InputText v-model="form.phone" class="w-full mt-1" />
          </div>
          <div>
            <label class="block text-sm font-medium text-gray-700">Tenant/Store Name</label>
            <InputText v-model="form.tenant_name" required class="w-full mt-1" placeholder="e.g. My Awesome Cafe" />
          </div>
          <div>
            <label class="block text-sm font-medium text-gray-700">Password</label>
            <Password v-model="form.password" required class="w-full mt-1" toggleMask />
          </div>
          <div>
            <label class="block text-sm font-medium text-gray-700">Confirm Password</label>
            <Password v-model="form.password_confirmation" required class="w-full mt-1" :feedback="false" toggleMask />
          </div>
        </div>

        <div>
          <Button type="submit" label="Register" :loading="authStore.loading" class="w-full" />
        </div>

        <div v-if="authStore.error" class="text-red-500 text-sm text-center">
          {{ authStore.error }}
        </div>

        <div class="text-center">
          <router-link to="/login" class="text-primary hover:underline text-sm">
            Already have an account? Login here
          </router-link>
        </div>
      </form>
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
