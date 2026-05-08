import Dexie from 'dexie';

export const db = new Dexie('RestokuV2');

// Skema Database Lokal
db.version(1).stores({
  products: 'id, category_id, code, name, is_active',
  categories: 'id, name, is_active',
  // Queue untuk pesanan offline tetap pakai ++id karena lokal
  orders_queue: '++id, order_number, total_amount, synced, created_at',
  settings: 'key, value'
});

/**
 * Helper untuk menyimpan produk ke lokal
 */
export async function syncProductsToLocal(products) {
  // Bersihkan data dari Proxy Vue agar bisa dikloning ke IndexedDB
  const rawData = JSON.parse(JSON.stringify(products));
  return await db.products.bulkPut(rawData);
}

/**
 * Helper untuk menyimpan kategori ke lokal
 */
export async function syncCategoriesToLocal(categories) {
  const rawData = JSON.parse(JSON.stringify(categories));
  return await db.categories.bulkPut(rawData);
}

/**
 * Simpan pesanan ke queue offline
 */
export async function saveOrderOffline(orderData) {
  return await db.orders_queue.add({
    ...orderData,
    synced: 0,
    created_at: new Date().toISOString()
  });
}

/**
 * Ambil semua pesanan yang belum disinkronkan
 */
export async function getPendingOrders() {
  return await db.orders_queue.where('synced').equals(0).toArray();
}

/**
 * Tandai pesanan sebagai sudah tersinkron
 */
export async function markOrderSynced(id) {
  return await db.orders_queue.update(id, { synced: 1 });
}
