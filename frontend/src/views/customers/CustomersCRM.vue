<template>
    <AppPage title="Customer CRM & Loyalty" :breadcrumb="['Master Data', 'Customers & CRM']" no-card>
        
        <!-- ── Top Control Bar ─────────────────────────────────────────── -->
        <div class="flex flex-wrap items-center justify-between gap-4 bg-white/80 backdrop-blur-md p-4 rounded-2xl border border-slate-100 shadow-sm mb-6 sticky top-0 z-30">
            <div class="flex flex-wrap items-center gap-3">
                <!-- Filter Tabs -->
                <div class="flex items-center gap-1.5 bg-slate-50 rounded-xl p-1">
                    <button v-for="filter in filterTabs" :key="filter.value" 
                        @click="selectedFilterTab = filter.value"
                        class="px-3 py-1.5 text-xs font-bold rounded-lg transition-all cursor-pointer"
                        :class="selectedFilterTab === filter.value
                            ? 'bg-emerald-600 text-white shadow-sm'
                            : 'text-slate-500 hover:text-slate-800'">
                        {{ filter.label }}
                    </button>
                </div>

                <!-- Branch Selector -->
                <div class="flex items-center gap-2 bg-slate-50 px-3 py-1.5 rounded-xl border border-slate-100">
                    <i class="pi pi-map-marker text-emerald-500 text-xs"></i>
                    <span class="text-xs text-slate-500 font-semibold">Cabang:</span>
                    <Select v-model="selectedBranch" :options="branchOptions" optionLabel="label" optionValue="value"
                        class="w-36 !border-none !bg-transparent !shadow-none !rounded-none !text-xs !p-0"
                        pt:input:class="!py-0 !px-1 !text-xs !font-bold" />
                </div>
            </div>

            <div class="flex items-center gap-3">
                <!-- Global Search -->
                <div class="relative">
                    <i class="pi pi-search absolute left-3 top-1/2 -translate-y-1/2 text-slate-400 text-xs"></i>
                    <input v-model="searchQuery" type="text" placeholder="Nama, HP, Email, ID..."
                        class="pl-8 pr-4 py-2 text-xs border border-slate-200 rounded-xl bg-white focus:outline-none focus:ring-2 focus:ring-emerald-300 w-56" />
                </div>

                <!-- Export Options -->
                <div class="flex items-center gap-1.5 bg-slate-50 rounded-xl p-1 border border-slate-100">
                    <button @click="exportCustomerData('excel')" class="w-8 h-8 rounded-lg flex items-center justify-center text-slate-500 hover:text-emerald-600 hover:bg-emerald-50 transition-colors cursor-pointer" title="Export Excel">
                        <i class="pi pi-file-excel text-xs"></i>
                    </button>
                    <button @click="exportCustomerData('pdf')" class="w-8 h-8 rounded-lg flex items-center justify-center text-slate-500 hover:text-rose-600 hover:bg-rose-50 transition-colors cursor-pointer" title="Export PDF">
                        <i class="pi pi-file-pdf text-xs"></i>
                    </button>
                </div>

                <!-- Add Customer Button -->
                <Button label="Customer Baru" icon="pi pi-plus" class="!rounded-xl !text-xs !py-2 !px-4 !bg-emerald-600 !border-emerald-600 shadow-sm" @click="openCreateCustomerModal" />
            </div>
        </div>

        <!-- ── CUSTOMER KPI OVERVIEW ────────────────────────────────────── -->
        <div class="grid grid-cols-2 md:grid-cols-4 xl:grid-cols-8 gap-3 mb-6">
            <div v-for="kpi in kpiOverviewList" :key="kpi.key"
                 class="bg-white rounded-2xl p-3.5 border border-slate-100 shadow-sm hover:-translate-y-0.5 transition-transform duration-200 cursor-default">
                <div class="flex justify-between items-start mb-2">
                    <p class="text-[9px] font-black text-slate-400 uppercase tracking-wider leading-tight">{{ kpi.title }}</p>
                    <div :class="kpi.iconBg" class="w-7 h-7 rounded-xl flex items-center justify-center flex-shrink-0">
                        <i :class="kpi.icon" class="text-[11px]"></i>
                    </div>
                </div>
                <h3 class="text-lg font-black text-slate-800 leading-none">{{ kpi.value }}</h3>
                <div class="flex items-center gap-1 mt-1.5">
                    <span class="text-[9px] font-bold" :class="kpi.trendClass">
                        <i :class="kpi.trendIcon" class="text-[8px] mr-0.5"></i>{{ kpi.growth }}
                    </span>
                    <span class="text-[9px] font-semibold text-slate-400">vs bln lalu</span>
                </div>
            </div>
        </div>

        <!-- ── MAIN CRM WORKSPACE GRID ───────────────────────────────────── -->
        <div class="grid grid-cols-1 xl:grid-cols-12 gap-6">

            <!-- ── LEFT PANEL: Directory & Tabs (Col 8) ──────────────────── -->
            <div class="xl:col-span-8 space-y-6">

                <!-- Customer Directory Card -->
                <div class="bg-white rounded-2xl border border-slate-100 shadow-sm overflow-hidden">
                    <div class="px-5 py-4 border-b border-slate-100 flex items-center justify-between bg-slate-50/50">
                        <div class="flex items-center gap-3">
                            <div class="w-8 h-8 bg-emerald-50 rounded-xl flex items-center justify-center">
                                <i class="pi pi-users text-emerald-600 text-sm"></i>
                            </div>
                            <div>
                                <h3 class="font-black text-slate-800 text-sm">Direktori Pelanggan</h3>
                                <p class="text-[10px] text-slate-400 font-medium">Klik baris pelanggan untuk memuat profil 360° di panel kanan</p>
                            </div>
                        </div>
                        <Tag :value="`${filteredCustomers.length} Customer Tampil`" severity="success" class="text-[10px] rounded-full" />
                    </div>

                    <AppDataTable :value="filteredCustomers" :paginator="true" :rows="10" class="app-table text-xs">
                        <Column header="Nama Customer" style="min-width: 180px">
                            <template #body="{ data }">
                                <button @click="selectCustomer(data)" class="flex items-center gap-2.5 text-left font-bold text-slate-700 hover:text-emerald-600 transition-colors w-full cursor-pointer">
                                    <div class="w-7 h-7 rounded-full bg-gradient-to-tr from-emerald-500 to-teal-600 flex items-center justify-center text-white text-[10px] font-black">
                                        {{ data.name[0] }}
                                    </div>
                                    <div>
                                        <p class="font-bold leading-tight">{{ data.name }}</p>
                                        <p class="text-[9px] text-slate-400 font-mono mt-0.5">{{ data.memberId }}</p>
                                    </div>
                                </button>
                            </template>
                        </Column>
                        <Column field="phone" header="Kontak">
                            <template #body="{ data }">
                                <p class="font-semibold text-slate-600">{{ data.phone }}</p>
                                <p class="text-[9px] text-slate-400">{{ data.email }}</p>
                            </template>
                        </Column>
                        <Column field="tier" header="Membership">
                            <template #body="{ data }">
                                <Tag :value="data.tier + ' Tier'" :class="getMembershipBadgeClass(data.tier)" class="text-[9px] rounded-lg" />
                            </template>
                        </Column>
                        <Column field="points" header="Loyalty Point" class="text-right">
                            <template #body="{ data }">
                                <span class="font-bold text-slate-700">{{ data.points.toLocaleString('id-ID') }} pt</span>
                            </template>
                        </Column>
                        <Column field="visits" header="Kunjungan" class="text-center">
                            <template #body="{ data }">
                                <span class="font-semibold text-slate-600">{{ data.visits }}x</span>
                            </template>
                        </Column>
                        <Column field="totalSpend" header="Total Belanja" class="text-right">
                            <template #body="{ data }">
                                <span class="font-black text-slate-800">{{ formatCurrency(data.totalSpend) }}</span>
                            </template>
                        </Column>
                        <Column field="status" header="Status" class="text-center">
                            <template #body="{ data }">
                                <span class="inline-flex items-center gap-1 text-[10px] font-bold px-2 py-0.5 rounded-full"
                                      :class="data.status === 'Active' ? 'bg-emerald-50 text-emerald-700' : 'bg-slate-100 text-slate-500'">
                                    <span class="w-1.5 h-1.5 rounded-full" :class="data.status === 'Active' ? 'bg-emerald-500' : 'bg-slate-400'"></span>
                                    {{ data.status }}
                                </span>
                            </template>
                        </Column>
                        <Column field="favoriteCategory" header="Kategori Favorit">
                            <template #body="{ data }">
                                <span class="text-[10px] bg-slate-100 text-slate-600 font-semibold px-2 py-1 rounded-md">{{ data.favoriteCategory }}</span>
                            </template>
                        </Column>
                    </AppDataTable>
                </div>

                <!-- CRM Subsections Tabs -->
                <div class="bg-white rounded-2xl border border-slate-100 shadow-sm overflow-hidden">
                    <!-- Tab Selector Header -->
                    <div class="flex flex-wrap items-center gap-1 p-2 bg-slate-50 border-b border-slate-100">
                        <button v-for="tab in crmTabs" :key="tab.id"
                            @click="activeCrmTab = tab.id"
                            class="flex items-center gap-2 px-3 py-2 text-xs font-bold rounded-xl transition-all cursor-pointer"
                            :class="activeCrmTab === tab.id
                                ? 'bg-white text-emerald-700 shadow-sm border border-slate-100'
                                : 'text-slate-400 hover:text-slate-700'">
                            <i :class="tab.icon" class="text-xs"></i>
                            {{ tab.label }}
                        </button>
                    </div>

                    <!-- Tab Content Panel -->
                    <div class="p-5">
                        
                        <!-- 1. Customer Segmentation -->
                        <div v-if="activeCrmTab === 'segmentation'" class="space-y-4">
                            <div class="flex justify-between items-center">
                                <h4 class="text-xs font-black text-slate-800 uppercase tracking-wider">Segmentasi Pelanggan Real-Time</h4>
                                <span class="text-[10px] text-slate-400 font-medium">Berdasarkan AI RFM Score (Recency, Frequency, Monetary)</span>
                            </div>
                            <div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-4 gap-4">
                                <div v-for="seg in customerSegments" :key="seg.name" 
                                     class="p-4 rounded-xl border border-slate-100 bg-slate-50/50 hover:bg-slate-50 transition-colors flex flex-col justify-between h-32 cursor-pointer"
                                     @click="filterBySegment(seg.filter)">
                                    <div class="flex justify-between items-start">
                                        <div :class="seg.colorBg" class="w-8 h-8 rounded-xl flex items-center justify-center">
                                            <i :class="seg.icon" class="text-xs"></i>
                                        </div>
                                        <span class="text-[10px] font-black text-slate-400 bg-white px-2 py-0.5 rounded-full shadow-sm">
                                            {{ seg.count }} Customer
                                        </span>
                                    </div>
                                    <div class="mt-4">
                                        <h5 class="text-xs font-black text-slate-800">{{ seg.name }}</h5>
                                        <p class="text-[10px] text-slate-400 font-medium leading-tight mt-0.5">{{ seg.description }}</p>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- 2. Campaign Center -->
                        <div v-else-if="activeCrmTab === 'campaigns'" class="space-y-4">
                            <div class="flex justify-between items-center">
                                <h4 class="text-xs font-black text-slate-800 uppercase tracking-wider">Dashboard Aktivasi & Promosi (Campaign Center)</h4>
                                <Button label="Buat Campaign Baru" icon="pi pi-plus" size="small" class="!text-[10px] !py-1.5 !bg-emerald-600 !border-emerald-600" @click="showCampaignModal = true" />
                            </div>
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                <div v-for="camp in marketingCampaigns" :key="camp.id"
                                     class="bg-white rounded-xl p-4 border border-slate-100 shadow-sm flex flex-col justify-between">
                                    <div>
                                        <div class="flex justify-between items-center mb-3">
                                            <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-[9px] font-black uppercase tracking-wider" 
                                                  :class="camp.badgeClass">
                                                <i :class="camp.icon" class="text-[8px]"></i> {{ camp.channel }}
                                            </span>
                                            <Tag :value="camp.status" :severity="camp.status === 'Aktif' ? 'success' : 'secondary'" class="text-[9px]" />
                                        </div>
                                        <h5 class="text-xs font-black text-slate-800">{{ camp.title }}</h5>
                                        <p class="text-[10px] text-slate-400 mt-1 font-semibold">Target: {{ camp.target }}</p>
                                        <p class="text-[10px] text-slate-400 mt-0.5">Penerima: {{ camp.recipients.toLocaleString('id-ID') }} Pelanggan</p>
                                    </div>
                                    
                                    <!-- Metrics -->
                                    <div class="grid grid-cols-3 gap-2 mt-4 pt-4 border-t border-slate-100 text-center">
                                        <div>
                                            <p class="text-xs font-black text-slate-800">{{ camp.openRate }}%</p>
                                            <p class="text-[8px] text-slate-400 uppercase font-black tracking-wider">Open Rate</p>
                                        </div>
                                        <div>
                                            <p class="text-xs font-black text-slate-800">{{ camp.conversion }}%</p>
                                            <p class="text-[8px] text-slate-400 uppercase font-black tracking-wider">Konversi</p>
                                        </div>
                                        <div>
                                            <p class="text-xs font-black text-emerald-600">{{ formatCurrency(camp.revenue) }}</p>
                                            <p class="text-[8px] text-slate-400 uppercase font-black tracking-wider">Revenue</p>
                                        </div>
                                    </div>

                                    <div class="mt-4 flex gap-2">
                                        <Button label="Lihat Analitik" severity="secondary" text class="w-full !text-[10px] !py-1.5 font-bold" />
                                        <Button label="Kirim Ulang" class="w-full !text-[10px] !py-1.5 font-bold !bg-emerald-600 !border-emerald-600" />
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- 3. Rewards Catalog -->
                        <div v-else-if="activeCrmTab === 'rewards'" class="space-y-4">
                            <div class="flex justify-between items-center">
                                <h4 class="text-xs font-black text-slate-800 uppercase tracking-wider">Katalog Penukaran Reward Loyalty</h4>
                                <div class="text-right">
                                    <p class="text-[10px] text-slate-400 font-medium">Customer Terpilih:</p>
                                    <p class="text-xs font-black text-emerald-600">{{ selectedCustomer.name }} ({{ selectedCustomer.points }} pt)</p>
                                </div>
                            </div>
                            <div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-3 gap-4">
                                <div v-for="reward in loyaltyRewards" :key="reward.title"
                                     class="bg-white rounded-xl p-4 border border-slate-100 shadow-sm flex flex-col justify-between">
                                    <div>
                                        <div class="flex justify-between items-start">
                                            <div class="w-8 h-8 rounded-xl bg-orange-50 flex items-center justify-center text-orange-500">
                                                <i :class="reward.icon" class="text-xs"></i>
                                            </div>
                                            <span class="text-[10px] font-black text-orange-600 bg-orange-50 px-2 py-0.5 rounded-full">
                                                {{ reward.pointsRequired }} Point
                                            </span>
                                        </div>
                                        <h5 class="text-xs font-black text-slate-800 mt-3">{{ reward.title }}</h5>
                                        <p class="text-[10px] text-slate-400 leading-normal mt-1">{{ reward.description }}</p>
                                    </div>
                                    <div class="mt-4 pt-3 border-t border-slate-50 flex justify-between items-center">
                                        <span class="text-[9px] font-black text-slate-400 uppercase">Min. Tier: {{ reward.minTier }}</span>
                                        <Button label="Tukar Reward" size="small" class="!text-[9px] !py-1 !px-2.5 !bg-emerald-600 !border-emerald-600 font-bold"
                                                :disabled="!canRedeemReward(reward)"
                                                @click="redeemRewardForCustomer(reward)" />
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- 4. Referral Program -->
                        <div v-else-if="activeCrmTab === 'referrals'" class="space-y-4">
                            <div class="flex justify-between items-center">
                                <h4 class="text-xs font-black text-slate-800 uppercase tracking-wider">Pelacakan Program Referral</h4>
                                <span class="text-[10px] text-slate-400 font-medium">Tamu Terpilih: {{ selectedCustomer.name }}</span>
                            </div>
                            
                            <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-4">
                                <div class="bg-slate-50/60 p-3 rounded-xl border border-slate-100 text-center">
                                    <p class="text-xs text-slate-400 font-bold uppercase tracking-wider">Kode Referral</p>
                                    <p class="text-base font-black text-slate-800 mt-1 font-mono">{{ selectedCustomer.referralCode || '—' }}</p>
                                </div>
                                <div class="bg-slate-50/60 p-3 rounded-xl border border-slate-100 text-center">
                                    <p class="text-xs text-slate-400 font-bold uppercase tracking-wider">Sukses Referral</p>
                                    <p class="text-base font-black text-slate-800 mt-1">{{ selectedCustomer.referrals || 0 }} Tamu</p>
                                </div>
                                <div class="bg-slate-50/60 p-3 rounded-xl border border-slate-100 text-center">
                                    <p class="text-xs text-slate-400 font-bold uppercase tracking-wider">Bonus Didapat</p>
                                    <p class="text-base font-black text-emerald-600 mt-1">{{ formatCurrency(selectedCustomer.referralEarned || 0) }}</p>
                                </div>
                            </div>

                            <!-- Referral Metrics -->
                            <div class="bg-white p-4 rounded-xl border border-slate-100">
                                <p class="text-xs font-black text-slate-800 mb-3">Tabel Konversi & Rujukan Tamu Baru</p>
                                <AppDataTable :value="referralList" :paginator="false" class="app-table text-[11px]">
                                    <Column field="referredName" header="Nama Tamu Dirujuk" />
                                    <Column field="date" header="Tanggal Join" />
                                    <Column field="totalSpend" header="Belanja Tamu Baru">
                                        <template #body="{ data }">
                                            <span class="font-bold">{{ formatCurrency(data.totalSpend) }}</span>
                                        </template>
                                    </Column>
                                    <Column field="conversionStatus" header="Status Konversi">
                                        <template #body="{ data }">
                                            <Tag :value="data.conversionStatus" 
                                                 :severity="data.conversionStatus === 'Transaksi Pertama' ? 'success' : 'warn'"
                                                 class="text-[9px] rounded" />
                                        </template>
                                    </Column>
                                </AppDataTable>
                            </div>
                        </div>

                        <!-- 5. Customer Feedback -->
                        <div v-else-if="activeCrmTab === 'feedback'" class="space-y-4">
                            <div class="flex justify-between items-center">
                                <h4 class="text-xs font-black text-slate-800 uppercase tracking-wider">Ulasan & Sentiment Analysis Pelanggan</h4>
                                <div class="flex items-center gap-2">
                                    <span class="text-[10px] text-slate-400 font-bold">Rata-rata Rating:</span>
                                    <span class="text-xs font-black text-amber-500 flex items-center gap-1">
                                        <i class="pi pi-star-fill"></i> 4.8/5.0
                                    </span>
                                </div>
                            </div>
                            
                            <div class="divide-y divide-slate-100">
                                <div v-for="review in customerReviews" :key="review.id" class="py-3.5 flex items-start gap-4">
                                    <div class="w-8 h-8 rounded-full bg-slate-100 flex items-center justify-center text-xs font-bold text-slate-600">
                                        {{ review.name[0] }}
                                    </div>
                                    <div class="flex-1 min-w-0">
                                        <div class="flex justify-between items-start">
                                            <div>
                                                <h5 class="text-xs font-black text-slate-800">{{ review.name }}</h5>
                                                <div class="flex items-center gap-1.5 mt-0.5">
                                                    <!-- Stars -->
                                                    <span class="flex gap-0.5 text-[9px] text-amber-400">
                                                        <i v-for="star in 5" :key="star" :class="star <= review.rating ? 'pi pi-star-fill' : 'pi pi-star'" class="text-[9px]"></i>
                                                    </span>
                                                    <span class="text-[9px] text-slate-400 font-medium">{{ review.date }}</span>
                                                </div>
                                            </div>
                                            
                                            <!-- Sentiment Badge -->
                                            <div class="flex items-center gap-2">
                                                <span class="text-[8px] font-black uppercase px-2 py-0.5 rounded-full"
                                                      :class="getSentimentClass(review.sentiment)">
                                                    AI Sentiment: {{ review.sentiment }}
                                                </span>
                                                <Tag :value="review.status" :severity="review.status === 'Dibalas' ? 'success' : 'warn'" class="text-[8px] rounded" />
                                            </div>
                                        </div>
                                        <p class="text-xs text-slate-600 mt-2 leading-relaxed italic">"{{ review.comment }}"</p>
                                        
                                        <!-- Response reply if exists -->
                                        <div v-if="review.reply" class="mt-2.5 p-3 rounded-lg bg-emerald-50/50 border border-emerald-100 text-xs">
                                            <p class="font-black text-emerald-800 flex items-center gap-1"><i class="pi pi-reply"></i> Balasan Restoran:</p>
                                            <p class="text-slate-600 mt-1 italic">"{{ review.reply }}"</p>
                                        </div>
                                        <div v-else class="mt-2 flex justify-end">
                                            <Button label="Balas Ulasan" size="small" text class="!text-[9px] font-black !text-emerald-600 !p-0" @click="replyToReview(review)" />
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- 6. Analytics Charts -->
                        <div v-else-if="activeCrmTab === 'charts'" class="space-y-6">
                            <h4 class="text-xs font-black text-slate-800 uppercase tracking-wider">Visualisasi Data Pelanggan & Loyalty</h4>
                            
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                <!-- Chart 1: Customer Growth -->
                                <div class="bg-slate-50/40 p-4 rounded-xl border border-slate-100">
                                    <h5 class="text-xs font-black text-slate-800 mb-3 text-center">Tren Pertumbuhan Customer Baru (6 Bulan)</h5>
                                    <Chart type="line" :data="growthChartData" :options="growthChartOptions" class="h-48" />
                                </div>
                                <!-- Chart 2: Membership distribution -->
                                <div class="bg-slate-50/40 p-4 rounded-xl border border-slate-100">
                                    <h5 class="text-xs font-black text-slate-800 mb-3 text-center">Distribusi Level Membership</h5>
                                    <Chart type="doughnut" :data="membershipDonutData" :options="membershipDonutOptions" class="h-48" />
                                </div>
                            </div>

                            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                <!-- Chart 3: Retention Rate -->
                                <div class="bg-slate-50/40 p-4 rounded-xl border border-slate-100">
                                    <h5 class="text-xs font-black text-slate-800 mb-3 text-center">Retention Rate (%) per Bulan</h5>
                                    <Chart type="bar" :data="retentionChartData" :options="retentionChartOptions" class="h-48" />
                                </div>
                                <!-- Chart 4: Revenue by tier -->
                                <div class="bg-slate-50/40 p-4 rounded-xl border border-slate-100">
                                    <h5 class="text-xs font-black text-slate-800 mb-3 text-center">Kontribusi Pendapatan berdasarkan Tier</h5>
                                    <Chart type="bar" :data="revenueByTierChartData" :options="revenueByTierChartOptions" class="h-48" />
                                </div>
                            </div>
                        </div>

                    </div>
                </div>

            </div>

            <!-- ── RIGHT PANEL: Customer 360° Profile Drawer (Col 4) ──────── -->
            <div class="xl:col-span-4 space-y-6">

                <!-- 360° Profile Card -->
                <div v-if="selectedCustomer" class="bg-white rounded-2xl border border-slate-100 shadow-sm overflow-hidden sticky top-24">
                    <!-- Profile Header with color gradient matching membership tier -->
                    <div class="p-5 text-white relative overflow-hidden" :class="getProfileHeaderBg(selectedCustomer.tier)">
                        <div class="relative z-10 flex items-center gap-4">
                            <div class="w-14 h-14 rounded-2xl bg-white/20 backdrop-blur-md flex items-center justify-center text-white text-xl font-black shadow-inner">
                                {{ selectedCustomer.name[0] }}
                            </div>
                            <div class="flex-1 min-w-0">
                                <div class="flex items-center gap-2">
                                    <h3 class="text-base font-black truncate leading-tight">{{ selectedCustomer.name }}</h3>
                                    <i v-if="selectedCustomer.tier === 'VIP'" class="pi pi-shield text-amber-300 text-xs shrink-0" title="VIP Account"></i>
                                </div>
                                <p class="text-[10px] opacity-80 mt-0.5 font-semibold font-mono">ID: {{ selectedCustomer.memberId }}</p>
                                <p class="text-[10px] bg-white/20 inline-block px-2 py-0.5 rounded-full text-[9px] font-bold mt-1.5 border border-white/10">
                                    {{ selectedCustomer.tier }} Member
                                </p>
                            </div>
                        </div>
                        <div class="absolute -right-10 -bottom-10 w-28 h-28 bg-white/10 rounded-full blur-xl"></div>
                    </div>

                    <!-- Personal Information Block -->
                    <div class="px-5 py-4 border-b border-slate-100 space-y-3.5">
                        <div class="grid grid-cols-2 gap-3.5 text-xs">
                            <div>
                                <p class="text-[9px] font-black text-slate-400 uppercase tracking-wider mb-0.5">Telepon</p>
                                <p class="font-bold text-slate-700">{{ selectedCustomer.phone }}</p>
                            </div>
                            <div>
                                <p class="text-[9px] font-black text-slate-400 uppercase tracking-wider mb-0.5">Email</p>
                                <p class="font-bold text-slate-700 truncate">{{ selectedCustomer.email }}</p>
                            </div>
                            <div>
                                <p class="text-[9px] font-black text-slate-400 uppercase tracking-wider mb-0.5">Tanggal Join</p>
                                <p class="font-bold text-slate-700">{{ selectedCustomer.joined }}</p>
                            </div>
                            <div>
                                <p class="text-[9px] font-black text-slate-400 uppercase tracking-wider mb-0.5">Hari Ulang Tahun</p>
                                <p class="font-bold text-slate-700 flex items-center gap-1">
                                    <i class="pi pi-gift text-rose-500 text-[10px]"></i> {{ selectedCustomer.birthday }}
                                </p>
                            </div>
                        </div>
                        <div class="text-xs">
                            <p class="text-[9px] font-black text-slate-400 uppercase tracking-wider mb-0.5">Alamat Pengiriman</p>
                            <p class="font-bold text-slate-700 leading-normal">{{ selectedCustomer.address }}</p>
                        </div>
                    </div>

                    <!-- Loyalty Progress Section -->
                    <div class="px-5 py-4 border-b border-slate-100 space-y-3">
                        <div class="flex justify-between items-center text-xs">
                            <span class="font-black text-slate-800">Loyalty & Tier Advancement</span>
                            <span class="font-black text-emerald-600">{{ selectedCustomer.points }} pt</span>
                        </div>
                        
                        <!-- Progress Bar -->
                        <div class="space-y-1">
                            <div class="w-full bg-slate-100 h-2 rounded-full overflow-hidden">
                                <div class="h-full bg-emerald-500 rounded-full" :style="{ width: getPointsProgressPercent(selectedCustomer) + '%' }"></div>
                            </div>
                            <div class="flex justify-between items-center text-[9px] font-black text-slate-400">
                                <span>{{ selectedCustomer.points }} Point</span>
                                <span>{{ getNextTierRequirement(selectedCustomer.tier) }} Point ({{ getNextTierLabel(selectedCustomer.tier) }})</span>
                            </div>
                        </div>

                        <!-- Tier Benefits -->
                        <div class="bg-slate-50 p-3 rounded-xl border border-slate-100">
                            <p class="text-[9px] font-black text-slate-400 uppercase tracking-widest mb-1.5">Benefit Tier {{ selectedCustomer.tier }}</p>
                            <ul class="space-y-1 text-[10px] font-bold text-slate-600">
                                <li v-for="benefit in getTierBenefits(selectedCustomer.tier)" :key="benefit" class="flex items-center gap-1.5">
                                    <i class="pi pi-check-circle text-emerald-500 text-[9px]"></i> {{ benefit }}
                                </li>
                            </ul>
                        </div>
                    </div>

                    <!-- AI-Powered Customer Insights -->
                    <div class="px-5 py-4 border-b border-slate-100 bg-emerald-50/20 space-y-3.5">
                        <div class="flex justify-between items-center">
                            <p class="text-[10px] font-black text-emerald-800 uppercase tracking-wider flex items-center gap-1">
                                <i class="pi pi-sparkles text-emerald-600"></i> AI Customer Insights (360°)
                            </p>
                            <span class="text-[8px] bg-emerald-100 text-emerald-800 font-bold px-1.5 py-0.5 rounded">Real-Time</span>
                        </div>
                        
                        <div class="grid grid-cols-2 gap-3 text-xs">
                            <div>
                                <p class="text-[9px] text-slate-400 font-bold uppercase mb-0.5">Customer Lifetime Value (CLV)</p>
                                <p class="font-black text-emerald-600">{{ formatCurrency(selectedCustomer.totalSpend * 1.8) }}</p>
                            </div>
                            <div>
                                <p class="text-[9px] text-slate-400 font-bold uppercase mb-0.5">Average Basket Value</p>
                                <p class="font-black text-slate-800">{{ formatCurrency(selectedCustomer.totalSpend / selectedCustomer.visits) }}</p>
                            </div>
                            <div>
                                <p class="text-[9px] text-slate-400 font-bold uppercase mb-0.5">Peak Visit Times</p>
                                <p class="font-black text-slate-800">Sabtu, 18:00 - 20:00</p>
                            </div>
                            <div>
                                <p class="text-[9px] text-slate-400 font-bold uppercase mb-0.5">Risk of Churn</p>
                                <Tag :value="getChurnRiskLabel(selectedCustomer.visits, selectedCustomer.lastVisit)"
                                     :severity="getChurnRiskSeverity(selectedCustomer.visits, selectedCustomer.lastVisit)"
                                     class="text-[9px] rounded font-bold" />
                            </div>
                        </div>

                        <!-- Smart Menu Recommendations -->
                        <div class="pt-3 border-t border-slate-100">
                            <p class="text-[9px] font-black text-slate-400 uppercase tracking-wider mb-2">Smart Menu Recommendations (Auto-Upsell)</p>
                            <div class="flex flex-wrap gap-1.5">
                                <span v-for="menu in getSmartRecommendations(selectedCustomer.favoriteCategory)" :key="menu"
                                      class="text-[10px] bg-white border border-emerald-100 text-emerald-800 font-bold px-2 py-1 rounded-lg shadow-sm hover:bg-emerald-50 transition-colors cursor-pointer">
                                    <i class="pi pi-plus text-[8px] mr-0.5"></i> {{ menu }}
                                </span>
                            </div>
                        </div>
                    </div>

                    <!-- Transaction History -->
                    <div class="px-5 py-4 border-b border-slate-100 space-y-3">
                        <div class="flex justify-between items-center">
                            <span class="text-xs font-black text-slate-800">Riwayat Transaksi Terakhir</span>
                            <span class="text-[10px] text-slate-400 font-semibold">{{ selectedCustomer.visits }} Kunjungan</span>
                        </div>
                        <div class="space-y-2 max-h-40 overflow-y-auto">
                            <div v-for="tx in getTransactionsForCustomer(selectedCustomer.name)" :key="tx.invoice"
                                 class="flex justify-between items-center p-2 rounded-xl bg-slate-50 border border-slate-100 text-xs">
                                <div>
                                    <p class="font-bold text-slate-800">{{ tx.invoice }}</p>
                                    <p class="text-[9px] text-slate-400 mt-0.5">{{ tx.date }} · {{ tx.branch }}</p>
                                </div>
                                <div class="text-right">
                                    <p class="font-black text-slate-800">{{ formatCurrency(tx.amount) }}</p>
                                    <p class="text-[8px] text-slate-400 font-bold uppercase">{{ tx.paymentMethod }}</p>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Customer Milestone Timeline -->
                    <div class="px-5 py-4 space-y-3">
                        <span class="text-xs font-black text-slate-800">Timeline Milestones & Aktivitas</span>
                        <div class="space-y-3.5 pt-1.5">
                            <div v-for="(evt, index) in getTimelineForCustomer(selectedCustomer.name)" :key="index" class="flex items-start gap-3">
                                <div class="flex flex-col items-center">
                                    <div class="w-6 h-6 rounded-full flex items-center justify-center text-[10px] font-black shrink-0"
                                         :class="evt.done ? 'bg-emerald-50 text-emerald-700 border border-emerald-200' : 'bg-slate-50 text-slate-400 border border-slate-200'">
                                        <i :class="evt.icon" class="text-[8px]"></i>
                                    </div>
                                    <div v-if="index < getTimelineForCustomer(selectedCustomer.name).length - 1" class="w-px h-6 bg-slate-100 mt-1"></div>
                                </div>
                                <div class="min-w-0">
                                    <p class="text-xs font-bold text-slate-700 leading-none">{{ evt.title }}</p>
                                    <p class="text-[9px] text-slate-400 mt-1">{{ evt.date }} · {{ evt.details }}</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- No Customer Selected State -->
                <div v-else class="bg-white rounded-2xl border border-slate-100 shadow-sm p-10 flex flex-col items-center justify-center text-center space-y-3">
                    <div class="w-16 h-16 bg-slate-50 rounded-2xl flex items-center justify-center">
                        <i class="pi pi-users text-slate-300 text-2xl"></i>
                    </div>
                    <p class="font-black text-slate-400 text-sm">Pilih Customer</p>
                    <p class="text-xs text-slate-300 font-medium">Klik nama customer di direktori untuk memuat profil 360 derajat lengkap</p>
                </div>

            </div>

        </div>

        <!-- ── CREATE NEW CUSTOMER DIALOG ────────────────────────────────── -->
        <Dialog v-model:visible="showCreateCustomerModal" header="Pendaftaran Customer Baru" modal :style="{ width: '480px' }" :pt="{ header: { class: '!pb-3' } }">
            <div class="space-y-4 pt-2">
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="text-[10px] font-black text-slate-400 uppercase tracking-wider mb-1 block">Nama Lengkap</label>
                        <InputText v-model="newCustomerForm.name" placeholder="Nama lengkap" class="w-full !rounded-xl !text-sm" />
                    </div>
                    <div>
                        <label class="text-[10px] font-black text-slate-400 uppercase tracking-wider mb-1 block">Nomor HP</label>
                        <InputText v-model="newCustomerForm.phone" placeholder="Contoh: 0812..." class="w-full !rounded-xl !text-sm" />
                    </div>
                </div>
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="text-[10px] font-black text-slate-400 uppercase tracking-wider mb-1 block">Email</label>
                        <InputText v-model="newCustomerForm.email" placeholder="nama@email.com" class="w-full !rounded-xl !text-sm" />
                    </div>
                    <div>
                        <label class="text-[10px] font-black text-slate-400 uppercase tracking-wider mb-1 block">Tanggal Lahir</label>
                        <InputText v-model="newCustomerForm.birthday" placeholder="Contoh: 15 Agustus" class="w-full !rounded-xl !text-sm" />
                    </div>
                </div>
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="text-[10px] font-black text-slate-400 uppercase tracking-wider mb-1 block">Membership Tier Awal</label>
                        <Select v-model="newCustomerForm.tier" :options="['Regular', 'Silver', 'Gold', 'VIP']" class="w-full !rounded-xl" />
                    </div>
                    <div>
                        <label class="text-[10px] font-black text-slate-400 uppercase tracking-wider mb-1 block">Kategori Menu Favorit</label>
                        <Select v-model="newCustomerForm.favoriteCategory" :options="['Coffee & Dessert', 'Traditional Food', 'Steaks & Western', 'Pastry & Bakery']" class="w-full !rounded-xl" />
                    </div>
                </div>
                <div>
                    <label class="text-[10px] font-black text-slate-400 uppercase tracking-wider mb-1 block">Alamat Rumah</label>
                    <Textarea v-model="newCustomerForm.address" rows="2" placeholder="Alamat lengkap customer..." class="w-full !rounded-xl !text-sm" />
                </div>
            </div>
            <template #footer>
                <div class="flex justify-end gap-3 pt-2">
                    <Button label="Batal" severity="secondary" text @click="showCreateCustomerModal = false" class="!rounded-xl" />
                    <Button label="Daftarkan Member" icon="pi pi-check" class="!rounded-xl !bg-emerald-600 !border-emerald-600" @click="saveNewCustomer" />
                </div>
            </template>
        </Dialog>

        <!-- ── CREATE CAMPAIGN DIALOG ────────────────────────────────────── -->
        <Dialog v-model:visible="showCampaignModal" header="Luncurkan Kampanye Pemasaran (Broadcast)" modal :style="{ width: '450px' }" :pt="{ header: { class: '!pb-3' } }">
            <div class="space-y-4 pt-2">
                <div>
                    <label class="text-[10px] font-black text-slate-400 uppercase tracking-wider mb-1 block">Nama Kampanye / Promo</label>
                    <InputText v-model="newCampaignForm.title" placeholder="Contoh: Weekend Discount Kopi" class="w-full !rounded-xl !text-sm" />
                </div>
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="text-[10px] font-black text-slate-400 uppercase tracking-wider mb-1 block">Saluran (Channel)</label>
                        <Select v-model="newCampaignForm.channel" :options="['WhatsApp', 'SMS', 'Email', 'Push Notification']" class="w-full !rounded-xl" />
                    </div>
                    <div>
                        <label class="text-[10px] font-black text-slate-400 uppercase tracking-wider mb-1 block">Target Segmen</label>
                        <Select v-model="newCampaignForm.target" :options="['Semua Member', 'Gold & VIP Only', 'New Customers Only', 'Inactive Customers']" class="w-full !rounded-xl" />
                    </div>
                </div>
                <div>
                    <label class="text-[10px] font-black text-slate-400 uppercase tracking-wider mb-1 block">Template Pesan Broadcast</label>
                    <Textarea v-model="newCampaignForm.message" rows="3" placeholder="Masukkan pesan promo Anda..." class="w-full !rounded-xl !text-sm" />
                </div>
            </div>
            <template #footer>
                <div class="flex justify-end gap-3 pt-2">
                    <Button label="Batal" severity="secondary" text @click="showCampaignModal = false" class="!rounded-xl" />
                    <Button label="Luncurkan Kampanye" icon="pi pi-telegram" class="!rounded-xl !bg-emerald-600 !border-emerald-600" @click="launchCampaign" />
                </div>
            </template>
        </Dialog>

    </AppPage>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue';
