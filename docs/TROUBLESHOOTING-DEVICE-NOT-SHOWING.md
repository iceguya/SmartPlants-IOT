# 🔧 Troubleshooting: Device Tidak Muncul di Dashboard

## Problem
Device berhasil provisioning tapi **tidak muncul di "Connected Devices"** di dashboard.

---

## 🔍 Root Cause Analysis

### **Issue 1: Device Tidak Punya user_id**

Device dibuat tapi `user_id` = NULL → tidak akan muncul di dashboard user manapun.

**Check:**
```bash
php scripts/check-devices.php
```

**Look for:**
```
⚠️  ORPHANED DEVICES (no user_id):
━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━
  - 62563 (ESP8266 SmartPlant)
```

**Fix:**
```bash
php scripts/fix-device-user-id.php
```

---

### **Issue 2: Provisioning Token Tidak Punya user_id**

Token dibuat tanpa `user_id` → device yang di-claim juga tidak dapat user_id.

**Check:**
```bash
php scripts/check-tokens.php
```

**Look for:**
```
Token:           ub78Nc5t9gt4iYWJDF922gtRqM4ER7lVN7BP
User ID:         ❌ NULL
User Email:      ❌ NO USER
```

**Fix:**
Buat token baru dengan user_id:
```bash
php artisan tinker
```
```php
$user = App\Models\User::where('email', 'pedal@gmail.com')->first();

App\Models\ProvisioningToken::create([
    'token' => Illuminate\Support\Str::random(36),
    'user_id' => $user->id,  // ← PENTING!
    'planned_device_id' => 'esp-' . time(),
    'name_hint' => 'ESP8266 SmartPlant',
    'location_hint' => 'Home',
    'expires_at' => now()->addDays(30),
    'claimed' => false,
]);
```

---

### **Issue 3: Device Di-Claim Berkali-kali**

ESP8266 menggunakan `ESP.getChipId()` sebagai device_id. Jika provision ulang dengan token berbeda, **device yang sama** akan pindah user!

**Contoh Problem:**
```
Token 1 (user A): Claims device 62563 → user_id = 1
Token 2 (user B): Claims device 62563 LAGI → user_id = 2 (overwrite!)
```

**Result:** Device hanya muncul di dashboard user B, **hilang dari dashboard user A**!

**Check:**
```bash
php scripts/check-tokens.php
```

**Look for:**
```
Token:           zsDX4SgsW80UHzgJONXVn7m2gpPT347bDmoL
Claimed Device:  62563
⚠️  WARNING: Device user_id (2) != Token user_id (1)

Token:           SDtm7FStN2cBqXZku4V2jAXB3OopqX656w7k
Claimed Device:  62563  ← SAME DEVICE!
```

**Fix Options:**

**A. Prevent Re-Claim (Already Fixed in Code)**
```php
// In ProvisioningController.php
if ($device->user_id !== null && $device->user_id !== $pt->user_id) {
    return response()->json([
        'message' => 'Device already claimed by another user.'
    ], 409);
}
```

**B. Clear EEPROM Before Re-Provisioning**
```
1. Upload: ESP8266_Clear_EEPROM.ino
2. Wait for "✅ EEPROM cleared successfully!"
3. Upload: esp8266_full_automation.ino with NEW token
4. Device will get new credentials
```

**C. Manual Device Transfer**
```bash
php artisan tinker
```
```php
// Transfer device to another user
$device = App\Models\Device::find('62563');
$newUser = App\Models\User::where('email', 'pedal@gmail.com')->first();

$device->update(['user_id' => $newUser->id]);

echo "Device transferred to: " . $newUser->email;
```

---

## 📋 Diagnostic Checklist

### **Step 1: Check Users**
```bash
php scripts/check-devices.php
```

Verify:
- ✅ User exists with email you're logged in with
- ✅ User has ID (e.g., ID: 2)

### **Step 2: Check Devices**
```bash
php scripts/check-devices.php
```

Verify:
- ✅ Device exists in database
- ✅ Device has `user_id` (not NULL)
- ✅ Device `user_id` matches **your** user ID
- ✅ Device `status` = 'online' or has recent `last_seen`

