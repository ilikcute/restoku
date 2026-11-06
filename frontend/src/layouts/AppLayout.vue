<template>
  <div class="flex h-screen bg-slate-50/70 font-sans text-slate-800 overflow-hidden">
    <!-- Mobile Overlay -->
    <div v-if="sidebarOpen" class="fixed inset-0 z-40 bg-black/40 lg:hidden" @click="sidebarOpen = false" />

    <!-- Docked Sidebar -->
    <AppSidebar v-model="sidebarOpen" />

    <!-- Main Workspace Area -->
    <div class="flex flex-1 flex-col min-w-0 overflow-hidden">
      <!-- Docked Header -->
      <AppHeader @toggle-sidebar="sidebarOpen = !sidebarOpen" />

      <!-- Scrollable Content Canvas -->
      <main class="flex-1 overflow-y-auto p-4 md:p-6">
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