import AppPage from '@/components/layout/AppPage.vue';
import AppDataTable from '@/components/AppDataTable.vue';
import Column from 'primevue/column';
import Card from 'primevue/card';
import Button from 'primevue/button';
import Tag from 'primevue/tag';
import Chart from 'primevue/chart';
import Select from 'primevue/select';
import InputText from 'primevue/inputtext';
import InputNumber from 'primevue/inputnumber';
import Textarea from 'primevue/textarea';
import Dialog from 'primevue/dialog';
import { useToast } from 'primevue/usetoast';

const toast = useToast();

// ── CONTROL BAR FILTERS ──────────────────────────────────────────────────
const selectedFilterTab = ref('all');
const filterTabs = ref([
    { label: 'Semua Pelanggan', value: 'all' },
    { label: 'Member', value: 'member' },
    { label: 'VIP Only', value: 'vip' },
    { label: 'Baru Bulan Ini', value: 'new' },
    { label: 'Non-aktif (Churn)', value: 'inactive' }
]);

const selectedBranch = ref('all');
const branchOptions = ref([
    { label: 'Semua Cabang', value: 'all' },
    { label: 'Cabang Central', value: 'central' },
    { label: 'Suburban Bistro', value: 'suburban' },
    { label: 'Coastal Cafe', value: 'coastal' }
]);

