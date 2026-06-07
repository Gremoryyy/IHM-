// ----------------
//
//  Appli : 1900_5_8_Esp32_Code_Pour_Drone_A_V1_27_04_2026_BDD
//  Langage : C++
//  Date : 27/04/2026
//  Modif : ajout chargement BDD + affichage Serial
//
//-------------

#include <WiFi.h>              // Connexion Wi-Fi de l'ESP32
#include <WiFiManager.h>       // Configuration Wi-Fi via portail web, sans mot de passe en dur dans le code
#include <WiFiClientSecure.h>  // Connexion HTTPS/TLS avec verification du certificat
#include <HTTPClient.h>        // Appel du fichier PHP sur le PC
#include <ArduinoJson.h>       // Lecture des donnees JSON envoyees par le PHP
#include <ESP32Servo.h>        // Pilotage des servomoteurs
#include <time.h>              // Gestion de l'heure pour verifier la validite du certificat TLS
#include <sys/time.h>          // Fonction settimeofday() pour definir une heure fixe si le NTP echoue

#define ROW_COUNT(array) (sizeof(array) / sizeof(*array))
// Cette macro fonctionne en divisant la taille totale du tableau par la taille d'une ligne.
// Cela donne le nombre de lignes du tableau.

Servo servo_0; // Poignet Ouvrir-Fermer / pince
Servo servo_1; // Poignet Haut-Bas
Servo servo_2; // Poignet Rotation
Servo servo_3; // Coude
Servo servo_4; // Epaule
Servo servo_5; // Base

// =========================
// API SECURISEE
// =========================
const char* apiHost = "192.168.1.101";
const int apiPort = 8443;

const char* apiUrl = "https://192.168.1.101:8443/api/export_positions.php?robot_id=1";

// Jeton secret partagé entre l'ESP32 et le serveur PHP
const char* API_TOKEN = "21f2d2968e3fc96a1f3fb4d1a627db8e49817b62e6c8450b579d701b96f3f74b";

