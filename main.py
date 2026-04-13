import tkinter as tk
from tkinter import ttk
import threading
import time
import serial
import serial.tools.list_ports

# ==========================================================
# CONFIGURATION SERIE
# ==========================================================
BAUDRATE = 9600
arduino = None
lecture_active = False

# ==========================================================
# ETAT LOCAL DU SYSTEME (IHM)
# True  = en marche
# False = arrete
# ==========================================================
systeme_actif_ui = True

# ==========================================================
# DONNEES ETUDIANT 1 POUR L'AFFICHAGE LOCAL
# ==========================================================
positions = {
    "Boite 1": [85, 65, 175, 80, 40, 40],
    "Boite 2": [110, 65, 175, 80, 40, 40],
    "Boite 3": [135, 65, 175, 80, 40, 40],
    "Boite 4": [160, 65, 175, 80, 40, 40],
    "Repos": [40, 130, 180, 80, 0, 120],
    "Stop": [40, 130, 180, 80, 0, 120]
}

# ==========================================================
# ETAT CAPTEURS
# None = inconnue
# 1    = presente
# 0    = absente
# ==========================================================
etat_capteurs = {
    "Boite 1": None,
    "Boite 2": None,
    "Boite 3": None,
    "Boite 4": None
}

sliders = []
labels_valeurs = []
labels_capteurs = {}

# boutons boites pour activation/desactivation
btn_box1 = None
btn_box2 = None
btn_box3 = None
btn_box4 = None
btn_home = None
btn_stop = None
btn_start = None

# autres widgets
btn_lire_capteurs = None
label_etat_systeme = None
label_connexion = None
label_utilisateur = None
label_role = None
message = None
combo_ports = None

# ==========================================================
# CODE PROVISOIRE DE CONNEXION IHM
# Comptes temporaires en attendant les vraies infos
# ==========================================================
utilisateurs_provisoires = {
    "admin": {
        "mot_de_passe": "admin123",
        "role": "Administrateur"
    },
    "operateur": {
        "mot_de_passe": "robot123",
        "role": "Operateur"
    },
    "etudiant": {
        "mot_de_passe": "test123",
        "role": "Etudiant"
    }
}

utilisateur_connecte = ""
role_connecte = ""
fenetre_connexion = None
entry_identifiant = None
entry_motdepasse = None
combo_role = None
label_message_connexion = None
btn_afficher_mdp = None
motdepasse_visible = False

# ==========================================================
# OUTILS
# ==========================================================
def mettre_message(texte, couleur="blue"):
    if message is not None:
        message.config(text=texte, fg=couleur)
        fenetre.update_idletasks()


def est_connecte():
    global arduino
    return arduino is not None and arduino.is_open


def mettre_a_jour_infos_session():
    if label_utilisateur is not None:
        texte_user = "Utilisateur : " + (utilisateur_connecte if utilisateur_connecte else "Aucun")
        label_utilisateur.config(text=texte_user)

    if label_role is not None:
        texte_role = "Role : " + (role_connecte if role_connecte else "Aucun")
        label_role.config(text=texte_role)


