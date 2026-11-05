<template>
    <AppPage title="Table Management" :breadcrumb="['Operations', 'Tables']" no-card>

        <!-- ── Top Control Bar ─────────────────────────────────────────── -->
        <div class="flex flex-wrap items-center justify-between gap-3 bg-white/80 backdrop-blur-md p-4 rounded-2xl border border-slate-100 shadow-sm mb-6 sticky top-0 z-30">
            <div class="flex flex-wrap items-center gap-3">
                <!-- Branch -->
                <div class="flex items-center gap-2">
                    <i class="pi pi-map-marker text-emerald-500 text-sm"></i>
                    <Select v-model="selectedBranch" :options="branchOptions" optionLabel="label" optionValue="value"
                        class="w-44 !rounded-xl" pt:input:class="!py-2 !text-xs" />
                </div>

                <!-- Floor -->
                <div class="flex items-center gap-2 bg-slate-50 rounded-xl p-1">
                    <button v-for="floor in floors" :key="floor.id" @click="selectedFloor = floor.id"
                        class="px-3 py-1.5 text-xs font-bold rounded-lg transition-all duration-200"
                        :class="selectedFloor === floor.id
                            ? 'bg-emerald-600 text-white shadow-sm'
                            : 'text-slate-500 hover:text-slate-800'">
                        {{ floor.label }}
                    </button>
                </div>

                <!-- Area filter -->
                <div class="flex items-center gap-1.5 bg-slate-50 rounded-xl p-1">
                    <button v-for="area in areas" :key="area.id" @click="selectedArea = area.id"
                        class="px-3 py-1.5 text-xs font-bold rounded-lg transition-all"
                        :class="selectedArea === area.id
                            ? 'bg-white text-emerald-700 shadow-sm border border-emerald-100'
                            : 'text-slate-500 hover:text-slate-700'">
                        {{ area.label }}
                    </button>
                </div>
            </div>

            <div class="flex items-center gap-3">
                <!-- Search -->
                <div class="relative">
                    <i class="pi pi-search absolute left-3 top-1/2 -translate-y-1/2 text-slate-400 text-xs"></i>
                    <input v-model="searchQuery" type="text" placeholder="Cari meja atau tamu..."
                        class="pl-8 pr-4 py-2 text-xs border border-slate-200 rounded-xl bg-white focus:outline-none focus:ring-2 focus:ring-emerald-300 w-52" />
                </div>

                <!-- Occupancy badge -->
                <div class="flex items-center gap-2 bg-emerald-50 border border-emerald-100 rounded-xl px-3 py-2">
                    <div class="w-2 h-2 rounded-full bg-emerald-500 animate-pulse"></div>
                    <span class="text-xs font-black text-emerald-700">{{ occupancyRate }}% Terisi</span>
                </div>

                <!-- Add Reservation -->
                <Button label="Reservasi" icon="pi pi-plus" class="!rounded-xl !text-xs !py-2 !bg-emerald-600 !border-emerald-600 shadow-sm" @click="showReservationModal = true" />
            </div>
        </div>

        <!-- ── KPI Row ──────────────────────────────────────────────────── -->
        <div class="grid grid-cols-2 md:grid-cols-4 xl:grid-cols-8 gap-3 mb-6">
            <div v-for="kpi in kpiCards" :key="kpi.key"
                 class="bg-white rounded-2xl p-3 border border-slate-100 shadow-sm hover:-translate-y-0.5 transition-transform duration-200 cursor-default">
                <div class="flex justify-between items-start mb-2">
                    <p class="text-[9px] font-black text-slate-400 uppercase tracking-wider leading-tight">{{ kpi.title }}</p>
                    <div :class="kpi.iconBg" class="w-6 h-6 rounded-lg flex items-center justify-center flex-shrink-0">
                        <i :class="kpi.icon" class="text-[10px]"></i>
                    </div>
                </div>
                <h3 class="text-xl font-black" :class="kpi.valueColor">{{ kpi.value }}</h3>
                <p class="text-[9px] font-semibold mt-0.5" :class="kpi.subColor">{{ kpi.sub }}</p>
            </div>
        </div>

        <!-- ── Main Grid ────────────────────────────────────────────────── -->
        <div class="grid grid-cols-1 xl:grid-cols-12 gap-5">

            <!-- ── LEFT: Floor Plan + Reservations + Waiting List ───────── -->
            <div class="xl:col-span-8 space-y-5">

                <!-- Floor Plan Card -->
                <div class="bg-white rounded-2xl border border-slate-100 shadow-sm overflow-hidden">
                    <!-- Floor Plan Header -->
                    <div class="flex items-center justify-between px-5 py-4 border-b border-slate-50">
                        <div class="flex items-center gap-3">
                            <div class="w-8 h-8 bg-emerald-50 rounded-xl flex items-center justify-center">
                                <i class="pi pi-map text-emerald-600 text-sm"></i>
                            </div>
                            <div>
                                <h3 class="font-black text-slate-800 text-sm">Floor Plan Interaktif</h3>
                                <p class="text-[10px] text-slate-400 font-medium">Klik meja untuk melihat detail & aksi cepat</p>
                            </div>
                        </div>
                        <!-- Legend -->
                        <div class="flex items-center gap-3 flex-wrap justify-end">
                            <div v-for="leg in statusLegend" :key="leg.key" class="flex items-center gap-1.5">
                                <span class="w-2.5 h-2.5 rounded-full" :class="leg.dot"></span>
                                <span class="text-[10px] text-slate-500 font-medium">{{ leg.label }}</span>
                            </div>
                        </div>
                    </div>

                    <!-- Floor Plan Canvas -->
                    <div class="p-5 space-y-6">
                        <div v-for="zone in filteredZones" :key="zone.id">
                            <!-- Zone Label -->
                            <div class="flex items-center gap-2 mb-3">
                                <span class="text-[10px] font-black text-slate-400 uppercase tracking-widest">{{ zone.name }}</span>
                                <div class="flex-1 h-px bg-slate-100"></div>
                                <span class="text-[10px] font-bold text-slate-400">{{ zone.tables.length }} meja</span>
                            </div>

                            <!-- Table Grid -->
                            <div class="flex flex-wrap gap-3">
                                <button
                                    v-for="table in zone.tables.filter(t => filteredTables.includes(t))"
                                    :key="table.id"
                                    @click="selectTable(table)"
                                    class="relative group flex flex-col items-center justify-center rounded-2xl border-2 transition-all duration-200 hover:scale-105 hover:shadow-md focus:outline-none focus:ring-2 focus:ring-emerald-400"
                                    :class="[
                                        tableSize(table),
                                        tableStatusClass(table.status),
                                        selectedTable?.id === table.id ? 'ring-2 ring-emerald-500 ring-offset-2 scale-105 shadow-md' : ''
                                    ]">

                                    <!-- Merge badge -->
                                    <div v-if="table.mergedWith" class="absolute -top-1.5 -right-1.5 bg-violet-600 text-white text-[8px] font-black rounded-full w-4 h-4 flex items-center justify-center">M</div>

                                    <!-- VIP badge -->
                                    <div v-if="table.vip" class="absolute -top-1.5 -left-1.5 bg-amber-500 text-white text-[8px] font-black rounded-full w-4 h-4 flex items-center justify-center">V</div>

                                    <!-- Table shape indicator -->
                                    <div class="text-[10px] font-black opacity-60 mb-0.5">
                                        <i :class="tableShapeIcon(table.shape)" class="text-base"></i>
                                    </div>

                                    <p class="text-xs font-black">{{ table.number }}</p>
                                    <p class="text-[9px] font-semibold opacity-70">{{ table.capacity }} kursi</p>

                                    <!-- Occupied: show timer -->
                                    <div v-if="table.status === 'occupied'" class="text-[8px] font-black mt-0.5 opacity-80">
                                        {{ table.elapsed }}
                                    </div>

                                    <!-- Occupied: mini bill -->
                                    <div v-if="table.status === 'occupied' && table.bill" class="text-[8px] font-bold mt-0.5 opacity-70">
                                        {{ formatCurrency(table.bill) }}
                                    </div>

                                    <!-- Status dot -->
                                    <div class="absolute bottom-1.5 right-1.5 w-1.5 h-1.5 rounded-full" :class="statusDot(table.status)"></div>
                                </button>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Reservations & Waiting List Row -->
                <div class="grid grid-cols-1 md:grid-cols-2 gap-5">

                    <!-- Upcoming Reservations -->
                    <div class="bg-white rounded-2xl border border-slate-100 shadow-sm overflow-hidden">
                        <div class="flex items-center justify-between px-5 py-4 border-b border-slate-50">
                            <div class="flex items-center gap-2">
                                <div class="w-7 h-7 bg-orange-50 rounded-xl flex items-center justify-center">
                                    <i class="pi pi-calendar text-orange-500 text-xs"></i>
                                </div>
                                <span class="font-black text-slate-800 text-sm">Reservasi Hari Ini</span>
                                <Tag :value="`${reservations.length}`" severity="warn" class="text-[10px] rounded-full ml-1" />
                            </div>
                            <Button label="Baru" icon="pi pi-plus" size="small" text class="!text-[10px] !text-emerald-600 font-bold" @click="showReservationModal = true" />
                        </div>
                        <div class="divide-y divide-slate-50 max-h-64 overflow-y-auto">
                            <div v-for="res in reservations" :key="res.id"
                                 class="flex items-start gap-3 px-4 py-3 hover:bg-slate-50/60 transition-colors cursor-pointer"
                                 @click="viewReservation(res)">
                                <div class="w-8 h-8 rounded-full bg-gradient-to-br from-blue-400 to-indigo-500 flex items-center justify-center text-white font-black text-xs flex-shrink-0">
                                    {{ res.name[0] }}
                                </div>
                                <div class="flex-1 min-w-0">
                                    <div class="flex justify-between items-start">
                                        <p class="font-bold text-slate-800 text-xs truncate">{{ res.name }}</p>
                                        <span class="text-[10px] font-black ml-2 flex-shrink-0 px-2 py-0.5 rounded-full"
                                              :class="reservationStatusClass(res.status)">{{ res.status }}</span>
                                    </div>
                                    <div class="flex items-center gap-3 mt-0.5">
                                        <span class="text-[10px] text-slate-400 flex items-center gap-1"><i class="pi pi-clock text-[9px]"></i> {{ res.time }}</span>
                                        <span class="text-[10px] text-slate-400 flex items-center gap-1"><i class="pi pi-users text-[9px]"></i> {{ res.guests }} tamu</span>
                                        <span class="text-[10px] text-slate-400">{{ res.area }}</span>
                                    </div>
                                    <p v-if="res.note" class="text-[9px] text-slate-400 italic mt-0.5 truncate">{{ res.note }}</p>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Waiting List -->
                    <div class="bg-white rounded-2xl border border-slate-100 shadow-sm overflow-hidden">
                        <div class="flex items-center justify-between px-5 py-4 border-b border-slate-50">
                            <div class="flex items-center gap-2">
                                <div class="w-7 h-7 bg-rose-50 rounded-xl flex items-center justify-center">
                                    <i class="pi pi-list text-rose-500 text-xs"></i>
                                </div>
                                <span class="font-black text-slate-800 text-sm">Waiting List</span>
                                <Tag :value="`${waitingList.length}`" severity="danger" class="text-[10px] rounded-full ml-1" />
                            </div>
                            <Button label="Tambah" icon="pi pi-plus" size="small" text class="!text-[10px] !text-emerald-600 font-bold" />
                        </div>
                        <div class="divide-y divide-slate-50 max-h-64 overflow-y-auto">
                            <div v-for="queue in waitingList" :key="queue.id"
                                 class="flex items-center gap-3 px-4 py-3 hover:bg-slate-50/60 transition-colors">
                                <div class="w-8 h-8 rounded-xl flex items-center justify-center text-white font-black text-xs flex-shrink-0"
                                     :class="queue.priority === 'VIP' ? 'bg-amber-500' : 'bg-slate-400'">
                                    #{{ queue.queue }}
                                </div>
                                <div class="flex-1 min-w-0">
                                    <div class="flex justify-between items-center">
                                        <p class="font-bold text-slate-800 text-xs">{{ queue.name }}</p>
                                        <Tag v-if="queue.priority === 'VIP'" value="VIP" severity="warn" class="text-[9px] rounded-full" />
                                    </div>
                                    <div class="flex items-center gap-3 mt-0.5">
                                        <span class="text-[10px] text-slate-400 flex items-center gap-1"><i class="pi pi-users text-[9px]"></i> {{ queue.guests }}</span>
                                        <span class="text-[10px] text-rose-500 font-bold flex items-center gap-1"><i class="pi pi-clock text-[9px]"></i> ~{{ queue.waitMins }} mnt</span>
                                        <span class="text-[10px] text-slate-400">{{ queue.area }}</span>
                                    </div>
                                </div>
                                <Button icon="pi pi-arrow-right" size="small" text rounded severity="success" class="flex-shrink-0" @click="assignWaiting(queue)" />
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Occupancy Analytics -->
                <div class="bg-white rounded-2xl border border-slate-100 shadow-sm overflow-hidden">
                    <div class="flex items-center justify-between px-5 py-4 border-b border-slate-50">
                        <div class="flex items-center gap-2">
                            <div class="w-7 h-7 bg-indigo-50 rounded-xl flex items-center justify-center">
                                <i class="pi pi-chart-line text-indigo-500 text-xs"></i>
                            </div>
                            <span class="font-black text-slate-800 text-sm">Analitik Okupansi</span>
                        </div>
                        <div class="flex items-center gap-1 bg-slate-50 rounded-xl p-1">
                            <button v-for="m in analyticsMetrics" :key="m.id" @click="selectedMetric = m.id"
                                class="px-3 py-1 text-[10px] font-bold rounded-lg transition-all"
                                :class="selectedMetric === m.id ? 'bg-white text-indigo-700 shadow-sm' : 'text-slate-400'">
                                {{ m.label }}
                            </button>
                        </div>
                    </div>
                    <div class="p-5">
                        <Chart type="bar" :data="analyticsChartData" :options="analyticsChartOptions" class="h-[180px]" />
                    </div>
                </div>
            </div>

            <!-- ── RIGHT: Table Detail + Quick Actions ─────────────────── -->
            <div class="xl:col-span-4 space-y-4">

                <!-- Table Detail Panel -->
                <div v-if="selectedTable" class="bg-white rounded-2xl border border-slate-100 shadow-sm overflow-hidden">
                    <!-- Table header bar with status color -->
                    <div class="p-5 text-white relative overflow-hidden" :class="tableDetailHeaderBg">
                        <div class="relative z-10">
                            <div class="flex justify-between items-start">
                                <div>
                                    <p class="text-[10px] font-black uppercase tracking-widest opacity-70">Meja Terpilih</p>
                                    <h2 class="text-2xl font-black mt-0.5">{{ selectedTable.number }}</h2>
                                </div>
                                <div class="text-right">
                                    <div class="bg-white/20 backdrop-blur-sm rounded-xl px-3 py-1.5">
                                        <p class="text-[10px] font-black uppercase">{{ statusLabel(selectedTable.status) }}</p>
                                    </div>
                                    <p class="text-[10px] mt-1 opacity-70">{{ selectedTable.area }} · {{ selectedTable.capacity }} kursi</p>
                                </div>
                            </div>

                            <!-- Timer if occupied -->
                            <div v-if="selectedTable.status === 'occupied'" class="mt-3 flex items-center gap-4">
                                <div class="text-center">
                                    <p class="text-2xl font-black">{{ selectedTable.elapsed }}</p>
                                    <p class="text-[9px] opacity-70 uppercase tracking-wider">Durasi Makan</p>
                                </div>
                                <div class="text-center">
                                    <p class="text-xl font-black">{{ formatCurrency(selectedTable.bill) }}</p>
                                    <p class="text-[9px] opacity-70 uppercase tracking-wider">Tagihan</p>
                                </div>
                            </div>
                        </div>
                        <div class="absolute -right-8 -bottom-8 w-32 h-32 bg-white/10 rounded-full blur-xl"></div>
                    </div>

                    <!-- Guest & Staff Info -->
                    <div v-if="selectedTable.status === 'occupied'" class="px-5 py-4 border-b border-slate-50">
                        <div class="grid grid-cols-2 gap-3 text-xs">
                            <div>
                                <p class="text-[9px] font-black text-slate-400 uppercase tracking-wider mb-1">Tamu</p>
                                <p class="font-bold text-slate-800">{{ selectedTable.customer || 'Walk-in' }}</p>
                            </div>
                            <div>
                                <p class="text-[9px] font-black text-slate-400 uppercase tracking-wider mb-1">Waiter</p>
                                <p class="font-bold text-slate-800">{{ selectedTable.waiter || '—' }}</p>
                            </div>
                            <div>
                                <p class="text-[9px] font-black text-slate-400 uppercase tracking-wider mb-1">Check In</p>
                                <p class="font-bold text-slate-800">{{ selectedTable.checkIn || '—' }}</p>
                            </div>
                            <div>
                                <p class="text-[9px] font-black text-slate-400 uppercase tracking-wider mb-1">Tamu Hadir</p>
                                <p class="font-bold text-slate-800">{{ selectedTable.guests || '—' }} orang</p>
                            </div>
                        </div>
                    </div>

                    <!-- Order Summary -->
                    <div v-if="selectedTable.status === 'occupied' && selectedTable.orders" class="px-5 py-4 border-b border-slate-50">
                        <p class="text-[9px] font-black text-slate-400 uppercase tracking-wider mb-3">Ringkasan Pesanan</p>
                        <div class="space-y-2 max-h-36 overflow-y-auto">
                            <div v-for="item in selectedTable.orders" :key="item.name" class="flex justify-between items-center">
                                <div class="flex items-center gap-2">
                                    <span class="w-5 h-5 bg-slate-100 rounded-lg text-[10px] font-black text-slate-600 flex items-center justify-center">{{ item.qty }}</span>
                                    <span class="text-xs font-semibold text-slate-700">{{ item.name }}</span>
                                </div>
                                <span class="text-xs font-bold text-slate-800">{{ formatCurrency(item.price * item.qty) }}</span>
                            </div>
                        </div>
                        <!-- Bill Breakdown -->
                        <div class="mt-3 pt-3 border-t border-dashed border-slate-200 space-y-1.5">
                            <div class="flex justify-between text-[10px] text-slate-500">
                                <span>Subtotal</span>
                                <span>{{ formatCurrency(billSubtotal) }}</span>
                            </div>
                            <div class="flex justify-between text-[10px] text-slate-500">
                                <span>Pajak (10%)</span>
                                <span>{{ formatCurrency(billSubtotal * 0.1) }}</span>
                            </div>
                            <div class="flex justify-between text-[10px] text-slate-500">
                                <span>Service Charge (5%)</span>
                                <span>{{ formatCurrency(billSubtotal * 0.05) }}</span>
                            </div>
                            <div class="flex justify-between text-xs font-black text-emerald-700 pt-1 border-t border-slate-200">
                                <span>TOTAL</span>
                                <span>{{ formatCurrency(billSubtotal * 1.15) }}</span>
                            </div>
                        </div>
                    </div>

                    <!-- Customer Loyalty Badge -->
                    <div v-if="selectedTable.memberInfo" class="px-5 py-3 border-b border-slate-50 bg-amber-50/50">
                        <div class="flex items-center gap-3">
                            <div class="w-8 h-8 bg-amber-100 rounded-xl flex items-center justify-center">
                                <i class="pi pi-star-fill text-amber-500 text-xs"></i>
                            </div>
                            <div>
                                <p class="text-xs font-black text-amber-800">{{ selectedTable.memberInfo.tier }}</p>
                                <p class="text-[10px] text-amber-600 font-medium">{{ selectedTable.memberInfo.visits }} kunjungan · Avg {{ formatCurrency(selectedTable.memberInfo.avgSpend) }}</p>
                            </div>
                        </div>
                    </div>

                    <!-- Quick Action Buttons -->
                    <div class="p-4 grid grid-cols-2 gap-2">
                        <button v-for="action in quickActions" :key="action.label"
                            @click="handleAction(action.key)"
                            class="flex items-center gap-2 px-3 py-2.5 rounded-xl text-xs font-bold border transition-all duration-200 hover:shadow-sm hover:-translate-y-0.5 text-left"
                            :class="action.classes">
                            <i :class="action.icon" class="text-xs flex-shrink-0"></i>
                            <span class="leading-tight">{{ action.label }}</span>
                        </button>
                    </div>
                </div>

                <!-- No table selected state -->
                <div v-else class="bg-white rounded-2xl border border-slate-100 shadow-sm p-10 flex flex-col items-center justify-center text-center space-y-3">
                    <div class="w-16 h-16 bg-slate-50 rounded-2xl flex items-center justify-center">
                        <i class="pi pi-th-large text-slate-300 text-2xl"></i>
                    </div>
                    <p class="font-black text-slate-400 text-sm">Pilih Meja</p>
                    <p class="text-xs text-slate-300 font-medium">Klik meja pada floor plan untuk melihat detail dan melakukan aksi</p>
                </div>

                <!-- Table Timeline (if occupied) -->
                <div v-if="selectedTable?.status === 'occupied'" class="bg-white rounded-2xl border border-slate-100 shadow-sm overflow-hidden">
                    <div class="px-5 py-4 border-b border-slate-50">
                        <span class="font-black text-slate-800 text-sm">Timeline Meja</span>
                    </div>
                    <div class="px-5 py-4 space-y-3">
                        <div v-for="(event, idx) in tableTimeline" :key="event.label" class="flex items-start gap-3">
                            <div class="flex flex-col items-center">
                                <div class="w-6 h-6 rounded-full flex items-center justify-center text-[10px] font-black flex-shrink-0"
                                     :class="event.done ? 'bg-emerald-100 text-emerald-700' : 'bg-slate-100 text-slate-400'">
                                    <i :class="event.done ? 'pi pi-check' : 'pi pi-circle'" class="text-[9px]"></i>
                                </div>
                                <div v-if="idx < tableTimeline.length - 1" class="w-px flex-1 mt-1" :class="event.done ? 'bg-emerald-200' : 'bg-slate-100'" style="min-height:16px"></div>
                            </div>
                            <div class="pb-2">
                                <p class="text-xs font-bold" :class="event.done ? 'text-slate-800' : 'text-slate-400'">{{ event.label }}</p>
                                <p v-if="event.time" class="text-[10px] text-slate-400">{{ event.time }}</p>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Merge Tables Widget -->
                <div class="bg-white rounded-2xl border border-slate-100 shadow-sm overflow-hidden">
                    <div class="px-5 py-4 border-b border-slate-50 flex items-center justify-between">
                        <div class="flex items-center gap-2">
                            <div class="w-7 h-7 bg-violet-50 rounded-xl flex items-center justify-center">
                                <i class="pi pi-sitemap text-violet-500 text-xs"></i>
                            </div>
                            <span class="font-black text-slate-800 text-sm">Penggabungan Meja</span>
                        </div>
                        <Tag value="3 Aktif" severity="info" class="text-[10px] rounded-full" />
                    </div>
                    <div class="p-4 space-y-2">
                        <div v-for="merge in activeMerges" :key="merge.id"
                             class="flex items-center justify-between p-3 bg-violet-50/60 rounded-xl border border-violet-100">
                            <div>
                                <p class="text-xs font-black text-violet-800">{{ merge.tables.join(' + ') }}</p>
                                <p class="text-[10px] text-violet-500 font-medium">Total {{ merge.capacity }} kursi · {{ merge.customer }}</p>
                            </div>
                            <Button icon="pi pi-times" size="small" text rounded severity="danger" @click="splitMerge(merge.id)" />
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- ── Reservation Modal (simple inline panel) ─────────────────── -->
        <Dialog v-model:visible="showReservationModal" header="Buat Reservasi Baru" modal :style="{ width: '480px' }" :pt="{ header: { class: '!pb-3' } }">
            <div class="space-y-4 pt-2">
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="text-[10px] font-black text-slate-400 uppercase tracking-wider mb-1 block">Nama Tamu</label>
                        <InputText v-model="newReservation.name" placeholder="Nama lengkap" class="w-full !rounded-xl !text-sm" />
                    </div>
                    <div>
                        <label class="text-[10px] font-black text-slate-400 uppercase tracking-wider mb-1 block">No. Telepon</label>
                        <InputText v-model="newReservation.phone" placeholder="+62..." class="w-full !rounded-xl !text-sm" />
                    </div>
                </div>
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="text-[10px] font-black text-slate-400 uppercase tracking-wider mb-1 block">Jumlah Tamu</label>
                        <InputNumber v-model="newReservation.guests" :min="1" :max="50" class="w-full" inputClass="!rounded-xl !text-sm" />
                    </div>
                    <div>
                        <label class="text-[10px] font-black text-slate-400 uppercase tracking-wider mb-1 block">Jam Reservasi</label>
                        <DatePicker v-model="newReservation.time" timeOnly hourFormat="24" class="w-full" inputClass="!rounded-xl !text-sm" />
                    </div>
                </div>
                <div>
                    <label class="text-[10px] font-black text-slate-400 uppercase tracking-wider mb-1 block">Area</label>
                    <Select v-model="newReservation.area" :options="['Indoor', 'Outdoor', 'VIP Room', 'Smoking', 'Terrace']" class="w-full !rounded-xl" />
                </div>
                <div>
                    <label class="text-[10px] font-black text-slate-400 uppercase tracking-wider mb-1 block">Catatan Khusus</label>
                    <Textarea v-model="newReservation.note" rows="2" placeholder="Ulang tahun, alergi makanan, dll..." class="w-full !rounded-xl !text-sm" />
                </div>
            </div>
            <template #footer>
                <div class="flex justify-end gap-3 pt-2">
                    <Button label="Batal" severity="secondary" text @click="showReservationModal = false" class="!rounded-xl" />
                    <Button label="Simpan Reservasi" icon="pi pi-check" class="!rounded-xl !bg-emerald-600 !border-emerald-600" @click="saveReservation" />
                </div>
            </template>
        </Dialog>

    </AppPage>