// Certificat CA local à coller ici après génération
static const char root_ca[] PROGMEM = R"EOF(
-----BEGIN CERTIFICATE-----
MIIFGzCCAwOgAwIBAgIUOZ635E5qnMTncN/XfzzAZwPBV7EwDQYJKoZIhvcNAQEL
BQAwHTEbMBkGA1UEAwwSUm9ib3Q2RERMLUxvY2FsLUNBMB4XDTI2MDUwNDEyNDIz
NFoXDTM2MDUwMTEyNDIzNFowHTEbMBkGA1UEAwwSUm9ib3Q2RERMLUxvY2FsLUNB
MIICIjANBgkqhkiG9w0BAQEFAAOCAg8AMIICCgKCAgEAjZ4GZd5eeT9pP5wHnJZY
tQY60ql2EfDmVVEXyuRnkmzgxBZMJOcolziWhTDLP5Lt2Dw+k6CrLZqNw43PrlMK
l+OZEhqDAPWqTySR6PsFiKUJTR2Y3Xcgqp2KpuXPQ+E6QRJX9Q9UjtJn3eKyqpzH
vCjcxySNVsdHtKt9x5zwJ8VDZYQn2Kvw09jadu4ruLsXfoCLBngq/6fk64KTPhix
O2mYBSjx7LMJflvccSVyGucaMUyZlka3vXv3P6yC6l9hmb0fpq+4d3p7pwRPM9Iw
QGGRZpOQDZFybAkCEr+UX+H0IYOtdYTVinDvNBQ/bXfQbXlhqKo0OmG+tN3+7NrP
hYFkFMhm2HUJuFe/8TVUQCI4zBZHdUqbFL1I7Nq+lcQczevXjP4kGglnoWUskhcr
j3mXIFx6UzkFD2PS2EdCDo+ZBtan5y5psAJoDQSQpdUwcA9JkxPGb2uFo2nN8bPX
5zQXq3ejv1/eJf80XtuBTdt9w4ykF7nkSj1/lfpAKUXAHvjQjnCXrMZWb0mDwW5C
KRBtX5gJrcoN3mQWjbvcJEH0OnYF0jjJebV0GLP24x/TW8OwC9ctX+Sr5ORqzn0p
FpTRpM2TchJnLPYkQfzKcwLlsPQk2MkHH6DPiSc1xFWHC2K5QDwrRQkxaEKPyCg4
ZWqGYy2uTzDmwAofc72vJsMCAwEAAaNTMFEwHQYDVR0OBBYEFMnoMTG0vDu9LZPh
sELF9P0ealztMB8GA1UdIwQYMBaAFMnoMTG0vDu9LZPhsELF9P0ealztMA8GA1Ud
EwEB/wQFMAMBAf8wDQYJKoZIhvcNAQELBQADggIBAA7QZTRlnsDeAm68+YvDAfwG
KT0qauZQilWIjyj2DI4hTIUoNTb8OVLH1SV3UdTOddeiaP8AJki6x5h7ltV6qZ65
saez4g4iKhH1tJ3IFq0loatvq2vtscc8kLT4teuWH38zQM5ZNMYegs3VxQnnNjoR
e84idmDxVmi/L1jcsOFrcPFv4ozQWSzrUSWyrcwd4GIuoZTdqEOexQiZQ9rVqmSp
TwxhyX3fUfVZotPR6P62nNe7OmuYtm1GU1p89Bu2wrVzr1hiVxlQL4yAz68LqoF8
DoJZC83qBezbeP7SM1HcdExiV5vh0T1sL3igj1GL5RbUlTCvQ8Bt3mjlu7ADA8oR
2iHd0kpyWkur8wtREsyEcORjMzsJOM7nA3qDrDqzv2KJVj1suBvG7+olGf4KwVXE
8Tabz0EFpg4s3LAkctUnPxmlFGyJjC6PF0XbMglI/X+FUZtGbE5+DoSj1wbcAzy9
f8CUWTYX6/LrEZiOcUClAIO0tpyrSTugN6wQyE9VL08gbGaIjaPcVkwAmbrm/kN3
DLzr95pKxzb+tCNS7g4d6XLGk21H5oDmKcdMg9bnoPCwzcTBPyH6Ker+uViCYs9b
GIpbEWK9kjnYaR/ShW9YKQ6QfQDsfKw2G55dzj0fFHGMUY63Sq490GJdyHYnixUY
HrimfIXPjfwMJaDcjYy4
-----END CERTIFICATE-----
)EOF";

// =========================
// MOUVEMENTS ROBOT A - VERSION PROF 27/04/2026
// =========================

// mouvement1 : HOME PINCE OUVERTE
int mouvement1[1][6] = {
  {83, 43, 98, 170, 160, 63}
};

// mouvement2 : HOME PINCE FERMEE
int mouvement2[1][6] = {
  {0, 43, 98, 170, 160, 63}
};

// mouvement3 : HOME PINCE OUVERTE VERS mouvement4 : PRENDRE UNE BOITE AU CENTRE DE LA PALETTE 1
int mouvement3[2][6] = {
  {83, 43, 98, 170, 160, 63},
  {87, 35, 98, 149, 141, 105}
};

// mouvement4 : PRENDRE UNE BOITE AU CENTRE DE LA PALETTE 1
int mouvement4[12][6] = {
  {87, 35, 98, 149, 141, 105},
  {87, 35, 98, 149, 107, 105},
  {87, 35, 98, 149, 92, 105},
  {87, 35, 98, 149, 92, 105},
  {87, 35, 98, 149, 92, 105},
  {87, 35, 98, 149, 92, 105},
  {0, 35, 98, 149, 92, 105},
  {0, 35, 98, 149, 92, 105},
  {0, 35, 98, 149, 92, 105},
  {0, 35, 98, 149, 92, 105},
  {0, 35, 98, 149, 110, 105},
  {0, 35, 98, 149, 138, 105}
};

// mouvement5 : PRENDRE UNE BOITE AU CENTRE DE LA PALETTE 1 VERS mouvement6 : DEPOSER UNE BOITE AU CENTRE DE LA PALETTE 2
int mouvement5[2][6] = {
  {0, 35, 98, 149, 138, 105},
  {0, 35, 98, 149, 138, 44}
};

