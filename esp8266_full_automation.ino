/*
 * SmartPlants ESP8266 - Full Automation Version
 * Features:
 * - Real sensors: DHT22, Soil Moisture, TCS3200
 * - Relay control for water pump
 * - Polling commands from server
 * - Auto-execute water_on commands
 */

#include <ESP8266WiFi.h>
#include <ESP8266HTTPClient.h>
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

// ===== WiFi & Server =====
const char* ssid = "pedal";
const char* password = "12345689";
const char* serverUrl = "http://192.168.137.1:8000";
const char* provisionToken = "zsDX4SgsW80UHzgJONXVn7m2gpPT347bDmoL";

// ===== EEPROM Credentials =====
struct Credentials {
  char magic[4];
  char deviceId[32];
  char apiKey[48];
  bool isValid;
};
Credentials creds;

// ===== Timing =====
unsigned long lastSensorRead = 0;
unsigned long lastCommandCheck = 0;
const unsigned long SENSOR_INTERVAL = 30000;  // 30 seconds
const unsigned long COMMAND_INTERVAL = 10000; // 10 seconds

// ===== Helper EEPROM =====
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

  http.begin(client, url);
  http.addHeader("Content-Type", "application/json");

  DynamicJsonDocument doc(256);
  doc["token"] = provisionToken;
  doc["device_id"] = String(ESP.getChipId());
  doc["name"] = "ESP8266 SmartPlant";
  doc["location"] = "Home";

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

    String id = resDoc["device_id"].as<String>();
    String key = resDoc["api_key"].as<String>();

    id.toCharArray(creds.deviceId, 32);
    key.toCharArray(creds.apiKey, 48);
    saveCredentials();
    http.end();
    return true;
  } else {
    Serial.printf("❌ Provisioning failed (code %d)\n", code);
    Serial.println("Response: " + http.getString());
    http.end();
    return false;
  }
}

// ===== Baca Sensor =====
float readSoilMoisture() {
  int raw = analogRead(SOIL_PIN);
  float percent = map(raw, 1023, 300, 0, 100);
  if (percent < 0) percent = 0;
  if (percent > 100) percent = 100;
  return percent;
}

void setColorMode(int s2Val, int s3Val) {
  digitalWrite(S2, s2Val);
  digitalWrite(S3, s3Val);
}

int readColorFrequency() {
  return pulseIn(OUT, LOW);
}

void readColorRGB(float &r, float &g, float &b) {
  setColorMode(LOW, LOW);
  int red = readColorFrequency();
  delay(100);
  setColorMode(HIGH, HIGH);
  int green = readColorFrequency();
  delay(100);
  setColorMode(LOW, HIGH);
  int blue = readColorFrequency();

  r = 255.0 * (1.0 - (float)red / 1000.0);
  g = 255.0 * (1.0 - (float)green / 1000.0);
  b = 255.0 * (1.0 - (float)blue / 1000.0);
}

// ===== Kirim Data =====
bool sendSensorData(float soil, float temp, float hum, float r, float g, float b) {
  WiFiClient client;
  HTTPClient http;
  String url = String(serverUrl) + "/api/ingest";

  http.begin(client, url);
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
  } else {
    Serial.printf("❌ HTTP failed (code %d)\n", code);
    http.end();
    return false;
  }
}

// ===== COMMAND CONTROL =====
void executeWaterOn(int durationSec) {
  Serial.printf("💧 Water ON for %d seconds\n", durationSec);
  
  // Safety: Max 60 seconds
  if (durationSec > 60) durationSec = 60;
  if (durationSec < 1) durationSec = 1;
  
  digitalWrite(RELAY_PIN, LOW);  // Relay aktif LOW (normally HIGH)
  delay(durationSec * 1000);
  digitalWrite(RELAY_PIN, HIGH); // Matikan relay
  
  Serial.println("✅ Water OFF");
}

