# SmartPlants IoT

Automasi & monitoring tanaman dalam satu sistem microcontroller + cloud — bebas repot, tanaman tetap happy 🌱

## 🎯 Apa yang dilakukan

* Sensor membaca kondisi seperti kelembapan tanah, suhu, cahaya.
* Microcontroller (contoh: ESP32) kirim data ke backend.
* Dashboard atau notifikasi memberi tahu kapan menyiram atau merawat tanaman.
* Ideal untuk rumah, apartemen, atau kebun kecil.

## 🧩 Komponen

* Hardware: ESP32 + sensor tanah + sensor cahaya + sensor suhu (dan lainnya jika tersedia)
* Firmware: kode microcontroller untuk baca & kirim data
* Backend: server atau cloud untuk terima data, simpan, tampilkan dashboard
* Frontend/Dashboard: antarmuka web atau aplikasi ringan untuk lihat data & kontrol

## 🚀 Cara mulai (versi cepat)

1. Clone repo:

   ```bash
   git clone https://github.com/iceguya/SmartPlants-IoT.git
   cd SmartPlants-IoT
   ```
2. Setup hardware: sambungkan ESP32 + sensor sesuai skema di `/hardware` atau folder yang relevan.
3. Konfigurasi firmware: buka file `config.h` atau `settings.json`, masukkan SSID WiFi, password, endpoint backend.
4. Upload firmware ke ESP32.
5. Jalankan backend atau sambungkan ke layanan cloud pilihanmu.
6. Buka dashboard (misal di `http://localhost:8000` atau sesuai konfigurasi) dan mulai melihat data!

## ✅ Kenapa bagus

* Otomatisasi: tanaman dirawat tanpa banyak intervensi manusia.
* Monitoring realtime: kamu bisa tahu kondisi tanaman dari jarak jauh.
* Modular & terbuka: bisa tambah sensor atau fungsi sendiri (siraman otomatis, kamera, dll).

## ⚠️ Catatan / batasan

* Firmware & backend belum sepenuhnya “plug-and-play” — mungkin butuh konfigurasi manual WiFi/endpoint.
* Jika memakai banyak sensor atau modul tambahan, perhatikan catu daya (power) dan pengkabelan.
* Keamanan: jika expose dashboard ke internet, pastikan autentikasi & enkripsi diterapkan.

## 🛠️ Pengembangan selanjutnya

* Tambah fitur siraman otomatis via relay atau pompa.
* Tambah logging historik dan grafik tren kondisi tanaman.
* Tambah notifikasi (email/Telegram) saat kondisi kritis (kelembapan rendah, suhu ekstrem).
* Integrasi dengan Smart Home (contoh: Home Assistant).

## 📝 Lisensi

MIT — gunakan, modifikasi, dan bagikan bebas.

---