// mouvement6 : DEPOSER UNE BOITE AU CENTRE DE LA PALETTE 2
int mouvement6[11][6] = {
  {0, 35, 98, 149, 138, 44},
  {0, 35, 98, 149, 110, 44},
  {0, 35, 98, 149, 95, 44},
  {0, 35, 98, 149, 95, 44},
  {0, 35, 98, 145, 95, 44},
  {0, 35, 98, 145, 95, 44},
  {83, 35, 98, 145, 95, 44},
  {83, 35, 98, 145, 140, 44},
  {83, 35, 98, 149, 140, 44},
  {83, 35, 98, 149, 140, 44},
  {83, 35, 98, 149, 140, 42}
};

// mouvement7 : DEPOSER UNE BOITE AU CENTRE DE LA PALETTE 2 VERS mouvement1 : HOME PINCE OUVERTE
int mouvement7[2][6] = {
  {83, 35, 98, 149, 140, 42},
  {83, 43, 98, 170, 160, 63}
};

//-------------
void mouvement(int (tab)[][6], int lignes)
{
  int pos_actuelle_servo1, pos_actuelle_servo2, pos_actuelle_servo3, pos_actuelle_servo4, pos_actuelle_servo5, pos_actuelle_servo6;
  int pos_precedente_servo1, pos_precedente_servo2, pos_precedente_servo3, pos_precedente_servo4, pos_precedente_servo5, pos_precedente_servo6;

  for (int i = 0; i < lignes; i++) {
    if (i > 0) {
      pos_actuelle_servo1 = tab[i][0];
      pos_actuelle_servo2 = tab[i][1];
      pos_actuelle_servo3 = tab[i][2];
      pos_actuelle_servo4 = tab[i][3];
      pos_actuelle_servo5 = tab[i][4];
      pos_actuelle_servo6 = tab[i][5];

      pos_precedente_servo1 = tab[i - 1][0];
      pos_precedente_servo2 = tab[i - 1][1];
      pos_precedente_servo3 = tab[i - 1][2];
      pos_precedente_servo4 = tab[i - 1][3];
      pos_precedente_servo5 = tab[i - 1][4];
      pos_precedente_servo6 = tab[i - 1][5];

      progressif(0, pos_precedente_servo1, pos_actuelle_servo1);
      progressif(1, pos_precedente_servo2, pos_actuelle_servo2);
      progressif(2, pos_precedente_servo3, pos_actuelle_servo3);
      progressif(3, pos_precedente_servo4, pos_actuelle_servo4);
      progressif(4, pos_precedente_servo5, pos_actuelle_servo5);
      progressif(5, pos_precedente_servo6, pos_actuelle_servo6);
    } else if (i == 0) {
      servo_0.write(tab[i][0]);
      servo_1.write(tab[i][1]);
      servo_2.write(tab[i][2]);
      servo_3.write(tab[i][3]);
      servo_4.write(tab[i][4]);
      servo_5.write(tab[i][5]);
    }
  }
}

//-------------
void progressif(int servo_no, int pos_precedente, int pos_actuelle)
{
  int delais_progressif = 50;

  if (pos_precedente < pos_actuelle) {
    for (int p = pos_precedente; p <= pos_actuelle; p++) {
      if (servo_no == 0) { servo_0.write(p); delay(delais_progressif); }
      if (servo_no == 1) { servo_1.write(p); delay(delais_progressif); }
      if (servo_no == 2) { servo_2.write(p); delay(delais_progressif); }
      if (servo_no == 3) { servo_3.write(p); delay(delais_progressif); }
      if (servo_no == 4) { servo_4.write(p); delay(delais_progressif); }
      if (servo_no == 5) { servo_5.write(p); delay(delais_progressif); }
    }
  } else if (pos_precedente > pos_actuelle) {
    for (int p = pos_precedente; p >= pos_actuelle; p--) {
      if (servo_no == 0) { servo_0.write(p); delay(delais_progressif); }
      if (servo_no == 1) { servo_1.write(p); delay(delais_progressif); }
      if (servo_no == 2) { servo_2.write(p); delay(delais_progressif); }
      if (servo_no == 3) { servo_3.write(p); delay(delais_progressif); }
      if (servo_no == 4) { servo_4.write(p); delay(delais_progressif); }
      if (servo_no == 5) { servo_5.write(p); delay(delais_progressif); }
    }
  }
}

