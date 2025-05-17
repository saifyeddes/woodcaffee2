-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Hôte : 127.0.0.1
-- Généré le : sam. 17 mai 2025 à 00:38
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
-- Base de données : `woodcaffe`
--

-- --------------------------------------------------------

--
-- Réinitialisation complète de la base WOODCAFFE
DROP TABLE IF EXISTS produits;
DROP TABLE IF EXISTS sous_categories;
DROP TABLE IF EXISTS categories;
DROP TABLE IF EXISTS admin;

-- Table des admin
CREATE TABLE `admin` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `password` varchar(255) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Déchargement des données de la table `admin`
--
INSERT INTO `admin` (`id`, `password`) VALUES
(1, '1111');

-- Table des catégories
CREATE TABLE `categories` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `nom` varchar(255) NOT NULL,
  `description` text DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Déchargement des données de la table `categories` (in desired order)
--
INSERT INTO `categories` (`id`, `nom`, `description`) VALUES
(1, 'Petit Dej', 'Petit déjeuner et brunch'),
(2, 'Boissons Chaudes', 'Cafés, thés, chocolats chauds'),
(3, 'Boissons', 'Eau, sodas, thés, chicha'),
(4, 'Boissons Froides', 'Milkshakes, frappuccino, smoothies, jus, etc.'),
(5, 'Boissons Wood Kaffee', 'Smoothies, Mojitos, Jus'),
(6, 'Sucrés', 'Desserts'),
(7, 'Salés', 'Plats salés');

-- Table des sous-catégories
CREATE TABLE `sous_categories` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `nom` varchar(255) NOT NULL,
  `categorie_id` int(11) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `categorie_id` (`categorie_id`),
  CONSTRAINT `fk_souscat_cat` FOREIGN KEY (`categorie_id`) REFERENCES `categories` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Insertion des sous-catégories
INSERT INTO `sous_categories` (`id`, `nom`, `categorie_id`) VALUES
-- Petit Dej
(1, 'Express', 1),
(2, 'Spécial', 1),
(3, 'Gourmand Sucré', 1),
(4, 'Gourmand Salé', 1),
(5, 'Brunch', 1),
-- Boissons Chaudes
(6, 'Café', 2),
(7, 'Café Aromatisée', 2),
(8, 'Chocolat Chaud', 2),
-- Boissons
(9, 'Direct', 3), -- New subcategory for direct products
(10, 'Chicha', 3), -- Renamed from ID 22 to maintain order
-- Boissons Froides
(11, 'Milkshakes', 4),
(12, 'Frappuccino', 4),
(13, 'Café Glacé', 4),
-- Boissons Wood Kaffee
(14, 'Smoothies', 5),
(15, 'Mojito', 5),
(16, 'Jus', 5),
-- Sucrés
(17, 'Crêpes', 6),
(18, 'Pancakes', 6),
(19, 'Jwajem', 6),
-- Salés
(20, 'Crêpes Salées', 7),
(21, 'Omelettes', 7);

--
-- Structure de la table `produits`
--

-- Table des produits
CREATE TABLE `produits` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `nom` varchar(255) NOT NULL,
  `prix` float NOT NULL,
  `sous_categorie_id` int(11) NOT NULL,
  `description` text DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `sous_categorie_id` (`sous_categorie_id`),
  CONSTRAINT `fk_produit_souscat` FOREIGN KEY (`sous_categorie_id`) REFERENCES `sous_categories` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Petit Dej
INSERT INTO produits (nom, prix, sous_categorie_id, description) VALUES
('Express', 6.50, 1, 'Café au choix + Pain Chocolat + Jus + 0,5 L Eau'),
('Spécial', 7.50, 2, 'Café au choix + Mini Pancake + Jus + 0,5 L Eau'),
('Gourmand Sucré', 11.00, 3, 'Café au choix + Mini Pancake + Jus + 0,5 L Eau + Plat (Chamia, Beurre, Confiture, Miel)'),
('Gourmand Salé', 12.00, 4, 'Café au choix + Jus + 0,5 L Eau + Plat (Gouta, Jambon, Salami, Fromage) + Légumes + Omelette'),
('Brunch', 30.00, 5, '2 Cafés au choix + 2 Pancakes + 2 Yaourts + 2 Jus + Plat (Gouta, Confiture, Chocolat, Miel, Chemia) + 2 Omelettes/Œufs + Plat (Harissa, Fromage, Jambon, Salami, Mayonnaise, Ketchup) + Brochettes de Fruits + 1 L Eau');

