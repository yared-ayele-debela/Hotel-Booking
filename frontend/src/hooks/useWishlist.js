import { useQuery, useMutation, useQueryClient } from '@tanstack/react-query';
import { useMemo } from 'react';
import { api } from '../lib/api';

export function useWishlist(enabled = true) {
  const queryClient = useQueryClient();

  const { data, isLoading } = useQuery({
    queryKey: ['wishlist'],
    queryFn: async () => {
      const res = await api.get('/wishlist');
      if (!res.data?.success) throw new Error(res.data?.message || 'Failed to load wishlist');
      return res.data;
    },
    enabled,
  });

  const items = data?.data?.data ?? data?.data ?? [];
  const wishlistHotelIds = useMemo(() => new Set(items.map((i) => i.hotel_id)), [items]);

  const addMutation = useMutation({
    mutationFn: async ({ hotelId, checkIn, checkOut }) => {
      const res = await api.post('/wishlist', {
        hotel_id: hotelId,
        ...(checkIn && { check_in: checkIn }),
        ...(checkOut && { check_out: checkOut }),
      });
      if (!res.data?.success) throw new Error(res.data?.message || 'Failed to add');
      return res.data;
    },
    onSuccess: () => queryClient.invalidateQueries({ queryKey: ['wishlist'] }),
  });

  const removeMutation = useMutation({
    mutationFn: async (hotelId) => {
      await api.delete(`/wishlist/${hotelId}`);
    },
    onSuccess: () => queryClient.invalidateQueries({ queryKey: ['wishlist'] }),
  });

  return {
    wishlist: items,
    wishlistHotelIds,
    isInWishlist: (hotelId) => wishlistHotelIds.has(hotelId),
    addToWishlist: addMutation.mutateAsync,
    removeFromWishlist: removeMutation.mutateAsync,
    addPending: addMutation.isPending,
    removePending: removeMutation.isPending,
    isLoading,
  };
}
