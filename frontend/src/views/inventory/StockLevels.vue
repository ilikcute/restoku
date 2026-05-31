<template>
    <AppPage
        :title="$t('inventory.stock_levels')"
        :breadcrumb="[$t('common.inventory'), $t('inventory.stock_levels')]"
    >
        <div class="space-y-1">
            <!-- Summary Cards -->
            <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
                <div
                    class="bg-white p-6 rounded-2xl shadow-sm border border-slate-100 flex items-center gap-4"
                >
                    <div
                        class="w-12 h-12 bg-primary-50 text-primary-600 rounded-xl flex items-center justify-center"
                    >
                        <i class="pi pi-box text-2xl"></i>
                    </div>
                    <div>
                        <p class="text-sm text-slate-500 font-medium">
                            {{ $t("inventory.total_tracked") }}
                        </p>
                        <h3 class="text-2xl font-bold text-slate-800">
                            {{ rows.length }}
                        </h3>
                    </div>
                </div>
                <div
                    class="bg-white p-6 rounded-2xl shadow-sm border border-slate-100 flex items-center gap-4"
                >
                    <div
                        class="w-12 h-12 bg-green-50 text-green-600 rounded-xl flex items-center justify-center"
                    >
                        <i class="pi pi-database text-2xl"></i>
                    </div>
                    <div>
                        <p class="text-sm text-slate-500 font-medium">
                            {{ $t("inventory.total_accumulation") }}
                        </p>
                        <h3 class="text-2xl font-bold text-slate-800">
                            {{ totalStock }}
                        </h3>
                    </div>
                </div>
                <div
                    class="bg-white p-6 rounded-2xl shadow-sm border border-slate-100 flex items-center gap-4"
                >
                    <div
                        class="w-12 h-12 bg-red-50 text-red-600 rounded-xl flex items-center justify-center"
                    >
                        <i class="pi pi-arrow-down text-2xl"></i>
                    </div>
                    <div>
                        <p class="text-sm text-slate-500 font-medium">
                            {{ $t("inventory.low_stock_count") }}
                        </p>
                        <h3 class="text-2xl font-bold text-slate-800">
                            {{ lowStockCount }}
                        </h3>
                    </div>
                </div>
                <div
                    class="bg-white p-6 rounded-2xl shadow-sm border border-slate-100 flex items-center gap-4"
                >
                    <div
                        class="w-12 h-12 bg-orange-50 text-orange-600 rounded-xl flex items-center justify-center"
                    >
                        <i class="pi pi-arrow-up text-2xl"></i>
                    </div>
                    <div>
                        <p class="text-sm text-slate-500 font-medium">
                            {{ $t("inventory.overstock_count") }}
                        </p>
                        <h3 class="text-2xl font-bold text-slate-800">
                            {{ overStockCount }}
                        </h3>
                    </div>
                </div>
            </div>

            <!-- Filters -->
            <div
                class="flex gap-3 bg-slate-50/50 p-1 rounded-2xl border border-slate-100"
            >
                <InputText
                    v-model="query"
                    :placeholder="$t('inventory.search_product')"
                    class="flex-1 !rounded-xl"
                />
                <Select
                    v-model="selectedCategoryId"
                    :options="categories"
                    optionLabel="name"
                    optionValue="id"
                    placeholder="Pilih Kategori"
                    class="flex-1 !rounded-xl"
                    showClear
                />
                <Button
                    icon="pi pi-refresh"
                    severity="secondary"
                    outlined
                    @click="load"
                    :loading="loading"
                    v-tooltip="'Refresh Data'"
                    class="flex-none w-12 !rounded-xl !bg-white !border !border-slate-200 !text-slate-500 hover:!bg-slate-100 shadow-sm"
                />
            </div>

            <!-- Data Table -->
            <div
                class="bg-white rounded-2xl shadow-sm border border-slate-100 overflow-hidden"
            >
                <AppDataTable
                    framed
                    :value="filteredRows"
                    :loading="loading"
                    paginator
                    v-model:first="first"
                    :rows="10"
                    responsiveLayout="stack"
                    breakpoint="960px"
                    class="app-table"
                    stripedRows
                >
                    <template #empty>
                        <div class="text-center py-8 text-slate-400">
                            <i class="pi pi-inbox text-4xl mb-2"></i>
                            <p>Data stok tidak ditemukan.</p>
                        </div>
                    </template>

                    <Column header="No" class="w-16 text-center">
                        <template #body="slotProps">
                            {{ slotProps.index + first + 1 }}
                        </template>
                    </Column>

                    <Column
                        :header="$t('common.name')"
                        field="name"
                        sortable
                        class="font-semibold"
                    />

                    <Column
                        :header="$t('common.category')"
                        field="category"
                        sortable
                    >
                        <template #body="{ data }">
                            <span
                                class="px-2 py-1 bg-slate-100 text-slate-600 rounded-lg text-xs font-bold uppercase tracking-wider"
                            >
                                {{ data.category }}
                            </span>
                        </template>
                    </Column>

                    <Column :header="$t('common.unit')" field="unit" sortable />

                    <Column
                        :header="$t('inventory.current')"
                        field="current_stock"
                        sortable
                        class="text-center"
                    >
                        <template #body="{ data }">
                            <span
                                class="text-lg font-bold"
                                :class="
                                    data.is_low_stock
                                        ? 'text-red-600'
                                        : 'text-slate-800'
                                "
                            >
                                {{ data.current_stock }}
                            </span>
                        </template>
                    </Column>

                    <Column
                        :header="$t('inventory.minimum')"
                        field="minimum_stock"
                        class="text-center text-slate-400"
                    />
                    <Column
                        :header="$t('inventory.maximum')"
                        field="maximum_stock"
                        class="text-center text-slate-400"
                    />

                    <Column header="Status" class="text-center">
                        <template #body="{ data }">
                            <Tag
                                v-if="data.is_low_stock"
                                :value="$t('inventory.low')"
                                severity="danger"
                                rounded
                            />
                            <Tag
                                v-else-if="data.is_over_stock"
                                :value="$t('inventory.overstock')"
                                severity="warn"
                                rounded
                            />
                            <Tag
                                v-else
                                :value="$t('inventory.safe')"
                                severity="success"
                                rounded
                            />
                        </template>
                    </Column>
                </AppDataTable>
            </div>
        </div>
    </AppPage>
