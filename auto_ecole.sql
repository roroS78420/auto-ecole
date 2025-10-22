Drop database if exists auto_ecole;
Create database auto_ecole;
Use auto_ecole;
-- phpMyAdmin SQL Dump
-- version 5.0.2
-- https://www.phpmyadmin.net/
--
-- Hôte : 127.0.0.1
-- Généré le : jeu. 29 avr. 2021 à 17:06
-- Version du serveur :  10.4.14-MariaDB
-- Version de PHP : 7.4.10

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Base de données : `auto_ecole`
--

-- --------------------------------------------------------

--
-- Structure de la table `lessons`
--

CREATE TABLE `lessons` (
  `id_l` int(11) NOT NULL,
  `titre` varchar(50) DEFAULT NULL,
  `description` text DEFAULT NULL,
  `date_l` datetime DEFAULT NULL,
  `date_fin` datetime DEFAULT NULL,
  `id_e` int(11) DEFAULT NULL,
  `id_m` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Déchargement des données de la table `lessons`
--

INSERT INTO `lessons` (`id_l`, `titre`, `description`, `date_l`, `date_fin`, `id_e`, `id_m`) VALUES
(1, 'Maitrise de la vitesse', 'Conduite en voiture tout en contrôlant la vitesse', '2021-04-15 09:00:00', '2021-04-15 11:00:00', 3, 2),
(2, 'Maitrise du volant', 'Conduite en voiture tout en contrôlant le volant', '2021-04-21 10:00:00', '2021-04-21 12:00:00', 3, 2),
(3, 'sdqsdqsd', 'qsdqsdq', '2021-04-27 15:36:00', '2021-04-27 15:36:00', 3, 2),
(4, 'sdqsdqsd', 'qsdqsdq', '2021-04-27 15:43:00', '2021-04-27 16:13:00', 3, 2),
(5, 'qsdqsd', 'sqdqsd', '2021-04-28 15:37:00', '2021-04-28 15:37:00', 3, 2),
(6, 'qsdqsd', 'sqdqsd', '2021-04-28 15:37:00', '2021-04-28 15:37:00', 3, 2);

-- --------------------------------------------------------

--
-- Structure de la table `messages`
--

CREATE TABLE `messages` (
  `id` int(11) NOT NULL,
  `id_exp` int(11) NOT NULL,
  `id_dest` int(11) NOT NULL,
  `message` text NOT NULL,
  `objet` text NOT NULL,
  `lu` int(11) DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Déchargement des données de la table `messages`
--

INSERT INTO `messages` (`id`, `id_exp`, `id_dest`, `message`, `objet`, `lu`) VALUES
(1, 2, 3, 'qsdqsd', 'qsdqsd', 0),
(2, 2, 3, 'dqsdqsdQSD QSDFqsfcsdfgdfsg', 'qsdqsdqs', 0),
(3, 3, 2, 'sqdsqdqsdqsd', 'Cours de conduite', 0);

-- --------------------------------------------------------

--
-- Structure de la table `users`
--

CREATE TABLE `users` (
  `id_u` int(11) NOT NULL,
  `nom` varchar(50) DEFAULT NULL,
  `prenom` varchar(50) DEFAULT NULL,
  `tel` varchar(10) DEFAULT NULL,
  `email` varchar(255) DEFAULT NULL,
  `mdp` varchar(255) DEFAULT NULL,
  `lvl` int(11) DEFAULT 1,
  `adresse` varchar(100) DEFAULT NULL,
  `cp` varchar(10) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Déchargement des données de la table `users`
--

INSERT INTO `users` (`id_u`, `nom`, `prenom`, `tel`, `email`, `mdp`, `lvl`, `adresse`, `cp`) VALUES
(1, 'Admin', 'admin', '0606060606', 'admin@gmail.com', 'd033e22ae348aeb5660fc2140aec35850c4da997', 3, '5 rue de CHEPAKOI', '77000'),
(2, 'moniteur', 'moniteur', '0707070707', 'moniteur@gmail.com', 'd8c1338b7ce9b36f4ed6714036c1bfb3343f6a2c', 2, '18, Avenue de CHEPAKOI', '87500'),
(3, 'eleve', 'eleve', '0808080808', 'eleve@gmail.com', '0e9a7fdc4821370a252df21582a4a656e81e0687', 1, '5 rue de CHEPAKOI', '78500');

--
-- Index pour les tables déchargées
--

--
-- Index pour la table `lessons`
--
ALTER TABLE `lessons`
  ADD PRIMARY KEY (`id_l`),
  ADD KEY `id_e` (`id_e`),
  ADD KEY `id_m` (`id_m`);

--
-- Index pour la table `messages`
--
ALTER TABLE `messages`
  ADD PRIMARY KEY (`id`),
  ADD KEY `id_dest` (`id_dest`),
  ADD KEY `id_exp` (`id_exp`);

--
-- Index pour la table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id_u`),
  ADD UNIQUE KEY `tel` (`tel`),
  ADD UNIQUE KEY `email` (`email`);

--
-- AUTO_INCREMENT pour les tables déchargées
--

--
-- AUTO_INCREMENT pour la table `lessons`
--
ALTER TABLE `lessons`
  MODIFY `id_l` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT pour la table `messages`
--
ALTER TABLE `messages`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT pour la table `users`
--
ALTER TABLE `users`
  MODIFY `id_u` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- Contraintes pour les tables déchargées
--

--
-- Contraintes pour la table `lessons`
--
ALTER TABLE `lessons`
  ADD CONSTRAINT `lessons_ibfk_1` FOREIGN KEY (`id_e`) REFERENCES `users` (`id_u`),
  ADD CONSTRAINT `lessons_ibfk_2` FOREIGN KEY (`id_m`) REFERENCES `users` (`id_u`);

--
-- Contraintes pour la table `messages`
--
ALTER TABLE `messages`
  ADD CONSTRAINT `messages_ibfk_1` FOREIGN KEY (`id_dest`) REFERENCES `users` (`id_u`),
  ADD CONSTRAINT `messages_ibfk_2` FOREIGN KEY (`id_exp`) REFERENCES `users` (`id_u`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
