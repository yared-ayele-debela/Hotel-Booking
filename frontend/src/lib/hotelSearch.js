/**
 * Parse Laravel paginated hotel list: { success, data: { data: hotels[], meta: { total, ... } } }
 */
export function parseHotelSearchResponse(apiBody) {
  if (!apiBody?.data) {
    return { hotels: [], meta: {}, total: 0 };
  }
  const payload = apiBody.data;
  const hotels = Array.isArray(payload) ? payload : (payload?.data ?? []);
  const meta = payload?.meta ?? {};
  const raw = meta.total;
  const n = typeof raw === 'number' ? raw : Number(raw);
  const total = Number.isFinite(n) ? n : hotels.length;
  return { hotels, meta, total };
}