</template>

<script setup>
import { ref, computed } from 'vue';
import AppPage from '@/components/layout/AppPage.vue';
import Button from 'primevue/button';
import Tag from 'primevue/tag';
import Chart from 'primevue/chart';
import Select from 'primevue/select';
import InputText from 'primevue/inputtext';
import InputNumber from 'primevue/inputnumber';
import DatePicker from 'primevue/datepicker';
import Textarea from 'primevue/textarea';
import Dialog from 'primevue/dialog';
import { useToast } from 'primevue/usetoast';

const toast = useToast();

// ── Filters ───────────────────────────────────────────────────────────────
const selectedBranch = ref('central');
const branchOptions = ref([
    { label: 'Cabang Central',  value: 'central' },
    { label: 'Suburban Bistro', value: 'suburban' },
    { label: 'Coastal Cafe',    value: 'coastal' },
]);

const selectedFloor = ref('1');
const floors = ref([
    { id: '1', label: 'Lantai 1' },
    { id: '2', label: 'Lantai 2' },
    { id: 'roof', label: 'Rooftop' },
]);

const selectedArea = ref('all');
const areas = ref([
    { id: 'all',     label: 'Semua' },
    { id: 'indoor',  label: 'Indoor' },
    { id: 'outdoor', label: 'Outdoor' },
    { id: 'vip',     label: 'VIP' },
    { id: 'smoking', label: 'Smoking' },
    { id: 'terrace', label: 'Terrace' },
]);