-- Boissons Chaudes > Café
INSERT INTO produits (nom, prix, sous_categorie_id) VALUES
('Express', 3.40, 6),
('Capucin', 3.50, 6),
('Direct', 3.40, 6),
('Grand Crème', 3.40, 6),
('Américain', 3.20, 6),
('Café au Lait', 3.20, 6);

-- Boissons Chaudes > Café Aromatisée
INSERT INTO produits (nom, prix, sous_categorie_id) VALUES
('Caramel', 4.50, 7),
('Noisette', 4.50, 7),
('Nescafé', 3.50, 7),
('Cappuccino', 4.00, 7),
('Cappuccino à la Crème', 6.00, 7),
('Café Turc', 4.00, 7);

-- Boissons Chaudes > Chocolat Chaud
INSERT INTO produits (nom, prix, sous_categorie_id) VALUES
('Chocolat Chaud Nature', 5.00, 8),
('Chocolat Chaud À la Crème', 7.00, 8);

-- Boissons > Direct (previously Autres Boissons and Thé)
INSERT INTO produits (nom, prix, sous_categorie_id) VALUES
('Eau 0,5 L', 2.00, 9),
('Eau 1 L', 2.50, 9),
('Soda', 3.50, 9),
('Soda Énergétique', 7.00, 9),
('Thé Vert à la Menthe', 3.00, 9),
('Thé Vert', 3.00, 9),
('Thé Infusion', 3.00, 9),
('Thé aux Amandes', 6.00, 9);

-- Boissons > Chicha
INSERT INTO produits (nom, prix, sous_categorie_id) VALUES
('Menthe', 8.00, 10),
('Raisin', 8.00, 10),
('Pomme', 8.00, 10),
('Cocktail Love', 8.00, 10),
('Gumgum', 8.00, 10),
('Chikhani', 8.00, 10);

-- Boissons Froides > Milkshakes
INSERT INTO produits (nom, prix, sous_categorie_id) VALUES
('Chocolat', 9.00, 11),
('Nutella', 8.00, 11),
('Oreo', 9.00, 11),
('Strawberry Oreo', 9.00, 11),
('Snickers', 9.00, 11),
('Kinder', 8.00, 11),
('Vanille', 8.00, 11);

-- Boissons Froides > Frappuccino
INSERT INTO produits (nom, prix, sous_categorie_id) VALUES
('Chocolat', 7.00, 12),
('Nutella', 8.00, 12),
('Oreo', 7.00, 12),
('Vanille', 6.50, 12),
('Caramel', 6.50, 12),
('Noisette', 6.50, 12);

-- Boissons Froides > Café Glacé
INSERT INTO produits (nom, prix, sous_categorie_id) VALUES
('Nutella', 8.00, 13),
('Chocolat', 6.00, 13),
('Caramel', 5.50, 13),
('Oreo', 5.50, 13),
('Noisette', 5.50, 13);

-- Boissons Wood Kaffee > Smoothies
INSERT INTO produits (nom, prix, sous_categorie_id) VALUES
('Fraise', 8.00, 14),
('Banane', 8.00, 14),
('Nutella', 8.00, 14),
('Pina Colada', 9.00, 14),
('Blueberry', 9.50, 14);

-- Boissons Wood Kaffee > Mojito
INSERT INTO produits (nom, prix, sous_categorie_id) VALUES
('Virgin', 6.00, 15),
('Bleu', 7.50, 15),
('Black', 8.00, 15),
('Red', 7.50, 15),
('Pina Colada', 8.00, 15),
('Energétique', 10.00, 15);

