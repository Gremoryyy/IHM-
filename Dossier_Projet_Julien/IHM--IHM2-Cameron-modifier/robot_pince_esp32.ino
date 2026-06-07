#include <Arduino.h>
#include <WiFi.h>
#include <HTTPClient.h>
#include <ArduinoJson.h>
#include <ESP32Servo.h>

/*
  ESP32 connecte a l'IHM / BDD.
  Flux :
  1) L'ESP32 interroge /ihm2/api/get_pending_command.php?robot_id=X
  2) Le backend renvoie une commande avec les positions servo en payload
  3) L'ESP32 execute les etapes puis appelle /ihm2/api/update_command_status.php

  A renseigner avant test :
  - WIFI_SSID / WIFI_PASSWORD
  - API_BASE_URL
  - ROBOT_ID
  - les broches servos
*/

static const char* WIFI_SSID = "YOUR_WIFI_SSID";
static const char* WIFI_PASSWORD = "YOUR_WIFI_PASSWORD";
static const char* API_BASE_URL = "http://192.168.1.10:8080/ihm2/api";
static const int ROBOT_ID = 1;

static const unsigned long POLL_INTERVAL_MS = 1500;
static const int SERVO_MIN_US = 500;
static const int SERVO_MAX_US = 2500;

static const int SERVO_6_PIN = 13;
static const int SERVO_7_PIN = 14;
static const int SERVO_8_PIN = 16;
static const int SERVO_9_PIN = 17;
static const int SERVO_10_PIN = 18;
static const int SERVO_11_PIN = 19;

Servo servo_6;
Servo servo_7;
Servo servo_8;
Servo servo_9;
Servo servo_10;
Servo servo_11;

int currentServo6 = 40;
int currentServo7 = 130;
int currentServo8 = 180;
int currentServo9 = 80;
int currentServo10 = 0;
int currentServo11 = 120;

unsigned long lastPollAt = 0;

static void configureServo(Servo& servo, int pin) {
  servo.setPeriodHertz(50);
  servo.attach(pin, SERVO_MIN_US, SERVO_MAX_US);
}

static void writePose(int s6, int s7, int s8, int s9, int s10, int s11) {
  servo_6.write(s6);
  servo_7.write(s7);
  servo_8.write(s8);
  servo_9.write(s9);
  servo_10.write(s10);
  servo_11.write(s11);

  currentServo6 = s6;
  currentServo7 = s7;
  currentServo8 = s8;
  currentServo9 = s9;
  currentServo10 = s10;
  currentServo11 = s11;
}

static void moveHomePosition() {
  writePose(40, 130, 180, 80, 0, 120);
}

static void connectWifi() {
  WiFi.mode(WIFI_STA);
  WiFi.begin(WIFI_SSID, WIFI_PASSWORD);

  while (WiFi.status() != WL_CONNECTED) {
    delay(700);
    Serial.println("Connexion WiFi...");
  }

  Serial.print("WiFi connecte, IP = ");
  Serial.println(WiFi.localIP());
}

static bool httpGet(const String& url, String& responseBody) {
  HTTPClient http;
  http.begin(url);
  int code = http.GET();
  if (code <= 0) {
    Serial.printf("GET echoue: %s\n", http.errorToString(code).c_str());
    http.end();
    return false;
  }

  responseBody = http.getString();
  http.end();
  return code >= 200 && code < 300;
}

static bool httpPostJson(const String& url, const String& body, String& responseBody) {
  HTTPClient http;
  http.begin(url);
  http.addHeader("Content-Type", "application/json");

  int code = http.POST(body);
  if (code <= 0) {
    Serial.printf("POST echoue: %s\n", http.errorToString(code).c_str());
    http.end();
    return false;
  }

  responseBody = http.getString();
  http.end();
  return code >= 200 && code < 300;
}

static bool updateCommandStatus(int commandId, const char* status, const char* details) {
  DynamicJsonDocument doc(256);
  doc["command_id"] = commandId;
  doc["status"] = status;
  doc["details"] = details;

  String body;
  serializeJson(doc, body);

  String response;
  const String url = String(API_BASE_URL) + "/update_command_status.php";
  return httpPostJson(url, body, response);
}

