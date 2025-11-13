import { defineConfig } from 'vite';
import laravel from 'laravel-vite-plugin';
import os from 'os';
import fs from 'fs';
import path from 'path';

// Fungsi baca .env Laravel (opsional, fallback ke Wi-Fi IP)
function getLaravelAppUrl() {
  try {
    const envPath = path.resolve(__dirname, '.env');
    const envContent = fs.readFileSync(envPath, 'utf-8');
    const match = envContent.match(/^APP_URL=(.+)$/m);
    if (match) {
      return match[1].trim().replace(/\/+$/, '');
    }
  } catch {
    return null;
  }
  return null;
}

// Fungsi ambil IP dari Wireless LAN Adapter
function getWifiIp() {
  const interfaces = os.networkInterfaces();
  for (const name in interfaces) {
    // Hanya ambil adapter yang mengandung kata "Wi-Fi" atau "Wireless"
    if (name.toLowerCase().includes('wi-fi') || name.toLowerCase().includes('wireless')) {
      for (const iface of interfaces[name]) {
        if (iface.family === 'IPv4' && !iface.internal) {
          return iface.address;
        }
      }
    }
  }

  // Jika tidak ada adapter Wi-Fi aktif, fallback ke IP dari APP_URL atau default
  const appUrl = getLaravelAppUrl();
  if (appUrl) return new URL(appUrl).hostname;
  return '127.0.0.1';
}

const localIP = getWifiIp();

console.log(`\n🌐  IP dari Wireless LAN Adapter Wi-Fi terdeteksi: ${localIP}`);
console.log('🧠  Vite akan otomatis menyesuaikan dengan jaringan Wi-Fi kamu.\n');

export default defineConfig({
  server: {
    host: '0.0.0.0',
    port: 5173,
    strictPort: false,
    hmr: {
      protocol: 'ws',
      host: localIP,
      port: 5173,
    },
    cors: true,
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
            console.log(`🌐  Server berjalan di IP Wi-Fi: \x1b[36m${localIP}:5173\x1b[0m`);
          }, 100);
        });
      },
    },
  ],
});
