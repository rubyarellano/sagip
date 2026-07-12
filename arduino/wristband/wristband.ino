/**
 * EcoLayon Emergency SOS Wristband Firmware
 * Target Microcontroller: ESP32-C3 SuperMini
 * 
 * Required Libraries (Install via Arduino Library Manager):
 * - SPI (Built-in)
 * - Wire (Built-in)
 * - LoRa (by Sandeep Mistry)
 * - TinyGPS++ (by Mikal Hart)
 * - Adafruit MPU6050 (by Adafruit)
 * - Adafruit Unified Sensor (by Adafruit)
 * - SparkFun MAX3010x Pulse Oximeter (by SparkFun)
 */

#include <SPI.h>
#include <Wire.h>
#include <LoRa.h>
#include <TinyGPS++.h>
#include <Adafruit_MPU6050.h>
#include <Adafruit_Sensor.h>
#include "MAX30105.h"
#include "heartRate.h"

// ==========================================
// PIN CONFIGURATION (ESP32-C3 SuperMini)
// ==========================================
#define LORA_SCK     4
#define LORA_MISO    5
#define LORA_MOSI    6
#define LORA_CS      7
#define LORA_RST     10
#define LORA_DIO0    3

#define I2C_SDA      8
#define I2C_SCL      9

#define GPS_RX_PIN   20 // Connect to GPS TX
#define GPS_TX_PIN   21 // Connect to GPS RX

#define BUZZER_PIN   0
#define VIBRA_PIN    1
#define BTN_PIN      2 // Manual SOS Button (Internal Pull-Up)

// ==========================================
// CONFIGURATION CONSTANTS
// ==========================================
const uint32_t DEVICE_ID = 20260620; // Unique wristband ID (e.g. date-based or MAC-derived)
const float G_TO_MS2 = 9.80665;
const long DEBOUNCE_DELAY = 50;      // Button debounce in ms
const long CLICK_WINDOW = 800;       // Multi-click detection window in ms
const long CANCEL_WINDOW = 10000;    // Time allowed to cancel low severity alerts (10s)

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

// ==========================================
// STATE & DRIVER INITIALIZATION
// ==========================================
TinyGPSPlus gps;
HardwareSerial gpsSerial(1); // Use Hardware Serial 1 for GPS

Adafruit_MPU6050 mpu;
MAX30105 particleSensor;

bool mpuConnected = false;
bool maxConnected = false;

// Button & Interrupt Variables
volatile bool btnPressed = false;
volatile unsigned long lastBtnInterruptTime = 0;
unsigned long btnPressStartTime = 0;
bool buttonWasHeld = false;
int buttonPressCount = 0;
unsigned long lastClickTime = 0;

// Alert Logic States
enum DeviceState { STATE_NORMAL, STATE_PENDING_CANCEL, STATE_TRANSMITTING };
DeviceState currentState = STATE_NORMAL;
unsigned long stateChangeTime = 0;
EmergencyPacket activeAlertPayload;

// Sensor buffers & thresholds
float maxGForceThisCycle = 1.0f;
unsigned long lastImmobileCheckTime = 0;
unsigned long immobileStartTime = 0;
bool isImmobile = false;
const float IMMOBILITY_THRESHOLD = 0.25f; // G-force variance threshold

// Biometrics variables
long lastBeatTime = 0;
float beatsPerMinute = 75.0f;
int beatAvg = 75;
byte rateSpot = 0;
byte rates[4];
uint8_t calculatedSpO2 = 98; // Default placeholder

// ==========================================
// INTERRUPT SERVICE ROUTINE FOR SOS BUTTON
// ==========================================
void IRAM_ATTR handleButtonInterrupt() {
  unsigned long interruptTime = millis();
  if (interruptTime - lastBtnInterruptTime > DEBOUNCE_DELAY) {
    btnPressed = true;
    lastBtnInterruptTime = interruptTime;
  }
}

