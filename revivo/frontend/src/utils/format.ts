const eur = new Intl.NumberFormat('fr-FR', { style: 'currency', currency: 'EUR' });

/** Formate un montant en euros (« 24,79 € »), espaces insécables normalisés. */
export const formatPrice = (value: number | string | undefined | null): string =>
  value === undefined || value === null ? '' : eur.format(Number(value)).replace(/ | /g, ' ');

export const formatDate = (iso?: string): string =>
  iso ? new Date(iso).toLocaleDateString('fr-FR', { day: 'numeric', month: 'short', year: 'numeric' }) : '';

/** « il y a 5 min », « il y a 3 j »… */
export function timeAgo(iso?: string): string {
  if (!iso) return '';
  const seconds = Math.max(0, Math.round((Date.now() - new Date(iso).getTime()) / 1000));
  if (seconds < 60) return "à l'instant";
  const units: [number, string][] = [[60, 'min'], [24, 'h'], [30, 'j'], [12, 'mois']];
  let value = seconds / 60;
  for (const [size, label] of units) {
    if (value < size) return `il y a ${Math.floor(value)} ${label}`;
    value /= size;
  }
  return `il y a ${Math.floor(value)} an(s)`;
}

export const ORDER_STATUS_LABELS: Record<string, string> = {
  PAID: 'Payée — à expédier',
  SHIPPED: 'Expédiée',
  DELIVERED: 'Livrée',
  COMPLETED: 'Finalisée',
  CANCELLED: 'Annulée',
  DISPUTED: 'En litige',
  REFUNDED: 'Remboursée',
};

export const OFFER_STATUS_LABELS: Record<string, string> = {
  PENDING: 'En attente',
  ACCEPTED: 'Acceptée',
  DECLINED: 'Refusée',
  CANCELLED: 'Annulée',
  EXPIRED: 'Expirée',
  USED: 'Utilisée',
};

export const CONDITION_LABELS: Record<string, string> = {
  NEW_WITH_TAGS: 'Neuf avec étiquette',
  NEW_WITHOUT_TAGS: 'Neuf sans étiquette',
  VERY_GOOD: 'Très bon état',
  GOOD: 'Bon état',
  SATISFACTORY: 'Satisfaisant',
};
