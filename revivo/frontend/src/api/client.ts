import axios, { AxiosError, type InternalAxiosRequestConfig } from 'axios';
import type { AuthResponse, Problem } from './types';

const ACCESS_KEY = 'revivo.accessToken';
const REFRESH_KEY = 'revivo.refreshToken';

/**
 * Stockage des jetons. Choix assumé pour une SPA de démonstration : localStorage.
 * En production, préférer un refresh token en cookie httpOnly + SameSite=strict (cf. docs/security.md).
 */
export const tokens = {
  get access() {
    return localStorage.getItem(ACCESS_KEY);
  },
  get refresh() {
    return localStorage.getItem(REFRESH_KEY);
  },
  save(auth: Pick<AuthResponse, 'accessToken' | 'refreshToken'>) {
    localStorage.setItem(ACCESS_KEY, auth.accessToken);
    localStorage.setItem(REFRESH_KEY, auth.refreshToken);
  },
  clear() {
    localStorage.removeItem(ACCESS_KEY);
    localStorage.removeItem(REFRESH_KEY);
  },
};

export const api = axios.create({ baseURL: '/api' });

api.interceptors.request.use((config) => {
  const token = tokens.access;
  if (token) config.headers.Authorization = `Bearer ${token}`;
  return config;
});

let refreshing: Promise<string | null> | null = null;
const listeners = new Set<() => void>();

/** Abonnement à la fin de session (refresh impossible) pour déconnecter l'UI. */
export function onSessionExpired(cb: () => void) {
  listeners.add(cb);
  return () => {
    listeners.delete(cb);
  };
}

async function refreshAccessToken(): Promise<string | null> {
  const refreshToken = tokens.refresh;
  if (!refreshToken) return null;
  try {
    const { data } = await axios.post<AuthResponse>('/api/auth/refresh', { refreshToken });
    tokens.save(data);
    return data.accessToken;
  } catch {
    tokens.clear();
    listeners.forEach((cb) => cb());
    return null;
  }
}

// Sur 401 : un seul refresh partagé entre les requêtes concurrentes, puis rejeu de la requête.
api.interceptors.response.use(undefined, async (error: AxiosError) => {
  const original = error.config as (InternalAxiosRequestConfig & { _retry?: boolean }) | undefined;
  if (error.response?.status === 401 && original && !original._retry && tokens.refresh) {
    original._retry = true;
    refreshing ??= refreshAccessToken().finally(() => (refreshing = null));
    const token = await refreshing;
    if (token) {
      original.headers.Authorization = `Bearer ${token}`;
      return api(original);
    }
  }
  return Promise.reject(error);
});

/** Message lisible à partir d'une erreur API. */
export function errorMessage(error: unknown): string {
  if (axios.isAxiosError(error)) {
    const problem = error.response?.data as Problem | undefined;
    if (problem?.errors) {
      return Object.entries(problem.errors)
        .map(([field, msg]) => `${field} : ${msg}`)
        .join(' · ');
    }
    if (problem?.detail) return problem.detail;
    if (error.response?.status === 401) return 'Veuillez vous connecter.';
  }
  return 'Une erreur inattendue est survenue.';
}