</template>

<script setup>
import { computed, ref, onMounted, watch } from "vue";
import AppDataTable from "@/components/AppDataTable.vue";
import StatusBadge from "@/components/StatusBadge.vue";
import { useI18n } from "vue-i18n";
import { inventoryApi } from "@/api/inventory";
import { categoryApi } from "@/api/master";
import AppPage from "@/components/layout/AppPage.vue";
import Column from "primevue/column";
import InputText from "primevue/inputtext";
import Select from "primevue/select";
import Tag from "primevue/tag";
import Button from "primevue/button";

const { t: $t } = useI18n();

const rows = ref([]);
const categories = ref([]);
const loading = ref(false);
const totalStock = ref(0);
const query = ref("");
const selectedCategoryId = ref(null);
const first = ref(0);

const filteredRows = computed(() => {
    return rows.value.filter((row) => {
        const matchSearch = row.name
            ?.toLowerCase()
            .includes(query.value.toLowerCase());
        return matchSearch;
    });
});

const lowStockCount = computed(() => {
    return rows.value.filter((r) => r.is_low_stock).length;
});

const overStockCount = computed(() => {
    return rows.value.filter((r) => r.is_over_stock).length;
});

async function loadCategories() {
    try {
        const response = await categoryApi.getAll();
        categories.value = response?.data?.data || [];
    } catch (error) {
        console.error("Failed to load categories", error);
    }
}

async function load() {
    loading.value = true;
    try {
        const params = {};
        if (selectedCategoryId.value) {
            params.category_id = selectedCategoryId.value;
        }

        const response = await inventoryApi.getStocks(params);
        // Handle the new response structure
        rows.value = response?.data?.data?.data || [];
        totalStock.value = response?.data?.data?.total_stock || 0;
    } finally {
        loading.value = false;
    }
}

// Watch for category change to reload from server
watch(selectedCategoryId, () => {
    load();
});

onMounted(() => {
    loadCategories();
    load();
});
</script>
