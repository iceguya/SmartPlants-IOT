# 🧪 Manual Testing Scripts

Folder ini berisi PHP script untuk testing manual API endpoints dan fungsi sistem.

## Test Scripts

### test_ingest.php
**Test API sensor data ingestion**

Simulasi ESP8266 mengirim sensor readings ke Laravel API.

**Test:**
- POST `/api/ingest` dengan sensor data
- Validasi authentication (X-Device-Id, X-Api-Key)
- Bulk insert sensor readings
- Device status update

**Cara pakai:**
```bash
php tests/manual/test_ingest.php
```

### test_provision.php
**Test provisioning API**

Test flow provisioning device baru:
- Generate provisioning token
- Claim token dengan device credentials
- Validasi token expiration

**Cara pakai:**
```bash
php tests/manual/test_provision.php
```

### check_devices.php
**Check device status**

Script untuk cek status semua devices di database (online/offline).

**Output:** List devices dengan last_seen dan status

**Cara pakai:**
```bash
php tests/manual/check_devices.php
```

### check_devices_detail.php
**Check device details**

Versi detail dengan informasi sensor, readings, dan commands.

**Cara pakai:**
```bash
php tests/manual/check_devices_detail.php
```

## Catatan

Script ini untuk testing development. Untuk production testing, gunakan:
- PHPUnit tests di `tests/Feature/`
- Postman collection (jika ada)
- API documentation tools

## Environment

Pastikan `.env` sudah dikonfigurasi sebelum run test scripts:
```bash
cp .env.example .env
php artisan key:generate
```