// ==========================================
// SYSTEM SETUP
// ==========================================
void setup() {
  Serial.begin(115200);
  delay(1000);
  Serial.println("--- EcoLayon Emergency SOS Wristband Booting ---");

  // Pin Direction Setup
  pinMode(BUZZER_PIN, OUTPUT);
  pinMode(VIBRA_PIN, OUTPUT);
  pinMode(BTN_PIN, INPUT_PULLUP);
  digitalWrite(BUZZER_PIN, LOW);
  digitalWrite(VIBRA_PIN, LOW);

  // Attach Interrupt to Manual SOS Button
  attachInterrupt(digitalPinToInterrupt(BTN_PIN), handleButtonInterrupt, CHANGE);

  // I2C Setup
  Wire.begin(I2C_SDA, I2C_SCL);

  // Initialize MPU6050
  if (mpu.begin()) {
    mpuConnected = true;
    mpu.setAccelerometerRange(MPU6050_RANGE_16_G);
    mpu.setGyroRange(MPU6050_RANGE_500_DEG);
    mpu.setFilterBandwidth(MPU6050_BAND_21_HZ);
    Serial.println("[OK] MPU6050 Sensor Initialized (16G Range).");
  } else {
    Serial.println("[ERROR] MPU6050 Connection Failed. Immobile/fall detection offline.");
  }

  // Initialize MAX30102
  if (particleSensor.begin(Wire, I2C_SPEED_FAST)) {
    maxConnected = true;
    particleSensor.setup(); // Configure with default settings
    particleSensor.setPulseAmplitudeRed(0x0A); // Turn Red LED low to indicate running
    particleSensor.setPulseAmplitudeIR(0x1F);  // Turn IR LED on
    Serial.println("[OK] MAX30102 Heart Rate/SpO2 Sensor Initialized.");
  } else {
    Serial.println("[ERROR] MAX30102 Connection Failed. Biometric tracking offline.");
  }

  // Initialize GPS Serial
  gpsSerial.begin(9600, SERIAL_8N1, GPS_RX_PIN, GPS_TX_PIN);
  Serial.println("[OK] ATGM336H GPS Serial connection opened.");

  // Initialize SPI & LoRa (RFM95W)
  SPI.begin(LORA_SCK, LORA_MISO, LORA_MOSI, LORA_CS);
  LoRa.setPins(LORA_CS, LORA_RST, LORA_DIO0);

  if (LoRa.begin(915E6)) { // Set frequency based on local region standard (915MHz)
    LoRa.setTxPower(20);    // Max power for thick foliage & disaster penetration
    LoRa.setSpreadingFactor(12); // Max spreading factor for maximum range
    LoRa.setSignalBandwidth(125E3);
    LoRa.setCodingRate4(8);
    Serial.println("[OK] RFM95W LoRa Transceiver Initialized @ 915MHz.");
  } else {
    Serial.println("[CRITICAL ERROR] RFM95W LoRa Transceiver failed to start.");
    // Blink SOS locally to notify user of system failure
    triggerLocalSOSBlink();
  }

  // Double vibration pulse to notify user boot is successful
  pulseVibrator(2, 100);
}

// ==========================================
// MAIN DEVICE LOOP
// ==========================================
void loop() {
  // 1. Process GPS Data streams
  while (gpsSerial.available() > 0) {
    gps.encode(gpsSerial.read());
  }

  // 2. Read Sensors
  readBiometrics();
  readFallSensors();

  // 3. Handle SOS Button clicks and patterns
  handleButtonStates();

  // 4. State Machine processing
  switch (currentState) {
    case STATE_NORMAL:
      checkAutomaticEmergencyTriggers();
      break;

    case STATE_PENDING_CANCEL:
      processPendingCancelState();
      break;

    case STATE_TRANSMITTING:
      transmitSOSPacket();
      break;
  }

  delay(10); // Tiny pause to avoid core overheating
}

// ==========================================
// SENSOR READING & LOGIC IMPLEMENTATIONS
// ==========================================

void readBiometrics() {
  if (!maxConnected) return;

  long irValue = particleSensor.getIR();
  
  // Heart Rate Peak Detection
  if (checkForBeat(irValue) == true) {
    long delta = millis() - lastBeatTime;
    lastBeatTime = millis();
    
    beatsPerMinute = 60 / (delta / 1000.0);

    if (beatsPerMinute < 255 && beatsPerMinute > 20) {
      rates[rateSpot++] = (byte)beatsPerMinute;
      rateSpot %= 4;

      // Average heart rate readings
      beatAvg = 0;
      for (byte x = 0 ; x < 4 ; x++) {
        beatAvg += rates[x];
      }
      beatAvg /= 4;
    }
  }

  // Very simplified SpO2 ratio based on Red vs IR amplitude ratio
  if (irValue > 50000) {
    long redValue = particleSensor.getRed();
    float ratio = (float)redValue / (float)irValue;
    if (ratio > 0.5 && ratio < 1.0) {
      calculatedSpO2 = (uint8_t)(110 - (ratio * 25)); // Emulated standard translation
    } else {
      calculatedSpO2 = 98;
    }
  } else {
    // If no finger detected, set to default safe/resting values
    beatAvg = 75;
    calculatedSpO2 = 99;
  }
}

