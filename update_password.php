<?php
// Connexion à la base de données
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "woodcaffe";
// Database connection variables
// $servername = "sql203.byethost24.com";
// $username = "b24_38999878";
// $password = "stv0kg7d";
// $dbname = "b24_38999878_woodcaffe";
try {
    $conn = new PDO("mysql:host=$servername;dbname=$dbname", $username, $password);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    // Vérifier si la table admin existe
    $tableExists = $conn->query("SHOW TABLES LIKE 'admin'")->rowCount() > 0;
    
    if (!$tableExists) {
        // Créer la table admin si elle n'existe pas
        $conn->exec("CREATE TABLE admin (
            id INT PRIMARY KEY AUTO_INCREMENT,
            password VARCHAR(255) NOT NULL
        )");
        
        // Insérer un mot de passe par défaut (haché)
        $defaultPassword = 'admin123'; // À changer en production !
        $hashedPassword = password_hash($defaultPassword, PASSWORD_BCRYPT, ['cost' => 12]);
        
        $stmt = $conn->prepare("INSERT INTO admin (password) VALUES (?)");
        $stmt->execute([$hashedPassword]);
        
        echo "Table 'admin' créée avec succès.\n";
        echo "Mot de passe par défaut: admin123 (à changer immédiatement)\n";
    } else {
        // Vérifier si un mot de passe existe déjà
        $stmt = $conn->query("SELECT * FROM admin WHERE id = 1");
        $admin = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if ($admin) {
            // Mettre à jour le mot de passe existant avec un hachage
            $newPassword = 'admin123'; // À changer en production !
            $hashedPassword = password_hash($newPassword, PASSWORD_BCRYPT, ['cost' => 12]);
            
            $stmt = $conn->prepare("UPDATE admin SET password = ? WHERE id = 1");
            $stmt->execute([$hashedPassword]);
            
            echo "Mot de passe mis à jour avec succès.\n";
            echo "Nouveau mot de passe: admin123 (à changer immédiatement)\n";
        } else {
            // Insérer un nouvel enregistrement admin
            $defaultPassword = 'admin123'; // À changer en production !
            $hashedPassword = password_hash($defaultPassword, PASSWORD_BCRYPT, ['cost' => 12]);
            
            $stmt = $conn->prepare("INSERT INTO admin (id, password) VALUES (1, ?)");
            $stmt->execute([$hashedPassword]);
            
            echo "Compte admin créé avec succès.\n";
            echo "Mot de passe par défaut: admin123 (à changer immédiatement)\n";
        }
    }
    
    // Afficher les informations de débogage
    $stmt = $conn->query("SELECT * FROM admin");
    $result = $stmt->fetchAll(PDO::FETCH_ASSOC);
    echo "\nContenu de la table admin :\n";
    print_r($result);
    
} catch(PDOException $e) {
    echo "Erreur : " . $e->getMessage();
}

// Supprimer ce fichier après utilisation pour des raisons de sécurité
// unlink(__FILE__);
?>

<p>Exécution terminée. <a href='index.php'>Retour à l'accueil</a></p>
