/*
 * SmartPlants ESP8266 - Dummy Data (Auto-Provisioning Version)
 * Updated by: Gemini Analysis
 */

#include <ESP8266WiFi.h>
#include <ESP8266HTTPClient.h>
#include <ArduinoJson.h>
#include <EEPROM.h>

// ===== KONFIGURASI USER (WAJIB DIGANTI) =====
const char* ssid = "NASA";
const char* password = "outerspace";

// Ganti dengan IP komputermu (jangan localhost)
const char* serverUrl = "http://192.168.43.116:8000"; 

// ⚠️ PENTING: Masukkan Token BARU dari Web Dashboard setiap kali flash ulang/reset DB
const char* provisionToken = "n3jPaLQyHXLstXrrFirQEHtq8IJiNA97uME1"; 

// ===== EEPROM Credentials =====
struct Credentials {
  char magic[4];      // Penanda unik "SPLT"
  char deviceId[32];
  char apiKey[48];
  bool isValid;
};
Credentials creds;

// ===== Helper EEPROM =====
void saveCredentials() {
  EEPROM.begin(512);
  creds.magic[0] = 'S'; creds.magic[1] = 'P'; creds.magic[2] = 'L'; creds.magic[3] = 'T';
  creds.isValid = true;
  EEPROM.put(0, creds);
  EEPROM.commit();
  EEPROM.end();
  Serial.println("💾 Credentials saved to EEPROM");
}

// FUNGSI BARU: Menghapus kredensial jika ditolak server
void clearCredentials() {
  EEPROM.begin(512);
  // Kita hanya perlu merusak 'magic' bytes atau set isValid ke false
  creds.isValid = false;
  creds.magic[0] = 0; 
  EEPROM.put(0, creds);
  EEPROM.commit();
  EEPROM.end();
  Serial.println("🗑️ Credentials CLEARED from EEPROM");
}

void loadCredentials() {
  EEPROM.begin(512);
  EEPROM.get(0, creds);
  EEPROM.end();

  if (creds.magic[0]=='S' && creds.magic[1]=='P' && creds.magic[2]=='L' && creds.magic[3]=='T' && creds.isValid) {
    Serial.println("✅ Credentials loaded from EEPROM");
  } else {
    creds.isValid = false;
    Serial.println("⚠️ No valid credentials found");
  }
}

// ===== Provisioning =====
bool doProvisioning() {
  WiFiClient client;
  HTTPClient http;
  String url = String(serverUrl) + "/api/provision/claim";

  Serial.println("🔧 Starting Provisioning...");
  Serial.println("Target: " + url);

  client.setTimeout(10000);
  http.begin(client, url);
  http.addHeader("Content-Type", "application/json");
  http.addHeader("Accept", "application/json"); // Good practice

  DynamicJsonDocument doc(256);
  doc["token"] = provisionToken;
  doc["device_id"] = String(ESP.getChipId()); // ID Hardware Unik
  doc["name"] = "ESP8266 SmartPlant";
  doc["location"] = "Garden";

  String body;
  serializeJson(doc, body);
  
  int code = http.POST(body);

  if (code == 200) {
    String response = http.getString();
    DynamicJsonDocument resDoc(512);
    
    if (deserializeJson(resDoc, response)) {
      Serial.println("❌ JSON parse error!");
      return false;
    }

    // Simpan kredensial baru
    String id = resDoc["device_id"].as<String>();
    String key = resDoc["api_key"].as<String>();

    id.toCharArray(creds.deviceId, 32);
    key.toCharArray(creds.apiKey, 48);
    saveCredentials();
    
    Serial.println("🎉 Provisioning SUCCESS!");
    http.end();
    return true;
  } else {
    Serial.printf("❌ Provisioning failed (code %d)\n", code);
    Serial.println("Response: " + http.getString());
    http.end();
    return false;
  }
}

// ===== Generate Dummy Data =====
#include <stdlib.h>

float randomFloat(float min, float max) {
  return min + ((float)rand() / RAND_MAX) * (max - min);
}

