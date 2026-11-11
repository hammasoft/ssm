import { useQuery } from '@tanstack/react-query';
import { CatalogApi } from '../api/endpoints';

const STATIC = { staleTime: Infinity, gcTime: Infinity } as const;

/** Référentiels quasi statiques : chargés une fois par session. */
export function useCatalog() {
  const categories = useQuery({ queryKey: ['categories'], queryFn: CatalogApi.categories, ...STATIC });
  const brands = useQuery({ queryKey: ['brands'], queryFn: CatalogApi.brands, ...STATIC });
  const meta = useQuery({ queryKey: ['meta'], queryFn: CatalogApi.meta, ...STATIC });
  return { categories: categories.data ?? [], brands: brands.data ?? [], meta: meta.data };
}
