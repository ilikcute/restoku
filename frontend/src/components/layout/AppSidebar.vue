<template>
    <aside
        :class="[
            'flex flex-col bg-white rounded-2xl shadow-sm border border-slate-100 transition-all duration-300 shrink-0 h-full overflow-hidden',
            'hidden lg:flex',
            isCollapsed ? 'w-20' : 'w-64',
        ]"
    >
        <!-- Logo Section -->
        <div
            class="flex h-16 shrink-0 items-center justify-between px-4 border-b border-slate-100"
        >
            <div
                v-if="!isCollapsed"
                class="flex items-center gap-3 overflow-hidden"
            >
                <div
                    class="w-9 h-9 rounded-xl bg-orange-500 flex items-center justify-center shrink-0 shadow-sm"
                >
                    <img
                        v-if="tenant.logo_url"
                        :src="tenant.logo_url"
                        alt="Logo"
                        class="w-7 h-7 object-contain rounded-lg"
                    />
                    <span
                        v-else
                        class="text-white font-black text-sm leading-none"
                    >
                        {{ (tenant.name || "R").charAt(0).toUpperCase() }}
                    </span>
                </div>
                <span
                    class="text-base font-extrabold text-slate-800 tracking-tight leading-tight truncate"
                >
                    {{ tenant.name || "Restoku" }}
                </span>
            </div>
            <div
                v-else
                class="w-9 h-9 rounded-xl bg-orange-500 flex items-center justify-center mx-auto shadow-sm"
            >
                <span class="text-white font-black text-sm leading-none">
                    {{ (tenant.name || "R").charAt(0).toUpperCase() }}
                </span>
            </div>
            <button
                v-if="!isCollapsed"
                @click="toggleSidebar"
                class="p-1.5 rounded-lg hover:bg-slate-100 text-slate-400 transition-colors shrink-0"
            >
                <i class="pi pi-bars text-base"></i>
            </button>
        </div>

        <!-- Navigation Section -->
        <nav class="flex-1 overflow-y-auto py-4 px-3 space-y-0.5">
            <div v-for="group in visibleGroups" :key="group.label">
                <!-- Section Label (for non-dashboard groups) -->
                <p
                    v-if="group.label !== 'common.dashboard' && !isCollapsed"
                    class="px-3 pt-4 pb-1 text-[10px] font-bold uppercase tracking-widest text-slate-400"
                >
                    {{ getSectionLabel(group.label) }}
                </p>
                <div
                    v-else-if="
                        group.label !== 'common.dashboard' && isCollapsed
                    "
                    class="pt-3 pb-1 flex justify-center"
                >
                    <div class="h-px w-8 bg-slate-200"></div>
                </div>

                <!-- Dashboard Direct Link -->
                <router-link
                    v-if="group.label === 'common.dashboard'"
                    to="/dashboard"
                    :class="[
                        'flex items-center gap-3 rounded-xl py-2.5 text-sm font-semibold transition-all duration-200',
                        isCollapsed
                            ? 'justify-center px-0 mx-auto w-10 h-10'
                            : 'px-3',
                        route.path === '/dashboard'
                            ? 'bg-orange-500 text-white shadow-sm'
                            : 'text-slate-500 hover:bg-slate-50 hover:text-slate-800',
                    ]"
                    @click="$emit('update:modelValue', false)"
                >
                    <i class="pi pi-layout text-base shrink-0" />
                    <span v-if="!isCollapsed" class="whitespace-nowrap">{{
                        $t("common.overview")
                    }}</span>

                    <!-- Tooltip collapsed -->
                    <div
                        v-if="isCollapsed"
                        class="absolute left-full ml-3 px-2.5 py-1.5 bg-slate-800 text-white text-xs font-semibold rounded-lg opacity-0 invisible group-hover:opacity-100 group-hover:visible transition-all whitespace-nowrap z-50 pointer-events-none"
                    >
                        {{ $t("common.overview") }}
                    </div>
                </router-link>

                <!-- Group Toggle Button (for non-dashboard groups) -->
                <button
                    v-if="group.label !== 'common.dashboard'"
                    @click="toggleGroup(group.label)"
                    :class="[
                        'w-full flex items-center gap-3 rounded-xl py-2.5 text-sm font-semibold transition-all duration-200',
                        isCollapsed
                            ? 'justify-center px-0'
                            : 'px-3 justify-between',
                        isGroupOpen(group.label)
                            ? 'text-slate-800'
                            : 'text-slate-400 hover:text-slate-700 hover:bg-slate-50',
                    ]"
                >
                    <div class="flex items-center gap-3">
                        <i
                            :class="[
                                group.icon || 'pi pi-folder',
                                'text-base shrink-0',
                            ]"
                        ></i>
                        <span v-if="!isCollapsed" class="whitespace-nowrap">{{
                            $t(group.label)
                        }}</span>
                    </div>
                    <i
                        v-if="!isCollapsed"
                        :class="[
                            'pi pi-chevron-down text-[10px] transition-transform duration-300',
                            isGroupOpen(group.label) ? 'rotate-180' : '',
                        ]"
                    ></i>
                </button>

                <!-- Submenu Items -->
                <div
                    v-if="
                        group.label !== 'common.dashboard' &&
                        isGroupOpen(group.label)
                    "
                    :class="['space-y-0.5 mt-0.5', !isCollapsed ? 'pl-3' : '']"
                >
                    <component
                        :is="item.external ? 'a' : 'router-link'"
                        v-for="item in group.items"
                        :key="item.to"
                        :to="item.external ? undefined : item.to"
                        :href="item.external ? item.to : undefined"
                        :target="item.external ? '_blank' : undefined"
                        :class="[
                            'flex items-center gap-3 rounded-xl py-2.5 text-sm font-medium transition-all duration-200 group relative',
                            isCollapsed
                                ? 'justify-center px-0 w-10 h-10 mx-auto'
                                : 'px-3',
                            !item.external &&
                            (route.path === item.to ||
                                route.path.startsWith(item.to + '/'))
                                ? 'bg-orange-500 text-white shadow-sm'
                                : 'text-slate-500 hover:bg-slate-50 hover:text-slate-800',
                        ]"
                        @click="
                            item.external
                                ? null
                                : $emit('update:modelValue', false)
                        "
                    >
                        <i :class="[item.icon, 'text-base shrink-0']" />
                        <span v-if="!isCollapsed" class="whitespace-nowrap">{{
                            $t(item.label)
                        }}</span>

                        <!-- Tooltip for collapsed state -->
                        <div
                            v-if="isCollapsed"
                            class="absolute left-full ml-3 px-2.5 py-1.5 bg-slate-800 text-white text-xs font-semibold rounded-lg opacity-0 invisible group-hover:opacity-100 group-hover:visible transition-all whitespace-nowrap z-50 pointer-events-none"
                        >
                            {{ $t(item.label) }}
                        </div>
                    </component>
                </div>
            </div>
        </nav>

        <!-- Footer: User Card -->
        <div class="shrink-0 border-t border-slate-100 p-3">
            <div
                v-if="!isCollapsed"
                class="flex items-center gap-3 p-2 rounded-xl hover:bg-slate-50 transition-colors cursor-pointer"
            >
                <img
                    :src="
                        authStore.user?.attributes?.avatar_url ||
                        `https://ui-avatars.com/api/?name=${authStore.user?.attributes?.name || 'Admin'}&background=f97316&color=fff&size=64`
                    "
                    alt="User"
                    class="w-9 h-9 rounded-full border-2 border-orange-100"
                />
                <div class="flex-1 overflow-hidden">
                    <p
                        class="text-sm font-semibold text-slate-800 truncate leading-tight"
                    >
                        {{ authStore.user?.attributes?.name || "Admin" }}
                    </p>
                    <p
                        class="text-[11px] text-slate-400 font-bold capitalize leading-tight"
                    >
                        {{
                            authStore.user?.attributes?.role || "Administrator"
                        }}
                    </p>
                </div>
                <button
                    @click="toggleUserMenu"
                    class="p-1 rounded-lg hover:bg-slate-100 text-slate-400 transition-colors"
                >
                    <i class="pi pi-ellipsis-v text-sm"></i>
                </button>
            </div>
            <div v-else class="flex justify-center">
                <img
                    :src="
                        authStore.user?.attributes?.avatar_url ||
                        `https://ui-avatars.com/api/?name=${authStore.user?.attributes?.name || 'Admin'}&background=f97316&color=fff&size=64`
                    "
                    alt="User"
                    class="w-9 h-9 rounded-full border-2 border-orange-100 cursor-pointer"
                    @click="toggleUserMenu"
                />
            </div>
            <Menu
                ref="userMenu"
                :model="userMenuItems"
                popup
                class="!rounded-xl !shadow-xl !border-slate-100"
            />
        </div>
    </aside>