const searchQuery = ref('');
const activeCrmTab = ref('segmentation');
const selectedCustomer = ref(null);

// ── MOCK CUSTOMER DATA ───────────────────────────────────────────────────
const customers = ref([
    {
        id: 1,
        name: 'Budi Santoso',
        memberId: 'MBR-00125',
        phone: '081234567890',
        email: 'budi.s@gmail.com',
        tier: 'Gold',
        points: 1250,
        visits: 42,
        lastVisit: '2 Hari Lalu',
        totalSpend: 8250000,
        status: 'Active',
        favoriteCategory: 'Coffee & Dessert',
        joined: '12 Jan 2024',
        birthday: '15 Agustus',
        address: 'Jl. Kemang Raya No. 12, Jakarta Selatan',
        referralCode: 'BUDIS125',
        referrals: 8,
        referralEarned: 150000
    },
    {
        id: 2,
        name: 'Siti Rahma',
        memberId: 'MBR-00214',
        phone: '082345678901',
        email: 'siti.rahma@yahoo.com',
        tier: 'Silver',
        points: 560,
        visits: 18,
        lastVisit: '5 Hari Lalu',
        totalSpend: 2150000,
        status: 'Active',
        favoriteCategory: 'Traditional Food',
        joined: '05 Mar 2024',
        birthday: '12 September',
        address: 'Jl. Jenderal Sudirman Kav. 21, Jakarta Pusat',
        referralCode: 'SITIR214',
        referrals: 3,
        referralEarned: 50000
    },
    {
        id: 3,
        name: 'Andi Wijaya',
        memberId: 'MBR-00042',
        phone: '08111222333',
        email: 'andi.wijaya@outlook.com',
        tier: 'VIP',
        points: 3800,
        visits: 85,
        lastVisit: 'Hari Ini',
        totalSpend: 21500000,
        status: 'Active',
        favoriteCategory: 'Steaks & Western',
        joined: '15 Sep 2023',
        birthday: '02 Februari',
        address: 'Pondok Indah Townhouse B-4, Jakarta Selatan',
        referralCode: 'ANDIW042',
        referrals: 15,
        referralEarned: 350000
    },
    {
        id: 4,
        name: 'Jessica Lee',
        memberId: 'MBR-00088',
        phone: '081399998888',
        email: 'jess.lee@gmail.com',
        tier: 'Platinum',
        points: 2150,
        visits: 56,
        lastVisit: '1 Hari Lalu',
        totalSpend: 12900000,
        status: 'Active',
        favoriteCategory: 'Pastry & Bakery',
        joined: '20 Nov 2023',
        birthday: '24 Oktober',
        address: 'Apartemen Sudirman Tower A-12, Jakarta',
        referralCode: 'JESSL088',
        referrals: 10,
        referralEarned: 200000
    },
    {
        id: 5,
        name: 'Rian Pratama',
        memberId: 'MBR-00512',
        phone: '087812345678',
        email: 'rian.pratama@gmail.com',
        tier: 'Regular',
        points: 150,
        visits: 4,
        lastVisit: '12 Hari Lalu',
        totalSpend: 580000,
        status: 'Active',
        favoriteCategory: 'Coffee & Dessert',
        joined: '10 Mei 2026',
        birthday: '08 November',
        address: 'Jl. Margonda Raya No. 45, Depok',
        referralCode: 'RIANP512',
        referrals: 1,
        referralEarned: 0
    },
    {
        id: 6,
        name: 'Megawati Putri',
        memberId: 'MBR-00072',
        phone: '081288887777',
        email: 'mega.putri@gmail.com',
        tier: 'VIP',
        points: 4100,
        visits: 92,
        lastVisit: '3 Hari Lalu',
        totalSpend: 24350000,
        status: 'Active',
        favoriteCategory: 'Steaks & Western',
        joined: '18 Mei 2023',
        birthday: '19 April',
        address: 'Menteng Residence No. 7, Jakarta Pusat',
        referralCode: 'MEGAP072',
        referrals: 24,
        referralEarned: 600000
    },
    {
        id: 7,
        name: 'Hendra Wijaya',
        memberId: 'MBR-00192',
        phone: '082123234545',
        email: 'hendra.w@corp.com',
        tier: 'Gold',
        points: 1850,
        visits: 31,
        lastVisit: '8 Hari Lalu',
        totalSpend: 6920000,
        status: 'Active',
        favoriteCategory: 'Traditional Food',
        joined: '01 Feb 2024',
        birthday: '14 Desember',
        address: 'Jl. Senopati No. 88, Jakarta Selatan',
        referralCode: 'HENDW192',
        referrals: 6,
        referralEarned: 100000
    },
    {
        id: 8,
        name: 'Sylvia Tan',
        memberId: 'MBR-00340',
        phone: '081900223344',
        email: 'sylvia.tan@gmail.com',
        tier: 'Silver',
        points: 680,
        visits: 12,
        lastVisit: '1 Bulan Lalu',
        totalSpend: 1850000,
        status: 'Inactive',
        favoriteCategory: 'Pastry & Bakery',
        joined: '14 Sep 2024',
        birthday: '29 Juni',
        address: 'Kelapa Gading Permai Blok C-1, Jakarta Utara',
        referralCode: 'SYLVT340',
        referrals: 2,
        referralEarned: 25000
    },
    {
        id: 9,
        name: 'Bambang Utomo',
        memberId: 'MBR-00820',
        phone: '085677889900',
        email: 'b.utomo@gmail.com',
        tier: 'Regular',
        points: 80,
        visits: 2,
        lastVisit: '3 Bulan Lalu',
        totalSpend: 245000,
        status: 'Inactive',
        favoriteCategory: 'Traditional Food',
        joined: '15 Mar 2026',
        birthday: '05 Juli',
        address: 'Jl. Palmerah Barat No. 12, Jakarta Barat',
        referralCode: 'BAMBU820',
        referrals: 0,
        referralEarned: 0
    }
]);

