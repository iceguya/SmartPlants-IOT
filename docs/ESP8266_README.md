# 🌱 SmartPlants ESP8266 - Setup Guide

## 📋 Penjelasan Masalah & Solusi

### ❌ Masalah yang terjadi:
1. **404 Not Found** → Rute API tidak terdaftar karena `bootstrap/app.php` tidak memuat `routes/api.php`
   - ✅ **Fixed**: Menambahkan `api: __DIR__.'/../routes/api.php'` ke bootstrap
   
2. **401 Invalid device credentials** → ESP8266 belum melakukan provisioning
   - ✅ **Solusi**: Device harus claim token provisioning terlebih dahulu untuk mendapat `device_id` dan `api_key`

---

## 🚀 Cara Setup ESP8266

### 1️⃣ Persiapan Hardware
- ESP8266 (NodeMCU, Wemos D1, dll)
- Sensor (opsional untuk testing, bisa pakai dummy data dulu)
- Kabel USB untuk upload

### 2️⃣ Install Library Arduino
Buka Arduino IDE → Sketch → Include Library → Manage Libraries, lalu install:
- **ArduinoJson** (by Benoit Blanchon) - versi 6.x

### 3️⃣ Generate Provisioning Token dari Web

1. Jalankan server Laravel:
   ```bash
   php artisan serve
   ```

2. Buka browser: `http://localhost:8000`

3. Login (atau register jika belum punya akun)

4. Buka menu **Provisioning**: `http://localhost:8000/provisioning`

5. Klik **Generate** untuk membuat token baru

6. **Salin token** yang muncul di tabel (contoh: `DEMO-TOKEN-12345`)

### 4️⃣ Edit Sketch ESP8266

Buka file `ESP8266_SmartPlants.ino` dan edit bagian berikut:

```cpp
// ===== KONFIGURASI WiFi =====
const char* ssid = "NamaWiFiAnda";           // ← GANTI
const char* password = "PasswordWiFiAnda";   // ← GANTI

// ===== KONFIGURASI SERVER =====
const char* serverUrl = "http://192.168.1.100:8000"; // ← GANTI dengan IP komputer server
// Cek IP komputer: ipconfig (Windows) atau ifconfig (Linux/Mac)

// ===== PROVISIONING TOKEN =====
const char* provisionToken = "TOKEN-DARI-WEB"; // ← PASTE token dari step 3
```

### 5️⃣ Upload ke ESP8266

1. Pilih board: **Tools → Board → ESP8266 → NodeMCU 1.0** (atau sesuai board Anda)
2. Pilih port: **Tools → Port → COMx** (sesuai port ESP8266)
3. Upload: **Sketch → Upload** (atau Ctrl+U)

### 6️⃣ Monitor Serial Output

1. Buka Serial Monitor: **Tools → Serial Monitor**
2. Set baud rate: **115200**
3. Anda akan melihat output seperti:

```
=== SmartPlants ESP8266 ===
Connecting to WiFi........
✅ WiFi connected!
IP: 192.168.1.105

⚠️  No credentials found. Starting provisioning...
🔧 Starting provisioning...
Sending: {"token":"DEMO-TOKEN-12345","device_id":"12345678","name":"ESP8266 SmartPlant","location":"Home"}
Response: {"message":"Provisioned","device_id":"12345678","api_key":"xyz123..."}
✅ Credentials saved to EEPROM
✅ Provisioning SUCCESS!

✅ Device ready to send data!

📊 Sensor readings:
  Soil: 45.23%
  Temp: 25.67°C
  Hum: 62.18%
  RGB: (123, 145, 67)

📤 Sending data: {"readings":[...]}
✅ Data sent successfully!
Response: {"message":"OK"}
```

---

## 🔧 Troubleshooting

### ❌ WiFi connection failed
- Periksa SSID dan password WiFi
- Pastikan ESP8266 dalam jangkauan WiFi
- Restart ESP8266

### ❌ Provisioning failed (HTTP 404)
- Pastikan server Laravel berjalan (`php artisan serve`)
- Periksa URL server di kode (gunakan IP, bukan `localhost`)
- Test manual: buka browser di HP/laptop yang terhubung WiFi sama → `http://192.168.x.x:8000`

### ❌ Provisioning failed (HTTP 401/404/410)
- Token expired → buat token baru dari web
- Token sudah dipakai → buat token baru (1 token = 1 device)
- Token salah → copy ulang dengan benar

### ❌ HTTP failed (code 401) "Invalid device credentials"
- Device belum provisioning → hapus EEPROM dan restart untuk provisioning ulang
- Untuk reset: tambahkan `clearCredentials();` di `setup()` lalu upload ulang

---

## 📊 Cek Data di Dashboard Web

1. Setelah ESP8266 berhasil kirim data, buka: `http://localhost:8000/dashboard`
2. Anda akan melihat device Anda dengan status **online**
3. Klik device untuk melihat grafik sensor realtime

---

## 🔄 Flow Kerja System

```
[ESP8266 Boot]
    ↓
[Cek EEPROM: ada credentials?]
    ├─ TIDAK → [Provisioning: claim token]
    │            ↓
    │         [Simpan device_id & api_key ke EEPROM]
    │            ↓
    └─ YA ────→ [Baca sensor]
                  ↓
               [Kirim data ke /api/ingest dengan header auth]
                  ↓
               [Server validasi credentials]
                  ↓
               [Simpan ke database]
                  ↓
               [Dashboard update realtime]
                  ↓
               [Tunggu 30 detik]
                  ↓
               [Ulangi]
```

---

## 📝 Catatan Penting

1. **Provisioning hanya dilakukan 1x** → credentials disimpan di EEPROM
2. **1 token = 1 device** → untuk device kedua, buat token baru
3. **Data dikirim tiap 30 detik** → bisa diubah di `delay(30000)`
4. **Untuk produksi**:
   - Ganti dummy data dengan sensor real
   - Gunakan HTTPS untuk keamanan
   - Tambahkan deep sleep untuk hemat baterai

---

## 🎯 Next Steps

- [ ] Ganti dummy data dengan sensor real (DHT22, soil moisture, TCS3200)
- [ ] Tambahkan fitur receive command dari server (`/api/commands/next`)
- [ ] Implement deep sleep untuk hemat power
- [ ] Setup server di cloud (Heroku, DigitalOcean, dll) untuk akses dari mana saja

---

## 📞 Support

Jika ada masalah, cek Serial Monitor dulu untuk error message. 
Biasanya pesan error sudah jelas menunjukkan masalahnya.

**Happy Coding! 🌱**
