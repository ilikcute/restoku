<template>
  <AppPage
    title="Rekonsiliasi Data DPKAD"
    :breadcrumb="['Laporan', 'Rekonsiliasi DPKAD']"
  >
    <div class="space-y-4">
      <!-- Filters and Actions Header -->
      <div class="flex flex-wrap items-center justify-between gap-4 bg-white p-4 rounded-2xl border border-slate-100 shadow-sm">
        <div class="flex flex-wrap items-center gap-3">
          <label class="text-xs font-bold text-slate-500 uppercase whitespace-nowrap">
            Periode
          </label>
          <DatePicker
            v-model="dates"
            selectionMode="range"
            :manualInput="false"
            showIcon
            iconDisplay="input"
            class="w-64"
            dateFormat="dd/mm/yy"
          />
          <div class="flex items-center gap-1.5">
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
          <Button
            label="Periksa Rekonsiliasi"
            icon="pi pi-search"
            @click="loadReconciliation"
            :loading="loading"
            class="!rounded-xl shadow-sm"
          />
        </div>

        <div class="flex items-center gap-2">
          <Button
            v-if="syncableCount > 0"
            :label="`Sinkronkan Semua Selisih (${syncableCount})`"
            icon="pi pi-sync"
            severity="warning"
            @click="confirmSyncAll"
            :loading="syncingAll"
            class="!rounded-xl shadow-sm"
          />
        </div>
      </div>

      <!-- Balance Status Banner -->
      <div
        v-if="summary"
        :class="[
          'p-4 rounded-2xl border transition-all duration-300 flex items-center justify-between gap-4',
          summary.is_balanced
            ? 'bg-emerald-50/70 border-emerald-200 text-emerald-900'
            : 'bg-amber-50/80 border-amber-200 text-amber-900'
        ]"
      >
        <div class="flex items-center gap-3.5">
          <div
            :class="[
              'w-11 h-11 rounded-xl flex items-center justify-center shrink-0 text-xl font-bold',
              summary.is_balanced
                ? 'bg-emerald-500 text-white shadow-emerald-200 shadow-md'
                : 'bg-amber-500 text-white shadow-amber-200 shadow-md'
            ]"
          >
            <i :class="summary.is_balanced ? 'pi pi-check-circle' : 'pi pi-exclamation-triangle'"></i>
          </div>
          <div>
            <div class="flex items-center gap-2">
              <h3 class="text-base font-black tracking-tight">
                {{ summary.is_balanced ? 'Status: SEIMBANG (BALANCE)' : 'Status: DITEMUKAN SELISIH' }}
              </h3>
              <span
                :class="[
                  'px-2.5 py-0.5 rounded-full text-[10px] font-black uppercase tracking-wider',
                  summary.is_balanced ? 'bg-emerald-100 text-emerald-800' : 'bg-amber-100 text-amber-800'
                ]"
              >
                {{ summary.is_balanced ? '100% Cocok' : `${mismatchedOrMissingCount} Pesanan Bermasalah` }}
              </span>
            </div>
            <p class="text-xs mt-0.5 opacity-90">
              {{
                summary.is_balanced
                  ? 'Data tabel orders dan order_items antara database Restoku dan resto_dpkad cocok secara jumlah record dan nominal.'
                  : 'Terdapat perbedaan jumlah data atau nominal antara database Restoku dan database resto_dpkad. Klik "Sinkronkan" untuk menyamakan data.'
              }}
            </p>
          </div>
        </div>

        <div class="hidden sm:flex items-center gap-3 shrink-0">
          <div class="text-right">
            <div class="text-[10px] font-bold uppercase tracking-wider text-slate-500">Rentang Waktu</div>
            <div class="text-xs font-semibold text-slate-700">
              {{ formatDateId(filters.start_date) }} - {{ formatDateId(filters.end_date) }}
            </div>
          </div>
        </div>
      </div>

      <!-- Metric Summary Cards -->
      <div v-if="summary" class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-4 gap-4">
        <!-- Card 1: Orders Record Count -->
        <Card class="border border-slate-100 shadow-sm !rounded-2xl">
          <template #content>
            <div class="flex items-start justify-between">
              <div>
                <span class="text-xs font-bold text-slate-400 uppercase tracking-wider">Transaksi (Orders)</span>
                <div class="mt-2 flex items-baseline gap-2">
                  <span class="text-2xl font-black text-slate-800">{{ summary.orders.restoku_count }}</span>
                  <span class="text-xs text-slate-400 font-medium">Restoku vs</span>
                  <span class="text-2xl font-black text-slate-800">{{ summary.orders.dpkad_count }}</span>
                  <span class="text-xs text-slate-400 font-medium">DPKAD</span>
                </div>
              </div>
              <div
                :class="[
                  'w-10 h-10 rounded-xl flex items-center justify-center text-sm font-bold',
                  summary.orders.diff_count === 0 ? 'bg-emerald-50 text-emerald-600' : 'bg-rose-50 text-rose-600'
                ]"
              >
                <i class="pi pi-list"></i>
              </div>
            </div>
            <div class="mt-3 pt-3 border-t border-slate-100 flex items-center justify-between text-xs">
              <span class="text-slate-500 font-medium">Selisih Record:</span>
              <span
                :class="[
                  'font-black',
                  summary.orders.diff_count === 0 ? 'text-emerald-600' : 'text-rose-600'
                ]"
              >
                {{ summary.orders.diff_count > 0 ? '+' : '' }}{{ summary.orders.diff_count }} Order
              </span>
            </div>
            <div class="mt-1 flex items-center justify-between text-[11px] text-slate-400">
              <span>Cocok: <b class="text-emerald-600">{{ summary.orders.matched_count }}</b></span>
              <span>Beda: <b class="text-amber-600">{{ summary.orders.mismatched_count }}</b></span>
              <span>Belum di DPKAD: <b class="text-rose-600">{{ summary.orders.not_in_dpkad_count }}</b></span>
            </div>
          </template>
        </Card>

        <!-- Card 2: Order Items Count & Qty -->
        <Card class="border border-slate-100 shadow-sm !rounded-2xl">
          <template #content>
            <div class="flex items-start justify-between">
              <div>
                <span class="text-xs font-bold text-slate-400 uppercase tracking-wider">Item Produk (Items)</span>
                <div class="mt-2 flex items-baseline gap-2">
                  <span class="text-2xl font-black text-slate-800">{{ summary.order_items.restoku_count }}</span>
                  <span class="text-xs text-slate-400 font-medium">Restoku vs</span>
                  <span class="text-2xl font-black text-slate-800">{{ summary.order_items.dpkad_count }}</span>
                  <span class="text-xs text-slate-400 font-medium">DPKAD</span>
                </div>
              </div>
              <div
                :class="[
                  'w-10 h-10 rounded-xl flex items-center justify-center text-sm font-bold',
                  summary.order_items.diff_count === 0 ? 'bg-emerald-50 text-emerald-600' : 'bg-rose-50 text-rose-600'
                ]"
              >
                <i class="pi pi-box"></i>
              </div>
            </div>
            <div class="mt-3 pt-3 border-t border-slate-100 flex items-center justify-between text-xs">
              <span class="text-slate-500 font-medium">Selisih Baris / Qty:</span>
              <span
                :class="[
                  'font-black',
                  summary.order_items.diff_count === 0 && summary.order_items.diff_qty === 0 ? 'text-emerald-600' : 'text-rose-600'
                ]"
              >
                {{ summary.order_items.diff_count }} baris ({{ summary.order_items.diff_qty }} pcs)
              </span>
            </div>
            <div class="mt-1 flex items-center justify-between text-[11px] text-slate-400">
              <span>Qty Restoku: <b class="text-slate-700">{{ summary.order_items.restoku_qty }} pcs</b></span>
              <span>Qty DPKAD: <b class="text-slate-700">{{ summary.order_items.dpkad_qty }} pcs</b></span>
            </div>
          </template>
        </Card>

        <!-- Card 3: Total Sales Amount -->
        <Card class="border border-slate-100 shadow-sm !rounded-2xl">
          <template #content>
            <div class="flex items-start justify-between">
              <div>
                <span class="text-xs font-bold text-slate-400 uppercase tracking-wider">Total Nominal (Grand Total)</span>
                <div class="mt-2">
                  <div class="text-lg font-black text-slate-800">Rp {{ money(summary.orders.restoku_total) }}</div>
                  <div class="text-xs text-slate-400 font-medium">DPKAD: Rp {{ money(summary.orders.dpkad_total) }}</div>
                </div>
              </div>
              <div
                :class="[
                  'w-10 h-10 rounded-xl flex items-center justify-center text-sm font-bold',
                  Math.abs(summary.orders.diff_total) < 0.01 ? 'bg-emerald-50 text-emerald-600' : 'bg-rose-50 text-rose-600'
                ]"
              >
                <i class="pi pi-money-bill"></i>
              </div>
            </div>
            <div class="mt-3 pt-3 border-t border-slate-100 flex items-center justify-between text-xs">
              <span class="text-slate-500 font-medium">Selisih Nominal:</span>
              <span
                :class="[
                  'font-black',
                  Math.abs(summary.orders.diff_total) < 0.01 ? 'text-emerald-600' : 'text-rose-600'
                ]"
              >
                {{ summary.orders.diff_total > 0 ? '+' : '' }}Rp {{ money(summary.orders.diff_total) }}
              </span>
            </div>
            <div class="mt-1 flex items-center justify-between text-[11px] text-slate-400">
              <span>Subtotal Diff: Rp {{ money(summary.orders.diff_subtotal) }}</span>
            </div>
          </template>
        </Card>

        <!-- Card 4: Tax Amount -->
        <Card class="border border-slate-100 shadow-sm !rounded-2xl">
          <template #content>
            <div class="flex items-start justify-between">
              <div>
                <span class="text-xs font-bold text-slate-400 uppercase tracking-wider">Pajak PB1 (Tax Amount)</span>
                <div class="mt-2">
                  <div class="text-lg font-black text-slate-800">Rp {{ money(summary.orders.restoku_tax) }}</div>
                  <div class="text-xs text-slate-400 font-medium">DPKAD: Rp {{ money(summary.orders.dpkad_tax) }}</div>
                </div>
              </div>
              <div
                :class="[
                  'w-10 h-10 rounded-xl flex items-center justify-center text-sm font-bold',
                  Math.abs(summary.orders.diff_tax) < 0.01 ? 'bg-emerald-50 text-emerald-600' : 'bg-rose-50 text-rose-600'
                ]"
              >
                <i class="pi pi-percentage"></i>
              </div>
            </div>
            <div class="mt-3 pt-3 border-t border-slate-100 flex items-center justify-between text-xs">
              <span class="text-slate-500 font-medium">Selisih Pajak:</span>
              <span
                :class="[
                  'font-black',
                  Math.abs(summary.orders.diff_tax) < 0.01 ? 'text-emerald-600' : 'text-rose-600'
                ]"
              >
                {{ summary.orders.diff_tax > 0 ? '+' : '' }}Rp {{ money(summary.orders.diff_tax) }}
              </span>
            </div>
            <div class="mt-1 flex items-center justify-between text-[11px] text-slate-400">
              <span>Service Diff: Rp {{ money(summary.orders.diff_service) }}</span>
            </div>
          </template>
        </Card>
      </div>

      <!-- Main Comparison Data Table -->
      <Card class="border border-slate-100 shadow-sm !rounded-2xl overflow-hidden">
        <template #title>
          <div class="flex flex-wrap items-center justify-between gap-3 px-2">
            <div>
              <span class="text-slate-800 font-black tracking-tight text-lg">Rincian Perbandingan Transaksi</span>
              <p class="text-xs text-slate-400 font-normal">
                Perbandingan data per transaksi antara restoku.orders & order_items vs resto_dpkad
              </p>
            </div>

            <!-- Tab Filter Chips -->
            <div class="flex items-center gap-1.5 bg-slate-100/70 p-1 rounded-xl">
              <button
                type="button"
                @click="activeFilter = 'all'"
                :class="[
                  'px-3 py-1.5 rounded-lg text-xs font-bold transition-all',
                  activeFilter === 'all'
                    ? 'bg-white text-slate-800 shadow-xs'
                    : 'text-slate-500 hover:text-slate-800'
                ]"
              >
                Semua ({{ orders.length }})
              </button>
              <button
                type="button"
                @click="activeFilter = 'issue'"
                :class="[
                  'px-3 py-1.5 rounded-lg text-xs font-bold transition-all',
                  activeFilter === 'issue'
                    ? 'bg-rose-500 text-white shadow-xs'
                    : 'text-slate-500 hover:text-rose-600'
                ]"
              >
                Hanya Selisih ({{ mismatchedOrMissingCount }})
              </button>
              <button
                type="button"
                @click="activeFilter = 'match'"
                :class="[
                  'px-3 py-1.5 rounded-lg text-xs font-bold transition-all',
                  activeFilter === 'match'
                    ? 'bg-emerald-600 text-white shadow-xs'
                    : 'text-slate-500 hover:text-emerald-700'
                ]"
              >
                Cocok ({{ matchedCount }})
              </button>
            </div>
          </div>
        </template>

        <template #content>
          <AppDataTable
            :value="filteredOrders"
            :loading="loading"
            class="app-table"
            stripedRows
            responsiveLayout="scroll"
            :paginator="true"
            :rows="15"
            :rowsPerPageOptions="[15, 25, 50, 100]"
          >
            <template #empty>
              <div class="text-center py-12">
                <i class="pi pi-check-circle text-4xl text-emerald-300 mb-3 block"></i>
                <div class="text-slate-500 font-medium">Tidak ada data transaksi yang cocok dengan filter.</div>
              </div>
            </template>

            <!-- Column 1: Order Info -->
            <Column header="Nomor Order & Info" class="min-w-[200px]">
              <template #body="{ data }">
                <div class="flex flex-col">
                  <span class="font-bold text-slate-800 text-sm">{{ data.order_number }}</span>
                  <div class="flex items-center gap-1.5 text-xs text-slate-500">
                    <i class="pi pi-user text-[10px]"></i>
                    <span>{{ data.customer_name || 'Pelanggan Umum' }}</span>
                  </div>
                  <span class="text-[11px] text-slate-400 mt-0.5">
                    {{ data.date ? formatDateFull(data.date) : '-' }}
                  </span>
                </div>
              </template>
            </Column>

            <!-- Column 2: Restoku DB -->
            <Column header="Database Restoku" class="min-w-[190px]">
              <template #body="{ data }">
                <div v-if="data.restoku" class="flex flex-col text-xs">
                  <div class="font-bold text-slate-800 text-sm">
                    Rp {{ money(data.restoku.total_amount) }}
                  </div>
                  <div class="text-slate-500 text-[11px]">
                    Subtotal: Rp {{ money(data.restoku.subtotal) }}
                  </div>
                  <div class="text-slate-400 text-[11px]">
                    Pajak: Rp {{ money(data.restoku.tax_amount) }} | Svc: Rp {{ money(data.restoku.service_charge) }}
                  </div>
                  <div class="text-slate-500 text-[11px] mt-0.5 font-medium">
                    {{ data.restoku.items_count }} item ({{ data.restoku.items_qty }} pcs)
                  </div>
                </div>
                <div v-else class="text-xs text-slate-400 italic">
                  Tidak ada di Restoku
                </div>
              </template>
            </Column>

            <!-- Column 3: DPKAD DB -->
            <Column header="Database DPKAD" class="min-w-[190px]">
              <template #body="{ data }">
                <div v-if="data.dpkad" class="flex flex-col text-xs">
                  <div class="font-bold text-slate-800 text-sm">
                    Rp {{ money(data.dpkad.total_amount) }}
                  </div>
                  <div class="text-slate-500 text-[11px]">
                    Subtotal: Rp {{ money(data.dpkad.subtotal) }}
                  </div>
                  <div class="text-slate-400 text-[11px]">
                    Pajak: Rp {{ money(data.dpkad.tax_amount) }} | Svc: Rp {{ money(data.dpkad.service_charge) }}
                  </div>
                  <div class="text-slate-500 text-[11px] mt-0.5 font-medium">
                    {{ data.dpkad.items_count }} item ({{ data.dpkad.items_qty }} pcs)
                  </div>
                </div>
                <div v-else class="text-xs text-rose-500 font-semibold flex items-center gap-1">
                  <i class="pi pi-times-circle text-xs"></i>
                  <span>Belum Terdata di DPKAD</span>
                </div>
              </template>
            </Column>

            <!-- Column 4: Difference -->
            <Column header="Selisih" class="min-w-[170px]">
              <template #body="{ data }">
                <div class="flex flex-col text-xs">
                  <div
                    :class="[
                      'font-black text-sm',
                      Math.abs(data.difference.total_amount) < 0.01 ? 'text-emerald-600' : 'text-rose-600'
                    ]"
                  >
                    {{ Math.abs(data.difference.total_amount) < 0.01 ? 'Rp 0' : `${data.difference.total_amount > 0 ? '+' : ''}Rp ${money(data.difference.total_amount)}` }}
                  </div>
                  <div
                    v-if="Math.abs(data.difference.tax_amount) >= 0.01"
                    class="text-[11px] text-rose-600"
                  >
                    Selisih Pajak: Rp {{ money(data.difference.tax_amount) }}
                  </div>
                  <div
                    v-if="data.difference.items_count !== 0 || data.difference.items_qty !== 0"
                    class="text-[11px] text-amber-700 font-medium"
                  >
                    Selisih Item: {{ data.difference.items_count }} baris ({{ data.difference.items_qty }} pcs)
                  </div>
                  <div
                    v-if="Math.abs(data.difference.total_amount) < 0.01 && data.difference.items_count === 0 && data.difference.items_qty === 0"
                    class="text-[11px] text-emerald-600 font-medium"
                  >
                    Selisih Rp 0 / 0 item
                  </div>
                </div>
              </template>
            </Column>

            <!-- Column 5: Status -->
            <Column header="Status" class="min-w-[130px]">
              <template #body="{ data }">
                <span
                  v-if="data.status === 'MATCH'"
                  class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-[11px] font-bold bg-emerald-50 text-emerald-700 border border-emerald-200"
                >
                  <i class="pi pi-check text-[10px]"></i>
                  Cocok
                </span>
                <span
                  v-else-if="data.status === 'MISMATCH'"
                  class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-[11px] font-bold bg-amber-50 text-amber-700 border border-amber-200"
                >
                  <i class="pi pi-exclamation-triangle text-[10px]"></i>
                  Beda Nominal/Item
                </span>
                <span
                  v-else-if="data.status === 'NOT_IN_DPKAD'"
                  class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-[11px] font-bold bg-rose-50 text-rose-700 border border-rose-200"
                >
                  <i class="pi pi-clock text-[10px]"></i>
                  Belum di DPKAD
                </span>
                <span
                  v-else-if="data.status === 'NOT_IN_RESTOKU'"
                  class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-[11px] font-bold bg-purple-50 text-purple-700 border border-purple-200"
                >
                  <i class="pi pi-question-circle text-[10px]"></i>
                  Hanya di DPKAD
                </span>
              </template>
            </Column>

            <!-- Column 6: Action -->
            <Column header="Aksi" class="text-right min-w-[130px]">
              <template #body="{ data }">
                <div class="flex items-center justify-end gap-1.5">
                  <Button
                    v-if="data.can_sync && data.id"
                    label="Sinkronkan"
                    icon="pi pi-sync"
                    size="small"
                    severity="warning"
                    outlined
                    class="!text-xs !py-1 !px-2.5"
                    :loading="syncingId === data.id"
                    @click="syncSingle(data)"
                  />
                  <Button
                    icon="pi pi-eye"
                    size="small"
                    text
                    severity="secondary"
                    class="!w-8 !h-8"
                    @click="openDetail(data)"
                    v-tooltip.top="'Lihat Rincian'"
                  />
                </div>
              </template>
            </Column>
          </AppDataTable>
        </template>
      </Card>

      <!-- Detail Dialog Modal -->
      <Dialog
        v-model:visible="detailVisible"
        modal
        header="Detail Rekonsiliasi Transaksi"
        :style="{ width: '560px' }"
        class="p-fluid"
      >
        <div v-if="selectedOrder" class="space-y-4 pt-2">
          <div class="bg-slate-50 p-3.5 rounded-xl border border-slate-100 flex items-center justify-between">
            <div>
              <div class="text-xs font-bold text-slate-400 uppercase">Nomor Order</div>
              <div class="text-base font-black text-slate-800">{{ selectedOrder.order_number }}</div>
              <div class="text-xs text-slate-500">{{ selectedOrder.customer_name || 'Pelanggan Umum' }}</div>
            </div>
            <div class="text-right">
              <div class="text-xs font-bold text-slate-400 uppercase">Waktu Transaksi</div>
              <div class="text-xs font-medium text-slate-700">{{ formatDateFull(selectedOrder.date) }}</div>
            </div>
          </div>

          <!-- Comparison Grid -->
          <div class="border border-slate-200 rounded-xl overflow-hidden">
            <table class="w-full text-xs text-left">
              <thead class="bg-slate-100 text-slate-600 font-bold uppercase text-[10px]">
                <tr>
                  <th class="p-2.5">Parameter</th>
                  <th class="p-2.5 text-right">Restoku</th>
                  <th class="p-2.5 text-right">resto_dpkad</th>
                  <th class="p-2.5 text-right">Selisih</th>
                </tr>
              </thead>
              <tbody class="divide-y divide-slate-100">
                <tr>
                  <td class="p-2.5 font-medium text-slate-600">Subtotal</td>
                  <td class="p-2.5 text-right font-semibold">Rp {{ money(selectedOrder.restoku?.subtotal) }}</td>
                  <td class="p-2.5 text-right font-semibold">Rp {{ money(selectedOrder.dpkad?.subtotal) }}</td>
                  <td class="p-2.5 text-right font-bold" :class="Math.abs(selectedOrder.difference.subtotal) > 0.01 ? 'text-rose-600' : 'text-emerald-600'">
                    Rp {{ money(selectedOrder.difference.subtotal) }}
                  </td>
                </tr>
                <tr>
                  <td class="p-2.5 font-medium text-slate-600">Pajak (Tax PB1)</td>
                  <td class="p-2.5 text-right font-semibold">Rp {{ money(selectedOrder.restoku?.tax_amount) }}</td>
                  <td class="p-2.5 text-right font-semibold">Rp {{ money(selectedOrder.dpkad?.tax_amount) }}</td>
                  <td class="p-2.5 text-right font-bold" :class="Math.abs(selectedOrder.difference.tax_amount) > 0.01 ? 'text-rose-600' : 'text-emerald-600'">
                    Rp {{ money(selectedOrder.difference.tax_amount) }}
                  </td>
                </tr>
                <tr>
                  <td class="p-2.5 font-medium text-slate-600">Service Charge</td>
                  <td class="p-2.5 text-right font-semibold">Rp {{ money(selectedOrder.restoku?.service_charge) }}</td>
                  <td class="p-2.5 text-right font-semibold">Rp {{ money(selectedOrder.dpkad?.service_charge) }}</td>
                  <td class="p-2.5 text-right font-bold" :class="Math.abs(selectedOrder.difference.service_charge) > 0.01 ? 'text-rose-600' : 'text-emerald-600'">
                    Rp {{ money(selectedOrder.difference.service_charge) }}
                  </td>
                </tr>
                <tr class="bg-slate-50/70 font-bold">
                  <td class="p-2.5 text-slate-800">Grand Total</td>
                  <td class="p-2.5 text-right text-slate-900">Rp {{ money(selectedOrder.restoku?.total_amount) }}</td>
                  <td class="p-2.5 text-right text-slate-900">Rp {{ money(selectedOrder.dpkad?.total_amount) }}</td>
                  <td class="p-2.5 text-right" :class="Math.abs(selectedOrder.difference.total_amount) > 0.01 ? 'text-rose-600' : 'text-emerald-600'">
                    Rp {{ money(selectedOrder.difference.total_amount) }}
                  </td>
                </tr>
                <tr>
                  <td class="p-2.5 font-medium text-slate-600">Jumlah Baris Item</td>
                  <td class="p-2.5 text-right">{{ selectedOrder.restoku?.items_count ?? 0 }} item</td>
                  <td class="p-2.5 text-right">{{ selectedOrder.dpkad?.items_count ?? 0 }} item</td>
                  <td class="p-2.5 text-right font-bold" :class="selectedOrder.difference.items_count !== 0 ? 'text-rose-600' : 'text-emerald-600'">
                    {{ selectedOrder.difference.items_count }}
                  </td>
                </tr>
                <tr>
                  <td class="p-2.5 font-medium text-slate-600">Total Kuantitas (Qty)</td>
                  <td class="p-2.5 text-right">{{ selectedOrder.restoku?.items_qty ?? 0 }} pcs</td>
                  <td class="p-2.5 text-right">{{ selectedOrder.dpkad?.items_qty ?? 0 }} pcs</td>
                  <td class="p-2.5 text-right font-bold" :class="selectedOrder.difference.items_qty !== 0 ? 'text-rose-600' : 'text-emerald-600'">
                    {{ selectedOrder.difference.items_qty }}
                  </td>
                </tr>
              </tbody>
            </table>
          </div>
        </div>

        <template #footer>
          <div class="flex justify-between items-center w-full">
            <Button
              v-if="selectedOrder?.can_sync && selectedOrder?.id"
              label="Sinkronkan Sekarang"
              icon="pi pi-sync"
              severity="warning"
              :loading="syncingId === selectedOrder.id"
              @click="syncSingle(selectedOrder); detailVisible = false;"
            />
            <div v-else></div>
            <Button
              label="Tutup"
              severity="secondary"
              outlined
              @click="detailVisible = false"
            />
          </div>
        </template>
      </Dialog>
    </div>
  </AppPage>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue';