void checkCommands() {
  WiFiClient client;
  HTTPClient http;
  String url = String(serverUrl) + "/api/commands/next";

  http.begin(client, url);
  http.addHeader("X-Device-Id", String(creds.deviceId));
  http.addHeader("X-Api-Key", String(creds.apiKey));

  int code = http.GET();
  
  if (code == 200) {
    String response = http.getString();
    DynamicJsonDocument doc(512);
    
    if (!deserializeJson(doc, response)) {
      if (doc["command"].isNull()) {
        // No pending commands
        http.end();
        return;
      }

      int cmdId = doc["id"];
      String command = doc["command"].as<String>();
      JsonObject params = doc["params"];

      Serial.printf("📥 Command received: %s (ID: %d)\n", command.c_str(), cmdId);

      if (command == "water_on") {
        int duration = params["duration_sec"] | 5; // Default 5 detik
        executeWaterOn(duration);
        
        // Send ACK
        http.end();
        HTTPClient httpAck;
        String ackUrl = String(serverUrl) + "/api/commands/" + String(cmdId) + "/ack";
        httpAck.begin(client, ackUrl);
        httpAck.addHeader("X-Device-Id", String(creds.deviceId));
        httpAck.addHeader("X-Api-Key", String(creds.apiKey));
        httpAck.POST("");
        httpAck.end();
        
        Serial.println("✅ Command ACK sent");
      }
    }
  }
  
  http.end();
}

// ===== Setup =====
void setup() {
  Serial.begin(115200);
  delay(100);
  Serial.println("\n=== SmartPlants (FULL AUTOMATION MODE) ===");

  // Setup pins
  pinMode(RELAY_PIN, OUTPUT);
  digitalWrite(RELAY_PIN, HIGH); // Relay OFF (aktif LOW)

  pinMode(S0, OUTPUT); pinMode(S1, OUTPUT);
  pinMode(S2, OUTPUT); pinMode(S3, OUTPUT);
  pinMode(OUT, INPUT);

  digitalWrite(S0, HIGH);
  digitalWrite(S1, LOW);

  dht.begin();

  // WiFi connection
  WiFi.begin(ssid, password);
  Serial.print("Connecting to WiFi");
  int attempt = 0;
  while (WiFi.status() != WL_CONNECTED && attempt < 20) {
    delay(500); Serial.print(".");
    attempt++;
  }

  if (WiFi.status() != WL_CONNECTED) {
    Serial.println("\n❌ WiFi Failed. Restarting...");
    ESP.restart();
  }
  Serial.println("\n✅ WiFi Connected!");
  Serial.println(WiFi.localIP());

  // Provisioning
  loadCredentials();
  if (!creds.isValid) {
    Serial.println("⚙️ No credentials found. Provisioning...");
    if (!doProvisioning()) {
      Serial.println("❌ Provisioning failed. Restarting...");
      delay(5000);
      ESP.restart();
    }
  }

  Serial.println("✅ Device ready!");
  Serial.println("🔁 Automation mode enabled");
}

// ===== Loop =====
void loop() {
  unsigned long now = millis();

  // Read sensors every 30 seconds
  if (now - lastSensorRead >= SENSOR_INTERVAL) {
    lastSensorRead = now;
    
    float soil = readSoilMoisture();
    float temp = dht.readTemperature();
    float hum = dht.readHumidity();
    float r, g, b;
    readColorRGB(r, g, b);

    if (isnan(temp) || isnan(hum)) {
      Serial.println("⚠️ DHT22 read failed, skipping...");
      return;
    }

    Serial.println("\n📊 Sensor Data:");
    Serial.printf("Soil: %.2f%%\n", soil);
    Serial.printf("Temp: %.2f°C\n", temp);
    Serial.printf("Hum : %.2f%%\n", hum);
    Serial.printf("RGB : (%.0f, %.0f, %.0f)\n", r, g, b);

    sendSensorData(soil, temp, hum, r, g, b);
  }

  // Check for commands every 10 seconds
  if (now - lastCommandCheck >= COMMAND_INTERVAL) {
    lastCommandCheck = now;
    checkCommands();
  }

  delay(100); // Small delay to prevent watchdog reset
}
