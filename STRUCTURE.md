# 📁 Struktur Direktori SmartPlants-IOT

Dokumentasi lengkap struktur folder dan organisasi file dalam proyek SmartPlants-IOT.

## 🎯 Prinsip Organisasi

### ✅ File di Root (Harus ada untuk Laravel/Deployment)
File-file berikut **HARUS** tetap di root directory karena requirement Laravel dan deployment platform:

- `composer.json`, `composer.lock` - PHP dependencies
- `package.json`, `package-lock.json` - Node dependencies
- `artisan` - Laravel CLI
- `vite.config.js`, `tailwind.config.js`, `postcss.config.js` - Build config
- `phpunit.xml` - Testing config
- `.env`, `.env.example` - Environment config
- `.gitignore`, `.gitattributes` - Git config
- `.editorconfig` - Editor config
- `nixpacks.toml` - Railway deployment
- `Caddyfile` - Caddy webserver config
- `procfile` - Process config untuk deployment
- `README.md` - Dokumentasi utama

### 📂 Folder Baru (Organisasi)
File-file yang **tidak required** di root dipindahkan ke folder terorganisir:

## 📚 `/docs` - Dokumentasi Teknis

**Isi:**
- `DROPDOWN-FIX.md` - Troubleshooting dropdown navbar
- `ESP8266_README.md` - Setup & wiring ESP8266
- `README.md` - Index dokumentasi

**Kapan menambahkan file di sini:**
- Tutorial atau guide
- Architecture documentation
- API documentation (Postman/Swagger export)
- Deployment guides
- Troubleshooting docs

## 🔌 `/esp8266` - Firmware Arduino

**Isi:**
- `ESP8266_SmartPlants.ino` - Main firmware (production)
- `esp8266_full_automation.ino` - Firmware dengan automasi lokal
- `fix.ino` - Testing/debugging sketches
- `README.md` - Upload instructions

**Kapan menambahkan file di sini:**
- File `.ino` untuk ESP8266/Arduino
- Library custom untuk ESP8266
- Firmware alternatif (ESP32, dll)

**Struktur yang disarankan:**
```
esp8266/
├── production/
│   └── ESP8266_SmartPlants.ino
├── experimental/
│   └── esp8266_full_automation.ino
├── tests/
│   └── fix.ino
└── README.md
```

## 🛠️ `/scripts` - Helper Scripts

**Isi:**
- `dev.bat` - Development server script (Windows)
- `fix-dropdown.bat` - Fix dropdown bug (Windows)
- `README.md` - Script documentation

**Cara pakai dari root:**
```cmd
scripts\dev.bat
scripts\fix-dropdown.bat
```

**Kapan menambahkan file di sini:**
- Automation scripts (bash, batch, PowerShell)
- Database seeders custom
- Deployment scripts
- Maintenance scripts

**Struktur yang disarankan:**
```
scripts/
├── windows/
│   ├── dev.bat
│   └── fix-dropdown.bat
├── linux/
│   ├── dev.sh
│   └── deploy.sh
└── README.md
```

## 🧪 `/tests/manual` - Manual Testing Scripts

**Isi:**
- `test_ingest.php` - Test API sensor ingestion
- `test_provision.php` - Test provisioning flow
- `check_devices.php` - Check device status
- `check_devices_detail.php` - Detailed device info
- `README.md` - Test script documentation

**Cara pakai:**
```bash
php tests/manual/test_ingest.php
php tests/manual/check_devices.php
```

**Kapan menambahkan file di sini:**
- PHP scripts untuk testing manual
- SQL queries untuk debugging
- Test data generators
- API testing scripts (sebelum ada Postman collection)

**Note:** Untuk automated tests, tetap gunakan `tests/Feature/` dan `tests/Unit/`

## 🗂️ Struktur Laravel Standar (Tidak Diubah)

Folder berikut adalah struktur Laravel standar dan **TIDAK** dipindahkan:

### `/app` - Application Code
- `Console/` - Artisan commands
- `Http/` - Controllers, Middleware, Requests
- `Models/` - Eloquent models
- `Providers/` - Service providers
- `Services/` - Business logic
- `Traits/` - Reusable traits
- `View/` - Blade components

### `/bootstrap` - Framework Bootstrap
- `app.php` - Bootstrap aplikasi
- `cache/` - Cached config & services

### `/config` - Configuration Files
- `app.php`, `database.php`, dll

### `/database` - Database Files
- `migrations/` - Database schema
- `seeders/` - Sample data
- `factories/` - Model factories

### `/public` - Public Assets
- `index.php` - Entry point
- `build/` - Compiled assets (Vite)

### `/resources` - Raw Assets & Views
- `views/` - Blade templates
- `css/` - Tailwind source
- `js/` - JavaScript source

### `/routes` - Route Definitions
- `web.php` - Web routes
- `api.php` - API routes

### `/storage` - Storage Files
- `app/` - User uploads
- `logs/` - Application logs
- `framework/` - Framework cache

### `/tests` - Automated Tests
- `Feature/` - Feature tests
- `Unit/` - Unit tests
- `manual/` - Manual test scripts (baru)

### `/vendor` - Composer Dependencies
Auto-generated, jangan diubah manual

## 📋 Checklist File Organization

Saat menambahkan file baru, tanyakan:

- [ ] Apakah file ini **required** di root? (Laravel/deployment)
  - ✅ Ya → Tetap di root
  - ❌ Tidak → Lanjut ke pertanyaan berikut

- [ ] File ini termasuk kategori apa?
  - 📚 Dokumentasi → `/docs`
  - 🔌 ESP8266 code → `/esp8266`
  - 🛠️ Helper script → `/scripts`
  - 🧪 Manual test → `/tests/manual`
  - 💻 Laravel code → `/app`, `/config`, dll (standar Laravel)

- [ ] Apakah ada README di folder tersebut?
  - ✅ Ada → Update README dengan file baru
  - ❌ Tidak ada → Buat README.md

## 🎨 Best Practices

1. **README di setiap folder custom** - Jelaskan tujuan folder dan cara pakai
2. **Naming convention** - Gunakan lowercase dengan underscore/dash
3. **Grouping** - Group file sejenis dalam subfolder jika > 5 files
4. **Git-friendly** - Jangan commit file temporary atau large binaries
5. **Documentation** - Update STRUCTURE.md ini saat ada perubahan major

## 🔄 Migration dari Struktur Lama

Jika ada file lama di root yang perlu dipindahkan:

```powershell
# Example: Pindahkan file .ino
Move-Item ESP8266_*.ino esp8266/

# Example: Pindahkan docs
Move-Item *README.md docs/
Move-Item DROPDOWN-FIX.md docs/

# Example: Pindahkan scripts
Move-Item *.bat scripts/

# Example: Pindahkan test files
Move-Item test_*.php tests/manual/
Move-Item check_*.php tests/manual/
```

## 📞 Pertanyaan?

Tidak yakin di mana harus menaruh file baru? Lihat dokumentasi ini atau tanyakan di Issues!

---

**Last Updated:** November 2025  
**Maintainer:** @kurokana
