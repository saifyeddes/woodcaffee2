Gestionnaire de Menu de Café
Ce projet permet de gérer le menu d’un café ayant deux interfaces : une pour les utilisateurs et une pour l’administrateur.

Fonctionnalités
Interface Utilisateur
Affichage du menu du café avec catégories, sous-catégories et produits.
Interface Administrateur
Connexion sécurisée
Gestion du menu (ajouter, modifier, supprimer catégories, sous-catégories et produits)
Changement de mot de passe
Technologies utilisées
HTML, CSS, JavaScript
PHP (pour la logique backend)
MySQL (pour la gestion de la base de données)
Un serveur local (XAMPP, WAMP, MAMP, etc.)
Structure du projet
bash
/projet-cafe/
│
├── index.php            # Page d'accueil pour l'utilisateur (affichage du menu)
├── admin.php            # Page de connexion admin
├── woodcoffe.sql         # Connexion à la base de données

Installation
Clonez ou téléchargez ce projet dans le répertoire de votre serveur local (par exemple, htdocs dans XAMPP).
Créez une base de données MySQL, par exemple cafe_db.
Importez le fichier SQL contenu dans database.sql pour créer les tables nécessaires.
Configurez la connexion à la base de données dans database.php.
Accédez à http://localhost/nom_du_projet/ pour voir le menu utilisateur.
Accédez à http://localhost/nom_du_projet/admin.php pour accéder à l'interface administrateur.
Fonctionnalités administratives
Connexion sécurisé (mot de passe)
Ajout, modification et suppression de catégories, sous-catégories et produits
Changement de mot de passe
Notes
Pensez à sécuriser votre interface d'administration (par exemple, ajouter un login sécurisé).
Vous pouvez personnaliser le style et les fonctionnalités selon vos besoins.
Contact
Pour toute question ou contribution, veuillez me contacter.

