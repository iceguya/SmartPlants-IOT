# Device Ownership Security Update

## 🔒 Fitur Baru - Strict Device Ownership

Update ini menambahkan **perlindungan penuh terhadap device ownership** untuk mencegah konflik dan override user yang berbeda.

---

## ✨ Yang Berubah

### 1. **Provisioning Logic Ketat**
- ✅ Device **TIDAK BISA** di-claim oleh user berbeda
- ✅ Re-provisioning oleh user yang sama: **generate API key baru**
- ✅ Auto-claim untuk orphaned devices (device tanpa owner)
- ✅ Comprehensive logging untuk semua provisioning attempts

### 2. **Middleware Validation**
- ✅ Setiap API request divalidasi ownership-nya
- ✅ Invalid API key langsung ditolak
- ✅ Orphaned devices tidak bisa kirim data

### 3. **Device Model Methods**
```php
$device->isOwnedBy($userId)        // Check ownership
$device->canBeClaimedBy($userId)   // Check if can claim
$device->isOrphaned()              // Check if no owner
$device->reassignTo($userId)       // Admin transfer (with logging)
$device->releaseOwnership()        // Make device orphaned
```

### 4. **Database Optimization**
- ✅ Index pada `user_id` untuk query cepat
- ✅ Composite index `(user_id, status)` untuk dashboard
- ✅ Index pada `last_seen` untuk online/offline check

---

## 📋 3 Skenario Provisioning

### Skenario 1: New Device
```
Token: user_id=1
Device: tidak ada

Result: ✅ Device dibuat, assigned ke user_id=1
```

### Skenario 2: Re-provision (Same User)
```
Token: user_id=1
Device: sudah ada, user_id=1

Result: ✅ Generate API key baru, update device info
```

### Skenario 3: Cross-user Claim (BLOCKED)
```
Token: user_id=2
Device: sudah ada, user_id=1

Result: ❌ HTTP 409 - "Device already registered to another user"
Error: DEVICE_OWNERSHIP_CONFLICT
Hint: Clear EEPROM dan provision dengan token baru
```

---

## 🚀 Deployment Steps

### Step 1: Run Migration
```bash
php artisan migrate
```

Ini akan menambahkan database indexes untuk performa lebih baik.

### Step 2: Verify System
```bash
php scripts/deploy-ownership-update.php
```

Script ini akan:
- ✅ Fix orphaned devices
- ✅ Detect ownership conflicts
- ✅ Show devices per user
- ✅ Verify system integrity

### Step 3: Test Provisioning

**Test 1: Normal Provisioning**
```bash
# Create token for user 1
php artisan tinker
>>> $token = App\Models\ProvisioningToken::create([
...   'token' => Str::random(40),
...   'user_id' => 1,
...   'expires_at' => now()->addDays(7)
... ]);
```

Upload firmware dengan token tersebut, verify device muncul di dashboard user 1.

**Test 2: Cross-user Prevention**
```bash
# Create token for user 2
>>> $token2 = App\Models\ProvisioningToken::create([
...   'token' => Str::random(40),
...   'user_id' => 2,
...   'expires_at' => now()->addDays(7)
... ]);
```

Gunakan token ini di ESP8266 yang SAMA, seharusnya dapat **HTTP 409 error**.

---

## 🔍 Monitoring & Logs

Semua provisioning attempts dicatat di logs dengan detail:

**Successful Provisioning:**
```
[INFO] New device provisioned
- device_id: 62563
- user_id: 1
- user_email: test@example.com
- token: zsDX4SgsW80...
```

**Blocked Cross-user Claim:**
```
[ERROR] Provisioning blocked: Device ownership conflict
- device_id: 62563
- current_owner_id: 1
- attempted_owner_id: 2
- token: SDtm7FStN2...
```

**Re-provision by Same User:**
```
[INFO] Device re-provisioned by same user
- device_id: 62563
- user_id: 1
- old_api_key: 3kJ9mP2q...
- new_api_key: 8hF5nL1w...
```

---

## 🛡️ Security Features

### API Request Validation
Setiap request ke `/api/ingest`, `/api/commands/*` divalidasi:

