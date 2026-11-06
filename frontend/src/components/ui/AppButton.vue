<template>
  <Button
    :class="variantClasses[variant] || variantClasses.primary"
  >
    <slot />
  </Button>
</template>

<script setup>
import Button from 'primevue/button';

defineProps({
  variant: {
    type: String,
    default: 'primary' // 'primary' | 'table-edit' | 'table-delete' | 'dialog-save' | 'secondary'
  }
});

const variantClasses = {
  primary: '!rounded-xl !px-4 !py-2 !bg-slate-900 !border-slate-900 hover:!bg-slate-800 text-white font-semibold shadow-xs transition-all duration-150 cursor-pointer',
  'table-edit': '!w-8 !h-8 !p-0 transition-all !text-slate-500 !border-slate-200 hover:!bg-slate-50 hover:!border-slate-300 !rounded-lg',
  'table-delete': '!w-8 !h-8 !p-0 transition-all !text-rose-500 !border-slate-200 hover:!bg-rose-50 hover:!border-rose-200 !rounded-lg',
  'dialog-save': '!rounded-xl !h-10 !px-5 !bg-slate-900 !border-slate-900 hover:!bg-slate-800 text-white font-semibold shadow-xs transition-all duration-150 cursor-pointer',
  secondary: '!rounded-xl !px-4 !py-2 !bg-white !border !border-slate-200 !text-slate-700 shadow-xs hover:!bg-slate-50 transition-all duration-150 cursor-pointer font-medium'
};
</script>
