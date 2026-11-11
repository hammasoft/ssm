import { Link, useNavigate } from 'react-router-dom';
import { useMutation, useQueryClient } from '@tanstack/react-query';
import { FavoritesApi } from '../api/endpoints';
import type { ItemCard as Item } from '../api/types';
import { useAuth } from '../auth/AuthContext';
import { formatPrice } from '../utils/format';
import { Avatar } from './ui';

export function ItemCard({ item }: { item: Item }) {
  const { user } = useAuth();
  const navigate = useNavigate();
  const queryClient = useQueryClient();
  const toggle = useMutation({
    mutationFn: () => (item.favorited ? FavoritesApi.remove(item.id) : FavoritesApi.add(item.id)),
    onSuccess: () => {
      queryClient.invalidateQueries({ queryKey: ['items'] });
      queryClient.invalidateQueries({ queryKey: ['favorites'] });
    },
  });

  return (
    <article className="item-card">
      <Link to={`/members/${item.seller.id}`} className="item-card-seller">
        <Avatar user={item.seller} size={22} />
        <span>{item.seller.username}</span>
      </Link>
      <Link to={`/items/${item.id}`} className="item-card-photo">
        {item.photoUrl ? <img src={item.photoUrl} alt={item.title} loading="lazy" /> : <div className="photo-placeholder" />}
        {item.status === 'SOLD' && <span className="ribbon">Vendu</span>}
        {item.status === 'HIDDEN' && <span className="ribbon ribbon-muted">Masqué</span>}
      </Link>
      <div className="item-card-body">
        <div className="item-card-price-row">
          <span className="price">{formatPrice(item.price)}</span>
          <button
            className={`fav ${item.favorited ? 'fav-on' : ''}`}
            aria-label={item.favorited ? 'Retirer des favoris' : 'Ajouter aux favoris'}
            onClick={() => (user ? toggle.mutate() : navigate('/login'))}
          >
            {item.favorited ? '♥' : '♡'} {item.favoriteCount > 0 && item.favoriteCount}
          </button>
        </div>
        <div className="muted small">{formatPrice(item.priceWithProtection)} incl. protection</div>
        <div className="item-card-meta">{[item.size, item.brand].filter(Boolean).join(' · ')}</div>
      </div>
    </article>
  );
}

export function ItemGrid({ items }: { items: Item[] }) {
  return (
    <div className="item-grid">
      {items.map((i) => (
        <ItemCard key={i.id} item={i} />
      ))}
    </div>
  );
}
