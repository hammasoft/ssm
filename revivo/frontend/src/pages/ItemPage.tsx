import { useState } from 'react';
import { Link, useNavigate, useParams } from 'react-router-dom';
import { useMutation, useQuery, useQueryClient } from '@tanstack/react-query';
import { errorMessage } from '../api/client';
import { FavoritesApi, ItemsApi, MessagingApi, OffersApi, OrdersApi, ReportsApi } from '../api/endpoints';
import type { ItemDetail, ShippingOption } from '../api/types';
import { useAuth } from '../auth/AuthContext';
import { ItemGrid } from '../components/ItemCard';
import { Badge, ErrorBox, Spinner, UserChip } from '../components/ui';
import { useCatalog } from '../hooks/useCatalog';
import { formatPrice, timeAgo } from '../utils/format';

type Panel = 'none' | 'buy' | 'offer' | 'message' | 'report';

export function ItemPage() {
  const id = Number(useParams().id);
  const { user } = useAuth();
  const navigate = useNavigate();
  const queryClient = useQueryClient();
  const [photo, setPhoto] = useState(0);
  const [panel, setPanel] = useState<Panel>('none');
  const [error, setError] = useState<string | null>(null);

  const { data: item, isLoading } = useQuery({ queryKey: ['items', id], queryFn: () => ItemsApi.get(id) });
  const { data: similar } = useQuery({ queryKey: ['items', id, 'similar'], queryFn: () => ItemsApi.similar(id) });

  const refresh = () => queryClient.invalidateQueries({ queryKey: ['items'] });
  const fav = useMutation({
    mutationFn: () => (item!.favorited ? FavoritesApi.remove(id) : FavoritesApi.add(id)),
    onSuccess: refresh,
  });
  const visibility = useMutation({ mutationFn: (hidden: boolean) => ItemsApi.setHidden(id, hidden), onSuccess: refresh, onError: (e) => setError(errorMessage(e)) });
  const remove = useMutation({ mutationFn: () => ItemsApi.remove(id), onSuccess: () => navigate(`/members/${user!.id}`), onError: (e) => setError(errorMessage(e)) });

  if (isLoading) return <Spinner />;
  if (!item) return <ErrorBox message="Article introuvable." />;

  const requireLogin = (p: Panel) => (user ? setPanel(panel === p ? 'none' : p) : navigate('/login', { state: { from: `/items/${id}` } }));

  return (
    <>
      <div className="item-page">
        <section className="gallery">
          <div className="gallery-main">
            {item.photos[photo] && <img src={item.photos[photo]} alt={item.title} />}
            {item.status === 'SOLD' && <span className="ribbon">Vendu</span>}
          </div>
          {item.photos.length > 1 && (
            <div className="gallery-thumbs">
              {item.photos.map((p, i) => (
                <button key={p} className={i === photo ? 'active' : ''} onClick={() => setPhoto(i)}>
                  <img src={p} alt={`Photo ${i + 1}`} />
                </button>
              ))}
            </div>
          )}
        </section>

        <aside className="card item-info">
          <div className="price-big">{formatPrice(item.price)}</div>
          <div className="muted">{formatPrice(item.priceWithProtection)} avec la protection acheteur</div>
          <h1 className="item-title">{item.title}</h1>
          <dl className="specs">
            {item.brand && (<><dt>Marque</dt><dd>{item.brand}</dd></>)}
            {item.size && (<><dt>Taille</dt><dd>{item.size}</dd></>)}
            <dt>État</dt><dd>{item.conditionLabel}</dd>
            {item.color && (<><dt>Couleur</dt><dd>{item.color}</dd></>)}
            <dt>Catégorie</dt><dd>{item.categoryName}</dd>
            <dt>Vues</dt><dd>{item.viewCount}</dd>
            <dt>Ajouté</dt><dd>{timeAgo(item.createdAt)}</dd>
          </dl>
          <p className="description">{item.description}</p>

          <ErrorBox message={error} />

          {item.mine ? (
            <div className="actions">
              {item.status === 'HIDDEN' && <Badge tone="warning">Annonce masquée</Badge>}
              {item.status !== 'SOLD' && <Link to={`/items/${id}/edit`} className="btn btn-primary btn-block">Modifier l'annonce</Link>}
              {item.status === 'ACTIVE' && <button className="btn btn-ghost btn-block" onClick={() => visibility.mutate(true)}>Masquer</button>}
              {item.status === 'HIDDEN' && <button className="btn btn-ghost btn-block" onClick={() => visibility.mutate(false)}>Réafficher</button>}
              {item.status !== 'SOLD' && (
                <button className="btn btn-danger btn-block" onClick={() => confirm('Supprimer définitivement cette annonce ?') && remove.mutate()}>Supprimer</button>
              )}
            </div>
          ) : (
            <div className="actions">
              {item.purchasable ? (
                <>
                  <button className="btn btn-primary btn-block" onClick={() => requireLogin('buy')}>Acheter</button>
                  <button className="btn btn-outline btn-block" onClick={() => requireLogin('offer')}>Faire une offre</button>
                </>
              ) : (
                <Badge tone="neutral">{item.status === 'SOLD' ? 'Cet article a été vendu' : 'Indisponible'}</Badge>
              )}
              <button className="btn btn-ghost btn-block" onClick={() => requireLogin('message')}>Envoyer un message</button>
              <button className="btn btn-ghost btn-block" onClick={() => (user ? fav.mutate() : navigate('/login'))}>
                {item.favorited ? '♥ Dans mes favoris' : '♡ Ajouter aux favoris'} ({item.favoriteCount})
              </button>
            </div>
          )}

          {panel === 'buy' && <CheckoutPanel item={item} />}
          {panel === 'offer' && <OfferPanel item={item} onDone={() => setPanel('none')} />}
          {panel === 'message' && <MessagePanel item={item} />}
          {panel === 'report' && <ReportPanel item={item} onDone={() => setPanel('none')} />}

          <div className="seller-box">
            <UserChip user={item.seller} />
          </div>
          {!item.mine && user && (
            <button className="link-button small" onClick={() => setPanel('report')}>Signaler cet article</button>
          )}
          <p className="protection-note">🛡️ Protection acheteur : votre paiement est conservé jusqu'à ce que vous confirmiez la bonne réception.</p>
        </aside>
      </div>

      {similar && similar.length > 0 && (
        <section>
          <h2>Articles similaires</h2>
          <ItemGrid items={similar} />
        </section>
      )}
    </>
  );
}

function CheckoutPanel({ item }: { item: ItemDetail }) {
  const navigate = useNavigate();
  const { meta } = useCatalog();
  const [shipping, setShipping] = useState<ShippingOption>('RELAY_POINT');
  const [address, setAddress] = useState('');
  const [card, setCard] = useState('tok_visa');
  const [error, setError] = useState<string | null>(null);
  const { data: quote } = useQuery({ queryKey: ['quote', item.id, shipping], queryFn: () => OrdersApi.quote(item.id, shipping) });
  const pay = useMutation({
    mutationFn: () => OrdersApi.checkout(item.id, { shippingOption: shipping, shippingAddress: address, paymentMethodToken: card }),
    onSuccess: (order) => navigate(`/orders/${order.id}`),
    onError: (e) => setError(errorMessage(e)),
  });

  return (
    <div className="panel">
      <h3>Paiement sécurisé</h3>
      <div className="radio-group">
        {meta?.shippingOptions.map((o) => (
          <label key={o.code}>
            <input type="radio" name="shipping" checked={shipping === o.code} onChange={() => setShipping(o.code)} /> {o.label}
          </label>
        ))}
      </div>
      {shipping !== 'HAND_DELIVERY' && (
        <label className="block">Adresse / point relais
          <input value={address} onChange={(e) => setAddress(e.target.value)} placeholder="12 rue des Lilas, 69001 Lyon" />
        </label>
      )}
      <label className="block">Carte (simulée)
        <select value={card} onChange={(e) => setCard(e.target.value)}>
          <option value="tok_visa">Visa •••• 4242 (acceptée)</option>
          <option value="tok_declined">Carte refusée (test)</option>
        </select>
      </label>
      {quote && (
        <table className="summary">
          <tbody>
            <tr><td>Article{quote.offerApplied && ' (offre acceptée)'}</td><td>{formatPrice(quote.itemPrice)}</td></tr>
            <tr><td>Protection acheteur</td><td>{formatPrice(quote.protectionFee)}</td></tr>
            <tr><td>Livraison</td><td>{formatPrice(quote.shippingPrice)}</td></tr>
            <tr className="total"><td>Total</td><td>{formatPrice(quote.total)}</td></tr>
          </tbody>
        </table>
      )}
      <ErrorBox message={error} />
      <button className="btn btn-primary btn-block" disabled={pay.isPending} onClick={() => pay.mutate()}>
        {pay.isPending ? 'Paiement…' : `Payer ${quote ? formatPrice(quote.total) : ''}`}
      </button>
    </div>
  );
}

function OfferPanel({ item, onDone }: { item: ItemDetail; onDone: () => void }) {
  const [amount, setAmount] = useState(Math.round(item.price * 0.85));
  const [error, setError] = useState<string | null>(null);
  const [sent, setSent] = useState(false);
  const make = useMutation({
    mutationFn: () => OffersApi.make(item.id, amount),
    onSuccess: () => setSent(true),
    onError: (e) => setError(errorMessage(e)),
  });
  if (sent) {
    return (
      <div className="panel">
        <p>✅ Offre envoyée ! Le vendeur a 48 h pour répondre. <Link to="/offers">Suivre mes offres</Link></p>
        <button className="btn btn-ghost" onClick={onDone}>Fermer</button>
      </div>
    );
  }
  return (
    <div className="panel">
      <h3>Proposer un prix</h3>
      <div className="chips">
        {[0.9, 0.8, 0.7].map((r) => (
          <button key={r} className="chip" onClick={() => setAmount(Math.round(item.price * r * 100) / 100)}>
            {formatPrice(Math.round(item.price * r * 100) / 100)}
          </button>
        ))}
      </div>
      <input type="number" step="0.5" min={1} value={amount} onChange={(e) => setAmount(Number(e.target.value))} />
      <ErrorBox message={error} />
      <button className="btn btn-primary btn-block" onClick={() => make.mutate()} disabled={make.isPending}>Envoyer l'offre</button>
    </div>
  );
}

function MessagePanel({ item }: { item: ItemDetail }) {
  const navigate = useNavigate();
  const [text, setText] = useState(`Bonjour, « ${item.title} » est-il toujours disponible ?`);
  const [error, setError] = useState<string | null>(null);
  const send = useMutation({
    mutationFn: () => MessagingApi.start(item.seller.id, text, item.id),
    onSuccess: (c) => navigate(`/inbox/${c.id}`),
    onError: (e) => setError(errorMessage(e)),
  });
  return (
    <div className="panel">
      <h3>Message à {item.seller.username}</h3>
      <textarea rows={3} value={text} onChange={(e) => setText(e.target.value)} />
      <ErrorBox message={error} />
      <button className="btn btn-primary btn-block" disabled={!text.trim()} onClick={() => send.mutate()}>Envoyer</button>
    </div>
  );
}

function ReportPanel({ item, onDone }: { item: ItemDetail; onDone: () => void }) {
  const [reason, setReason] = useState('COUNTERFEIT');
  const [details, setDetails] = useState('');
  const send = useMutation({ mutationFn: () => ReportsApi.create({ itemId: item.id, reason, details }), onSuccess: onDone });
  return (
    <div className="panel">
      <h3>Signaler l'article</h3>
      <select value={reason} onChange={(e) => setReason(e.target.value)}>
        <option value="COUNTERFEIT">Contrefaçon</option>
        <option value="PROHIBITED_ITEM">Article interdit</option>
        <option value="INAPPROPRIATE_CONTENT">Contenu inapproprié</option>
        <option value="SCAM">Arnaque</option>
        <option value="OTHER">Autre</option>
      </select>
      <textarea rows={2} placeholder="Précisions (optionnel)" value={details} onChange={(e) => setDetails(e.target.value)} />
      <button className="btn btn-danger btn-block" onClick={() => send.mutate()}>Envoyer le signalement</button>
    </div>
  );
}
