import { useNavigate } from 'react-router-dom';
import { useMutation, useQuery, useQueryClient } from '@tanstack/react-query';
import { NotificationsApi } from '../api/endpoints';
import { Empty, Spinner } from '../components/ui';
import { timeAgo } from '../utils/format';

export function NotificationsPage() {
  const navigate = useNavigate();
  const queryClient = useQueryClient();
  const { data, isLoading } = useQuery({ queryKey: ['notifications', 'list'], queryFn: NotificationsApi.list });
  const invalidate = () => queryClient.invalidateQueries({ queryKey: ['notifications'] });
  const readAll = useMutation({ mutationFn: NotificationsApi.readAll, onSuccess: invalidate });
  const read = useMutation({ mutationFn: NotificationsApi.read, onSuccess: invalidate });

  if (isLoading) return <Spinner />;
  return (
    <>
      <div className="row-between">
        <h1>Notifications</h1>
        <button className="btn btn-ghost" onClick={() => readAll.mutate()}>Tout marquer comme lu</button>
      </div>
      {!data?.content.length ? <Empty title="Rien de nouveau" /> : (
        <ul className="list">
          {data.content.map((n) => (
            <li key={n.id}>
              <button
                className={`notification card ${n.read ? '' : 'unread'}`}
                onClick={() => {
                  if (!n.read) read.mutate(n.id);
                  if (n.link) navigate(n.link);
                }}
              >
                <strong>{n.title}</strong>
                {n.body && <span>{n.body}</span>}
                <small className="muted">{timeAgo(n.createdAt)}</small>
              </button>
            </li>
          ))}
        </ul>
      )}
    </>
  );
}
