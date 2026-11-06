<template>
  <div class="flex flex-wrap items-center justify-between gap-4 bg-white p-3 rounded-2xl border border-slate-100 shadow-sm">
    <!-- Left: Date Range & Search -->
    <div class="flex flex-wrap items-center gap-3 flex-1 min-w-0">
      <div class="flex items-center gap-2">
        <label v-if="periodLabel" class="text-xs font-bold text-slate-500 uppercase whitespace-nowrap">
          {{ periodLabel }}
        </label>
        <DatePicker
          :modelValue="modelValue"
          @update:modelValue="$emit('update:modelValue', $event)"
          selectionMode="range"
          :manualInput="false"
          showIcon
          iconDisplay="input"
          class="w-64"
          dateFormat="dd/mm/yy"
        />
      </div>

      <!-- Quick Preset Buttons -->
      <div v-if="showPresets" class="flex items-center gap-1">
        <Button
          label="Hari Ini"
          size="small"
          text
          severity="secondary"
          class="!text-xs !py-1 !px-2.5"
          @click="setToday"
        />
        <Button
          label="Bulan Ini"
          size="small"
          text
          severity="secondary"
          class="!text-xs !py-1 !px-2.5"
          @click="setThisMonth"
        />
      </div>

      <!-- Optional Search Input -->
      <div v-if="showSearch" class="flex-1 min-w-[200px] max-w-sm">
        <InputText
          :modelValue="searchValue"
          @update:modelValue="$emit('update:searchValue', $event)"
          :placeholder="searchPlaceholder"
          class="w-full !rounded-xl !text-xs !py-2"
        />
      </div>

      <Button
        :label="buttonLabel"
        icon="pi pi-search"
        @click="$emit('search')"
        :loading="loading"
        class="!rounded-xl shadow-sm !text-xs !py-2"
      />
    </div>

    <!-- Right: Export buttons & custom slot -->
    <div class="flex items-center gap-2 shrink-0">
      <slot name="actions" />

      <Button
        v-if="showExportPdf"
        label="Cetak PDF"
        icon="pi pi-file-pdf"
        severity="danger"
        outlined
        size="small"
        class="!rounded-xl !text-xs"
        :loading="exportingPdf"
        @click="$emit('export-pdf')"
      />
      <Button
        v-if="showExportExcel"
        label="Export Excel"
        icon="pi pi-file-excel"
        severity="success"
        outlined
        size="small"
        class="!rounded-xl !text-xs"
        :loading="exportingExcel"
        @click="$emit('export-excel')"
      />
    </div>
  </div>
</template>

<script setup>
import DatePicker from 'primevue/datepicker';
import Button from 'primevue/button';
import InputText from 'primevue/inputtext';

const props = defineProps({
  modelValue: { type: Array, default: () => [] },
  periodLabel: { type: String, default: 'Periode' },
  buttonLabel: { type: String, default: 'Tampilkan Laporan' },
  showPresets: { type: Boolean, default: true },
  showSearch: { type: Boolean, default: false },
  searchValue: { type: String, default: '' },
  searchPlaceholder: { type: String, default: 'Cari...' },
  loading: { type: Boolean, default: false },
  showExportExcel: { type: Boolean, default: false },
  showExportPdf: { type: Boolean, default: false },
  exportingExcel: { type: Boolean, default: false },
  exportingPdf: { type: Boolean, default: false }
});

const emit = defineEmits([
  'update:modelValue',
  'update:searchValue',
  'search',
  'export-excel',
  'export-pdf'
]);

function setToday() {
  const today = new Date();
  emit('update:modelValue', [today, today]);
  emit('search');
}

function setThisMonth() {
  const start = new Date(new Date().getFullYear(), new Date().getMonth(), 1);
  const end = new Date();
  emit('update:modelValue', [start, end]);
  emit('search');
}
</script>