def mettre_a_jour_droits_selon_role():
    # Si aucun role, tout ce qui pilote est bloque
    if role_connecte == "":
        if btn_start is not None:
            btn_start.config(state="disabled")
        if btn_stop is not None:
            btn_stop.config(state="disabled")
        for bouton in [btn_box1, btn_box2, btn_box3, btn_box4, btn_home]:
            if bouton is not None:
                bouton.config(state="disabled")
        return

    # Administrateur : tous les controles
    if role_connecte == "Administrateur":
        if btn_start is not None:
            btn_start.config(state="normal")
        if btn_stop is not None:
            btn_stop.config(state="normal")

        if systeme_actif_ui:
            for bouton in [btn_box1, btn_box2, btn_box3, btn_box4, btn_home]:
                if bouton is not None:
                    bouton.config(state="normal")
        else:
            for bouton in [btn_box1, btn_box2, btn_box3, btn_box4, btn_home]:
                if bouton is not None:
                    bouton.config(state="disabled")
        return

    # Operateur : demarrage, arret et pilotage
    if role_connecte == "Operateur":
        if btn_start is not None:
            btn_start.config(state="normal")
        if btn_stop is not None:
            btn_stop.config(state="normal")

        if systeme_actif_ui:
            for bouton in [btn_box1, btn_box2, btn_box3, btn_box4, btn_home]:
                if bouton is not None:
                    bouton.config(state="normal")
        else:
            for bouton in [btn_box1, btn_box2, btn_box3, btn_box4, btn_home]:
                if bouton is not None:
                    bouton.config(state="disabled")
        return

    # Etudiant : lecture et observation seulement
    if role_connecte == "Etudiant":
        if btn_start is not None:
            btn_start.config(state="disabled")
        if btn_stop is not None:
            btn_stop.config(state="disabled")
        for bouton in [btn_box1, btn_box2, btn_box3, btn_box4, btn_home]:
            if bouton is not None:
                bouton.config(state="disabled")
        return


def set_systeme_ui(actif, source="local"):
    global systeme_actif_ui
    systeme_actif_ui = actif

    if systeme_actif_ui:
        label_etat_systeme.config(text="Etat : EN MARCHE", fg="green")
        if source == "local":
            mettre_message("Systeme mis en marche localement.", "green")
    else:
        label_etat_systeme.config(text="Etat : ARRETE", fg="red")
        if source == "local":
            mettre_message("Systeme arrete localement.", "red")

    mettre_a_jour_droits_selon_role()
    fenetre.update_idletasks()


# ==========================================================
# PORTS COM
# ==========================================================
def lister_ports():
    ports = serial.tools.list_ports.comports()
    liste_ports = [port.device for port in ports]

    combo_ports["values"] = liste_ports

    if liste_ports:
        combo_ports.current(0)
        mettre_message("Ports COM detectes avec succes.", "blue")
    else:
        combo_ports.set("")
        mettre_message("Aucun port COM detecte.", "red")


def connecter_port():
    global arduino, lecture_active

    port = port_selectionne.get().strip()

    if port == "":
        label_connexion.config(text="Non connecte", fg="red")
        mettre_message("Veuillez choisir un port COM.", "red")
        return

    try:
        if est_connecte():
            arduino.close()

        arduino = serial.Serial(port, BAUDRATE, timeout=1)
        time.sleep(2)

        try:
            arduino.reset_input_buffer()
            arduino.reset_output_buffer()
        except Exception:
            pass

        label_connexion.config(text="Connecte a " + port, fg="green")
        mettre_message(
            "Connexion reelle reussie au port " + port + " a " + str(BAUDRATE) + " bauds.",
            "green"
        )

        lecture_active = True
        thread_lecture = threading.Thread(target=lecture_serie_continue, daemon=True)
        thread_lecture.start()

        fenetre.after(400, demander_capteurs)

    except Exception as e:
        label_connexion.config(text="Non connecte", fg="red")
        mettre_message("Erreur de connexion : " + str(e), "red")


def deconnecter_port():
    global arduino, lecture_active

    try:
        lecture_active = False

        if est_connecte():
            arduino.close()

        label_connexion.config(text="Non connecte", fg="red")
        mettre_message("Connexion serie fermee.", "blue")

    except Exception as e:
        mettre_message("Erreur lors de la fermeture : " + str(e), "red")


# ==========================================================
# COMMUNICATION SERIE
# ==========================================================
def envoyer_commande(cmd):
    global arduino

    if not est_connecte():
        mettre_message("Arduino non connecte. Impossible d'envoyer la commande.", "red")
        return False

    try:
        arduino.write((cmd + "\n").encode("utf-8"))
        arduino.flush()
        print("ENVOI ARDUINO ->", repr(cmd))
        return True
    except Exception as e:
        mettre_message("Erreur d'envoi : " + str(e), "red")
        return False


