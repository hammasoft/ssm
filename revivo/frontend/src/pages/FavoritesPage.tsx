import { useQuery } from '@tanstack/react-query';
import { FavoritesApi } from '../api/endpoints';
import { ItemGrid } from '../components/ItemCard';
import { Empty, Spinner } from '../components/ui';

export function FavoritesPage() {
  const { data, isLoading } = useQuery({ queryKey: ['favorites'], queryFn: () => FavoritesApi.list() });
  if (isLoading) return <Spinner />;
  return (
    <>
      <h1>Mes favoris</h1>
      {data && data.content.length > 0 ? (
        <ItemGrid items={data.content} />
      ) : (
        <Empty title="Aucun favori pour l'instant">
          <p className="muted">Cliquez sur ♡ pour suivre un article : vous serez prévenu en cas de baisse de prix.</p>
        </Empty>
      )}
    </>
  );
}