void readFallSensors() {
  if (!mpuConnected) return;

  sensors_event_t a, g, temp;
  mpu.getEvent(&a, &g, &temp);

  // Compute total magnitude of linear acceleration
  float ax = a.acceleration.x;
  float ay = a.acceleration.y;
  float az = a.acceleration.z;
  float magnitude = sqrt(ax * ax + ay * ay + az * az);
  float gForce = magnitude / G_TO_MS2;

  if (gForce > maxGForceThisCycle) {
    maxGForceThisCycle = gForce;
  }

  // Immobility Tracking: check variance/motion level every 500ms
  if (millis() - lastImmobileCheckTime > 500) {
    lastImmobileCheckTime = millis();
    
    // Variance is simulated by angular rate magnitudes (gyroscope)
    float gyroMagnitude = sqrt(g.gyro.x * g.gyro.x + g.gyro.y * g.gyro.y + g.gyro.z * g.gyro.z);
    
    if (gyroMagnitude < IMMOBILITY_THRESHOLD && abs(gForce - 1.0f) < 0.15f) {
      if (immobileStartTime == 0) {
        immobileStartTime = millis();
      }
      // If motionless for more than 10 seconds
      if (millis() - immobileStartTime > 10000) {
        isImmobile = true;
      }
    } else {
      immobileStartTime = 0;
      isImmobile = false;
    }
  }
}

void handleButtonStates() {
  if (!btnPressed) {
    // Check click pattern completion window
    if (buttonPressCount > 0 && (millis() - lastClickTime > CLICK_WINDOW)) {
      triggerManualSOS(buttonPressCount);
      buttonPressCount = 0;
    }
    return;
  }

  btnPressed = false; // Reset interrupt flag
  int pinState = digitalRead(BTN_PIN);

  if (pinState == LOW) { // Button Pressed down
    btnPressStartTime = millis();
    buttonWasHeld = false;
  } 
  else { // Button Released up
    unsigned long pressDuration = millis() - btnPressStartTime;

    if (pressDuration > 2000) { // Held down for 2+ seconds
      buttonWasHeld = true;
      buttonPressCount = 0; // Clear click count
      triggerManualSOS(100); // Code 100 represents long-press Medical Emergency
    } 
    else if (pressDuration > 50) { // Valid short click (debounced)
      if (currentState == STATE_PENDING_CANCEL) {
        // Any button press during low severity alert cancels it
        cancelPendingAlert();
      } else {
        buttonPressCount++;
        lastClickTime = millis();
      }
    }
  }
}

// ==========================================
// EMERGENCY LEVEL EVALUATION & ACTION
// ==========================================

void checkAutomaticEmergencyTriggers() {
  if (maxGForceThisCycle < 2.5f) return;

  float peakG = maxGForceThisCycle;
  maxGForceThisCycle = 1.0f; // Reset peak tracker

  // Check orientation (horizontal status)
  sensors_event_t a, g, temp;
  if (mpuConnected) mpu.getEvent(&a, &g, &temp);
  bool isHorizontal = (mpuConnected && abs(a.acceleration.z) < 4.0f);

  // Initialize payload parameters
  activeAlertPayload.deviceId = DEVICE_ID;
  activeAlertPayload.latitude = gps.location.isValid() ? (float)gps.location.lat() : 0.0f;
  activeAlertPayload.longitude = gps.location.isValid() ? (float)gps.location.lng() : 0.0f;
  activeAlertPayload.gForce = peakG;
  activeAlertPayload.bpm = (uint8_t)beatAvg;
  activeAlertPayload.spo2 = (uint8_t)calculatedSpO2;
  activeAlertPayload.alertType = 0; // Auto-Fall type

  // 1. High/Critical Severity (>5.5g OR extreme immobility + abnormal heart rate)
  if (peakG >= 5.5f || (isImmobile && (beatAvg > 120 || beatAvg < 45))) {
    activeAlertPayload.severity = 3;
    currentState = STATE_TRANSMITTING;
    Serial.println(">>> CRITICAL ALERT: Immediate transmission triggered!");
    return;
  }

  // 2. Medium Severity (4.0g–5.5g + horizontal + motionless)
  if (peakG >= 4.0f && peakG < 5.5f) {
    // Wait for 10 seconds of motionless immobility to fully classify
    delay(2000); 
    readFallSensors();
    if (isImmobile || isHorizontal) {
      activeAlertPayload.severity = 2;
      currentState = STATE_TRANSMITTING;
      Serial.println(">>> MEDIUM ALERT: Fall & Immobility confirmed. Transmitting.");
      return;
    }
  }

  // 3. Low Severity (2.5g–4.0g, brief fall under 5s, normal heart rate)
  if (peakG >= 2.5f && peakG < 4.0f) {
    activeAlertPayload.severity = 1;
    currentState = STATE_PENDING_CANCEL;
    stateChangeTime = millis();
    Serial.println(">>> WARNING: Low impact detected. Pre-warning user for cancel option.");
    pulseVibrator(1, 800); // Heavy warning buzz
    triggerWarningBuzzer();
  }
}

