import { useState } from 'react';
import { useNavigate, useParams } from 'react-router-dom';
import { useMutation, useQuery, useQueryClient } from '@tanstack/react-query';
import { UsersApi } from '../api/endpoints';
import { useAuth } from '../auth/AuthContext';
import { ItemGrid } from '../components/ItemCard';
import { Avatar, Badge, Empty, Spinner, Stars } from '../components/ui';
import { formatDate, timeAgo } from '../utils/format';

export function ProfilePage() {
  const id = Number(useParams().id);
  const { user } = useAuth();
  const navigate = useNavigate();
  const queryClient = useQueryClient();
  const [tab, setTab] = useState<'items' | 'reviews'>('items');

  const { data: profile, isLoading } = useQuery({ queryKey: ['profile', id], queryFn: () => UsersApi.profile(id) });
  const items = useQuery({ queryKey: ['items', 'seller', id], queryFn: () => UsersApi.items(id) });
  const reviews = useQuery({ queryKey: ['reviews', id], queryFn: () => UsersApi.reviews(id), enabled: tab === 'reviews' });
  const follow = useMutation({
    mutationFn: () => (profile!.followedByMe ? UsersApi.unfollow(id) : UsersApi.follow(id)),
    onSuccess: () => queryClient.invalidateQueries({ queryKey: ['profile', id] }),
  });

  if (isLoading || !profile) return <Spinner />;
  const isMe = user?.id === id;

  return (
    <>
      <section className="profile card">
        <Avatar user={profile} size={88} />
        <div className="profile-info">
          <h1>{profile.username} {profile.holidayMode && <Badge tone="warning">En vacances</Badge>}</h1>
          <Stars value={profile.ratingAvg} count={profile.ratingCount} />
          <p className="muted">
            {[profile.city, profile.country].filter(Boolean).join(', ')} · Membre depuis {formatDate(profile.memberSince)}
          </p>
          {profile.bio && <p>{profile.bio}</p>}
          <p className="profile-stats">
            <strong>{profile.activeItems}</strong> articles · <strong>{profile.followers}</strong> abonnés · <strong>{profile.following}</strong> abonnements
          </p>
        </div>
        {!isMe && (
          <button className={`btn ${profile.followedByMe ? 'btn-ghost' : 'btn-primary'}`} onClick={() => (user ? follow.mutate() : navigate('/login'))}>
            {profile.followedByMe ? 'Abonné ✓' : "S'abonner"}
          </button>
        )}
      </section>

      <div className="tabs">
        <button className={tab === 'items' ? 'active' : ''} onClick={() => setTab('items')}>Dressing</button>
        <button className={tab === 'reviews' ? 'active' : ''} onClick={() => setTab('reviews')}>Évaluations ({profile.ratingCount})</button>
      </div>

      {tab === 'items' &&
        (items.data && items.data.content.length > 0 ? <ItemGrid items={items.data.content} /> : <Empty title="Aucun article en vente" />)}

      {tab === 'reviews' && (
        <div className="list">
          {reviews.data?.content.length === 0 && <Empty title="Pas encore d'évaluation" />}
          {reviews.data?.content.map((r) => (
            <div key={r.id} className="list-row card">
              <Avatar user={r.author} />
              <div className="grow">
                <strong>{r.author.username}</strong> <span className="muted small">({r.authorRole === 'BUYER' ? 'acheteur' : 'vendeur'}) · {timeAgo(r.createdAt)}</span>
                <div><Stars value={r.rating} /></div>
                {r.comment && <p>{r.comment}</p>}
                <p className="muted small">{r.itemTitle}</p>
              </div>
            </div>
          ))}
        </div>
      )}
    </>
  );
}