### **Step 3: Check Provisioning Tokens**
```bash
php scripts/check-tokens.php
```

Verify:
- ✅ Token has `user_id`
- ✅ Token `user_id` matches device `user_id`
- ✅ Token not expired
- ✅ No duplicate claims on same device

### **Step 4: Check Dashboard Query**

The dashboard shows devices using this query:
```php
Device::where('user_id', auth()->id())->get();
```

If `user_id` doesn't match or is NULL, device **will not appear**!

---

## 🛠️ Fix Scripts

### **Automatic Fix (Recommended)**
```bash
php scripts/fix-device-user-id.php
```

This will:
1. Find devices without `user_id`
2. Assign them to first user (or you can choose)
3. Update database
4. Create new provisioning token with correct `user_id`

### **Manual Fix via Tinker**
```bash
php artisan tinker
```

**Check current state:**
```php
// Show all devices
App\Models\Device::all();

// Show devices without user_id
App\Models\Device::whereNull('user_id')->get();

// Show your user
$me = App\Models\User::where('email', 'pedal@gmail.com')->first();
$me->devices; // Show devices belonging to you
```

**Fix device user_id:**
```php
$device = App\Models\Device::find('62563');
$user = App\Models\User::where('email', 'pedal@gmail.com')->first();

$device->update(['user_id' => $user->id]);

echo "Fixed! Device now belongs to: " . $user->email;
```

---

## ✅ Verification Steps

After fixing, verify device appears:

### **1. Check via Script**
```bash
php scripts/check-devices.php
```

Look for:
```
🔍 DASHBOARD QUERY SIMULATION:
━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━
User: pedal@gmail.com
Devices visible in dashboard: 1
  - ESP8266 SmartPlant (🟢 ONLINE)  ← Should see your device!
```

### **2. Check Dashboard**
1. Login to https://kurokana.alwaysdata.net
2. Go to Dashboard
3. Look for "Connected Devices" section
4. Device should appear with:
   - ✅ Name: ESP8266 SmartPlant
   - ✅ Status: Online (green dot)
   - ✅ Last seen: Recent timestamp

### **3. Check ESP8266 Serial**
```
✅ WiFi Connected!
✅ Credentials loaded from EEPROM
📡 Sending initial status update...
✅ Device status updated
✅ Device ready!
```

---

## 🔐 Best Practices

### **For Production:**

1. **Create tokens via dashboard UI (future feature)**
   - Automatically assigns `user_id` from logged-in user
   - No manual tinker commands needed

2. **One token per device**
   - Don't reuse tokens
   - Don't provision same device multiple times

3. **Clear EEPROM before transfer**
   - If device changes owner
   - Upload `ESP8266_Clear_EEPROM.ino` first
   - Then provision with new user's token

4. **Monitor provisioning logs**
   ```bash
   tail -f storage/logs/laravel.log | grep -i provision
   ```

### **For Development:**

1. **Always create tokens with user_id:**
   ```php
   ProvisioningToken::create([
       'token' => Str::random(36),
       'user_id' => auth()->id(), // ← From logged-in user
       // ... other fields
   ]);
   ```

2. **Use fix scripts regularly:**
   ```bash
   php scripts/check-devices.php   # Daily health check
   php scripts/check-tokens.php    # Before provisioning
   ```

3. **Database migrations:**
   ```bash
   # After any schema changes
   php artisan migrate:fresh --seed
   php artisan db:seed --class=DemoSeeder
   ```

---

## 📊 Summary

**Device muncul di dashboard jika dan hanya jika:**
1. ✅ Device exists in `devices` table
2. ✅ Device has `user_id` = current logged-in user's ID
3. ✅ Device `last_seen` is recent (< 5 minutes for online status)
4. ✅ User is authenticated and viewing dashboard

**Common mistakes:**
- ❌ Token created without `user_id`
- ❌ Device provisioned but `user_id` = NULL
- ❌ Same device claimed by multiple tokens (overwrite)
- ❌ ESP8266 not sending data (never updates `last_seen`)

**Quick fix command:**
```bash
php scripts/fix-device-user-id.php
```

This fixes 90% of "device not showing" issues! 🚀