def lecture_serie_continue():
    global lecture_active, arduino

    while lecture_active:
        try:
            if est_connecte() and arduino.in_waiting > 0:
                ligne = arduino.readline().decode("utf-8", errors="ignore").strip()

                if ligne != "":
                    print("RECU ARDUINO ->", repr(ligne))
                    fenetre.after(0, traiter_reponse_arduino, ligne)

        except Exception as e:
            print("ERREUR LECTURE SERIE ->", e)

        time.sleep(0.05)


def traiter_reponse_arduino(reponse):
    reponse = reponse.strip()

    if reponse == "READY":
        mettre_message("Arduino pret.", "green")
        return

    if "SENSORS:" in reponse:
        index = reponse.find("SENSORS:")
        contenu = reponse[index + len("SENSORS:"):].strip()
        valeurs = contenu.split(",")

        if len(valeurs) == 4:
            try:
                etat_capteurs["Boite 1"] = int(valeurs[0].strip())
                etat_capteurs["Boite 2"] = int(valeurs[1].strip())
                etat_capteurs["Boite 3"] = int(valeurs[2].strip())
                etat_capteurs["Boite 4"] = int(valeurs[3].strip())

                mettre_a_jour_affichage_capteurs()
                mettre_message("Capteurs reels mis a jour : " + reponse, "green")
            except Exception:
                mettre_message("Reponse capteurs invalide : " + reponse, "red")
        else:
            mettre_message("Format capteurs incorrect : " + reponse, "red")
        return

    if reponse.startswith("OK:START"):
        set_systeme_ui(True, source="arduino")
        mettre_message("Arduino confirme : " + reponse, "green")
        return

    if reponse.startswith("DONE:START"):
        mettre_message("Demarrage termine : " + reponse, "green")
        return

    if reponse.startswith("OK:STOP"):
        set_systeme_ui(False, source="arduino")
        mettre_message("Arduino confirme : " + reponse, "red")
        return

    if reponse.startswith("DONE:STOP"):
        mettre_message("Arret termine : " + reponse, "red")
        return

    if reponse.startswith("ERROR:SYSTEM_STOPPED"):
        set_systeme_ui(False, source="arduino")
        mettre_message("Arduino indique que le systeme est arrete.", "red")
        return

    if reponse.startswith("OK:"):
        mettre_message("Arduino confirme : " + reponse, "blue")
        return

    if reponse.startswith("DONE:"):
        mettre_message("Sequence terminee : " + reponse, "green")
        demander_capteurs()
        return

    if reponse.startswith("ABORTED:"):
        set_systeme_ui(False, source="arduino")
        mettre_message("Sequence interrompue : " + reponse, "red")
        return

    if reponse.startswith("ERROR:"):
        mettre_message("Arduino signale une erreur : " + reponse, "red")
        return

    mettre_message("Reponse Arduino : " + reponse, "blue")


def demander_capteurs():
    if envoyer_commande("GET_SENSORS"):
        mettre_message("Demande des capteurs envoyee...", "purple")


# ==========================================================
# CAPTEURS
# ==========================================================
def mettre_a_jour_affichage_capteurs():
    for nom_boite, etat in etat_capteurs.items():
        if etat is None:
            labels_capteurs[nom_boite].config(text="Inconnue", fg="gray")
        elif etat == 1:
            labels_capteurs[nom_boite].config(text="Presente", fg="green")
        else:
            labels_capteurs[nom_boite].config(text="Absente", fg="red")


# ==========================================================
# AFFICHAGE LOCAL DES SERVOS
# ==========================================================
def mettre_a_jour_servo(servo_id, valeur):
    valeur = int(float(valeur))
    labels_valeurs[servo_id].config(text=str(valeur) + "°")
    mettre_message("Servo " + str(servo_id + 1) + " regle a " + str(valeur) + "°", "blue")