-- Boissons Wood Kaffee > Jus
INSERT INTO produits (nom, prix, sous_categorie_id) VALUES
('Orange', 4.50, 16),
('Citronnade', 4.50, 16),
('Lait de Poule', 6.00, 16),
('Fraise', 7.00, 16),
('Duo Saison', 8.00, 16),
('Trio Saison', 9.50, 16),
('Spotif', 12.00, 16);

-- Sucrés > Crêpes
INSERT INTO produits (nom, prix, sous_categorie_id) VALUES
('Chocolat', 7.00, 17),
('Nutella', 9.00, 17),
('Chocolat Oreo', 9.00, 17),
('Chocolat Fruits Secs', 10.00, 17),
('Chocolat Banane', 9.00, 17),
('Nutella Fruits Secs', 12.00, 17),
('Nutella Banane', 11.00, 17);

-- Sucrés > Pancakes
INSERT INTO produits (nom, prix, sous_categorie_id) VALUES
('Chocolat', 10.00, 18),
('Nutella', 12.00, 18),
('Chocolat Oreo', 12.00, 18),
('Chocolat Fruits Secs', 13.00, 18),
('Chocolat Banane', 12.00, 18),
('Nutella Fruits Secs', 13.00, 18),
('Nutella Banane', 13.00, 18);

-- Sucrés > Pancakes (ID 18)
INSERT INTO produits (nom, prix, sous_categorie_id) VALUES
('Chocolat Fruits Secs', 13.00, 18),
('Chocolat Banane', 12.00, 18),
('Nutella Fruits Secs', 13.00, 18),
('Nutella Banane', 13.00, 18);

-- Sucrés > Jwajem (ID 19)
INSERT INTO produits (nom, prix, sous_categorie_id) VALUES
('Classic', 8.00, 19),
('Big', 12.00, 19),
('Wood', 18.00, 19);

-- Salés > Crêpes Salées
INSERT INTO produits (nom, prix, sous_categorie_id, description) VALUES
('Thon Fromage', 10.00, 20, NULL),
('Jambon Fromage', 8.50, 20, NULL),
('Poulet Fromage', 13.00, 20, NULL),
('Wood', 14.00, 20, 'Thon, Jambon, Fromage, Œuf');

-- Salés > Omelettes
INSERT INTO produits (nom, prix, sous_categorie_id, description) VALUES
('Végétarienne', 8.00, 21, NULL),
('Thon Fromage', 10.00, 21, NULL),
('Jambon Fromage', 8.50, 21, NULL),
('Poulet Fromage', 13.00, 21, NULL),
('Wood', 14.00, 21, 'Thon, Jambon, Fromage');

--
-- Index pour les tables déchargées
--

--
-- Index pour la table `admin`
--
ALTER TABLE `admin`
  ADD PRIMARY KEY (`id`);

--
-- Index pour la table `categories`
--
ALTER TABLE `categories`
  ADD PRIMARY KEY (`id`);

--
-- Index pour la table `produits`
--
ALTER TABLE `produits`
  ADD PRIMARY KEY (`id`),
  ADD KEY `sous_categorie_id` (`sous_categorie_id`);

--
-- AUTO_INCREMENT pour les tables déchargées
--

--
-- AUTO_INCREMENT pour la table `admin`
--
ALTER TABLE `admin`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT pour la table `categories`
--
ALTER TABLE `categories`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- AUTO_INCREMENT pour la table `sous_categories`
--
ALTER TABLE `sous_categories`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=22;

--
-- AUTO_INCREMENT pour la table `produits`
--
ALTER TABLE `produits`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=90;

--
-- Contraintes pour les tables déchargées
--

--
-- Contraintes pour la table `sous_categories`
--
ALTER TABLE `sous_categories`
  ADD CONSTRAINT `fk_souscat_cat` FOREIGN KEY (`categorie_id`) REFERENCES `categories` (`id`) ON DELETE CASCADE;

--
-- Contraintes pour la table `produits`
--
ALTER TABLE `produits`
  ADD CONSTRAINT `fk_produit_souscat` FOREIGN KEY (`sous_categorie_id`) REFERENCES `sous_categories` (`id`) ON DELETE CASCADE;

COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;