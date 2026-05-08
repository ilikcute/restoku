<template>
  <div class="flex h-screen bg-slate-50 font-sans text-slate-800">
    <div v-if="sidebarOpen" class="fixed inset-0 z-40 bg-black/50 lg:hidden" @click="sidebarOpen = false" />
    
    <AppSidebar v-model="sidebarOpen" />
    
    <div class="flex flex-1 flex-col overflow-hidden">
      <AppHeader @toggle-sidebar="sidebarOpen = !sidebarOpen" />
      
      <main class="flex-1 overflow-y-auto p-4 lg:p-8">
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
