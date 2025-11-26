# 🔧 Troubleshooting: Auto Water Command Not Working

## Problem
Ketika menekan tombol **Auto Water** dan **Aktifkan** di dashboard, relay dan pompa tidak menyala di ESP8266.

---

## 🔍 **Debugging Steps**

### **Step 1: Verify Command Creation**

Cek apakah command berhasil dibuat di database:

```bash
# Via PHP Script
php scripts/test-water-command.php [device_id] [duration]

# Contoh:
php scripts/test-water-command.php esp-plant-01 10
```

**Expected Output:**
```
✅ Water command created!
ID:          123
Command:     water_on
Duration:    10 seconds
Status:      pending
```

**Via Database:**
```bash
php artisan tinker
```
```php
// Check latest commands
\App\Models\Command::latest()->take(5)->get();

// Check pending commands for specific device
\App\Models\Command::where('device_id', 'YOUR_DEVICE_ID')
    ->where('status', 'pending')
    ->get();
```

---

### **Step 2: Monitor Command Flow**

Gunakan script monitoring:

```bash
php scripts/monitor-commands.php [device_id]
```

**Expected Flow:**
```
🟡 PENDING → 🔵 SENT → ✅ ACK
```

**Command lifecycle:**
1. **pending**: Command created from dashboard
2. **sent**: ESP8266 fetched command via `/api/commands/next`
3. **ack**: ESP8266 executed and sent acknowledgment

---

### **Step 3: Check Server Logs**

Monitor Laravel logs in real-time:

```bash
# Linux/Mac
tail -f storage/logs/laravel.log

# Windows PowerShell
Get-Content storage/logs/laravel.log -Wait -Tail 50
```

**What to look for:**
```
[timestamp] local.INFO: Water command created from dashboard
  {"device_id":"esp-plant-01","command_id":123,"duration":10}

[timestamp] local.INFO: Command polling from device
  {"device_id":"esp-plant-01"}

[timestamp] local.INFO: Sending command to device
  {"device_id":"esp-plant-01","command_id":123,"command":"water_on","params":{"duration_sec":10}}

[timestamp] local.INFO: Command ACK received
  {"device_id":"esp-plant-01","command_id":123,"command":"water_on"}
```

---

### **Step 4: Monitor ESP8266 Serial Output**

Upload `esp8266_full_automation.ino` dengan debug logging yang sudah ditambahkan.

**Expected Serial Output:**

```
🔍 Checking for commands...
📡 Command check response: 200
📥 Response: {"id":123,"command":"water_on","params":{"duration_sec":10}}
📥 Command received: water_on (ID: 123)
💧 Executing water_on for 10 seconds...
💧 Water ON for 10 seconds
✅ Water OFF
📤 Sending ACK to: https://kurokana.alwaysdata.net/api/commands/123/ack
ACK Response: 200
✅ Command ACK sent
```

**Common Issues in Serial:**

❌ **No polling output**
```
Problem: ESP8266 not calling checkCommands()
Solution: Verify loop() is running and COMMAND_INTERVAL is correct
```

❌ **HTTP 401 Unauthorized**
```
Problem: Invalid device credentials
Solution: Re-provision device or check API key in database
```

❌ **HTTP 404 Not Found**
```
Problem: API route not found
Solution: Check routes/api.php and run php artisan route:clear
```

❌ **Command is null**
```
Problem: No pending commands in database
Solution: Create command from dashboard or via test script
```

---

## 🐛 **Common Problems & Solutions**

### **Problem 1: Command Created but Never Sent**

**Symptoms:**
- Command stuck in `pending` status
- ESP8266 logs show: `⏸️ No pending commands`

**Diagnosis:**
```bash
# Check if device_id matches
php artisan tinker
```
```php
$cmd = \App\Models\Command::where('status', 'pending')->first();
echo "Command device_id: " . $cmd->device_id . "\n";

$device = \App\Models\Device::first();
echo "Actual device_id: " . $device->id . "\n";
```

**Solution:**
- Device ID mismatch! Update command or device ID
- Check `creds.deviceId` in ESP8266 EEPROM matches database

---

### **Problem 2: ESP8266 Not Polling Commands**

**Symptoms:**
- No "🔍 Checking for commands..." in Serial
- Commands never transition from pending

**Diagnosis:**
Check ESP8266 code:
```cpp
// In loop(), verify this is called
if (now - lastCommandCheck >= COMMAND_INTERVAL) {
    lastCommandCheck = now;
    checkCommands(); // ← Must be here!
}
```

**Solution:**
- Verify `COMMAND_INTERVAL` is defined (default 10000ms)
- Ensure loop() is not blocked by other operations
- Check WiFi connection is stable

---

### **Problem 3: HTTP 401 Unauthorized**

**Symptoms:**
```
📡 Command check response: 401
❌ Command check failed (code 401)
```

**Diagnosis:**
```bash
# Check device credentials
php artisan tinker
```
```php
$device = \App\Models\Device::find('YOUR_DEVICE_ID');
echo "Device API Key: " . $device->api_key . "\n";
```

**Solution:**
1. **Clear ESP8266 EEPROM:**
   ```
   Upload: esp8266/ESP8266_Clear_EEPROM.ino
   Wait for "✅ EEPROM cleared successfully!"
   ```

