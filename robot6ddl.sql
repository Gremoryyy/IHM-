-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Hôte : localhost
-- Généré le : lun. 23 mars 2026 à 11:37
-- Version du serveur : 10.4.32-MariaDB
-- Version de PHP : 8.2.12

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Base de données : `robot6ddl`
--

-- --------------------------------------------------------

--
-- Structure de la table `connection_logs`
--

CREATE TABLE `connection_logs` (
  `id` int(11) NOT NULL,
  `user_id` int(11) DEFAULT NULL,
  `username` varchar(50) NOT NULL,
  `success` tinyint(1) NOT NULL,
  `ip_address` varchar(45) DEFAULT NULL,
  `log_time` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Structure de la table `robots`
--

CREATE TABLE `robots` (
  `id` int(11) NOT NULL,
  `robot_name` varchar(30) NOT NULL,
  `esp32_role` varchar(20) NOT NULL,
  `status` varchar(20) NOT NULL DEFAULT 'inactive',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Déchargement des données de la table `robots`
--

INSERT INTO `robots` (`id`, `robot_name`, `esp32_role`, `status`, `created_at`) VALUES
(1, 'robot_1', 'master', 'inactive', '2026-03-23 10:02:58'),
(2, 'robot_2', 'slave', 'inactive', '2026-03-23 10:02:58'),
(3, 'robot_3', 'slave', 'inactive', '2026-03-23 10:02:58');

-- --------------------------------------------------------

--
-- Structure de la table `robot_action_logs`
--

CREATE TABLE `robot_action_logs` (
  `id` int(11) NOT NULL,
  `robot_id` int(11) NOT NULL,
  `user_id` int(11) DEFAULT NULL,
  `action_type` varchar(50) NOT NULL,
  `commande` varchar(50) DEFAULT NULL,
  `box_number` int(11) DEFAULT NULL,
  `status` varchar(20) NOT NULL DEFAULT 'success',
  `details` varchar(255) DEFAULT NULL,
  `action_time` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Structure de la table `robot_commands`
--

CREATE TABLE `robot_commands` (
  `id` int(11) NOT NULL,
  `robot_id` int(11) NOT NULL,
  `user_id` int(11) DEFAULT NULL,
  `command_type` varchar(50) NOT NULL,
  `target_robot_id` int(11) DEFAULT NULL,
  `box_number` int(11) DEFAULT NULL,
  `payload` text DEFAULT NULL,
  `status` varchar(20) NOT NULL DEFAULT 'pending',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `executed_at` timestamp NULL DEFAULT NULL,
  `details` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Structure de la table `robot_positions`
--

CREATE TABLE `robot_positions` (
  `id` int(11) NOT NULL,
  `robot_id` int(11) NOT NULL,
  `commande` varchar(50) NOT NULL,
  `box_number` int(11) DEFAULT NULL,
  `step_order` int(11) NOT NULL,
  `servo_6_angle` int(11) NOT NULL,
  `servo_7_angle` int(11) NOT NULL,
  `servo_8_angle` int(11) NOT NULL,
  `servo_9_angle` int(11) NOT NULL,
  `servo_10_angle` int(11) NOT NULL,
  `servo_11_angle` int(11) NOT NULL,
  `speed_ms_per_degree` int(11) NOT NULL DEFAULT 10,
  `commentaire` varchar(255) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Déchargement des données de la table `robot_positions`
--

INSERT INTO `robot_positions` (`id`, `robot_id`, `commande`, `box_number`, `step_order`, `servo_6_angle`, `servo_7_angle`, `servo_8_angle`, `servo_9_angle`, `servo_10_angle`, `servo_11_angle`, `speed_ms_per_degree`, `commentaire`, `created_at`) VALUES
(1, 1, 'home', NULL, 1, 40, 130, 180, 80, 0, 120, 10, 'Position de départ réelle du code actuel', '2026-03-23 10:02:58'),
(2, 1, 'charger_boite', 1, 1, 160, 130, 180, 80, 0, 120, 10, 'Boite 1 - rotation base vers la boite', '2026-03-23 10:02:58'),
(3, 1, 'charger_boite', 1, 2, 160, 130, 180, 80, 0, 150, 10, 'Boite 1 - ouverture pince', '2026-03-23 10:02:58'),
(4, 1, 'charger_boite', 1, 3, 160, 60, 180, 80, 0, 150, 10, 'Boite 1 - descente bras 1', '2026-03-23 10:02:58'),
(5, 1, 'charger_boite', 1, 4, 160, 60, 170, 80, 0, 150, 10, 'Boite 1 - descente bras 2', '2026-03-23 10:02:58'),
(6, 1, 'charger_boite', 1, 5, 160, 60, 170, 80, 40, 150, 10, 'Boite 1 - bras pince en position', '2026-03-23 10:02:58'),
(7, 1, 'charger_boite', 1, 6, 160, 60, 170, 80, 40, 50, 10, 'Boite 1 - fermeture pince', '2026-03-23 10:02:58'),
(8, 1, 'charger_boite', 1, 7, 160, 60, 160, 80, 40, 50, 10, 'Boite 1 - relève bras', '2026-03-23 10:02:58'),
(9, 1, 'charger_boite', 1, 8, 40, 60, 160, 80, 40, 50, 10, 'Boite 1 - retour base', '2026-03-23 10:02:58'),
(10, 1, 'charger_boite', 1, 9, 40, 60, 160, 80, 40, 140, 10, 'Boite 1 - ouverture partielle pince', '2026-03-23 10:02:58'),
(11, 1, 'charger_boite', 1, 10, 40, 130, 160, 80, 40, 140, 10, 'Boite 1 - remontée bras 1', '2026-03-23 10:02:58'),
(12, 1, 'charger_boite', 1, 11, 40, 130, 180, 80, 0, 120, 10, 'Boite 1 - retour position de départ', '2026-03-23 10:02:58'),
(13, 1, 'charger_boite', 2, 1, 135, 130, 180, 80, 0, 120, 10, 'Boite 2 - rotation base vers la boite', '2026-03-23 10:02:58'),
(14, 1, 'charger_boite', 2, 2, 135, 130, 180, 80, 0, 150, 10, 'Boite 2 - ouverture pince', '2026-03-23 10:02:58'),
(15, 1, 'charger_boite', 2, 3, 135, 60, 180, 80, 0, 150, 10, 'Boite 2 - descente bras 1', '2026-03-23 10:02:58'),
(16, 1, 'charger_boite', 2, 4, 135, 60, 170, 80, 0, 150, 10, 'Boite 2 - descente bras 2', '2026-03-23 10:02:58'),
(17, 1, 'charger_boite', 2, 5, 135, 60, 170, 80, 40, 150, 10, 'Boite 2 - bras pince en position', '2026-03-23 10:02:58'),
(18, 1, 'charger_boite', 2, 6, 135, 60, 170, 80, 40, 50, 10, 'Boite 2 - fermeture pince', '2026-03-23 10:02:58'),
(19, 1, 'charger_boite', 2, 7, 135, 60, 160, 80, 40, 50, 10, 'Boite 2 - relève bras', '2026-03-23 10:02:58'),
(20, 1, 'charger_boite', 2, 8, 40, 60, 160, 80, 40, 50, 10, 'Boite 2 - retour base', '2026-03-23 10:02:58'),
(21, 1, 'charger_boite', 2, 9, 40, 60, 160, 80, 40, 140, 10, 'Boite 2 - ouverture partielle pince', '2026-03-23 10:02:58'),
(22, 1, 'charger_boite', 2, 10, 40, 130, 160, 80, 40, 140, 10, 'Boite 2 - remontée bras 1', '2026-03-23 10:02:58'),
(23, 1, 'charger_boite', 2, 11, 40, 130, 180, 80, 0, 120, 10, 'Boite 2 - retour position de départ', '2026-03-23 10:02:58'),
(24, 1, 'charger_boite', 3, 1, 110, 130, 180, 80, 0, 120, 10, 'Boite 3 - rotation base vers la boite', '2026-03-23 10:02:58'),
(25, 1, 'charger_boite', 3, 2, 110, 130, 180, 80, 0, 150, 10, 'Boite 3 - ouverture pince', '2026-03-23 10:02:58'),
(26, 1, 'charger_boite', 3, 3, 110, 60, 180, 80, 0, 150, 10, 'Boite 3 - descente bras 1', '2026-03-23 10:02:58'),
(27, 1, 'charger_boite', 3, 4, 110, 60, 170, 80, 0, 150, 10, 'Boite 3 - descente bras 2', '2026-03-23 10:02:58'),
(28, 1, 'charger_boite', 3, 5, 110, 60, 170, 80, 40, 150, 10, 'Boite 3 - bras pince en position', '2026-03-23 10:02:58'),
(29, 1, 'charger_boite', 3, 6, 110, 60, 170, 80, 40, 50, 10, 'Boite 3 - fermeture pince', '2026-03-23 10:02:58'),
(30, 1, 'charger_boite', 3, 7, 110, 60, 160, 80, 40, 50, 10, 'Boite 3 - relève bras', '2026-03-23 10:02:58'),
(31, 1, 'charger_boite', 3, 8, 40, 60, 160, 80, 40, 50, 10, 'Boite 3 - retour base', '2026-03-23 10:02:58'),
(32, 1, 'charger_boite', 3, 9, 40, 60, 160, 80, 40, 140, 10, 'Boite 3 - ouverture partielle pince', '2026-03-23 10:02:58'),
(33, 1, 'charger_boite', 3, 10, 40, 130, 160, 80, 40, 140, 10, 'Boite 3 - remontée bras 1', '2026-03-23 10:02:58'),
(34, 1, 'charger_boite', 3, 11, 40, 130, 180, 80, 0, 120, 10, 'Boite 3 - retour position de départ', '2026-03-23 10:02:58'),
(35, 1, 'charger_boite', 4, 1, 85, 130, 180, 80, 0, 120, 10, 'Boite 4 - rotation base vers la boite', '2026-03-23 10:02:58'),
(36, 1, 'charger_boite', 4, 2, 85, 130, 180, 80, 0, 150, 10, 'Boite 4 - ouverture pince', '2026-03-23 10:02:58'),
(37, 1, 'charger_boite', 4, 3, 85, 60, 180, 80, 0, 150, 10, 'Boite 4 - descente bras 1', '2026-03-23 10:02:58'),
(38, 1, 'charger_boite', 4, 4, 85, 60, 170, 80, 0, 150, 10, 'Boite 4 - descente bras 2', '2026-03-23 10:02:58'),
(39, 1, 'charger_boite', 4, 5, 85, 60, 170, 80, 40, 150, 10, 'Boite 4 - bras pince en position', '2026-03-23 10:02:58'),
(40, 1, 'charger_boite', 4, 6, 85, 60, 170, 80, 40, 50, 10, 'Boite 4 - fermeture pince', '2026-03-23 10:02:58'),
(41, 1, 'charger_boite', 4, 7, 85, 60, 160, 80, 40, 50, 10, 'Boite 4 - relève bras', '2026-03-23 10:02:58'),
(42, 1, 'charger_boite', 4, 8, 40, 60, 160, 80, 40, 50, 10, 'Boite 4 - retour base', '2026-03-23 10:02:58'),
(43, 1, 'charger_boite', 4, 9, 40, 60, 160, 80, 40, 140, 10, 'Boite 4 - ouverture partielle pince', '2026-03-23 10:02:58'),
(44, 1, 'charger_boite', 4, 10, 40, 130, 160, 80, 40, 140, 10, 'Boite 4 - remontée bras 1', '2026-03-23 10:02:58'),
(45, 1, 'charger_boite', 4, 11, 40, 130, 180, 80, 0, 120, 10, 'Boite 4 - retour position de départ', '2026-03-23 10:02:58');

-- --------------------------------------------------------

--
-- Structure de la table `sensor_states`
--

CREATE TABLE `sensor_states` (
  `id` int(11) NOT NULL,
  `robot_id` int(11) NOT NULL,
  `sensor_pin` int(11) NOT NULL,
  `box_number` int(11) NOT NULL,
  `box_present` tinyint(1) NOT NULL DEFAULT 0,
  `note` varchar(255) DEFAULT NULL,
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Déchargement des données de la table `sensor_states`
--

INSERT INTO `sensor_states` (`id`, `robot_id`, `sensor_pin`, `box_number`, `box_present`, `note`, `updated_at`) VALUES
(1, 1, 5, 1, 0, 'Capteur IR boite 1', '2026-03-23 10:02:58'),
(2, 1, 4, 2, 0, 'Capteur IR boite 2', '2026-03-23 10:02:58'),
(3, 1, 3, 3, 0, 'Capteur IR boite 3', '2026-03-23 10:02:58'),
(4, 1, 2, 4, 0, 'Capteur IR boite 4', '2026-03-23 10:02:58');

-- --------------------------------------------------------

--
-- Structure de la table `users`
--

CREATE TABLE `users` (
  `id` int(11) NOT NULL,
  `username` varchar(50) NOT NULL,
  `password_hash` varchar(255) NOT NULL,
  `role` varchar(20) NOT NULL DEFAULT 'user',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Déchargement des données de la table `users`
--

INSERT INTO `users` (`id`, `username`, `password_hash`, `role`, `created_at`) VALUES
(1, 'admin', '240be518fabd2724ddb6f04eeb1da5967448d7e831c08c8fa822809f74c720a9', 'admin', '2026-03-23 10:02:58');

--
-- Index pour les tables déchargées
--

--
-- Index pour la table `connection_logs`
--
ALTER TABLE `connection_logs`
  ADD PRIMARY KEY (`id`),
  ADD KEY `fk_connection_logs_user` (`user_id`);

--
-- Index pour la table `robots`
--
ALTER TABLE `robots`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `robot_name` (`robot_name`);

--
-- Index pour la table `robot_action_logs`
--
ALTER TABLE `robot_action_logs`
  ADD PRIMARY KEY (`id`),
  ADD KEY `fk_robot_action_logs_robot` (`robot_id`),
  ADD KEY `fk_robot_action_logs_user` (`user_id`);

--
-- Index pour la table `robot_commands`
--
ALTER TABLE `robot_commands`
  ADD PRIMARY KEY (`id`),
  ADD KEY `fk_robot_commands_robot` (`robot_id`),
  ADD KEY `fk_robot_commands_target_robot` (`target_robot_id`),
  ADD KEY `fk_robot_commands_user` (`user_id`);

--
-- Index pour la table `robot_positions`
--
ALTER TABLE `robot_positions`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uq_robot_step` (`robot_id`,`commande`,`box_number`,`step_order`);

--
-- Index pour la table `sensor_states`
--
ALTER TABLE `sensor_states`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uq_robot_sensor_pin` (`robot_id`,`sensor_pin`),
  ADD UNIQUE KEY `uq_robot_box` (`robot_id`,`box_number`);

--
-- Index pour la table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `username` (`username`);

--
-- AUTO_INCREMENT pour les tables déchargées
--

--
-- AUTO_INCREMENT pour la table `connection_logs`
--
ALTER TABLE `connection_logs`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT pour la table `robots`
--
ALTER TABLE `robots`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT pour la table `robot_action_logs`
--
ALTER TABLE `robot_action_logs`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT pour la table `robot_commands`
--
ALTER TABLE `robot_commands`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT pour la table `robot_positions`
--
ALTER TABLE `robot_positions`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=46;

--
-- AUTO_INCREMENT pour la table `sensor_states`
--
ALTER TABLE `sensor_states`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT pour la table `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- Contraintes pour les tables déchargées
--

--
-- Contraintes pour la table `connection_logs`
--
ALTER TABLE `connection_logs`
  ADD CONSTRAINT `fk_connection_logs_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE SET NULL;

--
-- Contraintes pour la table `robot_action_logs`
--
ALTER TABLE `robot_action_logs`
  ADD CONSTRAINT `fk_robot_action_logs_robot` FOREIGN KEY (`robot_id`) REFERENCES `robots` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_robot_action_logs_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE SET NULL;

--
-- Contraintes pour la table `robot_commands`
--
ALTER TABLE `robot_commands`
  ADD CONSTRAINT `fk_robot_commands_robot` FOREIGN KEY (`robot_id`) REFERENCES `robots` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_robot_commands_target_robot` FOREIGN KEY (`target_robot_id`) REFERENCES `robots` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `fk_robot_commands_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE SET NULL;

--
-- Contraintes pour la table `robot_positions`
--
ALTER TABLE `robot_positions`
  ADD CONSTRAINT `fk_robot_positions_robot` FOREIGN KEY (`robot_id`) REFERENCES `robots` (`id`) ON DELETE CASCADE;

--
-- Contraintes pour la table `sensor_states`
--
ALTER TABLE `sensor_states`
  ADD CONSTRAINT `fk_sensor_states_robot` FOREIGN KEY (`robot_id`) REFERENCES `robots` (`id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
