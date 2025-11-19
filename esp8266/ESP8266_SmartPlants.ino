/*
 * SmartPlants ESP8266 - Provisioning + Data Sender
 * 
 * Flow:
 * 1. Boot -> cek apakah ada credentials tersimpan di EEPROM
 * 2. Jika tidak ada -> masuk mode provisioning (claim token)
 * 3. Jika ada -> kirim data sensor ke server
 * 
 * Dependencies:
 * - ESP8266WiFi
 * - ESP8266HTTPClient
 * - ArduinoJson (install via Library Manager)
 */

#include <ESP8266WiFi.h>
#include <ESP8266HTTPClient.h>
#include <ArduinoJson.h>
#include <EEPROM.h>

// ===== KONFIGURASI WiFi =====
const char* ssid = "pedal";
const char* password = "12345689";

// ===== KONFIGURASI SERVER =====
const char* serverUrl = "http://192.168.137.1:8000"; // Ganti dengan IP/domain server Anda
// Untuk HTTPS, gunakan WiFiClientSecure dan ganti http:// dengan https://

// ===== PROVISIONING TOKEN =====
// Dapatkan token dari web dashboard (http://server/provisioning -> klik Generate)
const char* provisionToken = "zsDX4SgsW80UHzgJONXVn7m2gpPT347bDmoL"; // GANTI dengan token dari web

// ===== STORAGE CREDENTIALS =====
struct Credentials {
  char magic[4];      // "SPLT" marker untuk validasi
  char deviceId[32];
  char apiKey[48];
  bool isValid;
};

Credentials creds;

// ===== EEPROM HELPERS =====
void saveCredentials() {
  EEPROM.begin(512);
  
  // Set magic marker
  creds.magic[0] = 'S';
  creds.magic[1] = 'P';
  creds.magic[2] = 'L';
  creds.magic[3] = 'T';
  creds.isValid = true;
  
  EEPROM.put(0, creds);
  EEPROM.commit();
  EEPROM.end();
  Serial.println("✅ Credentials saved to EEPROM");
}

void loadCredentials() {
  EEPROM.begin(512);
  EEPROM.get(0, creds);
  EEPROM.end();
  
  // Validasi magic marker
  if (creds.magic[0] == 'S' && creds.magic[1] == 'P' && 
      creds.magic[2] == 'L' && creds.magic[3] == 'T' && creds.isValid) {
    Serial.println("✅ Credentials loaded from EEPROM");
    Serial.print("Device ID: "); Serial.println(creds.deviceId);
    Serial.print("API Key: "); Serial.println(creds.apiKey);
  } else {
    Serial.println("⚠️  No valid credentials found (EEPROM empty or corrupt)");
    creds.isValid = false;
  }
}

void clearCredentials() {
  Serial.println("🗑️  Clearing EEPROM...");
  EEPROM.begin(512);
  for (int i = 0; i < 512; i++) {
    EEPROM.write(i, 0);
  }
  EEPROM.commit();
  EEPROM.end();
  
  memset(&creds, 0, sizeof(creds));
  creds.isValid = false;
  
  Serial.println("✅ EEPROM cleared successfully!");
}

// ===== PROVISIONING =====
bool doProvisioning() {
  Serial.println("🔧 Starting provisioning...");
  
  WiFiClient client;
  HTTPClient http;
  
  String chipId = String(ESP.getChipId());
  String url = String(serverUrl) + "/api/provision/claim";
  
  http.begin(client, url);
  http.addHeader("Content-Type", "application/json");
  
  // Buat JSON body
  DynamicJsonDocument doc(256);
  doc["token"] = provisionToken;
  doc["device_id"] = chipId;
  doc["name"] = "ESP8266 SmartPlant";
  doc["location"] = "Home";
  
  String body;
  serializeJson(doc, body);
  
  Serial.println("Sending: " + body);
  
  int code = http.POST(body);
  
  if (code == 200) {
    String response = http.getString();
    Serial.println("Response: " + response);
    
    DynamicJsonDocument resDoc(512);
    DeserializationError error = deserializeJson(resDoc, response);
    
    if (error) {
      Serial.print("❌ JSON parse error: ");
      Serial.println(error.c_str());
      http.end();
      return false;
    }
    
    // Simpan credentials
    String returnedDeviceId = resDoc["device_id"].as<String>();
    String apiKey = resDoc["api_key"].as<String>();
    
    returnedDeviceId.toCharArray(creds.deviceId, 32);
    apiKey.toCharArray(creds.apiKey, 48);
    creds.isValid = true;
    
    saveCredentials();
    
    Serial.println("✅ Provisioning SUCCESS!");
    Serial.print("Device ID: "); Serial.println(creds.deviceId);
    Serial.print("API Key: "); Serial.println(creds.apiKey);
    
    http.end();
    return true;
    
  } else {
    Serial.printf("❌ Provisioning FAILED (HTTP %d)\n", code);
    Serial.println("Response: " + http.getString());
    http.end();
    return false;
  }
}