const searchQuery = ref('');
const selectedTable = ref(null);
const selectedMetric = ref('occupancy');
const showReservationModal = ref(false);

// ── Mock Table Data ────────────────────────────────────────────────────────
const zones = ref([
    {
        id: 'indoor', name: 'Indoor Area', areaKey: 'indoor',
        tables: [
            { id: 'A01', number: 'A01', capacity: 4, shape: 'square', area: 'Indoor', status: 'available',  vip: false, mergedWith: null },
            { id: 'A02', number: 'A02', capacity: 4, shape: 'square', area: 'Indoor', status: 'occupied',   vip: false, mergedWith: null, customer: 'Andi Wijaya',    guests: 3, waiter: 'Rizki',   checkIn: '18:12', elapsed: '45m', bill: 235000, orders: [{ name: 'Nasi Goreng Spesial', qty: 2, price: 55000 }, { name: 'Es Teh Manis', qty: 3, price: 15000 }, { name: 'Ayam Bakar', qty: 1, price: 75000 }] },
            { id: 'A03', number: 'A03', capacity: 6, shape: 'round',  area: 'Indoor', status: 'occupied',   vip: false, mergedWith: null, customer: 'Budi Santoso',   guests: 4, waiter: 'Dewi',   checkIn: '18:32', elapsed: '25m', bill: 458000, orders: [{ name: 'Steak Sapi Ribeye', qty: 2, price: 145000 }, { name: 'Pasta Carbonara', qty: 1, price: 75000 }, { name: 'Cappuccino', qty: 3, price: 38000 }], memberInfo: { tier: 'Gold Member', visits: 18, avgSpend: 420000 } },
            { id: 'A04', number: 'A04', capacity: 4, shape: 'square', area: 'Indoor', status: 'reserved',   vip: false, mergedWith: null },
            { id: 'A05', number: 'A05', capacity: 2, shape: 'round',  area: 'Indoor', status: 'cleaning',   vip: false, mergedWith: null },
            { id: 'A06', number: 'A06', capacity: 6, shape: 'square', area: 'Indoor', status: 'available',  vip: false, mergedWith: null },
            { id: 'A07', number: 'A07', capacity: 4, shape: 'square', area: 'Indoor', status: 'occupied',   vip: false, mergedWith: 'A08', customer: 'Keluarga Hendra', guests: 7, waiter: 'Sari', checkIn: '17:50', elapsed: '1j 7m', bill: 892000, orders: [{ name: 'Paket Keluarga', qty: 2, price: 320000 }, { name: 'Jus Alpukat', qty: 4, price: 28000 }, { name: 'Es Krim', qty: 3, price: 35000 }] },
            { id: 'A08', number: 'A08', capacity: 4, shape: 'square', area: 'Indoor', status: 'occupied',   vip: false, mergedWith: 'A07' },
            { id: 'A09', number: 'A09', capacity: 8, shape: 'long',   area: 'Indoor', status: 'available',  vip: false, mergedWith: null },
            { id: 'A10', number: 'A10', capacity: 4, shape: 'square', area: 'Indoor', status: 'waiting-bill', vip: false, mergedWith: null, customer: 'Jessica L.', guests: 2, waiter: 'Rizki', checkIn: '17:15', elapsed: '1j 42m', bill: 325000 },
        ]
    },
    {
        id: 'outdoor', name: 'Outdoor Area', areaKey: 'outdoor',
        tables: [
            { id: 'B01', number: 'B01', capacity: 4, shape: 'round',  area: 'Outdoor', status: 'available', vip: false, mergedWith: null },
            { id: 'B02', number: 'B02', capacity: 4, shape: 'round',  area: 'Outdoor', status: 'available', vip: false, mergedWith: null },
            { id: 'B03', number: 'B03', capacity: 6, shape: 'square', area: 'Outdoor', status: 'occupied',  vip: false, mergedWith: null, customer: 'Siti Rahmawati', guests: 5, waiter: 'Budi', checkIn: '18:45', elapsed: '12m', bill: 120000, orders: [{ name: 'Sate Ayam Madura', qty: 3, price: 35000 }, { name: 'Es Kelapa Muda', qty: 5, price: 25000 }] },
            { id: 'B04', number: 'B04', capacity: 4, shape: 'round',  area: 'Outdoor', status: 'occupied',  vip: false, mergedWith: null, customer: 'Walk-in', guests: 2, waiter: 'Rani', checkIn: '18:50', elapsed: '7m', bill: 85000 },
            { id: 'B05', number: 'B05', capacity: 6, shape: 'square', area: 'Outdoor', status: 'reserved',  vip: false, mergedWith: null },
            { id: 'B06', number: 'B06', capacity: 4, shape: 'round',  area: 'Outdoor', status: 'available', vip: false, mergedWith: null },
        ]
    },
    {
        id: 'vip', name: 'VIP Room', areaKey: 'vip',
        tables: [
            { id: 'VIP-01', number: 'VIP-01', capacity: 8,  shape: 'long',   area: 'VIP',     status: 'waiting-bill', vip: true, mergedWith: null, customer: 'Tn. Hartono (Platinum)', guests: 6, waiter: 'Jessica', checkIn: '17:00', elapsed: '1j 57m', bill: 2450000, orders: [{ name: 'Wagyu Steak A5', qty: 2, price: 650000 }, { name: 'Lobster Bakar', qty: 1, price: 850000 }, { name: 'Wine Bottle', qty: 1, price: 750000 }], memberInfo: { tier: 'Platinum Member', visits: 42, avgSpend: 2100000 } },
            { id: 'VIP-02', number: 'VIP-02', capacity: 10, shape: 'long',   area: 'VIP',     status: 'available', vip: true, mergedWith: null },
            { id: 'VIP-03', number: 'VIP-03', capacity: 6,  shape: 'round',  area: 'VIP',     status: 'occupied',  vip: true, mergedWith: null, customer: 'Meeting Room Booking', guests: 5, waiter: 'Dewi', checkIn: '18:00', elapsed: '57m', bill: 1250000 },
        ]
    },
    {
        id: 'smoking', name: 'Smoking Area', areaKey: 'smoking',
        tables: [
            { id: 'S01', number: 'S01', capacity: 4, shape: 'square', area: 'Smoking', status: 'available', vip: false, mergedWith: null },
            { id: 'S02', number: 'S02', capacity: 4, shape: 'square', area: 'Smoking', status: 'occupied',  vip: false, mergedWith: null, customer: 'Walk-in', guests: 2, waiter: 'Rizki', checkIn: '19:00', elapsed: '5m', bill: 45000 },
            { id: 'S03', number: 'S03', capacity: 4, shape: 'round',  area: 'Smoking', status: 'out-of-service', vip: false, mergedWith: null },
        ]
    },
    {
        id: 'terrace', name: 'Terrace', areaKey: 'terrace',
        tables: [
            { id: 'T01', number: 'T01', capacity: 2, shape: 'round',  area: 'Terrace', status: 'available', vip: false, mergedWith: null },
            { id: 'T02', number: 'T02', capacity: 2, shape: 'round',  area: 'Terrace', status: 'occupied',  vip: false, mergedWith: null, customer: 'Walk-in', guests: 2, waiter: 'Sari', checkIn: '18:58', elapsed: '9m', bill: 68000 },
            { id: 'T03', number: 'T03', capacity: 4, shape: 'square', area: 'Terrace', status: 'reserved',  vip: false, mergedWith: null },
            { id: 'T04', number: 'T04', capacity: 4, shape: 'square', area: 'Terrace', status: 'cleaning',  vip: false, mergedWith: null },
        ]
    },
]);

