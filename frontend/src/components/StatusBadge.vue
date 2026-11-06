<template>
  <span
    :class="[
      'inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-[10px] font-bold uppercase tracking-widest border whitespace-nowrap',
      variantClasses[resolvedVariant] || variantClasses.info
    ]"
  >
    <i
      v-if="showIcon"
      :class="[
        'text-[10px]',
        resolvedVariant === 'active' || resolvedVariant === 'success' || resolvedVariant === 'income' || resolvedVariant === 'match'
          ? 'pi pi-check-circle'
          : resolvedVariant === 'failed' || resolvedVariant === 'danger' || resolvedVariant === 'inactive' || resolvedVariant === 'expense'
            ? 'pi pi-times-circle'
            : resolvedVariant === 'pending' || resolvedVariant === 'warning' || resolvedVariant === 'mismatch'
              ? 'pi pi-exclamation-triangle'
              : 'pi pi-circle-fill'
      ]"
    />
    <slot>{{ displayLabel }}</slot>
  </span>
</template>

<script setup>
import { computed } from 'vue';

const props = defineProps({
  status: { type: String, default: '' },
  label: { type: String, default: '' },
  size: { type: String, default: 'default' }, // default | sm
  showIcon: { type: Boolean, default: true }
});

const resolvedVariant = computed(() => {
  const s = String(props.status).toLowerCase().trim();
  if (['active', 'success', 'completed', 'verified', 'approved', 'adjusted', 'online', 'open', 'match'].includes(s)) return 'active';
  if (['income'].includes(s)) return 'income';
  if (['expense'].includes(s)) return 'expense';
  if (['inactive', 'danger', 'failed', 'cancelled', 'rejected', 'deleted', 'offline', 'closed', 'critical', 'not_in_dpkad'].includes(s)) return 'danger';
  if (['pending', 'warning', 'investigation', 'draft', 'on-hold', 'mismatch'].includes(s)) return 'warning';
  if (['not_in_restoku', 'purple'].includes(s)) return 'purple';
  if (['info', 'secondary'].includes(s)) return 'info';
  return s || 'info';
});

const displayLabel = computed(() => {
  if (props.label) return props.label;
  const s = String(props.status).toLowerCase().trim();
  if (s === 'income') return 'Masuk';
  if (s === 'expense') return 'Keluar';
  if (s === 'match') return 'Cocok';
  if (s === 'mismatch') return 'Beda Nominal';
  if (s === 'not_in_dpkad') return 'Belum di DPKAD';
  if (s === 'not_in_restoku') return 'Hanya di DPKAD';
  return props.status || resolvedVariant.value;
});

const variantClasses = {
  active:    'bg-emerald-50 text-emerald-600 border-emerald-100',
  success:   'bg-emerald-50 text-emerald-600 border-emerald-100',
  income:    'bg-emerald-50 text-emerald-600 border-emerald-100',
  expense:   'bg-rose-50 text-rose-600 border-rose-100',
  inactive:  'bg-rose-50 text-rose-600 border-rose-100',
  danger:    'bg-rose-50 text-rose-600 border-rose-100',
  failed:    'bg-rose-50 text-rose-600 border-rose-100',
  pending:   'bg-amber-50 text-amber-600 border-amber-100',
  warning:   'bg-amber-50 text-amber-600 border-amber-100',
  purple:    'bg-purple-50 text-purple-600 border-purple-100',
  info:      'bg-slate-50 text-slate-500 border-slate-100',
};
</script>