//-------------
// Affiche le contenu reel d'un tableau en memoire.
// Utilise pour prouver que les valeurs changent avant/apres chargement BDD.
void afficherTableau(const char* titre, const char* nom, int tab[][6], int lignes)
{
  Serial.println();
  Serial.println(titre);
  Serial.print(nom);
  Serial.println(" = {");

  for (int i = 0; i < lignes; i++) {
    Serial.print("  {");

    for (int j = 0; j < 6; j++) {
      Serial.print(tab[i][j]);

      if (j < 5) {
        Serial.print(", ");
      }
    }

    Serial.println("}");
  }

  Serial.println("}");
}

//-------------
// Verifie qu'un mouvement existe dans le JSON avec le bon nombre d'etapes
// et 6 valeurs par etape. Sert a eviter un chargement partiel dangereux.
bool verifierMouvement(JsonObject mouvements, const char* nom, int lignes)
{
  JsonArray mouvementJson = mouvements[nom].as<JsonArray>();

  if (mouvementJson.isNull() || mouvementJson.size() != lignes) {
    Serial.print("Erreur verification : ");
    Serial.print(nom);
    Serial.print(" | attendu : ");
    Serial.print(lignes);
    Serial.print(" etapes | recu : ");
    Serial.println(mouvementJson.isNull() ? 0 : mouvementJson.size());
    return false;
  }

  for (int i = 0; i < lignes; i++) {
    JsonArray ligne = mouvementJson[i].as<JsonArray>();

    if (ligne.isNull() || ligne.size() != 6) {
      Serial.print("Erreur verification ligne : ");
      Serial.print(nom);
      Serial.print(" etape ");
      Serial.print(i + 1);
      Serial.println(" | attendu : 6 valeurs servo");
      return false;
    }
  }

  return true;
}

//-------------
// Copie un mouvement recu en JSON dans un tableau du programme.
bool copierMouvement(JsonObject mouvements, const char* nom, int tab[][6], int lignes)
{
  JsonArray mouvementJson = mouvements[nom].as<JsonArray>();

  if (mouvementJson.isNull() || mouvementJson.size() != lignes) {
    Serial.print("Erreur chargement : ");
    Serial.print(nom);
    Serial.print(" | attendu : ");
    Serial.print(lignes);
    Serial.print(" etapes | recu : ");
    Serial.println(mouvementJson.isNull() ? 0 : mouvementJson.size());
    return false;
  }

  Serial.print("Valeurs recuperees depuis la BDD pour ");
  Serial.println(nom);

  for (int i = 0; i < lignes; i++) {
    JsonArray ligne = mouvementJson[i].as<JsonArray>();

    if (ligne.isNull() || ligne.size() != 6) {
      Serial.print("Erreur format ligne JSON : ");
      Serial.print(nom);
      Serial.print(" etape ");
      Serial.println(i + 1);
      return false;
    }

    Serial.print("  Etape ");
    Serial.print(i + 1);
    Serial.print(" : ");

    for (int j = 0; j < 6; j++) {
      tab[i][j] = ligne[j].as<int>();
      Serial.print(tab[i][j]);

      if (j < 5) {
        Serial.print(", ");
      }
    }

    Serial.println();
  }

  Serial.print("Chargement OK : ");
  Serial.println(nom);
  return true;
}