// ===== Kirim Data (Dengan Auto-Reset) =====
bool sendSensorData(float soil, float temp, float hum, float r, float g, float b) {
  WiFiClient client;
  HTTPClient http;
  String url = String(serverUrl) + "/api/ingest";

  // Setup timeouts agar tidak error -11
  client.setTimeout(15000);
  http.begin(client, url);
  http.setTimeout(15000);
  
  http.addHeader("Content-Type", "application/json");
  http.addHeader("X-Device-Id", String(creds.deviceId));
  http.addHeader("X-Api-Key", String(creds.apiKey));

  DynamicJsonDocument doc(512);
  JsonArray readings = doc.createNestedArray("readings");

  // Helper lambda
  auto addReading = [&](const char* type, float val) {
      JsonObject obj = readings.createNestedObject();
      obj["type"] = type;
      obj["value"] = val;
  };

  addReading("soil", soil);
  addReading("temp", temp);
  addReading("hum", hum);
  addReading("color_r", r);
  addReading("color_g", g);
  addReading("color_b", b);

  String body;
  serializeJson(doc, body);
  Serial.println("📤 Sending Data...");

  int code = http.POST(body);

  bool success = false;
  if (code == 200) {
    Serial.println("✅ Data sent successfully! (200 OK)");
    success = true;
  } 
  else if (code == 401) {
    // === SELF HEALING LOGIC ===
    Serial.println("❌ Error 401: Unauthorized / Invalid Credentials.");
    Serial.println("🔄 System will reset credentials and restart to re-provision.");
    
    clearCredentials(); // Hapus API Key lama
    delay(1000);
    ESP.restart();      // Restart untuk masuk mode provisioning
  }
  else {
    Serial.printf("❌ HTTP Failed. Error Code: %d\n", code);
    if (code == -11) Serial.println("⚠️ Timeout detected.");
    String payload = http.getString();
    if (payload.length() > 0) Serial.println("Server says: " + payload);
  }

  http.end();
  return success;
}

// ===== Setup =====
void setup() {
  Serial.begin(115200);
  delay(1000); // Beri waktu serial monitor
  Serial.println("\n\n=== SmartPlants (Auto-Prov) ===");
  srand(millis());

  WiFi.begin(ssid, password);
  Serial.print("Connecting to WiFi");
  
  int attempt = 0;
  while (WiFi.status() != WL_CONNECTED && attempt < 30) {
    delay(500); Serial.print(".");
    attempt++;
  }

  if (WiFi.status() != WL_CONNECTED) {
    Serial.println("\n❌ WiFi Failed. Check SSID/Password.");
    delay(5000);
    ESP.restart();
  }
  Serial.println("\n✅ WiFi Connected!");
  Serial.print("IP: "); Serial.println(WiFi.localIP());

  loadCredentials();

  // Jika kredensial tidak valid (atau baru di-clear), lakukan provisioning
  if (!creds.isValid) {
    Serial.println("⚙️ No valid credentials. Starting Provisioning sequence...");
    
    // Loop sampai berhasil provisioning
    while (!doProvisioning()) {
      Serial.println("❌ Provisioning failed. Retrying in 10s...");
      Serial.println("⚠️ TIP: Make sure 'provisionToken' in code matches Web Dashboard!");
      delay(10000);
    }
  }

  Serial.println("✅ Device Authenticated & Ready!");
}

// ===== Loop =====
void loop() {
  // Data Dummy
  float soil = randomFloat(30, 80);
  float temp = randomFloat(25, 32);
  float hum  = randomFloat(50, 90);
  float r    = randomFloat(0, 255);
  float g    = randomFloat(0, 255);
  float b    = randomFloat(0, 255);

  Serial.println("\n📊 Sensor Dummy Data:");
  Serial.printf("Soil: %.2f%%\n", soil);
  
  sendSensorData(soil, temp, hum, r, g, b);

  int sleepMs = 10000; // Kirim tiap 10 detik
  Serial.printf("⏳ Next send in %d ms...\n", sleepMs);
  delay(sleepMs);
}