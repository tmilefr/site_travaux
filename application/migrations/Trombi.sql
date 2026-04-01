-- phpMyAdmin SQL Dump
-- version 5.0.4
-- https://www.phpmyadmin.net/
--
-- Hôte : localhost
-- Généré le : jeu. 21 juil. 2022 à 21:14
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
-- Structure de la table `trombi`
--

CREATE TABLE `trombi` (
  `id` int(11) NOT NULL,
  `id_grp` int(11) DEFAULT NULL,
  `photo` tinytext CHARACTER SET latin1 NOT NULL,
  `nom` varchar(50) CHARACTER SET latin1 NOT NULL DEFAULT '',
  `num_tel` varchar(20) CHARACTER SET latin1 NOT NULL DEFAULT '',
  `email` varchar(50) CHARACTER SET latin1 NOT NULL DEFAULT '',
  `ref_travaux` tinyint(4) NOT NULL DEFAULT '1',
  `title` varchar(255) NOT NULL,
  `description` varchar(255) NOT NULL,
  `color` varchar(255) NOT NULL,
  `classif` varchar(255) NOT NULL,
  `created` datetime DEFAULT NULL,
  `updated` datetime DEFAULT NULL
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

--
-- Déchargement des données de la table `trombi`
--

INSERT INTO `trombi` (`id`, `id_grp`, `photo`, `nom`, `num_tel`, `email`, `ref_travaux`, `title`, `description`, `color`, `classif`, `created`, `updated`) VALUES
(8, 6, '', '976', '', 'test@test.com', 1, '', '', '', 'RT', '2021-11-22 10:05:38', '2022-06-28 09:14:55'),
(11, 11, '', '881', '', '', 1, '', '', '', 'PE', '0000-00-00 00:00:00', '2022-06-30 08:36:58'),
(10, 11, '', '570', '', '', 1, '', '', '', 'SE', '2021-11-22 10:15:51', '2022-06-30 08:36:58'),
(13, 6, '', '984', '', '', 1, '', '', '', 'ME', '0000-00-00 00:00:00', '2022-06-28 09:14:55'),
(19, 12, '', '396', '', '', 1, '', '', '', '...', '2022-02-23 01:36:23', '0000-00-00 00:00:00'),
(20, 12, '', '570', '', '', 1, '', '', '', 'RT', '2022-02-23 01:36:23', '0000-00-00 00:00:00');

--
-- Index pour les tables déchargées
--

--
-- Index pour la table `trombi`
--
ALTER TABLE `trombi`
  ADD PRIMARY KEY (`id`);

--
-- AUTO_INCREMENT pour les tables déchargées
--

--
-- AUTO_INCREMENT pour la table `trombi`
--
ALTER TABLE `trombi`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=22;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
