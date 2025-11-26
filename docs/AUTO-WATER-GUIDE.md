# 🚰 Auto Water System - User Guide

## Overview

Sistem auto water sekarang memiliki **2 mode operasi**:

### **1. Manual Water** (dari Dashboard)
- Klik tombol **"Aktifkan"** di dashboard
- Pompa menyala sesuai durasi yang ditentukan (default 5 detik)
- Bekerja **kapan saja**, tidak peduli kondisi tanah

### **2. Auto Water** (otomatis berdasarkan kondisi)
- **Default: DISABLED** (tidak aktif)
- Hanya menyala jika **soil moisture < 35%**
- Pompa menyala selama **10 detik** otomatis
- Diaktifkan via command dari dashboard

---

## 🔧 Konfigurasi ESP8266

### **Parameter Auto Water** (di code):

```cpp
const float SOIL_THRESHOLD = 35.0;  // Auto water jika soil < 35%
const int AUTO_WATER_DURATION = 10; // Auto water selama 10 detik
bool autoWaterEnabled = false;      // Auto water dimatikan by default
```

### **Ubah Threshold:**

Jika ingin pompa menyala pada kelembapan yang berbeda:
```cpp
const float SOIL_THRESHOLD = 40.0;  // Ubah jadi 40%
```

### **Ubah Durasi:**

```cpp
const int AUTO_WATER_DURATION = 15; // Ubah jadi 15 detik
```

---

## 📱 Cara Menggunakan dari Dashboard

### **Manual Water (Satu Kali)**

1. Login ke dashboard `https://kurokana.alwaysdata.net`
2. Klik device Anda
3. Scroll ke bagian **"Auto Water"**
4. Masukkan durasi (1-60 detik)
5. Klik **"Aktifkan"**
6. Monitor Serial ESP8266 untuk konfirmasi

**Expected Serial Output:**
```
🔍 Checking for commands...
📡 Command check response: 200
📥 Response: {"id":123,"command":"water_on","params":{"duration_sec":5}}
📥 Command received: water_on (ID: 123)
💧 MANUAL: Executing water_on for 5 seconds...
💧 Water ON for 5 seconds
✅ Water OFF
✅ Command ACK sent
```

### **Enable Auto Water Mode**

**Via Tinker (sementara, belum ada UI):**
```bash
php artisan tinker
```

```php
// Get your device
$device = \App\Models\Device::find('YOUR_DEVICE_ID');

// Create auto water enable command
\App\Models\Command::create([
    'device_id' => $device->id,
    'command' => 'auto_water_enable',
    'params' => [],
    'status' => 'pending'
]);
```

**Expected Serial Output:**
```
📥 Command received: auto_water_enable (ID: 124)
✅ AUTO WATER ENABLED
```

### **Disable Auto Water Mode**

```php
\App\Models\Command::create([
    'device_id' => $device->id,
    'command' => 'auto_water_disable',
    'params' => [],
    'status' => 'pending'
]);
```

**Expected Serial Output:**
```
📥 Command received: auto_water_disable (ID: 125)
⏸️ AUTO WATER DISABLED
```

---

## 🔍 Monitor Auto Water

### **Serial Output saat Auto Water DISABLED:**

```
📊 Sensor Data:
Soil: 42.35% (auto water disabled)
Temp: 28.50°C
Hum : 72.10%
RGB : (120, 200, 80)
```

### **Serial Output saat Auto Water ENABLED (soil > threshold):**

```
📊 Sensor Data:
Soil: 45.23% ✅ OK (auto water enabled, threshold: 35.0%)
Temp: 28.50°C
Hum : 72.10%
RGB : (120, 200, 80)
```

### **Serial Output saat Auto Water TRIGGERED:**

```
📊 Sensor Data:
Soil: 28.50% ⚠️ LOW! (threshold: 35.0%)
🤖 AUTO WATER TRIGGERED!
💧 Water ON for 10 seconds
✅ Water OFF
Temp: 28.50°C
Hum : 72.10%
RGB : (120, 200, 80)
```

---

## 🐛 Troubleshooting

### **Device Tidak Muncul di "Connected Devices"**

**Penyebab:** Device tidak update `last_seen`

**Solusi sudah ditambahkan:**
- ESP8266 kirim status update setiap **60 detik**
- Function `updateDeviceStatus()` dipanggil otomatis
- Initial status update saat setup

