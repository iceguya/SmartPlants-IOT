#include <ESP8266WiFi.h>
#include <ESP8266HTTPClient.h>
#include <WiFiClientSecure.h>  
#include <ArduinoJson.h>
#include <EEPROM.h>

// ===== KONFIGURASI WiFi =====
const char* ssid = "pedal";          
const char* password = "12345689";   

// ===== KONFIGURASI SERVER (HTTPS) =====
const char* serverUrl = "https://kurokana.alwaysdata.net";  

// ===== PROVISIONING TOKEN =====
const char* provisionToken = "zsDX4SgsW80UHzgJONXVn7m2gpPT347bDmoL"; 
// ===== STORAGE CREDENTIALS =====
struct Credentials {
  char magic[4];      // "SPLT" marker
  char deviceId[32];
  char apiKey[48];
  bool isValid;
};

Credentials creds;

// ===== EEPROM HELPERS =====
void saveCredentials() {
  EEPROM.begin(512);
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
  
  if (creds.magic[0] == 'S' && creds.magic[1] == 'P' && 
      creds.magic[2] == 'L' && creds.magic[3] == 'T' && creds.isValid) {
    Serial.println("✅ Credentials loaded from EEPROM");
    Serial.print("Device ID: "); Serial.println(creds.deviceId);
    Serial.print("API Key: "); Serial.println(creds.apiKey);
  } else {
    Serial.println("⚠️  No valid credentials found");
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
  Serial.println("✅ EEPROM cleared!");
}

// ===== PROVISIONING WITH HTTPS =====
bool doProvisioning() {
  Serial.println("🔧 Starting provisioning (HTTPS)...");
  
  WiFiClientSecure client;  
  client.setInsecure();      
  
  HTTPClient http;
  
  String chipId = String(ESP.getChipId());
  String url = String(serverUrl) + "/api/provision/claim";
  
  http.begin(client, url);  
  http.addHeader("Content-Type", "application/json");
  
  DynamicJsonDocument doc(256);
  doc["token"] = provisionToken;
  doc["chip_id"] = chipId;
  
  String payload;
  serializeJson(doc, payload);
  
  Serial.print("📤 POST "); Serial.println(url);
  Serial.print("Body: "); Serial.println(payload);
  
  int code = http.POST(payload);
  
  if (code == 200) {
    String response = http.getString();
    Serial.println("✅ Provisioning SUCCESS!");
    Serial.print("Response: "); Serial.println(response);
    
    DynamicJsonDocument resDoc(512);
    deserializeJson(resDoc, response);
    
    const char* devId = resDoc["device_id"];
    const char* key = resDoc["api_key"];
    
    if (devId && key) {
      strncpy(creds.deviceId, devId, 31);
      strncpy(creds.apiKey, key, 47);
      saveCredentials();
      
      http.end();
      return true;
    } else {
      Serial.println("❌ Invalid response format");
      http.end();
      return false;
    }
  } else {
    Serial.printf("❌ Provisioning FAILED (HTTP %d)\n", code);
    Serial.println("Response: " + http.getString());
    http.end();
    return false;
  }
}

// ===== KIRIM DATA SENSOR WITH HTTPS =====
bool sendSensorData(float soil, float temp, float hum, float r, float g, float b) {
  WiFiClientSecure client;  
  client.setInsecure();      
  
  HTTPClient http;
  
  String url = String(serverUrl) + "/api/ingest";
  
  http.begin(client, url);  
  http.addHeader("Content-Type", "application/json");
  http.addHeader("X-Device-Id", String(creds.deviceId));
  http.addHeader("X-Api-Key", String(creds.apiKey));
  
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
  
  String payload;
  serializeJson(doc, payload);
  
  Serial.print("📤 Sending: "); Serial.println(payload);
  
  int code = http.POST(payload);
  
  if (code == 200) {
    Serial.println("✅ Data sent successfully!");
    http.end();
    return true;
  } else if (code > 0) {
    Serial.printf("❌ HTTP error: %d\n", code);
    Serial.println("Response: " + http.getString());
    http.end();
    return false;
  } else {
    Serial.printf("❌ HTTP failed (code %d)\n", code);
    http.end();
    return false;
  }
}

// ===== DUMMY SENSOR READING =====
void readSensors(float &soil, float &temp, float &hum, float &r, float &g, float &b) {
  soil = random(0, 100) + random(0, 100) / 100.0;
  temp = random(20, 35) + random(0, 100) / 100.0;
  hum = random(40, 80) + random(0, 100) / 100.0;
  r = random(0, 255) + random(0, 100) / 100.0;
  g = random(0, 255) + random(0, 100) / 100.0;
  b = random(0, 255) + random(0, 100) / 100.0;
  
  Serial.println("📊 Sensor Dummy Data:");
  Serial.printf("Soil: %.2f%%\n", soil);
  Serial.printf("Temp: %.2f°C\n", temp);
  Serial.printf("Hum : %.2f%%\n", hum);
  Serial.printf("RGB : (%.0f, %.0f, %.0f)\n", r, g, b);
}

// ===== SETUP =====
void setup() {
  Serial.begin(115200);
  delay(100);
  Serial.println();
  Serial.println("========================================");
  Serial.println("🌱 SmartPlants ESP8266 (HTTPS Version)");
  Serial.println("========================================");
  
  // UNCOMMENT BARIS INI UNTUK RESET EEPROM (hapus credentials)
  // clearCredentials();
  // Serial.println("🗑️  EEPROM cleared!");
  // while(1) delay(1000); // Halt after clearing
  
  // Load credentials dari EEPROM
  loadCredentials();
  
  // Connect WiFi
  Serial.print("📡 Connecting to WiFi: ");
  Serial.println(ssid);
  WiFi.begin(ssid, password);
  
  int attempts = 0;
  while (WiFi.status() != WL_CONNECTED && attempts < 30) {
    delay(500);
    Serial.print(".");
    attempts++;
  }
  
  if (WiFi.status() != WL_CONNECTED) {
    Serial.println();
    Serial.println("❌ WiFi connection FAILED!");
    Serial.println("⚠️  Check your SSID and password, then reset ESP8266");
    while(1) delay(1000); // Halt
  }
  
  Serial.println();
  Serial.println("✅ WiFi connected!");
  Serial.print("IP: "); Serial.println(WiFi.localIP());
  Serial.print("Server: "); Serial.println(serverUrl);
  
  // Jika belum punya credentials, lakukan provisioning
  if (!creds.isValid) {
    Serial.println();
    Serial.println("⚠️  Device not provisioned yet");
    Serial.println("🔧 Starting provisioning...");
    
    if (doProvisioning()) {
      Serial.println("✅ Provisioning SUCCESS! Restarting in 3s...");
      delay(3000);
      ESP.restart();
    } else {
      Serial.println("❌ Provisioning FAILED!");
      Serial.println("⚠️  Check token and server, then reset ESP8266");
      while(1) delay(1000); // Halt
    }
  }
  
  Serial.println();
  Serial.println("✅ Device provisioned and ready!");
  Serial.println("🚀 Starting data transmission...");
  Serial.println();
}

// ===== LOOP =====
void loop() {
  // Check WiFi masih connect
  if (WiFi.status() != WL_CONNECTED) {
    Serial.println("⚠️  WiFi disconnected, reconnecting...");
    WiFi.begin(ssid, password);
    delay(5000);
    return;
  }
  
  // Baca sensor
  float soil, temp, hum, r, g, b;
  readSensors(soil, temp, hum, r, g, b);
  
  // Kirim ke server
  sendSensorData(soil, temp, hum, r, g, b);
  
  // Delay random 7-10 detik
  int delayMs = random(7000, 10000);
  Serial.printf("⏳ Next send in %d ms...\n", delayMs);
  Serial.println();
  delay(delayMs);
}
