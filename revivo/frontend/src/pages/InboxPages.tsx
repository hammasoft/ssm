import { useEffect, useRef, useState, type FormEvent } from 'react';
import { Link, useParams } from 'react-router-dom';
import { useMutation, useQuery, useQueryClient } from '@tanstack/react-query';
import { MessagingApi } from '../api/endpoints';
import { Avatar, Empty, Spinner } from '../components/ui';
import { timeAgo } from '../utils/format';

function ConversationList({ activeId }: { activeId?: number }) {
  const { data, isLoading } = useQuery({ queryKey: ['conversations'], queryFn: MessagingApi.inbox });
  if (isLoading) return <Spinner />;
  if (!data?.content.length) return <Empty title="Aucune conversation" />;
  return (
    <ul className="conversations">
      {data.content.map((c) => (
        <li key={c.id}>
          <Link to={`/inbox/${c.id}`} className={`conversation ${c.id === activeId ? 'active' : ''}`}>
            <Avatar user={c.other} />
            <div className="grow">
              <div className="row-between">
                <strong>{c.other.username}</strong>
                <small className="muted">{timeAgo(c.lastMessageAt)}</small>
              </div>
              {c.itemTitle && <div className="small muted">{c.itemTitle}</div>}
              <div className={`small ${c.unread ? 'unread' : 'muted'}`}>{c.lastMessage}</div>
            </div>
            {c.unread > 0 && <span className="dot static">{c.unread}</span>}
          </Link>
        </li>
      ))}
    </ul>
  );
}

export function InboxPage() {
  return (
    <div className="inbox">
      <aside><h1>Messages</h1><ConversationList /></aside>
      <section className="conversation-empty muted">Sélectionnez une conversation</section>
    </div>
  );
}

export function ConversationPage() {
  const id = Number(useParams().id);
  const queryClient = useQueryClient();
  const [text, setText] = useState('');
  const bottom = useRef<HTMLDivElement>(null);

  // Rafraîchissement : le notification-service pousse NEW_MESSAGE en SSE, le polling sert de filet de sécurité.
  const { data } = useQuery({
    queryKey: ['conversations', id, 'messages'],
    queryFn: () => MessagingApi.messages(id),
    refetchInterval: 10_000,
  });
  const messages = [...(data?.content ?? [])].reverse();

  useEffect(() => {
    bottom.current?.scrollIntoView({ behavior: 'smooth' });
    queryClient.invalidateQueries({ queryKey: ['conversations', 'unread'] });
  }, [messages.length, queryClient]);

  const send = useMutation({
    mutationFn: () => MessagingApi.send(id, text),
    onSuccess: () => {
      setText('');
      queryClient.invalidateQueries({ queryKey: ['conversations'] });
    },
  });

  const submit = (e: FormEvent) => {
    e.preventDefault();
    if (text.trim()) send.mutate();
  };

  return (
    <div className="inbox">
      <aside className="hide-mobile"><h1>Messages</h1><ConversationList activeId={id} /></aside>
      <section className="thread card">
        <div className="messages">
          {messages.map((m) => (
            <div key={m.id} className={`bubble ${m.mine ? 'mine' : ''}`}>
              {m.content}
              <small>{timeAgo(m.createdAt)}{m.mine && m.readAt ? ' · Lu' : ''}</small>
            </div>
          ))}
          <div ref={bottom} />
        </div>
        <form className="composer" onSubmit={submit}>
          <input value={text} onChange={(e) => setText(e.target.value)} placeholder="Écrire un message…" maxLength={2000} />
          <button className="btn btn-primary" disabled={!text.trim() || send.isPending}>Envoyer</button>
        </form>
      </section>
    </div>
  );
}
