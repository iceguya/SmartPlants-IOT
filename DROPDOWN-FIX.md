# ⚠️ PENTING: Dropdown Bug Fix

## Masalah
Dropdown navbar **tidak berfungsi** setelah menjalankan `npm run dev` karena file `public/hot` membuat Laravel load assets dari Vite dev server yang HMR-nya tidak kompatibel dengan Alpine.js.

## Solusi Cepat

### Jika Dropdown Sudah Bug:
Jalankan file batch ini:
```bash
fix-dropdown.bat
```

Atau manual:
```bash
# 1. Stop npm run dev (Ctrl+C)
# 2. Hapus file hot
del public\hot

# 3. Build ulang
npm run build

# 4. Refresh browser (Ctrl+F5)
```

---

## Workflow Development yang Benar

### ❌ JANGAN Pakai Ini (Akan Bug):
```bash
npm run dev
```
**Kenapa?** Vite dev server membuat file `public/hot` yang menyebabkan Alpine.js tidak ter-load.

---

### ✅ Pakai Salah Satu Ini:

#### Option 1: Build Sekali (Recommended untuk Testing)
```bash
npm run build
```
- Paling stabil
- Dropdown pasti jalan
- Harus rebuild manual setiap ubah CSS/JS

#### Option 2: Watch Mode (Auto-rebuild)
```bash
npm run watch
```
- Auto rebuild saat file berubah
- Tidak pakai HMR (lebih stabil)
- Dropdown tetap berfungsi
- Harus refresh manual browser

#### Option 3: Pakai Helper Script
```bash
dev.bat
```
Menu interaktif untuk pilih mode development.

---

## Cara Kerja (Technical)

### Kenapa Bug?
1. `npm run dev` → Vite dev server start di port 5173
2. Vite create file `public/hot` berisi URL dev server
3. Laravel Vite plugin baca file `hot` → load assets dari dev server
4. HMR (Hot Module Replacement) interfere dengan Alpine.js initialization
5. Dropdown **tidak berfungsi** karena Alpine.js events tidak ter-register

### Kenapa `npm run build` Fix?
1. `npm run build` → Compile assets ke `public/build/`
2. **Tidak** create file `public/hot`
3. Laravel load assets dari `public/build/manifest.json`
4. Alpine.js ter-load sempurna
5. Dropdown **berfungsi normal**

---

## Quick Commands

| Command | Kapan Pakai | Dropdown Work? |
|---------|-------------|----------------|
| `npm run build` | Setelah edit CSS/JS | ✅ Yes |
| `npm run watch` | Development (auto-rebuild) | ✅ Yes |
| `npm run dev` | **JANGAN PAKAI** | ❌ No (bug!) |
| `fix-dropdown.bat` | Saat dropdown bug | ✅ Fix |

---

## Tips
- **Selalu** jalankan `npm run build` sebelum testing
- Gunakan `npm run watch` jika sering edit CSS/JS
- Jangan lupa **Ctrl+F5** (hard refresh) di browser
- Jika dropdown bug, cukup jalankan `fix-dropdown.bat`

---

## Alternatif (Advanced)
Jika tetap ingin pakai `npm run dev`, edit `vite.config.js`:
```javascript
server: {
  // Disable HMR untuk Alpine.js
  hmr: false,
}
```
Tapi ini akan disable hot reload sepenuhnya.
