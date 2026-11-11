import { useEffect, useState, type ChangeEvent, type FormEvent } from 'react';
import { useNavigate, useParams } from 'react-router-dom';
import { useQuery } from '@tanstack/react-query';
import { errorMessage } from '../api/client';
import { ItemsApi } from '../api/endpoints';
import type { ItemRequest } from '../api/types';
import { ErrorBox, Spinner } from '../components/ui';
import { useCatalog } from '../hooks/useCatalog';

const EMPTY: ItemRequest = { title: '', description: '', categoryId: 0, brandId: null, size: '', color: '', condition: 'VERY_GOOD', price: 0, photoUrls: [] };

/** Création et édition d'annonce (même formulaire). */
export function SellPage() {
  const editId = useParams().id ? Number(useParams().id) : null;
  const navigate = useNavigate();
  const { categories, brands, meta } = useCatalog();
  const [form, setForm] = useState<ItemRequest>(EMPTY);
  const [uploading, setUploading] = useState(false);
  const [busy, setBusy] = useState(false);
  const [error, setError] = useState<string | null>(null);

  const { data: existing, isLoading } = useQuery({ queryKey: ['items', editId], queryFn: () => ItemsApi.get(editId!), enabled: !!editId });
  useEffect(() => {
    if (existing) {
      setForm({
        title: existing.title, description: existing.description, categoryId: existing.categoryId, brandId: existing.brandId ?? null,
        size: existing.size ?? '', color: existing.color ?? '', condition: existing.condition, price: existing.price, photoUrls: existing.photos,
      });
    }
  }, [existing]);

  const update = <K extends keyof ItemRequest>(key: K, value: ItemRequest[K]) => setForm((f) => ({ ...f, [key]: value }));

  const onFiles = async (e: ChangeEvent<HTMLInputElement>) => {
    const files = Array.from(e.target.files ?? []).slice(0, 10 - form.photoUrls.length);
    setUploading(true);
    setError(null);
    try {
      const urls = await Promise.all(files.map((f) => ItemsApi.upload(f).then((r) => r.url)));
      update('photoUrls', [...form.photoUrls, ...urls]);
    } catch (err) {
      setError(errorMessage(err));
    } finally {
      setUploading(false);
      e.target.value = '';
    }
  };

  const submit = async (e: FormEvent) => {
    e.preventDefault();
    setBusy(true);
    setError(null);
    const body = { ...form, brandId: form.brandId || null, size: form.size || undefined, color: form.color || undefined };
    try {
      const saved = editId ? await ItemsApi.update(editId, body) : await ItemsApi.create(body);
      navigate(`/items/${saved.id}`);
    } catch (err) {
      setError(errorMessage(err));
    } finally {
      setBusy(false);
    }
  };

  if (editId && isLoading) return <Spinner />;

  return (
    <div className="sell card">
      <h1>{editId ? "Modifier l'annonce" : 'Vendre un article'}</h1>
      <ErrorBox message={error} />
      <form className="form" onSubmit={submit}>
        <fieldset>
          <legend>Photos (1 à 10)</legend>
          <div className="photo-picker">
            {form.photoUrls.map((url, i) => (
              <div key={url} className="photo-thumb">
                <img src={url} alt={`Photo ${i + 1}`} />
                <button type="button" aria-label="Retirer" onClick={() => update('photoUrls', form.photoUrls.filter((u) => u !== url))}>×</button>
                {i === 0 && <span className="cover">Couverture</span>}
              </div>
            ))}
            {form.photoUrls.length < 10 && (
              <label className="photo-add">
                {uploading ? '…' : '+ Ajouter'}
                <input type="file" accept="image/jpeg,image/png,image/webp" multiple hidden onChange={onFiles} />
              </label>
            )}
          </div>
        </fieldset>
        <label>Titre<input value={form.title} onChange={(e) => update('title', e.target.value)} required minLength={5} maxLength={100} placeholder="ex : Veste en jean Levi's" /></label>
        <label>Description<textarea rows={4} value={form.description} onChange={(e) => update('description', e.target.value)} required minLength={10} maxLength={3000} placeholder="État, défauts éventuels, raison de la vente…" /></label>
        <div className="grid-2">
          <label>Catégorie
            <select value={form.categoryId || ''} onChange={(e) => update('categoryId', Number(e.target.value))} required>
              <option value="">Choisir…</option>
              {categories.map((c) => (
                <optgroup key={c.id} label={c.name}>
                  {c.children.map((s) => <option key={s.id} value={s.id}>{s.name}</option>)}
                </optgroup>
              ))}
            </select>
          </label>
          <label>Marque
            <select value={form.brandId ?? ''} onChange={(e) => update('brandId', e.target.value ? Number(e.target.value) : null)}>
              <option value="">Sans marque</option>
              {brands.map((b) => <option key={b.id} value={b.id}>{b.name}</option>)}
            </select>
          </label>
          <label>Taille
            <select value={form.size} onChange={(e) => update('size', e.target.value)}>
              <option value="">—</option>
              {meta?.sizes.map((s) => <option key={s}>{s}</option>)}
            </select>
          </label>
          <label>Couleur
            <select value={form.color} onChange={(e) => update('color', e.target.value)}>
              <option value="">—</option>
              {meta?.colors.map((c) => <option key={c}>{c}</option>)}
            </select>
          </label>
          <label>État
            <select value={form.condition} onChange={(e) => update('condition', e.target.value)}>
              {meta?.conditions.map((c) => <option key={c.code} value={c.code}>{c.label}</option>)}
            </select>
          </label>
          <label>Prix (€)<input type="number" min={1} step="0.01" value={form.price || ''} onChange={(e) => update('price', Number(e.target.value))} required /></label>
        </div>
        <p className="muted small">Aucune commission vendeur : vous recevez l'intégralité du prix. Les frais de protection sont payés par l'acheteur.</p>
        <button className="btn btn-primary btn-block" disabled={busy || uploading || form.photoUrls.length === 0}>
          {busy ? 'Enregistrement…' : editId ? 'Enregistrer' : 'Publier'}
        </button>
      </form>
    </div>
  );
}