// All tables flat
const allTables = computed(() => zones.value.flatMap(z => z.tables));

// Filtered tables based on area + search
const filteredTables = computed(() => {
    return allTables.value.filter(t => {
        const matchArea = selectedArea.value === 'all' || t.area.toLowerCase() === selectedArea.value;
        const matchSearch = !searchQuery.value || 
            t.number.toLowerCase().includes(searchQuery.value.toLowerCase()) ||
            (t.customer || '').toLowerCase().includes(searchQuery.value.toLowerCase());
        return matchArea && matchSearch;
    });
});

// Filtered zones (only show zones with visible tables)
const filteredZones = computed(() => {
    return zones.value.filter(z =>
        z.tables.some(t => filteredTables.value.includes(t))
    );
});

// ── KPI Data ──────────────────────────────────────────────────────────────
const totalTables    = computed(() => allTables.value.length);
const occupiedCount  = computed(() => allTables.value.filter(t => t.status === 'occupied' || t.status === 'waiting-bill').length);
const availableCount = computed(() => allTables.value.filter(t => t.status === 'available').length);
const reservedCount  = computed(() => allTables.value.filter(t => t.status === 'reserved').length);
const cleaningCount  = computed(() => allTables.value.filter(t => t.status === 'cleaning').length);
const occupancyRate  = computed(() => Math.round((occupiedCount.value / totalTables.value) * 100));