</template>

<script setup>
import { computed, ref, onMounted, watch } from "vue";
import { useRoute, useRouter } from "vue-router";
import { useAuthStore } from "@/stores/auth";
import { useI18n } from "vue-i18n";
import Menu from "primevue/menu";

const props = defineProps({
    modelValue: { type: Boolean, default: false },
});

const emit = defineEmits(["update:modelValue"]);

const route = useRoute();
const router = useRouter();
const { t: $t } = useI18n();
const authStore = useAuthStore();
const permissions = computed(
    () => authStore.user?.attributes?.permissions || [],
);
const tenant = computed(
    () => authStore.user?.relationships?.tenant?.data?.attributes || {},
);
const isCollapsed = ref(false);

// State for open groups
const openGroups = ref(new Set());
const userMenu = ref();

const userMenuItems = computed(() => [
    {
        label: $t("user_menu.profile"),
        icon: "pi pi-user",
        command: () => {
            router.push("/settings/profile");
            emit("update:modelValue", false);
        },
    },
    {
        label: $t("user_menu.users"),
        icon: "pi pi-users",
        command: () => {
            router.push("/settings/users");
            emit("update:modelValue", false);
        },
        visible: permissions.value.includes("manage-users"),
    },
    { separator: true },
    {
        label: $t("user_menu.logout"),
        icon: "pi pi-sign-out",
        command: async () => {
            await authStore.logout();
            router.push("/login");
        },
    },
]);