import AppPage from '@/components/layout/AppPage.vue';
import AppDataTable from '@/components/AppDataTable.vue';
import Card from 'primevue/card';
import Button from 'primevue/button';
import DatePicker from 'primevue/datepicker';
import Column from 'primevue/column';
import Dialog from 'primevue/dialog';
import { useToast } from 'primevue/usetoast';
import { reportsApi } from '@/api/reports';

const toast = useToast();

const loading = ref(false);
const syncingAll = ref(false);
const syncingId = ref(null);
const activeFilter = ref('all');

const detailVisible = ref(false);
const selectedOrder = ref(null);

const dates = ref([
  new Date(new Date().getFullYear(), new Date().getMonth(), 1),
  new Date(),
]);

const summary = ref(null);
const orders = ref([]);
const filters = ref({
  start_date: null,
  end_date: null,
});

const matchedCount = computed(() => {
  return orders.value.filter((o) => o.status === 'MATCH').length;
});

const mismatchedOrMissingCount = computed(() => {
  return orders.value.filter((o) => o.status !== 'MATCH').length;
});

const syncableCount = computed(() => {
  return orders.value.filter((o) => o.can_sync && o.id).length;
});

const filteredOrders = computed(() => {
  if (activeFilter.value === 'issue') {
    return orders.value.filter((o) => o.status !== 'MATCH');
  }
  if (activeFilter.value === 'match') {
    return orders.value.filter((o) => o.status === 'MATCH');
  }
  return orders.value;
});

