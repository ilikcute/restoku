<template>
    <AppPage
        :title="$t('inventory.audit_report')"
        :breadcrumb="[$t('common.inventory'), $t('inventory.movements')]"
    >
        <div class="space-y-1">
            <!-- Filters -->
            <div
                class="flex gap-3 bg-slate-50/50 p-1 rounded-2xl border border-slate-100"
            >
                <!-- Date Group -->
                <div class="flex items-center gap-3">
                    <label
                        class="text-xs font-bold text-slate-500 uppercase whitespace-nowrap"
                        >{{ $t("inventory.period") }}</label
                    >
                    <DatePicker
                        v-model="dates"
                        selectionMode="range"
                        :manualInput="false"
                        showIcon
                        iconDisplay="input"
                        class="flex-1 !rounded-xl"
                    />
                </div>

                <!-- Search Group -->
                <div class="flex items-center gap-3 flex-1">
                    <label
                        class="text-xs font-bold text-slate-500 uppercase whitespace-nowrap"
                        >{{ $t("inventory.search_product") }}</label
                    >
                    <span class="p-input-icon-left w-full">
                        <InputText
                            v-model="search"
                            class="w-full !rounded-xl"
                            :placeholder="$t('inventory.search_product')"
                        />
                    </span>
                </div>

                <Button
                    :label="$t('common.search')"
                    icon="pi pi-search"
                    @click="loadData"
                    :loading="loading"
                    class="!rounded-xl shadow-sm"
                />
            </div>

            <!-- Audit Table -->
            <Card class="border-none shadow-sm overflow-hidden">
                <template #content>
                    <AppDataTable
                        :value="filteredRows"
                        :loading="loading"
                        class="app-table"
                        paginator
                        :rows="10"
                        responsiveLayout="scroll"
                        v-model:first="first"
                    >
                        <template #empty>
                            <div class="p-4 text-center text-slate-500">
                                {{ $t("common.no_data") }}
                            </div>
                        </template>

                        <Column
                            field="product_name"
                            :header="$t('common.name')"
                            frozen
                            sortable
                            class="font-bold min-w-[200px]"
                        >
                            <template #body="{ data }">
                                <div class="flex flex-col">
                                    <span
                                        class="text-[10px] text-slate-400 font-mono"
                                        >{{ data.code }}</span
                                    >
                                    <span>{{ data.product_name }}</span>
                                </div>
                            </template>
                        </Column>
                        <Column
                            field="unit"
                            :header="$t('sidebar.units')"
                            class="text-center"
                        />

                        <Column
                            field="initial_balance"
                            :header="$t('inventory.initial_balance')"
                            class="text-right bg-slate-50"
                        >
                            <template #body="{ data }">{{
                                formatQty(data.initial_balance)
                            }}</template>
                        </Column>

                        <Column
                            field="purchase"
                            :header="$t('inventory.purchase_in')"
                            class="text-right text-emerald-600"
                        >
                            <template #body="{ data }">{{
                                formatQty(data.purchase + data.sale_return)
                            }}</template>
                        </Column>

                        <Column
                            field="sale"
                            :header="$t('inventory.sale_out')"
                            class="text-right text-rose-600"
                        >
                            <template #body="{ data }">{{
                                formatQty(data.sale + data.purchase_return)
                            }}</template>
                        </Column>

                        <Column
                            field="adjustment"
                            :header="$t('inventory.adjustment')"
                            class="text-right"
                        >
                            <template #body="{ data }">
                                <span
                                    :class="
                                        data.adjustment >= 0
                                            ? 'text-blue-600'
                                            : 'text-orange-600'
                                    "
                                >
                                    {{ formatQty(data.adjustment) }}
                                </span>
                            </template>
                        </Column>

                        <Column
                            field="final_balance"
                            :header="$t('inventory.final_balance')"
                            class="text-right bg-indigo-50 font-bold"
                        >
                            <template #body="{ data }">{{
                                formatQty(data.final_balance)
                            }}</template>
                        </Column>

                        <Column class="w-24 text-center">
                            <template #body="{ data }">
                                <Button
                                    icon="pi pi-list"
                                    severity="secondary"
                                    rounded
                                    text
                                    @click="showDetail(data)"
                                />
                            </template>
                        </Column>
                    </AppDataTable>
                </template>
            </Card>
        </div>

        <!-- Detail Modal -->
        <Dialog
            v-model:visible="detailVisible"
            :header="selectedProduct?.product_name"
            modal
            class="w-full max-w-4xl"
            :dismissableMask="true"
        >
            <div v-if="detailLoading" class="flex justify-center p-8">
                <i class="pi pi-spin pi-spinner text-4xl text-blue-500"></i>
            </div>
            <div v-else class="space-y-4">
                <div
                    class="flex justify-between items-center bg-slate-50 p-4 rounded-lg"
                >
                    <div class="text-sm">
                        <span class="text-slate-500"
                            >{{ $t("inventory.initial_balance") }}:</span
                        >
                        <span class="ml-2 font-bold">{{
                            formatQty(selectedProduct?.initial_balance)
                        }}</span>
                    </div>
                    <div class="text-sm font-bold text-indigo-700">
                        <span>{{ $t("inventory.final_balance") }}:</span>
                        <span class="ml-2">{{
                            formatQty(selectedProduct?.final_balance)
                        }}</span>
                    </div>
                </div>

                <AppDataTable
                    :value="detailRows"
                    class="app-table"
                    :rows="10"
                    paginator
                >
                    <Column :header="$t('common.date')">
                        <template #body="{ data }">{{
                            formatFullDate(data.created_at)
                        }}</template>
                    </Column>
                    <Column field="type" :header="$t('inventory.type')">
                        <template #body="{ data }">
                            <Tag
                                :value="
                                    translateType(
                                        data.type,
                                        data.reference_type,
                                    )
                                "
                                :severity="getSeverity(data.type)"
                            />
                        </template>
                    </Column>
                    <Column
                        field="quantity"
                        :header="$t('inventory.qty')"
                        class="text-right font-bold"
                    >
                        <template #body="{ data }">
                            <span
                                :class="
                                    isAddition(data.type)
                                        ? 'text-emerald-600'
                                        : 'text-rose-600'
                                "
                            >
                                {{ isAddition(data.type) ? "+" : "-"
                                }}{{ formatQty(data.quantity) }}
                            </span>
                        </template>
                    </Column>
                    <Column
                        :header="$t('inventory.current')"
                        class="text-right opacity-60"
                    >
                        <template #body="{ data }">{{
                            formatQty(data.new_stock)
                        }}</template>
                    </Column>
                    <Column
                        field="notes"
                        :header="$t('common.description')"
                        class="text-sm italic"
                    />
                </AppDataTable>
            </div>
        </Dialog>
    </AppPage>
