#include <ESP8266WiFi.h>
#include <ESP8266HTTPClient.h>
#include <WiFiClientSecure.h>
#include <ArduinoJson.h>
#include <EEPROM.h>
#include <DHT.h>

// ===== PIN KONFIGURASI =====
#define DHTPIN D2
#define DHTTYPE DHT22
#define SOIL_PIN A0
#define RELAY_PIN D4  // Pin untuk relay pompa air

// TCS3200 pins
#define S0 D5
#define S1 D6
#define S2 D7
#define S3 D8
#define OUT D1

DHT dht(DHTPIN, DHTTYPE);

// ===== KALIBRASI WARNA (AUTO-CALIBRATION MODE) =====
// PERHATIAN: Range ini akan di-update otomatis berdasarkan pembacaan sensor
// Nilai awal ini adalah default, sensor akan auto-adjust
int R_MIN = 15;   
int R_MAX = 800;  // Diperbesar untuk menangkap raw value tinggi
int G_MIN = 18;   
int G_MAX = 800;  
int B_MIN = 12;   
int B_MAX = 800;  

// Faktor Pengali (Green Booster)
const float FACTOR_R = 0.70; 
const float FACTOR_G = 1.35; 
const float FACTOR_B = 1.05; 

// ===== WiFi & Server =====
const char* ssid = "NASA";
const char* password = "outerspace";
const char* serverUrl = "https://kurokana.alwaysdata.net";  // HTTPS
const char* provisionToken = "iZzBvMeX593wOOV3FMOlexj4swQ0feqAE49E";

// ===== EEPROM Credentials =====
struct Credentials {
  char magic[4];
  char deviceId[64];  // DIPERBESAR untuk user-scoped ID
  char apiKey[48];
  bool isValid;
};
Credentials creds;

// ===== Timing =====
unsigned long lastSensorRead = 0;
unsigned long lastCommandCheck = 0;
const unsigned long SENSOR_INTERVAL = 30000;  // 30 seconds
const unsigned long COMMAND_INTERVAL = 10000; // 10 seconds

// ===== HTTP Timeout Settings =====
const int HTTP_TIMEOUT = 10000; // 10 detik timeout

// ===== Helper Functions =====

// Fungsi Map untuk Float
float mapFloat(float x, float in_min, float in_max, float out_min, float out_max) {
  if (in_max == in_min) return out_min; // Prevent division by zero
  float result = (x - in_min) * (out_max - out_min) / (in_max - in_min) + out_min;
  return result;
}

void saveCredentials() {
  EEPROM.begin(512);
  creds.magic[0] = 'S'; creds.magic[1] = 'P'; creds.magic[2] = 'L'; creds.magic[3] = 'T';
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

  if (creds.magic[0]=='S' && creds.magic[1]=='P' && creds.magic[2]=='L' && creds.magic[3]=='T' && creds.isValid) {
    Serial.println("✅ Credentials loaded from EEPROM");
    Serial.printf("   Device ID: %s\n", creds.deviceId);
  } else {
    creds.isValid = false;
    Serial.println("⚠ No valid credentials found");
  }
}

// ===== Provisioning dengan Error Handling =====
bool doProvisioning() {
  Serial.println("\n🔧 Starting provisioning...");
  
  WiFiClientSecure client;
  client.setInsecure(); 
  client.setTimeout(HTTP_TIMEOUT);
  
  HTTPClient http;
  http.setTimeout(HTTP_TIMEOUT);
  
  String url = String(serverUrl) + "/api/provision/claim";
  Serial.println("📡 Provisioning URL: " + url);

  if (!http.begin(client, url)) {
    Serial.println("❌ HTTP.begin() failed!");
    return false;
  }
  
  http.addHeader("Content-Type", "application/json");

  DynamicJsonDocument doc(256);
  doc["token"] = provisionToken;
  doc["device_id"] = String(ESP.getChipId());
  doc["name"] = "LABTD";
  doc["location"] = "Home";

  String body;
  serializeJson(doc, body);
  Serial.println("📤 Request: " + body);
  
  int code = http.POST(body);
  Serial.printf("📥 Response code: %d\n", code);

  if (code == 200) {
    String response = http.getString();
    Serial.println("📥 Response: " + response);
    
    DynamicJsonDocument resDoc(512);
    DeserializationError error = deserializeJson(resDoc, response);
    
    if (error) {
      Serial.println("❌ JSON parse error: " + String(error.c_str()));
      http.end();
      return false;
    }

    String id = resDoc["device_id"].as<String>();
    String key = resDoc["api_key"].as<String>();

    if (id.length() == 0 || key.length() == 0) {
      Serial.println("❌ Empty device_id or api_key!");
      http.end();
      return false;
    }

    id.toCharArray(creds.deviceId, 64);
    key.toCharArray(creds.apiKey, 48);
    saveCredentials();
    http.end();
    return true;
  } else if (code > 0) {
    String response = http.getString();
    Serial.printf("❌ Provisioning failed (code %d): %s\n", code, response.c_str());
  } else {
    Serial.printf("❌ Connection failed (code %d)\n", code);
    Serial.println("Possible causes:");
    Serial.println("  - Server unreachable");
    Serial.println("  - SSL/TLS handshake failed");
    Serial.println("  - DNS resolution failed");
  }
  
  http.end();
  return false;
}