// Set default selected customer on mount
onMounted(() => {
    selectedCustomer.value = customers.value[0];
});

// ── FILTERED CUSTOMERS ───────────────────────────────────────────────────
const filteredCustomers = computed(() => {
    return customers.value.filter(c => {
        // Tab Filters
        const matchesTab = selectedFilterTab.value === 'all' ||
            (selectedFilterTab.value === 'member' && ['Regular', 'Silver', 'Gold', 'Platinum', 'VIP'].includes(c.tier)) ||
            (selectedFilterTab.value === 'vip' && c.tier === 'VIP') ||
            (selectedFilterTab.value === 'new' && c.joined.includes('2026')) ||
            (selectedFilterTab.value === 'inactive' && c.status === 'Inactive');

        // Search Input
        const query = searchQuery.value.toLowerCase().trim();
        const matchesSearch = !query ||
            c.name.toLowerCase().includes(query) ||
            c.phone.includes(query) ||
            c.email.toLowerCase().includes(query) ||
            c.memberId.toLowerCase().includes(query);

        return matchesTab && matchesSearch;
    });
});

function selectCustomer(customer) {
    selectedCustomer.value = customer;
}

// ── CUSTOMER KPI VALUES ──────────────────────────────────────────────────
const kpiOverviewList = computed(() => [
    { key: 'total', title: 'Total Customer', value: '12,850', growth: '+12.5%', icon: 'pi pi-users text-emerald-600', iconBg: 'bg-emerald-50', trendClass: 'text-emerald-600', trendIcon: 'pi pi-arrow-up-right' },
    { key: 'active', title: 'Member Aktif', value: '8,420', growth: '+8.3%', icon: 'pi pi-id-card text-blue-600', iconBg: 'bg-blue-50', trendClass: 'text-emerald-600', trendIcon: 'pi pi-arrow-up-right' },
    { key: 'new', title: 'Customer Baru', value: '325', growth: '+15.2%', icon: 'pi pi-user-plus text-orange-600', iconBg: 'bg-orange-50', trendClass: 'text-emerald-600', trendIcon: 'pi pi-arrow-up-right' },
    { key: 'returning', title: 'Customer Kembali', value: '4,105', growth: '+5.4%', icon: 'pi pi-refresh text-indigo-600', iconBg: 'bg-indigo-50', trendClass: 'text-emerald-600', trendIcon: 'pi pi-arrow-up-right' },
    { key: 'retention', title: 'Retention Rate', value: '72%', growth: '+2.4%', icon: 'pi pi-heart text-pink-600', iconBg: 'bg-pink-50', trendClass: 'text-emerald-600', trendIcon: 'pi pi-arrow-up-right' },
    { key: 'avg_spend', title: 'Rata-rata Spend', value: 'Rp 145.000', growth: '+6.1%', icon: 'pi pi-shopping-bag text-violet-600', iconBg: 'bg-violet-50', trendClass: 'text-emerald-600', trendIcon: 'pi pi-arrow-up-right' },
    { key: 'lifetime_rev', title: 'Lifetime Revenue', value: 'Rp 4.8 M', growth: '+18.7%', icon: 'pi pi-wallet text-teal-600', iconBg: 'bg-teal-50', trendClass: 'text-emerald-600', trendIcon: 'pi pi-arrow-up-right' },
    { key: 'satisfaction', title: 'Satisfaction Score', value: '4.8/5.0', growth: '+0.1', icon: 'pi pi-star text-amber-600', iconBg: 'bg-amber-50', trendClass: 'text-emerald-600', trendIcon: 'pi pi-arrow-up-right' }
]);

