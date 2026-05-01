-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Hôte : localhost
-- Généré le : mar. 28 avr. 2026 à 16:35
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
(93, 2, 'mouvement7', 2, 83, 43, 98, 170, 160, 63, 10, 0, 'Robot B - mouvement7 - etape 2', '2026-04-28 14:33:49');

-- --------------------------------------------------------

--
-- Structure de la table `robot_positions_backup_robot_a_20260428`
--

CREATE TABLE `robot_positions_backup_robot_a_20260428` (
  `id` int(11) NOT NULL DEFAULT 0,
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
-- Déchargement des données de la table `robot_positions_backup_robot_a_20260428`
--

INSERT INTO `robot_positions_backup_robot_a_20260428` (`id`, `robot_id`, `action_name`, `step_order`, `servo_base_angle`, `servo_shoulder_angle`, `servo_elbow_angle`, `servo_wrist_angle`, `servo_rotate_angle`, `servo_grip_angle`, `speed_ms_per_degree`, `pause_after_ms`, `commentaire`, `created_at`) VALUES
(1, 1, 'home_pince_ouverte', 1, 63, 140, 149, 43, 98, 83, 50, 1000, 'Robot A - mouvement1 - HOME pince ouverte - etape 1', '2026-04-27 07:15:52'),
(2, 1, 'home_pince_fermee', 1, 63, 140, 149, 43, 98, 0, 50, 1000, 'Robot A - mouvement2 - HOME pince fermee - etape 1', '2026-04-27 07:15:52'),
(3, 1, 'aller_prise_boite_palette1', 1, 63, 140, 149, 43, 98, 83, 50, 0, 'Robot A - mouvement3 - HOME vers prise boite palette 1 - etape 1', '2026-04-27 07:15:52'),
(4, 1, 'aller_prise_boite_palette1', 2, 105, 141, 149, 35, 98, 87, 50, 1000, 'Robot A - mouvement3 - HOME vers prise boite palette 1 - etape 2', '2026-04-27 07:15:52'),
(5, 1, 'prendre_boite_palette1', 1, 105, 141, 149, 35, 98, 87, 50, 0, 'Robot A - mouvement4 - prise boite palette 1 - etape 1', '2026-04-27 07:15:52'),
(6, 1, 'prendre_boite_palette1', 2, 105, 107, 149, 35, 98, 87, 50, 0, 'Robot A - mouvement4 - prise boite palette 1 - etape 2', '2026-04-27 07:15:52'),
(7, 1, 'prendre_boite_palette1', 3, 105, 92, 149, 35, 98, 87, 50, 0, 'Robot A - mouvement4 - prise boite palette 1 - etape 3', '2026-04-27 07:15:52'),
(8, 1, 'prendre_boite_palette1', 4, 105, 92, 149, 35, 98, 87, 50, 0, 'Robot A - mouvement4 - prise boite palette 1 - etape 4', '2026-04-27 07:15:52'),
(9, 1, 'prendre_boite_palette1', 5, 105, 92, 149, 35, 98, 87, 50, 0, 'Robot A - mouvement4 - prise boite palette 1 - etape 5', '2026-04-27 07:15:52'),
(10, 1, 'prendre_boite_palette1', 6, 105, 92, 149, 35, 98, 87, 50, 0, 'Robot A - mouvement4 - prise boite palette 1 - etape 6', '2026-04-27 07:15:52'),
(11, 1, 'prendre_boite_palette1', 7, 105, 92, 149, 35, 98, 0, 50, 0, 'Robot A - mouvement4 - prise boite palette 1 - etape 7', '2026-04-27 07:15:52'),
(12, 1, 'prendre_boite_palette1', 8, 105, 92, 149, 35, 98, 0, 50, 0, 'Robot A - mouvement4 - prise boite palette 1 - etape 8', '2026-04-27 07:15:52'),
(13, 1, 'prendre_boite_palette1', 9, 105, 92, 149, 35, 98, 0, 50, 0, 'Robot A - mouvement4 - prise boite palette 1 - etape 9', '2026-04-27 07:15:52'),
(14, 1, 'prendre_boite_palette1', 10, 105, 92, 149, 35, 98, 0, 50, 0, 'Robot A - mouvement4 - prise boite palette 1 - etape 10', '2026-04-27 07:15:52'),
(15, 1, 'prendre_boite_palette1', 11, 105, 110, 149, 35, 98, 0, 50, 0, 'Robot A - mouvement4 - prise boite palette 1 - etape 11', '2026-04-27 07:15:52'),
(16, 1, 'prendre_boite_palette1', 12, 105, 138, 149, 35, 98, 0, 50, 1000, 'Robot A - mouvement4 - prise boite palette 1 - etape 12', '2026-04-27 07:15:52'),
(17, 1, 'transfert_palette1_vers_palette2', 1, 105, 138, 149, 35, 98, 0, 50, 0, 'Robot A - mouvement5 - transfert palette 1 vers palette 2 - etape 1', '2026-04-27 07:15:52'),
(18, 1, 'transfert_palette1_vers_palette2', 2, 42, 138, 149, 35, 98, 0, 50, 1000, 'Robot A - mouvement5 - transfert palette 1 vers palette 2 - etape 2', '2026-04-27 07:15:52'),
(19, 1, 'deposer_boite_palette2', 1, 42, 138, 149, 35, 98, 0, 50, 0, 'Robot A - mouvement6 - depot boite palette 2 - etape 1', '2026-04-27 07:15:52'),
(20, 1, 'deposer_boite_palette2', 2, 42, 110, 149, 35, 98, 0, 50, 0, 'Robot A - mouvement6 - depot boite palette 2 - etape 2', '2026-04-27 07:15:52'),
(21, 1, 'deposer_boite_palette2', 3, 42, 95, 149, 35, 98, 0, 50, 0, 'Robot A - mouvement6 - depot boite palette 2 - etape 3', '2026-04-27 07:15:52'),
(22, 1, 'deposer_boite_palette2', 4, 42, 95, 149, 35, 98, 0, 50, 0, 'Robot A - mouvement6 - depot boite palette 2 - etape 4', '2026-04-27 07:15:52'),
(23, 1, 'deposer_boite_palette2', 5, 42, 95, 149, 35, 98, 0, 50, 0, 'Robot A - mouvement6 - depot boite palette 2 - etape 5', '2026-04-27 07:15:52'),
(24, 1, 'deposer_boite_palette2', 6, 42, 95, 149, 35, 98, 0, 50, 0, 'Robot A - mouvement6 - depot boite palette 2 - etape 6', '2026-04-27 07:15:52'),
(25, 1, 'deposer_boite_palette2', 7, 42, 95, 149, 35, 98, 83, 50, 0, 'Robot A - mouvement6 - depot boite palette 2 - etape 7', '2026-04-27 07:15:52'),
(26, 1, 'deposer_boite_palette2', 8, 42, 140, 149, 35, 98, 83, 50, 0, 'Robot A - mouvement6 - depot boite palette 2 - etape 8', '2026-04-27 07:15:52'),
(27, 1, 'deposer_boite_palette2', 9, 42, 140, 149, 35, 98, 83, 50, 0, 'Robot A - mouvement6 - depot boite palette 2 - etape 9', '2026-04-27 07:15:52'),
(28, 1, 'deposer_boite_palette2', 10, 42, 140, 149, 35, 98, 83, 50, 0, 'Robot A - mouvement6 - depot boite palette 2 - etape 10', '2026-04-27 07:15:52'),
(29, 1, 'deposer_boite_palette2', 11, 42, 140, 149, 35, 98, 83, 50, 1000, 'Robot A - mouvement6 - depot boite palette 2 - etape 11', '2026-04-27 07:15:52'),
(30, 1, 'retour_home_pince_ouverte', 1, 42, 140, 149, 35, 98, 83, 50, 0, 'Robot A - mouvement7 - retour HOME pince ouverte - etape 1', '2026-04-27 07:15:52'),
(31, 1, 'retour_home_pince_ouverte', 2, 63, 140, 149, 43, 98, 83, 50, 1000, 'Robot A - mouvement7 - retour HOME pince ouverte - etape 2', '2026-04-27 07:15:52');

-- --------------------------------------------------------

--
-- Structure de la table `robot_positions_backup_robot_b_20260428`
--

CREATE TABLE `robot_positions_backup_robot_b_20260428` (
  `id` int(11) NOT NULL DEFAULT 0,
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
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=94;

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