const kpiCards = computed(() => [
    { key: 'total',     title: 'Total Meja',    value: totalTables.value,   icon: 'pi pi-th-large text-slate-600',   iconBg: 'bg-slate-100',   valueColor: 'text-slate-800',    sub: 'Semua area',             subColor: 'text-slate-400' },
    { key: 'occupied',  title: 'Terisi',         value: occupiedCount.value, icon: 'pi pi-users text-blue-600',       iconBg: 'bg-blue-50',     valueColor: 'text-blue-700',     sub: `${occupancyRate.value}% rate`,  subColor: 'text-blue-400' },
    { key: 'available', title: 'Kosong',          value: availableCount.value,icon: 'pi pi-check-circle text-emerald-600', iconBg: 'bg-emerald-50', valueColor: 'text-emerald-700', sub: 'Siap digunakan',          subColor: 'text-emerald-400' },
    { key: 'reserved',  title: 'Reservasi',      value: reservedCount.value, icon: 'pi pi-calendar text-orange-600',  iconBg: 'bg-orange-50',   valueColor: 'text-orange-700',   sub: 'Hari ini',               subColor: 'text-orange-400' },
    { key: 'cleaning',  title: 'Cleaning',       value: cleaningCount.value, icon: 'pi pi-sparkles text-rose-600',    iconBg: 'bg-rose-50',     valueColor: 'text-rose-700',     sub: 'Sedang dibersihkan',     subColor: 'text-rose-400' },
    { key: 'waiting',   title: 'Waiting List',   value: 12,                  icon: 'pi pi-clock text-violet-600',     iconBg: 'bg-violet-50',   valueColor: 'text-violet-700',   sub: '~15 mnt est.',           subColor: 'text-violet-400' },
    { key: 'today_res', title: 'Reservasi Hari Ini', value: 8,               icon: 'pi pi-bookmark text-indigo-600',  iconBg: 'bg-indigo-50',   valueColor: 'text-indigo-700',   sub: '3 datang, 2 diconf.',    subColor: 'text-indigo-400' },
    { key: 'avg_time',  title: 'Avg Dining',     value: '58m',               icon: 'pi pi-stopwatch text-teal-600',   iconBg: 'bg-teal-50',     valueColor: 'text-teal-700',     sub: 'Per meja per giliran',   subColor: 'text-teal-400' },
]);