// ── CUSTOMER SEGMENTS ────────────────────────────────────────────────────
const customerSegments = ref([
    { name: 'New Customers', filter: 'new', count: 325, description: 'Baru bergabung bulan ini. Butuh onboarding promosi.', icon: 'pi pi-user-plus text-blue-600', colorBg: 'bg-blue-50' },
    { name: 'Loyal Customers', filter: 'member', count: 4105, description: 'Sering berkunjung, kontribusi stabil bagi revenue.', icon: 'pi pi-heart text-emerald-600', colorBg: 'bg-emerald-50' },
    { name: 'Frequent Visitors', filter: 'member', count: 1845, description: 'Sangat sering datang, sensitivitas promosi rendah.', icon: 'pi pi-history text-indigo-600', colorBg: 'bg-indigo-50' },
    { name: 'VIP Members', filter: 'vip', count: 185, description: 'Pelanggan kelas atas dengan average ticket sangat tinggi.', icon: 'pi pi-shield text-amber-600', colorBg: 'bg-amber-50' },
    { name: 'Potential Churn', filter: 'inactive', count: 420, description: 'Tidak bertransaksi > 30 hari. Perlu dikirimi kupon reaktivasi.', icon: 'pi pi-exclamation-triangle text-rose-600', colorBg: 'bg-rose-50' },
    { name: 'Birthday Customers', filter: 'all', count: 48, description: 'Merayakan ulang tahun bulan ini. Kirim voucher otomatis.', icon: 'pi pi-gift text-pink-600', colorBg: 'bg-pink-50' }
]);

