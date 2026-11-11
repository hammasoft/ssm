import { useState } from 'react';
import { useMutation, useQuery, useQueryClient } from '@tanstack/react-query';
import { errorMessage } from '../api/client';
import { WalletApi } from '../api/endpoints';
import { ErrorBox, Spinner } from '../components/ui';
import { formatDate, formatPrice } from '../utils/format';

export function WalletPage() {
  const queryClient = useQueryClient();
  const summary = useQuery({ queryKey: ['wallet'], queryFn: WalletApi.summary });
  const history = useQuery({ queryKey: ['wallet', 'transactions'], queryFn: WalletApi.transactions });
  const [amount, setAmount] = useState('');
  const [iban, setIban] = useState('FR7630006000011234567890189');
  const [error, setError] = useState<string | null>(null);
  const withdraw = useMutation({
    mutationFn: () => WalletApi.withdraw(Number(amount), iban.replace(/\s/g, '').toUpperCase()),
    onSuccess: () => {
      setAmount('');
      setError(null);
      queryClient.invalidateQueries({ queryKey: ['wallet'] });
    },
    onError: (e) => setError(errorMessage(e)),
  });

  if (summary.isLoading) return <Spinner />;
  return (
    <>
      <h1>Porte-monnaie</h1>
      <div className="grid-2">
        <div className="card stat">
          <span className="muted">Disponible</span>
          <strong className="stat-value">{formatPrice(summary.data?.available)}</strong>
          <div className="inline-form">
            <input type="number" min={1} step="0.01" placeholder="Montant" value={amount} onChange={(e) => setAmount(e.target.value)} />
            <input placeholder="IBAN" value={iban} onChange={(e) => setIban(e.target.value)} />
            <button className="btn btn-primary" disabled={!amount} onClick={() => withdraw.mutate()}>Retirer</button>
          </div>
          <ErrorBox message={error} />
        </div>
        <div className="card stat">
          <span className="muted">En attente (séquestre)</span>
          <strong className="stat-value">{formatPrice(summary.data?.pending)}</strong>
          <p className="small muted">Libéré quand l'acheteur confirme la réception, ou automatiquement 48 h après la livraison.</p>
        </div>
      </div>
      <h2>Historique</h2>
      <table className="table">
        <thead><tr><th>Date</th><th>Opération</th><th className="right">Montant</th></tr></thead>
        <tbody>
          {history.data?.content.map((t) => (
            <tr key={t.id}>
              <td>{formatDate(t.createdAt)}</td>
              <td>{t.description}{t.orderId && <span className="muted"> · commande {t.orderId}</span>}</td>
              <td className={`right ${t.amount < 0 ? 'negative' : 'positive'}`}>{formatPrice(t.amount)}</td>
            </tr>
          ))}
        </tbody>
      </table>
    </>
  );
}
