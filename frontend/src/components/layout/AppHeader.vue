<template>
  <header
    class="flex min-h-[4rem] py-3 shrink-0 items-center justify-between px-4 md:px-6 bg-white rounded-2xl shadow-sm border border-slate-100 transition-all duration-300">
    <div class="flex items-center gap-4 flex-1">
      <!-- Mobile Menu Toggle -->
      <div class="lg:hidden">
        <button class="p-2 rounded-xl hover:bg-slate-100 text-slate-500 transition-colors"
          @click="$emit('toggle-sidebar')">
          <i class="pi pi-bars text-lg"></i>
        </button>
      </div>

      <!-- Dynamic Page Title (Teleported here) -->
      <div id="app-header-title" class="flex-1 min-w-0"></div>

      <!-- Greeting Section (Hidden when page title exists) -->
      <div id="app-header-greeting" class="hidden lg:flex flex-col items-start">
        <div class="flex items-center gap-1.5">
          <span class="text-sm font-medium text-slate-500">{{ $t('header.greeting') }},</span>
          <span class="text-sm font-bold text-slate-800">{{ authStore.user?.attributes?.name?.split(' ')[0] || 'Admin'
          }}</span>
          <span class="text-sm font-medium text-slate-500">
            ! {{ $t('header.welcome_back') }}
            {{ tenant.name ? 'di ' : '' }}<span class="font-bold text-orange-500">{{ tenant.name }}</span>.
          </span>
        </div>
        <p class="text-[10px] text-slate-400 mt-0.5 flex items-center gap-2">
          <span class="flex items-center">
            <i class="pi pi-calendar text-[10px] mr-1"></i>
            {{ todayFormatted }}
          </span>
          <span class="w-px h-2 bg-slate-200"></span>
          <span class="flex items-center font-mono font-bold text-slate-500">
            <i class="pi pi-clock text-[10px] mr-1"></i>
            {{ currentTime }}
          </span>
          <span class="italic text-slate-400">. {{ $t('header.nice_day') }}</span>
        </p>
      </div>
    </div>

    <!-- Right Actions -->
    <div class="flex items-center gap-2">
      <!-- Dynamic Page Actions (Teleported here) -->
      <div id="app-header-actions" class="flex items-center gap-2 mr-1 md:mr-2"></div>

      <!-- Shift Badge -->
      <Tag v-if="shiftStore.activeShift" icon="pi pi-clock" :value="$t('header.shift_open')" severity="success"
        class="hidden sm:flex !rounded-xl px-3 py-1.5 !bg-orange-50 !text-orange-600 !border-orange-100" />

      <!-- Settings Icon -->
      <button class="p-2.5 rounded-xl hover:bg-slate-100 text-slate-500 transition-colors"
        @click="() => router.push('/settings/tenant')">
        <i class="pi pi-cog text-lg"></i>
      </button>

      <!-- Divider -->
      <div class="h-8 w-px bg-slate-100 mx-1 hidden md:block"></div>

      <!-- User Profile -->
      <button
        class="flex items-center gap-2.5 hover:bg-slate-100 text-slate-400 p-1.5 pr-3 rounded-2xl transition-colors cursor-pointer"
        @click="toggleUserMenu">
        <img
          :src="authStore.user?.attributes?.avatar_url || `https://ui-avatars.com/api/?name=${authStore.user?.attributes?.name || 'Admin'}&background=f97316&color=fff&size=64`"
          alt="User" class="w-9 h-9 rounded-full border-2 border-orange-100" />
        <div class="text-left overflow-hidden md:block">
          <p class="text-xs font-semibold text-slate-800 truncate leading-tight">
            {{ authStore.user?.attributes?.name?.split(' ')[0] || 'Admin' }}
          </p>
          <p class="text-[10px] text-orange-500 font-bold capitalize leading-tight">
            {{ authStore.user?.attributes?.role || 'Administrator' }}
          </p>
        </div>
      </button>
      <Menu ref="userMenu" :model="userMenuItems" popup class="!rounded-xl !shadow-xl !border-slate-100" />
    </div>
  </header>
</template>

<script setup>
import { computed, onMounted, onUnmounted, ref } from 'vue';
import { useRouter } from 'vue-router';
import { useI18n } from 'vue-i18n';
import { useAuthStore } from '@/stores/auth';
import { useShiftStore } from '@/stores/shift';
import Tag from 'primevue/tag';
import Menu from 'primevue/menu';

defineEmits(['toggle-sidebar']);

const router = useRouter();
const authStore = useAuthStore();
const shiftStore = useShiftStore();
const { t: $t } = useI18n();
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

<style>
body.has-page-title #app-header-greeting {
  display: none !important;
}
</style>