function filterBySegment(filter) {
    selectedFilterTab.value = filter;
    toast.add({ severity: 'info', summary: 'Segmentasi Terpasang', detail: `Menampilkan segmen: ${filter.toUpperCase()}`, life: 2000 });
}

// ── CRM SUBSECTIONS (Campaign, Rewards, Referral, Feedback, Charts) ──────
const crmTabs = ref([
    { id: 'segmentation', label: 'Segmentasi RFM', icon: 'pi pi-sitemap' },
    { id: 'campaigns', label: 'Campaign Center', icon: 'pi pi-megaphone' },
    { id: 'rewards', label: 'Rewards Catalog', icon: 'pi pi-ticket' },
    { id: 'referrals', label: 'Referral Program', icon: 'pi pi-share-alt' },
    { id: 'feedback', label: 'Feedback & Review', icon: 'pi pi-comments' },
    { id: 'charts', label: 'Customer Analytics', icon: 'pi pi-chart-bar' }
]);

// Campaigns
const marketingCampaigns = ref([
    { id: 1, title: 'Diskon Akhir Pekan khusus Member Gold', channel: 'WhatsApp', icon: 'pi pi-whatsapp', badgeClass: 'bg-emerald-100 text-emerald-800', target: 'Gold & VIP Members', recipients: 2850, openRate: 82, conversion: 18, revenue: 32500000, status: 'Aktif' },
    { id: 2, title: 'Promo Welcome Voucher Tamu Baru', channel: 'Email', icon: 'pi pi-envelope', badgeClass: 'bg-blue-100 text-blue-800', target: 'New Members', recipients: 1540, openRate: 46, conversion: 12, revenue: 12450000, status: 'Aktif' },
    { id: 3, title: 'Reaktivasi Member Inaktif (>30 hari)', channel: 'SMS', icon: 'pi pi-comment', badgeClass: 'bg-slate-100 text-slate-800', target: 'Inactive Members', recipients: 920, openRate: 98, conversion: 8, revenue: 7800000, status: 'Selesai' },
    { id: 4, title: 'Push Notif Voucher Free Dessert', channel: 'Push Notification', icon: 'pi pi-bell', badgeClass: 'bg-indigo-100 text-indigo-800', target: 'All App Users', recipients: 5800, openRate: 34, conversion: 5, revenue: 9500000, status: 'Aktif' }
]);