def appliquer_position_locale(nom_position):
    if nom_position not in positions:
        return

    valeurs = positions[nom_position]

    for i in range(6):
        sliders[i].set(valeurs[i])
        labels_valeurs[i].config(text=str(valeurs[i]) + "°")


# ==========================================================
# START / STOP
# ==========================================================
def start_action():
    if est_connecte():
        if envoyer_commande("START"):
            mettre_message("Commande START envoyee.", "green")
    else:
        set_systeme_ui(True, source="local")
        mettre_message("Mode simulation : START active localement.", "green")


def stop_action():
    if est_connecte():
        if envoyer_commande("STOP"):
            mettre_message("Commande STOP envoyee.", "red")
    else:
        set_systeme_ui(False, source="local")
        mettre_message("Mode simulation : STOP active localement.", "red")


# ==========================================================
# ACTIONS IHM -> ARDUINO
# ==========================================================
def executer_boite(numero):
    nom_boite = "Boite " + str(numero)

    if not systeme_actif_ui:
        mettre_message("Systeme arrete. Appuie d'abord sur START.", "red")
        return

    if etat_capteurs[nom_boite] is None:
        demander_capteurs()
        mettre_message("Lecture des capteurs en cours, reclique ensuite sur " + nom_boite + ".", "orange")
        return

    if etat_capteurs[nom_boite] == 0:
        mettre_message(nom_boite + " absente : sequence impossible.", "red")
        return

    commande = "BOX" + str(numero)

    if envoyer_commande(commande):
        appliquer_position_locale(nom_boite)
        mettre_message("Commande " + commande + " envoyee.", "purple")


def retour_home():
    if not systeme_actif_ui:
        mettre_message("Systeme arrete. HOME impossible tant que le systeme est stoppe.", "red")
        return

    if envoyer_commande("HOME"):
        appliquer_position_locale("Repos")
        mettre_message("Commande HOME envoyee.", "purple")


# ==========================================================
# CODE PROVISOIRE DE CONNEXION IHM
# ==========================================================
def basculer_visibilite_motdepasse():
    global motdepasse_visible

    motdepasse_visible = not motdepasse_visible

    if motdepasse_visible:
        entry_motdepasse.config(show="")
        btn_afficher_mdp.config(text="Masquer")
    else:
        entry_motdepasse.config(show="*")
        btn_afficher_mdp.config(text="Voir")


