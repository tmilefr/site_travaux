-- phpMyAdmin SQL Dump
-- version 5.2.3
-- https://www.phpmyadmin.net/
--
-- Hôte : localhost:3306
-- Généré le : jeu. 01 oct. 2026 à 09:08
-- Version du serveur : 10.11.14-MariaDB-0ubuntu0.24.04.1
-- Version de PHP : 8.4.25

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Base de données : `itregiomlh_prod`
--

-- --------------------------------------------------------

--
-- Structure de la table `options`
--

CREATE TABLE `options` (
  `id` int(11) NOT NULL,
  `cle` varchar(255) NOT NULL,
  `value` varchar(255) NOT NULL,
  `filter` varchar(255) NOT NULL,
  `created` date NOT NULL,
  `updated` date NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

--
-- Déchargement des données de la table `options`
--

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

--
-- Index pour les tables déchargées
--

--
-- Index pour la table `options`
--
ALTER TABLE `options`
  ADD PRIMARY KEY (`id`);

--
-- AUTO_INCREMENT pour les tables déchargées
--

--
-- AUTO_INCREMENT pour la table `options`
--
ALTER TABLE `options`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=50;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
