-- phpMyAdmin SQL Dump
-- version 5.0.4
-- https://www.phpmyadmin.net/
--
-- Hôte : localhost
-- Généré le : jeu. 21 juil. 2022 à 21:18
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
-- Structure de la table `groupes`
--

CREATE TABLE `groupes` (
  `id` int(11) NOT NULL,
  `title` varchar(255) DEFAULT NULL,
  `color` varchar(255) DEFAULT NULL,
  `type` varchar(255) DEFAULT NULL,
  `acteurs` varchar(255) NOT NULL,
  `member` varchar(255) NOT NULL,
  `created` datetime NOT NULL,
  `updated` datetime NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

--
-- Déchargement des données de la table `groupes`
--

INSERT INTO `groupes` (`id`, `title`, `color`, `type`, `acteurs`, `member`, `created`, `updated`) VALUES
(1, 'Fêtes et vie de l’école', 'nicdark_bg_orange', 'com', '[]', '', '2021-11-22 09:54:14', '2022-06-28 10:17:01'),
(2, 'Ressources Humaines et comptabilité', 'nicdark_bg_yellowdark', 'com', '[]', '', '2021-11-22 09:54:53', '2021-11-22 11:16:40'),
(3, 'Travaux et entretien des bâtiments', 'nicdark_bg_green', 'com', '[]', '', '2021-11-22 09:55:11', '2021-11-22 10:26:20'),
(4, 'Ménage (hors journalier)', 'nicdark_bg_violet', 'com', '[]', '', '2021-11-22 09:55:29', '2021-11-22 10:26:37'),
(5, 'Bilinguisme (hors temps scolaire)', 'nicdark_bg_red', 'com', '[]', '', '2021-11-22 09:55:39', '2021-11-22 10:26:46'),
(6, 'Informatique', 'nicdark_bg_bluedark', 'com', '[\"976\",\"984\"]', '', '2021-11-22 09:55:48', '2022-06-28 09:14:55'),
(7, 'Entretien avec les nouvelles familles', 'nicdark_bg_green', 'com', '[]', '', '2021-11-22 09:55:58', '2022-06-28 09:01:02'),
(8, 'Suivi des Unités Associatives', 'nicdark_bg_orangedark', 'com', '[]', '', '2021-11-22 09:56:07', '2021-12-07 04:21:33'),
(9, 'Communication', 'nicdark_bg_greendark', 'com', '[]', '', '2021-11-22 09:56:15', '2021-12-07 04:21:42'),
(10, 'Financement', 'nicdark_bg_yellowdark', 'com', '[]', '', '2021-11-22 09:56:24', '2021-12-07 04:22:14'),
(11, 'Bureau', 'nicdark_bg_orange', 'org', '[\"570\",\"881\"]', '', '2021-11-22 10:13:51', '2022-06-30 08:36:58'),
(12, 'TEST', 'nicdark_bg_red', 'org', '[\"396\",\"570\"]', '', '2022-02-23 01:36:14', '2022-02-23 01:36:23');

--
-- Index pour les tables déchargées
--

--
-- Index pour la table `groupes`
--
ALTER TABLE `groupes`
  ADD PRIMARY KEY (`id`);

--
-- AUTO_INCREMENT pour les tables déchargées
--

--
-- AUTO_INCREMENT pour la table `groupes`
--
ALTER TABLE `groupes`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=13;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
