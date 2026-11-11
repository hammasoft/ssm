import { useState } from 'react';
import { Link, useParams } from 'react-router-dom';
import { useMutation, useQuery, useQueryClient } from '@tanstack/react-query';
import { errorMessage } from '../api/client';
import { OrdersApi } from '../api/endpoints';
import type { Order } from '../api/types';
import { Badge, Empty, ErrorBox, Spinner, UserChip } from '../components/ui';
import { formatDate, formatPrice, ORDER_STATUS_LABELS } from '../utils/format';

const tone = (s: string) =>
  s === 'COMPLETED' ? 'success' : s === 'DISPUTED' || s === 'CANCELLED' || s === 'REFUNDED' ? 'danger' : s === 'PAID' ? 'warning' : 'info';

export function OrdersPage() {
  const [tab, setTab] = useState<'purchases' | 'sales'>('purchases');
  const { data, isLoading } = useQuery({
    queryKey: ['orders', tab],
    queryFn: tab === 'purchases' ? OrdersApi.purchases : OrdersApi.sales,
  });
  return (
    <>
      <h1>Mes commandes</h1>
      <div className="tabs">
        <button className={tab === 'purchases' ? 'active' : ''} onClick={() => setTab('purchases')}>Achats</button>
        <button className={tab === 'sales' ? 'active' : ''} onClick={() => setTab('sales')}>Ventes</button>
      </div>
      {isLoading ? <Spinner /> : data?.content.length === 0 ? <Empty title="Aucune commande" /> : (
        <div className="list">
          {data?.content.map((o) => (
            <Link key={o.id} to={`/orders/${o.id}`} className="list-row card">
              {o.itemPhotoUrl && <img className="thumb" src={o.itemPhotoUrl} alt="" />}
              <div className="grow">
                <strong>{o.itemTitle}</strong>
                <div className="muted small">
                  {tab === 'purchases' ? `Vendeur : ${o.seller.username}` : `Acheteur : ${o.buyer.username}`} · {formatDate(o.paidAt)}
                </div>
              </div>
              <div className="right">
                <div>{formatPrice(tab === 'purchases' ? o.total : o.itemPrice)}</div>
                <Badge tone={tone(o.status)}>{ORDER_STATUS_LABELS[o.status]}</Badge>
              </div>
            </Link>
          ))}
        </div>
      )}
    </>
  );
}

const STEPS: { key: keyof Order; label: string }[] = [
  { key: 'paidAt', label: 'Payée' },
  { key: 'shippedAt', label: 'Expédiée' },
  { key: 'deliveredAt', label: 'Livrée' },
  { key: 'completedAt', label: 'Finalisée' },
];

