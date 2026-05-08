export function unwrapCollection(response) {
  const payload = response?.data?.data;
  const items = Array.isArray(payload) ? payload : (payload?.data || []);
  return {
    items: items,
    meta: payload?.meta || null,
    links: payload?.links || null
  };
}

export function unwrapItem(response) {
  return response?.data?.data ?? null;
}
