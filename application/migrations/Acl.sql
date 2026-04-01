-- phpMyAdmin SQL Dump
-- version 5.0.4
-- https://www.phpmyadmin.net/
--
-- Hôte : localhost
-- Généré le : jeu. 21 juil. 2022 à 21:01
-- Version du serveur :  5.7.11
-- Version de PHP : 7.4.11

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Base de données : `travaux_v2`
--

-- --------------------------------------------------------

--
-- Structure de la table `acl_actions`
--

CREATE TABLE `acl_actions` (
  `id` int(11) NOT NULL,
  `id_ctrl` int(10) NOT NULL,
  `action` varchar(255) NOT NULL,
  `created` datetime NOT NULL,
  `updated` datetime NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

--
-- Déchargement des données de la table `acl_actions`
--

INSERT INTO `acl_actions` (`id`, `id_ctrl`, `action`, `created`, `updated`) VALUES
(1, 0, 'list', '2021-10-21 08:14:51', '0000-00-00 00:00:00'),
(2, 0, 'edit', '2021-10-21 08:14:51', '0000-00-00 00:00:00'),
(3, 0, 'add', '2021-10-21 08:14:51', '0000-00-00 00:00:00'),
(4, 0, 'view', '2021-10-21 08:14:51', '0000-00-00 00:00:00'),
(5, 0, 'delete', '2021-10-21 08:14:51', '0000-00-00 00:00:00'),
(6, 1, 'add', '2021-10-21 08:15:25', '2022-06-27 08:01:14'),
(7, 1, 'edit', '2021-10-21 08:15:25', '2022-06-27 08:01:14'),
(8, 1, 'view', '2021-10-21 08:15:25', '2022-06-27 08:01:14'),
(9, 1, 'list', '2021-10-21 08:15:25', '2022-06-27 08:01:14'),
(10, 1, 'delete', '2021-10-21 08:15:25', '2022-06-27 08:01:14'),
(11, 2, 'add', '2021-10-21 08:15:56', '0000-00-00 00:00:00'),
(12, 2, 'edit', '2021-10-21 08:15:56', '0000-00-00 00:00:00'),
(13, 2, 'list', '2021-10-21 08:15:56', '0000-00-00 00:00:00'),
(14, 2, 'view', '2021-10-21 08:15:56', '0000-00-00 00:00:00'),
(15, 2, 'delete', '2021-10-21 08:15:56', '0000-00-00 00:00:00'),
(16, 3, 'add', '2021-10-21 08:16:23', '2021-12-07 03:11:53'),
(17, 3, 'edit', '2021-10-21 08:16:23', '2021-12-07 03:11:53'),
(18, 3, 'list', '2021-10-21 08:16:23', '2021-12-07 03:11:53'),
(19, 3, 'view', '2021-10-21 08:16:23', '2021-12-07 03:11:53'),
(20, 3, 'delete', '2021-10-21 08:16:23', '2021-12-07 03:11:53'),
(21, 4, 'add', '2021-10-21 08:16:58', '0000-00-00 00:00:00'),
(22, 4, 'edit', '2021-10-21 08:16:58', '0000-00-00 00:00:00'),
(23, 4, 'view', '2021-10-21 08:16:58', '0000-00-00 00:00:00'),
(24, 4, 'list', '2021-10-21 08:16:58', '0000-00-00 00:00:00'),
(25, 4, 'delete', '2021-10-21 08:16:58', '0000-00-00 00:00:00'),
(26, 5, 'add', '2021-10-21 08:17:23', '2021-11-24 08:48:29'),
(27, 5, 'edit', '2021-10-21 08:17:23', '2021-11-24 08:48:29'),
(28, 5, 'view', '2021-10-21 08:17:23', '2021-11-24 08:48:29'),
(29, 5, 'list', '2021-10-21 08:17:23', '2021-11-24 08:48:29'),
(30, 5, 'delete', '2021-10-21 08:17:23', '2021-11-24 08:48:29'),
(31, 1, 'histo', '0000-00-00 00:00:00', '2022-06-27 08:01:14'),
(32, 1, 'check', '0000-00-00 00:00:00', '2022-06-27 08:01:14'),
(33, 1, 'units', '0000-00-00 00:00:00', '2022-06-27 08:01:14'),
(34, 5, 'orga', '0000-00-00 00:00:00', '2021-11-24 08:48:29'),
(35, 3, 'register', '0000-00-00 00:00:00', '2021-12-07 03:11:53'),
(36, 3, 'register_one', '0000-00-00 00:00:00', '2021-12-07 03:11:53'),
(37, 6, 'list', '2021-11-08 09:15:00', '0000-00-00 00:00:00'),
(38, 6, 'add', '2021-11-08 09:15:00', '0000-00-00 00:00:00'),
(39, 6, 'edit', '2021-11-08 09:15:00', '0000-00-00 00:00:00'),
(40, 6, 'view', '2021-11-08 09:15:00', '0000-00-00 00:00:00'),
(41, 6, 'delete', '2021-11-08 09:15:00', '0000-00-00 00:00:00'),
(42, 7, 'add', '2021-11-09 03:17:21', '2021-11-09 04:19:51'),
(43, 7, 'edit', '2021-11-09 03:17:21', '2021-11-09 04:19:51'),
(44, 7, 'delete', '2021-11-09 03:17:21', '2021-11-09 04:19:51'),
(45, 7, 'list', '2021-11-09 03:17:21', '2021-11-09 04:19:51'),
(46, 7, 'view', '2021-11-09 03:17:21', '2021-11-09 04:19:51'),
(47, 8, 'add', '2021-11-09 03:17:42', '2021-11-09 04:22:24'),
(48, 8, 'edit', '2021-11-09 03:17:42', '2021-11-09 04:22:24'),
(49, 8, 'list', '2021-11-09 03:17:42', '2021-11-09 04:22:24'),
(50, 8, 'view', '2021-11-09 03:17:42', '2021-11-09 04:22:24'),
(51, 8, 'delete', '2021-11-09 03:17:42', '2021-11-09 04:22:24'),
(52, 9, 'add', '2021-11-09 03:18:04', '0000-00-00 00:00:00'),
(53, 9, 'view', '2021-11-09 03:18:04', '0000-00-00 00:00:00'),
(54, 9, 'edit', '2021-11-09 03:18:04', '0000-00-00 00:00:00'),
(55, 9, 'list', '2021-11-09 03:18:04', '0000-00-00 00:00:00'),
(56, 9, 'delete', '2021-11-09 03:18:04', '0000-00-00 00:00:00'),
(57, 10, 'add', '2021-11-09 03:18:24', '0000-00-00 00:00:00'),
(58, 10, 'edit', '2021-11-09 03:18:24', '0000-00-00 00:00:00'),
(59, 10, 'view', '2021-11-09 03:18:24', '0000-00-00 00:00:00'),
(60, 10, 'list', '2021-11-09 03:18:24', '0000-00-00 00:00:00'),
(61, 10, 'delete', '2021-11-09 03:18:24', '0000-00-00 00:00:00'),
(62, 11, 'add', '2021-11-09 04:12:34', '2021-11-09 04:20:54'),
(63, 11, 'edit', '2021-11-09 04:12:34', '2021-11-09 04:20:54'),
(64, 11, 'delete', '2021-11-09 04:12:34', '2021-11-09 04:20:54'),
(65, 11, 'list', '2021-11-09 04:12:34', '2021-11-09 04:20:54'),
(66, 11, 'view', '2021-11-09 04:12:34', '2021-11-09 04:20:54'),
(67, 12, 'list', '2021-11-09 04:21:26', '0000-00-00 00:00:00'),
(68, 8, 'set_rules', '0000-00-00 00:00:00', '2021-11-09 04:22:24'),
(69, 5, 'JsonData', '0000-00-00 00:00:00', '2021-11-24 08:48:29'),
(70, 3, 'Jsondata', '0000-00-00 00:00:00', '2021-12-07 03:11:53'),
(71, 1, 'histofamily', '0000-00-00 00:00:00', '2022-06-27 08:01:14'),
(72, 13, 'myaccount', '2022-06-20 11:04:48', '0000-00-00 00:00:00'),
(73, 1, 'MaJCompetence', '0000-00-00 00:00:00', '2022-06-27 08:01:14');

-- --------------------------------------------------------

--
-- Structure de la table `acl_controllers`
--

CREATE TABLE `acl_controllers` (
  `id` int(11) NOT NULL,
  `controller` varchar(255) NOT NULL,
  `actions` varchar(255) NOT NULL,
  `created` datetime NOT NULL,
  `updated` datetime NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

--
-- Déchargement des données de la table `acl_controllers`
--

INSERT INTO `acl_controllers` (`id`, `controller`, `actions`, `created`, `updated`) VALUES
(1, 'Familys_controller', '[\"add\",\"edit\",\"view\",\"list\",\"delete\",\"histo\",\"check\",\"units\",\"histofamily\",\"MaJCompetence\"]', '2021-10-21 08:14:51', '2022-06-27 08:01:14'),
(2, 'Histo_controller', '[\"add\",\"edit\",\"list\",\"view\",\"delete\"]', '2021-10-21 08:15:52', '2021-10-21 08:15:56'),
(3, 'Admwork_controller', '[\"add\",\"edit\",\"list\",\"view\",\"delete\",\"register\",\"register_one\",\"Jsondata\"]', '2021-10-21 08:16:19', '2021-12-07 03:11:53'),
(4, 'Units_controller', '[\"add\",\"edit\",\"view\",\"list\",\"delete\"]', '2021-10-21 08:16:54', '2021-10-21 08:16:58'),
(5, 'Orgchart_controller', '[\"add\",\"edit\",\"view\",\"list\",\"delete\",\"orga\",\"JsonData\"]', '2021-10-21 08:17:18', '2021-11-24 08:48:29'),
(6, 'Options_controller', '[\"list\",\"add\",\"edit\",\"view\",\"delete\"]', '2021-11-08 09:14:54', '2021-11-08 09:15:00'),
(7, 'Acl_users_controller', '[\"add\",\"edit\",\"delete\",\"list\",\"view\"]', '2021-11-09 03:16:30', '2021-11-09 04:19:51'),
(8, 'Acl_roles_controller', '[\"add\",\"edit\",\"list\",\"view\",\"delete\",\"set_rules\"]', '2021-11-09 03:16:41', '2021-11-09 04:22:24'),
(11, 'Acl_controllers_controller', '[\"add\",\"edit\",\"delete\",\"list\",\"view\"]', '2021-11-09 03:17:09', '2021-11-09 04:20:54'),
(12, 'Parameters', '[\"list\"]', '2021-11-09 04:21:21', '2021-11-09 04:21:26'),
(13, 'Home', '[\"myaccount\"]', '2022-06-20 11:04:38', '2022-06-20 11:04:48');

-- --------------------------------------------------------

--
-- Structure de la table `acl_roles`
--

CREATE TABLE `acl_roles` (
  `id` int(11) NOT NULL,
  `role_name` varchar(255) NOT NULL,
  `role_description` varchar(255) NOT NULL,
  `created` datetime NOT NULL,
  `updated` datetime NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

--
-- Déchargement des données de la table `acl_roles`
--

INSERT INTO `acl_roles` (`id`, `role_name`, `role_description`, `created`, `updated`) VALUES
(1, 'Admin', 'Rôle admin', '2021-08-19 01:05:00', '0000-00-00 00:00:00'),
(2, 'Famille', 'Le rôle pour la famille qui se connecte ', '2021-11-09 03:09:04', '0000-00-00 00:00:00');

-- --------------------------------------------------------

--
-- Structure de la table `acl_roles_controllers`
--

CREATE TABLE `acl_roles_controllers` (
  `id` int(11) NOT NULL,
  `id_role` int(11) NOT NULL,
  `id_ctrl` int(11) NOT NULL,
  `id_act` int(11) NOT NULL,
  `allow` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

--
-- Déchargement des données de la table `acl_roles_controllers`
--

INSERT INTO `acl_roles_controllers` (`id`, `id_role`, `id_ctrl`, `id_act`, `allow`) VALUES
(767, 2, 5, 34, 1),
(768, 2, 5, 69, 1),
(769, 2, 13, 72, 1),
(770, 2, 1, 31, 1),
(771, 2, 3, 36, 1),
(772, 2, 3, 35, 1),
(773, 1, 4, 23, 1),
(774, 1, 4, 24, 1),
(775, 1, 4, 22, 1),
(776, 1, 4, 25, 1),
(777, 1, 4, 21, 1),
(778, 1, 12, 67, 1),
(779, 1, 5, 28, 1),
(780, 1, 5, 34, 1),
(781, 1, 5, 29, 1),
(782, 1, 5, 69, 1),
(783, 1, 5, 27, 1),
(784, 1, 5, 30, 1),
(785, 1, 5, 26, 1),
(786, 1, 6, 40, 1),
(787, 1, 6, 37, 1),
(788, 1, 6, 39, 1),
(789, 1, 6, 41, 1),
(790, 1, 6, 38, 1),
(791, 1, 13, 72, 1),
(792, 1, 2, 14, 1),
(793, 1, 2, 13, 1),
(794, 1, 2, 12, 1),
(795, 1, 2, 15, 1),
(796, 1, 2, 11, 1),
(797, 1, 1, 8, 1),
(798, 1, 1, 33, 1),
(799, 1, 1, 73, 1),
(800, 1, 1, 9, 1),
(801, 1, 1, 71, 1),
(802, 1, 1, 31, 1),
(803, 1, 1, 7, 1),
(804, 1, 1, 10, 1),
(805, 1, 1, 32, 1),
(806, 1, 1, 6, 1),
(807, 1, 3, 19, 1),
(808, 1, 3, 36, 1),
(809, 1, 3, 35, 1),
(810, 1, 3, 18, 1),
(811, 1, 3, 70, 1),
(812, 1, 3, 17, 1),
(813, 1, 3, 20, 1),
(814, 1, 3, 16, 1),
(815, 1, 7, 46, 1),
(816, 1, 7, 45, 1),
(817, 1, 7, 43, 1),
(818, 1, 7, 44, 1),
(819, 1, 7, 42, 1),
(820, 1, 8, 50, 1),
(821, 1, 8, 68, 1),
(822, 1, 8, 49, 1),
(823, 1, 8, 48, 1),
(824, 1, 8, 51, 1),
(825, 1, 8, 47, 1),
(826, 1, 11, 66, 1),
(827, 1, 11, 65, 1),
(828, 1, 11, 63, 1),
(829, 1, 11, 64, 1),
(830, 1, 11, 62, 1);

-- --------------------------------------------------------

--
-- Structure de la table `acl_users`
--

CREATE TABLE `acl_users` (
  `id` int(11) NOT NULL,
  `name` varchar(255) NOT NULL,
  `login` varchar(255) NOT NULL,
  `password` varchar(255) NOT NULL,
  `role_id` int(11) NOT NULL,
  `created` datetime NOT NULL,
  `updated` datetime NOT NULL,
  `recaptchaResponse` varchar(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

--
-- Déchargement des données de la table `acl_users`
--

INSERT INTO `acl_users` (`id`, `name`, `login`, `password`, `role_id`, `created`, `updated`, `recaptchaResponse`) VALUES
(1, 'Administrateur Site UA', 'admin', 'siqSoGvcjjx/o', 1, '2021-08-19 01:28:23', '2022-01-08 10:04:29', ''),
(2, 'Root', 'root', 'simLJmBSjY1tQ', 1, '2022-01-08 10:04:37', '0000-00-00 00:00:00', '');

--
-- Index pour les tables déchargées
--

--
-- Index pour la table `acl_actions`
--
ALTER TABLE `acl_actions`
  ADD PRIMARY KEY (`id`);

--
-- Index pour la table `acl_controllers`
--
ALTER TABLE `acl_controllers`
  ADD PRIMARY KEY (`id`);

--
-- Index pour la table `acl_roles`
--
ALTER TABLE `acl_roles`
  ADD PRIMARY KEY (`id`);

--
-- Index pour la table `acl_roles_controllers`
--
ALTER TABLE `acl_roles_controllers`
  ADD PRIMARY KEY (`id`);

--
-- Index pour la table `acl_users`
--
ALTER TABLE `acl_users`
  ADD PRIMARY KEY (`id`);

--
-- AUTO_INCREMENT pour les tables déchargées
--

--
-- AUTO_INCREMENT pour la table `acl_actions`
--
ALTER TABLE `acl_actions`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=74;

--
-- AUTO_INCREMENT pour la table `acl_controllers`
--
ALTER TABLE `acl_controllers`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=14;

--
-- AUTO_INCREMENT pour la table `acl_roles`
--
ALTER TABLE `acl_roles`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT pour la table `acl_roles_controllers`
--
ALTER TABLE `acl_roles_controllers`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=831;

--
-- AUTO_INCREMENT pour la table `acl_users`
--
ALTER TABLE `acl_users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