**Verify:**
```bash
# Check device last_seen
php artisan tinker
```
```php
$device = \App\Models\Device::first();
echo "Last seen: " . $device->last_seen . "\n";
echo "Status: " . $device->status . "\n";
```

**Expected:**
```
Last seen: 2025-11-26 14:30:15
Status: online
```

### **Auto Water Tidak Menyala**

**Checklist:**
1. ✅ Auto water mode **enabled**?
   - Check serial: `⚙️ Auto water: ENABLED`
   
2. ✅ Soil moisture **di bawah threshold** (35%)?
   - Check serial: `Soil: 28.50% ⚠️ LOW!`
   
3. ✅ Relay **bekerja**?
   - Dengar bunyi "klik" saat pompa menyala
   
4. ✅ Wiring **benar**?
   - COM → Power (-)
   - NO → Pompa (-)

### **Manual Water Tidak Bekerja**

**Follow guide:** `docs/TROUBLESHOOTING-WATER-COMMAND.md`

**Quick check:**
1. Command created di database?
2. ESP8266 polling `/api/commands/next`?
3. Relay pin correct (D4)?
4. Relay active level (LOW = ON)?

---

## 📊 Database Commands

### **Supported Commands:**

| Command | Params | Description |
|---------|--------|-------------|
| `water_on` | `duration_sec` (1-60) | Manual water dari dashboard |
| `auto_water_enable` | - | Aktifkan mode auto water |
| `auto_water_disable` | - | Matikan mode auto water |

### **Check Pending Commands:**

```bash
php artisan tinker
```
```php
// All pending commands
\App\Models\Command::where('status', 'pending')->get();

// Latest 10 commands
\App\Models\Command::latest()->take(10)->get();

// Commands for specific device
\App\Models\Command::where('device_id', 'YOUR_DEVICE_ID')
    ->orderBy('id', 'desc')
    ->take(5)
    ->get();
```

---

## ⚙️ Advanced Configuration

### **Custom Threshold per Device**

Untuk menyimpan threshold di database (future feature):

```php
// Add to devices table migration
$table->float('auto_water_threshold')->default(35.0);
$table->integer('auto_water_duration')->default(10);
```

### **Multiple Threshold Levels**

```cpp
// In ESP8266 code
if (soil < 20.0) {
    // Critical: water 30 seconds
    executeWaterOn(30);
} else if (soil < 35.0) {
    // Low: water 10 seconds
    executeWaterOn(10);
}
```

### **Time-based Auto Water**

```cpp
// Only auto water during daytime (6 AM - 6 PM)
int currentHour = hour();  // Requires TimeLib
if (autoWaterEnabled && soil < SOIL_THRESHOLD && 
    currentHour >= 6 && currentHour < 18) {
    executeWaterOn(AUTO_WATER_DURATION);
}
```

---

## 🎯 Best Practices

1. **Test Manual Water First**
   - Verify relay dan pompa bekerja
   - Adjust durasi sesuai kebutuhan

2. **Monitor Soil Threshold**
   - Observe natural soil moisture pattern
   - Adjust threshold sesuai jenis tanaman

3. **Start with Auto Water Disabled**
   - Observe sensor readings dulu
   - Enable auto water setelah confident dengan threshold

4. **Regular Maintenance**
   - Check pompa dan relay setiap minggu
   - Clean sensor soil moisture
   - Verify wiring connections

5. **Safety Limits**
   - Max duration: 60 seconds (hardware protection)
   - Min duration: 1 second
   - Threshold validated: 0-100%

---

## 📝 Summary

✅ **Manual water** works via dashboard "Aktifkan" button
✅ **Auto water** only triggers when soil < 35% AND auto mode enabled
✅ **Device status** updated every 60s to appear online
✅ **Multiple commands** supported (water_on, auto_water_enable, auto_water_disable)
✅ **Safety limits** built-in (max 60s duration)
✅ **Debugging logs** in Serial Monitor

**Default Behavior:**
- Auto water: **DISABLED**
- Threshold: **35%**
- Auto duration: **10 seconds**
- Manual duration: **User configurable (1-60s)**
- Status update: **Every 60 seconds**
- Command check: **Every 10 seconds**
- Sensor read: **Every 30 seconds**