def ouvrir_fenetre_connexion():
    global fenetre_connexion
    global entry_identifiant, entry_motdepasse, combo_role, label_message_connexion, btn_afficher_mdp

    fenetre_connexion = tk.Toplevel(fenetre)
    fenetre_connexion.title("Authentification utilisateur")
    fenetre_connexion.geometry("540x380+430+180")
    fenetre_connexion.configure(bg="lightgray")
    fenetre_connexion.resizable(False, False)
    fenetre_connexion.grab_set()
    fenetre_connexion.protocol("WM_DELETE_WINDOW", quitter_application)

    titre_connexion = tk.Label(
        fenetre_connexion,
        text="Connexion a l'IHM",
        font=("Arial", 20, "bold"),
        bg="lightgray",
        fg="navy"
    )
    titre_connexion.pack(pady=15)

    cadre_formulaire = tk.Frame(fenetre_connexion, bg="lightgray")
    cadre_formulaire.pack(pady=10)

    tk.Label(
        cadre_formulaire,
        text="Identifiant :",
        font=("Arial", 12, "bold"),
        bg="lightgray",
        width=15,
        anchor="w"
    ).grid(row=0, column=0, padx=10, pady=8)

    entry_identifiant = tk.Entry(cadre_formulaire, font=("Arial", 12), width=25)
    entry_identifiant.grid(row=0, column=1, padx=10, pady=8)

    tk.Label(
        cadre_formulaire,
        text="Mot de passe :",
        font=("Arial", 12, "bold"),
        bg="lightgray",
        width=15,
        anchor="w"
    ).grid(row=1, column=0, padx=10, pady=8)

    cadre_mdp = tk.Frame(cadre_formulaire, bg="lightgray")
    cadre_mdp.grid(row=1, column=1, padx=10, pady=8)

    entry_motdepasse = tk.Entry(cadre_mdp, font=("Arial", 12), width=18, show="*")
    entry_motdepasse.pack(side="left", padx=(0, 6))

    btn_afficher_mdp = tk.Button(
        cadre_mdp,
        text="Voir",
        font=("Arial", 10, "bold"),
        width=8,
        command=basculer_visibilite_motdepasse
    )
    btn_afficher_mdp.pack(side="left")

    tk.Label(
        cadre_formulaire,
        text="Role :",
        font=("Arial", 12, "bold"),
        bg="lightgray",
        width=15,
        anchor="w"
    ).grid(row=2, column=0, padx=10, pady=8)

    combo_role = ttk.Combobox(
        cadre_formulaire,
        state="readonly",
        values=["Administrateur", "Operateur", "Etudiant"],
        width=22
    )
    combo_role.grid(row=2, column=1, padx=10, pady=8)

    label_message_connexion = tk.Label(
        fenetre_connexion,
        text="Veuillez vous identifier.",
        font=("Arial", 11, "bold"),
        bg="lightgray",
        fg="blue"
    )
    label_message_connexion.pack(pady=12)

    cadre_boutons_connexion = tk.Frame(fenetre_connexion, bg="lightgray")
    cadre_boutons_connexion.pack(pady=10)

    btn_connexion = tk.Button(
        cadre_boutons_connexion,
        text="Connexion",
        font=("Arial", 11, "bold"),
        width=12,
        bg="darkgreen",
        fg="white",
        command=valider_connexion_provisoire
    )
    btn_connexion.pack(side="left", padx=6)

    btn_reset = tk.Button(
        cadre_boutons_connexion,
        text="Reinitialiser",
        font=("Arial", 11, "bold"),
        width=12,
        command=reinitialiser_formulaire_connexion
    )
    btn_reset.pack(side="left", padx=6)

    btn_annuler = tk.Button(
        cadre_boutons_connexion,
        text="Annuler",
        font=("Arial", 11, "bold"),
        width=12,
        command=annuler_connexion
    )
    btn_annuler.pack(side="left", padx=6)

    btn_quitter_connexion = tk.Button(
        cadre_boutons_connexion,
        text="Quitter",
        font=("Arial", 11, "bold"),
        width=12,
        bg="black",
        fg="white",
        command=quitter_application
    )
    btn_quitter_connexion.pack(side="left", padx=6)

    entry_identifiant.focus_set()


def reinitialiser_formulaire_connexion():
    global motdepasse_visible

    entry_identifiant.delete(0, tk.END)
    entry_motdepasse.delete(0, tk.END)
    combo_role.set("")

    motdepasse_visible = False
    entry_motdepasse.config(show="*")
    btn_afficher_mdp.config(text="Voir")

    label_message_connexion.config(text="Formulaire reinitialise.", fg="blue")
    entry_identifiant.focus_set()


def annuler_connexion():
    global motdepasse_visible

    entry_identifiant.delete(0, tk.END)
    entry_motdepasse.delete(0, tk.END)
    combo_role.set("")

    motdepasse_visible = False
    entry_motdepasse.config(show="*")
    btn_afficher_mdp.config(text="Voir")

    label_message_connexion.config(text="Saisie annulee.", fg="orange")
    entry_identifiant.focus_set()