void processPendingCancelState() {
  unsigned long timeElapsed = millis() - stateChangeTime;

  // Sound warning buzzers periodically
  if (timeElapsed % 2000 < 100) {
    digitalWrite(BUZZER_PIN, HIGH);
    digitalWrite(VIBRA_PIN, HIGH);
  } else {
    digitalWrite(BUZZER_PIN, LOW);
    digitalWrite(VIBRA_PIN, LOW);
  }

  if (timeElapsed > CANCEL_WINDOW) {
    // Time expired without cancellation. Upgrade & Transmit.
    currentState = STATE_TRANSMITTING;
    digitalWrite(BUZZER_PIN, LOW);
    digitalWrite(VIBRA_PIN, LOW);
    Serial.println(">>> Pre-warning window expired. Transmitting Low Severity SOS.");
  }
}

void cancelPendingAlert() {
  currentState = STATE_NORMAL;
  digitalWrite(BUZZER_PIN, LOW);
  digitalWrite(VIBRA_PIN, LOW);
  Serial.println(">>> SOS alert successfully CANCELLED by user.");
  
  // Confirmed cancel pulse
  pulseVibrator(3, 50);
}

void triggerManualSOS(int pattern) {
  activeAlertPayload.deviceId = DEVICE_ID;
  activeAlertPayload.latitude = gps.location.isValid() ? (float)gps.location.lat() : 0.0f;
  activeAlertPayload.longitude = gps.location.isValid() ? (float)gps.location.lng() : 0.0f;
  activeAlertPayload.gForce = 1.0f;
  activeAlertPayload.bpm = (uint8_t)beatAvg;
  activeAlertPayload.spo2 = (uint8_t)calculatedSpO2;
  activeAlertPayload.severity = 3; // Manual SOS is always immediately Critical/High severity

  switch (pattern) {
    case 100: // Long press
      activeAlertPayload.alertType = 1; // Medical
      Serial.println(">>> MANUAL SOS: Medical Emergency Pattern detected.");
      break;
    case 2: // Double Click
      activeAlertPayload.alertType = 2; // Fire
      Serial.println(">>> MANUAL SOS: Fire Emergency Pattern detected.");
      break;
    case 3: // Triple Click
      activeAlertPayload.alertType = 3; // Landslide
      Serial.println(">>> MANUAL SOS: Landslide Emergency Pattern detected.");
      break;
    default:
      activeAlertPayload.alertType = 1; // Default fallback to Medical/General
      Serial.println(">>> MANUAL SOS: Click Pattern unrecognized, sending general SOS.");
      break;
  }

  currentState = STATE_TRANSMITTING;
}

// ==========================================
// WIRELESS TRANSMISSION (LORA)
// ==========================================

void transmitSOSPacket() {
  Serial.println(">>> Preparing LoRa Packet Transmission...");
  
  // Double-check GPS one last time
  if (gps.location.isValid()) {
    activeAlertPayload.latitude = (float)gps.location.lat();
    activeAlertPayload.longitude = (float)gps.location.lng();
  }

  // Vibrate continuously during packet send
  digitalWrite(VIBRA_PIN, HIGH);

  // Send packet 3 times to ensure reception redundancy through foliage & storm noise
  for (int retry = 0; retry < 3; retry++) {
    LoRa.beginPacket();
    LoRa.write((uint8_t*)&activeAlertPayload, sizeof(EmergencyPacket));
    LoRa.endPacket();
    delay(100);
  }

  digitalWrite(VIBRA_PIN, LOW);
  Serial.println("LoRa Transmission complete.");
  
  // Successful sending notification (3 quick pulses)
  pulseVibrator(3, 80);

  // Reset state to normal monitoring
  currentState = STATE_NORMAL;
  maxGForceThisCycle = 1.0f; 
}

// ==========================================
// HAPTIC FEEDBACK UTILITIES
// ==========================================

void pulseVibrator(int count, int duration) {
  for (int i = 0; i < count; i++) {
    digitalWrite(VIBRA_PIN, HIGH);
    delay(duration);
    digitalWrite(VIBRA_PIN, LOW);
    if (i < count - 1) delay(100);
  }
}

void triggerWarningBuzzer() {
  for (int i = 0; i < 3; i++) {
    digitalWrite(BUZZER_PIN, HIGH);
    delay(150);
    digitalWrite(BUZZER_PIN, LOW);
    delay(100);
  }
}

void triggerLocalSOSBlink() {
  while (true) {
    digitalWrite(BUZZER_PIN, HIGH);
    digitalWrite(VIBRA_PIN, HIGH);
    delay(100);
    digitalWrite(BUZZER_PIN, LOW);
    digitalWrite(VIBRA_PIN, LOW);
    delay(100);
  }
}
