# ESP32 — Code embarqué (prototype)

Ce dossier regroupe la partie ESP32 du projet robot collaboratif.

## Contenu actuel

- `robot_pince/robot_pince_esp32.ino` : exemple de pilotage simple (servo + lecture capteur).

## Mise en route rapide

1. Ouvrir le fichier `.ino` dans l’IDE Arduino.
2. Sélectionner une carte ESP32 compatible.
3. Installer les bibliothèques nécessaires (`WiFi`, `Servo` selon environnement).
4. Vérifier les broches matérielles et téléverser.

## Sécurité et configuration

- Ne pas committer d’identifiants WiFi réels dans le dépôt.
- Préférer un fichier local non versionné ou des constantes remplacées avant commit.