// ===== Baca Sensor =====
float readSoilMoisture() {
  int raw = analogRead(SOIL_PIN);
  float percent = map(raw, 1023, 300, 0, 100);
  percent = constrain(percent, 0, 100);
  return percent;
}

// ===== PERBAIKAN LOGIKA WARNA =====
int getRawFrequency(int s2State, int s3State) {
  digitalWrite(S2, s2State);
  digitalWrite(S3, s3State);
  delay(100); // Tambah delay untuk stabilisasi
  return pulseIn(OUT, LOW, 100000); // Timeout 100ms
}

void readColorRGB(float &r, float &g, float &b) {
  // 1. Baca Raw Frequency
  int rawR = getRawFrequency(LOW, LOW);
  int rawG = getRawFrequency(HIGH, HIGH);
  int rawB = getRawFrequency(LOW, HIGH);

  Serial.printf("[RAW] R:%d G:%d B:%d ", rawR, rawG, rawB);

  // 2. AUTO-CALIBRATION: Update min/max jika menemukan nilai baru
  // Ini membantu sensor beradaptasi dengan kondisi pencahayaan
  if (rawR > 0) {
    if (rawR < R_MIN) R_MIN = rawR;
    if (rawR > R_MAX) R_MAX = rawR;
  }
  if (rawG > 0) {
    if (rawG < G_MIN) G_MIN = rawG;
    if (rawG > G_MAX) G_MAX = rawG;
  }
  if (rawB > 0) {
    if (rawB < B_MIN) B_MIN = rawB;
    if (rawB > B_MAX) B_MAX = rawB;
  }

  // 3. MAPPING DENGAN SAFETY CHECK
  float valR = 0, valG = 0, valB = 0;
  
  if (rawR > 0) {
    valR = mapFloat(rawR, R_MIN, R_MAX, 255, 0);
    valR = constrain(valR, 0, 255);
  }
  
  if (rawG > 0) {
    valG = mapFloat(rawG, G_MIN, G_MAX, 255, 0);
    valG = constrain(valG, 0, 255);
  }
  
  if (rawB > 0) {
    valB = mapFloat(rawB, B_MIN, B_MAX, 255, 0);
    valB = constrain(valB, 0, 255);
  }

  Serial.printf("[MAPPED] R:%.0f G:%.0f B:%.0f ", valR, valG, valB);

  // 4. TERAPKAN GREEN BOOSTER
  valR = valR * FACTOR_R;
  valG = valG * FACTOR_G;
  valB = valB * FACTOR_B;

  // 5. LEAF DETECTION LOGIC
  if (valG > 50) {
    if (valG > valB && valG > (valR * 0.8)) {
      Serial.print(" -> (🍃 Leaf Detected)");
      valG += 40;
      valR -= 20;
    }
  }

  // 6. Final Output
  r = constrain(valR, 0, 255);
  g = constrain(valG, 0, 255);
  b = constrain(valB, 0, 255);

  Serial.printf(" -> [FINAL] R:%.0f G:%.0f B:%.0f\n", r, g, b);
  Serial.printf("[CALIBRATION] R:%d-%d G:%d-%d B:%d-%d\n", 
                R_MIN, R_MAX, G_MIN, G_MAX, B_MIN, B_MAX);
}

