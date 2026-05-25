<template>
  <span
    :class="[
      'inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-[10px] font-bold uppercase tracking-widest border whitespace-nowrap',
      variantClasses[resolvedVariant]
    ]"
  >
    <i
      v-if="showIcon"
      :class="[
        'text-[10px]',
        resolvedVariant === 'active' || resolvedVariant === 'success'
          ? 'pi pi-check-circle'
          : resolvedVariant === 'failed' || resolvedVariant === 'danger' || resolvedVariant === 'inactive'
            ? 'pi pi-times-circle'
            : resolvedVariant === 'pending' || resolvedVariant === 'warning'
              ? 'pi pi-clock'
              : 'pi pi-circle-fill'
      ]"
    />
    <slot>{{ label || resolvedVariant }}</slot>
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
  if (['active', 'success', 'completed', 'verified', 'approved', 'adjusted', 'online', 'open'].includes(s)) return 'active';
  if (['inactive', 'danger', 'failed', 'cancelled', 'rejected', 'deleted', 'offline', 'closed', 'critical'].includes(s)) return 'inactive';
  if (['pending', 'warning', 'investigation', 'draft', 'on-hold'].includes(s)) return 'pending';
  if (['info', 'secondary'].includes(s)) return 'info';
  return s || 'info';
});

const variantClasses = {
  active:    'bg-emerald-50 text-emerald-600 border-emerald-100',
  success:   'bg-emerald-50 text-emerald-600 border-emerald-100',
  inactive:  'bg-rose-50 text-rose-600 border-rose-100',
  danger:    'bg-rose-50 text-rose-600 border-rose-100',
  failed:    'bg-rose-50 text-rose-600 border-rose-100',
  pending:   'bg-amber-50 text-amber-600 border-amber-100',
  warning:   'bg-amber-50 text-amber-600 border-amber-100',
  info:      'bg-slate-50 text-slate-500 border-slate-100',
};
</script>
