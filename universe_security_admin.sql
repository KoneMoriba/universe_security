-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Hôte : 127.0.0.1
-- Généré le : dim. 21 sep. 2025 à 18:49
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
-- Base de données : `universe_security_admin`
--

-- --------------------------------------------------------

--
-- Structure de la table `about_content`
--

CREATE TABLE `about_content` (
  `id` int(11) NOT NULL,
  `section_key` varchar(50) NOT NULL,
  `title` varchar(200) NOT NULL,
  `content` text NOT NULL,
  `image` varchar(255) DEFAULT NULL,
  `is_active` tinyint(1) DEFAULT 1,
  `display_order` int(11) DEFAULT 0,
  `created_by` int(11) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Déchargement des données de la table `about_content`
--

INSERT INTO `about_content` (`id`, `section_key`, `title`, `content`, `image`, `is_active`, `display_order`, `created_by`, `created_at`, `updated_at`) VALUES
(1, 'main_about', 'À Propos de Universe Security', 'Universe-Security est une entreprise spécialisée dans les solutions de sécurité innovantes. Nous offrons une gamme complète de services pour protéger votre domicile, votre entreprise et vos biens les plus précieux.', '', 1, 1, NULL, '2025-09-21 02:51:13', '2025-09-21 02:53:00'),
(2, 'our_mission', 'Notre Mission', 'Fournir des solutions de sécurité de pointe, fiables et adaptées aux besoins spécifiques de chaque client, tout en maintenant les plus hauts standards de qualité et de service.', NULL, 1, 2, NULL, '2025-09-21 02:51:13', '2025-09-21 02:51:13'),
(3, 'our_vision', 'Notre Vision', 'Devenir le leader régional des solutions de sécurité intégrées, en combinant innovation technologique et excellence du service client.', NULL, 1, 3, NULL, '2025-09-21 02:51:13', '2025-09-21 02:51:13');

-- --------------------------------------------------------

--
-- Structure de la table `admins`
--

CREATE TABLE `admins` (
  `id` int(11) NOT NULL,
  `username` varchar(50) NOT NULL,
  `email` varchar(100) NOT NULL,
  `password` varchar(255) NOT NULL,
  `full_name` varchar(100) NOT NULL,
  `role` enum('super_admin','admin','moderator') DEFAULT 'admin',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `last_login` timestamp NULL DEFAULT NULL,
  `is_active` tinyint(1) DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Déchargement des données de la table `admins`
--

INSERT INTO `admins` (`id`, `username`, `email`, `password`, `full_name`, `role`, `created_at`, `updated_at`, `last_login`, `is_active`) VALUES
(1, 'admin', 'admin@universesecurity.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Moriba', 'super_admin', '2025-09-18 01:03:34', '2025-09-21 12:37:51', '2025-09-21 12:37:51', 1),
(2, 'Abd', 'koneabdoul0411@gmail.com', '$2y$10$k3i13JIA4lFmIyQm6yMTfOYw30rj/sB/O.uVzCvPpH9h6ZYznJa7u', 'Abdoul', 'admin', '2025-09-18 01:41:02', '2025-09-18 01:41:27', '2025-09-18 01:41:27', 1),
(3, 'momo@universe', 'momo@universe-security.com', '$2y$10$f861cleHYUcKd6QCkk8QNe6eB2cVlpvf/.RFUwW3yCkKyiDZrJmN.', 'momo', '', '2025-09-21 00:38:54', '2025-09-21 00:41:09', '2025-09-21 00:41:09', 1),
(4, 'mimi@universe', 'mimi@universe-security.com', '$2y$10$aTIEZyE3mCEbtxZE.LiWBecjrCr2xsIOCfOSA8MKSam1qfMYG7Y0S', 'mimi', 'admin', '2025-09-21 00:39:48', '2025-09-21 00:39:48', NULL, 1);

-- --------------------------------------------------------

--
-- Structure de la table `admin_logs`
--

CREATE TABLE `admin_logs` (
  `id` int(11) NOT NULL,
  `admin_id` int(11) NOT NULL,
  `action` varchar(100) NOT NULL,
  `table_name` varchar(50) DEFAULT NULL,
  `record_id` int(11) DEFAULT NULL,
  `old_values` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`old_values`)),
  `new_values` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`new_values`)),
  `ip_address` varchar(45) DEFAULT NULL,
  `user_agent` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Déchargement des données de la table `admin_logs`
--