// ===== KIRIM DATA SENSOR =====
bool sendSensorData(float soil, float temp, float hum, float r, float g, float b) {
  WiFiClient client;
  HTTPClient http;
  
  String url = String(serverUrl) + "/api/ingest";
  
  http.begin(client, url);
  http.addHeader("Content-Type", "application/json");
  http.addHeader("X-Device-Id", String(creds.deviceId));
  http.addHeader("X-Api-Key", String(creds.apiKey));
  
  // Buat JSON payload
  DynamicJsonDocument doc(512);
  JsonArray readings = doc.createNestedArray("readings");
  
  JsonObject r1 = readings.createNestedObject();
  r1["type"] = "soil";
  r1["value"] = soil;
  
  JsonObject r2 = readings.createNestedObject();
  r2["type"] = "temp";
  r2["value"] = temp;
  
  JsonObject r3 = readings.createNestedObject();
  r3["type"] = "hum";
  r3["value"] = hum;
  
  JsonObject r4 = readings.createNestedObject();
  r4["type"] = "color_r";
  r4["value"] = r;
  
  JsonObject r5 = readings.createNestedObject();
  r5["type"] = "color_g";
  r5["value"] = g;
  
  JsonObject r6 = readings.createNestedObject();
  r6["type"] = "color_b";
  r6["value"] = b;
  
  String body;
  serializeJson(doc, body);
  
  Serial.println("📤 Sending data: " + body);
  
  int code = http.POST(body);
  
  if (code == 200) {
    Serial.println("✅ Data sent successfully!");
    String response = http.getString();
    Serial.println("Response: " + response);
    http.end();
    return true;
  } else {
    Serial.printf("❌ HTTP failed (code %d)\n", code);
    Serial.println("Response: " + http.getString());
    http.end();
    return false;
  }
}

// ===== SETUP =====
void setup() {
  Serial.begin(115200);
  delay(100);
  Serial.println("\n\n=== SmartPlants ESP8266 ===");
  
  // UNCOMMENT baris di bawah untuk RESET EEPROM (hapus credentials lama)
  //clearCredentials();
 // Serial.println("🗑️  EEPROM cleared! Remove this line and re-upload.");
  //while(1) delay(1000); // Stop here
  
  // Connect WiFi
  Serial.print("Connecting to WiFi");
  WiFi.begin(ssid, password);
  
  int attempts = 0;
  while (WiFi.status() != WL_CONNECTED && attempts < 20) {
    delay(500);
    Serial.print(".");
    attempts++;
  }
  
  if (WiFi.status() != WL_CONNECTED) {
    Serial.println("\n❌ WiFi connection failed!");
    Serial.println("Restarting...");
    delay(3000);
    ESP.restart();
  }
  
  Serial.println("\n✅ WiFi connected!");
  Serial.print("IP: ");
  Serial.println(WiFi.localIP());
  
  // Load credentials
  loadCredentials();
  
  // Jika belum ada credentials, lakukan provisioning
  if (!creds.isValid) {
    Serial.println("\n⚠️  No credentials found. Starting provisioning...");
    
    if (!doProvisioning()) {
      Serial.println("❌ Provisioning failed. Please check:");
      Serial.println("1. Token is valid and not expired");
      Serial.println("2. Server URL is correct");
      Serial.println("3. Server is running");
      Serial.println("\nRestarting in 10 seconds...");
      delay(10000);
      ESP.restart();
    }
  }
  
  Serial.println("\n✅ Device ready to send data!");
}

// ===== LOOP =====
void loop() {
  // Baca sensor (contoh dummy data)
  float soilMoisture = random(20, 80) + random(0, 100) / 100.0;
  float temperature = 20 + random(0, 15) + random(0, 100) / 100.0;
  float humidity = 50 + random(0, 30) + random(0, 100) / 100.0;
  float colorR = random(0, 255);
  float colorG = random(50, 200);
  float colorB = random(0, 100);
  
  Serial.println("\n📊 Sensor readings:");
  Serial.printf("  Soil: %.2f%%\n", soilMoisture);
  Serial.printf("  Temp: %.2f°C\n", temperature);
  Serial.printf("  Hum: %.2f%%\n", humidity);
  Serial.printf("  RGB: (%.0f, %.0f, %.0f)\n", colorR, colorG, colorB);
  
  // Kirim ke server
  if (sendSensorData(soilMoisture, temperature, humidity, colorR, colorG, colorB)) {
    Serial.println("✅ Cycle complete");
  } else {
    Serial.println("❌ Send failed - will retry next cycle");
  }
  
  // Tunggu 30 detik sebelum kirim lagi
  Serial.println("\n⏳ Waiting 30 seconds...\n");
  delay(30000);
}

/* 
 * CATATAN PENTING:
 * 
 * 1. SEBELUM UPLOAD:
 *    - Ganti ssid dan password WiFi
 *    - Ganti serverUrl dengan IP/domain server Anda
 *    - Dapatkan token dari web (http://server/provisioning)
 *    - Masukkan token ke variable provisionToken
 * 
 * 2. RESET CREDENTIALS:
 *    - Jika ingin reset device, panggil clearCredentials() di setup()
 *    - Atau hapus data EEPROM via Serial Monitor
 * 
 * 3. TROUBLESHOOTING:
 *    - Buka Serial Monitor (115200 baud)
 *    - Periksa pesan error
 *    - Pastikan server Laravel berjalan (php artisan serve)
 *    - Pastikan ESP8266 bisa ping ke server
 * 
 * 4. GANTI DUMMY DATA dengan sensor asli:
 *    - Tambahkan library sensor (DHT22, soil moisture, TCS3200)
 *    - Ganti nilai dummy di loop() dengan pembacaan sensor real
 */