export function OrderPage() {
  const id = Number(useParams().id);
  const queryClient = useQueryClient();
  const [error, setError] = useState<string | null>(null);
  const [tracking, setTracking] = useState('');
  const [reason, setReason] = useState('');
  const [rating, setRating] = useState(5);
  const [comment, setComment] = useState('');

  const { data: o, isLoading } = useQuery({ queryKey: ['orders', id], queryFn: () => OrdersApi.get(id) });
  const act = useMutation({
    mutationFn: (fn: () => Promise<unknown>) => fn(),
    onSuccess: () => {
      setError(null);
      queryClient.invalidateQueries({ queryKey: ['orders'] });
      queryClient.invalidateQueries({ queryKey: ['wallet'] });
    },
    onError: (e) => setError(errorMessage(e)),
  });

  if (isLoading || !o) return <Spinner />;
  const seller = o.role === 'SELLER';
  const buyer = o.role === 'BUYER';
  const canShip = seller && o.status === 'PAID' && o.shippingOption !== 'HAND_DELIVERY';
  const canConfirm = buyer && (o.status === 'SHIPPED' || o.status === 'DELIVERED' || (o.status === 'PAID' && o.shippingOption === 'HAND_DELIVERY'));

  return (
    <div className="order-page">
      <h1>Commande n° {o.id}</h1>
      <div className="card order-head">
        {o.itemPhotoUrl && <img className="thumb-lg" src={o.itemPhotoUrl} alt="" />}
        <div className="grow">
          <Link to={`/items/${o.itemId}`}><strong>{o.itemTitle}</strong></Link>
          <div>{seller ? <>Acheteur : <UserChip user={o.buyer} /></> : <>Vendeur : <UserChip user={o.seller} /></>}</div>
          <Badge tone={tone(o.status)}>{ORDER_STATUS_LABELS[o.status]}</Badge>
        </div>
      </div>

      <ol className="timeline">
        {STEPS.map((s) => (
          <li key={s.key} className={o[s.key] ? 'done' : ''}>
            <span>{s.label}</span>
            <small>{formatDate(o[s.key] as string | undefined)}</small>
          </li>
        ))}
      </ol>

      <div className="grid-2">
        <div className="card">
          <h3>Détail</h3>
          <table className="summary">
            <tbody>
              <tr><td>Article</td><td>{formatPrice(o.itemPrice)}</td></tr>
              {buyer && <tr><td>Protection acheteur</td><td>{formatPrice(o.protectionFee)}</td></tr>}
              {buyer && <tr><td>Livraison</td><td>{formatPrice(o.shippingPrice)}</td></tr>}
              <tr className="total"><td>{buyer ? 'Total payé' : 'Vous recevez'}</td><td>{formatPrice(buyer ? o.total : o.itemPrice)}</td></tr>
            </tbody>
          </table>
          <p className="small muted">Livraison : {o.shippingOption === 'HAND_DELIVERY' ? 'remise en main propre' : o.shippingAddress}</p>
          {o.trackingNumber && <p className="small">Suivi : <code>{o.trackingNumber}</code></p>}
          {o.disputeReason && <p className="small">Litige : {o.disputeReason}</p>}
        </div>

        <div className="card">
          <h3>Actions</h3>
          <ErrorBox message={error} />
          {canShip && (
            <div className="inline-form">
              <input placeholder="N° de suivi" value={tracking} onChange={(e) => setTracking(e.target.value)} />
              <button className="btn btn-primary" onClick={() => act.mutate(() => OrdersApi.ship(id, tracking))}>Déclarer l'envoi</button>
            </div>
          )}
          {seller && o.status === 'SHIPPED' && (
            <button className="btn btn-ghost btn-block" onClick={() => act.mutate(() => OrdersApi.delivered(id))}>Simuler la livraison (transporteur)</button>
          )}
          {canConfirm && (
            <button className="btn btn-primary btn-block" onClick={() => act.mutate(() => OrdersApi.confirm(id))}>Tout est OK, finaliser</button>
          )}
          {o.status === 'PAID' && o.role !== 'ADMIN' && (
            <div className="inline-form">
              <input placeholder="Motif d'annulation" value={reason} onChange={(e) => setReason(e.target.value)} />
              <button className="btn btn-danger" disabled={!reason} onClick={() => act.mutate(() => OrdersApi.cancel(id, reason))}>Annuler</button>
            </div>
          )}
          {buyer && (o.status === 'SHIPPED' || o.status === 'DELIVERED') && (
            <div className="inline-form">
              <input placeholder="Décrivez le problème" value={reason} onChange={(e) => setReason(e.target.value)} />
              <button className="btn btn-danger" disabled={!reason} onClick={() => act.mutate(() => OrdersApi.dispute(id, reason))}>Signaler un problème</button>
            </div>
          )}
          {o.status === 'COMPLETED' && !o.reviewedByMe && o.role !== 'ADMIN' && (
            <div className="form">
              <label>Votre évaluation
                <select value={rating} onChange={(e) => setRating(Number(e.target.value))}>
                  {[5, 4, 3, 2, 1].map((r) => <option key={r} value={r}>{'★'.repeat(r)}</option>)}
                </select>
              </label>
              <textarea rows={2} placeholder="Un commentaire ?" value={comment} onChange={(e) => setComment(e.target.value)} />
              <button className="btn btn-primary" onClick={() => act.mutate(() => OrdersApi.review(id, rating, comment))}>Publier l'avis</button>
            </div>
          )}
          {o.status === 'COMPLETED' && o.reviewedByMe && <p className="muted">Merci pour votre évaluation ⭐</p>}
          {o.status === 'DISPUTED' && <p className="muted">Notre équipe de modération examine le litige.</p>}
        </div>
      </div>
    </div>
  );
}