function money(value) {
  return Number(value || 0).toLocaleString('id-ID');
}

function formatDateId(dateStr) {
  if (!dateStr) return '-';
  const date = new Date(dateStr);
  return date.toLocaleDateString('id-ID', {
    day: '2-digit',
    month: 'short',
    year: 'numeric',
  });
}

function formatDateFull(dateStr) {
  if (!dateStr) return '-';
  const date = new Date(dateStr);
  return date.toLocaleDateString('id-ID', {
    day: '2-digit',
    month: 'short',
    year: 'numeric',
    hour: '2-digit',
    minute: '2-digit',
  });
}

function formatDatePayload(date) {
  if (!date) return null;
  const d = new Date(date);
  let month = '' + (d.getMonth() + 1);
  let day = '' + d.getDate();
  const year = d.getFullYear();
  if (month.length < 2) month = '0' + month;
  if (day.length < 2) day = '0' + day;
  return [year, month, day].join('-');
}

function setToday() {
  const today = new Date();
  dates.value = [today, today];
  loadReconciliation();
}

function setThisMonth() {
  const start = new Date(new Date().getFullYear(), new Date().getMonth(), 1);
  const end = new Date();
  dates.value = [start, end];
  loadReconciliation();
}

async function loadReconciliation() {
  if (!dates.value || !dates.value[0] || !dates.value[1]) {
    toast.add({
      severity: 'warn',
      summary: 'Peringatan',
      detail: 'Silakan pilih rentang tanggal.',
      life: 3000,
    });
    return;
  }

  loading.value = true;
  try {
    const params = {
      start_date: formatDatePayload(dates.value[0]),
      end_date: formatDatePayload(dates.value[1]),
    };

    const res = await reportsApi.getReconciliation(params);
    const data = res.data?.data || {};

    summary.value = data.summary || null;
    orders.value = data.orders || [];
    filters.value = data.filters || {
      start_date: params.start_date,
      end_date: params.end_date,
    };
  } catch (error) {
    console.error('Error fetching reconciliation:', error);
    toast.add({
      severity: 'error',
      summary: 'Gagal',
      detail: error.response?.data?.message || 'Gagal memuat data rekonsiliasi.',
      life: 4000,
    });
  } finally {
    loading.value = false;
  }
}

