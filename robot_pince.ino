/*
  Robot pince - exemple "prise & dépose" (Arduino)

  Hypothèse matériel (modifiable) :
  - 2 moteurs DC + driver type L298N / TB6612 (2 côtés: gauche/droite)
  - 3 capteurs suiveur de ligne (gauche/centre/droite) en entrée digitale
  - 1 capteur ultrason (HC-SR04) pour détecter une caisse devant
  - 2 servos: bras (haut/bas) + pince (ouvrir/fermer)

  Principe:
  1) Suivre la ligne jusqu'à détecter une caisse (ultrason)
  2) Prendre la caisse (descendre bras, fermer pince, remonter bras)
  3) Aller jusqu'à la zone "palette" (ex: en comptant des intersections)
  4) Déposer dans le grand rectangle (ouvrir pince, remonter/retour)

  IMPORTANT:
  - Tu dois adapter les broches, les angles des servos, et la logique "zone palette"
    selon TON robot et TON terrain (ligne, marqueurs, distance, etc.).
*/

#include <Arduino.h>
#include <Servo.h>

// -------------------------
// Pins à adapter
// -------------------------

// Moteur gauche
static const uint8_t L_IN1 = 4;
static const uint8_t L_IN2 = 5;
static const uint8_t L_EN  = 6;   // PWM

// Moteur droit
static const uint8_t R_IN1 = 7;
static const uint8_t R_IN2 = 8;
static const uint8_t R_EN  = 9;   // PWM

// Capteurs de ligne (sortie digitale: 0/1)
static const uint8_t LINE_L = A0;
static const uint8_t LINE_C = A1;
static const uint8_t LINE_R = A2;

// Ultrason
static const uint8_t US_TRIG = 10;
static const uint8_t US_ECHO = 11;

// Servos
static const uint8_t SERVO_ARM_PIN  = 2;
static const uint8_t SERVO_GRIP_PIN = 3;

// -------------------------
// Réglages à calibrer
// -------------------------

// Angles (à calibrer selon ton montage)
static const int ARM_UP   = 130;
static const int ARM_DOWN = 70;
static const int GRIP_OPEN   = 110;
static const int GRIP_CLOSED = 60;

// Vitesse base (0..255)
static const int SPEED_FWD = 150;
static const int SPEED_TURN = 140;

// Suivi de ligne (simple)
static const int LINE_KP = 45; // plus grand = corrige plus fort

// Détection caisse
static const int CRATE_DETECT_CM = 10; // distance seuil devant le robot
static const uint16_t CRATE_STABLE_MS = 250; // stabilité avant prise

// Zone palette (exemple)
// Ici: on suppose que tu as 2 intersections (croisements) à passer pour arriver à la palette.
static const uint8_t PALLET_INTERSECTIONS_TO_PASS = 2;

// Dépose (exemple): 2x2 positions dans le grand rectangle
static const uint8_t PALLET_SLOTS = 4;

// -------------------------
// Objets / état
// -------------------------

Servo armServo;
Servo gripServo;

enum class State : uint8_t {
  SEARCH_CRATE,
  GRAB_CRATE,
  GO_TO_PALLET,
  DROP_CRATE,
  RETURN_TO_SEARCH
};

static State state = State::SEARCH_CRATE;
static uint8_t passedIntersections = 0;
static uint8_t dropIndex = 0;

// -------------------------
// Utilitaires
// -------------------------

static void setMotorRaw(uint8_t in1, uint8_t in2, uint8_t en, int speedSigned) {
  int speedAbs = abs(speedSigned);
  if (speedAbs > 255) speedAbs = 255;

  if (speedSigned >= 0) {
    digitalWrite(in1, HIGH);
    digitalWrite(in2, LOW);
  } else {
    digitalWrite(in1, LOW);
    digitalWrite(in2, HIGH);
  }
  analogWrite(en, speedAbs);
}

static void drive(int leftSpeed, int rightSpeed) {
  setMotorRaw(L_IN1, L_IN2, L_EN, leftSpeed);
  setMotorRaw(R_IN1, R_IN2, R_EN, rightSpeed);
}

static void stopMotors() {
  drive(0, 0);
}

static uint8_t readLineL() { return (uint8_t)digitalRead(LINE_L); }
static uint8_t readLineC() { return (uint8_t)digitalRead(LINE_C); }
static uint8_t readLineR() { return (uint8_t)digitalRead(LINE_R); }

// Retourne true si on est sur une intersection/croisement (ex: 3 capteurs détectent la ligne).
static bool isIntersection() {
  const uint8_t l = readLineL();
  const uint8_t c = readLineC();
  const uint8_t r = readLineR();
  return (l == HIGH && c == HIGH && r == HIGH);
}

