import { Link, Route, Routes } from 'react-router-dom';
import { Layout } from './components/Layout';
import { RequireAuth } from './components/RequireAuth';
import { Empty } from './components/ui';
import { AdminPage } from './pages/AdminPage';
import { LoginPage, RegisterPage } from './pages/AuthPages';
import { FavoritesPage } from './pages/FavoritesPage';
import { FeedPage } from './pages/FeedPage';
import { HomePage } from './pages/HomePage';
import { ConversationPage, InboxPage } from './pages/InboxPages';
import { ItemPage } from './pages/ItemPage';
import { NotificationsPage } from './pages/NotificationsPage';
import { OffersPage } from './pages/OffersPage';
import { OrderPage, OrdersPage } from './pages/OrdersPages';
import { ProfilePage } from './pages/ProfilePage';
import { SellPage } from './pages/SellPage';
import { SettingsPage } from './pages/SettingsPage';
import { WalletPage } from './pages/WalletPage';

const auth = (el: JSX.Element) => <RequireAuth>{el}</RequireAuth>;

export default function App() {
  return (
    <Routes>
      <Route element={<Layout />}>
        <Route index element={<HomePage />} />
        <Route path="login" element={<LoginPage />} />
        <Route path="register" element={<RegisterPage />} />
        <Route path="items/:id" element={<ItemPage />} />
        <Route path="members/:id" element={<ProfilePage />} />
        <Route path="sell" element={auth(<SellPage />)} />
        <Route path="items/:id/edit" element={auth(<SellPage />)} />
        <Route path="feed" element={auth(<FeedPage />)} />
        <Route path="favorites" element={auth(<FavoritesPage />)} />
        <Route path="offers" element={auth(<OffersPage />)} />
        <Route path="orders" element={auth(<OrdersPage />)} />
        <Route path="orders/:id" element={auth(<OrderPage />)} />
        <Route path="inbox" element={auth(<InboxPage />)} />
        <Route path="inbox/:id" element={auth(<ConversationPage />)} />
        <Route path="wallet" element={auth(<WalletPage />)} />
        <Route path="notifications" element={auth(<NotificationsPage />)} />
        <Route path="settings" element={auth(<SettingsPage />)} />
        <Route path="admin" element={<RequireAuth admin><AdminPage /></RequireAuth>} />
        <Route path="*" element={<Empty title="Page introuvable"><Link to="/">Retour à l'accueil</Link></Empty>} />
      </Route>
    </Routes>
  );
}