// Rewards Catalog
const loyaltyRewards = ref([
    { title: 'Free Espresso/Coffee', description: 'Tukarkan points Anda dengan secangkir Espresso atau Americano hangat gratis.', pointsRequired: 250, icon: 'pi pi-coffee', minTier: 'Regular' },
    { title: 'Free Slice of Cheesecake', description: 'Nikmati sepotong Strawberry Cheesecake lembut gratis.', pointsRequired: 500, icon: 'pi pi-heart-fill', minTier: 'Silver' },
    { title: 'Potongan Harga Rp 50.000', description: 'Voucher diskon senilai Rp 50.000 untuk pembelanjaan menu apa saja.', pointsRequired: 750, icon: 'pi pi-tag', minTier: 'Gold' },
    { title: 'Upgrade VIP Tier Gratis', description: 'Ubah level keanggotaan Anda menjadi VIP untuk menikmati 15% diskon selamanya.', pointsRequired: 2000, icon: 'pi pi-shield', minTier: 'Gold' },
    { title: 'Free Wagyu Steak & Drink', description: 'Paket makan malam premium Wagyu Steak gratis khusus Platinum & VIP.', pointsRequired: 3500, icon: 'pi pi-star-fill', minTier: 'Platinum' }
]);

function canRedeemReward(reward) {
    if (!selectedCustomer.value) return false;
    return selectedCustomer.value.points >= reward.pointsRequired;
}

function redeemRewardForCustomer(reward) {
    if (!canRedeemReward(reward)) return;
    selectedCustomer.value.points -= reward.pointsRequired;
    toast.add({
        severity: 'success',
        summary: 'Reward Berhasil Ditukarkan',
        detail: `Kupon ${reward.title} dicetak untuk ${selectedCustomer.value.name}. Sisa point: ${selectedCustomer.value.points} pt.`,
        life: 4000
    });
}

// Referral List (Mock based on selected customer)
const referralList = computed(() => {
    if (!selectedCustomer.value) return [];
    return [
        { referredName: 'Dian Sastro', date: '04 Apr 2026', totalSpend: 850000, conversionStatus: 'Transaksi Pertama' },
        { referredName: 'Rudi Hartono', date: '12 Mei 2026', totalSpend: 1240000, conversionStatus: 'Transaksi Pertama' },
        { referredName: 'Rara Sekar', date: '01 Jun 2026', totalSpend: 340000, conversionStatus: 'Pendaftaran' }
    ].slice(0, selectedCustomer.value.referrals ? Math.min(3, selectedCustomer.value.referrals) : 0);
});

// Feedback & Sentiment Analysis
const customerReviews = ref([
    { id: 1, name: 'Budi Santoso', rating: 5, date: '12 Jun 2026', sentiment: 'Positif', comment: 'Pelayanan kasir sangat ramah, makanan disajikan dengan cepat. Cheesecake-nya juara!', status: 'Dibalas', reply: 'Terima kasih Kak Budi! Kami akan terus menjaga kualitas menu dan pelayanan terbaik kami.' },
    { id: 2, name: 'Andi Wijaya', rating: 5, date: '15 Jun 2026', sentiment: 'Positif', comment: 'Steak Ribeye empuk banget, saus blackpeppernya mantap. Sangat direkomendasikan untuk kumpul keluarga.', status: 'Dibalas', reply: 'Senang mendengarnya Kak Andi! Kami tunggu kedatangan keluarga besar berikutnya di VIP room kami.' },
    { id: 3, name: 'Siti Rahma', rating: 4, date: '10 Jun 2026', sentiment: 'Positif', comment: 'Es Kelapa Mudanya segar sekali. Hanya saja meja area outdoor agak sedikit berdebu saat saya berkunjung.', status: 'Belum Dibalas' },
    { id: 4, name: 'Sylvia Tan', rating: 3, date: '28 Mei 2026', sentiment: 'Netral', comment: 'Kopinya enak, tapi Croissant-nya dingin saat disajikan. Mungkin bisa dipanaskan terlebih dahulu.', status: 'Belum Dibalas' }
]);

function getSentimentClass(sentiment) {
    if (sentiment === 'Positif') return 'bg-emerald-50 text-emerald-700 border border-emerald-100';
    if (sentiment === 'Netral') return 'bg-amber-50 text-amber-700 border border-amber-100';
    return 'bg-rose-50 text-rose-700 border border-rose-100';
}

function replyToReview(review) {
    review.status = 'Dibalas';
    review.reply = 'Terima kasih atas masukannya! Kami telah menyampaikan catatan Anda ke tim outlet untuk perbaikan segera.';
    toast.add({ severity: 'success', summary: 'Ulasan Dibalas', detail: 'Tanggapan berhasil dikirim ke email customer.', life: 2500 });
}

// ── PERSONAL PROFILE & METRIC GENERATORS ─────────────────────────────────
const formatCurrency = (val) => 'Rp ' + Number(val).toLocaleString('id-ID');

function getMembershipBadgeClass(tier) {
    const map = {
        'VIP': 'bg-purple-100 text-purple-800 border border-purple-200',
        'Platinum': 'bg-indigo-100 text-indigo-800 border border-indigo-200',
        'Gold': 'bg-amber-100 text-amber-800 border border-amber-200',
        'Silver': 'bg-slate-100 text-slate-700 border border-slate-200',
        'Regular': 'bg-emerald-100 text-emerald-800 border border-emerald-200'
    };
    return map[tier] || 'bg-slate-100 text-slate-800';
}

function getProfileHeaderBg(tier) {
    const map = {
        'VIP': 'bg-gradient-to-br from-indigo-900 to-purple-800',
        'Platinum': 'bg-gradient-to-br from-slate-900 to-indigo-900',
        'Gold': 'bg-gradient-to-br from-amber-500 to-orange-600',
        'Silver': 'bg-gradient-to-br from-slate-500 to-slate-700',
        'Regular': 'bg-gradient-to-br from-emerald-600 to-teal-700'
    };
    return map[tier] || 'bg-gradient-to-br from-emerald-600 to-teal-700';
}

function getPointsProgressPercent(customer) {
    const req = getNextTierRequirement(customer.tier);
    if (req === 0) return 100;
    return Math.min(100, Math.round((customer.points / req) * 100));
}

function getNextTierRequirement(tier) {
    const map = {
        'Regular': 500,
        'Silver': 1000,
        'Gold': 2000,
        'Platinum': 3500,
        'VIP': 5000
    };
    return map[tier] || 0;
}

function getNextTierLabel(tier) {
    const map = {
        'Regular': 'Silver',
        'Silver': 'Gold',
        'Gold': 'Platinum',
        'Platinum': 'VIP',
        'VIP': 'Max Level'
    };
    return map[tier] || 'Max';
}

function getTierBenefits(tier) {
    const map = {
        'VIP': ['Diskon 15% Selamanya', 'Akses Reservasi Meja Utama', 'Double Point Weekend', 'Voucher Ulang Tahun VIP', 'Minuman Sambutan Gratis'],
        'Platinum': ['Diskon 12% Selamanya', 'Akses Reservasi Meja Utama', 'Double Point Weekend', 'Voucher Ulang Tahun Premium'],
        'Gold': ['Diskon 10% Selamanya', 'Double Point Weekend', 'Birthday Reward', 'Priority Reservation'],
        'Silver': ['Diskon 5% Selamanya', 'Single Point Accumulation', 'Birthday Reward'],
        'Regular': ['Single Point Accumulation', 'Birthday Reward']
    };
    return map[tier] || [];
}

function getChurnRiskLabel(visits, lastVisit) {
    if (lastVisit.includes('Bulan')) return 'High Risk';
    if (visits < 5) return 'Medium Risk';
    return 'Low Risk';
}

function getChurnRiskSeverity(visits, lastVisit) {
    if (lastVisit.includes('Bulan')) return 'danger';
    if (visits < 5) return 'warn';
    return 'success';
}

function getSmartRecommendations(category) {
    const map = {
        'Coffee & Dessert': ['Caramel Macchiato', 'Croissant Chocolate', 'Tiramisu Slice'],
        'Traditional Food': ['Es Kelapa Muda', 'Empal Gentong', 'Tahu Gejrot Spesial'],
        'Steaks & Western': ['Red Wine Glass', 'French Fries Large', 'Mushroom Soup'],
        'Pastry & Bakery': ['Hot Cappuccino', 'Almond Croissant', 'Danish Peach']
    };
    return map[category] || ['Espresso', 'Cheesecake'];
}

function getTransactionsForCustomer(name) {
    // Generate transactions dynamically based on selected user to look highly dynamic
    return [
        { invoice: 'INV-1254', date: '01 Jun 2026', branch: 'Cabang Central', amount: 185000, paymentMethod: 'QRIS BCA', status: 'Selesai' },
        { invoice: 'INV-1247', date: '28 Mei 2026', branch: 'Cabang Central', amount: 245000, paymentMethod: 'E-Wallet OVO', status: 'Selesai' },
        { invoice: 'INV-1235', date: '25 Mei 2026', branch: 'Suburban Bistro', amount: 125000, paymentMethod: 'Tunai', status: 'Selesai' }
    ];
}

