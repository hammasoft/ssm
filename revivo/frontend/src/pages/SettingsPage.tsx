import { useState, type FormEvent } from 'react';
import { errorMessage } from '../api/client';
import { UsersApi } from '../api/endpoints';
import { useAuth } from '../auth/AuthContext';
import { ErrorBox } from '../components/ui';

export function SettingsPage() {
  const { user, setUser } = useAuth();
  const [form, setForm] = useState({
    bio: user?.bio ?? '', city: user?.city ?? '', country: user?.country ?? 'FR', avatarUrl: user?.avatarUrl ?? '', holidayMode: user?.holidayMode ?? false,
  });
  const [pwd, setPwd] = useState({ current: '', next: '' });
  const [message, setMessage] = useState<string | null>(null);
  const [error, setError] = useState<string | null>(null);

  const save = async (e: FormEvent) => {
    e.preventDefault();
    setError(null);
    try {
      setUser(await UsersApi.update({ ...form, avatarUrl: form.avatarUrl || undefined, country: form.country || undefined }));
      setMessage('Profil enregistré.');
    } catch (err) {
      setError(errorMessage(err));
    }
  };

  const changePassword = async (e: FormEvent) => {
    e.preventDefault();
    setError(null);
    try {
      await UsersApi.changePassword(pwd.current, pwd.next);
      setPwd({ current: '', next: '' });
      setMessage('Mot de passe modifié. Vos autres sessions ont été déconnectées.');
    } catch (err) {
      setError(errorMessage(err));
    }
  };

  return (
    <div className="settings">
      <h1>Paramètres</h1>
      {message && <div className="alert alert-success">{message}</div>}
      <ErrorBox message={error} />
      <form className="card form" onSubmit={save}>
        <h2>Profil</h2>
        <label>Bio<textarea rows={3} maxLength={500} value={form.bio} onChange={(e) => setForm({ ...form, bio: e.target.value })} /></label>
        <div className="grid-2">
          <label>Ville<input value={form.city} onChange={(e) => setForm({ ...form, city: e.target.value })} /></label>
          <label>Pays (ISO)<input value={form.country} maxLength={2} onChange={(e) => setForm({ ...form, country: e.target.value.toUpperCase() })} /></label>
        </div>
        <label className="checkbox">
          <input type="checkbox" checked={form.holidayMode} onChange={(e) => setForm({ ...form, holidayMode: e.target.checked })} />
          Mode vacances (mes articles sont masqués de la recherche)
        </label>
        <button className="btn btn-primary">Enregistrer</button>
      </form>
      <form className="card form" onSubmit={changePassword}>
        <h2>Sécurité</h2>
        <label>Mot de passe actuel<input type="password" value={pwd.current} onChange={(e) => setPwd({ ...pwd, current: e.target.value })} required /></label>
        <label>Nouveau mot de passe<input type="password" minLength={8} value={pwd.next} onChange={(e) => setPwd({ ...pwd, next: e.target.value })} required /></label>
        <button className="btn btn-outline">Changer le mot de passe</button>
      </form>
    </div>
  );
}
