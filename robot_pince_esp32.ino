#include <Arduino.h>
#include <ESP32Servo.h>

/*
  Version ESP32 tres proche de ton code Arduino UNO d'origine.
  On garde :
  - les 6 servos
  - les 3 declenchements
  - la meme suite de mouvements

  A adapter avant test :
  - les broches ESP32 reelles
  - l'alimentation externe des servos
*/

Servo servo_11;
Servo servo_10;
Servo servo_9;
Servo servo_8;
Servo servo_7;
Servo servo_6;

// Broches servos ESP32.
static const int SERVO_6_PIN = 13;
static const int SERVO_7_PIN = 14;
static const int SERVO_8_PIN = 16;
static const int SERVO_9_PIN = 17;
static const int SERVO_10_PIN = 18;
static const int SERVO_11_PIN = 19;

// Entrees de declenchement ESP32.
// Equivalent logique de tes digitalRead(5), digitalRead(3), digitalRead(2).
static const int TRIGGER_POS_160_PIN = 23;
static const int TRIGGER_POS_110_PIN = 25;
static const int TRIGGER_POS_85_PIN = 26;

static const int SERVO_MIN_US = 500;
static const int SERVO_MAX_US = 2500;

static void configureServo(Servo& servo, int pin) {
  servo.setPeriodHertz(50);
  servo.attach(pin, SERVO_MIN_US, SERVO_MAX_US);
}

static void executeSequence(int servo6StartAngle) {
  servo_6.write(servo6StartAngle);
  delay(1000);
  servo_11.write(150);
  delay(1000);
  servo_7.write(60);
  delay(1000);
  servo_8.write(170);
  delay(1000);
  servo_9.write(80);
  delay(1000);
  servo_10.write(40);
  delay(1000);
  servo_11.write(50);
  delay(1000);
  servo_8.write(160);
  delay(1000);
  servo_6.write(40);
  delay(1000);
  servo_11.write(140);
  delay(1000);
  servo_6.write(40);
  delay(1000);
  servo_7.write(130);
  delay(1000);
  servo_8.write(180);
  delay(1000);
  servo_9.write(80);
  delay(1000);
  servo_10.write(0);
  delay(1000);
  servo_11.write(120);
}

void setup()
{
  Serial.begin(115200);

  pinMode(TRIGGER_POS_160_PIN, INPUT_PULLUP);
  pinMode(TRIGGER_POS_110_PIN, INPUT_PULLUP);
  pinMode(TRIGGER_POS_85_PIN, INPUT_PULLUP);

  ESP32PWM::allocateTimer(0);
  ESP32PWM::allocateTimer(1);
  ESP32PWM::allocateTimer(2);
  ESP32PWM::allocateTimer(3);

  configureServo(servo_6, SERVO_6_PIN);
  servo_6.write(40);
  delay(1000);
  configureServo(servo_7, SERVO_7_PIN);
  servo_7.write(130);
  delay(1000);
  configureServo(servo_8, SERVO_8_PIN);
  servo_8.write(180);
  delay(1000);
  configureServo(servo_9, SERVO_9_PIN);
  servo_9.write(80);
  delay(1000);
  configureServo(servo_10, SERVO_10_PIN);
  servo_10.write(0);
  delay(1000);
  configureServo(servo_11, SERVO_11_PIN);
  servo_11.write(120);
}

void loop()
{
  if (digitalRead(TRIGGER_POS_160_PIN) == LOW) {
    executeSequence(160);
  }
  /* else if (digitalRead(4) == LOW) {
    executeSequence(135);
  } */
  else if (digitalRead(TRIGGER_POS_110_PIN) == LOW) {
    executeSequence(110);
  }
  else if (digitalRead(TRIGGER_POS_85_PIN) == LOW) {
    executeSequence(85);
  }

  delay(20);
}