//-------------
// Fonction appelee au demarrage.
// Elle connecte l'ESP32 au Wi-Fi, appelle l'API PHP du PC,
// recupere les positions venant de la BDD, puis remplit les tableaux mouvement1 a mouvement7.
// Si une erreur arrive, les valeurs ecrites en dur dans le code sont conservees.
bool chargerPositionsDepuisBDD()
{
  Serial.println("Connexion Wi-Fi via WiFiManager...");

WiFiManager wm;
wm.setConfigPortalTimeout(180);

// Nom du point d'accès temporaire créé par l'ESP32 si aucun Wi-Fi n'est configuré
bool wifiOK = wm.autoConnect("Robot6DDL-A-Config", "robot6ddl");

if (!wifiOK) {
  Serial.println("Wi-Fi impossible. Redemarrage ESP32.");
  ESP.restart();
  return false;
}

Serial.println("Wi-Fi connecte");
Serial.print("IP ESP32 : ");
Serial.println(WiFi.localIP());

  Serial.println("\nWi-Fi connecte");
  Serial.print("IP ESP32 : ");
  Serial.println(WiFi.localIP());

 WiFiClientSecure client;
  HTTPClient http;

  client.setCACert(root_ca);

  Serial.print("Appel API securise : ");
  Serial.println(apiUrl);

  if (!http.begin(client, apiUrl)) {
    Serial.println("Erreur : http.begin() impossible.");
    return false;
}

http.addHeader("X-ESP32-TOKEN", API_TOKEN);

int httpCode = http.GET();

  if (httpCode != 200) {
    Serial.print("Erreur HTTP : ");
    Serial.println(httpCode);
    Serial.print("Detail : ");
    Serial.println(http.errorToString(httpCode));
    http.end();
    return false;
  }

  String payload = http.getString();
  http.end();

  DynamicJsonDocument doc(16000);

  DeserializationError error = deserializeJson(doc, payload);
  if (error) {
    Serial.print("Erreur lecture JSON : ");
    Serial.println(error.c_str());
    Serial.println("Payload recu :");
    Serial.println(payload);
    return false;
  }

  if (!doc["ok"].as<bool>()) {
    Serial.println("Erreur API PHP.");
    Serial.println("Payload recu :");
    Serial.println(payload);
    return false;
  }

  JsonObject mouvements = doc["movements"].as<JsonObject>();
  if (mouvements.isNull()) {
    Serial.println("Erreur JSON : objet movements absent.");
    return false;
  }

  bool verificationOK = true;
  verificationOK &= verifierMouvement(mouvements, "mouvement1", ROW_COUNT(mouvement1));
  verificationOK &= verifierMouvement(mouvements, "mouvement2", ROW_COUNT(mouvement2));
  verificationOK &= verifierMouvement(mouvements, "mouvement3", ROW_COUNT(mouvement3));
  verificationOK &= verifierMouvement(mouvements, "mouvement4", ROW_COUNT(mouvement4));
  verificationOK &= verifierMouvement(mouvements, "mouvement5", ROW_COUNT(mouvement5));
  verificationOK &= verifierMouvement(mouvements, "mouvement6", ROW_COUNT(mouvement6));
  verificationOK &= verifierMouvement(mouvements, "mouvement7", ROW_COUNT(mouvement7));

  if (!verificationOK) {
    Serial.println("Verification BDD echouee : aucun tableau n'est remplace.");
    return false;
  }

  bool copieOK = true;
  copieOK &= copierMouvement(mouvements, "mouvement1", mouvement1, ROW_COUNT(mouvement1));
  copieOK &= copierMouvement(mouvements, "mouvement2", mouvement2, ROW_COUNT(mouvement2));
  copieOK &= copierMouvement(mouvements, "mouvement3", mouvement3, ROW_COUNT(mouvement3));
  copieOK &= copierMouvement(mouvements, "mouvement4", mouvement4, ROW_COUNT(mouvement4));
  copieOK &= copierMouvement(mouvements, "mouvement5", mouvement5, ROW_COUNT(mouvement5));
  copieOK &= copierMouvement(mouvements, "mouvement6", mouvement6, ROW_COUNT(mouvement6));
  copieOK &= copierMouvement(mouvements, "mouvement7", mouvement7, ROW_COUNT(mouvement7));

  return copieOK;
}

