import { useState } from 'react';
import { Link } from 'react-router-dom';
import { useMutation, useQuery, useQueryClient } from '@tanstack/react-query';
import { errorMessage } from '../api/client';
import { OffersApi } from '../api/endpoints';
import { Badge, Empty, ErrorBox, Spinner } from '../components/ui';
import { formatPrice, OFFER_STATUS_LABELS, timeAgo } from '../utils/format';

export function OffersPage() {
  const [tab, setTab] = useState<'received' | 'sent'>('received');
  const [error, setError] = useState<string | null>(null);
  const queryClient = useQueryClient();
  const { data, isLoading } = useQuery({ queryKey: ['offers', tab], queryFn: tab === 'received' ? OffersApi.received : OffersApi.sent });
  const act = useMutation({
    mutationFn: (fn: () => Promise<unknown>) => fn(),
    onSuccess: () => queryClient.invalidateQueries({ queryKey: ['offers'] }),
    onError: (e) => setError(errorMessage(e)),
  });

  return (
    <>
      <h1>Offres</h1>
      <div className="tabs">
        <button className={tab === 'received' ? 'active' : ''} onClick={() => setTab('received')}>Reçues</button>
        <button className={tab === 'sent' ? 'active' : ''} onClick={() => setTab('sent')}>Envoyées</button>
      </div>
      <ErrorBox message={error} />
      {isLoading ? <Spinner /> : data?.content.length === 0 ? <Empty title="Aucune offre" /> : (
        <div className="list">
          {data?.content.map((o) => (
            <div key={o.id} className="list-row card">
              {o.itemPhotoUrl && <img className="thumb" src={o.itemPhotoUrl} alt="" />}
              <div className="grow">
                <Link to={`/items/${o.itemId}`}><strong>{o.itemTitle}</strong></Link>
                <div className="muted small">
                  {tab === 'received' ? `${o.buyer.username} propose` : 'Vous proposez'} {formatPrice(o.amount)} au lieu de {formatPrice(o.itemPrice)} · {timeAgo(o.createdAt)}
                </div>
              </div>
              <div className="right">
                <Badge tone={o.status === 'ACCEPTED' ? 'success' : o.status === 'PENDING' ? 'warning' : 'neutral'}>{OFFER_STATUS_LABELS[o.status]}</Badge>
                {tab === 'received' && o.status === 'PENDING' && (
                  <div className="btn-row">
                    <button className="btn btn-primary btn-sm" onClick={() => act.mutate(() => OffersApi.accept(o.id))}>Accepter</button>
                    <button className="btn btn-ghost btn-sm" onClick={() => act.mutate(() => OffersApi.decline(o.id))}>Refuser</button>
                  </div>
                )}
                {tab === 'sent' && o.status === 'PENDING' && (
                  <button className="btn btn-ghost btn-sm" onClick={() => act.mutate(() => OffersApi.cancel(o.id))}>Annuler</button>
                )}
                {tab === 'sent' && o.status === 'ACCEPTED' && (
                  <Link className="btn btn-primary btn-sm" to={`/items/${o.itemId}`}>Acheter à {formatPrice(o.amount)}</Link>
                )}
              </div>
            </div>
          ))}
        </div>
      )}
    </>
  );
}
