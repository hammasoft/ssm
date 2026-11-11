import { useEffect, useState } from 'react';
import { useQuery, useQueryClient } from '@tanstack/react-query';
import { tokens } from '../api/client';
import { MessagingApi, NotificationsApi } from '../api/endpoints';
import type { AppNotification } from '../api/types';

/**
 * Compteurs de non-lus + flux temps réel (Server-Sent Events) du notification-service.
 * Chaque événement reçu rafraîchit les données concernées via React Query.
 */
export function useNotifications(enabled: boolean) {
  const queryClient = useQueryClient();
  const [toast, setToast] = useState<AppNotification | null>(null);

  const unreadNotifications = useQuery({
    queryKey: ['notifications', 'unread'],
    queryFn: NotificationsApi.unread,
    enabled,
    refetchInterval: 60_000,
  });
  const unreadMessages = useQuery({
    queryKey: ['conversations', 'unread'],
    queryFn: MessagingApi.unread,
    enabled,
    refetchInterval: 30_000,
  });

  useEffect(() => {
    if (!enabled || !tokens.access) return;
    const source = new EventSource(`/api/notifications/stream?access_token=${encodeURIComponent(tokens.access)}`);
    source.addEventListener('notification', (event) => {
      const notification = JSON.parse((event as MessageEvent).data) as AppNotification;
      setToast(notification);
      queryClient.invalidateQueries({ queryKey: ['notifications'] });
      if (notification.type === 'NEW_MESSAGE') queryClient.invalidateQueries({ queryKey: ['conversations'] });
      if (notification.type.startsWith('ORDER') || notification.type === 'ITEM_SOLD' || notification.type === 'FUNDS_RELEASED') {
        queryClient.invalidateQueries({ queryKey: ['orders'] });
        queryClient.invalidateQueries({ queryKey: ['wallet'] });
      }
      if (notification.type.startsWith('OFFER')) queryClient.invalidateQueries({ queryKey: ['offers'] });
    });
    // EventSource se reconnecte seul ; en cas d'expiration du jeton, on le recrée au prochain rendu.
    source.onerror = () => {
      if (source.readyState === EventSource.CLOSED) source.close();
    };
    return () => source.close();
  }, [enabled, queryClient]);

  useEffect(() => {
    if (!toast) return;
    const t = setTimeout(() => setToast(null), 5000);
    return () => clearTimeout(t);
  }, [toast]);

  return {
    unreadNotifications: unreadNotifications.data?.count ?? 0,
    unreadMessages: unreadMessages.data?.count ?? 0,
    toast,
    dismissToast: () => setToast(null),
  };
}
