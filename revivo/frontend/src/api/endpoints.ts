import { api } from './client';
import type {
  AdminStats, AppNotification, AuthResponse, Brand, CategoryNode, CheckoutQuote, Conversation, ItemCard,
  ItemDetail, ItemRequest, Me, Message, Meta, Offer, Order, Page, PublicProfile, Report, Review, ShippingOption,
  WalletSummary, WalletTransaction,
} from './types';

const data = <T,>(p: Promise<{ data: T }>) => p.then((r) => r.data);

export interface SearchParams {
  q?: string;
  categoryId?: string;
  brandIds?: string;
  sizes?: string;
  conditions?: string;
  minPrice?: string;
  maxPrice?: string;
  sort?: string;
  page?: number;
}

export const AuthApi = {
  login: (email: string, password: string) => data(api.post<AuthResponse>('/auth/login', { email, password })),
  register: (username: string, email: string, password: string) =>
    data(api.post<AuthResponse>('/auth/register', { username, email, password })),
  logout: (refreshToken: string) => api.post('/auth/logout', { refreshToken }),
};

export const UsersApi = {
  me: () => data(api.get<Me>('/users/me')),
  update: (body: Partial<Me>) => data(api.put<Me>('/users/me', body)),
  changePassword: (currentPassword: string, newPassword: string) =>
    api.post('/users/me/password', { currentPassword, newPassword }),
  profile: (id: number) => data(api.get<PublicProfile>(`/users/${id}`)),
  items: (id: number, page = 0) => data(api.get<Page<ItemCard>>(`/users/${id}/items`, { params: { page } })),
  reviews: (id: number, page = 0) => data(api.get<Page<Review>>(`/users/${id}/reviews`, { params: { page } })),
  follow: (id: number) => api.post(`/users/${id}/follow`),
  unfollow: (id: number) => api.delete(`/users/${id}/follow`),
};

export const CatalogApi = {
  categories: () => data(api.get<CategoryNode[]>('/categories')),
  brands: () => data(api.get<Brand[]>('/brands')),
  meta: () => data(api.get<Meta>('/meta')),
};

export const ItemsApi = {
  search: (params: SearchParams) => data(api.get<Page<ItemCard>>('/items', { params: { size: 24, ...params } })),
  feed: (page = 0) => data(api.get<Page<ItemCard>>('/items/feed', { params: { page } })),
  get: (id: number) => data(api.get<ItemDetail>(`/items/${id}`)),
  similar: (id: number) => data(api.get<ItemCard[]>(`/items/${id}/similar`)),
  create: (body: ItemRequest) => data(api.post<ItemDetail>('/items', body)),
  update: (id: number, body: ItemRequest) => data(api.put<ItemDetail>(`/items/${id}`, body)),
  setHidden: (id: number, hidden: boolean) => api.post(`/items/${id}/visibility`, null, { params: { hidden } }),
  remove: (id: number) => api.delete(`/items/${id}`),
  upload: (file: File) => {
    const form = new FormData();
    form.append('file', file);
    return data(api.post<{ url: string }>('/uploads', form));
  },
};

export const FavoritesApi = {
  list: (page = 0) => data(api.get<Page<ItemCard>>('/favorites', { params: { page } })),
  add: (itemId: number) => api.put(`/favorites/${itemId}`),
  remove: (itemId: number) => api.delete(`/favorites/${itemId}`),
};

export const OffersApi = {
  make: (itemId: number, amount: number) => data(api.post<Offer>(`/items/${itemId}/offers`, { amount })),
  sent: () => data(api.get<Page<Offer>>('/offers/sent')),
  received: () => data(api.get<Page<Offer>>('/offers/received')),
  accept: (id: number) => data(api.post<Offer>(`/offers/${id}/accept`)),
  decline: (id: number) => data(api.post<Offer>(`/offers/${id}/decline`)),
  cancel: (id: number) => data(api.post<Offer>(`/offers/${id}/cancel`)),
};

export const OrdersApi = {
  quote: (itemId: number, shippingOption: ShippingOption) =>
    data(api.get<CheckoutQuote>(`/items/${itemId}/checkout-quote`, { params: { shippingOption } })),
  checkout: (itemId: number, body: { shippingOption: ShippingOption; shippingAddress?: string; paymentMethodToken: string }) =>
    data(api.post<Order>(`/items/${itemId}/checkout`, body)),
  purchases: () => data(api.get<Page<Order>>('/orders/purchases')),
  sales: () => data(api.get<Page<Order>>('/orders/sales')),
  get: (id: number) => data(api.get<Order>(`/orders/${id}`)),
  ship: (id: number, trackingNumber: string) => data(api.post<Order>(`/orders/${id}/ship`, { trackingNumber })),
  delivered: (id: number) => data(api.post<Order>(`/orders/${id}/delivered`)),
  confirm: (id: number) => data(api.post<Order>(`/orders/${id}/confirm`)),
  cancel: (id: number, reason: string) => data(api.post<Order>(`/orders/${id}/cancel`, { reason })),
  dispute: (id: number, reason: string) => data(api.post<Order>(`/orders/${id}/dispute`, { reason })),
  review: (id: number, rating: number, comment: string) =>
    data(api.post<Review>(`/orders/${id}/review`, { rating, comment })),
};

export const WalletApi = {
  summary: () => data(api.get<WalletSummary>('/wallet')),
  transactions: () => data(api.get<Page<WalletTransaction>>('/wallet/transactions')),
  withdraw: (amount: number, iban: string) => data(api.post<WalletSummary>('/wallet/withdrawals', { amount, iban })),
};

export const MessagingApi = {
  inbox: () => data(api.get<Page<Conversation>>('/conversations')),
  unread: () => data(api.get<{ count: number }>('/conversations/unread-count')),
  start: (recipientId: number, content: string, itemId?: number) =>
    data(api.post<Conversation>('/conversations', { recipientId, itemId, content })),
  messages: (id: number) => data(api.get<Page<Message>>(`/conversations/${id}/messages`, { params: { size: 100 } })),
  send: (id: number, content: string) => data(api.post<Message>(`/conversations/${id}/messages`, { content })),
};

/** Format de pagination Spring Data « VIA_DTO » du notification-service. */
interface SpringPage<T> {
  content: T[];
  page: { size: number; number: number; totalElements: number; totalPages: number };
}

export const NotificationsApi = {
  list: () => data(api.get<SpringPage<AppNotification>>('/notifications', { params: { size: 50 } })),
  unread: () => data(api.get<{ count: number }>('/notifications/unread-count')),
  read: (id: number) => api.post(`/notifications/${id}/read`),
  readAll: () => api.post('/notifications/read-all'),
};

export const ReportsApi = {
  create: (body: { itemId?: number; userId?: number; reason: string; details?: string }) => api.post('/reports', body),
};

export const AdminApi = {
  stats: () => data(api.get<AdminStats>('/admin/stats')),
  reports: () => data(api.get<Page<Report>>('/admin/reports')),
  resolve: (id: number, action: 'REMOVE_ITEM' | 'BAN_USER' | 'DISMISS') =>
    data(api.post<Report>(`/admin/reports/${id}/resolve`, { action })),
  disputes: () => data(api.get<Page<Order>>('/admin/disputes')),
  resolveDispute: (id: number, refundBuyer: boolean) =>
    data(api.post<Order>(`/admin/orders/${id}/resolve-dispute`, null, { params: { refundBuyer } })),
};