static int computeDelayMs(JsonObject step) {
  const int target6 = step["servo_6_angle"] | currentServo6;
  const int target7 = step["servo_7_angle"] | currentServo7;
  const int target8 = step["servo_8_angle"] | currentServo8;
  const int target9 = step["servo_9_angle"] | currentServo9;
  const int target10 = step["servo_10_angle"] | currentServo10;
  const int target11 = step["servo_11_angle"] | currentServo11;
  const int speedMsPerDegree = step["speed_ms_per_degree"] | 10;

  int maxDelta = abs(target6 - currentServo6);
  maxDelta = max(maxDelta, abs(target7 - currentServo7));
  maxDelta = max(maxDelta, abs(target8 - currentServo8));
  maxDelta = max(maxDelta, abs(target9 - currentServo9));
  maxDelta = max(maxDelta, abs(target10 - currentServo10));
  maxDelta = max(maxDelta, abs(target11 - currentServo11));

  return max(250, maxDelta * speedMsPerDegree);
}

static bool executePositions(JsonArray positions) {
  for (JsonObject step : positions) {
    const int delayMs = computeDelayMs(step);
    writePose(
      step["servo_6_angle"] | currentServo6,
      step["servo_7_angle"] | currentServo7,
      step["servo_8_angle"] | currentServo8,
      step["servo_9_angle"] | currentServo9,
      step["servo_10_angle"] | currentServo10,
      step["servo_11_angle"] | currentServo11
    );
    delay(delayMs);
  }

  return true;
}

static bool handlePayloadPositions(const char* payload, int commandId) {
  DynamicJsonDocument payloadDoc(8192);
  DeserializationError payloadError = deserializeJson(payloadDoc, payload);
  if (payloadError) {
    Serial.printf("Payload JSON invalide: %s\n", payloadError.c_str());
    updateCommandStatus(commandId, "error", "Payload JSON invalide");
    return false;
  }

  JsonArray positions = payloadDoc["positions"].as<JsonArray>();
  if (positions.isNull() || positions.size() == 0) {
    updateCommandStatus(commandId, "error", "Aucune position recue");
    return false;
  }

  const bool ok = executePositions(positions);
  updateCommandStatus(commandId, ok ? "completed" : "error", ok ? "Sequence executee" : "Execution impossible");
  return ok;
}

static void pollCommands() {
  String response;
  const String url = String(API_BASE_URL) + "/get_pending_command.php?robot_id=" + String(ROBOT_ID);
  if (!httpGet(url, response)) {
    return;
  }

  DynamicJsonDocument doc(4096);
  DeserializationError error = deserializeJson(doc, response);
  if (error) {
    Serial.printf("JSON invalide: %s\n", error.c_str());
    return;
  }

  JsonObject command = doc["command"].as<JsonObject>();
  if (command.isNull()) {
    return;
  }

  const int commandId = command["id"] | 0;
  const String commandType = command["command_type"] | "";
  Serial.printf("Commande recue #%d type=%s\n", commandId, commandType.c_str());

  if (commandType == "stop") {
    moveHomePosition();
    delay(800);
    updateCommandStatus(commandId, "completed", "Arret execute");
    return;
  }

  if (commandType != "charger_boite") {
    updateCommandStatus(commandId, "cancelled", "Commande ignoree par ce sketch");
    return;
  }

  const char* payload = command["payload"] | "";
  handlePayloadPositions(payload, commandId);
}

void setup()
{
  Serial.begin(115200);

  ESP32PWM::allocateTimer(0);
  ESP32PWM::allocateTimer(1);
  ESP32PWM::allocateTimer(2);
  ESP32PWM::allocateTimer(3);

  configureServo(servo_6, SERVO_6_PIN);
  configureServo(servo_7, SERVO_7_PIN);
  configureServo(servo_8, SERVO_8_PIN);
  configureServo(servo_9, SERVO_9_PIN);
  configureServo(servo_10, SERVO_10_PIN);
  configureServo(servo_11, SERVO_11_PIN);

  moveHomePosition();
  delay(1000);
  connectWifi();
}

void loop()
{
  if (WiFi.status() != WL_CONNECTED) {
    connectWifi();
  }

  if (millis() - lastPollAt >= POLL_INTERVAL_MS) {
    lastPollAt = millis();
    pollCommands();
  }

  delay(50);
}
