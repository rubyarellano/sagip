/**
 * EcoLayon Emergency SOS Gateway Firmware
 * Target Microcontroller: ESP32 DevKitC / standard ESP32
 * 
 * Required Libraries (Install via Arduino Library Manager):
 * - SPI (Built-in)
 * - WiFi (Built-in)
 * - HTTPClient (Built-in)
 * - Preferences (Built-in)
 * - LoRa (by Sandeep Mistry)
 * - WiFiManager (by tzapu)
 * - ArduinoJson (by Benoit Blanchon)
 */

#include <SPI.h>
#include <WiFi.h>
#include <HTTPClient.h>
#include <Preferences.h>
#include <LoRa.h>
#include <WiFiManager.h>
#include <ArduinoJson.h>

// ==========================================
// PIN CONFIGURATION (ESP32 DevKit standard SPI)
// ==========================================
#define LORA_SCK     18
#define LORA_MISO    19
#define LORA_MOSI    23
#define LORA_CS      5
#define LORA_RST     14
#define LORA_DIO0    2

// ==========================================
// DYNAMIC CONFIGURATION & STORAGE
// ==========================================
Preferences preferences;
char backend_api_url[128] = "http://192.168.1.100/api/emergencies/report"; // Default fallback
bool shouldSaveConfig = false;

// ==========================================
// EMERGENCY PACKET STRUCTURE (18 Bytes)
// ==========================================
struct __attribute__((__packed__)) EmergencyPacket {
  uint32_t deviceId;      // 4 Bytes
  float latitude;         // 4 Bytes
  float longitude;        // 4 Bytes
  float gForce;           // 4 Bytes
  uint8_t bpm;            // 1 Byte
  uint8_t spo2;           // 1 Byte
  uint8_t severity;       // 1 Byte: 1=Low, 2=Medium, 3=High/Critical
  uint8_t alertType;      // 1 Byte: 0=Auto-Fall, 1=Manual Medical, 2=Manual Fire, 3=Manual Landslide
};

// Callback notifying us of the need to save new configurations from portal
void saveConfigCallback() {
  Serial.println("[INFO] New configuration received. Will save to flash.");
  shouldSaveConfig = true;
}

// ==========================================
// SYSTEM SETUP
// ==========================================
void setup() {
  Serial.begin(115200);
  delay(1000);
  Serial.println("--- EcoLayon Emergency SOS Gateway Booting ---");

  // Load configured API URL from ESP32 Preferences flash storage
  preferences.begin("gateway", false);
  String savedUrl = preferences.getString("api_url", "http://192.168.1.100/api/emergencies/report");
  savedUrl.toCharArray(backend_api_url, 128);
  Serial.print("[OK] Loaded API URL from Preferences: ");
  Serial.println(backend_api_url);

  // WiFiManager Setup
  WiFiManager wm;
  
  // Set configuration save callback
  wm.setSaveConfigCallback(saveConfigCallback);

  // Create custom input field in config portal for API URL
  WiFiManagerParameter custom_backend_api_url("api_url", "Backend API URL", backend_api_url, 128);
  wm.addParameter(&custom_backend_api_url);

  // Automatically connect using saved credentials.
  // If it fails, start an Access Point named "EcoLayon_Gateway_AP" (ip 192.168.4.1) for captive portal setup.
  Serial.println("[INFO] Checking Wi-Fi credentials...");
  bool res = wm.autoConnect("EcoLayon_Gateway_AP");

  if (!res) {
    Serial.println("[CRITICAL] Failed to connect to Wi-Fi or portal hit timeout. Restarting...");
    delay(3000);
    ESP.restart();
  }

  Serial.println("[OK] Successfully connected to Wi-Fi!");
  Serial.print("Local IP Address: ");
  Serial.println(WiFi.localIP());

  // Save the custom parameter if updated via the captive portal
  if (shouldSaveConfig) {
    String newUrl = String(custom_backend_api_url.getValue());
    newUrl.trim();
    if (newUrl.length() > 0) {
      newUrl.toCharArray(backend_api_url, 128);
      preferences.putString("api_url", newUrl);
      Serial.print("[OK] Saved new API URL to Preferences: ");
      Serial.println(backend_api_url);
    }
  }

  // Close preferences namespace
  preferences.end();

  // Initialize SPI & LoRa (RFM95W)
  SPI.begin(LORA_SCK, LORA_MISO, LORA_MOSI, LORA_CS);
  LoRa.setPins(LORA_CS, LORA_RST, LORA_DIO0);

  if (LoRa.begin(915E6)) { // Set frequency matching the wristband standard (915MHz)
    LoRa.setSpreadingFactor(12);
    LoRa.setSignalBandwidth(125E3);
    LoRa.setCodingRate4(8);
    Serial.println("[OK] RFM95W LoRa Transceiver Initialized @ 915MHz.");
  } else {
    Serial.println("[CRITICAL ERROR] RFM95W LoRa Transceiver failed to start.");
    while (1) {
      delay(1000);
    }
  }

  Serial.println("[OK] Gateway is active, listening for emergency packet broadcasts...");
}

