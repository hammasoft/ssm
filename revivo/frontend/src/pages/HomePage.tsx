import { useQuery, keepPreviousData } from '@tanstack/react-query';
import { useSearchParams } from 'react-router-dom';
import { ItemsApi, type SearchParams } from '../api/endpoints';
import { ItemGrid } from '../components/ItemCard';
import { Empty, Pagination, Spinner } from '../components/ui';
import { useCatalog } from '../hooks/useCatalog';

const FILTER_KEYS = ['q', 'categoryId', 'brandIds', 'sizes', 'conditions', 'minPrice', 'maxPrice', 'sort'] as const;

export function HomePage() {
  const [params, setParams] = useSearchParams();
  const { categories, brands, meta } = useCatalog();

  const search: SearchParams = { page: Number(params.get('page') ?? 0) };
  FILTER_KEYS.forEach((k) => {
    const v = params.get(k);
    if (v) (search as Record<string, unknown>)[k] = v;
  });

  const { data, isLoading } = useQuery({
    queryKey: ['items', 'search', search],
    queryFn: () => ItemsApi.search(search),
    placeholderData: keepPreviousData,
  });

  const set = (key: string, value: string) => {
    const next = new URLSearchParams(params);
    if (value) next.set(key, value);
    else next.delete(key);
    if (key !== 'page') next.delete('page');
    setParams(next);
  };

  const hasFilters = FILTER_KEYS.some((k) => params.get(k));

  return (
    <>
      {!hasFilters && (
        <section className="hero">
          <div>
            <h1>Donnez une seconde vie à vos vêtements.</h1>
            <p>Vendez sans commission, achetez en toute sécurité grâce au paiement protégé.</p>
          </div>
        </section>
      )}

      <div className="filters">
        <select value={params.get('categoryId') ?? ''} onChange={(e) => set('categoryId', e.target.value)} aria-label="Catégorie">
          <option value="">Toutes catégories</option>
          {categories.map((c) => (
            <optgroup key={c.id} label={c.name}>
              <option value={c.id}>Tout « {c.name} »</option>
              {c.children.map((s) => <option key={s.id} value={s.id}>{s.name}</option>)}
            </optgroup>
          ))}
        </select>
        <select value={params.get('brandIds') ?? ''} onChange={(e) => set('brandIds', e.target.value)} aria-label="Marque">
          <option value="">Toutes marques</option>
          {brands.map((b) => <option key={b.id} value={b.id}>{b.name}</option>)}
        </select>
        <select value={params.get('sizes') ?? ''} onChange={(e) => set('sizes', e.target.value)} aria-label="Taille">
          <option value="">Toutes tailles</option>
          {meta?.sizes.map((s) => <option key={s} value={s}>{s}</option>)}
        </select>
        <select value={params.get('conditions') ?? ''} onChange={(e) => set('conditions', e.target.value)} aria-label="État">
          <option value="">Tous états</option>
          {meta?.conditions.map((c) => <option key={c.code} value={c.code}>{c.label}</option>)}
        </select>
        <input type="number" min={0} placeholder="Prix min" value={params.get('minPrice') ?? ''} onChange={(e) => set('minPrice', e.target.value)} />
        <input type="number" min={0} placeholder="Prix max" value={params.get('maxPrice') ?? ''} onChange={(e) => set('maxPrice', e.target.value)} />
        <select value={params.get('sort') ?? 'newest'} onChange={(e) => set('sort', e.target.value)} aria-label="Tri">
          <option value="newest">Plus récents</option>
          <option value="price_asc">Prix croissant</option>
          <option value="price_desc">Prix décroissant</option>
          <option value="popular">Populaires</option>
        </select>
        {hasFilters && <button className="btn btn-ghost" onClick={() => setParams({})}>Effacer</button>}
      </div>

      {isLoading ? (
        <Spinner />
      ) : data && data.content.length > 0 ? (
        <>
          <p className="muted">{data.totalElements} résultat{data.totalElements > 1 ? 's' : ''}</p>
          <ItemGrid items={data.content} />
          <Pagination page={data.page} totalPages={data.totalPages} onChange={(p) => set('page', String(p))} />
        </>
      ) : (
        <Empty title="Aucun article ne correspond à votre recherche">
          <p className="muted">Essayez d'élargir vos filtres.</p>
        </Empty>
      )}
    </>
  );
}
