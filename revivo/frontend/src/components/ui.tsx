import type { ReactNode } from 'react';
import { Link } from 'react-router-dom';
import type { UserSummary } from '../api/types';

export function Stars({ value, count }: { value: number; count?: number }) {
  const rounded = Math.round(Number(value));
  return (
    <span className="stars" aria-label={`Note ${value} sur 5`}>
      {'★'.repeat(rounded)}
      <span className="stars-empty">{'★'.repeat(5 - rounded)}</span>
      {count !== undefined && <span className="muted"> ({count})</span>}
    </span>
  );
}

export function Avatar({ user, size = 36 }: { user: Pick<UserSummary, 'username' | 'avatarUrl'>; size?: number }) {
  return user.avatarUrl ? (
    <img className="avatar" src={user.avatarUrl} alt={user.username} style={{ width: size, height: size }} />
  ) : (
    <span className="avatar avatar-letter" style={{ width: size, height: size, fontSize: size / 2.2 }}>
      {user.username.charAt(0).toUpperCase()}
    </span>
  );
}

export function UserChip({ user }: { user: UserSummary }) {
  return (
    <Link to={`/members/${user.id}`} className="user-chip">
      <Avatar user={user} size={28} />
      <span>{user.username}</span>
      {user.ratingCount > 0 && <Stars value={user.ratingAvg} count={user.ratingCount} />}
    </Link>
  );
}

export function Badge({ children, tone = 'neutral' }: { children: ReactNode; tone?: 'neutral' | 'success' | 'warning' | 'danger' | 'info' }) {
  return <span className={`badge badge-${tone}`}>{children}</span>;
}

export function Empty({ title, children }: { title: string; children?: ReactNode }) {
  return (
    <div className="empty">
      <div className="empty-icon" aria-hidden>🧺</div>
      <h3>{title}</h3>
      {children}
    </div>
  );
}

export function Spinner() {
  return <div className="spinner" role="status" aria-label="Chargement" />;
}

export function ErrorBox({ message }: { message?: string | null }) {
  return message ? <div className="alert alert-error" role="alert">{message}</div> : null;
}

export function Pagination({ page, totalPages, onChange }: { page: number; totalPages: number; onChange: (p: number) => void }) {
  if (totalPages <= 1) return null;
  return (
    <nav className="pagination" aria-label="Pagination">
      <button className="btn btn-ghost" disabled={page === 0} onClick={() => onChange(page - 1)}>← Précédent</button>
      <span>Page {page + 1} / {totalPages}</span>
      <button className="btn btn-ghost" disabled={page + 1 >= totalPages} onClick={() => onChange(page + 1)}>Suivant →</button>
    </nav>
  );
}
