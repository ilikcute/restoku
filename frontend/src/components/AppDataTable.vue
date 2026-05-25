<template>
  <div :class="wrapperClass">
    <!-- Optional Toolbar -->
    <div
      v-if="$slots['toolbar-left'] || $slots['toolbar-right']"
      :class="toolbarClass"
    >
      <div class="flex items-center gap-3 flex-1 min-w-0">
        <slot name="toolbar-left" />
      </div>
      <div class="flex items-center gap-2 shrink-0">
        <slot name="toolbar-right" />
      </div>
    </div>

    <DataTable
      v-bind="attrs"
      :value="value"
      :loading="loading"
      :paginator="paginator"
      :rows="rows"
      :totalRecords="totalRecords"
      :lazy="lazy"
      :first="first ?? 0"
      :rowsPerPageOptions="rowsPerPageOptions"
      :selectionMode="selectionMode"
      :dataKey="dataKey"
      :stripedRows="stripedRows"
      class="app-table"
      :class="{ 'app-table-compact': compact, 'app-table-striped': stripedRows, 'app-table-flat': flat }"
      @page="onPage"
      @rowSelect="$emit('rowSelect', $event)"
      @rowUnselect="$emit('rowUnselect', $event)"
      @selectAllChange="$emit('selectAllChange', $event)"
      scrollable
      scrollHeight="flex"
      tableStyle="min-width: 100%"
    >
      <slot />
    </DataTable>
  </div>
</template>

<script setup>
import DataTable from 'primevue/datatable';
import { computed, useAttrs } from 'vue';

defineOptions({
  inheritAttrs: false
});


const attrs = useAttrs();
const emit = defineEmits(['page', 'rowSelect', 'rowUnselect', 'selectAllChange', 'update:first']);

const props = defineProps({
  value: { type: Array, default: () => [] },
  loading: { type: Boolean, default: false },
  paginator: { type: Boolean, default: true },
  rows: { type: Number, default: 10 },
  totalRecords: { type: Number, default: 0 },
  lazy: { type: Boolean, default: false },
  first: { type: Number, default: 0 },
  rowsPerPageOptions: { type: Array, default: () => [10, 20, 50] },
  selectionMode: { type: String, default: null },
  dataKey: { type: String, default: 'id' },
  stripedRows: { type: Boolean, default: false },
  compact: { type: Boolean, default: false },
  framed: { type: Boolean, default: false },
  flat: { type: Boolean, default: false }
});

const wrapperClass = computed(() => (
  props.framed ? 'bg-white rounded-2xl shadow-sm border border-slate-100 overflow-hidden' : ''
));

const toolbarClass = computed(() => (
  props.framed
    ? 'flex items-center justify-between gap-4 px-5 py-4 border-b border-slate-100'
    : 'flex items-center justify-between gap-4 px-1 py-2'
));

function onPage(event) {
  emit('update:first', event.first ?? 0);
  emit('page', event);
}
</script>
