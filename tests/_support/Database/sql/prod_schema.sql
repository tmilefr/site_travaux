-- Schéma de la base de production (CodeIgniter 3, MariaDB 10.11) SANS données personnelles.
-- Seules les tables de référence (acl_*, options) contiennent des lignes.

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET time_zone = "+00:00";

/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

CREATE TABLE `acl_actions` (
  `id` int(11) NOT NULL,
  `id_ctrl` int(10) NOT NULL,
  `action` varchar(255) NOT NULL,
  `created` datetime NOT NULL,
  `updated` datetime NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

INSERT INTO `acl_actions` (`id`, `id_ctrl`, `action`, `created`, `updated`) VALUES
(1, 0, 'list', '2021-10-21 08:14:51', '0000-00-00 00:00:00'),
(2, 0, 'edit', '2021-10-21 08:14:51', '0000-00-00 00:00:00'),
(3, 0, 'add', '2021-10-21 08:14:51', '0000-00-00 00:00:00'),
(4, 0, 'view', '2021-10-21 08:14:51', '0000-00-00 00:00:00'),
(5, 0, 'delete', '2021-10-21 08:14:51', '0000-00-00 00:00:00'),
(6, 1, 'add', '2021-10-21 08:15:25', '2025-11-24 11:15:55'),
(7, 1, 'edit', '2021-10-21 08:15:25', '2025-11-24 11:15:55'),
(8, 1, 'view', '2021-10-21 08:15:25', '2025-11-24 11:15:55'),
(9, 1, 'list', '2021-10-21 08:15:25', '2025-11-24 11:15:55'),
(10, 1, 'delete', '2021-10-21 08:15:25', '2025-11-24 11:15:55'),
(11, 2, 'add', '2021-10-21 08:15:56', '0000-00-00 00:00:00'),
(12, 2, 'edit', '2021-10-21 08:15:56', '0000-00-00 00:00:00'),
(13, 2, 'list', '2021-10-21 08:15:56', '0000-00-00 00:00:00'),
(14, 2, 'view', '2021-10-21 08:15:56', '0000-00-00 00:00:00'),
(15, 2, 'delete', '2021-10-21 08:15:56', '0000-00-00 00:00:00'),
(16, 3, 'add', '2021-10-21 08:16:23', '2025-04-02 08:20:40'),
(17, 3, 'edit', '2021-10-21 08:16:23', '2025-04-02 08:20:40'),
(18, 3, 'list', '2021-10-21 08:16:23', '2025-04-02 08:20:40'),
(19, 3, 'view', '2021-10-21 08:16:23', '2025-04-02 08:20:40'),
(20, 3, 'delete', '2021-10-21 08:16:23', '2025-04-02 08:20:40'),
(21, 4, 'add', '2021-10-21 08:16:58', '2022-12-10 07:48:17'),
(22, 4, 'edit', '2021-10-21 08:16:58', '2022-12-10 07:48:17'),
(23, 4, 'view', '2021-10-21 08:16:58', '2022-12-10 07:48:17'),
(24, 4, 'list', '2021-10-21 08:16:58', '2022-12-10 07:48:17'),
(25, 4, 'delete', '2021-10-21 08:16:58', '2022-12-10 07:48:17'),
(26, 5, 'add', '2021-10-21 08:17:23', '2024-05-10 12:33:26'),
(27, 5, 'edit', '2021-10-21 08:17:23', '2024-05-10 12:33:26'),
(28, 5, 'view', '2021-10-21 08:17:23', '2024-05-10 12:33:26'),
(29, 5, 'list', '2021-10-21 08:17:23', '2024-05-10 12:33:26'),
(30, 5, 'delete', '2021-10-21 08:17:23', '2024-05-10 12:33:26'),
(31, 1, 'histo', '0000-00-00 00:00:00', '2025-11-24 11:15:55'),
(34, 5, 'orga', '0000-00-00 00:00:00', '2024-05-10 12:33:26'),
(35, 3, 'register', '0000-00-00 00:00:00', '2025-04-02 08:20:40'),
(36, 3, 'register_one', '0000-00-00 00:00:00', '2025-04-02 08:20:40'),
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
(69, 5, 'JsonData', '0000-00-00 00:00:00', '2024-05-10 12:33:26'),
(70, 3, 'Jsondata', '0000-00-00 00:00:00', '2025-04-02 08:20:40'),
(72, 13, 'myaccount', '2022-06-20 11:04:48', '2023-10-09 09:14:09'),
(73, 1, 'MaJCompetence', '0000-00-00 00:00:00', '2025-11-24 11:15:55'),
(74, 1, 'skills', '0000-00-00 00:00:00', '2025-11-24 11:15:55'),
(75, 3, 'MakePdf', '0000-00-00 00:00:00', '2025-04-02 08:20:40'),
(76, 4, 'valid', '0000-00-00 00:00:00', '2022-12-10 07:48:17'),
(77, 1, 'JsonData', '0000-00-00 00:00:00', '2025-11-24 11:15:55'),
(78, 4, 'Jsondata', '0000-00-00 00:00:00', '2022-12-10 07:48:17'),
(79, 4, 'valids', '0000-00-00 00:00:00', '2022-12-10 07:48:17'),
(80, 1, 'stats', '0000-00-00 00:00:00', '2025-11-24 11:15:55'),
(81, 1, 'stats_export', '0000-00-00 00:00:00', '2025-11-24 11:15:55'),
(82, 14, 'edit', '2023-10-09 08:44:37', '0000-00-00 00:00:00'),
(83, 14, 'list', '2023-10-09 08:44:37', '0000-00-00 00:00:00'),
(84, 14, 'view', '2023-10-09 08:44:37', '0000-00-00 00:00:00'),
(85, 14, 'add', '2023-10-09 08:44:37', '0000-00-00 00:00:00'),
(86, 14, 'delete', '2023-10-09 08:44:37', '0000-00-00 00:00:00'),
(87, 15, 'Templates', '2023-10-09 08:45:06', '0000-00-00 00:00:00'),
(88, 15, 'mails', '2023-10-09 08:45:06', '0000-00-00 00:00:00'),
(89, 15, 'login', '2023-10-09 08:45:06', '0000-00-00 00:00:00'),
(90, 15, 'logout', '2023-10-09 08:45:06', '0000-00-00 00:00:00'),
(91, 16, 'add', '2023-10-09 08:53:42', '0000-00-00 00:00:00'),
(92, 16, 'edit', '2023-10-09 08:53:42', '0000-00-00 00:00:00'),
(93, 16, 'list', '2023-10-09 08:53:42', '0000-00-00 00:00:00'),
(94, 16, 'view', '2023-10-09 08:53:42', '0000-00-00 00:00:00'),
(95, 16, 'delete', '2023-10-09 08:53:42', '0000-00-00 00:00:00'),
(96, 13, 'index', '0000-00-00 00:00:00', '2023-10-09 09:14:09'),
(97, 5, 'view_one', '0000-00-00 00:00:00', '2024-05-10 12:33:26'),
(98, 17, 'add', '2024-04-22 03:35:34', '2024-04-24 03:19:22'),
(99, 17, 'edit', '0000-00-00 00:00:00', '2024-04-24 03:19:22'),
(100, 17, 'delete', '0000-00-00 00:00:00', '2024-04-24 03:19:22'),
(101, 17, 'list', '0000-00-00 00:00:00', '2024-04-24 03:19:22'),
(102, 17, 'JsonData', '0000-00-00 00:00:00', '2024-04-24 03:19:22'),
(103, 18, 'list', '2024-04-24 03:18:45', '0000-00-00 00:00:00'),
(104, 18, 'add', '2024-04-24 03:18:45', '0000-00-00 00:00:00'),
(105, 18, 'edit', '2024-04-24 03:18:45', '0000-00-00 00:00:00'),
(106, 18, 'delete', '2024-04-24 03:18:45', '0000-00-00 00:00:00'),
(107, 18, 'JsonData', '2024-04-24 03:18:45', '0000-00-00 00:00:00'),
(108, 3, 'managed_one', '0000-00-00 00:00:00', '2025-04-02 08:20:40'),
(109, 5, 'organisation', '0000-00-00 00:00:00', '2024-05-10 12:33:26'),
(110, 5, 'featured', '0000-00-00 00:00:00', '2024-05-10 12:33:26'),
(111, 19, 'add', '2024-05-10 12:33:44', '0000-00-00 00:00:00'),
(112, 19, 'list', '2024-05-10 12:33:44', '0000-00-00 00:00:00'),
(113, 19, 'edit', '2024-05-10 12:33:44', '0000-00-00 00:00:00'),
(114, 19, 'delete', '2024-05-10 12:33:44', '0000-00-00 00:00:00'),
(115, 19, 'JsonData', '2024-05-10 12:33:44', '0000-00-00 00:00:00'),
(116, 19, 'view', '2024-05-10 12:33:44', '0000-00-00 00:00:00'),
(117, 20, 'add', '2024-05-10 12:34:12', '0000-00-00 00:00:00'),
(118, 20, 'edit', '2024-05-10 12:34:12', '0000-00-00 00:00:00'),
(119, 20, 'list', '2024-05-10 12:34:12', '0000-00-00 00:00:00'),
(120, 20, 'delete', '2024-05-10 12:34:12', '0000-00-00 00:00:00'),
(121, 20, 'view', '2024-05-10 12:34:12', '0000-00-00 00:00:00'),
(122, 20, 'JsonData', '2024-05-10 12:34:12', '0000-00-00 00:00:00'),
(123, 3, 'admin', '0000-00-00 00:00:00', '2025-04-02 08:20:40'),
(124, 3, 'draftvalidation', '0000-00-00 00:00:00', '2025-04-02 08:20:40'),
(125, 1, 'MassUpdate', '0000-00-00 00:00:00', '2025-11-24 11:15:55');

CREATE TABLE `acl_controllers` (
  `id` int(11) NOT NULL,
  `controller` varchar(255) NOT NULL,
  `actions` varchar(255) NOT NULL,
  `created` datetime NOT NULL,
  `updated` datetime NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

INSERT INTO `acl_controllers` (`id`, `controller`, `actions`, `created`, `updated`) VALUES
(1, 'Familys_controller', '[\"add\",\"stats\",\"JsonData\",\"skills\",\"MaJCompetence\",\"histo\",\"delete\",\"list\",\"view\",\"edit\",\"stats_export\",\"MassUpdate\"]', '2021-10-21 08:14:51', '2025-11-24 11:15:55'),
(2, 'Histo_controller', '[\"add\",\"edit\",\"list\",\"view\",\"delete\"]', '2021-10-21 08:15:52', '2021-10-21 08:15:56'),
(3, 'Admwork_controller', '[\"add\",\"managed_one\",\"MakePdf\",\"Jsondata\",\"register_one\",\"register\",\"delete\",\"view\",\"list\",\"edit\",\"admin\",\"draftvalidation\"]', '2021-10-21 08:16:19', '2025-04-02 08:20:40'),
(4, 'Units_controller', '[\"add\",\"edit\",\"view\",\"list\",\"delete\",\"valid\",\"Jsondata\",\"valids\"]', '2021-10-21 08:16:54', '2022-12-10 07:48:17'),
(5, 'Orgchart_controller', '[\"add\",\"edit\",\"view\",\"list\",\"delete\",\"orga\",\"JsonData\",\"view_one\",\"organisation\",\"featured\"]', '2021-10-21 08:17:18', '2024-05-10 12:33:26'),
(6, 'Options_controller', '[\"list\",\"add\",\"edit\",\"view\",\"delete\"]', '2021-11-08 09:14:54', '2021-11-08 09:15:00'),
(7, 'Acl_users_controller', '[\"add\",\"edit\",\"delete\",\"list\",\"view\"]', '2021-11-09 03:16:30', '2021-11-09 04:19:51'),
(8, 'Acl_roles_controller', '[\"add\",\"edit\",\"list\",\"view\",\"delete\",\"set_rules\"]', '2021-11-09 03:16:41', '2021-11-09 04:22:24'),
(11, 'Acl_controllers_controller', '[\"add\",\"edit\",\"delete\",\"list\",\"view\"]', '2021-11-09 03:17:09', '2021-11-09 04:20:54'),
(12, 'Parameters', '[\"list\"]', '2021-11-09 04:21:21', '2021-11-09 04:21:26'),
(13, 'Home', '[\"myaccount\",\"index\"]', '2022-06-20 11:04:38', '2023-10-09 09:14:09'),
(14, 'Sendmail_controller', '[\"edit\",\"list\",\"view\",\"add\",\"delete\"]', '2023-10-09 08:44:37', '0000-00-00 00:00:00'),
(15, 'api', '[\"Templates\",\"mails\",\"login\",\"logout\"]', '2023-10-09 08:45:06', '0000-00-00 00:00:00'),
(16, 'Templates_controller', '[\"add\",\"edit\",\"list\",\"view\",\"delete\"]', '2023-10-09 08:53:42', '0000-00-00 00:00:00'),
(17, 'GroupesMembers_controller', '[\"add\",\"edit\",\"delete\",\"list\",\"JsonData\"]', '2024-04-22 03:35:34', '2024-04-24 03:19:22'),
(18, 'Candidatures_controller', '[\"list\",\"add\",\"edit\",\"delete\",\"JsonData\"]', '2024-04-24 03:18:35', '2024-04-24 03:18:45'),
(19, 'Files_controller', '[\"add\",\"list\",\"edit\",\"delete\",\"JsonData\",\"view\"]', '2024-05-10 12:33:44', '0000-00-00 00:00:00'),
(20, 'Event_controller', '[\"add\",\"edit\",\"list\",\"delete\",\"view\",\"JsonData\"]', '2024-05-10 12:34:12', '0000-00-00 00:00:00');

CREATE TABLE `acl_roles` (
  `id` int(11) NOT NULL,
  `role_name` varchar(255) NOT NULL,
  `role_description` varchar(255) NOT NULL,
  `created` datetime NOT NULL,
  `updated` datetime NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

INSERT INTO `acl_roles` (`id`, `role_name`, `role_description`, `created`, `updated`) VALUES
(1, 'Admin', 'Rôle admin', '2021-08-19 01:05:00', '0000-00-00 00:00:00'),
(2, 'Famille', 'Le rôle pour la famille qui se connecte ', '2021-11-09 03:09:04', '0000-00-00 00:00:00'),
(3, 'Gestionnaire', 'Gestionnaire de commissions', '2024-05-20 09:33:49', '0000-00-00 00:00:00');

CREATE TABLE `acl_roles_controllers` (
  `id` int(11) NOT NULL,
  `id_role` int(11) NOT NULL,
  `id_ctrl` int(11) NOT NULL,
  `id_act` int(11) NOT NULL,
  `allow` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

INSERT INTO `acl_roles_controllers` (`id`, `id_role`, `id_ctrl`, `id_act`, `allow`) VALUES
(2765, 3, 4, 23, 1),
(2766, 3, 4, 79, 1),
(2767, 3, 4, 76, 1),
(2768, 3, 4, 24, 1),
(2769, 3, 4, 78, 1),
(2770, 3, 4, 22, 1),
(2771, 3, 4, 21, 1),
(2772, 3, 16, 94, 1),
(2773, 3, 16, 93, 1),
(2774, 3, 16, 92, 1),
(2775, 3, 16, 91, 1),
(2776, 3, 14, 84, 1),
(2777, 3, 14, 82, 1),
(2778, 3, 14, 85, 1),
(2779, 3, 5, 97, 1),
(2780, 3, 5, 28, 1),
(2781, 3, 5, 109, 1),
(2782, 3, 5, 34, 1),
(2783, 3, 5, 29, 1),
(2784, 3, 5, 69, 1),
(2785, 3, 5, 110, 1),
(2786, 3, 5, 27, 1),
(2787, 3, 5, 30, 1),
(2788, 3, 5, 26, 1),
(2789, 3, 6, 40, 1),
(2790, 3, 6, 37, 1),
(2791, 3, 6, 39, 1),
(2792, 3, 6, 38, 1),
(2793, 3, 13, 72, 1),
(2794, 3, 13, 96, 1),
(2795, 3, 2, 14, 1),
(2796, 3, 2, 13, 1),
(2797, 3, 2, 12, 1),
(2798, 3, 2, 11, 1),
(2799, 3, 17, 101, 1),
(2800, 3, 17, 99, 1),
(2801, 3, 17, 98, 1),
(2802, 3, 19, 116, 1),
(2803, 3, 19, 112, 1),
(2804, 3, 19, 115, 1),
(2805, 3, 19, 113, 1),
(2806, 3, 19, 111, 1),
(2807, 3, 1, 8, 1),
(2808, 3, 1, 31, 1),
(2809, 3, 20, 121, 1),
(2810, 3, 20, 119, 1),
(2811, 3, 20, 122, 1),
(2812, 3, 20, 118, 1),
(2813, 3, 20, 117, 1),
(2814, 3, 18, 103, 1),
(2815, 3, 18, 107, 1),
(2816, 3, 15, 87, 1),
(2817, 3, 15, 88, 1),
(2818, 3, 15, 90, 1),
(2819, 3, 15, 89, 1),
(2820, 3, 3, 19, 1),
(2821, 3, 3, 36, 1),
(2822, 3, 3, 35, 1),
(2823, 3, 3, 108, 1),
(2824, 3, 3, 75, 1),
(2825, 3, 3, 18, 1),
(2826, 3, 3, 70, 1),
(2827, 3, 3, 17, 1),
(2828, 3, 3, 123, 1),
(2829, 3, 3, 16, 1),
(2830, 4, 4, 23, 1),
(2831, 4, 4, 79, 1),
(2832, 4, 4, 76, 1),
(2833, 4, 4, 24, 1),
(2834, 4, 4, 78, 1),
(2835, 4, 4, 22, 1),
(2836, 4, 4, 21, 1),
(2837, 4, 16, 94, 1),
(2838, 4, 16, 93, 1),
(2839, 4, 16, 92, 1),
(2840, 4, 16, 91, 1),
(2841, 4, 12, 67, 1),
(2842, 4, 5, 97, 1),
(2843, 4, 5, 28, 1),
(2844, 4, 5, 109, 1),
(2845, 4, 5, 34, 1),
(2846, 4, 5, 69, 1),
(2847, 4, 5, 110, 1),
(2848, 4, 6, 40, 1),
(2849, 4, 6, 37, 1),
(2850, 4, 6, 38, 1),
(2851, 4, 13, 72, 1),
(2852, 4, 13, 96, 1),
(2853, 4, 2, 14, 1),
(2854, 4, 2, 13, 1),
(2855, 4, 17, 102, 1),
(2856, 4, 1, 8, 1),
(2857, 4, 1, 81, 1),
(2858, 4, 1, 9, 1),
(2859, 4, 1, 77, 1),
(2860, 4, 1, 31, 1),
(2861, 4, 1, 7, 1),
(2862, 4, 20, 122, 1),
(2863, 4, 18, 103, 1),
(2864, 4, 18, 107, 1),
(2865, 4, 15, 87, 1),
(2866, 4, 15, 88, 1),
(2867, 4, 15, 90, 1),
(2868, 4, 15, 89, 1),
(2869, 4, 3, 19, 1),
(2870, 4, 3, 36, 1),
(2871, 4, 3, 35, 1),
(2872, 4, 3, 108, 1),
(2873, 4, 3, 75, 1),
(2874, 4, 3, 18, 1),
(2875, 4, 3, 70, 1),
(2876, 4, 3, 17, 1),
(2877, 4, 3, 123, 1),
(2878, 4, 3, 16, 1),
(2879, 2, 5, 97, 1),
(2880, 2, 5, 109, 1),
(2881, 2, 5, 34, 1),
(2882, 2, 5, 69, 1),
(2883, 2, 13, 72, 1),
(2884, 2, 13, 96, 1),
(2885, 2, 1, 74, 1),
(2886, 2, 1, 73, 1),
(2887, 2, 1, 77, 1),
(2888, 2, 1, 31, 1),
(2889, 2, 1, 7, 1),
(2890, 2, 3, 36, 1),
(2891, 2, 3, 35, 1),
(3103, 1, 4, 23, 1),
(3104, 1, 4, 79, 1),
(3105, 1, 4, 76, 1),
(3106, 1, 4, 24, 1),
(3107, 1, 4, 78, 1),
(3108, 1, 4, 22, 1),
(3109, 1, 4, 25, 1),
(3110, 1, 4, 21, 1),
(3111, 1, 16, 94, 1),
(3112, 1, 16, 93, 1),
(3113, 1, 16, 92, 1),
(3114, 1, 16, 95, 1),
(3115, 1, 16, 91, 1),
(3116, 1, 14, 84, 1),
(3117, 1, 14, 83, 1),
(3118, 1, 14, 82, 1),
(3119, 1, 14, 86, 1),
(3120, 1, 14, 85, 1),
(3121, 1, 12, 67, 1),
(3122, 1, 5, 97, 1),
(3123, 1, 5, 28, 1),
(3124, 1, 5, 109, 1),
(3125, 1, 5, 34, 1),
(3126, 1, 5, 29, 1),
(3127, 1, 5, 69, 1),
(3128, 1, 5, 110, 1),
(3129, 1, 5, 27, 1),
(3130, 1, 5, 30, 1),
(3131, 1, 5, 26, 1),
(3132, 1, 6, 40, 1),
(3133, 1, 6, 37, 1),
(3134, 1, 6, 39, 1),
(3135, 1, 6, 41, 1),
(3136, 1, 6, 38, 1),
(3137, 1, 13, 72, 1),
(3138, 1, 13, 96, 1),
(3139, 1, 2, 14, 1),
(3140, 1, 2, 13, 1),
(3141, 1, 2, 12, 1),
(3142, 1, 2, 15, 1),
(3143, 1, 2, 11, 1),
(3144, 1, 17, 101, 1),
(3145, 1, 17, 102, 1),
(3146, 1, 17, 99, 1),
(3147, 1, 17, 100, 1),
(3148, 1, 17, 98, 1),
(3149, 1, 19, 116, 1),
(3150, 1, 19, 112, 1),
(3151, 1, 19, 115, 1),
(3152, 1, 19, 113, 1),
(3153, 1, 19, 114, 1),
(3154, 1, 19, 111, 1),
(3155, 1, 1, 8, 1),
(3156, 1, 1, 81, 1),
(3157, 1, 1, 80, 1),
(3158, 1, 1, 74, 1),
(3159, 1, 1, 125, 1),
(3160, 1, 1, 73, 1),
(3161, 1, 1, 9, 1),
(3162, 1, 1, 77, 1),
(3163, 1, 1, 31, 1),
(3164, 1, 1, 7, 1),
(3165, 1, 1, 10, 1),
(3166, 1, 1, 6, 1),
(3167, 1, 20, 121, 1),
(3168, 1, 20, 119, 1),
(3169, 1, 20, 122, 1),
(3170, 1, 20, 118, 1),
(3171, 1, 20, 120, 1),
(3172, 1, 20, 117, 1),
(3173, 1, 18, 103, 1),
(3174, 1, 18, 107, 1),
(3175, 1, 18, 105, 1),
(3176, 1, 18, 106, 1),
(3177, 1, 18, 104, 1),
(3178, 1, 15, 87, 1),
(3179, 1, 15, 88, 1),
(3180, 1, 15, 90, 1),
(3181, 1, 15, 89, 1),
(3182, 1, 3, 19, 1),
(3183, 1, 3, 36, 1),
(3184, 1, 3, 35, 1),
(3185, 1, 3, 108, 1),
(3186, 1, 3, 75, 1),
(3187, 1, 3, 18, 1),
(3188, 1, 3, 70, 1),
(3189, 1, 3, 17, 1),
(3190, 1, 3, 124, 1),
(3191, 1, 3, 20, 1),
(3192, 1, 3, 123, 1),
(3193, 1, 3, 16, 1),
(3194, 1, 7, 46, 1),
(3195, 1, 7, 45, 1),
(3196, 1, 7, 43, 1),
(3197, 1, 7, 44, 1),
(3198, 1, 7, 42, 1),
(3199, 1, 8, 50, 1),
(3200, 1, 8, 68, 1),
(3201, 1, 8, 49, 1),
(3202, 1, 8, 48, 1),
(3203, 1, 8, 51, 1),
(3204, 1, 8, 47, 1),
(3205, 1, 11, 66, 1),
(3206, 1, 11, 65, 1),
(3207, 1, 11, 63, 1),
(3208, 1, 11, 64, 1),
(3209, 1, 11, 62, 1);

CREATE TABLE `acl_users` (
  `id` int(11) NOT NULL,
  `name` varchar(255) NOT NULL,
  `login` varchar(255) NOT NULL,
  `password` varchar(255) NOT NULL,
  `role_id` int(11) NOT NULL,
  `created` datetime NOT NULL,
  `updated` datetime NOT NULL,
  `recaptchaResponse` varchar(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

CREATE TABLE `candidatures` (
  `id` int(11) NOT NULL,
  `id_fam` int(11) NOT NULL,
  `id_grp` int(11) DEFAULT NULL,
  `name` varchar(255) NOT NULL,
  `surname` varchar(255) NOT NULL,
  `email` varchar(255) DEFAULT NULL,
  `phone` varchar(255) DEFAULT NULL,
  `memo` varchar(255) DEFAULT NULL,
  `created` datetime NOT NULL,
  `updated` datetime NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

CREATE TABLE `capacitys` (
  `id` int(11) NOT NULL,
  `id_fam` int(10) DEFAULT NULL,
  `id_cap` varchar(255) NOT NULL,
  `created` datetime NOT NULL,
  `updated` datetime NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

CREATE TABLE `ci_sessions` (
  `id` varchar(128) NOT NULL,
  `ip_address` varchar(45) NOT NULL,
  `timestamp` int(10) UNSIGNED NOT NULL DEFAULT 0,
  `data` blob NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

CREATE TABLE `emails` (
  `id` int(11) NOT NULL,
  `id_fam` int(11) NOT NULL,
  `email` varchar(255) NOT NULL,
  `name` varchar(255) NOT NULL,
  `created` datetime NOT NULL,
  `updated` datetime NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

CREATE TABLE `events` (
  `id` int(11) NOT NULL,
  `title` varchar(255) DEFAULT NULL,
  `type` varchar(255) DEFAULT NULL,
  `memo` varchar(255) DEFAULT NULL,
  `hit` varchar(255) DEFAULT NULL,
  `statut` varchar(255) DEFAULT NULL,
  `color` varchar(255) NOT NULL,
  `date` date DEFAULT NULL,
  `time` varchar(255) NOT NULL,
  `created` date NOT NULL,
  `updated` date NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

CREATE TABLE `famille` (
  `id` int(11) NOT NULL,
  `idfamille` int(11) NOT NULL,
  `role_id` int(11) DEFAULT NULL,
  `login` varchar(50) NOT NULL DEFAULT '',
  `nom` varchar(255) DEFAULT NULL,
  `prenom` varchar(255) DEFAULT NULL,
  `password` varchar(50) NOT NULL DEFAULT '',
  `adresse` varchar(100) NOT NULL DEFAULT '',
  `cp` varchar(10) NOT NULL DEFAULT '',
  `ville` varchar(80) NOT NULL DEFAULT '',
  `tel` varchar(20) NOT NULL DEFAULT '',
  `e_mail` varchar(100) NOT NULL,
  `e_mail_comp` varchar(255) DEFAULT NULL,
  `members` varchar(255) DEFAULT NULL,
  `nb_enfants` int(11) DEFAULT 0,
  `nb_unites_restantes` float DEFAULT 0,
  `id_profil` int(11) NOT NULL DEFAULT 0,
  `ecole` char(1) NOT NULL DEFAULT 'M',
  `capacity` varchar(255) DEFAULT NULL,
  `civil_year` varchar(255) NOT NULL,
  `created` datetime DEFAULT NULL,
  `updated` datetime DEFAULT NULL
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_general_ci;

CREATE TABLE `files` (
  `id` int(11) NOT NULL,
  `name` varchar(255) DEFAULT NULL,
  `type` varchar(255) DEFAULT NULL,
  `memo` varchar(255) NOT NULL,
  `path` varchar(255) NOT NULL,
  `statut` varchar(255) DEFAULT NULL,
  `created` date NOT NULL,
  `updated` date NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

CREATE TABLE `groupes` (
  `id` int(11) NOT NULL,
  `title` varchar(255) DEFAULT NULL,
  `short` varchar(255) DEFAULT NULL,
  `intro` text NOT NULL,
  `color` varchar(255) DEFAULT NULL,
  `type` varchar(255) DEFAULT NULL,
  `acteurs` varchar(255) NOT NULL,
  `member` varchar(255) NOT NULL,
  `mission` text NOT NULL,
  `actions` longtext DEFAULT NULL,
  `role` longtext DEFAULT NULL,
  `needs` longtext DEFAULT NULL,
  `search` longtext DEFAULT NULL,
  `grp` varchar(255) DEFAULT NULL,
  `related` varchar(255) DEFAULT NULL,
  `listorder` int(10) DEFAULT NULL,
  `hit` int(11) DEFAULT NULL,
  `created` datetime NOT NULL,
  `updated` datetime NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

CREATE TABLE `groupes_member` (
  `id` int(11) NOT NULL,
  `id_fam` int(11) NOT NULL,
  `name` varchar(255) NOT NULL,
  `surname` varchar(255) NOT NULL,
  `email` varchar(255) DEFAULT NULL,
  `phone` varchar(255) DEFAULT NULL,
  `picture` varchar(255) DEFAULT NULL,
  `thumbnail` varchar(255) DEFAULT NULL,
  `created` datetime NOT NULL,
  `updated` datetime NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

CREATE TABLE `grp_related` (
  `id` int(11) NOT NULL,
  `id_grp` int(11) DEFAULT NULL,
  `ref` varchar(50) CHARACTER SET latin1 COLLATE latin1_swedish_ci NOT NULL,
  `created` datetime DEFAULT NULL,
  `updated` datetime DEFAULT NULL
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_general_ci;

CREATE TABLE `infos` (
  `id` int(11) NOT NULL,
  `id_famille` int(11) DEFAULT 0,
  `id_travaux` int(11) DEFAULT 0,
  `heure_debut_prevue` time DEFAULT '00:00:00',
  `heure_fin_prevue` time DEFAULT '00:00:00',
  `heure_debut_effective` time DEFAULT '00:00:00',
  `heure_fin_effective` time DEFAULT '00:00:00',
  `remarque` blob DEFAULT NULL,
  `nb_unites_valides` float NOT NULL DEFAULT 0,
  `nb_participants` int(11) NOT NULL DEFAULT 0,
  `type_participant` varchar(255) NOT NULL,
  `nb_unites_valides_effectif` float DEFAULT 0,
  `type_session` int(10) DEFAULT NULL,
  `created` datetime DEFAULT NULL,
  `updated` datetime DEFAULT NULL
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_general_ci;

CREATE TABLE `liens` (
  `id_liens` int(11) NOT NULL,
  `categorie` varchar(100) DEFAULT NULL,
  `titre` varchar(100) DEFAULT NULL,
  `lien` varchar(255) DEFAULT NULL,
  `descriptif` longtext DEFAULT NULL
) ENGINE=MyISAM DEFAULT CHARSET=latin1 COLLATE=latin1_general_ci;

CREATE TABLE `login` (
  `id` int(11) NOT NULL,
  `login` varchar(30) NOT NULL DEFAULT '',
  `password` varchar(30) NOT NULL DEFAULT ''
) ENGINE=MyISAM DEFAULT CHARSET=latin1 COLLATE=latin1_general_ci;

CREATE TABLE `members` (
  `id` int(11) NOT NULL,
  `id_fam` int(11) NOT NULL,
  `type` varchar(255) NOT NULL,
  `nom` varchar(255) NOT NULL,
  `prenom` varchar(255) NOT NULL,
  `created` datetime NOT NULL,
  `updated` datetime NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

CREATE TABLE `options` (
  `id` int(11) NOT NULL,
  `cle` varchar(255) NOT NULL,
  `value` varchar(255) NOT NULL,
  `filter` varchar(255) NOT NULL,
  `created` date NOT NULL,
  `updated` date NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

INSERT INTO `options` (`id`, `cle`, `value`, `filter`, `created`, `updated`) VALUES
(1, 'PE', 'Président', 'classif', '2021-10-26', '2021-11-22'),
(2, 'RT', 'Responsable de commission', 'classif', '2021-10-26', '2024-04-24'),
(3, 'nicdark_bg_white', 'blanc', 'color', '2021-10-26', '2021-10-26'),
(4, 'nicdark_bg_grey', 'gris', 'color', '2021-10-26', '2021-10-26'),
(5, 'nicdark_bg_grey2', 'gris 2', 'color', '2021-10-26', '0000-00-00'),
(6, 'nicdark_bg_greydark', 'gris foncé', 'color', '2021-10-26', '0000-00-00'),
(7, 'nicdark_bg_greydark2', 'gris foncé 2', 'color', '2021-10-26', '0000-00-00'),
(8, 'nicdark_bg_green', 'vert', 'color', '2021-10-26', '0000-00-00'),
(9, 'nicdark_bg_blue', 'bleu', 'color', '2021-10-26', '0000-00-00'),
(10, 'nicdark_bg_violet', 'violet', 'color', '2021-10-26', '0000-00-00'),
(11, 'nicdark_bg_orange', 'orange', 'color', '2021-10-26', '0000-00-00'),
(12, 'nicdark_bg_red', 'rouge', 'color', '2021-10-26', '0000-00-00'),
(13, 'nicdark_bg_yellow', 'jaune', 'color', '2021-10-26', '0000-00-00'),
(14, 'nicdark_bg_greendark', 'vert foncé', 'color', '2021-10-26', '0000-00-00'),
(15, 'nicdark_bg_bluedark', 'bleu foncé', 'color', '2021-10-26', '0000-00-00'),
(16, 'nicdark_bg_violetdark', 'violet foncé', 'color', '2021-10-26', '0000-00-00'),
(17, 'nicdark_bg_orangedark', 'orange foncé', 'color', '2021-10-26', '0000-00-00'),
(18, 'nicdark_bg_reddark', 'rouge foncé', 'color', '2021-10-26', '0000-00-00'),
(19, 'nicdark_bg_yellowdark', 'jaune foncé', 'color', '2021-10-26', '0000-00-00'),
(20, 'MEN', 'Ménage', 'type', '2021-10-26', '0000-00-00'),
(21, 'TRA', 'Travaux', 'type', '2021-10-26', '0000-00-00'),
(22, 'GOU', 'Goûter', 'type', '2021-10-26', '2021-10-26'),
(23, 'LAV', 'Lavage', 'type', '2021-10-26', '0000-00-00'),
(24, 'DEC', 'Dechetterie', 'type', '2021-10-26', '0000-00-00'),
(25, 'BU', 'Membre Bureau', 'classif', '2021-11-22', '0000-00-00'),
(26, 'ME', 'Membre de commission', 'classif', '2021-11-22', '2021-11-22'),
(27, 'VP', 'Vice-président', 'classif', '2021-11-22', '0000-00-00'),
(28, 'TR', 'Trésorier', 'classif', '2021-11-22', '0000-00-00'),
(29, 'SE', 'Secrétaire', 'classif', '2021-11-22', '0000-00-00'),
(30, 'org', 'Organisation', 'typegroupe', '2021-11-22', '0000-00-00'),
(31, 'com', 'Commission', 'typegroupe', '2021-11-22', '2021-11-22'),
(32, 'pein', 'Peinture', 'capacity', '2022-06-26', '0000-00-00'),
(33, 'mac', 'Maçonnerie', 'capacity', '2022-06-26', '0000-00-00'),
(34, 'elec', 'Electricité', 'capacity', '2022-06-26', '0000-00-00'),
(35, 'chau', 'Chauffage', 'capacity', '2022-06-26', '0000-00-00'),
(36, 'san', 'Sanitaire', 'capacity', '2022-06-26', '0000-00-00'),
(37, 'inf', 'Informatique (gestion)', 'capacity', '2022-06-27', '0000-00-00'),
(38, 'dev', 'Informatique (developpement)', 'capacity', '2022-06-27', '0000-00-00'),
(39, 'can', 'Cantine', 'type', '2022-06-30', '0000-00-00'),
(40, 'INF', 'Informatique', 'type', '2022-07-23', '0000-00-00'),
(41, 'ama', 'Activités manuelle et artistique (bricolage ... )', 'capacity', '2022-09-13', '2022-09-13'),
(42, 'reftra', 'Réfèrent de session', 'classif', '2023-10-09', '0000-00-00'),
(43, 'pvca', 'Document de CA', 'filetype', '2024-05-10', '0000-00-00'),
(44, 'reuca', 'Réunion CA', 'event_type', '2024-05-10', '0000-00-00'),
(45, 'reubur', 'Réunion bureau', 'event_type', '2024-05-10', '0000-00-00'),
(46, 'URG', 'Urgent', 'type', '2024-09-23', '0000-00-00'),
(47, 'ag', 'Assemblée Générale', 'event_type', '2025-05-19', '0000-00-00'),
(48, 'pub', 'public', 'filetype', '2025-05-23', '0000-00-00'),
(49, 'cve', 'Evénementiel', 'type', '2026-03-04', '2026-03-04');

CREATE TABLE `sendmail` (
  `id` int(11) UNSIGNED NOT NULL,
  `reference` varchar(255) DEFAULT NULL,
  `email` varchar(255) DEFAULT NULL,
  `object` varchar(255) DEFAULT NULL,
  `message` longtext DEFAULT NULL,
  `statut` int(10) DEFAULT NULL,
  `detail_statut` int(11) DEFAULT NULL,
  `error` varchar(255) NOT NULL,
  `created` datetime DEFAULT NULL,
  `updated` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

CREATE TABLE `sendmail_statut` (
  `id` int(11) UNSIGNED NOT NULL,
  `id_sen` varchar(255) DEFAULT NULL,
  `date` datetime DEFAULT NULL,
  `sendstatut` varchar(255) DEFAULT NULL,
  `error` varchar(255) DEFAULT NULL,
  `created` datetime DEFAULT NULL,
  `updated` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

CREATE TABLE `templates` (
  `id` int(11) NOT NULL,
  `id_type` varchar(255) NOT NULL,
  `name` varchar(255) NOT NULL,
  `text` longtext NOT NULL,
  `created` datetime NOT NULL,
  `updated` datetime NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

CREATE TABLE `travaux` (
  `id` int(11) NOT NULL,
  `id_personnel` int(11) DEFAULT 0,
  `date_travaux` date DEFAULT '0000-00-00',
  `heure_deb_trav` time NOT NULL DEFAULT '08:30:00',
  `heure_fin_trav` time NOT NULL DEFAULT '13:00:00',
  `type` varchar(255) NOT NULL,
  `nb_units` float NOT NULL,
  `titre` varchar(255) NOT NULL,
  `description` blob DEFAULT NULL,
  `nb_inscrits_max` smallint(6) DEFAULT 0,
  `referent_travaux` varchar(100) CHARACTER SET latin1 COLLATE latin1_swedish_ci DEFAULT '',
  `txtmodel` text DEFAULT NULL,
  `repas` tinyint(4) DEFAULT NULL,
  `ecole` char(1) CHARACTER SET latin1 COLLATE latin1_swedish_ci NOT NULL DEFAULT 'M',
  `accespar` char(1) CHARACTER SET latin1 COLLATE latin1_swedish_ci NOT NULL DEFAULT 'B',
  `archived` int(11) NOT NULL DEFAULT 0,
  `civil_year` varchar(255) DEFAULT NULL,
  `created` datetime DEFAULT NULL,
  `updated` datetime DEFAULT NULL,
  `type_session` int(10) DEFAULT NULL,
  `statut` int(11) DEFAULT NULL,
  `filter` varchar(255) DEFAULT NULL
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_general_ci;

CREATE TABLE `trombi` (
  `id` int(11) NOT NULL,
  `id_grp` int(11) DEFAULT NULL,
  `ref` varchar(50) CHARACTER SET latin1 COLLATE latin1_swedish_ci NOT NULL,
  `order` varchar(50) CHARACTER SET latin1 COLLATE latin1_swedish_ci NOT NULL,
  `classif` varchar(255) NOT NULL,
  `created` datetime DEFAULT NULL,
  `updated` datetime DEFAULT NULL
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_general_ci;

CREATE TABLE `unites` (
  `id` int(3) UNSIGNED NOT NULL,
  `unites_id_famille` int(11) NOT NULL,
  `unites_desc` longtext NOT NULL,
  `unites_referent_travaux` varchar(100) NOT NULL,
  `unites_date` date NOT NULL DEFAULT '0000-00-00',
  `unites_heure_debut` time NOT NULL DEFAULT '00:00:00',
  `unites_heure_fin` time NOT NULL DEFAULT '00:00:00',
  `unites_valides` float NOT NULL DEFAULT 0,
  `unites_comm` varchar(50) DEFAULT NULL,
  `type_session` int(10) DEFAULT NULL,
  `civil_year` varchar(255) DEFAULT NULL,
  `archived` int(11) NOT NULL DEFAULT 0,
  `created` datetime DEFAULT NULL,
  `updated` datetime DEFAULT NULL
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_general_ci;

ALTER TABLE `acl_actions`
  ADD PRIMARY KEY (`id`);

ALTER TABLE `acl_controllers`
  ADD PRIMARY KEY (`id`);

ALTER TABLE `acl_roles`
  ADD PRIMARY KEY (`id`);

ALTER TABLE `acl_roles_controllers`
  ADD PRIMARY KEY (`id`);

ALTER TABLE `acl_users`
  ADD PRIMARY KEY (`id`);

ALTER TABLE `candidatures`
  ADD PRIMARY KEY (`id`);

ALTER TABLE `capacitys`
  ADD PRIMARY KEY (`id`);

ALTER TABLE `ci_sessions`
  ADD KEY `ci_sessions_timestamp` (`timestamp`);

ALTER TABLE `emails`
  ADD PRIMARY KEY (`id`);

ALTER TABLE `events`
  ADD PRIMARY KEY (`id`);

ALTER TABLE `famille`
  ADD PRIMARY KEY (`id`);

ALTER TABLE `files`
  ADD PRIMARY KEY (`id`);

ALTER TABLE `groupes`
  ADD PRIMARY KEY (`id`);

ALTER TABLE `groupes_member`
  ADD PRIMARY KEY (`id`);

ALTER TABLE `grp_related`
  ADD PRIMARY KEY (`id`);

ALTER TABLE `infos`
  ADD PRIMARY KEY (`id`);

ALTER TABLE `liens`
  ADD PRIMARY KEY (`id_liens`);

ALTER TABLE `login`
  ADD PRIMARY KEY (`id`);

ALTER TABLE `members`
  ADD PRIMARY KEY (`id`);

ALTER TABLE `options`
  ADD PRIMARY KEY (`id`);

ALTER TABLE `sendmail`
  ADD PRIMARY KEY (`id`);

ALTER TABLE `sendmail_statut`
  ADD PRIMARY KEY (`id`);

ALTER TABLE `templates`
  ADD PRIMARY KEY (`id`),
  ADD KEY `id_fam` (`id_type`);

ALTER TABLE `travaux`
  ADD PRIMARY KEY (`id`);

ALTER TABLE `trombi`
  ADD PRIMARY KEY (`id`);

ALTER TABLE `unites`
  ADD PRIMARY KEY (`id`);

ALTER TABLE `acl_actions`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=126;

ALTER TABLE `acl_controllers`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=21;

ALTER TABLE `acl_roles`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

ALTER TABLE `acl_roles_controllers`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3210;

ALTER TABLE `acl_users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

ALTER TABLE `candidatures`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=28;

ALTER TABLE `capacitys`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

ALTER TABLE `emails`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

ALTER TABLE `events`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=23;

ALTER TABLE `famille`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

ALTER TABLE `files`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

ALTER TABLE `groupes`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=28;

ALTER TABLE `groupes_member`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=20;

ALTER TABLE `grp_related`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

ALTER TABLE `infos`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8483;

ALTER TABLE `liens`
  MODIFY `id_liens` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=39;

ALTER TABLE `login`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

ALTER TABLE `members`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

ALTER TABLE `options`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=50;

ALTER TABLE `sendmail`
  MODIFY `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT;

ALTER TABLE `sendmail_statut`
  MODIFY `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT;

ALTER TABLE `templates`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

ALTER TABLE `travaux`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

ALTER TABLE `trombi`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

ALTER TABLE `unites`
  MODIFY `id` int(3) UNSIGNED NOT NULL AUTO_INCREMENT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
