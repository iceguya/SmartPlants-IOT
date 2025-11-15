# 🛠️ Helper Scripts

Folder ini berisi script utilitas untuk development dan troubleshooting.

## Script Windows (.bat)

### dev.bat
**Development server script**

Script untuk menjalankan development server dengan langkah:
1. Hapus `public/hot` (fix dropdown bug)
2. Build assets dengan Vite
3. Start Laravel development server

**Cara pakai:**
```cmd
scripts\dev.bat
```

### fix-dropdown.bat
**Fix dropdown navbar bug**

Script khusus untuk memperbaiki masalah dropdown yang tidak berfungsi setelah Vite dev server.

**Masalah:** File `public/hot` yang dibuat Vite menyebabkan Alpine.js tidak load  
**Solusi:** Script ini hapus file tersebut dan rebuild assets

**Cara pakai:**
```cmd
scripts\fix-dropdown.bat
```

Setelah run script ini, refresh browser untuk melihat dropdown berfungsi kembali.

## Catatan

Script ini dibuat untuk Windows. Untuk Linux/Mac, buat shell script (.sh) dengan perintah equivalent.

**Contoh dev.sh (Linux/Mac):**
```bash
#!/bin/bash
rm -f public/hot
npm run build
php artisan serve
```