def valider_connexion_provisoire():
    global utilisateur_connecte, role_connecte

    identifiant = entry_identifiant.get().strip()
    motdepasse = entry_motdepasse.get().strip()
    role_choisi = combo_role.get().strip()

    if identifiant == "" or motdepasse == "" or role_choisi == "":
        label_message_connexion.config(
            text="Veuillez remplir tous les champs obligatoires.",
            fg="red"
        )
        return

    if identifiant not in utilisateurs_provisoires:
        label_message_connexion.config(
            text="Identifiant inconnu.",
            fg="red"
        )
        return

    infos = utilisateurs_provisoires[identifiant]

    if motdepasse != infos["mot_de_passe"]:
        label_message_connexion.config(
            text="Mot de passe incorrect.",
            fg="red"
        )
        entry_motdepasse.delete(0, tk.END)
        return

    if role_choisi != infos["role"]:
        label_message_connexion.config(
            text="Role non autorise pour cet utilisateur.",
            fg="red"
        )
        return

    utilisateur_connecte = identifiant
    role_connecte = role_choisi

    mettre_a_jour_infos_session()
    mettre_a_jour_droits_selon_role()

    label_message_connexion.config(
        text="Connexion reussie.",
        fg="green"
    )

    fenetre.deiconify()
    fenetre_connexion.destroy()

    mettre_message(
        "Connexion reussie : " + utilisateur_connecte + " (" + role_connecte + ").",
        "green"
    )


# ==========================================================
# FERMETURE
# ==========================================================
def quitter_application():
    global arduino, lecture_active

    lecture_active = False

    try:
        if est_connecte():
            arduino.close()
    except Exception:
        pass

    try:
        fenetre.destroy()
    except Exception:
        pass


# ==========================================================
# FENETRE PRINCIPALE
# ==========================================================
fenetre = tk.Tk()
fenetre.title("IHM Robot a pince")
fenetre.geometry("1380x900+30+20")
fenetre.configure(bg="lightgray")

fenetre.lift()
fenetre.attributes("-topmost", True)
fenetre.after(1000, lambda: fenetre.attributes("-topmost", False))

port_selectionne = tk.StringVar()

# On cache l'IHM principale tant que la connexion n'est pas validee
fenetre.withdraw()

# ==========================================================
# EN-TETE
# ==========================================================
titre = tk.Label(
    fenetre,
    text="Projet Robots - IHM Python",
    font=("Arial", 24, "bold"),
    bg="lightgray"
)
titre.pack(pady=10)

sous_titre = tk.Label(
    fenetre,
    text="Pilotage reel du robot via Arduino",
    font=("Arial", 15),
    bg="lightgray"
)
sous_titre.pack(pady=5)

# ==========================================================
# INFOS SESSION
# ==========================================================
cadre_session = tk.Frame(fenetre, bg="lightgray")
cadre_session.pack(pady=4)

label_utilisateur = tk.Label(
    cadre_session,
    text="Utilisateur : Aucun",
    font=("Arial", 12, "bold"),
    bg="lightgray",
    fg="navy"
)
label_utilisateur.pack(side="left", padx=15)

label_role = tk.Label(
    cadre_session,
    text="Role : Aucun",
    font=("Arial", 12, "bold"),
    bg="lightgray",
    fg="darkmagenta"
)
label_role.pack(side="left", padx=15)

# ==========================================================
# ETAT SYSTEME
# ==========================================================
label_etat_systeme = tk.Label(
    fenetre,
    text="Etat : EN MARCHE",
    font=("Arial", 14, "bold"),
    bg="lightgray",
    fg="green"
)
label_etat_systeme.pack(pady=6)

# ==========================================================
# ZONE CONNEXION USB
# ==========================================================
cadre_usb = tk.LabelFrame(
    fenetre,
    text="Connexion USB Arduino",
    font=("Arial", 13, "bold"),
    bg="lightgray",
    padx=15,
    pady=10
)
cadre_usb.pack(pady=10)

tk.Label(
    cadre_usb,
    text="Port COM :",
    font=("Arial", 12, "bold"),
    bg="lightgray"
).pack(side="left", padx=8)

