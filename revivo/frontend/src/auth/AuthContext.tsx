import { createContext, useCallback, useContext, useEffect, useMemo, useState, type ReactNode } from 'react';
import { useQueryClient } from '@tanstack/react-query';
import { onSessionExpired, tokens } from '../api/client';
import { AuthApi, UsersApi } from '../api/endpoints';
import type { AuthResponse, Me } from '../api/types';

interface AuthState {
  user: Me | null;
  loading: boolean;
  login: (email: string, password: string) => Promise<void>;
  register: (username: string, email: string, password: string) => Promise<void>;
  logout: () => Promise<void>;
  setUser: (user: Me) => void;
}

const AuthContext = createContext<AuthState | null>(null);

export function AuthProvider({ children }: { children: ReactNode }) {
  const [user, setUser] = useState<Me | null>(null);
  const [loading, setLoading] = useState(true);
  const queryClient = useQueryClient();

  useEffect(() => {
    if (!tokens.access) {
      setLoading(false);
      return;
    }
    UsersApi.me()
      .then(setUser)
      .catch(() => tokens.clear())
      .finally(() => setLoading(false));
  }, []);

  useEffect(() => onSessionExpired(() => setUser(null)), []);

  const handleAuth = useCallback(
    (auth: AuthResponse) => {
      tokens.save(auth);
      setUser(auth.user);
      queryClient.invalidateQueries();
    },
    [queryClient],
  );

  const value = useMemo<AuthState>(
    () => ({
      user,
      loading,
      setUser,
      login: async (email, password) => handleAuth(await AuthApi.login(email, password)),
      register: async (username, email, password) => handleAuth(await AuthApi.register(username, email, password)),
      logout: async () => {
        const refresh = tokens.refresh;
        tokens.clear();
        setUser(null);
        queryClient.clear();
        if (refresh) await AuthApi.logout(refresh).catch(() => undefined);
      },
    }),
    [user, loading, handleAuth, queryClient],
  );

  return <AuthContext.Provider value={value}>{children}</AuthContext.Provider>;
}

export function useAuth(): AuthState {
  const ctx = useContext(AuthContext);
  if (!ctx) throw new Error('useAuth doit être utilisé dans <AuthProvider>');
  return ctx;
}
