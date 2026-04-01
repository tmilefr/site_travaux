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
-- Structure de la table `capacitys`
--

CREATE TABLE `capacitys` (
  `id` int(11) NOT NULL,
  `id_fam` int(10) DEFAULT NULL,
  `id_cap` varchar(255) NOT NULL,
  `created` datetime NOT NULL,
  `updated` datetime NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

--
-- Déchargement des données de la table `capacitys`
--

INSERT INTO `capacitys` (`id`, `id_fam`, `id_cap`, `created`, `updated`) VALUES
(23, 1061, 'elec', '2022-06-27 16:17:09', '0000-00-00 00:00:00'),
(24, 1061, 'mac', '2022-06-27 16:17:09', '0000-00-00 00:00:00'),
(25, 1061, 'pein', '2022-06-27 16:17:09', '0000-00-00 00:00:00'),
(37, 1000, 'pein', '2022-06-27 20:16:51', '0000-00-00 00:00:00'),
(38, 643, 'elec', '2022-06-27 20:16:51', '0000-00-00 00:00:00'),
(39, 967, 'chau', '2022-06-27 20:16:51', '0000-00-00 00:00:00'),
(40, 1003, 'elec', '2022-06-27 20:16:51', '0000-00-00 00:00:00'),
(41, 1003, 'mac', '2022-06-27 20:16:51', '0000-00-00 00:00:00'),
(42, 1003, 'pein', '2022-06-27 20:16:51', '0000-00-00 00:00:00'),
(43, 965, 'san', '2022-06-27 20:16:51', '0000-00-00 00:00:00'),
(44, 965, 'chau', '2022-06-27 20:16:51', '0000-00-00 00:00:00'),
(45, 965, 'pein', '2022-06-27 20:16:51', '0000-00-00 00:00:00'),
(47, 867, 'elec', '2022-06-27 20:16:51', '0000-00-00 00:00:00'),
(48, 867, 'mac', '2022-06-27 20:16:51', '0000-00-00 00:00:00'),
(49, 867, 'pein', '2022-06-27 20:16:51', '0000-00-00 00:00:00'),
(50, 885, 'pein', '2022-06-27 20:16:51', '0000-00-00 00:00:00'),
(51, 884, 'san', '2022-06-27 20:16:51', '0000-00-00 00:00:00'),
(52, 884, 'chau', '2022-06-27 20:16:51', '0000-00-00 00:00:00'),
(53, 884, 'elec', '2022-06-27 20:16:51', '0000-00-00 00:00:00'),
(54, 884, 'mac', '2022-06-27 20:16:51', '0000-00-00 00:00:00'),
(55, 884, 'pein', '2022-06-27 20:16:51', '0000-00-00 00:00:00'),
(56, 994, 'san', '2022-06-27 20:16:51', '0000-00-00 00:00:00'),
(57, 994, 'chau', '2022-06-27 20:16:51', '0000-00-00 00:00:00'),
(58, 994, 'elec', '2022-06-27 20:16:51', '0000-00-00 00:00:00'),
(59, 994, 'mac', '2022-06-27 20:16:51', '0000-00-00 00:00:00'),
(60, 994, 'pein', '2022-06-27 20:16:51', '0000-00-00 00:00:00'),
(63, 991, 'elec', '2022-06-27 20:16:51', '0000-00-00 00:00:00'),
(113, 984, 'elec', '2022-06-30 20:32:59', '0000-00-00 00:00:00'),
(114, 984, 'dev', '2022-06-30 20:32:59', '0000-00-00 00:00:00'),
(115, 984, 'inf', '2022-06-30 20:32:59', '0000-00-00 00:00:00'),
(116, 881, 'elec', '2022-06-30 20:37:52', '0000-00-00 00:00:00'),
(117, 881, 'pein', '2022-06-30 20:37:52', '0000-00-00 00:00:00');

--
-- Index pour les tables déchargées
--

--
-- Index pour la table `capacitys`
--
ALTER TABLE `capacitys`
  ADD PRIMARY KEY (`id`);

--
-- AUTO_INCREMENT pour les tables déchargées
--

--
-- AUTO_INCREMENT pour la table `capacitys`
--
ALTER TABLE `capacitys`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=118;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