combo_ports = ttk.Combobox(
    cadre_usb,
    textvariable=port_selectionne,
    state="readonly",
    width=18
)
combo_ports.pack(side="left", padx=8)

btn_actualiser = tk.Button(
    cadre_usb,
    text="Actualiser",
    font=("Arial", 11, "bold"),
    command=lister_ports
)
btn_actualiser.pack(side="left", padx=8)

btn_connecter = tk.Button(
    cadre_usb,
    text="Connecter",
    font=("Arial", 11, "bold"),
    bg="darkgreen",
    fg="white",
    command=connecter_port
)
btn_connecter.pack(side="left", padx=8)

btn_deconnecter = tk.Button(
    cadre_usb,
    text="Deconnecter",
    font=("Arial", 11, "bold"),
    bg="darkred",
    fg="white",
    command=deconnecter_port
)
btn_deconnecter.pack(side="left", padx=8)

btn_lire_capteurs = tk.Button(
    cadre_usb,
    text="Lire capteurs",
    font=("Arial", 11, "bold"),
    bg="navy",
    fg="white",
    command=demander_capteurs
)
btn_lire_capteurs.pack(side="left", padx=8)

label_connexion = tk.Label(
    cadre_usb,
    text="Non connecte",
    font=("Arial", 12, "bold"),
    bg="lightgray",
    fg="red"
)
label_connexion.pack(side="left", padx=15)

# ==========================================================
# ZONE CENTRALE
# ==========================================================
zone_centrale = tk.Frame(fenetre, bg="lightgray")
zone_centrale.pack(pady=10, fill="both", expand=True)

# ==========================================================
# CADRE SERVOS
# ==========================================================
cadre_servos = tk.LabelFrame(
    zone_centrale,
    text="Affichage des servomoteurs",
    font=("Arial", 14, "bold"),
    bg="lightgray",
    padx=15,
    pady=15
)
cadre_servos.pack(side="left", padx=20, pady=10, fill="both", expand=True)

servos = [
    {"nom": "Servo 1 - Socle", "min": 0, "max": 180, "valeur": 40},
    {"nom": "Servo 2 - 1er bras", "min": 0, "max": 180, "valeur": 130},
    {"nom": "Servo 3 - 2eme bras", "min": 0, "max": 180, "valeur": 180},
    {"nom": "Servo 4 - Rotation", "min": 0, "max": 180, "valeur": 80},
    {"nom": "Servo 5 - Bras pince", "min": 0, "max": 180, "valeur": 0},
    {"nom": "Servo 6 - Pince", "min": 0, "max": 180, "valeur": 120},
]

for i, servo in enumerate(servos):
    ligne = tk.Frame(cadre_servos, bg="lightgray")
    ligne.pack(fill="x", pady=8)

    label_nom = tk.Label(
        ligne,
        text=servo["nom"],
        font=("Arial", 12, "bold"),
        width=20,
        anchor="w",
        bg="lightgray"
    )
    label_nom.pack(side="left", padx=8)

    slider = tk.Scale(
        ligne,
        from_=servo["min"],
        to=servo["max"],
        orient="horizontal",
        length=320,
        resolution=1,
        command=lambda valeur, idx=i: mettre_a_jour_servo(idx, valeur)
    )
    slider.set(servo["valeur"])
    slider.pack(side="left", padx=8)

    label_valeur = tk.Label(
        ligne,
        text=str(servo["valeur"]) + "°",
        font=("Arial", 12, "bold"),
        width=7,
        bg="lightgray",
        fg="darkgreen"
    )
    label_valeur.pack(side="left", padx=8)

    sliders.append(slider)
    labels_valeurs.append(label_valeur)

# ==========================================================
# CADRE CAPTEURS
# ==========================================================
cadre_capteurs = tk.LabelFrame(
    zone_centrale,
    text="Etat reel des capteurs infrarouges",
    font=("Arial", 14, "bold"),
    bg="lightgray",
    padx=20,
    pady=20
)
cadre_capteurs.pack(side="right", padx=20, pady=10, fill="y")