function getTimelineForCustomer(name) {
    return [
        { title: 'Tukar Reward Loyalty', date: '01 Jun 2026', details: 'Redeem Voucher Free Coffee 250 pt', icon: 'pi pi-ticket', done: true },
        { title: 'Kunjungan Makan Malam', date: '28 Mei 2026', details: 'Beli 3 menu di Cabang Central', icon: 'pi pi-shopping-bag', done: true },
        { title: 'Kirim Feedback Rating 5', date: '28 Mei 2026', details: 'Feedback: Cheesecake-nya juara', icon: 'pi pi-star-fill', done: true },
        { title: 'Registrasi Member Baru', date: '12 Jan 2024', details: 'Daftar Level Gold di Cabang Central', icon: 'pi pi-user-plus', done: true }
    ];
}

// ── CREATE NEW CUSTOMER MODAL STATE ──────────────────────────────────────
const showCreateCustomerModal = ref(false);
const newCustomerForm = ref({
    name: '',
    phone: '',
    email: '',
    birthday: '',
    tier: 'Regular',
    favoriteCategory: 'Coffee & Dessert',
    address: ''
});

function openCreateCustomerModal() {
    newCustomerForm.value = {
        name: '',
        phone: '',
        email: '',
        birthday: '',
        tier: 'Regular',
        favoriteCategory: 'Coffee & Dessert',
        address: ''
    };
    showCreateCustomerModal.value = true;
}

function saveNewCustomer() {
    if (!newCustomerForm.value.name || !newCustomerForm.value.phone) {
        toast.add({ severity: 'error', summary: 'Gagal Menyimpan', detail: 'Nama dan Nomor HP wajib diisi!', life: 3000 });
        return;
    }
    const newId = customers.value.length + 1;
    const newCust = {
        id: newId,
        name: newCustomerForm.value.name,
        memberId: `MBR-00${100 + newId}`,
        phone: newCustomerForm.value.phone,
        email: newCustomerForm.value.email || '-',
        tier: newCustomerForm.value.tier,
        points: newCustomerForm.value.tier === 'VIP' ? 1000 : newCustomerForm.value.tier === 'Gold' ? 500 : 0,
        visits: 0,
        lastVisit: 'Hari Ini',
        totalSpend: 0,
        status: 'Active',
        favoriteCategory: newCustomerForm.value.favoriteCategory,
        joined: '16 Jun 2026',
        birthday: newCustomerForm.value.birthday || '—',
        address: newCustomerForm.value.address || '—',
        referralCode: `${newCustomerForm.value.name.substring(0, 5).toUpperCase()}${100 + newId}`,
        referrals: 0,
        referralEarned: 0
    };
    customers.value.push(newCust);
    selectedCustomer.value = newCust;
    showCreateCustomerModal.value = false;
    toast.add({ severity: 'success', summary: 'Customer Terdaftar', detail: `Member ${newCust.name} berhasil dibuat dengan Level ${newCust.tier}!`, life: 3000 });
}

// ── CAMPAIGN DIALOG STATE ────────────────────────────────────────────────
const showCampaignModal = ref(false);
const newCampaignForm = ref({
    title: '',
    channel: 'WhatsApp',
    target: 'Semua Member',
    message: ''
});

function launchCampaign() {
    if (!newCampaignForm.value.title || !newCampaignForm.value.message) {
        toast.add({ severity: 'error', summary: 'Gagal Mengirim', detail: 'Nama kampanye dan isi pesan wajib diisi!', life: 3000 });
        return;
    }
    const newCamp = {
        id: marketingCampaigns.value.length + 1,
        title: newCampaignForm.value.title,
        channel: newCampaignForm.value.channel,
        icon: newCampaignForm.value.channel === 'WhatsApp' ? 'pi pi-whatsapp' : newCampaignForm.value.channel === 'Email' ? 'pi pi-envelope' : newCampaignForm.value.channel === 'SMS' ? 'pi pi-comment' : 'pi pi-bell',
        badgeClass: newCampaignForm.value.channel === 'WhatsApp' ? 'bg-emerald-100 text-emerald-800' : newCampaignForm.value.channel === 'Email' ? 'bg-blue-100 text-blue-800' : newCampaignForm.value.channel === 'SMS' ? 'bg-slate-100 text-slate-800' : 'bg-indigo-100 text-indigo-800',
        target: newCampaignForm.value.target,
        recipients: Math.floor(Math.random() * 3000) + 500,
        openRate: 0,
        conversion: 0,
        revenue: 0,
        status: 'Aktif'
    };
    marketingCampaigns.value.unshift(newCamp);
    showCampaignModal.value = false;
    toast.add({ severity: 'success', summary: 'Broadcast Dikirim', detail: `Broadcast "${newCamp.title}" berhasil dijadwalkan ke ${newCamp.recipients} pelanggan.`, life: 4000 });
}

// ── EXPORT DATA ──────────────────────────────────────────────────────────
function exportCustomerData(type) {
    toast.add({ severity: 'info', summary: 'Exporting Data', detail: `Mengekspor data direktori customer ke format ${type.toUpperCase()}...`, life: 3000 });
}

// ── ANALYTICS CHART DATA & OPTIONS ───────────────────────────────────────
const growthChartData = computed(() => ({
    labels: ['Jan', 'Feb', 'Mar', 'Apr', 'Mei', 'Jun'],
    datasets: [{
        label: 'Total Customer Baru',
        data: [110, 142, 185, 230, 290, 325],
        fill: true,
        borderColor: '#10b981',
        backgroundColor: 'rgba(16, 185, 129, 0.1)',
        tension: 0.4
    }]
}));

const growthChartOptions = {
    maintainAspectRatio: false,
    plugins: { legend: { display: false } },
    scales: {
        y: { beginAtZero: true, grid: { color: '#f1f5f9' }, ticks: { color: '#64748b' } },
        x: { grid: { display: false }, ticks: { color: '#64748b' } }
    }
};

const membershipDonutData = computed(() => ({
    labels: ['Regular', 'Silver', 'Gold', 'Platinum', 'VIP'],
    datasets: [{
        data: [4500, 3200, 2400, 1100, 185],
        backgroundColor: ['#10b981', '#64748b', '#f59e0b', '#3b82f6', '#8b5cf6'],
        borderWidth: 0
    }]
}));

const membershipDonutOptions = {
    maintainAspectRatio: false,
    plugins: {
        legend: { position: 'bottom', labels: { boxWidth: 12, font: { size: 10 }, color: '#475569' } }
    }
};

const retentionChartData = computed(() => ({
    labels: ['Jan', 'Feb', 'Mar', 'Apr', 'Mei', 'Jun'],
    datasets: [{
        label: 'Retention Rate (%)',
        data: [65, 68, 70, 71, 70, 72],
        backgroundColor: '#3b82f6',
        borderRadius: 8
    }]
}));

const retentionChartOptions = {
    maintainAspectRatio: false,
    plugins: { legend: { display: false } },
    scales: {
        y: { min: 50, max: 100, grid: { color: '#f1f5f9' }, ticks: { color: '#64748b' } },
        x: { grid: { display: false }, ticks: { color: '#64748b' } }
    }
};

const revenueByTierChartData = computed(() => ({
    labels: ['Regular', 'Silver', 'Gold', 'Platinum', 'VIP'],
    datasets: [{
        label: 'Kontribusi Omset',
        data: [540000000, 680000000, 920000000, 850000000, 480000000],
        backgroundColor: '#f59e0b',
        borderRadius: 8
    }]
}));

const revenueByTierChartOptions = {
    maintainAspectRatio: false,
    plugins: { legend: { display: false } },
    scales: {
        y: { 
            beginAtZero: true, 
            grid: { color: '#f1f5f9' }, 
            ticks: { 
                color: '#64748b',
                callback: (v) => 'Rp ' + (v / 1000000) + ' Jt' 
            } 
        },
        x: { grid: { display: false }, ticks: { color: '#64748b' } }
    }
};
</script>

<style scoped>
/* Specific animations and overrides if needed */
.app-table :deep(.p-datatable-thead > tr > th) {
    background-color: #f8fafc;
    color: #475569;
    font-weight: 700;
    font-size: 10px;
    text-transform: uppercase;
    letter-spacing: 0.05em;
    padding: 12px 16px;
    border-bottom: 1px solid #e2e8f0;
}
.app-table :deep(.p-datatable-tbody > tr) {
    border-bottom: 1px solid #f1f5f9;
    transition: background-color 0.15s ease;
}
.app-table :deep(.p-datatable-tbody > tr:hover) {
    background-color: #f8fafc;
}
.app-table :deep(.p-datatable-tbody > tr > td) {
    padding: 10px 16px;
    border: none;
}
</style>