1. **Header Check**: `X-Device-Id` dan `X-Api-Key` harus ada
2. **Device Lookup**: Device harus terdaftar
3. **API Key Match**: API key harus cocok
4. **Ownership Check**: Device harus punya owner (tidak orphaned)

### Error Responses

**Missing Credentials (401):**
```json
{
  "message": "Missing device credentials",
  "error": "MISSING_CREDENTIALS"
}
```

**Invalid API Key (403):**
```json
{
  "message": "Invalid API key",
  "error": "INVALID_API_KEY",
  "hint": "API key may have changed. Please re-provision device."
}
```

**Orphaned Device (403):**
```json
{
  "message": "Device has no owner",
  "error": "ORPHANED_DEVICE",
  "hint": "Device must be claimed by a user before use."
}
```

---

## 🔧 Troubleshooting

### Device Tidak Muncul di Dashboard

**Diagnosis:**
```bash
php scripts/check-devices.php
```

**Kemungkinan penyebab:**
1. Device punya `user_id` berbeda → Transfer ownership atau clear EEPROM
2. Device orphaned (`user_id=null`) → Re-provision dengan token valid
3. Device offline → Check `last_seen` timestamp

### API Error -5 dari ESP8266

**Diagnosis:** Check logs di server

**Kemungkinan penyebab:**
1. API key berubah setelah re-provision → Clear EEPROM, provision ulang
2. Device di-claim user lain → Dapat HTTP 409, clear EEPROM
3. Middleware blocking orphaned device → Provision dengan token valid

### Transfer Device ke User Lain

**Option 1: Via Tinker (Admin)**
```php
php artisan tinker
>>> $device = App\Models\Device::find('62563');
>>> $device->reassignTo(2, 'User request transfer');
```

**Option 2: Clear EEPROM & Re-provision (User)**
1. Upload `ESP8266_Clear_EEPROM.ino`
2. Create token untuk user baru
3. Upload firmware dengan token baru

---

## 📊 Database Schema

### Devices Table
```
id (PK, string)
name
location
api_key
status
last_seen
user_id (FK → users.id)  ← OWNERSHIP KEY
created_at
updated_at

Indexes:
- user_id
- (user_id, status) composite
- last_seen
```

### Provisioning Tokens Table
```
id (PK)
token (unique)
user_id (FK → users.id)  ← TOKEN OWNER
planned_device_id
claimed (boolean)
claimed_device_id (FK → devices.id)
claimed_at
expires_at
created_at
updated_at

Indexes:
- user_id
- claimed_device_id
- (token, claimed, expires_at) composite
```

---

## ✅ Testing Checklist

- [ ] Migration berhasil tanpa error
- [ ] Script `deploy-ownership-update.php` tidak ada konflik
- [ ] Provision device baru → muncul di dashboard user yang benar
- [ ] Re-provision device sama user → API key berubah, device tetap muncul
- [ ] Provision device lain user → HTTP 409, device tidak override
- [ ] Send data dari device → middleware validate ownership
- [ ] Command polling → hanya commands untuk device tersebut
- [ ] Dashboard menampilkan devices sesuai `user_id`
- [ ] Logs mencatat semua provisioning attempts

---

## 🎯 Benefits

✅ **No More Ownership Conflicts** - Device tidak bisa dicuri/override user lain  
✅ **Automatic Validation** - Middleware check setiap API request  
✅ **Clear Error Messages** - User tahu exactly apa yang salah  
✅ **Comprehensive Logging** - Audit trail untuk semua ownership changes  
✅ **Better Performance** - Database indexes speed up queries  
✅ **Secure Re-provisioning** - New API key setiap re-provision  

---

## 📝 Notes

- **ESP.getChipId() adalah constant** - Setiap ESP8266 punya ID unik yang tidak berubah
- **API key berubah saat re-provision** - Old firmware dengan old API key akan ditolak
- **Clear EEPROM wajib** - Sebelum transfer device ke user lain
- **Orphaned devices** - Hanya bisa digunakan setelah di-claim via provisioning token

---

**Version:** 2.0  
**Date:** November 26, 2025  
**Author:** SmartPlants Team
