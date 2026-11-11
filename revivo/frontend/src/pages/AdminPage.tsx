import { Link } from 'react-router-dom';
import { useMutation, useQuery, useQueryClient } from '@tanstack/react-query';
import { AdminApi } from '../api/endpoints';
import { Empty, Spinner } from '../components/ui';
import { formatPrice, timeAgo } from '../utils/format';

export function AdminPage() {
  const queryClient = useQueryClient();
  const stats = useQuery({ queryKey: ['admin', 'stats'], queryFn: AdminApi.stats, refetchInterval: 15_000 });
  const reports = useQuery({ queryKey: ['admin', 'reports'], queryFn: AdminApi.reports });
  const disputes = useQuery({ queryKey: ['admin', 'disputes'], queryFn: AdminApi.disputes });
  const invalidate = () => queryClient.invalidateQueries({ queryKey: ['admin'] });
  const resolve = useMutation({ mutationFn: (a: { id: number; action: 'REMOVE_ITEM' | 'BAN_USER' | 'DISMISS' }) => AdminApi.resolve(a.id, a.action), onSuccess: invalidate });
  const arbitrate = useMutation({ mutationFn: (a: { id: number; refund: boolean }) => AdminApi.resolveDispute(a.id, a.refund), onSuccess: invalidate });

  if (stats.isLoading) return <Spinner />;
  const s = stats.data!;
  const tiles: [string, string | number][] = [
    ['Membres', s.users], ['Articles en ligne', s.activeItems], ['Articles vendus', s.soldItems],
    ['Commandes en cours', s.ordersInProgress], ['Litiges ouverts', s.openDisputes], ['Signalements', s.openReports],
    ['Volume finalisé', formatPrice(s.completedVolume)], ['Outbox en attente', s.outboxBacklog],
  ];

  return (
    <>
      <h1>Modération</h1>
      <div className="stats-grid">
        {tiles.map(([label, value]) => (
          <div key={label} className="card stat"><span className="muted small">{label}</span><strong className="stat-value">{value}</strong></div>
        ))}
      </div>

      <h2>Signalements</h2>
      {!reports.data?.content.length ? <Empty title="Aucun signalement en attente" /> : (
        <table className="table">
          <thead><tr><th>Motif</th><th>Cible</th><th>Par</th><th>Quand</th><th /></tr></thead>
          <tbody>
            {reports.data.content.map((r) => (
              <tr key={r.id}>
                <td>{r.reason}<div className="small muted">{r.details}</div></td>
                <td>{r.itemId ? <Link to={`/items/${r.itemId}`}>{r.itemTitle}</Link> : null} {r.reportedUsername && <span className="muted">({r.reportedUsername})</span>}</td>
                <td>{r.reporterUsername}</td>
                <td>{timeAgo(r.createdAt)}</td>
                <td className="btn-row">
                  {r.itemId && <button className="btn btn-danger btn-sm" onClick={() => resolve.mutate({ id: r.id, action: 'REMOVE_ITEM' })}>Retirer</button>}
                  <button className="btn btn-danger btn-sm" onClick={() => resolve.mutate({ id: r.id, action: 'BAN_USER' })}>Suspendre</button>
                  <button className="btn btn-ghost btn-sm" onClick={() => resolve.mutate({ id: r.id, action: 'DISMISS' })}>Classer</button>
                </td>
              </tr>
            ))}
          </tbody>
        </table>
      )}

      <h2>Litiges</h2>
      {!disputes.data?.content.length ? <Empty title="Aucun litige" /> : (
        <table className="table">
          <thead><tr><th>Commande</th><th>Motif</th><th>Montant</th><th /></tr></thead>
          <tbody>
            {disputes.data.content.map((o) => (
              <tr key={o.id}>
                <td><Link to={`/orders/${o.id}`}>#{o.id} — {o.itemTitle}</Link><div className="small muted">{o.buyer.username} → {o.seller.username}</div></td>
                <td>{o.disputeReason}</td>
                <td>{formatPrice(o.total)}</td>
                <td className="btn-row">
                  <button className="btn btn-primary btn-sm" onClick={() => arbitrate.mutate({ id: o.id, refund: true })}>Rembourser l'acheteur</button>
                  <button className="btn btn-ghost btn-sm" onClick={() => arbitrate.mutate({ id: o.id, refund: false })}>Payer le vendeur</button>
                </td>
              </tr>
            ))}
          </tbody>
        </table>
      )}
    </>
  );
}