// ── Status helpers ─────────────────────────────────────────────────────────
const statusLegend = [
    { key: 'available',    label: 'Kosong',       dot: 'bg-emerald-500' },
    { key: 'occupied',     label: 'Terisi',       dot: 'bg-blue-500' },
    { key: 'reserved',     label: 'Reservasi',    dot: 'bg-orange-500' },
    { key: 'waiting-bill', label: 'Tunggu Bayar', dot: 'bg-violet-500' },
    { key: 'cleaning',     label: 'Cleaning',     dot: 'bg-rose-500' },
    { key: 'out-of-service',label: 'Non-aktif',   dot: 'bg-slate-400' },
];

function tableStatusClass(status) {
    const map = {
        'available':     'bg-emerald-50 border-emerald-200 text-emerald-800 hover:bg-emerald-100',
        'occupied':      'bg-blue-50 border-blue-300 text-blue-800 hover:bg-blue-100',
        'reserved':      'bg-orange-50 border-orange-300 text-orange-800 hover:bg-orange-100',
        'waiting-bill':  'bg-violet-50 border-violet-300 text-violet-800 hover:bg-violet-100',
        'cleaning':      'bg-rose-50 border-rose-200 text-rose-700 hover:bg-rose-100',
        'out-of-service':'bg-slate-100 border-slate-200 text-slate-400 cursor-not-allowed hover:bg-slate-100',
    };
    return map[status] || 'bg-slate-50 border-slate-200';
}

