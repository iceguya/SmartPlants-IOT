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
    {
      name: 'dropdown-warning',
      configureServer(server) {
        server.httpServer?.once('listening', () => {
          setTimeout(() => {
            console.log('\n\x1b[43m\x1b[30m%s\x1b[0m', '                                                          ');
            console.log('\x1b[43m\x1b[30m%s\x1b[0m', '  ⚠️  WARNING: Dropdown akan BUG dengan dev server!     ');
            console.log('\x1b[43m\x1b[30m%s\x1b[0m', '  ✅  Gunakan "npm run build" atau "npm run watch"      ');
            console.log('\x1b[43m\x1b[30m%s\x1b[0m', '  🔧  Jalankan "fix-dropdown.bat" jika dropdown bug     ');
            console.log('\x1b[43m\x1b[30m%s\x1b[0m', '                                                          ');
            console.log('');
          }, 100);
        });
      }
    }
  ],
});
