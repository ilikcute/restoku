<template>
  <div class="space-y-5 animate-in fade-in duration-500">
    <!-- Teleport Page Header to AppHeader -->
    <Teleport to="#app-header-title" v-if="isMounted">
      <div class="flex flex-col animate-in fade-in duration-300">
        <h1 class="text-lg md:text-xl font-black text-slate-800 tracking-tight flex items-center gap-2.5">
          <span class="w-1.5 h-5 md:h-6 rounded-full inline-block" :class="accentClass"></span>
          {{ title }}
        </h1>
        <div v-if="breadcrumb?.length || subtitle" class="mt-0.5 flex flex-col gap-0.5">
          <p v-if="subtitle" class="text-slate-400 text-xs md:text-sm font-medium">{{ subtitle }}</p>
          <div v-if="breadcrumb?.length" class="text-[9px] md:text-[10px] text-slate-400 font-semibold uppercase tracking-widest flex items-center gap-1.5 flex-wrap">
            <router-link to="/dashboard" class="hover:text-orange-500 transition-colors">Dashboard</router-link>
            <i class="pi pi-angle-right text-[8px] md:text-[9px]"></i>
            <span v-for="(item, index) in breadcrumb" :key="index" class="flex items-center gap-1.5">
              <span :class="index === breadcrumb.length - 1 ? 'text-slate-600' : ''">{{ item }}</span>
              <i v-if="index < breadcrumb.length - 1" class="pi pi-angle-right text-[8px] md:text-[9px]"></i>
            </span>
          </div>
        </div>
      </div>
    </Teleport>

    <!-- Teleport Actions to AppHeader -->
    <Teleport to="#app-header-actions" v-if="isMounted && $slots.actions">
      <div class="flex items-center gap-2 animate-in fade-in duration-300">
        <slot name="actions"></slot>
      </div>
    </Teleport>

    <!-- Content Section -->
    <div
      v-if="!noCard"
      class="bg-white rounded-2xl shadow-sm border border-slate-100 overflow-hidden animate-in slide-in-from-bottom-4 duration-500"
    >
      <div class="p-5">
        <slot></slot>
      </div>
    </div>
    <div v-else class="animate-in slide-in-from-bottom-4 duration-500">
      <slot></slot>
    </div>
  </div>
</template>

<script setup>
import { computed, ref, onMounted, onUnmounted } from 'vue';

const isMounted = ref(false);

onMounted(() => {
  isMounted.value = true;
  document.body.classList.add('has-page-title');
});

onUnmounted(() => {
  document.body.classList.remove('has-page-title');
});


const props = defineProps({
  title: {
    type: String,
    required: true
  },
  subtitle: {
    type: String,
    default: ''
  },
  accent: {
    type: String,
    default: 'orange'
  },
  breadcrumb: {
    type: Array,
    default: () => []
  },
  noCard: {
    type: Boolean,
    default: false
  }
});

const accentClass = computed(() => {
  const map = {
    orange: 'bg-orange-500',
    emerald: 'bg-emerald-500',
    blue: 'bg-blue-500',
    rose: 'bg-rose-500',
    amber: 'bg-amber-500',
    indigo: 'bg-indigo-500'
  };
  return map[props.accent] || 'bg-orange-500';
});
</script>
