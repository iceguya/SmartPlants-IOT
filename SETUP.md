# SmartPlants IoT - Quick Setup Guide

## 🚀 Quick Start (3 Steps)

```bash
# 1. Clone repository
git clone https://github.com/kurokana/SmartPlants-IOT.git
cd SmartPlants-IOT

# 2. Install dependencies
composer install
npm install

# 3. Setup environment & migrate
cp .env.example .env
php artisan key:generate
php artisan migrate

# That's it! 🎉
```

---

## 📋 What Happens Automatically

When you run `php artisan migrate`, the system will:

✅ Create all database tables  
✅ Add indexes for performance  
✅ Migrate old device IDs to user-scoped format (if any)  
✅ Setup ownership constraints  

**No manual scripts needed!** Everything runs automatically.

---

## 🔧 Configuration

### 1. Database Setup

Edit `.env`:
```env
DB_CONNECTION=pgsql
DB_HOST=127.0.0.1
DB_PORT=5432
DB_DATABASE=your_database
DB_USERNAME=your_username
DB_PASSWORD=your_password
```

### 2. Application URL

```env
APP_URL=https://your-domain.com
```

### 3. Timezone (Optional)

```env
APP_TIMEZONE=Asia/Jakarta
```

---

## 🔌 ESP8266 Setup

### 1. Update Firmware Configuration

Edit `esp8266/esp8266_full_automation.ino`:

```cpp
const char* ssid = "Your_WiFi_SSID";
const char* password = "Your_WiFi_Password";
const char* serverUrl = "https://your-domain.com";  // No trailing slash
```

### 2. Generate Provisioning Token

```bash
php artisan tinker
```

```php
>>> $token = App\Models\ProvisioningToken::create([
...   'token' => Str::random(40),
...   'user_id' => 1,  // Your user ID
...   'name_hint' => 'ESP8266 SmartPlant',
...   'location_hint' => 'Home',
...   'expires_at' => now()->addDays(30)
... ]);
>>> echo $token->token;
```

Copy the token and update firmware:

```cpp
const char* provisionToken = "YOUR_TOKEN_HERE";
```

### 3. Upload Firmware

Upload to ESP8266 via Arduino IDE.

**First boot:**
- Device will connect to WiFi
- Auto-provision with server
- Receive unique device ID: `user_{user_id}_chip_{chip_id}`
- Start sending sensor data

---

## 📊 System Architecture

### Device ID Format

**Old System (Before):**
```
Device ID: "62563"  ← Same across all users (conflict!)
```

**New System (Current):**
```
User 1: "user_1_chip_62563"  ← Unique per user
User 2: "user_2_chip_62563"  ← Same ESP8266, different user
```

**Benefits:**
- ✅ No device ID conflicts between users
- ✅ Same ESP8266 can be used by multiple users
- ✅ Automatic namespace isolation
- ✅ Zero configuration needed

### Provisioning Flow

```
1. ESP8266 → Server: "token + chip_id: 62563"
2. Server: "user_id from token = 1"
3. Server: "Generate unique ID: user_1_chip_62563"
4. Server → ESP8266: "device_id + api_key"
5. ESP8266: "Save to EEPROM"
6. ESP8266: "Use for all future requests"
```

---

## 🛠️ Development Commands

### Start Development Server

```bash
php artisan serve
npm run dev
```

### Clear Cache

```bash
php artisan cache:clear
php artisan config:clear
php artisan route:clear
php artisan view:clear
```

### Run Tests

```bash
php artisan test
```

---

## 📦 Production Deployment

### Method 1: Manual Deployment

```bash
# On production server
git pull origin main
composer install --no-dev --optimize-autoloader
php artisan migrate --force
php artisan config:cache
php artisan route:cache
php artisan view:cache
```

### Method 2: Automatic Deployment (GitHub Actions)

Already configured in `.github/workflows/deploy.yml`

**On every push to `main`:**
1. ✅ Code pushed to GitHub
2. ✅ Auto-deployed to production
3. ✅ Migrations run automatically
4. ✅ Cache cleared

---

## 🔍 Verify Installation

### Check Database Tables

```bash
php artisan tinker
```

```php
>>> DB::table('devices')->count();
>>> DB::table('users')->count();
>>> DB::table('sensors')->count();
```

### Check Device Format

```php
>>> App\Models\Device::all(['id', 'user_id', 'name']);
```

Expected output:
```php
[
  {
    "id": "user_1_chip_62563",
    "user_id": 1,
    "name": "ESP8266 SmartPlant"
  }
]
```

### Check API Endpoints

```bash
curl https://your-domain.com/api/provision/claim
```

Expected: `{"message":"Invalid provisioning token"}` (normal, just checking endpoint works)

---

## 📚 Documentation

- **Setup Guide:** This file
- **Device Ownership:** `docs/DEVICE-OWNERSHIP-UPDATE.md`
- **User-Scoped IDs:** `docs/USER-SCOPED-DEVICE-ID.md`
- **Testing Guide:** `docs/TESTING-OWNERSHIP-SECURITY.md`
- **Auto Water Guide:** `docs/AUTO-WATER-GUIDE.md`
- **Troubleshooting:** `docs/TROUBLESHOOTING-*.md`

---

## 🐛 Troubleshooting

### Migration Errors

**Error: "Column already exists"**
```bash
# Check migration status
php artisan migrate:status

# Rollback if needed
php artisan migrate:rollback

# Fresh migration (CAUTION: Deletes all data!)
php artisan migrate:fresh
```

### Device Not Appearing

**Check:**
1. Device has `user_id` assigned
2. Device ID format: `user_{id}_chip_{chip_id}`
3. User is logged in as correct user

```bash
php artisan tinker
>>> $device = App\Models\Device::find('user_1_chip_62563');
>>> $device->user_id;  // Should match logged-in user
```

### ESP8266 Provisioning Fails

**Check:**
1. WiFi credentials correct
2. Server URL correct (HTTPS, no trailing slash)
3. Provisioning token valid and not expired
4. Token not already claimed

```php
>>> $token = App\Models\ProvisioningToken::where('token', 'xxx')->first();
>>> $token->claimed;  // Should be false
>>> $token->expires_at->isFuture();  // Should be true
```

---

## 🔐 Security Features

### Automatic Protection

- ✅ **User-scoped device IDs** - No cross-user conflicts
- ✅ **Ownership validation** - Every API request checked
- ✅ **API key rotation** - New key on re-provision
- ✅ **Middleware protection** - Validates credentials
- ✅ **Comprehensive logging** - Audit trail for all actions

### Middleware Stack

All device API endpoints protected by:
1. `device.key` - Validates API key
2. `device.ownership` - Validates device ownership

---

## 📞 Support

**Issues:** https://github.com/kurokana/SmartPlants-IOT/issues  
**Email:** [Your support email]

---

## 📄 License

[Your License Here]

---

**Version:** 3.0  
**Last Updated:** November 26, 2025  
**Status:** Production Ready ✅