for nom_boite in ["Boite 1", "Boite 2", "Boite 3", "Boite 4"]:
    ligne = tk.Frame(cadre_capteurs, bg="lightgray")
    ligne.pack(pady=10, anchor="w")

    label_nom = tk.Label(
        ligne,
        text=nom_boite,
        font=("Arial", 13, "bold"),
        width=10,
        anchor="w",
        bg="lightgray"
    )
    label_nom.pack(side="left", padx=8)

    label_etat = tk.Label(
        ligne,
        text="Inconnue",
        font=("Arial", 13, "bold"),
        width=10,
        bg="lightgray",
        fg="gray"
    )
    label_etat.pack(side="left", padx=8)

    labels_capteurs[nom_boite] = label_etat

# ==========================================================
# MESSAGE
# ==========================================================
message = tk.Label(
    fenetre,
    text="IHM prete avec gestion START / STOP et connexion provisoire.",
    font=("Arial", 14, "bold"),
    bg="lightgray",
    fg="blue",
    relief="solid",
    bd=1,
    padx=10,
    pady=8,
    width=105
)
message.pack(pady=10)

# ==========================================================
# BOUTONS BAS
# ==========================================================
frame_boutons = tk.Frame(fenetre, bg="lightgray")
frame_boutons.pack(pady=10)

btn_start = tk.Button(
    frame_boutons,
    text="Start",
    font=("Arial", 11, "bold"),
    width=12,
    bg="darkgreen",
    fg="white",
    command=start_action
)
btn_start.pack(side="left", padx=8, pady=5)

btn_stop = tk.Button(
    frame_boutons,
    text="Stop",
    font=("Arial", 11, "bold"),
    width=12,
    bg="red",
    fg="white",
    command=stop_action
)
btn_stop.pack(side="left", padx=8, pady=5)

btn_box1 = tk.Button(
    frame_boutons,
    text="Boite 1",
    font=("Arial", 11, "bold"),
    width=12,
    bg="green",
    fg="white",
    command=lambda: executer_boite(1)
)
btn_box1.pack(side="left", padx=8, pady=5)

btn_box2 = tk.Button(
    frame_boutons,
    text="Boite 2",
    font=("Arial", 11, "bold"),
    width=12,
    bg="green",
    fg="white",
    command=lambda: executer_boite(2)
)
btn_box2.pack(side="left", padx=8, pady=5)

btn_box3 = tk.Button(
    frame_boutons,
    text="Boite 3",
    font=("Arial", 11, "bold"),
    width=12,
    bg="green",
    fg="white",
    command=lambda: executer_boite(3)
)
btn_box3.pack(side="left", padx=8, pady=5)

btn_box4 = tk.Button(
    frame_boutons,
    text="Boite 4",
    font=("Arial", 11, "bold"),
    width=12,
    bg="green",
    fg="white",
    command=lambda: executer_boite(4)
)
btn_box4.pack(side="left", padx=8, pady=5)

btn_home = tk.Button(
    frame_boutons,
    text="Repos",
    font=("Arial", 11, "bold"),
    width=12,
    bg="blue",
    fg="white",
    command=retour_home
)
btn_home.pack(side="left", padx=8, pady=5)

btn_quitter = tk.Button(
    frame_boutons,
    text="Quitter",
    font=("Arial", 11, "bold"),
    width=12,
    bg="black",
    fg="white",
    command=quitter_application
)
btn_quitter.pack(side="left", padx=8, pady=5)

# ==========================================================
# LANCEMENT
# ==========================================================
mettre_a_jour_affichage_capteurs()
lister_ports()
mettre_a_jour_infos_session()
set_systeme_ui(True, source="local")
mettre_a_jour_droits_selon_role()
ouvrir_fenetre_connexion()

fenetre.mainloop()