<template>
  <div class="flex flex-wrap items-center justify-between gap-3 bg-white p-3.5 rounded-2xl border border-slate-200/80 shadow-xs">
    <!-- Left: Presets, Date Pickers & Search -->
    <div class="flex flex-wrap items-center gap-2.5 flex-1 min-w-0">
      <!-- Quick Preset Pills (Smooth Segmented Control) -->
      <div v-if="showPresets" class="flex items-center gap-0.5 p-1 bg-slate-100 rounded-xl">
        <button
          type="button"
          v-for="p in presets"
          :key="p.id"
          @click="selectPreset(p.id)"
          :class="[
            'px-2.5 py-1 text-xs rounded-lg font-medium transition-all duration-150',
            activePreset === p.id
              ? 'bg-white text-slate-900 shadow-xs font-semibold'
              : 'text-slate-500 hover:text-slate-800'
          ]"
        >
          {{ p.label }}
        </button>
      </div>

      <!-- Date Inputs: Dari & Sampai -->
      <div class="flex items-center gap-2 flex-wrap sm:flex-nowrap">
        <div class="flex items-center gap-1.5">
          <span class="text-[11px] font-bold text-slate-400 uppercase tracking-wider">Dari</span>
          <DatePicker
            v-model="startDate"
            dateFormat="dd/mm/yy"
            showIcon
            iconDisplay="input"
            class="w-36"
            inputClass="!text-xs !py-1.5 !rounded-xl font-medium"
          />
        </div>

        <span class="text-xs font-bold text-slate-400">s/d</span>

        <div class="flex items-center gap-1.5">
          <span class="text-[11px] font-bold text-slate-400 uppercase tracking-wider">Sampai</span>
          <DatePicker
            v-model="endDate"
            dateFormat="dd/mm/yy"
            showIcon
            iconDisplay="input"
            class="w-36"
            inputClass="!text-xs !py-1.5 !rounded-xl font-medium"
          />
        </div>

        <!-- Duration Badge -->
        <span
          v-if="durationDays > 0"
          class="hidden md:inline-flex items-center gap-1 px-2.5 py-1 rounded-lg text-[11px] font-semibold bg-slate-100 text-slate-600 border border-slate-200/60"
          :title="`Rentang terpilih: ${durationDays} hari`"
        >
          <i class="pi pi-calendar text-[10px] text-slate-400"></i>
          {{ durationDays }} Hari
        </span>
      </div>

      <!-- Optional Search Input -->
      <div v-if="showSearch" class="flex-1 min-w-[180px] max-w-xs">
        <InputText
          :modelValue="searchValue"
          @update:modelValue="$emit('update:searchValue', $event)"
          :placeholder="searchPlaceholder"
          class="w-full !rounded-xl !text-xs !py-1.5"
        />
      </div>

      <!-- Search / Submit Button -->
      <Button
        :label="buttonLabel"
        icon="pi pi-search"
        @click="$emit('search')"
        :loading="loading"
        class="!rounded-xl shadow-xs !text-xs !py-1.5 !px-3.5 font-bold !bg-slate-900 !border-slate-900 hover:!bg-slate-800 text-white"
      />
    </div>

    <!-- Right: Export buttons & custom slot (Clean Subtle Outlines) -->
    <div class="flex items-center gap-2 shrink-0">
      <slot name="actions" />

      <Button
        v-if="showExportPdf"
        label="Cetak PDF"
        size="small"
        class="!rounded-xl !text-xs !py-1.5 !px-3 !bg-white !text-slate-700 !border-slate-200 hover:!bg-slate-50 shadow-xs font-medium"
        :loading="exportingPdf"
        @click="$emit('export-pdf')"
      >
        <template #icon>
          <i class="pi pi-file-pdf mr-1.5 text-rose-500"></i>
        </template>
      </Button>
      <Button
        v-if="showExportExcel"
        label="Export Excel"
        size="small"
        class="!rounded-xl !text-xs !py-1.5 !px-3 !bg-white !text-slate-700 !border-slate-200 hover:!bg-slate-50 shadow-xs font-medium"
        :loading="exportingExcel"
        @click="$emit('export-excel')"
      >
        <template #icon>
          <i class="pi pi-file-excel mr-1.5 text-emerald-600"></i>
        </template>
      </Button>
    </div>
  </div>
