-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Hôte : localhost
-- Généré le : lun. 04 mai 2026 à 13:59
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
  `robot_name` varchar(20) NOT NULL,
  `esp32_role` varchar(20) NOT NULL,
  `ip_address` varchar(45) DEFAULT NULL,
  `status` varchar(20) NOT NULL DEFAULT 'offline',
  `last_seen_at` datetime DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Déchargement des données de la table `robots`
--

INSERT INTO `robots` (`id`, `robot_name`, `esp32_role`, `ip_address`, `status`, `last_seen_at`, `created_at`) VALUES
(1, 'Robot A', 'robot', '192.168.1.201', 'offline', NULL, '2026-04-13 11:43:26'),
(2, 'Robot B', 'robot', '192.168.1.202', 'offline', NULL, '2026-04-13 11:43:26'),
(3, 'Robot C', 'robot', '192.168.1.203', 'offline', NULL, '2026-04-13 11:43:26');

-- --------------------------------------------------------

--
-- Structure de la table `robot_action_logs`
--

CREATE TABLE `robot_action_logs` (
  `id` int(11) NOT NULL,
  `robot_id` int(11) NOT NULL,
  `user_id` int(11) DEFAULT NULL,
  `command_id` int(11) DEFAULT NULL,
  `action_name` varchar(50) NOT NULL,
  `status` varchar(20) NOT NULL,
  `details` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Structure de la table `robot_commands`
--

CREATE TABLE `robot_commands` (
  `id` int(11) NOT NULL,
  `robot_id` int(11) NOT NULL,
  `created_by_user_id` int(11) DEFAULT NULL,
  `target_robot_id` int(11) DEFAULT NULL,
  `action_name` varchar(50) NOT NULL,
  `payload` text DEFAULT NULL,
  `status` varchar(20) NOT NULL DEFAULT 'pending',
  `details` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `started_at` datetime DEFAULT NULL,
  `executed_at` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Structure de la table `robot_positions`
--

CREATE TABLE `robot_positions` (
  `id` int(11) NOT NULL,
  `robot_id` int(11) NOT NULL,
  `action_name` varchar(50) NOT NULL,
  `step_order` int(11) NOT NULL,
  `servo_base_angle` int(11) NOT NULL,
  `servo_shoulder_angle` int(11) NOT NULL,
  `servo_elbow_angle` int(11) NOT NULL,
  `servo_wrist_angle` int(11) NOT NULL,
  `servo_rotate_angle` int(11) NOT NULL,
  `servo_grip_angle` int(11) NOT NULL,
  `speed_ms_per_degree` int(11) NOT NULL DEFAULT 10,
  `pause_after_ms` int(11) NOT NULL DEFAULT 0,
  `commentaire` varchar(255) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Déchargement des données de la table `robot_positions`
--

INSERT INTO `robot_positions` (`id`, `robot_id`, `action_name`, `step_order`, `servo_base_angle`, `servo_shoulder_angle`, `servo_elbow_angle`, `servo_wrist_angle`, `servo_rotate_angle`, `servo_grip_angle`, `speed_ms_per_degree`, `pause_after_ms`, `commentaire`, `created_at`) VALUES
(32, 1, 'mouvement1', 1, 83, 43, 98, 170, 160, 63, 10, 0, NULL, '2026-04-28 13:09:28'),
(33, 1, 'mouvement2', 1, 0, 43, 98, 170, 160, 63, 10, 0, NULL, '2026-04-28 13:09:28'),
(34, 1, 'mouvement3', 1, 83, 43, 98, 170, 160, 63, 10, 0, NULL, '2026-04-28 13:09:28'),
(35, 1, 'mouvement3', 2, 87, 35, 98, 149, 141, 105, 10, 0, NULL, '2026-04-28 13:09:28'),
(36, 1, 'mouvement4', 1, 87, 35, 98, 149, 141, 105, 10, 0, NULL, '2026-04-28 13:09:28'),
(37, 1, 'mouvement4', 2, 87, 35, 98, 149, 107, 105, 10, 0, NULL, '2026-04-28 13:09:28'),
(38, 1, 'mouvement4', 3, 87, 35, 98, 149, 92, 105, 10, 0, NULL, '2026-04-28 13:09:28'),
(39, 1, 'mouvement4', 4, 87, 35, 98, 149, 92, 105, 10, 0, NULL, '2026-04-28 13:09:28'),
(40, 1, 'mouvement4', 5, 87, 35, 98, 149, 92, 105, 10, 0, NULL, '2026-04-28 13:09:28'),
(41, 1, 'mouvement4', 6, 87, 35, 98, 149, 92, 105, 10, 0, NULL, '2026-04-28 13:09:28'),
(42, 1, 'mouvement4', 7, 0, 35, 98, 149, 92, 105, 10, 0, NULL, '2026-04-28 13:09:28'),
(43, 1, 'mouvement4', 8, 0, 35, 98, 149, 92, 105, 10, 0, NULL, '2026-04-28 13:09:28'),
(44, 1, 'mouvement4', 9, 0, 35, 98, 149, 92, 105, 10, 0, NULL, '2026-04-28 13:09:28'),
(45, 1, 'mouvement4', 10, 0, 35, 98, 149, 92, 105, 10, 0, NULL, '2026-04-28 13:09:28'),
(46, 1, 'mouvement4', 11, 0, 35, 98, 149, 110, 105, 10, 0, NULL, '2026-04-28 13:09:28'),
(47, 1, 'mouvement4', 12, 0, 35, 98, 149, 138, 105, 10, 0, NULL, '2026-04-28 13:09:28'),
(48, 1, 'mouvement5', 1, 0, 35, 98, 149, 138, 105, 10, 0, NULL, '2026-04-28 13:09:28'),
(49, 1, 'mouvement5', 2, 0, 35, 98, 149, 138, 44, 10, 0, NULL, '2026-04-28 13:09:28'),
(50, 1, 'mouvement6', 1, 0, 35, 98, 149, 138, 44, 10, 0, NULL, '2026-04-28 13:09:28'),
(51, 1, 'mouvement6', 2, 0, 35, 98, 149, 110, 44, 10, 0, NULL, '2026-04-28 13:09:28'),
(52, 1, 'mouvement6', 3, 0, 35, 98, 149, 95, 44, 10, 0, NULL, '2026-04-28 13:09:28'),
(53, 1, 'mouvement6', 4, 0, 35, 98, 149, 95, 44, 10, 0, NULL, '2026-04-28 13:09:28'),
(54, 1, 'mouvement6', 5, 0, 35, 98, 145, 95, 44, 10, 0, NULL, '2026-04-28 13:09:28'),
(55, 1, 'mouvement6', 6, 0, 35, 98, 145, 95, 44, 10, 0, NULL, '2026-04-28 13:09:28'),
(56, 1, 'mouvement6', 7, 83, 35, 98, 145, 95, 44, 10, 0, NULL, '2026-04-28 13:09:28'),
(57, 1, 'mouvement6', 8, 83, 35, 98, 145, 140, 44, 10, 0, NULL, '2026-04-28 13:09:28'),
(58, 1, 'mouvement6', 9, 83, 35, 98, 149, 140, 44, 10, 0, NULL, '2026-04-28 13:09:28'),
(59, 1, 'mouvement6', 10, 83, 35, 98, 149, 140, 44, 10, 0, NULL, '2026-04-28 13:09:28'),
(60, 1, 'mouvement6', 11, 83, 35, 98, 149, 140, 42, 10, 0, NULL, '2026-04-28 13:09:28'),
(61, 1, 'mouvement7', 1, 83, 35, 98, 149, 140, 42, 10, 0, NULL, '2026-04-28 13:09:28'),
(62, 1, 'mouvement7', 2, 83, 43, 98, 170, 160, 63, 10, 0, NULL, '2026-04-28 13:09:28'),
(63, 2, 'mouvement1', 1, 83, 43, 98, 170, 160, 63, 10, 0, 'Robot B - mouvement1 - etape 1', '2026-04-28 14:33:49'),
(64, 2, 'mouvement2', 1, 10, 43, 98, 170, 160, 63, 10, 0, 'Robot B - mouvement2 - etape 1', '2026-04-28 14:33:49'),
(65, 2, 'mouvement3', 1, 83, 43, 98, 170, 160, 63, 10, 0, 'Robot B - mouvement3 - etape 1', '2026-04-28 14:33:49'),
(66, 2, 'mouvement3', 2, 87, 35, 98, 149, 141, 105, 10, 0, 'Robot B - mouvement3 - etape 2', '2026-04-28 14:33:49'),
(67, 2, 'mouvement4', 1, 87, 35, 98, 149, 141, 105, 10, 0, 'Robot B - mouvement4 - etape 1', '2026-04-28 14:33:49'),
(68, 2, 'mouvement4', 2, 87, 35, 98, 149, 110, 105, 10, 0, 'Robot B - mouvement4 - etape 2', '2026-04-28 14:33:49'),
(69, 2, 'mouvement4', 3, 87, 35, 98, 149, 100, 105, 10, 0, 'Robot B - mouvement4 - etape 3', '2026-04-28 14:33:49'),
(70, 2, 'mouvement4', 4, 87, 35, 98, 149, 100, 105, 10, 0, 'Robot B - mouvement4 - etape 4', '2026-04-28 14:33:49'),
(71, 2, 'mouvement4', 5, 87, 35, 98, 149, 100, 105, 10, 0, 'Robot B - mouvement4 - etape 5', '2026-04-28 14:33:49'),
(72, 2, 'mouvement4', 6, 87, 35, 98, 149, 100, 105, 10, 0, 'Robot B - mouvement4 - etape 6', '2026-04-28 14:33:49'),
(73, 2, 'mouvement4', 7, 10, 35, 98, 149, 100, 105, 10, 0, 'Robot B - mouvement4 - etape 7', '2026-04-28 14:33:49'),
(74, 2, 'mouvement4', 8, 10, 35, 98, 149, 100, 105, 10, 0, 'Robot B - mouvement4 - etape 8', '2026-04-28 14:33:49'),
(75, 2, 'mouvement4', 9, 10, 35, 98, 149, 100, 105, 10, 0, 'Robot B - mouvement4 - etape 9', '2026-04-28 14:33:49'),
(76, 2, 'mouvement4', 10, 10, 35, 98, 149, 100, 105, 10, 0, 'Robot B - mouvement4 - etape 10', '2026-04-28 14:33:49'),
(77, 2, 'mouvement4', 11, 10, 35, 98, 149, 110, 105, 10, 0, 'Robot B - mouvement4 - etape 11', '2026-04-28 14:33:49'),
(78, 2, 'mouvement4', 12, 10, 35, 98, 149, 141, 105, 10, 0, 'Robot B - mouvement4 - etape 12', '2026-04-28 14:33:49'),
(79, 2, 'mouvement5', 1, 10, 35, 98, 149, 141, 105, 10, 0, 'Robot B - mouvement5 - etape 1', '2026-04-28 14:33:49'),
(80, 2, 'mouvement5', 2, 10, 35, 98, 149, 138, 32, 10, 0, 'Robot B - mouvement5 - etape 2', '2026-04-28 14:33:49'),
(81, 2, 'mouvement6', 1, 10, 35, 98, 149, 138, 32, 10, 0, 'Robot B - mouvement6 - etape 1', '2026-04-28 14:33:49'),
(82, 2, 'mouvement6', 2, 10, 35, 98, 149, 138, 32, 10, 0, 'Robot B - mouvement6 - etape 2', '2026-04-28 14:33:49'),
(83, 2, 'mouvement6', 3, 10, 35, 98, 149, 138, 32, 10, 0, 'Robot B - mouvement6 - etape 3', '2026-04-28 14:33:49'),
(84, 2, 'mouvement6', 4, 10, 35, 98, 149, 100, 32, 10, 0, 'Robot B - mouvement6 - etape 4', '2026-04-28 14:33:49'),
(85, 2, 'mouvement6', 5, 10, 35, 98, 137, 95, 32, 10, 0, 'Robot B - mouvement6 - etape 5', '2026-04-28 14:33:49'),
(86, 2, 'mouvement6', 6, 10, 35, 98, 137, 95, 32, 10, 0, 'Robot B - mouvement6 - etape 6', '2026-04-28 14:33:49'),
(87, 2, 'mouvement6', 7, 83, 35, 98, 137, 95, 32, 10, 0, 'Robot B - mouvement6 - etape 7', '2026-04-28 14:33:49'),
(88, 2, 'mouvement6', 8, 83, 35, 98, 137, 95, 32, 10, 0, 'Robot B - mouvement6 - etape 8', '2026-04-28 14:33:49'),
(89, 2, 'mouvement6', 9, 83, 35, 98, 149, 100, 32, 10, 0, 'Robot B - mouvement6 - etape 9', '2026-04-28 14:33:49'),
(90, 2, 'mouvement6', 10, 83, 35, 98, 149, 120, 32, 10, 0, 'Robot B - mouvement6 - etape 10', '2026-04-28 14:33:49'),
(91, 2, 'mouvement6', 11, 83, 35, 98, 149, 130, 32, 10, 0, 'Robot B - mouvement6 - etape 11', '2026-04-28 14:33:49'),
(92, 2, 'mouvement7', 1, 83, 35, 98, 149, 130, 32, 10, 0, 'Robot B - mouvement7 - etape 1', '2026-04-28 14:33:49'),
(93, 2, 'mouvement7', 2, 83, 43, 98, 170, 160, 63, 10, 0, 'Robot B - mouvement7 - etape 2', '2026-04-28 14:33:49'),
(156, 3, 'mouvement1', 1, 83, 43, 105, 180, 160, 63, 10, 0, 'Robot C - mouvement1 - HOME pince ouverte', '2026-05-04 09:42:39'),
(157, 3, 'mouvement2', 1, 10, 43, 105, 180, 160, 63, 10, 0, 'Robot C - mouvement2 - HOME pince fermee', '2026-05-04 09:42:39'),
(158, 3, 'mouvement3', 1, 83, 43, 105, 180, 160, 63, 10, 0, 'Robot C - mouvement3 - etape 1', '2026-05-04 09:42:39'),
(159, 3, 'mouvement3', 2, 87, 35, 105, 180, 160, 100, 10, 0, 'Robot C - mouvement3 - etape 2', '2026-05-04 09:42:39'),
(160, 3, 'mouvement4', 1, 87, 35, 105, 180, 160, 100, 10, 0, 'Robot C - mouvement4 - etape 1', '2026-05-04 09:42:39'),
(161, 3, 'mouvement4', 2, 87, 35, 105, 170, 150, 100, 10, 0, 'Robot C - mouvement4 - etape 2', '2026-05-04 09:42:39'),
(162, 3, 'mouvement4', 3, 87, 35, 105, 160, 110, 100, 10, 0, 'Robot C - mouvement4 - etape 3', '2026-05-04 09:42:39'),
(163, 3, 'mouvement4', 4, 87, 35, 105, 150, 100, 100, 10, 0, 'Robot C - mouvement4 - etape 4', '2026-05-04 09:42:39'),
(164, 3, 'mouvement4', 5, 87, 35, 98, 149, 85, 100, 10, 0, 'Robot C - mouvement4 - etape 5', '2026-05-04 09:42:39'),
(165, 3, 'mouvement4', 6, 87, 35, 98, 149, 85, 100, 10, 0, 'Robot C - mouvement4 - etape 6', '2026-05-04 09:42:39'),
(166, 3, 'mouvement4', 7, 10, 35, 98, 149, 85, 100, 10, 0, 'Robot C - mouvement4 - etape 7', '2026-05-04 09:42:39'),
(167, 3, 'mouvement4', 8, 10, 35, 98, 149, 85, 100, 10, 0, 'Robot C - mouvement4 - etape 8', '2026-05-04 09:42:39'),
(168, 3, 'mouvement4', 9, 10, 35, 105, 150, 100, 100, 10, 0, 'Robot C - mouvement4 - etape 9', '2026-05-04 09:42:39'),
(169, 3, 'mouvement4', 10, 10, 35, 105, 160, 110, 100, 10, 0, 'Robot C - mouvement4 - etape 10', '2026-05-04 09:42:39'),
(170, 3, 'mouvement4', 11, 10, 35, 105, 170, 150, 100, 10, 0, 'Robot C - mouvement4 - etape 11', '2026-05-04 09:42:39'),
(171, 3, 'mouvement4', 12, 10, 35, 105, 180, 160, 100, 10, 0, 'Robot C - mouvement4 - etape 12', '2026-05-04 09:42:39'),
(172, 3, 'mouvement5', 1, 10, 35, 105, 180, 160, 100, 10, 0, 'Robot C - mouvement5 - etape 1', '2026-05-04 09:42:39'),
(173, 3, 'mouvement5', 2, 10, 35, 105, 180, 160, 25, 10, 0, 'Robot C - mouvement5 - etape 2', '2026-05-04 09:42:39'),
(174, 3, 'mouvement6', 1, 10, 35, 105, 180, 160, 25, 10, 0, 'Robot C - mouvement6 - etape 1', '2026-05-04 09:42:39'),
(175, 3, 'mouvement6', 2, 10, 35, 105, 170, 150, 25, 10, 0, 'Robot C - mouvement6 - etape 2', '2026-05-04 09:42:39'),
(176, 3, 'mouvement6', 3, 10, 35, 105, 160, 110, 25, 10, 0, 'Robot C - mouvement6 - etape 3', '2026-05-04 09:42:39'),
(177, 3, 'mouvement6', 4, 10, 35, 105, 160, 100, 25, 10, 0, 'Robot C - mouvement6 - etape 4', '2026-05-04 09:42:39'),
(178, 3, 'mouvement6', 5, 10, 35, 98, 160, 95, 25, 10, 0, 'Robot C - mouvement6 - etape 5', '2026-05-04 09:42:39'),
(179, 3, 'mouvement6', 6, 10, 35, 98, 160, 95, 25, 10, 0, 'Robot C - mouvement6 - etape 6', '2026-05-04 09:42:39'),
(180, 3, 'mouvement6', 7, 83, 35, 98, 160, 95, 25, 10, 0, 'Robot C - mouvement6 - etape 7', '2026-05-04 09:42:39'),
(181, 3, 'mouvement6', 8, 83, 35, 98, 160, 95, 25, 10, 0, 'Robot C - mouvement6 - etape 8', '2026-05-04 09:42:39'),
(182, 3, 'mouvement6', 9, 83, 35, 98, 170, 95, 25, 10, 0, 'Robot C - mouvement6 - etape 9', '2026-05-04 09:42:39'),
(183, 3, 'mouvement6', 10, 83, 35, 98, 175, 150, 25, 10, 0, 'Robot C - mouvement6 - etape 10', '2026-05-04 09:42:39'),
(184, 3, 'mouvement6', 11, 83, 35, 98, 180, 160, 25, 10, 0, 'Robot C - mouvement6 - etape 11', '2026-05-04 09:42:39'),
(185, 3, 'mouvement7', 1, 83, 35, 98, 180, 160, 25, 10, 0, 'Robot C - mouvement7 - etape 1', '2026-05-04 09:42:39'),
(186, 3, 'mouvement7', 2, 83, 35, 98, 180, 160, 63, 10, 0, 'Robot C - mouvement7 - etape 2', '2026-05-04 09:42:39');

-- --------------------------------------------------------

--
-- Structure de la table `sensor_states`
--

CREATE TABLE `sensor_states` (
  `id` int(11) NOT NULL,
  `robot_id` int(11) NOT NULL,
  `sensor_number` tinyint(4) NOT NULL,
  `sensor_label` varchar(30) DEFAULT NULL,
  `box_present` tinyint(1) NOT NULL DEFAULT 0,
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Déchargement des données de la table `sensor_states`
--

INSERT INTO `sensor_states` (`id`, `robot_id`, `sensor_number`, `sensor_label`, `box_present`, `updated_at`) VALUES
(1, 1, 1, 'input', 0, '2026-04-13 11:43:26'),
(2, 2, 1, 'input', 0, '2026-04-13 11:43:26'),
(3, 3, 1, 'input', 0, '2026-04-13 11:43:26'),
(4, 1, 2, 'output', 0, '2026-04-13 11:43:26'),
(5, 2, 2, 'output', 0, '2026-04-13 11:43:26'),
(6, 3, 2, 'output', 0, '2026-04-13 11:43:26');

-- --------------------------------------------------------

--
-- Structure de la table `users`
--

CREATE TABLE `users` (
  `id` int(11) NOT NULL,
  `username` varchar(50) NOT NULL,
  `password_hash` char(64) NOT NULL,
  `role` varchar(20) NOT NULL DEFAULT 'user',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Déchargement des données de la table `users`
--

INSERT INTO `users` (`id`, `username`, `password_hash`, `role`, `created_at`) VALUES
(1, 'admin', '240be518fabd2724ddb6f04eeb1da5967448d7e831c08c8fa822809f74c720a9', 'admin', '2026-04-13 11:43:26');

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
  ADD KEY `idx_robot_action_logs_robot_date` (`robot_id`,`created_at`),
  ADD KEY `fk_robot_action_logs_user` (`user_id`),
  ADD KEY `fk_robot_action_logs_command` (`command_id`);

--
-- Index pour la table `robot_commands`
--
ALTER TABLE `robot_commands`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_robot_commands_robot_status` (`robot_id`,`status`),
  ADD KEY `fk_robot_commands_user` (`created_by_user_id`),
  ADD KEY `fk_robot_commands_target_robot` (`target_robot_id`);

--
-- Index pour la table `robot_positions`
--
ALTER TABLE `robot_positions`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uq_robot_action_step` (`robot_id`,`action_name`,`step_order`);

--
-- Index pour la table `sensor_states`
--
ALTER TABLE `sensor_states`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uq_robot_sensor` (`robot_id`,`sensor_number`);

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
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=187;

--
-- AUTO_INCREMENT pour la table `sensor_states`
--
ALTER TABLE `sensor_states`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

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
  ADD CONSTRAINT `fk_robot_action_logs_command` FOREIGN KEY (`command_id`) REFERENCES `robot_commands` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `fk_robot_action_logs_robot` FOREIGN KEY (`robot_id`) REFERENCES `robots` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_robot_action_logs_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE SET NULL;

--
-- Contraintes pour la table `robot_commands`
--
ALTER TABLE `robot_commands`
  ADD CONSTRAINT `fk_robot_commands_robot` FOREIGN KEY (`robot_id`) REFERENCES `robots` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_robot_commands_target_robot` FOREIGN KEY (`target_robot_id`) REFERENCES `robots` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `fk_robot_commands_user` FOREIGN KEY (`created_by_user_id`) REFERENCES `users` (`id`) ON DELETE SET NULL;

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