//-------------
void setup()
{
  Serial.begin(115200);
  delay(10000);
  Serial.println();
  Serial.println("===== DEMARRAGE DU PROGRAMME =====");
  Serial.println("Version 27/04/2026 + chargement BDD");

  pinMode(14, INPUT); // Capteur infrarouge

  // Allow allocation of all timers
  ESP32PWM::allocateTimer(0);
  ESP32PWM::allocateTimer(1);
  ESP32PWM::allocateTimer(2);
  ESP32PWM::allocateTimer(3);

  servo_0.attach(13, 500, 2400); // Poignet Ouvrir-Fermer / pince - Esp32 D13
  servo_1.attach(12, 500, 2400); // Poignet Haut-Bas - Esp32 D12
  servo_2.attach(26, 500, 2400); // Poignet Rotation - Esp32 D26
  // Attention : broche Esp32 D14 ET D27 NON OK d'apres le code professeur
  servo_3.attach(25);            // Coude - Esp32 D25
  servo_4.attach(33);            // Epaule - Esp32 D33
  servo_5.attach(32);            // Base - Esp32 D32

  Serial.println("===== AVANT CHARGEMENT BDD =====");
  afficherTableau("Tableau mouvement1 AVANT BDD", "mouvement1", mouvement1, ROW_COUNT(mouvement1));
  afficherTableau("Tableau mouvement7 AVANT BDD", "mouvement7", mouvement7, ROW_COUNT(mouvement7));

  bool chargementBDD = chargerPositionsDepuisBDD();

  Serial.println("===== APRES CHARGEMENT BDD =====");
  afficherTableau("Tableau mouvement1 APRES BDD", "mouvement1", mouvement1, ROW_COUNT(mouvement1));
  afficherTableau("Tableau mouvement7 APRES BDD", "mouvement7", mouvement7, ROW_COUNT(mouvement7));

  if (chargementBDD) {
    Serial.println("RESULTAT : les tableaux ont ete remplaces par les valeurs de la BDD.");
  } else {
    Serial.println("RESULTAT : chargement BDD echoue, valeurs de secours conservees.");
  }

  // Une seule sequence de depart
  int delais1 = 1000;
  int delais2 = 500;
  int delais3 = 250;

  delay(delais1);

  // mouvement1 : HOME PINCE OUVERTE
  mouvement(mouvement1, ROW_COUNT(mouvement1));
  delay(delais1);

  // mouvement2 : HOME PINCE FERMEE
  mouvement(mouvement2, ROW_COUNT(mouvement2));
  delay(delais1);

  // mouvement1 : HOME PINCE OUVERTE
  mouvement(mouvement1, ROW_COUNT(mouvement1));
  delay(delais1);

  /*
  mouvement(mouvement3, ROW_COUNT(mouvement3));
  delay(delais1);

  mouvement(mouvement4, ROW_COUNT(mouvement4));
  delay(delais1);

  mouvement(mouvement5, ROW_COUNT(mouvement5));
  delay(delais1);

  mouvement(mouvement6, ROW_COUNT(mouvement6));
  delay(delais1);

  mouvement(mouvement7, ROW_COUNT(mouvement7));
  delay(delais1);
  */
}

//-------------
void loop()
{
  int delais1 = 1000;
  int presence_boite = 1;

  // BOITE detected
  if (digitalRead(14) == 0) {
    presence_boite = 0;

    // mouvement1 : HOME PINCE OUVERTE
    mouvement(mouvement1, ROW_COUNT(mouvement1));
    delay(delais1);

    // mouvement2 : HOME PINCE FERMEE
    mouvement(mouvement2, ROW_COUNT(mouvement2));
    delay(delais1);

    // mouvement1 : HOME PINCE OUVERTE
    mouvement(mouvement1, ROW_COUNT(mouvement1));
    delay(delais1);

    // mouvement3 : HOME PINCE OUVERTE VERS PRENDRE UNE BOITE
    mouvement(mouvement3, ROW_COUNT(mouvement3));
    delay(delais1);

    // mouvement4 : PRENDRE UNE BOITE
    mouvement(mouvement4, ROW_COUNT(mouvement4));
    delay(delais1);

    // mouvement5 : TRANSFERT VERS PALETTE 2
    mouvement(mouvement5, ROW_COUNT(mouvement5));
    delay(delais1);

    // mouvement6 : DEPOSER UNE BOITE
    mouvement(mouvement6, ROW_COUNT(mouvement6));
    delay(delais1);

    // mouvement7 : RETOUR HOME
    mouvement(mouvement7, ROW_COUNT(mouvement7));
    delay(delais1);
  }
}
