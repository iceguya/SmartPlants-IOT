import { defineConfig } from 'vite';
import laravel from 'laravel-vite-plugin';

export default defineConfig({
  server: {
    host: '0.0.0.0',    // dengarkan semua IP
    port: 5173,         // port Vite default
    strictPort: false,
    hmr: {
      protocol: 'ws',   // websocket untuk HMR
      host: '192.168.43.206', // ganti dengan IP mesin yang dipakai (lihat screenshot)
      port: 5173,
    },
  },
  plugins: [
    laravel({
      input: ['resources/css/app.css', 'resources/js/app.js'],
      refresh: true,
    }),
  ],
});