2. **Re-provision device:**
   ```
   Upload: esp8266_full_automation.ino
   Monitor Serial - device will auto-provision
   ```

---

### **Problem 4: Relay Not Activating**

**Symptoms:**
- Serial shows: `💧 Water ON for X seconds`
- But relay/pompa tidak menyala

**Diagnosis:**
Check hardware connections:
```cpp
// In setup(), verify:
pinMode(RELAY_PIN, OUTPUT);
digitalWrite(RELAY_PIN, HIGH); // Relay OFF

// In executeWaterOn():
digitalWrite(RELAY_PIN, LOW);  // ← Should turn relay ON
delay(duration * 1000);
digitalWrite(RELAY_PIN, HIGH); // ← Should turn relay OFF
```

**Solutions:**

**A. Relay Active HIGH (not LOW):**
```cpp
// Change executeWaterOn() to:
digitalWrite(RELAY_PIN, HIGH);  // ON (if relay is active HIGH)
delay(durationSec * 1000);
digitalWrite(RELAY_PIN, LOW);   // OFF
```

**B. Wrong Pin:**
```cpp
// Verify RELAY_PIN matches your wiring
#define RELAY_PIN D4  // Try D1, D2, D5, etc.
```

**C. Test Relay Manually:**
```cpp
// Add to loop() temporarily:
digitalWrite(RELAY_PIN, LOW);
delay(2000);
digitalWrite(RELAY_PIN, HIGH);
delay(2000);
```

---

### **Problem 5: Command Sent but Not Executed**

**Symptoms:**
- Command transitions: pending → sent
- But never reaches `ack`
- No execution logs in Serial

**Diagnosis:**
Check JSON parsing in `checkCommands()`:

**Solution:**
Verify ESP8266 firmware has updated `checkCommands()` with proper error handling:
```cpp
DeserializationError error = deserializeJson(doc, response);
if (error) {
    Serial.println("❌ JSON parse error: " + String(error.c_str()));
    return;
}
```

---

## 📊 **Testing Checklist**

- [ ] **Database**: Command created with `status='pending'`
- [ ] **Device ID**: Matches between ESP8266 EEPROM and database
- [ ] **API Routes**: `/api/commands/next` and `/api/commands/{id}/ack` accessible
- [ ] **Middleware**: `device.key` middleware passes (valid API key)
- [ ] **ESP8266 Polling**: `checkCommands()` called every 10 seconds
- [ ] **Serial Logs**: Shows command received and executed
- [ ] **Server Logs**: Shows command lifecycle (created → sent → ack)
- [ ] **Relay Pin**: Correct pin number and active level (HIGH/LOW)
- [ ] **Hardware**: Relay, pompa, power supply connected properly

---

## 🚀 **Quick Test Commands**

```bash
# 1. Create test command
php scripts/test-water-command.php esp-plant-01 5

# 2. Monitor in real-time
php scripts/monitor-commands.php esp-plant-01

# 3. Watch server logs
tail -f storage/logs/laravel.log | grep -i command

# 4. Check database
php artisan tinker
> \App\Models\Command::where('status', 'pending')->count()
> \App\Models\Command::latest()->first()
```

---

## 📝 **Manual API Test**

Test API endpoints manually:

```bash
# Get device credentials first
php artisan tinker
```
```php
$device = \App\Models\Device::first();
echo "Device ID: " . $device->id . "\n";
echo "API Key: " . $device->api_key . "\n";
```

**Test `/api/commands/next`:**
```bash
curl -X GET https://kurokana.alwaysdata.net/api/commands/next \
  -H "X-Device-Id: YOUR_DEVICE_ID" \
  -H "X-Api-Key: YOUR_API_KEY"
```

**Expected Response (no commands):**
```json
{"command":null}
```

**Expected Response (has command):**
```json
{
  "id": 123,
  "command": "water_on",
  "params": {
    "duration_sec": 10
  }
}
```

**Test `/api/commands/{id}/ack`:**
```bash
curl -X POST https://kurokana.alwaysdata.net/api/commands/123/ack \
  -H "X-Device-Id: YOUR_DEVICE_ID" \
  -H "X-Api-Key: YOUR_API_KEY"
```

**Expected Response:**
```json
{"message":"ACK received"}
```

---

## 🎯 **Solution Summary**

**Penyebab paling umum:**
1. ✅ **Device ID mismatch** - ESP8266 device_id ≠ database device_id
2. ✅ **ESP8266 tidak polling** - `checkCommands()` tidak dipanggil di loop()
3. ✅ **Relay active level salah** - Relay active HIGH tapi code pakai LOW
4. ✅ **Credentials invalid** - API key tidak match atau expired

**Fix steps:**
1. Upload firmware dengan logging (sudah diupdate)
2. Monitor Serial output (115200 baud)
3. Cek server logs (`tail -f storage/logs/laravel.log`)
4. Test relay manually untuk confirm hardware OK
5. Verify device credentials match antara ESP8266 dan database

---

**Need help?** Share:
- Serial Monitor output
- Server log excerpt
- Command monitor output
- Hardware wiring diagram
