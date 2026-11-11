import { useState, type FormEvent } from 'react';
import { Link, NavLink, Outlet, useNavigate, useSearchParams } from 'react-router-dom';
import { useAuth } from '../auth/AuthContext';
import { useCatalog } from '../hooks/useCatalog';
import { useNotifications } from '../hooks/useNotifications';
import { Avatar } from './ui';

export function Layout() {
  const { user, logout } = useAuth();
  const navigate = useNavigate();
  const [params] = useSearchParams();
  const [q, setQ] = useState(params.get('q') ?? '');
  const [menuOpen, setMenuOpen] = useState(false);
  const { categories } = useCatalog();
  const { unreadNotifications, unreadMessages, toast, dismissToast } = useNotifications(!!user);

  const search = (e: FormEvent) => {
    e.preventDefault();
    navigate(q.trim() ? `/?q=${encodeURIComponent(q.trim())}` : '/');
  };

  return (
    <>
      <header className="header">
        <div className="container header-inner">
          <Link to="/" className="logo" aria-label="Revivo — accueil">
            <span className="logo-mark">R</span>Revivo
          </Link>
          <form className="search" onSubmit={search} role="search">
            <input value={q} onChange={(e) => setQ(e.target.value)} placeholder="Rechercher des articles, marques…" aria-label="Rechercher" />
          </form>
          <nav className="header-actions">
            {user ? (
              <>
                <NavLink to="/inbox" className="icon-link" title="Messages">
                  ✉{unreadMessages > 0 && <span className="dot">{unreadMessages}</span>}
                </NavLink>
                <NavLink to="/notifications" className="icon-link" title="Notifications">
                  🔔{unreadNotifications > 0 && <span className="dot">{unreadNotifications}</span>}
                </NavLink>
                <NavLink to="/favorites" className="icon-link" title="Favoris">♡</NavLink>
                <div className="menu">
                  <button className="menu-trigger" onClick={() => setMenuOpen((o) => !o)} aria-expanded={menuOpen}>
                    <Avatar user={user} size={32} />
                  </button>
                  {menuOpen && (
                    <div className="menu-panel" onClick={() => setMenuOpen(false)}>
                      <Link to={`/members/${user.id}`}>Mon dressing</Link>
                      <Link to="/feed">Mon fil d'actualité</Link>
                      <Link to="/orders">Mes commandes</Link>
                      <Link to="/offers">Mes offres</Link>
                      <Link to="/wallet">Porte-monnaie</Link>
                      <Link to="/settings">Paramètres</Link>
                      {user.role === 'ADMIN' && <Link to="/admin">Modération</Link>}
                      <button onClick={() => logout().then(() => navigate('/'))}>Se déconnecter</button>
                    </div>
                  )}
                </div>
                <Link to="/sell" className="btn btn-primary">Vendre</Link>
              </>
            ) : (
              <>
                <Link to="/login" className="btn btn-ghost">Se connecter</Link>
                <Link to="/register" className="btn btn-primary">S'inscrire</Link>
              </>
            )}
          </nav>
        </div>
        <div className="container categories-bar">
          {categories.map((c) => (
            <Link key={c.id} to={`/?categoryId=${c.id}`}>{c.name}</Link>
          ))}
        </div>
      </header>

      <main className="container main">
        <Outlet />
      </main>

      {toast && (
        <div className="toast" role="status" onClick={() => { dismissToast(); if (toast.link) navigate(toast.link); }}>
          <strong>{toast.title}</strong>
          {toast.body && <p>{toast.body}</p>}
        </div>
      )}

      <footer className="footer">
        <div className="container">
          <span>Revivo — projet de démonstration (Spring Boot · Kafka · React)</span>
          <span className="muted">Paiements simulés : aucune transaction réelle.</span>
        </div>
      </footer>
    </>
  );
}