static long readUltrasonicCm() {
  digitalWrite(US_TRIG, LOW);
  delayMicroseconds(2);
  digitalWrite(US_TRIG, HIGH);
  delayMicroseconds(10);
  digitalWrite(US_TRIG, LOW);

  // timeout ~ 25ms => ~ 4m
  const unsigned long duration = pulseIn(US_ECHO, HIGH, 25000UL);
  if (duration == 0) return 999; // rien

  // vitesse son: 343 m/s => 29.1 us/cm aller-retour ~ 58.2 us/cm
  return (long)(duration / 58UL);
}

static void followLineStep(int baseSpeed) {
  // Selon câblage, HIGH/LOW peut être inversé. Si ton robot tourne "à l'envers",
  // inverse la logique des capteurs ou swappe les corrections.
  const int l = (readLineL() == HIGH) ? 1 : 0;
  const int c = (readLineC() == HIGH) ? 1 : 0;
  const int r = (readLineR() == HIGH) ? 1 : 0;

  // Erreur: gauche = -1, centre = 0, droite = +1
  int error = 0;
  if (c == 1) error = 0;
  else if (l == 1) error = -1;
  else if (r == 1) error = +1;
  else error = 0; // ligne perdue => continue tout droit (à ajuster si besoin)

  const int correction = LINE_KP * error;
  const int left = baseSpeed - correction;
  const int right = baseSpeed + correction;
  drive(left, right);
}

static void armUp() {
  armServo.write(ARM_UP);
  delay(400);
}

static void armDown() {
  armServo.write(ARM_DOWN);
  delay(450);
}

static void gripOpen() {
  gripServo.write(GRIP_OPEN);
  delay(350);
}

static void gripClose() {
  gripServo.write(GRIP_CLOSED);
  delay(450);
}

static void grabSequence() {
  stopMotors();
  armDown();
  gripClose();
  armUp();
}

static void dropSequence() {
  stopMotors();
  armDown();
  gripOpen();
  armUp();
}

// Exemple: se placer sur une "case" du grand rectangle.
// Ici on fait simple: on avance un peu plus à chaque dépose.
static void moveToDropSlot(uint8_t slotIndex) {
  // A adapter selon ton terrain (marqueurs, intersections, distance, etc.)
  const uint16_t forwardMs = 450 + (uint16_t)slotIndex * 250;
  drive(SPEED_FWD, SPEED_FWD);
  delay(forwardMs);
  stopMotors();
  delay(200);
}

// -------------------------
// Setup / Loop
// -------------------------

void setup() {
  pinMode(L_IN1, OUTPUT);
  pinMode(L_IN2, OUTPUT);
  pinMode(L_EN, OUTPUT);
  pinMode(R_IN1, OUTPUT);
  pinMode(R_IN2, OUTPUT);
  pinMode(R_EN, OUTPUT);

  pinMode(LINE_L, INPUT);
  pinMode(LINE_C, INPUT);
  pinMode(LINE_R, INPUT);

  pinMode(US_TRIG, OUTPUT);
  pinMode(US_ECHO, INPUT);

  armServo.attach(SERVO_ARM_PIN);
  gripServo.attach(SERVO_GRIP_PIN);

  gripOpen();
  armUp();
}

void loop() {
  switch (state) {
    case State::SEARCH_CRATE: {
      followLineStep(SPEED_FWD);

      const long d = readUltrasonicCm();
      if (d <= CRATE_DETECT_CM) {
        const unsigned long t0 = millis();
        bool stable = true;
        while (millis() - t0 < CRATE_STABLE_MS) {
          if (readUltrasonicCm() > CRATE_DETECT_CM) {
            stable = false;
            break;
          }
          delay(30);
        }
        if (stable) {
          state = State::GRAB_CRATE;
        }
      }
      break;
    }

    case State::GRAB_CRATE: {
      grabSequence();
      passedIntersections = 0;
      state = State::GO_TO_PALLET;
      break;
    }

    case State::GO_TO_PALLET: {
      followLineStep(SPEED_FWD);

      static bool wasIntersection = false;
      const bool inter = isIntersection();
      if (inter && !wasIntersection) {
        passedIntersections++;
      }
      wasIntersection = inter;

      if (passedIntersections >= PALLET_INTERSECTIONS_TO_PASS) {
        stopMotors();
        delay(200);
        state = State::DROP_CRATE;
      }
      break;
    }

    case State::DROP_CRATE: {
      const uint8_t slot = dropIndex % PALLET_SLOTS;
      moveToDropSlot(slot);
      dropSequence();
      dropIndex++;
      state = State::RETURN_TO_SEARCH;
      break;
    }

    case State::RETURN_TO_SEARCH: {
      // Exemple très simple: demi-tour puis repartir (à adapter!)
      drive(SPEED_TURN, -SPEED_TURN);
      delay(650);
      stopMotors();
      delay(200);

      state = State::SEARCH_CRATE;
      break;
    }
  }
}

