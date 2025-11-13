import { defineConfig } from 'vite';
import laravel from 'laravel-vite-plugin';
import os from 'os';

// Fungsi untuk mendapatkan IP lokal secara otomatis
function getLocalIpAddress() {
  const interfaces = os.networkInterfaces();
  for (const interfaceName in interfaces) {
    for (const iface of interfaces[interfaceName]) {
      if (iface.family === 'IPv4' && !iface.internal) {
        return iface.address;
      }
    }
  }
  return '127.0.0.1'; // fallback kalau gagal deteksi
}

const localIP = getLocalIpAddress();

export default defineConfig({
  server: {
    host: '0.0.0.0',
    port: 5173,
    strictPort: false,
    hmr: {
      protocol: 'ws',
      host: localIP, // otomatis pakai IP lokal yang terdeteksi
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
            console.log(`🌐  Server berjalan di IP lokal: \x1b[36m${localIP}:5173\x1b[0m`);
          }, 100);
        });
      }
    }
  ],
});
