# 🔌 ESP8266 Firmware

Folder ini berisi firmware Arduino untuk ESP8266 NodeMCU yang digunakan dalam sistem SmartPlants IOT.

## File Firmware

### ESP8266_SmartPlants.ino
**File utama** - Firmware lengkap dengan fitur:
- ✅ Pembacaan sensor (DHT22, Soil Moisture, TCS3200)
- ✅ Kontrol relay (water pump)
- ✅ Koneksi WiFi dengan auto-reconnect
- ✅ HTTP client untuk kirim data ke Laravel API
- ✅ Command polling dari server
- ✅ Status LED indikator

### esp8266_full_automation.ino
Versi dengan automasi lokal tambahan (opsional).

### fix.ino
Kode untuk testing/debugging sensor individual.

## Cara Upload

1. Install Arduino IDE
2. Install board ESP8266: File → Preferences → Additional Boards Manager URLs:
   ```
   http://arduino.esp8266.com/stable/package_esp8266com_index.json
   ```
3. Install library yang diperlukan:
   - DHT sensor library (Adafruit)
   - ArduinoJson
4. Buka `ESP8266_SmartPlants.ino`
5. Edit konfigurasi WiFi dan API endpoint
6. Tools → Board → ESP8266 NodeMCU
7. Tools → Port → (pilih COM port ESP8266)
8. Upload ⬆️

## Wiring

Lihat dokumentasi lengkap di `docs/ESP8266_README.md`

## Troubleshooting

**Error upload:**
- Pastikan driver CH340/CP2102 terinstal
- Tutup Serial Monitor sebelum upload
- Tekan tombol FLASH saat upload jika diperlukan

**Device tidak connect:**
- Cek WiFi credentials
- Cek API endpoint URL
- Monitor Serial untuk debug log (115200 baud)
