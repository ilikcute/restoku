<template>
  <header class="flex h-20 shrink-0 items-center justify-between px-6 lg:px-10 bg-transparent">
    <div class="flex items-center gap-4 flex-1">
      <!-- Mobile Menu Toggle -->
      <div class="lg:hidden">
        <Button icon="pi pi-bars" text rounded class="!text-slate-800 bg-white shadow-sm" @click="$emit('toggle-sidebar')" />
      </div>

      <!-- Greeting Section (Moved to Left) -->
      <div class="hidden lg:flex flex-col items-start px-2">
        <div class="flex items-center gap-1.5">
          <span class="text-sm font-medium text-slate-500">{{ $t('header.greeting') }},</span>
          <span class="text-sm font-bold text-slate-800">{{ authStore.user?.attributes?.name?.split(' ')[0] || 'Admin' }}</span>
          <span class="text-sm font-medium text-slate-500">! {{ $t('header.welcome_back') }} {{ tenant.name ? 'di ' : '' }}<span class="font-bold text-emerald-600">{{ tenant.name }}</span>.</span>
        </div>
        <p class="text-[10px] text-slate-400 mt-0.5 flex items-center gap-2">
          <span class="flex items-center">
            <i class="pi pi-calendar text-[10px] mr-1"></i>
            {{ todayFormatted }}
          </span>
          <span class="w-px h-2 bg-slate-300"></span>
          <span class="flex items-center font-mono font-bold text-slate-500">
            <i class="pi pi-clock text-[10px] mr-1"></i>
            {{ currentTime }}
          </span>
          <span class="ml-1 italic text-slate-400">. {{ $t('header.nice_day') }}</span>
        </p>
      </div>
    </div>

    <div class="flex items-center gap-4 lg:gap-6">
      <Tag v-if="shiftStore.activeShift" icon="pi pi-clock" :value="$t('header.shift_open')" severity="success" class="hidden sm:flex shadow-sm !rounded-xl px-3 py-1.5" />

      <!-- Divider -->
      <div class="h-8 w-px bg-slate-200 hidden md:block"></div>

      <!-- User Profile -->
      <button class="flex items-center gap-3 hover:bg-white/50 p-1.5 rounded-2xl transition-colors" @click="toggleUserMenu">
        <div class="text-right hidden md:block">
          <p class="text-[10px] text-slate-400 font-bold uppercase tracking-wider mb-0.5">{{ $t('header.logged_in_as') }}</p>
          <p class="text-xs text-emerald-600 font-bold capitalize">{{ authStore.user?.attributes?.role || 'Administrator' }}</p>
        </div>
        <img :src="authStore.user?.attributes?.avatar_url || `https://ui-avatars.com/api/?name=${authStore.user?.attributes?.name || 'Admin'}&background=10b981&color=fff`" alt="User" class="w-10 h-10 rounded-full border-2 border-white shadow-sm" />
      </button>
      <Menu ref="userMenu" :model="userMenuItems" popup class="!rounded-xl !shadow-xl !border-slate-100" />
    </div>
  </header>
</template>

<script setup>
import { computed, onMounted, onUnmounted, ref } from 'vue';
import { useRouter } from 'vue-router';
import { useAuthStore } from '@/stores/auth';
import { useShiftStore } from '@/stores/shift';
import Button from 'primevue/button';
import Tag from 'primevue/tag';
import Menu from 'primevue/menu';

defineEmits(['toggle-sidebar']);

const router = useRouter();
const authStore = useAuthStore();
const shiftStore = useShiftStore();
const userMenu = ref();
const currentTime = ref('');
let timer = null;

const todayFormatted = computed(() => {
  return new Intl.DateTimeFormat('id-ID', {
    day: 'numeric',
    month: 'long',
    year: 'numeric'
  }).format(new Date());
});

const permissions = computed(() => authStore.user?.attributes?.permissions || []);
const tenant = computed(() => authStore.user?.relationships?.tenant?.data?.attributes || {});

const userMenuItems = computed(() => [
  { label: $t('user_menu.profile'), icon: 'pi pi-user', command: () => router.push('/settings/profile') },
  {
    label: $t('user_menu.users'),
    icon: 'pi pi-users',
    command: () => router.push('/settings/users'),
    visible: permissions.value.includes('manage-users')
  },
  { separator: true },
  {
    label: $t('user_menu.logout'),
    icon: 'pi pi-sign-out',
    command: async () => {
      await authStore.logout();
      router.push('/login');
    }
  }
]);

import { useI18n } from 'vue-i18n';
const { t: $t } = useI18n();

function toggleUserMenu(event) {
  userMenu.value?.toggle(event);
}

function updateTime() {
  const now = new Date();
  currentTime.value = now.toLocaleTimeString('id-ID', { 
    hour: '2-digit', 
    minute: '2-digit', 
    second: '2-digit' 
  });
}

onMounted(() => {
  updateTime();
  timer = setInterval(updateTime, 1000);
});

onUnmounted(() => {
  if (timer) clearInterval(timer);
});
</script>
