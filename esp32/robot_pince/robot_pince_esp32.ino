# Robot Gripper ESP32 Code

#include <WiFi.h>
#include <Servo.h>

// WiFi credentials
const char* ssid = "YOUR_SSID";
const char* password = "YOUR_PASSWORD";

// Servo pin
const int servoPin = 13; 
Servo myServo;

// Sensor pin
const int sensorPin = 34; // Assuming an analog sensor

void setup() {
    Serial.begin(115200);
    WiFi.begin(ssid, password);
    
    while (WiFi.status() != WL_CONNECTED) {
        delay(1000);
        Serial.println("Connecting to WiFi...");
    }

    Serial.println("Connected to WiFi");
    myServo.attach(servoPin);
}

void loop() {
    int sensorValue = analogRead(sensorPin);
    Serial.print("Sensor Value: ");
    Serial.println(sensorValue);
    
    // Example: Control the servo based on sensor value
    if (sensorValue > 500) {
        myServo.write(90); // Open the gripper
    } else {
        myServo.write(0); // Close the gripper
    }
    delay(200);
}