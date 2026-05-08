<template>
  <div class="space-y-6 animate-in fade-in duration-500">
    <!-- Header Section (Premium Glassmorphism) -->
    <div class="flex flex-col md:flex-row justify-between items-start md:items-center gap-4 bg-white/60 backdrop-blur-lg p-6 rounded-3xl border border-slate-200/50 shadow-sm">
      <div>
        <h1 class="text-2xl font-black text-slate-800 tracking-tight italic uppercase flex items-center gap-3">
          <span class="w-2 h-8 bg-emerald-500 rounded-full inline-block"></span>
          {{ title }}
        </h1>
        <div v-if="breadcrumb || subtitle" class="mt-1 flex flex-col gap-1">
          <p v-if="subtitle" class="text-slate-500 text-sm font-medium">{{ subtitle }}</p>
          <div v-if="breadcrumb.length" class="text-[10px] text-slate-400 font-bold uppercase tracking-widest flex items-center gap-2">
            <router-link to="/dashboard" class="hover:text-emerald-600 transition-colors">DASHBOARD</router-link>
            <i class="pi pi-angle-right text-[8px]"></i>
            <span v-for="(item, index) in breadcrumb" :key="index" class="flex items-center gap-2">
              <span :class="index === breadcrumb.length - 1 ? 'text-slate-600' : ''">{{ item }}</span>
              <i v-if="index < breadcrumb.length - 1" class="pi pi-angle-right text-[8px]"></i>
            </span>
          </div>
        </div>
      </div>
      <div v-if="$slots.actions" class="flex items-center gap-3 w-full md:w-auto">
        <slot name="actions"></slot>
      </div>
    </div>
    
    <!-- Content Section -->
    <div v-if="!noCard" class="bg-white rounded-3xl shadow-sm border border-slate-100 overflow-hidden animate-in slide-in-from-bottom-4 duration-700">
      <div class="p-6">
        <slot></slot>
      </div>
    </div>
    <div v-else class="animate-in slide-in-from-bottom-4 duration-700">
      <slot></slot>
    </div>
  </div>
</template>

<script setup>
import { computed } from 'vue';

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
    default: 'emerald'
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
    emerald: 'bg-emerald-500',
    orange: 'bg-orange-500',
    blue: 'bg-blue-500',
    rose: 'bg-rose-500',
    amber: 'bg-amber-500',
    indigo: 'bg-indigo-500'
  };
  return map[props.accent] || 'bg-emerald-500';
});
</script>