// ===== Kirim Data dengan Better Error Handling =====
bool sendSensorData(float soil, float temp, float hum, float r, float g, float b) {
  WiFiClientSecure client;
  client.setInsecure(); 
  client.setTimeout(HTTP_TIMEOUT);
  
  HTTPClient http;
  http.setTimeout(HTTP_TIMEOUT);
  
  String url = String(serverUrl) + "/api/ingest";

  if (!http.begin(client, url)) {
    Serial.println("❌ HTTP.begin() failed for ingest!");
    return false;
  }

  http.addHeader("Content-Type", "application/json");
  http.addHeader("X-Device-Id", String(creds.deviceId));
  http.addHeader("X-Api-Key", String(creds.apiKey));

  DynamicJsonDocument doc(512);
  JsonArray readings = doc.createNestedArray("readings");

  JsonObject j1 = readings.createNestedObject(); j1["type"] = "soil"; j1["value"] = soil;
  JsonObject j2 = readings.createNestedObject(); j2["type"] = "temp"; j2["value"] = temp;
  JsonObject j3 = readings.createNestedObject(); j3["type"] = "hum";  j3["value"] = hum;
  JsonObject j4 = readings.createNestedObject(); j4["type"] = "color_r"; j4["value"] = r;
  JsonObject j5 = readings.createNestedObject(); j5["type"] = "color_g"; j5["value"] = g;
  JsonObject j6 = readings.createNestedObject(); j6["type"] = "color_b"; j6["value"] = b;

  String body;
  serializeJson(doc, body);
  Serial.println("📤 Sending: " + body);

  int code = http.POST(body);
  
  if (code == 200) {
    Serial.println("✅ Data sent successfully!");
    http.end();
    return true;
  } else if (code > 0) {
    String response = http.getString();
    Serial.printf("❌ HTTP failed (code %d): %s\n", code, response.c_str());
  } else {
    Serial.printf("❌ Connection failed (code %d)\n", code);
    Serial.println("  Check: WiFi signal, server status, SSL/TLS");
  }
  
  http.end();
  return false;
}

// ===== COMMAND CONTROL =====
void executeWaterOn(int durationSec) {
  Serial.printf("💧 Water ON for %d seconds\n", durationSec);
  durationSec = constrain(durationSec, 1, 60);
  
  digitalWrite(RELAY_PIN, LOW); 
  delay(durationSec * 1000);
  digitalWrite(RELAY_PIN, HIGH); 
  Serial.println("✅ Water OFF");
}

void checkCommands() {
  WiFiClientSecure client;
  client.setInsecure(); 
  client.setTimeout(HTTP_TIMEOUT);
  
  HTTPClient http;
  http.setTimeout(HTTP_TIMEOUT);
  
  String url = String(serverUrl) + "/api/commands/next";

  if (!http.begin(client, url)) {
    Serial.println("❌ HTTP.begin() failed for commands!");
    return;
  }

  http.addHeader("X-Device-Id", String(creds.deviceId));
  http.addHeader("X-Api-Key", String(creds.apiKey));

  int code = http.GET();
  
  if (code == 200) {
    String response = http.getString();
    
    DynamicJsonDocument doc(512);
    DeserializationError error = deserializeJson(doc, response);
    
    if (error) {
      Serial.println("❌ JSON parse error: " + String(error.c_str()));
      http.end();
      return;
    }
    
    if (doc["command"].isNull()) {
      // No pending commands (normal)
      http.end();
      return;
    }

    int cmdId = doc["id"];
    String command = doc["command"].as<String>();
    JsonObject params = doc["params"];

    Serial.printf("📥 Command: %s (ID: %d)\n", command.c_str(), cmdId);

    if (command == "water_on") {
      // ✅ FIX: Gunakan .as<int>() dengan default value yang benar
      int duration = params["duration_sec"].as<int>();
      if (duration == 0) duration = 5; // Default 5 detik jika tidak ada parameter
      
      // Debug: Print parameter JSON
      Serial.print("📋 Params received: ");
      serializeJson(params, Serial);
      Serial.println();
      
      Serial.printf("💧 Executing water_on for %d seconds...\n", duration);
      executeWaterOn(duration);
      
      // Send ACK
      http.end();
      HTTPClient httpAck;
      String ackUrl = String(serverUrl) + "/api/commands/" + String(cmdId) + "/ack";
      
      if (httpAck.begin(client, ackUrl)) {
        httpAck.addHeader("X-Device-Id", String(creds.deviceId));
        httpAck.addHeader("X-Api-Key", String(creds.apiKey));
        int ackCode = httpAck.POST("");
        Serial.printf("✅ Command ACK sent (code %d)\n", ackCode);
        httpAck.end();
      }
    }
  } else if (code == 404) {
    // No commands (normal)
  } else if (code > 0) {
    Serial.printf("⚠ Command check: HTTP %d\n", code);
  } else {
    // Connection error - don't spam logs
    Serial.printf("⚠ Command check failed (code %d)\n", code);
  }
  
  http.end();
}

