import tkinter as tk
from tkinter import ttk
import serial.tools.list_ports

# =========================
# DONNEES DES POSITIONS
# =========================
positions = {
    "Boite 1": [160, 65, 175, 80, 40, 40],
    "Boite 2": [135, 65, 175, 80, 40, 40],
    "Boite 3": [110, 65, 175, 80, 40, 40],
    "Boite 4": [85, 65, 175, 80, 40, 40],
    "Palette": [50, 65, 175, 80, 40, 40],
    "Repos": [40, 130, 180, 80, 0, 40],
    "Stop": [40, 130, 180, 80, 0, 40]
}

# 1 = presente, 0 = absente
etat_capteurs = {
    "Boite 1": 1,
    "Boite 2": 1,
    "Boite 3": 1,
    "Boite 4": 1
}

sliders = []
labels_valeurs = []
labels_capteurs = {}

# =========================
# FONCTIONS PORT COM
# =========================
def lister_ports():
    ports = serial.tools.list_ports.comports()
    liste_ports = [port.device for port in ports]

    combo_ports["values"] = liste_ports

    if liste_ports:
        combo_ports.current(0)
        message.config(text="Ports COM detectes avec succes.", fg="blue")
    else:
        combo_ports.set("")
        message.config(text="Aucun port COM detecte.", fg="red")

    fenetre.update_idletasks()


def connecter_port():
    port = port_selectionne.get().strip()

    if port == "":
        label_connexion.config(text="Non connecte", fg="red")
        message.config(text="Veuillez choisir un port COM.", fg="red")
        fenetre.update_idletasks()
        return

    label_connexion.config(text="Connecte a " + port, fg="green")
    message.config(text="Connexion simulee au port " + port, fg="green")
    fenetre.update_idletasks()


# =========================
# FONCTIONS IHM
# =========================
def mettre_a_jour_servo(servo_id, valeur):
    valeur = int(float(valeur))
    labels_valeurs[servo_id].config(text=str(valeur) + "°")
    message.config(
        text="Servo " + str(servo_id + 1) + " regle a " + str(valeur) + "°",
        fg="blue"
    )
    fenetre.update_idletasks()


def mettre_a_jour_affichage_capteurs():
    for nom_boite, etat in etat_capteurs.items():
        if etat == 1:
            labels_capteurs[nom_boite].config(text="Presente", fg="green")
        else:
            labels_capteurs[nom_boite].config(text="Absente", fg="red")


def basculer_capteur(nom_boite):
    if etat_capteurs[nom_boite] == 1:
        etat_capteurs[nom_boite] = 0
    else:
        etat_capteurs[nom_boite] = 1

    mettre_a_jour_affichage_capteurs()
    message.config(text="Etat capteur modifie pour " + nom_boite, fg="purple")
    fenetre.update_idletasks()


def appliquer_position(nom_position):
    if nom_position in etat_capteurs:
        if etat_capteurs[nom_position] == 0:
            message.config(
                text=nom_position + " absente : sequence impossible.",
                fg="red"
            )
            fenetre.update_idletasks()
            return

    valeurs = positions[nom_position]

    for i in range(6):
        sliders[i].set(valeurs[i])
        labels_valeurs[i].config(text=str(valeurs[i]) + "°")

    if nom_position == "Stop":
        message.config(text="Arret demande : retour a une position sure.", fg="red")
    else:
        message.config(text="Position '" + nom_position + "' appliquee.", fg="green")

    fenetre.update_idletasks()


def quitter_application():
    fenetre.destroy()


# =========================
# FENETRE PRINCIPALE
# =========================
fenetre = tk.Tk()
fenetre.title("IHM Robot a pince")
fenetre.geometry("1350x820+40+20")
fenetre.configure(bg="lightgray")

fenetre.lift()
fenetre.attributes("-topmost", True)
fenetre.after(1000, lambda: fenetre.attributes("-topmost", False))

port_selectionne = tk.StringVar()

# =========================
# EN-TETE
# =========================
titre = tk.Label(
    fenetre,
    text="Projet Robots - IHM Python",
    font=("Arial", 24, "bold"),
    bg="lightgray"
)
titre.pack(pady=10)

sous_titre = tk.Label(
    fenetre,
    text="Pilotage manuel des 6 servomoteurs",
    font=("Arial", 15),
    bg="lightgray"
)
sous_titre.pack(pady=5)

# =========================
# ZONE CONNEXION USB
# =========================
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

label_connexion = tk.Label(
    cadre_usb,
    text="Non connecte",
    font=("Arial", 12, "bold"),
    bg="lightgray",
    fg="red"
)
label_connexion.pack(side="left", padx=15)

# =========================
# ZONE CENTRALE
# =========================
zone_centrale = tk.Frame(fenetre, bg="lightgray")
zone_centrale.pack(pady=10, fill="both", expand=True)

# =========================
# CADRE SERVOS
# =========================
cadre_servos = tk.LabelFrame(
    zone_centrale,
    text="Commande des servomoteurs",
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
    {"nom": "Servo 6 - Pince", "min": 0, "max": 180, "valeur": 40},
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

# =========================
# CADRE CAPTEURS
# =========================
cadre_capteurs = tk.LabelFrame(
    zone_centrale,
    text="Etat des capteurs infrarouges",
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
        text="",
        font=("Arial", 13, "bold"),
        width=10,
        bg="lightgray"
    )
    label_etat.pack(side="left", padx=8)

    bouton_test = tk.Button(
        ligne,
        text="Basculer",
        font=("Arial", 11, "bold"),
        width=10,
        command=lambda n=nom_boite: basculer_capteur(n)
    )
    bouton_test.pack(side="left", padx=8)

    labels_capteurs[nom_boite] = label_etat

mettre_a_jour_affichage_capteurs()

# =========================
# MESSAGE
# =========================
message = tk.Label(
    fenetre,
    text="Les sliders, boutons, capteurs et ports COM sont prets.",
    font=("Arial", 14, "bold"),
    bg="lightgray",
    fg="blue",
    relief="solid",
    bd=1,
    padx=10,
    pady=8,
    width=75
)
message.pack(pady=10)

# =========================
# BOUTONS BAS
# =========================
frame_boutons = tk.Frame(fenetre, bg="lightgray")
frame_boutons.pack(pady=10)

liste_boutons = [
    ("Boite 1", "green"),
    ("Boite 2", "green"),
    ("Boite 3", "green"),
    ("Boite 4", "green"),
    ("Palette", "orange"),
    ("Repos", "blue"),
    ("Stop", "red")
]

for nom, couleur in liste_boutons:
    bouton = tk.Button(
        frame_boutons,
        text=nom,
        font=("Arial", 11, "bold"),
        width=12,
        bg=couleur,
        fg="white",
        command=lambda n=nom: appliquer_position(n)
    )
    bouton.pack(side="left", padx=8, pady=5)

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

# Charger les ports au lancement
lister_ports()

fenetre.mainloop()