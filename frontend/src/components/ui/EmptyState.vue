<template>
  <div class="text-center py-12 px-4">
    <div class="w-14 h-14 mx-auto mb-3 rounded-2xl bg-slate-50 flex items-center justify-center text-slate-300">
      <i :class="[icon, 'text-2xl']"></i>
    </div>
    <h4 v-if="title" class="font-bold text-slate-700 text-sm mb-1">{{ title }}</h4>
    <p class="text-xs text-slate-400 max-w-sm mx-auto leading-relaxed">{{ message }}</p>
    <div v-if="$slots.action" class="mt-4">
      <slot name="action" />
    </div>
  </div>
</template>

<script setup>
defineProps({
  icon: { type: String, default: 'pi pi-inbox' },
  title: { type: String, default: '' },
  message: { type: String, default: 'Tidak ada data untuk ditampilkan.' }
});
</script>