// ===== Setup =====
void setup() {
  Serial.begin(115200);
  delay(100);
  Serial.println("\n╔════════════════════════════════════════╗");
  Serial.println("║  SmartPlants IoT - Auto Mode v2.0     ║");
  Serial.println("╚════════════════════════════════════════╝");

  // Setup pins
  pinMode(RELAY_PIN, OUTPUT);
  digitalWrite(RELAY_PIN, HIGH); 

  pinMode(S0, OUTPUT); pinMode(S1, OUTPUT);
  pinMode(S2, OUTPUT); pinMode(S3, OUTPUT);
  pinMode(OUT, INPUT);

  // Scaling 20%
  digitalWrite(S0, HIGH);
  digitalWrite(S1, LOW);

  dht.begin();

  // WiFi connection
  Serial.println("\n📡 Connecting to WiFi: " + String(ssid));
  WiFi.mode(WIFI_STA);
  WiFi.begin(ssid, password);
  
  int attempt = 0;
  while (WiFi.status() != WL_CONNECTED && attempt < 30) {
    delay(500); 
    Serial.print(".");
    attempt++;
  }

  if (WiFi.status() != WL_CONNECTED) {
    Serial.println("\n❌ WiFi Failed. Restarting in 10s...");
    delay(10000);
    ESP.restart();
  }
  
  Serial.println("\n✅ WiFi Connected!");
  Serial.print("   IP Address: ");
  Serial.println(WiFi.localIP());
  Serial.print("   Signal: ");
  Serial.print(WiFi.RSSI());
  Serial.println(" dBm");

  // Provisioning
  loadCredentials();
  if (!creds.isValid) {
    Serial.println("\n⚙ Starting provisioning...");
    
    int provAttempts = 0;
    while (!doProvisioning() && provAttempts < 3) {
      provAttempts++;
      Serial.printf("⚠ Retry provisioning (%d/3)...\n", provAttempts);
      delay(5000);
    }
    
    if (!creds.isValid) {
      Serial.println("❌ Provisioning failed after 3 attempts. Restarting...");
      delay(10000);
      ESP.restart();
    }
  }

  Serial.println("\n✅ Device ready!");
  Serial.println("🔁 Automation mode enabled");
  Serial.println("━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n");
}

// ===== Loop =====
void loop() {
  // Check WiFi connection
  if (WiFi.status() != WL_CONNECTED) {
    Serial.println("⚠ WiFi disconnected! Reconnecting...");
    WiFi.reconnect();
    delay(5000);
    return;
  }

  unsigned long now = millis();

  // Read sensors every 30 seconds
  if (now - lastSensorRead >= SENSOR_INTERVAL) {
    lastSensorRead = now;
    
    Serial.println("\n━━━━━━━ SENSOR READ ━━━━━━━");
    
    float soil = readSoilMoisture();
    float temp = dht.readTemperature();
    float hum = dht.readHumidity();
    float r, g, b;
    readColorRGB(r, g, b);

    if (isnan(temp) || isnan(hum)) {
      Serial.println("⚠ DHT22 read failed, using defaults");
      temp = 0;
      hum = 0;
    }

    Serial.println("\n📊 Sensor Summary:");
    Serial.printf("   Soil: %.2f%%\n", soil);
    Serial.printf("   Temp: %.2f°C\n", temp);
    Serial.printf("   Hum : %.2f%%\n", hum);
    Serial.printf("   RGB : (%.0f, %.0f, %.0f)\n", r, g, b);

    sendSensorData(soil, temp, hum, r, g, b);
  }

  // Check commands every 10 seconds
  if (now - lastCommandCheck >= COMMAND_INTERVAL) {
    lastCommandCheck = now;
    checkCommands();
  }

  delay(100); 
}
