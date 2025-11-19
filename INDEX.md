# 🗺️ SmartPlants-IOT - Quick Navigation

Panduan cepat untuk navigasi file dan folder dalam proyek ini.

## 🚀 Quick Start

1. **Setup Laravel:** [README.md](README.md#-instalasi)
2. **Setup ESP8266:** [docs/ESP8266_README.md](docs/ESP8266_README.md)
3. **Fix Dropdown Bug:** [docs/DROPDOWN-FIX.md](docs/DROPDOWN-FIX.md)

## 📂 Folder Structure

### Development Files
- 📚 [docs/](docs/) - Dokumentasi teknis
- 🔌 [esp8266/](esp8266/) - Arduino firmware untuk ESP8266
- 🛠️ [scripts/](scripts/) - Helper scripts (dev, fix, deploy)
- 🧪 [tests/manual/](tests/manual/) - Manual testing scripts

### Laravel Standard
- 💻 [app/](app/) - Application code (Controllers, Models, Services)
- 🗄️ [database/](database/) - Migrations & seeders
- 🎨 [resources/](resources/) - Views, CSS, JS
- 🛣️ [routes/](routes/) - Route definitions
- ⚙️ [config/](config/) - Configuration files

## 📖 Documentation

| File | Description |
|------|-------------|
| [README.md](README.md) | 📘 Main documentation |
| [STRUCTURE.md](STRUCTURE.md) | 📁 Folder organization guide |
| [docs/ESP8266_README.md](docs/ESP8266_README.md) | 🔌 ESP8266 setup & wiring |
| [docs/DROPDOWN-FIX.md](docs/DROPDOWN-FIX.md) | 🐛 Troubleshooting dropdown |

## 🛠️ Common Tasks

### Run Development Server
```bash
# Option 1: Using helper script (Windows)
scripts\dev.bat

# Option 2: Manual
npm run build
php artisan serve
```

### Upload ESP8266 Firmware
1. Open Arduino IDE
2. Open `esp8266/ESP8266_SmartPlants.ino`
3. Edit WiFi & API config
4. Upload to board

### Fix Dropdown Bug
```bash
scripts\fix-dropdown.bat
```

### Run Tests
```bash
# Automated tests
php artisan test

# Manual API tests
php tests/manual/test_ingest.php
php tests/manual/check_devices.php
```

## 🎯 File Categories

### Must Stay in Root (Laravel/Deploy Requirements)
- `composer.json`, `package.json` - Dependencies
- `artisan` - Laravel CLI
- `*.config.js` - Build configs
- `.env.example` - Environment template
- `nixpacks.toml`, `Caddyfile`, `procfile` - Deployment

### Organized in Folders
- Documentation → `docs/`
- ESP8266 code → `esp8266/`
- Scripts → `scripts/`
- Manual tests → `tests/manual/`

## 📞 Need Help?

- 🐛 **Bugs:** [GitHub Issues](https://github.com/kurokana/SmartPlants-IOT/issues)
- 📚 **Docs:** [docs/](docs/)
- 🔧 **Setup:** [README.md](README.md)
- 📁 **Structure:** [STRUCTURE.md](STRUCTURE.md)

---

**Happy Coding! 🌱**