async function syncSingle(order) {
  if (!order || !order.id) return;

  syncingId.value = order.id;
  try {
    const res = await reportsApi.syncReconciliation({
      order_ids: [order.id],
    });

    toast.add({
      severity: 'success',
      summary: 'Berhasil',
      detail: res.data?.message || `Order ${order.order_number} berhasil disinkronkan ke resto_dpkad.`,
      life: 3000,
    });

    await loadReconciliation();
  } catch (error) {
    console.error('Error syncing order:', error);
    toast.add({
      severity: 'error',
      summary: 'Gagal Sinkronisasi',
      detail: error.response?.data?.message || 'Terjadi kesalahan saat menyinkronkan data.',
      life: 4000,
    });
  } finally {
    syncingId.value = null;
  }
}

async function confirmSyncAll() {
  const syncableIds = orders.value
    .filter((o) => o.can_sync && o.id)
    .map((o) => o.id);

  if (syncableIds.length === 0) {
    toast.add({
      severity: 'info',
      summary: 'Info',
      detail: 'Tidak ada order yang perlu disinkronkan.',
      life: 3000,
    });
    return;
  }

  syncingAll.value = true;
  try {
    const res = await reportsApi.syncReconciliation({
      order_ids: syncableIds,
    });

    toast.add({
      severity: 'success',
      summary: 'Sinkronisasi Selesai',
      detail: res.data?.message || `${syncableIds.length} order berhasil disinkronkan ke resto_dpkad.`,
      life: 4000,
    });

    await loadReconciliation();
  } catch (error) {
    console.error('Error syncing all orders:', error);
    toast.add({
      severity: 'error',
      summary: 'Gagal Sinkronisasi',
      detail: error.response?.data?.message || 'Terjadi kesalahan saat menyinkronkan data.',
      life: 4000,
    });
  } finally {
    syncingAll.value = false;
  }
}

function openDetail(order) {
  selectedOrder.value = order;
  detailVisible.value = true;
}

onMounted(() => {
  loadReconciliation();
});
</script>

