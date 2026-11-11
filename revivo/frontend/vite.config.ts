/// <reference types="vitest" />
import { defineConfig } from 'vite';
import react from '@vitejs/plugin-react';

// En dev, le proxy reproduit le routage de la passerelle Nginx utilisée en production :
//   /api/notifications → notification-service, /api et /uploads → marketplace-service
export default defineConfig({
  plugins: [react()],
  server: {
    port: 5173,
    proxy: {
      '/api/notifications': { target: 'http://localhost:8082', changeOrigin: true },
      '/api': { target: 'http://localhost:8081', changeOrigin: true },
      '/uploads': { target: 'http://localhost:8081', changeOrigin: true },
    },
  },
  test: {
    environment: 'jsdom',
    globals: true,
    setupFiles: './src/setupTests.ts',
  },
});
