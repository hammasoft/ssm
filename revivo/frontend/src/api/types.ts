// Types miroirs des DTO exposés par le backend (cf. Swagger : /swagger-ui.html)

export interface Page<T> {
  content: T[];
  page: number;
  size: number;
  totalElements: number;
  totalPages: number;
  last: boolean;
}

export interface UserSummary {
  id: number;
  username: string;
  avatarUrl?: string;
  ratingAvg: number;
  ratingCount: number;
}

export interface Me extends UserSummary {
  email: string;
  role: 'USER' | 'ADMIN';
  bio?: string;
  city?: string;
  country?: string;
  holidayMode: boolean;
  createdAt: string;
}

export interface AuthResponse {
  accessToken: string;
  refreshToken: string;
  expiresIn: number;
  user: Me;
}

export interface PublicProfile {
  id: number;
  username: string;
  bio?: string;
  city?: string;
  country?: string;
  avatarUrl?: string;
  holidayMode: boolean;
  ratingAvg: number;
  ratingCount: number;
  followers: number;
  following: number;
  activeItems: number;
  followedByMe: boolean;
  memberSince: string;
}

export interface ItemCard {
  id: number;
  title: string;
  price: number;
  priceWithProtection: number;
  size?: string;
  brand?: string;
  condition: string;
  photoUrl?: string;
  status: string;
  favoriteCount: number;
  favorited: boolean;
  seller: UserSummary;
}

export interface ItemDetail {
  id: number;
  title: string;
  description: string;
  price: number;
  priceWithProtection: number;
  size?: string;
  color?: string;
  condition: string;
  conditionLabel: string;
  categoryId: number;
  categoryName: string;
  brandId?: number;
  brand?: string;
  photos: string[];
  status: string;
  viewCount: number;
  favoriteCount: number;
  favorited: boolean;
  mine: boolean;
  purchasable: boolean;
  seller: UserSummary;
  createdAt: string;
  updatedAt: string;
}

export interface ItemRequest {
  title: string;
  description: string;
  categoryId: number;
  brandId?: number | null;
  size?: string;
  color?: string;
  condition: string;
  price: number;
  photoUrls: string[];
}

export interface CategoryNode {
  id: number;
  name: string;
  slug: string;
  children: CategoryNode[];
}

export interface Brand {
  id: number;
  name: string;
}

export interface Meta {
  conditions: { code: string; label: string }[];
  sizes: string[];
  colors: string[];
  shippingOptions: { code: ShippingOption; label: string }[];
}

export type ShippingOption = 'RELAY_POINT' | 'HOME_DELIVERY' | 'HAND_DELIVERY';

export interface Offer {
  id: number;
  itemId: number;
  itemTitle: string;
  itemPhotoUrl?: string;
  itemPrice: number;
  amount: number;
  status: 'PENDING' | 'ACCEPTED' | 'DECLINED' | 'CANCELLED' | 'EXPIRED' | 'USED';
  expiresAt: string;
  buyer: UserSummary;
  seller: UserSummary;
  createdAt: string;
}

export interface CheckoutQuote {
  itemId: number;
  itemPrice: number;
  offerApplied: boolean;
  protectionFee: number;
  shippingPrice: number;
  total: number;
}

export type OrderStatus = 'PAID' | 'SHIPPED' | 'DELIVERED' | 'COMPLETED' | 'CANCELLED' | 'DISPUTED' | 'REFUNDED';

export interface Order {
  id: number;
  itemId: number;
  itemTitle: string;
  itemPhotoUrl?: string;
  buyer: UserSummary;
  seller: UserSummary;
  itemPrice: number;
  shippingPrice: number;
  protectionFee: number;
  total: number;
  status: OrderStatus;
  shippingOption: ShippingOption;
  shippingAddress?: string;
  trackingNumber?: string;
  disputeReason?: string;
  role: 'BUYER' | 'SELLER' | 'ADMIN';
  reviewedByMe: boolean;
  paidAt?: string;
  shippedAt?: string;
  deliveredAt?: string;
  completedAt?: string;
  cancelledAt?: string;
}

export interface Review {
  id: number;
  orderId: number;
  itemTitle: string;
  rating: number;
  comment?: string;
  authorRole: 'BUYER' | 'SELLER';
  author: UserSummary;
  createdAt: string;
}

export interface Conversation {
  id: number;
  other: UserSummary;
  itemId?: number;
  itemTitle?: string;
  itemPhotoUrl?: string;
  lastMessage?: string;
  lastMessageAt?: string;
  unread: number;
}

export interface Message {
  id: number;
  senderId: number;
  content: string;
  mine: boolean;
  readAt?: string;
  createdAt: string;
}

export interface WalletSummary {
  available: number;
  pending: number;
}

export interface WalletTransaction {
  id: number;
  type: string;
  amount: number;
  orderId?: number;
  description?: string;
  createdAt: string;
}

export interface AppNotification {
  id: number;
  type: string;
  title: string;
  body?: string;
  link?: string;
  read: boolean;
  createdAt: string;
}

export interface Report {
  id: number;
  reason: string;
  details?: string;
  status: string;
  itemId?: number;
  itemTitle?: string;
  reportedUserId?: number;
  reportedUsername?: string;
  reporterId: number;
  reporterUsername: string;
  createdAt: string;
}

export interface AdminStats {
  users: number;
  activeItems: number;
  soldItems: number;
  ordersInProgress: number;
  openDisputes: number;
  openReports: number;
  completedVolume: number;
  outboxBacklog: number;
}

/** Réponse d'erreur RFC 7807 renvoyée par l'API. */
export interface Problem {
  title?: string;
  detail?: string;
  code?: string;
  errors?: Record<string, string>;
}
