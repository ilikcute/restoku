<template>
  <div class="flex h-screen bg-slate-50 font-sans text-slate-800 p-2 gap-2">
    <!-- Mobile Overlay -->
    <div v-if="sidebarOpen" class="fixed inset-0 z-40 bg-black/50 lg:hidden" @click="sidebarOpen = false" />

    <!-- Sidebar as Floating Card -->
    <AppSidebar v-model="sidebarOpen" />

    <!-- Main Area -->
    <div class="flex flex-1 flex-col gap-2 overflow-hidden min-w-0">
      <!-- Header as Floating Card -->
      <AppHeader @toggle-sidebar="sidebarOpen = !sidebarOpen" />

      <!-- Scrollable Content -->
      <main class="flex-1 overflow-y-auto">
        <router-view />
      </main>
    </div>
  </div>
  <Toast position="top-right" />
  <ConfirmDialog />
</template>

<script setup>
import { onMounted, ref } from 'vue';
import { useShiftStore } from '@/stores/shift';
import Toast from 'primevue/toast';
import ConfirmDialog from 'primevue/confirmdialog';

import AppSidebar from '@/components/layout/AppSidebar.vue';
import AppHeader from '@/components/layout/AppHeader.vue';

const shiftStore = useShiftStore();
const sidebarOpen = ref(false);

onMounted(() => {
  shiftStore.fetchCurrentShift();
});
</script>

<style scoped>
main::-webkit-scrollbar {
  display: none;
}

main {
  scrollbar-width: none;
  -ms-overflow-style: none;
}
</style>
