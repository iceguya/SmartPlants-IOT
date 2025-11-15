# 🌱 SmartPlants-IOT

Sistem monitoring dan otomasi tanaman pintar berbasis IoT menggunakan ESP8266 dan Laravel. Proyek ini memungkinkan Anda untuk memantau kondisi tanaman secara real-time dan mengontrol sistem penyiraman secara otomatis atau manual melalui dashboard web.

## 📋 Daftar Isi

- [Fitur Utama](#-fitur-utama)
- [Arsitektur Sistem](#-arsitektur-sistem)
- [Teknologi](#-teknologi)
- [Persyaratan](#-persyaratan)
- [Instalasi](#-instalasi)
- [Konfigurasi](#-konfigurasi)
- [Cara Penggunaan](#-cara-penggunaan)
- [API Endpoints](#-api-endpoints)
- [Hardware Setup](#-hardware-setup)
- [Troubleshooting](#-troubleshooting)
- [Kontribusi](#-kontribusi)
- [Lisensi](#-lisensi)

## ✨ Fitur Utama

### 🖥️ Dashboard Web
- **Monitoring Real-time**: Pantau data sensor secara langsung
- **Visualisasi Data**: Grafik dan chart untuk tracking historis
- **Multi-Device Support**: Kelola beberapa perangkat IoT sekaligus
- **User Authentication**: Sistem login dengan Laravel Breeze
- **Responsive Design**: Tampilan optimal di desktop dan mobile

### 🤖 Otomasi
- **Automation Rules**: Buat aturan otomasi berdasarkan kondisi sensor
- **Smart Watering**: Penyiraman otomatis berdasarkan kelembaban tanah
- **Scheduling**: Jadwal penyiraman otomatis
- **Manual Control**: Kontrol manual sistem penyiraman via dashboard

### 📊 Monitoring Sensor
- **Kelembaban Tanah**: Monitoring tingkat kelembaban tanah
- **Suhu & Kelembaban Udara**: Pemantauan kondisi lingkungan
- **Sensor Warna RGB**: Analisis warna daun untuk deteksi kesehatan tanaman
- **Historical Data**: Penyimpanan dan analisis data historis

### 🔧 Device Management
- **Auto Provisioning**: Sistem registrasi perangkat otomatis dengan token
- **Device Authentication**: API key untuk setiap perangkat
- **Command Queue**: Sistem antrian perintah untuk kontrol perangkat
- **Device Status**: Monitor status online/offline perangkat

## 🏗️ Arsitektur Sistem

```
┌─────────────────┐
│   ESP8266       │
│   (Sensors +    │──── WiFi ────┐
│    Actuators)   │              │
└─────────────────┘              │
                                 ▼
                         ┌────────────────┐
                         │  Laravel API   │
                         │   (Backend)    │
                         └────────────────┘
                                 │
                    ┌────────────┼────────────┐
                    ▼            ▼            ▼
              ┌──────────┐ ┌──────────┐ ┌──────────┐
              │ Database │ │   Web    │ │   API    │
              │  (MySQL/ │ │Dashboard │ │Endpoints │
              │  SQLite) │ │          │ │          │
              └──────────┘ └──────────┘ └──────────┘
```

## 🛠️ Teknologi

### Backend
- **Framework**: Laravel 12.x
- **PHP**: ^8.2
- **Database**: MySQL / SQLite
- **Authentication**: Laravel Breeze

### Frontend
- **Templating**: Blade
- **CSS Framework**: Tailwind CSS
- **Build Tool**: Vite
- **JavaScript**: Vanilla JS / Alpine.js (via Breeze)

### IoT/Hardware
- **Microcontroller**: ESP8266
- **Protocol**: HTTP/HTTPS
- **Data Format**: JSON
- **Libraries**: 
  - ESP8266WiFi
  - ESP8266HTTPClient
  - ArduinoJson

## 📦 Persyaratan

### Server Requirements
- PHP >= 8.2
- Composer
- Node.js & NPM
- MySQL 8.0+ atau SQLite
- Web Server (Apache/Nginx) atau Laravel Development Server

### Hardware Requirements
- ESP8266 (NodeMCU, Wemos D1 Mini, dll)
- Sensor Kelembaban Tanah
- Sensor DHT22 (Temperature & Humidity)
- Sensor Warna TCS3200 (opsional)
- Relay Module untuk pompa air
- Pompa air mini
- Kabel jumper & breadboard

## 🚀 Instalasi

### 1. Clone Repository

```bash
git clone https://github.com/kurokana/SmartPlants-IOT.git
cd SmartPlants-IOT
```

### 2. Install Dependencies

```bash
# Install PHP dependencies
composer install

# Install Node dependencies
npm install
```

### 3. Setup Environment

```bash
# Copy file environment
cp .env.example .env

# Generate application key
php artisan key:generate
```

### 4. Konfigurasi Database

Edit file `.env` dan sesuaikan konfigurasi database:

```env
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=smartplants
DB_USERNAME=root
DB_PASSWORD=
```

Atau gunakan SQLite:

```env
DB_CONNECTION=sqlite
DB_DATABASE=/absolute/path/to/database.sqlite
```

### 5. Migrasi Database

```bash
php artisan migrate
```

### 6. Build Assets

```bash
npm run build
```

### 7. Jalankan Server

```bash
# Development server
php artisan serve

# Atau dengan npm script (includes queue & vite)
composer dev
```

Aplikasi akan berjalan di `http://localhost:8000`

## ⚙️ Konfigurasi

### Konfigurasi ESP8266

1. **Install Library Arduino**:
   - ESP8266WiFi
   - ESP8266HTTPClient
   - ArduinoJson (via Library Manager)

2. **Edit File `esp8266/ESP8266_SmartPlants.ino`**:

```cpp
// Ganti dengan kredensial WiFi Anda
const char* ssid = "NAMA_WIFI_ANDA";
const char* password = "PASSWORD_WIFI_ANDA";

// Ganti dengan IP/domain server Anda
const char* serverUrl = "http://192.168.1.100:8000";

// Token provisioning (dapatkan dari dashboard)
const char* provisionToken = "TOKEN_DARI_DASHBOARD";
```

3. **Upload ke ESP8266** melalui Arduino IDE

## 📖 Cara Penggunaan

### 1. Registrasi & Login

1. Buka `http://localhost:8000`
2. Klik **Register** untuk membuat akun
3. Login dengan akun yang telah dibuat

### 2. Generate Provisioning Token

1. Masuk ke menu **Provisioning** di dashboard
2. Klik **Generate Token**
3. Isi form:
   - **Name**: Nama untuk identifikasi token
   - **Max Uses**: Jumlah maksimal device yang bisa menggunakan token
   - **Expires At**: Tanggal kadaluarsa (opsional)
4. Klik **Generate**
5. Copy token yang dihasilkan

### 3. Provisioning Device (ESP8266)

1. Paste token ke dalam kode ESP8266:
   ```cpp
   const char* provisionToken = "TOKEN_ANDA_DI_SINI";
   ```
2. Upload kode ke ESP8266
3. Buka Serial Monitor (115200 baud)
4. Device akan otomatis:
   - Connect ke WiFi
   - Melakukan provisioning dengan server
   - Menyimpan credentials ke EEPROM
   - Mulai mengirim data sensor

### 4. Monitoring Device

1. Kembali ke **Dashboard**
2. Anda akan melihat device yang terdaftar
3. Klik device untuk melihat detail:
   - Data sensor real-time
   - Grafik historis
   - Status device

### 5. Kontrol Manual

1. Di halaman detail device
2. Klik tombol **Water On** untuk menyalakan pompa
3. Pompa akan menyala sesuai durasi yang ditentukan

### 6. Setup Automation

1. Buka halaman **Automation** dari detail device
2. Klik **Create New Rule**
3. Isi form automation:
   - **Rule Name**: Nama aturan (contoh: "Auto Water when Dry")
   - **Condition Type**: Pilih sensor (soil/temp/hum)
   - **Operator**: Pilih operator (<, >, =, ≤, ≥)
   - **Value**: Nilai threshold (contoh: 30 untuk kelembaban 30%)
   - **Action**: Pilih aksi (water_on, water_off)
   - **Duration**: Durasi aksi dalam detik
4. Klik **Save**
5. Toggle **Active/Inactive** untuk mengaktifkan/menonaktifkan rule

## 🔌 API Endpoints

### Provisioning

#### Generate Token (Web)
```http
POST /provisioning/generate
Content-Type: application/json
Authorization: Bearer {token}

{
  "name": "Home Garden Token",
  "max_uses": 5,
  "expires_at": "2025-12-31"
}
```

#### Claim Device
```http
POST /api/provision/claim
Content-Type: application/json

{
  "token": "xxxxx",
  "device_id": "ESP_123456",
  "name": "Garden Plant 1",
  "location": "Backyard"
}

Response:
{
  "device_id": "ESP_123456",
  "api_key": "xxxxxxxxxxxxxx",
  "message": "Device claimed successfully"
}
```

### Data Ingestion

#### Send Sensor Data
```http
POST /api/ingest
Content-Type: application/json
X-Device-Id: ESP_123456
X-Api-Key: xxxxxxxxxxxxxx

{
  "readings": [
    {"type": "soil", "value": 45.5},
    {"type": "temp", "value": 28.3},
    {"type": "hum", "value": 65.2},
    {"type": "color_r", "value": 120},
    {"type": "color_g", "value": 180},
    {"type": "color_b", "value": 90}
  ]
}
```

### Commands

#### Get Next Command
```http
GET /api/commands/next
X-Device-Id: ESP_123456
X-Api-Key: xxxxxxxxxxxxxx

Response:
{
  "id": 1,
  "command": "water_on",
  "params": {"duration": 30}
}
```

#### Acknowledge Command
```http
POST /api/commands/1/ack
X-Device-Id: ESP_123456
X-Api-Key: xxxxxxxxxxxxxx

{
  "status": "completed"
}
```

## 🔧 Hardware Setup

### Pin Configuration (ESP8266)

```cpp
// Sensor Kelembaban Tanah
#define SOIL_MOISTURE_PIN A0

// DHT22 (Temperature & Humidity)
#define DHT_PIN D4
#define DHT_TYPE DHT22

// TCS3200 Color Sensor
#define S0 D0
#define S1 D1
#define S2 D2
#define S3 D3
#define OUT D5

// Relay untuk Pompa Air
#define RELAY_PIN D6
```

### Wiring Diagram

```
ESP8266          Sensor/Actuator
-------          ---------------
A0      ────────  Soil Moisture (Signal)
D4      ────────  DHT22 (Data)
D6      ────────  Relay (IN)
3.3V    ────────  Sensors (VCC)
GND     ────────  Sensors (GND)

Relay            Pompa Air
-----            ---------
COM     ────────  Power Supply (+)
NO      ────────  Pompa (+)
```

## 🐛 Troubleshooting

### ESP8266 tidak bisa connect WiFi
- Pastikan SSID dan password benar
- Periksa sinyal WiFi cukup kuat
- Restart ESP8266

### Provisioning gagal
- Pastikan token valid dan belum expired
- Cek server Laravel sudah running
- Pastikan ESP8266 bisa ping ke server
- Periksa Serial Monitor untuk error detail

### Data tidak masuk ke dashboard
- Cek API key dan device ID valid
- Pastikan endpoint `/api/ingest` bisa diakses
- Periksa format JSON payload
- Cek log Laravel: `tail -f storage/logs/laravel.log`

### Command tidak diterima device
- Pastikan device polling endpoint `/api/commands/next`
- Cek middleware `device.key` tidak memblokir request
- Periksa queue command di database

### Reset Device Credentials

Uncomment baris berikut di `setup()` dan upload ulang:

```cpp
clearCredentials();
Serial.println("🗑️  EEPROM cleared!");
while(1) delay(1000);
```

Setelah EEPROM terhapus, comment kembali dan upload ulang untuk provisioning baru.

## 🧪 Testing

```bash
# Run all tests
php artisan test

# Run specific test
php artisan test --filter=ProvisioningTest
```

## 📝 Development

### File Structure

```
SmartPlants-IOT/
├── app/                            # Laravel application code
│   ├── Console/                    # CLI commands & scheduler
│   ├── Http/
│   │   ├── Controllers/            # Request handlers
│   │   │   ├── Api/                # API endpoints for ESP8266
│   │   │   ├── DashboardController.php
│   │   │   ├── AutomationController.php
│   │   │   └── ProvisioningAdminController.php
│   │   ├── Middleware/             # HTTP filters
│   │   └── Requests/               # Form validation
│   ├── Models/                     # Eloquent models
│   ├── Providers/                  # Service providers
│   ├── Services/                   # Business logic layer
│   ├── Traits/                     # Reusable code snippets
│   └── View/                       # Blade components
├── database/
│   ├── migrations/                 # Database schema
│   └── seeders/                    # Sample data
├── resources/
│   ├── views/                      # Blade templates
│   ├── css/                        # Tailwind CSS
│   └── js/                         # Frontend JavaScript
├── routes/
│   ├── web.php                     # Web routes
│   └── api.php                     # API routes for ESP8266
├── docs/                           # 📚 Documentation
│   ├── DROPDOWN-FIX.md
│   └── ESP8266_README.md
├── esp8266/                        # 🔌 Arduino firmware
│   ├── ESP8266_SmartPlants.ino     # Main ESP8266 code
│   ├── esp8266_full_automation.ino # Full automation version
│   └── README.md
├── scripts/                        # 🛠️ Helper scripts
│   ├── dev.bat                     # Development server
│   ├── fix-dropdown.bat            # Fix dropdown bug
│   └── README.md
├── tests/
│   ├── Feature/                    # Feature tests
│   ├── Unit/                       # Unit tests
│   └── manual/                     # 🧪 Manual test scripts
│       ├── test_ingest.php
│       ├── test_provision.php
│       ├── check_devices.php
│       └── README.md
├── .env.example                    # Environment template
├── composer.json                   # PHP dependencies
├── package.json                    # Node dependencies
├── nixpacks.toml                   # Deployment config (Railway)
├── Caddyfile                       # Caddy webserver config
└── README.md                       # This file
```

## 🤝 Kontribusi

Kontribusi sangat diterima! Silakan:

1. Fork repository ini
2. Buat branch fitur (`git checkout -b feature/AmazingFeature`)
3. Commit perubahan (`git commit -m 'Add some AmazingFeature'`)
4. Push ke branch (`git push origin feature/AmazingFeature`)
5. Buka Pull Request

## 📄 Lisensi

Proyek ini menggunakan lisensi MIT. Lihat file `LICENSE` untuk detail.

## 👨‍💻 Author

**kurokana**

- GitHub: [@kurokana](https://github.com/kurokana)

## 🙏 Acknowledgments

- Laravel Framework
- ESP8266 Community
- Tailwind CSS
- ArduinoJson Library

## 📞 Support

Jika Anda mengalami masalah atau memiliki pertanyaan:

1. Buka [Issues](https://github.com/kurokana/SmartPlants-IOT/issues)
2. Cek dokumentasi ESP8266: [docs/ESP8266_README.md](docs/ESP8266_README.md)
3. Periksa troubleshooting guide di atas

---

**Happy Planting! 🌱**