function statusDot(status) {
    const map = {
        'available':     'bg-emerald-500',
        'occupied':      'bg-blue-500',
        'reserved':      'bg-orange-500',
        'waiting-bill':  'bg-violet-500',
        'cleaning':      'bg-rose-500',
        'out-of-service':'bg-slate-400',
    };
    return map[status] || 'bg-slate-400';
}

function statusLabel(status) {
    const map = {
        'available':      'Kosong',
        'occupied':       'Terisi',
        'reserved':       'Reservasi',
        'waiting-bill':   'Tunggu Bayar',
        'cleaning':       'Cleaning',
        'out-of-service': 'Non-aktif',
    };
    return map[status] || status;
}

const tableDetailHeaderBg = computed(() => {
    const map = {
        'available':      'bg-gradient-to-br from-emerald-600 to-emerald-800',
        'occupied':       'bg-gradient-to-br from-blue-600 to-blue-800',
        'reserved':       'bg-gradient-to-br from-orange-500 to-orange-700',
        'waiting-bill':   'bg-gradient-to-br from-violet-600 to-violet-800',
        'cleaning':       'bg-gradient-to-br from-rose-500 to-rose-700',
        'out-of-service': 'bg-gradient-to-br from-slate-500 to-slate-700',
    };
    return map[selectedTable.value?.status] || 'bg-gradient-to-br from-slate-600 to-slate-800';
});

function tableSize(table) {
    if (table.shape === 'long') return 'w-[100px] h-[56px]';
    if (table.capacity >= 6)    return 'w-[72px] h-[72px]';
    return 'w-[64px] h-[64px]';
}

function tableShapeIcon(shape) {
    return shape === 'round' ? 'pi pi-circle' : shape === 'long' ? 'pi pi-minus' : 'pi pi-stop';
}

// ── Table Selection & Actions ──────────────────────────────────────────────
function selectTable(table) {
    if (table.status === 'out-of-service') return;
    selectedTable.value = table;
}

const billSubtotal = computed(() => {
    if (!selectedTable.value?.orders) return 0;
    return selectedTable.value.orders.reduce((s, i) => s + (i.price * i.qty), 0);
});

const tableTimeline = computed(() => {
    if (!selectedTable.value) return [];
    const s = selectedTable.value;
    return [
        { label: 'Tamu Check In',      time: s.checkIn,  done: true },
        { label: 'Pesanan Dibuat',     time: s.checkIn,  done: !!s.orders?.length },
        { label: 'Makanan Disajikan',  time: '',          done: s.elapsed && parseInt(s.elapsed) > 15 },
        { label: 'Minta Bill',         time: '',          done: s.status === 'waiting-bill' },
        { label: 'Pembayaran Selesai', time: '',          done: false },
        { label: 'Meja Dibebaskan',    time: '',          done: false },
    ];
});

const quickActions = computed(() => {
    const status = selectedTable.value?.status;
    return [
        { key: 'assign',   label: 'Assign Tamu',   icon: 'pi pi-user-plus',  classes: 'bg-emerald-50 border-emerald-200 text-emerald-700 hover:bg-emerald-100' },
        { key: 'pos',      label: 'Buka POS',      icon: 'pi pi-calculator', classes: 'bg-blue-50 border-blue-200 text-blue-700 hover:bg-blue-100' },
        { key: 'move',     label: 'Pindah Meja',   icon: 'pi pi-arrows-alt', classes: 'bg-slate-50 border-slate-200 text-slate-700 hover:bg-slate-100' },
        { key: 'merge',    label: 'Gabung Meja',   icon: 'pi pi-sitemap',    classes: 'bg-violet-50 border-violet-200 text-violet-700 hover:bg-violet-100' },
        { key: 'reserve',  label: 'Reservasi',     icon: 'pi pi-calendar',   classes: 'bg-orange-50 border-orange-200 text-orange-700 hover:bg-orange-100' },
        { key: 'clean',    label: 'Set Cleaning',  icon: 'pi pi-sparkles',   classes: 'bg-rose-50 border-rose-200 text-rose-700 hover:bg-rose-100' },
        { key: 'bill',     label: 'Cetak Bill',    icon: 'pi pi-print',      classes: 'bg-indigo-50 border-indigo-200 text-indigo-700 hover:bg-indigo-100', show: status === 'occupied' || status === 'waiting-bill' },
        { key: 'close',    label: 'Tutup Meja',    icon: 'pi pi-times-circle',classes: 'bg-slate-50 border-slate-200 text-slate-600 hover:bg-slate-100' },
    ];
});

function handleAction(key) {
    const table = selectedTable.value;
    if (!table) return;

    const actions = {
        pos:     () => { toast.add({ severity: 'info',    summary: 'Membuka POS', detail: `POS untuk meja ${table.number}`, life: 2000 }); },
        assign:  () => { toast.add({ severity: 'success', summary: 'Assign Tamu', detail: `Meja ${table.number} siap menerima tamu`, life: 2000 }); },
        move:    () => { toast.add({ severity: 'info',    summary: 'Pindah Meja', detail: 'Pilih meja tujuan...', life: 2000 }); },
        merge:   () => { toast.add({ severity: 'info',    summary: 'Gabung Meja', detail: 'Pilih meja yang akan digabung...', life: 2000 }); },
        reserve: () => { showReservationModal.value = true; },
        clean:   () => {
            table.status = 'cleaning';
            toast.add({ severity: 'warn', summary: 'Set Cleaning', detail: `Meja ${table.number} ditandai cleaning`, life: 2000 });
        },
        bill:    () => { toast.add({ severity: 'success', summary: 'Cetak Bill', detail: `Bill meja ${table.number} dikirim ke printer`, life: 2000 }); },
        close:   () => {
            table.status = 'available';
            table.customer = null; table.guests = null; table.orders = null; table.bill = 0; table.checkIn = null; table.elapsed = null; table.memberInfo = null;
            selectedTable.value = null;
            toast.add({ severity: 'success', summary: 'Meja Ditutup', detail: `Meja ${table.number} kembali tersedia`, life: 2000 });
        },
    };
    actions[key]?.();
}