// ==========================================
// MAIN RECEPTION LOOP
// ==========================================
void loop() {
  // Check Wi-Fi Connection status periodically
  if (WiFi.status() != WL_CONNECTED) {
    Serial.println("[WARN] Wi-Fi lost! Attempting background reconnection...");
    WiFi.reconnect();
  }

  // Parse incoming packet
  int packetSize = LoRa.parsePacket();
  
  if (packetSize > 0) {
    Serial.print(">>> Received LoRa Packet size: ");
    Serial.print(packetSize);
    Serial.print(" bytes | RSSI: ");
    Serial.print(LoRa.packetRssi());
    Serial.print(" dBm | SNR: ");
    Serial.println(LoRa.packetSnr());

    // Validate size matches EmergencyPacket struct
    if (packetSize == sizeof(EmergencyPacket)) {
      EmergencyPacket packet;
      
      // Read bytes into struct
      LoRa.readBytes((uint8_t*)&packet, sizeof(EmergencyPacket));

      // Process and log packet details
      logPacket(packet);

      // Forward to backend web server
      forwardToServer(packet);
    } else {
      Serial.println("[ERROR] Packet size mismatch. Ignoring corrupted payload.");
    }
  }

  delay(10);
}

// ==========================================
// WEB API FORWARDING FUNCTION
// ==========================================

void forwardToServer(EmergencyPacket packet) {
  if (WiFi.status() != WL_CONNECTED) {
    Serial.println("[ERROR] No Wi-Fi connection. Cannot forward emergency payload to server.");
    return;
  }

  // Create JSON Payload
  StaticJsonDocument<256> doc;
  doc["device_id"] = packet.deviceId;
  doc["latitude"] = packet.latitude;
  doc["longitude"] = packet.longitude;
  doc["g_force"] = packet.gForce;
  doc["bpm"] = packet.bpm;
  doc["spo2"] = packet.spo2;

  // Translate severity enum to API strings
  switch (packet.severity) {
    case 1:
      doc["severity"] = "low";
      break;
    case 2:
      doc["severity"] = "medium";
      break;
    case 3:
      doc["severity"] = "critical";
      break;
    default:
      doc["severity"] = "unknown";
      break;
  }

  // Translate alert type enum to API strings
  switch (packet.alertType) {
    case 0:
      doc["alert_type"] = "auto_fall";
      break;
    case 1:
      doc["alert_type"] = "manual_medical";
      break;
    case 2:
      doc["alert_type"] = "manual_fire";
      break;
    case 3:
      doc["alert_type"] = "manual_landslide";
      break;
    default:
      doc["alert_type"] = "general_sos";
      break;
  }

  String jsonString;
  serializeJson(doc, jsonString);

  // Send HTTP POST request
  HTTPClient http;
  Serial.print("Forwarding payload to server: ");
  Serial.println(backend_api_url);

  http.begin(backend_api_url);
  http.addHeader("Content-Type", "application/json");

  int httpResponseCode = http.POST(jsonString);

  if (httpResponseCode > 0) {
    Serial.print("Server HTTP response code: ");
    Serial.println(httpResponseCode);
    String responseBody = http.getString();
    Serial.print("Response: ");
    Serial.println(responseBody);
  } else {
    Serial.print("Error sending POST request: ");
    Serial.println(http.errorToString(httpResponseCode).c_str());
  }

  http.end();
}

// ==========================================
// UTILITY PRINT LOGS
// ==========================================
void logPacket(EmergencyPacket packet) {
  Serial.println("=========================================");
  Serial.print(" EMERGENCY ALERT DECODED: Device #");
  Serial.println(packet.deviceId);
  Serial.println("-----------------------------------------");
  Serial.print("Location:  "); Serial.print(packet.latitude, 6);
  Serial.print(", "); Serial.println(packet.longitude, 6);
  Serial.print("Impact Force: "); Serial.print(packet.gForce); Serial.println(" g");
  Serial.print("Biometrics:   "); Serial.print(packet.bpm); Serial.print(" BPM | ");
  Serial.print(packet.spo2); Serial.println("% SpO2");
  
  Serial.print("Severity:     ");
  if (packet.severity == 1) Serial.println("LOW (Warning)");
  else if (packet.severity == 2) Serial.println("MEDIUM (Confirmed Fall)");
  else if (packet.severity == 3) Serial.println("HIGH / CRITICAL");
  
  Serial.print("Alert Type:   ");
  if (packet.alertType == 0) Serial.println("Automatic Fall Detection");
  else if (packet.alertType == 1) Serial.println("Manual SOS Button - Medical");
  else if (packet.alertType == 2) Serial.println("Manual SOS Button - Fire");
  else if (packet.alertType == 3) Serial.println("Manual SOS Button - Landslide");
  
  Serial.println("=========================================");
}