</template>

<script setup>
import { ref, onMounted, computed, watch } from "vue";
import AppDataTable from "@/components/AppDataTable.vue";
import StatusBadge from "@/components/StatusBadge.vue";
import { useI18n } from "vue-i18n";
import { inventoryApi } from "@/api/inventory";
import AppPage from "@/components/layout/AppPage.vue";
import Card from "primevue/card";
import Column from "primevue/column";
import Button from "primevue/button";
import DatePicker from "primevue/datepicker";
import InputText from "primevue/inputtext";
import Dialog from "primevue/dialog";
import Tag from "primevue/tag";
import { useToast } from "primevue/usetoast";

const { t: $t } = useI18n();
const toast = useToast();

const dates = ref([
    new Date(new Date().getFullYear(), new Date().getMonth(), 1),
    new Date(),
]);
const search = ref("");
const loading = ref(false);
const rows = ref([]);
const totalRecords = ref(0);
const first = ref(0);

// Debounce search
let searchTimer = null;
watch(search, () => {
    clearTimeout(searchTimer);
    searchTimer = setTimeout(() => {
        loadData();
    }, 500);
});

// Detail Modal State
const detailVisible = ref(false);
const detailLoading = ref(false);
const detailRows = ref([]);
const selectedProduct = ref(null);

const filteredRows = computed(() => rows.value);

function formatQty(val) {
    if (val === undefined || val === null) return "0";
    return Number(val).toLocaleString("id-ID");
}

function formatFullDate(date) {
    return new Date(date).toLocaleString("id-ID", {
        year: "numeric",
        month: "short",
        day: "numeric",
        hour: "2-digit",
        minute: "2-digit",
    });
}

function translateType(type, ref) {
    if (type === "initial") return $t("inventory.initial_balance");
    if (ref && ref.includes("Order"))
        return type === "in"
            ? $t("inventory.sale_return")
            : $t("inventory.sale");
    if (ref && ref.includes("Purchase"))
        return type === "in"
            ? $t("inventory.purchase")
            : $t("inventory.purchase_return");
    if (type === "adjustment_in") return $t("inventory.adjustment") + " (+)";
    if (type === "adjustment_out") return $t("inventory.adjustment") + " (-)";
    return type;
}

function getSeverity(type) {
    if (type === "in" || type === "adjustment_in" || type === "initial")
        return "success";
    if (type === "out" || type === "adjustment_out") return "danger";
    return "info";
}

function isAddition(type) {
    return type === "in" || type === "adjustment_in" || type === "initial";
}

const formatDate = (date) => {
    if (!date) return null;
    const d = new Date(date);
    let month = "" + (d.getMonth() + 1);
    let day = "" + d.getDate();
    let year = d.getFullYear();
    if (month.length < 2) month = "0" + month;
    if (day.length < 2) day = "0" + day;
    return [year, month, day].join("-");
};

async function loadData() {
    if (!dates.value || !dates.value[0] || !dates.value[1]) return;

    loading.value = true;
    try {
        const params = {
            start_date: formatDate(dates.value[0]),
            end_date: formatDate(dates.value[1]),
        };
        if (search.value) params.q = search.value;

        const response = await inventoryApi.getMovements(params);
        rows.value = response.data.data.items;
    } catch (error) {
        toast.add({
            severity: "error",
            summary: "Error",
            detail: "Gagal mengambil data mutasi stok",
            life: 3000,
        });
    } finally {
        loading.value = false;
    }
}

async function showDetail(product) {
    selectedProduct.value = product;
    detailVisible.value = true;
    detailLoading.value = true;

    try {
        const params = {
            start_date: formatDate(dates.value[0]),
            end_date: formatDate(dates.value[1]),
        };
        const response = await inventoryApi.getMovementDetail(
            product.product_id,
            params,
        );
        detailRows.value = response.data.data.movements;
    } catch (error) {
        toast.add({
            severity: "error",
            summary: "Error",
            detail: "Gagal mengambil detail mutasi",
            life: 3000,
        });
        detailVisible.value = false;
    } finally {
        detailLoading.value = false;
    }
}

onMounted(() => {
    loadData();
});
</script>