const toggleUserMenu = (event) => {
    userMenu.value?.toggle(event);
};

const toggleSidebar = () => {
    isCollapsed.value = !isCollapsed.value;
    if (isCollapsed.value) {
        openGroups.value.clear();
    } else {
        autoExpandActiveGroup();
    }
};

const toggleGroup = (label) => {
    if (isCollapsed.value) {
        isCollapsed.value = false;
    }

    if (openGroups.value.has(label)) {
        openGroups.value.delete(label);
    } else {
        openGroups.value.add(label);
    }
};

const isGroupOpen = (label) => openGroups.value.has(label);

// Section label mapping for display
const sectionLabelMap = {
    "common.master_data": "Master",
    "common.inventory": "Inventory",
    "common.sales": "Sales",
    "common.purchasing": "Purchasing",
    "common.finance": "Finance",
    "common.reports": "Reports",
    "common.settings": "Settings",
};

const getSectionLabel = (label) => sectionLabelMap[label] || label;

const menuGroups = [
    {
        label: "common.dashboard",
        icon: "pi pi-layout",
        permission: "view-dashboard",
        items: [
            {
                to: "/dashboard",
                icon: "pi pi-layout",
                label: "common.overview",
            },
        ],
    },
    {
        label: "common.master_data",
        icon: "pi pi-database",
        permission: "view-master-data",
        items: [
            {
                to: "/master/products",
                icon: "pi pi-box",
                label: "sidebar.products",
                permission: "view-products",
            },
            {
                to: "/master/categories",
                icon: "pi pi-tags",
                label: "sidebar.categories",
                permission: "view-categories",
            },
            {
                to: "/master/units",
                icon: "pi pi-sliders-h",
                label: "sidebar.units",
                permission: "view-units",
            },
            {
                to: "/master/suppliers",
                icon: "pi pi-truck",
                label: "sidebar.suppliers",
                permission: "view-suppliers",
            },
            {
                to: "/master/customers",
                icon: "pi pi-user",
                label: "sidebar.customers",
                permission: "view-customers",
            },
        ],
    },
    {
        label: "common.inventory",
        icon: "pi pi-warehouse",
        permission: "view-inventory",
        items: [
            {
                to: "/inventory/stocks",
                icon: "pi pi-warehouse",
                label: "sidebar.stock_levels",
                permission: "view-stocks",
            },
            {
                to: "/inventory/movements",
                icon: "pi pi-sync",
                label: "sidebar.movements",
                permission: "view-stock-movements",
            },
            {
                to: "/inventory/adjustments",
                icon: "pi pi-pencil",
                label: "sidebar.adjustments",
                permission: "view-stock-adjustments",
            },
        ],
    },
    {
        icon: "pi pi-shopping-cart",
        label: "common.sales",
        permission: "view-sales",
        items: [
            {
                to: "/sales/shifts",
                icon: "pi pi-clock",
                label: "sidebar.shift_manager",
                permission: "view-shifts",
            },
            {
                to: "/sales/pos",
                icon: "pi pi-calculator",
                label: "sidebar.pos",
                permission: "view-pos",
            },
            {
                to: "/sales/orders",
                icon: "pi pi-list",
                label: "sidebar.orders",
                permission: "view-orders",
            },
            {
                to: "/sales/import",
                icon: "pi pi-upload",
                label: "Import Transaksi",
                permission: "view-orders",
            },
            {
                to: "/sales/returns",
                icon: "pi pi-plus-circle",
                label: "sidebar.create_return",
                permission: "view-sales-returns",
            },
            {
                to: "/menu",
                icon: "pi pi-qrcode",
                label: "sidebar.digital_menu",
            },
        ],
    },
    {
        icon: "pi pi-shopping-bag",
        label: "common.purchasing",
        permission: "view-purchasing",
        items: [
            {
                to: "/purchasing/purchases",
                icon: "pi pi-list",
                label: "sidebar.purchases",
                permission: "view-purchases",
            },
            {
                to: "/purchasing/returns",
                icon: "pi pi-plus-circle",
                label: "sidebar.create_return",
                permission: "view-purchase-returns",
            },
            {
                to: "/purchasing/procurement",
                icon: "pi pi-search-plus",
                label: "sidebar.procurement",
                permission: "view-procurement",
            },
            {
                to: "/inventory/alerts",
                icon: "pi pi-bell",
                label: "sidebar.alerts",
                permission: "view-inventory-alerts",
            },
        ],
    },
    {
        label: "common.finance",
        icon: "pi pi-wallet",
        permission: "view-finance",
        items: [
            {
                to: "/finance/accounts",
                icon: "pi pi-wallet",
                label: "sidebar.accounts",
                permission: "view-accounts",
            },
            {
                to: "/finance/transactions",
                icon: "pi pi-money-bill",
                label: "sidebar.transactions",
                permission: "view-transactions",
            },
            {
                to: "/finance/closings",
                icon: "pi pi-check-circle",
                label: "sidebar.daily_closings",
                permission: "view-closings",
            },
        ],
    },
    {
        label: "common.reports",
        icon: "pi pi-chart-bar",
        permission: "view-reports",
        items: [
            {
                to: "/reports/recap",
                icon: "pi pi-chart-bar",
                label: "sidebar.recap",
                permission: "view-reports",
            },
            {
                to: "/reports/sales",
                icon: "pi pi-list",
                label: "sidebar.sales_report",
                permission: "view-report-sales",
            },
            {
                to: "/reports/purchases",
                icon: "pi pi-shopping-bag",
                label: "sidebar.purchase_report",
                permission: "view-report-purchases",
            },
            {
                to: "/reports/sales-returns",
                icon: "pi pi-replay",
                label: "sidebar.sales_return_report",
                permission: "view-report-returns",
            },
            {
                to: "/reports/purchase-returns",
                icon: "pi pi-replay",
                label: "sidebar.purchase_return_report",
                permission: "view-report-returns",
            },
            {
                to: "/reports/tax",
                icon: "pi pi-percentage",
                label: "sidebar.tax_report",
                permission: "view-report-tax",
            },
            {
                to: "/reports/tax-fixed",
                icon: "pi pi-percentage",
                label: "sidebar.tax_report_fixed",
                permission: "view-report-tax",
            },
            {
                to: "/reports/audit-logs",
                icon: "pi pi-history",
                label: "sidebar.audit_trail",
                permission: "view-reports",
            },
        ],
    },
    {
        label: "common.settings",
        icon: "pi pi-cog",
        items: [
            {
                to: "/settings/profile",
                icon: "pi pi-user-edit",
                label: "sidebar.profile",
                permission: "view-profile",
            },
            {
                to: "/settings/tenant",
                icon: "pi pi-building",
                label: "common.business_profile",
                permission: "view-business-profile",
            },
            {
                to: "/settings/printer",
                icon: "pi pi-print",
                label: "sidebar.printer_settings",
                permission: "view-business-profile",
            },
            {
                to: "/settings/users",
                icon: "pi pi-users",
                label: "sidebar.users",
                permission: "manage-users",
            },
            {
                to: "/settings/roles",
                icon: "pi pi-lock",
                label: "settings.roles",
                permission: "manage-roles",
            },
            {
                to: "/settings/promotions",
                icon: "pi pi-megaphone",
                label: "sidebar.promotions",
                permission: "view-promotions",
            },
            {
                to: "/settings/database",
                icon: "pi pi-database",
                label: "sidebar.database",
                permission: "manage-tenant-settings",
            },
        ],
    },
];

const visibleGroups = computed(() =>
    menuGroups
        .filter(
            (group) =>
                !group.permission ||
                permissions.value.includes(group.permission),
        )
        .map((group) => ({
            ...group,
            items: group.items.filter(
                (item) =>
                    !item.permission ||
                    permissions.value.includes(item.permission),
            ),
        }))
        .filter((group) => group.items.length > 0),
);

const autoExpandActiveGroup = () => {
    const currentPath = route.path;
    visibleGroups.value.forEach((group) => {
        if (group.items.some((item) => currentPath.startsWith(item.to))) {
            openGroups.value.add(group.label);
        }
    });
};

onMounted(() => {
    autoExpandActiveGroup();
});

watch(
    () => route.path,
    () => {
        if (!isCollapsed.value) {
            autoExpandActiveGroup();
        }
    },
);
</script>

<style scoped>
nav::-webkit-scrollbar {
    display: none;
}

nav {
    scrollbar-width: none;
    -ms-overflow-style: none;
}
</style>