// ── Reservations ──────────────────────────────────────────────────────────
const reservations = ref([
    { id: 'RES-1025', name: 'Andi Wijaya',       phone: '0812-1234-5678', guests: 6, time: '19:00', area: 'Indoor',   status: 'Confirmed', note: 'Meja dekat jendela SVP', source: 'Phone' },
    { id: 'RES-1026', name: 'Siti Rahmawati',    phone: '0813-9876-5432', guests: 4, time: '19:30', area: 'Outdoor',  status: 'Confirmed', note: '', source: 'WhatsApp' },
    { id: 'RES-1027', name: 'Keluarga Hendra',   phone: '0821-4567-8901', guests: 10,time: '18:00', area: 'Indoor',   status: 'Arrived',   note: 'Ulang tahun anak', source: 'App' },
    { id: 'RES-1028', name: 'Meeting Corp PT X', phone: '0811-2222-3333', guests: 8, time: '17:00', area: 'VIP Room', status: 'Completed', note: 'Butuh proyektor', source: 'Phone' },
    { id: 'RES-1029', name: 'Jessica Loren',     phone: '0819-6543-2109', guests: 2, time: '20:00', area: 'Terrace',  status: 'Waiting',   note: '', source: 'App' },
    { id: 'RES-1030', name: 'Budi Santoso',      phone: '0815-3344-5566', guests: 5, time: '20:30', area: 'Indoor',   status: 'Confirmed', note: 'Alergi kacang', source: 'WhatsApp' },
]);

function reservationStatusClass(status) {
    const map = {
        'Confirmed': 'bg-emerald-100 text-emerald-700',
        'Waiting':   'bg-amber-100 text-amber-700',
        'Arrived':   'bg-blue-100 text-blue-700',
        'Completed': 'bg-slate-100 text-slate-500',
        'Cancelled': 'bg-rose-100 text-rose-600',
    };
    return map[status] || 'bg-slate-100 text-slate-600';
}

function viewReservation(res) {
    toast.add({ severity: 'info', summary: res.name, detail: `${res.guests} tamu · ${res.time} · ${res.area}`, life: 3000 });
}

// ── Waiting List ──────────────────────────────────────────────────────────
const waitingList = ref([
    { id: 1, queue: 15, name: 'Siti Rahma',    guests: 3, waitMins: 15, area: 'Indoor',  priority: 'Normal' },
    { id: 2, queue: 16, name: 'Pak Dono',      guests: 6, waitMins: 25, area: 'Outdoor', priority: 'Normal' },
    { id: 3, queue: 17, name: 'Nn. Caroline',  guests: 2, waitMins: 8,  area: 'Terrace', priority: 'VIP' },
    { id: 4, queue: 18, name: 'Grup Wisata',   guests: 12,waitMins: 35, area: 'Indoor',  priority: 'Normal' },
]);

function assignWaiting(queue) {
    toast.add({ severity: 'success', summary: 'Assign Queue', detail: `${queue.name} (Queue #${queue.queue}) akan diarahkan ke meja`, life: 2500 });
}

// ── Merge Data ────────────────────────────────────────────────────────────
const activeMerges = ref([
    { id: 1, tables: ['A07', 'A08'], capacity: 8,  customer: 'Keluarga Hendra' },
    { id: 2, tables: ['B05', 'B06'], capacity: 10, customer: 'Arisan RT 05' },
    { id: 3, tables: ['T01', 'T02'], capacity: 4,  customer: 'Couple Anniversary' },
]);

function splitMerge(id) {
    activeMerges.value = activeMerges.value.filter(m => m.id !== id);
    toast.add({ severity: 'success', summary: 'Meja Dipisah', detail: 'Gabungan meja berhasil dilepas', life: 2000 });
}

// ── New Reservation Form ──────────────────────────────────────────────────
const newReservation = ref({ name: '', phone: '', guests: 2, time: null, area: 'Indoor', note: '' });

function saveReservation() {
    if (!newReservation.value.name || !newReservation.value.time) {
        toast.add({ severity: 'warn', summary: 'Lengkapi Form', detail: 'Nama dan jam harus diisi', life: 2500 });
        return;
    }
    reservations.value.unshift({
        id: `RES-${1031 + reservations.value.length}`,
        ...newReservation.value,
        time: newReservation.value.time?.toTimeString().slice(0, 5) || '00:00',
        status: 'Confirmed',
        source: 'Manual',
    });
    newReservation.value = { name: '', phone: '', guests: 2, time: null, area: 'Indoor', note: '' };
    showReservationModal.value = false;
    toast.add({ severity: 'success', summary: 'Reservasi Disimpan', detail: 'Reservasi berhasil dibuat', life: 2500 });
}

// ── Analytics Chart ────────────────────────────────────────────────────────
const analyticsMetrics = ref([
    { id: 'occupancy', label: 'Okupansi' },
    { id: 'revenue',   label: 'Revenue/Meja' },
    { id: 'turnover',  label: 'Turnover' },
]);

const analyticsChartData = computed(() => {
    const labels = ['08:00','09:00','10:00','11:00','12:00','13:00','14:00','15:00','16:00','17:00','18:00','19:00','20:00','21:00'];
    const datasets = {
        occupancy: [10, 15, 22, 55, 88, 92, 78, 45, 38, 62, 85, 95, 88, 70],
        revenue:   [120,180,210,450,820,940,710,380,310,520,780,950,840,620].map(v => v * 1000),
        turnover:  [0.3,0.4,0.6,1.2,2.1,2.3,1.9,1.1,0.9,1.5,2.0,2.4,2.1,1.7],
    };
    const colors = { occupancy: '#10b981', revenue: '#f59e0b', turnover: '#6366f1' };
    const metric = selectedMetric.value;
    return {
        labels,
        datasets: [{
            label: analyticsMetrics.value.find(m => m.id === metric)?.label,
            data: datasets[metric],
            backgroundColor: colors[metric] + '33',
            borderColor: colors[metric],
            borderWidth: 2,
            borderRadius: 6,
        }],
    };
});

const analyticsChartOptions = {
    maintainAspectRatio: false,
    plugins: { legend: { display: false } },
    scales: {
        y: { beginAtZero: true, ticks: { font: { size: 10 } }, grid: { color: '#f1f5f9' } },
        x: { ticks: { font: { size: 9 }, maxRotation: 0 }, grid: { display: false } },
    },
};

// ── Helpers ────────────────────────────────────────────────────────────────
function formatCurrency(value) {
    if (!value) return 'Rp 0';
    return 'Rp ' + Number(value).toLocaleString('id-ID');
}
</script>

<style scoped>
.app-table :deep(.p-datatable-thead > tr > th) {
    background-color: #f8fafc !important;
    font-weight: 700 !important;
    color: #475569 !important;
}
</style>
