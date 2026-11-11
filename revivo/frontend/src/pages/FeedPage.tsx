import { useQuery } from '@tanstack/react-query';
import { Link } from 'react-router-dom';
import { ItemsApi } from '../api/endpoints';
import { ItemGrid } from '../components/ItemCard';
import { Empty, Spinner } from '../components/ui';

export function FeedPage() {
  const { data, isLoading } = useQuery({ queryKey: ['items', 'feed'], queryFn: () => ItemsApi.feed() });
  if (isLoading) return <Spinner />;
  return (
    <>
      <h1>Mon fil d'actualité</h1>
      {data && data.content.length > 0 ? (
        <ItemGrid items={data.content} />
      ) : (
        <Empty title="Votre fil est vide">
          <p className="muted">Abonnez-vous à des membres pour voir leurs nouveautés ici. <Link to="/">Explorer le catalogue</Link></p>
        </Empty>
      )}
    </>
  );
}