</template>

<script setup>
import { computed } from 'vue';
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

const presets = [
  { id: 'today', label: 'Hari Ini' },
  { id: 'yesterday', label: 'Kemarin' },
  { id: 'last7', label: '7 Hari' },
  { id: 'thisMonth', label: 'Bulan Ini' },
  { id: 'lastMonth', label: 'Bulan Lalu' }
];

const startDate = computed({
  get: () => (props.modelValue?.[0] ? new Date(props.modelValue[0]) : null),
  set: (val) => {
    emit('update:modelValue', [val, props.modelValue?.[1] || val]);
  }
});

const endDate = computed({
  get: () => (props.modelValue?.[1] ? new Date(props.modelValue[1]) : null),
  set: (val) => {
    emit('update:modelValue', [props.modelValue?.[0] || val, val]);
  }
});

function toYmd(date) {
  if (!date) return '';
  const d = new Date(date);
  const month = String(d.getMonth() + 1).padStart(2, '0');
  const day = String(d.getDate()).padStart(2, '0');
  return `${d.getFullYear()}-${month}-${day}`;
}

const activePreset = computed(() => {
  if (!props.modelValue || !props.modelValue[0] || !props.modelValue[1]) return null;
  const s = toYmd(props.modelValue[0]);
  const e = toYmd(props.modelValue[1]);

  const now = new Date();
  const todayStr = toYmd(now);
  if (s === todayStr && e === todayStr) return 'today';

  const yest = new Date(now);
  yest.setDate(yest.getDate() - 1);
  const yestStr = toYmd(yest);
  if (s === yestStr && e === yestStr) return 'yesterday';

  const last7 = new Date(now);
  last7.setDate(last7.getDate() - 6);
  if (s === toYmd(last7) && e === todayStr) return 'last7';

  const mStart = toYmd(new Date(now.getFullYear(), now.getMonth(), 1));
  const mEnd = toYmd(new Date(now.getFullYear(), now.getMonth() + 1, 0));
  if (s === mStart && (e === mEnd || e === todayStr)) return 'thisMonth';

  const lmStart = toYmd(new Date(now.getFullYear(), now.getMonth() - 1, 1));
  const lmEnd = toYmd(new Date(now.getFullYear(), now.getMonth(), 0));
  if (s === lmStart && e === lmEnd) return 'lastMonth';

  return null;
});

const durationDays = computed(() => {
  if (!props.modelValue || !props.modelValue[0] || !props.modelValue[1]) return 0;
  const d1 = new Date(props.modelValue[0]);
  const d2 = new Date(props.modelValue[1]);
  d1.setHours(0, 0, 0, 0);
  d2.setHours(0, 0, 0, 0);
  const diffTime = d2.getTime() - d1.getTime();
  if (diffTime < 0) return 0;
  return Math.round(diffTime / (1000 * 60 * 60 * 24)) + 1;
});

function selectPreset(id) {
  const now = new Date();
  let start, end;

  if (id === 'today') {
    start = new Date();
    end = new Date();
  } else if (id === 'yesterday') {
    start = new Date();
    start.setDate(start.getDate() - 1);
    end = new Date(start);
  } else if (id === 'last7') {
    end = new Date();
    start = new Date();
    start.setDate(start.getDate() - 6);
  } else if (id === 'thisMonth') {
    start = new Date(now.getFullYear(), now.getMonth(), 1);
    end = new Date(now.getFullYear(), now.getMonth() + 1, 0);
  } else if (id === 'lastMonth') {
    start = new Date(now.getFullYear(), now.getMonth() - 1, 1);
    end = new Date(now.getFullYear(), now.getMonth(), 0);
  }

  if (start && end) {
    emit('update:modelValue', [start, end]);
    emit('search');
  }
}
</script>