INSERT INTO `admin_logs` (`id`, `admin_id`, `action`, `table_name`, `record_id`, `old_values`, `new_values`, `ip_address`, `user_agent`, `created_at`) VALUES
(1, 1, 'Connexion', 'admins', 1, NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', '2025-09-18 01:25:36'),
(2, 1, 'Mise à jour devis', 'quote_requests', 1, NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', '2025-09-18 01:39:09'),
(3, 1, 'Déconnexion', 'admins', 1, NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', '2025-09-18 01:40:18'),
(4, 2, 'Inscription', 'admins', 2, NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', '2025-09-18 01:41:02'),
(5, 2, 'Connexion', 'admins', 2, NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', '2025-09-18 01:41:27'),
(6, 2, 'Mise à jour témoignage', 'testimonials', 1, NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', '2025-09-18 02:00:56'),
(7, 2, 'Déconnexion', 'admins', 2, NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', '2025-09-18 02:33:10'),
(8, 1, 'Connexion', 'admins', 1, NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', '2025-09-18 02:36:08'),
(9, 1, 'Déconnexion', 'admins', 1, NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', '2025-09-18 02:37:31'),
(10, 1, 'Connexion', 'admins', 1, NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', '2025-09-18 12:31:41'),
(11, 1, 'Déconnexion', 'admins', 1, NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', '2025-09-18 12:32:59'),
(12, 1, 'Connexion', 'admins', 1, NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', '2025-09-19 09:08:05'),
(13, 1, 'Mise à jour service', 'services', 1, NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', '2025-09-19 09:15:06'),
(14, 1, 'Déconnexion', 'admins', 1, NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', '2025-09-19 09:15:12'),
(15, 1, 'Connexion', 'admins', 1, NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', '2025-09-19 10:38:37'),
(16, 1, 'Suppression témoignage', 'testimonials', 1, NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', '2025-09-19 10:38:50'),
(17, 1, 'Mise à jour témoignage', 'testimonials', 2, NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', '2025-09-19 10:39:20'),
(18, 1, 'Déconnexion', 'admins', 1, NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', '2025-09-19 10:39:43'),
(19, 1, 'Connexion', 'admins', 1, NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', '2025-09-19 10:47:41'),
(20, 1, 'Déconnexion', 'admins', 1, NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', '2025-09-19 10:50:06'),
(21, 1, 'Connexion', 'admins', 1, NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', '2025-09-19 10:50:09'),
(22, 1, 'Déconnexion', 'admins', 1, NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', '2025-09-19 10:56:30'),
(23, 1, 'Connexion', 'admins', 1, NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', '2025-09-19 11:14:21'),
(24, 1, 'Déconnexion', 'admins', 1, NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', '2025-09-19 11:14:27'),
(25, 1, 'Connexion', 'admins', 1, NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', '2025-09-19 11:54:05'),
(26, 1, 'Déconnexion', 'admins', 1, NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', '2025-09-19 15:58:46'),
(27, 1, 'Connexion', 'admins', 1, NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', '2025-09-19 16:07:47'),
(28, 1, 'Déconnexion', 'admins', 1, NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', '2025-09-19 16:10:58'),
(29, 1, 'Connexion', 'admins', 1, NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', '2025-09-19 16:25:22'),
(30, 1, 'Déconnexion', 'admins', 1, NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', '2025-09-19 16:27:36'),
(31, 1, 'Connexion', 'admins', 1, NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', '2025-09-19 16:27:54'),
(32, 1, 'Déconnexion', 'admins', 1, NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', '2025-09-19 16:28:29'),
(33, 1, 'Connexion', 'admins', 1, NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', '2025-09-19 16:46:08'),
(34, 1, 'Déconnexion', 'admins', 1, NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', '2025-09-19 16:46:55'),
(35, 1, 'Connexion', 'admins', 1, NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', '2025-09-19 16:53:35'),
(36, 1, 'Suppression service', 'services', 4, NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', '2025-09-19 17:05:17'),
(37, 1, 'Suppression service', 'services', 5, NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', '2025-09-19 17:05:21'),
(38, 1, 'Déconnexion', 'admins', 1, NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', '2025-09-19 17:05:46'),
(39, 1, 'Connexion', 'admins', 1, NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', '2025-09-19 17:07:10'),
(40, 1, 'Déconnexion', 'admins', 1, NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', '2025-09-19 17:08:42'),
(41, 1, 'Connexion', 'admins', 1, NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', '2025-09-19 17:10:23'),
(42, 1, 'Déconnexion', 'admins', 1, NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', '2025-09-19 17:31:57'),
(43, 1, 'Connexion', 'admins', 1, NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', '2025-09-19 17:31:59'),
(44, 1, 'Déconnexion', 'admins', 1, NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', '2025-09-19 17:32:03'),
(45, 1, 'Connexion', 'admins', 1, NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', '2025-09-19 17:33:16'),
(46, 1, 'Déconnexion', 'admins', 1, NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', '2025-09-19 17:36:30'),
(47, 1, 'Connexion', 'admins', 1, NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', '2025-09-19 17:48:05'),
(48, 1, 'Déconnexion', 'admins', 1, NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', '2025-09-19 17:49:10'),
(49, 1, 'Connexion', 'admins', 1, NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', '2025-09-19 17:49:42'),
(50, 1, 'Connexion', 'admins', 1, NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', '2025-09-19 18:09:23'),
(51, 1, 'Connexion', 'admins', 1, NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', '2025-09-19 18:09:25'),
(52, 1, 'Connexion', 'admins', 1, NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', '2025-09-19 18:09:26'),
(53, 1, 'Connexion', 'admins', 1, NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', '2025-09-19 18:09:27'),
(54, 1, 'Connexion', 'admins', 1, NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', '2025-09-19 18:09:28'),
(55, 1, 'Connexion', 'admins', 1, NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', '2025-09-19 18:09:54'),
(56, 1, 'Connexion', 'admins', 1, NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', '2025-09-19 18:09:55'),
(57, 1, 'Connexion', 'admins', 1, NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', '2025-09-19 18:09:56'),
(58, 1, 'Connexion', 'admins', 1, NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', '2025-09-19 18:09:56'),
(59, 1, 'Connexion', 'admins', 1, NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', '2025-09-19 18:09:56'),
(60, 1, 'Connexion', 'admins', 1, NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', '2025-09-19 18:09:56'),
(61, 1, 'Connexion', 'admins', 1, NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', '2025-09-19 18:14:53'),
(62, 1, 'Connexion', 'admins', 1, NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', '2025-09-19 18:14:55'),
(63, 1, 'Connexion', 'admins', 1, NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', '2025-09-19 18:15:16'),
(64, 1, 'Connexion', 'admins', 1, NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', '2025-09-19 18:15:24'),
(65, 1, 'Connexion', 'admins', 1, NULL, NULL, '', '', '2025-09-19 18:19:50'),
(66, 1, 'Connexion', 'admins', 1, NULL, NULL, '', '', '2025-09-19 18:20:43'),
(67, 1, 'Déconnexion', 'admins', 1, NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', '2025-09-19 18:42:16'),
(68, 1, 'Connexion', 'admins', 1, NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', '2025-09-19 19:19:04'),
(69, 1, 'Déconnexion', 'admins', 1, NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', '2025-09-19 19:20:11'),
(70, 1, 'Connexion', 'admins', 1, NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', '2025-09-20 12:18:29'),
(71, 1, 'Déconnexion', 'admins', 1, NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', '2025-09-20 12:30:39'),
(72, 1, 'Connexion', 'admins', 1, NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', '2025-09-20 12:42:17'),
(73, 1, 'Déconnexion', 'admins', 1, NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', '2025-09-20 13:11:02'),
(74, 1, 'Connexion', 'admins', 1, NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', '2025-09-20 13:12:51'),
(75, 1, 'Approbation témoignage', 'testimonials', 2, NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', '2025-09-20 13:13:16'),
(76, 1, 'Approbation témoignage', 'testimonials', 2, NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', '2025-09-20 13:22:45'),
(77, 1, 'Création service', 'services', 6, NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', '2025-09-20 19:34:19'),
(78, 1, 'Modification service', 'services', 6, NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', '2025-09-20 19:35:35'),
(79, 1, 'Modification service', 'services', 6, NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', '2025-09-20 19:56:24'),
(80, 1, 'Modification service', 'services', 6, NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', '2025-09-20 19:59:41'),
(81, 1, 'Déconnexion', 'admins', 1, NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', '2025-09-20 20:00:25'),
(82, 1, 'Connexion', 'admins', 1, NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', '2025-09-20 20:22:34'),
(83, 1, 'Déconnexion', 'admins', 1, NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', '2025-09-20 20:24:11'),
(84, 1, 'Connexion', 'admins', 1, NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', '2025-09-20 20:25:34'),
(85, 1, 'Déconnexion', 'admins', 1, NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', '2025-09-20 20:27:36'),
(86, 1, 'Connexion', 'admins', 1, NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', '2025-09-20 23:48:39'),
(87, 1, 'Déconnexion', 'admins', 1, NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', '2025-09-20 23:49:29'),
(88, 1, 'Connexion', 'admins', 1, NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', '2025-09-21 00:20:08'),
(89, 3, 'Inscription', 'admins', 3, NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', '2025-09-21 00:38:54'),
(90, 1, 'Création utilisateur: momo@universe', 'admins', NULL, NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', '2025-09-21 00:38:55'),
(91, 4, 'Inscription', 'admins', 4, NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', '2025-09-21 00:39:48'),
(92, 1, 'Création utilisateur: mimi@universe', 'admins', NULL, NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', '2025-09-21 00:39:48'),
(93, 1, 'Déconnexion', 'admins', 1, NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', '2025-09-21 00:40:44'),
(94, 3, 'Connexion', 'admins', 3, NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', '2025-09-21 00:41:09'),
(95, 3, 'Déconnexion', 'admins', 3, NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', '2025-09-21 00:41:42'),
(96, 1, 'Connexion', 'admins', 1, NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', '2025-09-21 00:41:59'),
(97, 1, 'Déconnexion', 'admins', 1, NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', '2025-09-21 00:42:18'),
(98, 1, 'Connexion', 'admins', 1, NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', '2025-09-21 00:42:36'),
(99, 1, 'Déconnexion', 'admins', 1, NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', '2025-09-21 00:42:56'),
(100, 1, 'Connexion', 'admins', 1, NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', '2025-09-21 02:28:51'),
(101, 1, 'Déconnexion', 'admins', 1, NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', '2025-09-21 02:34:34'),
(102, 1, 'Connexion', 'admins', 1, NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', '2025-09-21 02:47:22'),
(103, 1, 'Déconnexion', 'admins', 1, NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', '2025-09-21 02:53:06'),
(104, 1, 'Connexion', 'admins', 1, NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', '2025-09-21 02:55:01'),
(105, 1, 'Déconnexion', 'admins', 1, NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', '2025-09-21 02:56:36'),
(106, 1, 'Connexion', 'admins', 1, NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', '2025-09-21 03:02:39'),
(107, 1, 'Déconnexion', 'admins', 1, NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', '2025-09-21 03:05:29'),
(108, 1, 'Connexion', 'admins', 1, NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', '2025-09-21 12:37:51'),
(109, 1, 'Suppression service', 'services', 1, NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', '2025-09-21 12:42:10'),
(110, 1, 'Suppression service', 'services', 2, NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', '2025-09-21 12:42:15'),
(111, 1, 'Suppression service', 'services', 3, NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', '2025-09-21 12:42:20'),
(112, 1, 'Déconnexion', 'admins', 1, NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', '2025-09-21 12:42:30');

-- --------------------------------------------------------

--
-- Structure de la table `blog_articles`
--

CREATE TABLE `blog_articles` (
  `id` int(11) NOT NULL,
  `title` varchar(255) NOT NULL,
  `slug` varchar(255) NOT NULL,
  `excerpt` text DEFAULT NULL,
  `content` longtext NOT NULL,
  `featured_image` varchar(255) DEFAULT NULL,
  `category` varchar(100) DEFAULT NULL,
  `tags` text DEFAULT NULL,
  `author_id` int(11) DEFAULT NULL,
  `is_published` tinyint(1) DEFAULT 0,
  `is_featured` tinyint(1) DEFAULT 0,
  `views_count` int(11) DEFAULT 0,
  `published_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Déchargement des données de la table `blog_articles`
--

INSERT INTO `blog_articles` (`id`, `title`, `slug`, `excerpt`, `content`, `featured_image`, `category`, `tags`, `author_id`, `is_published`, `is_featured`, `views_count`, `published_at`, `created_at`, `updated_at`) VALUES
(4, 'site web universe', 'site-web-universe', 'tessssyyyy resum', 'fsdgbvgdgvdh ', 'uploads/blog/article_1758423914_7473.jpg', '', '[]', 1, 0, 0, 0, NULL, '2025-09-21 03:05:14', '2025-09-21 03:05:14');

-- --------------------------------------------------------

--
-- Structure de la table `contact_messages`
--

CREATE TABLE `contact_messages` (
  `id` int(11) NOT NULL,
  `name` varchar(100) NOT NULL,
  `email` varchar(150) NOT NULL,
  `subject` varchar(200) NOT NULL,
  `message` text NOT NULL,
  `phone` varchar(20) DEFAULT NULL,
  `company` varchar(100) DEFAULT NULL,
  `is_read` tinyint(1) DEFAULT 0,
  `is_replied` tinyint(1) DEFAULT 0,
  `admin_notes` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Déchargement des données de la table `contact_messages`
--

INSERT INTO `contact_messages` (`id`, `name`, `email`, `subject`, `message`, `phone`, `company`, `is_read`, `is_replied`, `admin_notes`, `created_at`, `updated_at`) VALUES
(1, 'Kone Abdoul-Aziz Moriba', 'kabdoul2001@gmail.com', 'test cont', 'test messge', '0778181516', NULL, 1, 0, NULL, '2025-09-21 02:54:35', '2025-09-21 02:55:53');

-- --------------------------------------------------------

--
-- Structure de la table `medias`
--

CREATE TABLE `medias` (
  `id` int(11) NOT NULL,
  `title` varchar(255) NOT NULL,
  `description` text DEFAULT NULL,
  `file_path` varchar(500) NOT NULL,
  `file_type` varchar(50) NOT NULL,
  `media_type` enum('photo','video') NOT NULL,
  `alt_text` varchar(255) DEFAULT NULL,
  `file_size` int(11) DEFAULT 0,
  `is_active` tinyint(1) DEFAULT 1,
  `display_order` int(11) DEFAULT 0,
  `created_by` int(11) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Déchargement des données de la table `medias`
--

INSERT INTO `medias` (`id`, `title`, `description`, `file_path`, `file_type`, `media_type`, `alt_text`, `file_size`, `is_active`, `display_order`, `created_by`, `created_at`, `updated_at`) VALUES
(4, 'Présentation Entreprise', 'Vidéo de présentation de Universe Security', 'img/carousel.mp4', 'mp4', 'video', 'Présentation Universe Security', 0, 1, 1, 1, '2025-09-19 14:53:04', '2025-09-19 15:02:50'),
(21, 'test', 'dz\"ad', 'uploads/photos/Workstation_Photos_-_Download_Free_High-Quality_Pi_1758303281.jpg', 'jpg', 'photo', '', 0, 1, 1, 1, '2025-09-19 17:34:41', '2025-09-19 17:34:41'),
(23, 'test 2', 'azerrt', 'uploads/photos/This_is_what_a_businessman_looks_like_while_workin_1758304137.jpg', 'jpg', 'photo', '', 0, 1, 1, 1, '2025-09-19 17:48:57', '2025-09-19 17:48:57');

-- --------------------------------------------------------

--
-- Structure de la table `offers`
--

CREATE TABLE `offers` (
  `id` int(11) NOT NULL,
  `title` varchar(255) NOT NULL,
  `subtitle` varchar(255) DEFAULT NULL,
  `description` text DEFAULT NULL,
  `features` text DEFAULT NULL,
  `price` decimal(10,2) DEFAULT NULL,
  `price_text` varchar(100) DEFAULT NULL,
  `icon` varchar(100) DEFAULT NULL,
  `is_featured` tinyint(1) DEFAULT 0,
  `is_active` tinyint(1) DEFAULT 1,
  `display_order` int(11) DEFAULT 0,
  `created_by` int(11) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Déchargement des données de la table `offers`
--

INSERT INTO `offers` (`id`, `title`, `subtitle`, `description`, `features`, `price`, `price_text`, `icon`, `is_featured`, `is_active`, `display_order`, `created_by`, `created_at`, `updated_at`) VALUES
(4, 'test', 'test sous t', 'fdg', '[\"- propre\\r\",\"- s\\u00e9curis\\u00e9\"]', 150000.00, '', '', 0, 1, 0, 1, '2025-09-21 03:04:15', '2025-09-21 03:04:15');

-- --------------------------------------------------------

--
-- Structure de la table `products`
--

CREATE TABLE `products` (
  `id` int(11) NOT NULL,
  `name` varchar(100) NOT NULL,
  `description` text DEFAULT NULL,
  `price` decimal(10,2) DEFAULT NULL,
  `currency` varchar(10) DEFAULT 'XOF',
  `category` varchar(50) DEFAULT NULL,
  `image_url` varchar(255) DEFAULT NULL,
  `is_active` tinyint(1) DEFAULT 1,
  `is_featured` tinyint(1) DEFAULT 0,
  `display_order` int(11) DEFAULT 0,
  `stock_quantity` int(11) DEFAULT 0,
  `product_image` varchar(255) DEFAULT NULL,
  `specifications` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `created_by` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Structure de la table `quote_requests`
--

CREATE TABLE `quote_requests` (
  `id` int(11) NOT NULL,
  `name` varchar(100) NOT NULL,
  `email` varchar(100) NOT NULL,
  `phone` varchar(20) DEFAULT NULL,
  `service` varchar(100) NOT NULL,
  `message` text DEFAULT NULL,
  `status` enum('nouveau','en_cours','traite','refuse') DEFAULT 'nouveau',
  `priority` enum('basse','normale','haute','urgente') DEFAULT 'normale',
  `admin_notes` text DEFAULT NULL,
  `assigned_to` int(11) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Déchargement des données de la table `quote_requests`
--

INSERT INTO `quote_requests` (`id`, `name`, `email`, `phone`, `service`, `message`, `status`, `priority`, `admin_notes`, `assigned_to`, `created_at`, `updated_at`) VALUES
(1, 'Test User', 'test@example.com', '0778181516', 'Développement Web et Mobile', 'heyehhs', 'en_cours', 'normale', '', NULL, '2025-09-18 01:38:19', '2025-09-18 01:39:09'),
(2, 'Test User 2', 'test2@example.com', '0103858458', 'Intelligence Artificielle', 'gfrfe', 'nouveau', 'normale', NULL, NULL, '2025-09-18 01:42:36', '2025-09-18 01:42:36'),
(3, 'Test User 2', 'test2@example.com', '0103858458', 'Intelligence Artificielle', 'gfrfe', 'nouveau', 'normale', NULL, NULL, '2025-09-18 02:01:06', '2025-09-18 02:01:06');

-- --------------------------------------------------------

--
-- Structure de la table `services`
--

CREATE TABLE `services` (
  `id` int(11) NOT NULL,
  `title` varchar(100) NOT NULL,
  `description` text NOT NULL,
  `icon` varchar(50) DEFAULT NULL,
  `price` decimal(10,2) DEFAULT NULL,
  `currency` varchar(10) DEFAULT 'XOF',
  `is_active` tinyint(1) DEFAULT 1,
  `display_order` int(11) DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `created_by` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Déchargement des données de la table `services`
--

INSERT INTO `services` (`id`, `title`, `description`, `icon`, `price`, `currency`, `is_active`, `display_order`, `created_at`, `updated_at`, `created_by`) VALUES
(6, 'Matériels informatiques', 'Un service complet de vente et maintenance de matériels informatiques adaptés à vos besoins', 'fa-solid fa-laptop', NULL, 'XOF', 1, 0, '2025-09-20 19:34:19', '2025-09-20 19:59:40', 1);

-- --------------------------------------------------------

--
-- Structure de la table `site_settings`
--

CREATE TABLE `site_settings` (
  `id` int(11) NOT NULL,
  `setting_key` varchar(50) NOT NULL,
  `setting_value` text DEFAULT NULL,
  `setting_type` enum('text','number','boolean','json') DEFAULT 'text',
  `description` text DEFAULT NULL,
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `updated_by` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Déchargement des données de la table `site_settings`
--

INSERT INTO `site_settings` (`id`, `setting_key`, `setting_value`, `setting_type`, `description`, `updated_at`, `updated_by`) VALUES
(1, 'site_name', 'Universe Security', 'text', 'Nom du site', '2025-09-18 01:03:37', NULL),
(2, 'contact_email', 'contact@universesecurity.com', 'text', 'Email de contact principal', '2025-09-18 01:03:37', NULL),
(3, 'contact_phone', '+225 0101012501', 'text', 'Téléphone de contact', '2025-09-18 01:03:37', NULL),
(4, 'address', 'Cocody Angré 8ième, Abidjan, Côte d\'Ivoire', 'text', 'Adresse de l\'entreprise', '2025-09-18 01:03:37', NULL),
(5, 'maintenance_mode', '1', 'boolean', 'Mode maintenance du site', '2025-09-21 00:42:14', NULL),
(6, 'google_analytics_id', '', 'text', 'ID Google Analytics', '2025-09-18 01:03:37', NULL);

-- --------------------------------------------------------

--
-- Structure de la table `site_visits`
--

CREATE TABLE `site_visits` (
  `id` int(11) NOT NULL,
  `ip_address` varchar(45) DEFAULT NULL,
  `user_agent` text DEFAULT NULL,
  `page_url` varchar(255) DEFAULT NULL,
  `referrer` varchar(255) DEFAULT NULL,
  `country` varchar(50) DEFAULT NULL,
  `city` varchar(50) DEFAULT NULL,
  `visit_date` date DEFAULT NULL,
  `visit_time` time DEFAULT NULL,
  `session_id` varchar(100) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Déchargement des données de la table `site_visits`
--

INSERT INTO `site_visits` (`id`, `ip_address`, `user_agent`, `page_url`, `referrer`, `country`, `city`, `visit_date`, `visit_time`, `session_id`, `created_at`) VALUES
(1, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', '/universe-security/', 'http://localhost/', NULL, NULL, '2025-09-18', '01:23:12', 'pmpsc13asnm4r52fat00st9su6', '2025-09-18 01:23:12'),
(2, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', '/universe-security/index.php', 'http://localhost/universe-security/admin/login.php', NULL, NULL, '2025-09-18', '01:41:55', 'pmpsc13asnm4r52fat00st9su6', '2025-09-18 01:41:55'),
(3, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', '/universe-security/index.php?', 'http://localhost/universe-security/index.php', NULL, NULL, '2025-09-18', '02:22:01', 'pmpsc13asnm4r52fat00st9su6', '2025-09-18 02:22:01'),
(4, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', '/universe-security/', 'http://localhost/', NULL, NULL, '2025-09-19', '08:56:27', '6thj92sfnrs5ieou0vgp15j5jj', '2025-09-19 08:56:27'),
(5, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', '/universe-security/index.php', 'http://localhost/universe-security/admin/login.php', NULL, NULL, '2025-09-19', '09:15:14', '6thj92sfnrs5ieou0vgp15j5jj', '2025-09-19 09:15:14'),
(6, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', '/universe-security/index.php/admin', NULL, NULL, NULL, '2025-09-19', '11:14:00', '6thj92sfnrs5ieou0vgp15j5jj', '2025-09-19 11:14:00'),
(7, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', '/universe-security/index.php/lib/owlcarousel/assets/owl.carousel.min.css', 'http://localhost/universe-security/index.php/admin', NULL, NULL, '2025-09-19', '11:14:01', '6thj92sfnrs5ieou0vgp15j5jj', '2025-09-19 11:14:01'),
(8, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', '/universe-security/index.php/lib/animate/animate.min.css', 'http://localhost/universe-security/index.php/admin', NULL, NULL, '2025-09-19', '11:14:02', '6thj92sfnrs5ieou0vgp15j5jj', '2025-09-19 11:14:02'),
(9, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', '/universe-security/index.php/css/bootstrap.min.css', 'http://localhost/universe-security/index.php/admin', NULL, NULL, '2025-09-19', '11:14:02', '6thj92sfnrs5ieou0vgp15j5jj', '2025-09-19 11:14:02'),
(10, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', '/universe-security/index.php/css/style.css', 'http://localhost/universe-security/index.php/admin', NULL, NULL, '2025-09-19', '11:14:03', '6thj92sfnrs5ieou0vgp15j5jj', '2025-09-19 11:14:03'),
(11, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', '/universe-security/index.php/img/logo%20universe%20security.png', 'http://localhost/universe-security/index.php/admin', NULL, NULL, '2025-09-19', '11:14:03', '6thj92sfnrs5ieou0vgp15j5jj', '2025-09-19 11:14:03'),
(12, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', '/universe-security/index.php/img/about.jpg', 'http://localhost/universe-security/index.php/admin', NULL, NULL, '2025-09-19', '11:14:04', '6thj92sfnrs5ieou0vgp15j5jj', '2025-09-19 11:14:04'),
(13, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', '/universe-security/index.php/img/feature.jpg', 'http://localhost/universe-security/index.php/admin', NULL, NULL, '2025-09-19', '11:14:04', '6thj92sfnrs5ieou0vgp15j5jj', '2025-09-19 11:14:04'),
(14, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', '/universe-security/index.php/img/team-2.jpg', 'http://localhost/universe-security/index.php/admin', NULL, NULL, '2025-09-19', '11:14:04', '6thj92sfnrs5ieou0vgp15j5jj', '2025-09-19 11:14:04'),
(15, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', '/universe-security/index.php/img/team-1.jpg', 'http://localhost/universe-security/index.php/admin', NULL, NULL, '2025-09-19', '11:14:05', '6thj92sfnrs5ieou0vgp15j5jj', '2025-09-19 11:14:05'),
(16, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', '/universe-security/index.php/lib/wow/wow.min.js', 'http://localhost/universe-security/index.php/admin', NULL, NULL, '2025-09-19', '11:14:05', '6thj92sfnrs5ieou0vgp15j5jj', '2025-09-19 11:14:05'),
(17, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', '/universe-security/index.php/lib/easing/easing.min.js', 'http://localhost/universe-security/index.php/admin', NULL, NULL, '2025-09-19', '11:14:05', '6thj92sfnrs5ieou0vgp15j5jj', '2025-09-19 11:14:05'),
(18, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', '/universe-security/index.php/lib/waypoints/waypoints.min.js', 'http://localhost/universe-security/index.php/admin', NULL, NULL, '2025-09-19', '11:14:06', '6thj92sfnrs5ieou0vgp15j5jj', '2025-09-19 11:14:06'),
(19, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', '/universe-security/index.php/lib/counterup/counterup.min.js', 'http://localhost/universe-security/index.php/admin', NULL, NULL, '2025-09-19', '11:14:06', '6thj92sfnrs5ieou0vgp15j5jj', '2025-09-19 11:14:06'),
(20, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', '/universe-security/index.php/lib/owlcarousel/owl.carousel.min.js', 'http://localhost/universe-security/index.php/admin', NULL, NULL, '2025-09-19', '11:14:07', '6thj92sfnrs5ieou0vgp15j5jj', '2025-09-19 11:14:07'),
(21, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', '/universe-security/index.php/js/main.js', 'http://localhost/universe-security/index.php/admin', NULL, NULL, '2025-09-19', '11:14:07', '6thj92sfnrs5ieou0vgp15j5jj', '2025-09-19 11:14:07'),
(22, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', '/universe-security/index.php/js/neural-animation.js', 'http://localhost/universe-security/index.php/admin', NULL, NULL, '2025-09-19', '11:14:07', '6thj92sfnrs5ieou0vgp15j5jj', '2025-09-19 11:14:07'),
(23, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', '/universe-security/index.php/js/active-nav.js', 'http://localhost/universe-security/index.php/admin', NULL, NULL, '2025-09-19', '11:14:07', '6thj92sfnrs5ieou0vgp15j5jj', '2025-09-19 11:14:07'),
(24, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', '/universe-security/index.php/js/theme-toggle.js', 'http://localhost/universe-security/index.php/admin', NULL, NULL, '2025-09-19', '11:14:07', '6thj92sfnrs5ieou0vgp15j5jj', '2025-09-19 11:14:07'),
(25, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', '/universe-security/index.php/img/team-3.jpg', 'http://localhost/universe-security/index.php/admin', NULL, NULL, '2025-09-19', '11:14:07', '6thj92sfnrs5ieou0vgp15j5jj', '2025-09-19 11:14:07'),
(26, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', '/universe-security/index.php/img/blog-1.jpg', 'http://localhost/universe-security/index.php/admin', NULL, NULL, '2025-09-19', '11:14:07', '6thj92sfnrs5ieou0vgp15j5jj', '2025-09-19 11:14:07'),
(27, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', '/universe-security/index.php/img/blog-2.jpg', 'http://localhost/universe-security/index.php/admin', NULL, NULL, '2025-09-19', '11:14:07', '6thj92sfnrs5ieou0vgp15j5jj', '2025-09-19 11:14:07'),
(28, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', '/universe-security/index.php/img/blog-3.jpg', 'http://localhost/universe-security/index.php/admin', NULL, NULL, '2025-09-19', '11:14:07', '6thj92sfnrs5ieou0vgp15j5jj', '2025-09-19 11:14:07'),
(29, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', '/universe-security/index.php/img/snc.jpg', 'http://localhost/universe-security/index.php/admin', NULL, NULL, '2025-09-19', '11:14:07', '6thj92sfnrs5ieou0vgp15j5jj', '2025-09-19 11:14:07'),
(30, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', '/universe-security/index.php/img/logo%20universe%20security.jpg', 'http://localhost/universe-security/index.php/admin', NULL, NULL, '2025-09-19', '11:14:07', '6thj92sfnrs5ieou0vgp15j5jj', '2025-09-19 11:14:07'),
(31, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', '/universe-security/index.php/img/logo_advantech.jpg', 'http://localhost/universe-security/index.php/admin', NULL, NULL, '2025-09-19', '11:14:07', '6thj92sfnrs5ieou0vgp15j5jj', '2025-09-19 11:14:07'),
(32, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', '/universe-security/index.php/img/carousel.mp4', 'http://localhost/universe-security/index.php/admin', NULL, NULL, '2025-09-19', '11:14:07', '6thj92sfnrs5ieou0vgp15j5jj', '2025-09-19 11:14:07'),
(33, '::1', 'colly - https://github.com/gocolly/colly', '/universe-security/test_media_display.php', NULL, NULL, NULL, '2025-09-19', '16:04:12', 'k912e66r91c7l56q57vqv3nas5', '2025-09-19 16:04:12'),
(34, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', '/universe-security/index.php?', 'http://localhost/universe-security/index.php', NULL, NULL, '2025-09-19', '19:21:59', '', '2025-09-19 19:21:59'),
(35, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', '/universe-security/', 'http://localhost/', NULL, NULL, '2025-09-20', '10:59:16', '', '2025-09-20 10:59:16'),
(36, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', '/universe-security/index.php', 'http://localhost/universe-security/tarification.php', NULL, NULL, '2025-09-20', '11:02:16', '', '2025-09-20 11:02:16'),
(37, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', '/universe-security/index.php', 'http://localhost/universe-security/blog.php', NULL, NULL, '2025-09-21', '00:00:08', '', '2025-09-21 00:00:08'),
(38, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', '/universe-security/index.php/admin', NULL, NULL, NULL, '2025-09-21', '00:05:27', '', '2025-09-21 00:05:27'),
(39, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', '/universe-security/index.php/lib/owlcarousel/assets/owl.carousel.min.css', 'http://localhost/universe-security/index.php/admin', NULL, NULL, '2025-09-21', '00:05:27', '', '2025-09-21 00:05:27'),
(40, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', '/universe-security/index.php/img/logo%20universe%20security.png', 'http://localhost/universe-security/index.php/admin', NULL, NULL, '2025-09-21', '00:05:27', '', '2025-09-21 00:05:27'),
(41, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', '/universe-security/index.php/img/about.jpg', 'http://localhost/universe-security/index.php/admin', NULL, NULL, '2025-09-21', '00:05:27', '', '2025-09-21 00:05:27'),
(42, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', '/universe-security/index.php/css/style.css', 'http://localhost/universe-security/index.php/admin', NULL, NULL, '2025-09-21', '00:05:27', '', '2025-09-21 00:05:27'),
(43, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', '/universe-security/index.php/lib/animate/animate.min.css', 'http://localhost/universe-security/index.php/admin', NULL, NULL, '2025-09-21', '00:05:27', '', '2025-09-21 00:05:27'),
(44, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', '/universe-security/index.php/css/bootstrap.min.css', 'http://localhost/universe-security/index.php/admin', NULL, NULL, '2025-09-21', '00:05:27', '', '2025-09-21 00:05:27'),
(45, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', '/universe-security/index.php/img/snc.jpg', 'http://localhost/universe-security/index.php/admin', NULL, NULL, '2025-09-21', '00:05:28', '', '2025-09-21 00:05:28'),
(46, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', '/universe-security/index.php/uploads/team/member_1758373850_8466.jpg', 'http://localhost/universe-security/index.php/admin', NULL, NULL, '2025-09-21', '00:05:28', '', '2025-09-21 00:05:28'),
(47, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', '/universe-security/index.php/img/feature.jpg', 'http://localhost/universe-security/index.php/admin', NULL, NULL, '2025-09-21', '00:05:28', '', '2025-09-21 00:05:28'),
(48, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', '/universe-security/index.php/lib/easing/easing.min.js', 'http://localhost/universe-security/index.php/admin', NULL, NULL, '2025-09-21', '00:05:28', '', '2025-09-21 00:05:28'),
(49, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', '/universe-security/index.php/lib/wow/wow.min.js', 'http://localhost/universe-security/index.php/admin', NULL, NULL, '2025-09-21', '00:05:28', '', '2025-09-21 00:05:28'),
(50, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', '/universe-security/index.php/lib/waypoints/waypoints.min.js', 'http://localhost/universe-security/index.php/admin', NULL, NULL, '2025-09-21', '00:05:28', '', '2025-09-21 00:05:28'),
(51, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', '/universe-security/index.php/lib/counterup/counterup.min.js', 'http://localhost/universe-security/index.php/admin', NULL, NULL, '2025-09-21', '00:05:28', '', '2025-09-21 00:05:28'),
(52, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', '/universe-security/index.php/lib/owlcarousel/owl.carousel.min.js', 'http://localhost/universe-security/index.php/admin', NULL, NULL, '2025-09-21', '00:05:28', '', '2025-09-21 00:05:28'),
(53, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', '/universe-security/index.php/js/main.js', 'http://localhost/universe-security/index.php/admin', NULL, NULL, '2025-09-21', '00:05:28', '', '2025-09-21 00:05:28'),
(54, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', '/universe-security/index.php/js/neural-animation.js', 'http://localhost/universe-security/index.php/admin', NULL, NULL, '2025-09-21', '00:05:28', '', '2025-09-21 00:05:28'),
(55, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', '/universe-security/index.php/js/active-nav.js', 'http://localhost/universe-security/index.php/admin', NULL, NULL, '2025-09-21', '00:05:28', '', '2025-09-21 00:05:28'),
(56, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', '/universe-security/index.php/img/logo%20universe%20security.jpg', 'http://localhost/universe-security/index.php/admin', NULL, NULL, '2025-09-21', '00:05:28', '', '2025-09-21 00:05:28'),
(57, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', '/universe-security/index.php/js/theme-toggle.js', 'http://localhost/universe-security/index.php/admin', NULL, NULL, '2025-09-21', '00:05:28', '', '2025-09-21 00:05:28'),
(58, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', '/universe-security/index.php/img/logo_advantech.jpg', 'http://localhost/universe-security/index.php/admin', NULL, NULL, '2025-09-21', '00:05:29', '', '2025-09-21 00:05:29'),
(59, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', '/universe-security/index.php/img/carousel.mp4', 'http://localhost/universe-security/index.php/admin', NULL, NULL, '2025-09-21', '00:05:29', '', '2025-09-21 00:05:29'),
(60, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', '/universe-security/', 'http://localhost/', NULL, NULL, '2025-09-21', '02:25:44', '', '2025-09-21 02:25:44');

-- --------------------------------------------------------

--
-- Structure de la table `team_members`
--

CREATE TABLE `team_members` (
  `id` int(11) NOT NULL,
  `name` varchar(255) NOT NULL,
  `position` varchar(255) NOT NULL,
  `bio` text DEFAULT NULL,
  `image` varchar(255) DEFAULT NULL,
  `email` varchar(255) DEFAULT NULL,
  `phone` varchar(50) DEFAULT NULL,
  `social_facebook` varchar(255) DEFAULT NULL,
  `social_twitter` varchar(255) DEFAULT NULL,
  `social_linkedin` varchar(255) DEFAULT NULL,
  `social_instagram` varchar(255) DEFAULT NULL,
  `is_active` tinyint(1) DEFAULT 1,
  `display_order` int(11) DEFAULT 0,
  `created_by` int(11) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Déchargement des données de la table `team_members`
--

INSERT INTO `team_members` (`id`, `name`, `position`, `bio`, `image`, `email`, `phone`, `social_facebook`, `social_twitter`, `social_linkedin`, `social_instagram`, `is_active`, `display_order`, `created_by`, `created_at`, `updated_at`) VALUES
(4, 'Kone Abdoul-Aziz Moriba', 'Informaticien & CM', 'Expert en Dev Web', 'uploads/team/member_1758373850_8466.jpg', 'kabdoul2001@gmail.com', '0778181516', '', '', '', '', 1, 1, 1, '2025-09-20 13:10:50', '2025-09-20 13:10:50');

-- --------------------------------------------------------

--
-- Structure de la table `testimonials`
--

CREATE TABLE `testimonials` (
  `id` int(11) NOT NULL,
  `client_name` varchar(100) NOT NULL,
  `client_position` varchar(100) DEFAULT NULL,
  `client_company` varchar(100) DEFAULT NULL,
  `content` text NOT NULL,
  `rating` int(11) DEFAULT 5 CHECK (`rating` >= 1 and `rating` <= 5),
  `client_image` varchar(255) DEFAULT NULL,
  `is_approved` tinyint(1) DEFAULT 0,
  `is_featured` tinyint(1) DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `approved_by` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Déchargement des données de la table `testimonials`
--

INSERT INTO `testimonials` (`id`, `client_name`, `client_position`, `client_company`, `content`, `rating`, `client_image`, `is_approved`, `is_featured`, `created_at`, `updated_at`, `approved_by`) VALUES
(2, 'test2', 'test2p', 'test2e', 'testttttt', 5, '', 1, 1, '2025-09-19 09:14:43', '2025-09-20 13:22:45', 1),
(3, 'test2', 'test2p', 'test2e', 'ezfffez', 5, '', 1, 0, '2025-09-19 19:19:51', '2025-09-19 19:19:51', NULL);

-- --------------------------------------------------------

--
-- Structure de la table `vendors`
--

CREATE TABLE `vendors` (
  `id` int(11) NOT NULL,
  `name` varchar(100) NOT NULL,
  `logo` varchar(255) NOT NULL,
  `website` varchar(255) DEFAULT NULL,
  `description` text DEFAULT NULL,
  `is_active` tinyint(1) DEFAULT 1,
  `display_order` int(11) DEFAULT 0,
  `created_by` int(11) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Déchargement des données de la table `vendors`
--

INSERT INTO `vendors` (`id`, `name`, `logo`, `website`, `description`, `is_active`, `display_order`, `created_by`, `created_at`, `updated_at`) VALUES
(1, 'SNC Lavalin', 'img/snc.jpg', 'https://www.snclavalin.com', 'Partenaire technologique majeur', 1, 1, NULL, '2025-09-21 02:51:13', '2025-09-21 02:51:13'),
(2, 'Universe Security', 'img/logo universe security.jpg', '#', 'Notre entreprise', 1, 2, NULL, '2025-09-21 02:51:13', '2025-09-21 02:51:13'),
(3, 'Advantech', 'img/logo_advantech.jpg', 'https://www.advantech.com', 'Solutions technologiques avancées', 1, 3, NULL, '2025-09-21 02:51:13', '2025-09-21 02:51:13');

--
-- Index pour les tables déchargées
--

--
-- Index pour la table `about_content`
--
ALTER TABLE `about_content`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `section_key` (`section_key`),
  ADD KEY `created_by` (`created_by`),
  ADD KEY `idx_section_key` (`section_key`),
  ADD KEY `idx_is_active` (`is_active`);

--
-- Index pour la table `admins`
--
ALTER TABLE `admins`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `username` (`username`),
  ADD UNIQUE KEY `email` (`email`);

--
-- Index pour la table `admin_logs`
--
ALTER TABLE `admin_logs`
  ADD PRIMARY KEY (`id`),
  ADD KEY `admin_id` (`admin_id`);

--
-- Index pour la table `blog_articles`
--
ALTER TABLE `blog_articles`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `slug` (`slug`),
  ADD KEY `author_id` (`author_id`);

--
-- Index pour la table `contact_messages`
--
ALTER TABLE `contact_messages`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_created_at` (`created_at`),
  ADD KEY `idx_is_read` (`is_read`),
  ADD KEY `idx_email` (`email`);

--
-- Index pour la table `medias`
--
ALTER TABLE `medias`
  ADD PRIMARY KEY (`id`),
  ADD KEY `created_by` (`created_by`),
  ADD KEY `idx_media_type` (`media_type`),
  ADD KEY `idx_is_active` (`is_active`),
  ADD KEY `idx_display_order` (`display_order`);

--
-- Index pour la table `offers`
--
ALTER TABLE `offers`
  ADD PRIMARY KEY (`id`),
  ADD KEY `created_by` (`created_by`);

--
-- Index pour la table `products`
--
ALTER TABLE `products`
  ADD PRIMARY KEY (`id`),
  ADD KEY `created_by` (`created_by`);

--
-- Index pour la table `quote_requests`
--
ALTER TABLE `quote_requests`
  ADD PRIMARY KEY (`id`),
  ADD KEY `assigned_to` (`assigned_to`);

--
-- Index pour la table `services`
--
ALTER TABLE `services`
  ADD PRIMARY KEY (`id`),
  ADD KEY `created_by` (`created_by`);

--
-- Index pour la table `site_settings`
--
ALTER TABLE `site_settings`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `setting_key` (`setting_key`),
  ADD KEY `updated_by` (`updated_by`);

--
-- Index pour la table `site_visits`
--
ALTER TABLE `site_visits`
  ADD PRIMARY KEY (`id`);

--
-- Index pour la table `team_members`
--
ALTER TABLE `team_members`
  ADD PRIMARY KEY (`id`),
  ADD KEY `created_by` (`created_by`);

--
-- Index pour la table `testimonials`
--
ALTER TABLE `testimonials`
  ADD PRIMARY KEY (`id`),
  ADD KEY `approved_by` (`approved_by`);

--
-- Index pour la table `vendors`
--
ALTER TABLE `vendors`
  ADD PRIMARY KEY (`id`),
  ADD KEY `created_by` (`created_by`),
  ADD KEY `idx_is_active` (`is_active`),
  ADD KEY `idx_display_order` (`display_order`);

--
-- AUTO_INCREMENT pour les tables déchargées
--

--
-- AUTO_INCREMENT pour la table `about_content`
--
ALTER TABLE `about_content`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT pour la table `admins`
--
ALTER TABLE `admins`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT pour la table `admin_logs`
--
ALTER TABLE `admin_logs`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=113;

--
-- AUTO_INCREMENT pour la table `blog_articles`
--
ALTER TABLE `blog_articles`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT pour la table `contact_messages`
--
ALTER TABLE `contact_messages`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT pour la table `medias`
--
ALTER TABLE `medias`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=24;

--
-- AUTO_INCREMENT pour la table `offers`
--
ALTER TABLE `offers`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT pour la table `products`
--
ALTER TABLE `products`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT pour la table `quote_requests`
--
ALTER TABLE `quote_requests`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT pour la table `services`
--
ALTER TABLE `services`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT pour la table `site_settings`
--
ALTER TABLE `site_settings`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT pour la table `site_visits`
--
ALTER TABLE `site_visits`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=61;

--
-- AUTO_INCREMENT pour la table `team_members`
--
ALTER TABLE `team_members`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT pour la table `testimonials`
--
ALTER TABLE `testimonials`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT pour la table `vendors`
--
ALTER TABLE `vendors`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- Contraintes pour les tables déchargées
--

--
-- Contraintes pour la table `about_content`
--
ALTER TABLE `about_content`
  ADD CONSTRAINT `about_content_ibfk_1` FOREIGN KEY (`created_by`) REFERENCES `admins` (`id`) ON DELETE SET NULL;

--
-- Contraintes pour la table `admin_logs`
--
ALTER TABLE `admin_logs`
  ADD CONSTRAINT `admin_logs_ibfk_1` FOREIGN KEY (`admin_id`) REFERENCES `admins` (`id`) ON DELETE CASCADE;

--
-- Contraintes pour la table `blog_articles`
--
ALTER TABLE `blog_articles`
  ADD CONSTRAINT `blog_articles_ibfk_1` FOREIGN KEY (`author_id`) REFERENCES `admins` (`id`) ON DELETE SET NULL;

--
-- Contraintes pour la table `medias`
--
ALTER TABLE `medias`
  ADD CONSTRAINT `medias_ibfk_1` FOREIGN KEY (`created_by`) REFERENCES `admins` (`id`) ON DELETE SET NULL;

--
-- Contraintes pour la table `offers`
--
ALTER TABLE `offers`
  ADD CONSTRAINT `offers_ibfk_1` FOREIGN KEY (`created_by`) REFERENCES `admins` (`id`) ON DELETE SET NULL;

--
-- Contraintes pour la table `products`
--
ALTER TABLE `products`
  ADD CONSTRAINT `products_ibfk_1` FOREIGN KEY (`created_by`) REFERENCES `admins` (`id`) ON DELETE SET NULL;

--
-- Contraintes pour la table `quote_requests`
--
ALTER TABLE `quote_requests`
  ADD CONSTRAINT `quote_requests_ibfk_1` FOREIGN KEY (`assigned_to`) REFERENCES `admins` (`id`) ON DELETE SET NULL;

--
-- Contraintes pour la table `services`
--
ALTER TABLE `services`
  ADD CONSTRAINT `services_ibfk_1` FOREIGN KEY (`created_by`) REFERENCES `admins` (`id`) ON DELETE SET NULL;

--
-- Contraintes pour la table `site_settings`
--
ALTER TABLE `site_settings`
  ADD CONSTRAINT `site_settings_ibfk_1` FOREIGN KEY (`updated_by`) REFERENCES `admins` (`id`) ON DELETE SET NULL;

--
-- Contraintes pour la table `team_members`
--
ALTER TABLE `team_members`
  ADD CONSTRAINT `team_members_ibfk_1` FOREIGN KEY (`created_by`) REFERENCES `admins` (`id`) ON DELETE SET NULL;

--
-- Contraintes pour la table `testimonials`
--
ALTER TABLE `testimonials`
  ADD CONSTRAINT `testimonials_ibfk_1` FOREIGN KEY (`approved_by`) REFERENCES `admins` (`id`) ON DELETE SET NULL;

--
-- Contraintes pour la table `vendors`
--
ALTER TABLE `vendors`
  ADD CONSTRAINT `vendors_ibfk_1` FOREIGN KEY (`created_by`) REFERENCES `admins` (`id`) ON DELETE SET NULL;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
