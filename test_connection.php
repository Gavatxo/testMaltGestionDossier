<?php
// test_connection.php
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Test Connexion Base de Données</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 40px; background: #f5f5f5; }
        .container { max-width: 600px; margin: 0 auto; background: white; padding: 30px; border-radius: 8px; box-shadow: 0 2px 10px rgba(0,0,0,0.1); }
        .success { color: #28a745; background: #d4edda; padding: 15px; border: 1px solid #c3e6cb; border-radius: 4px; margin: 10px 0; }
        .error { color: #dc3545; background: #f8d7da; padding: 15px; border: 1px solid #f5c6cb; border-radius: 4px; margin: 10px 0; }
        .info { color: #0c5460; background: #d1ecf1; padding: 15px; border: 1px solid #bee5eb; border-radius: 4px; margin: 10px 0; }
        table { width: 100%; border-collapse: collapse; margin-top: 20px; }
        th, td { border: 1px solid #ddd; padding: 8px; text-align: left; }
        th { background-color: #f2f2f2; }
        pre { background: #f8f9fa; padding: 10px; border-radius: 4px; overflow-x: auto; }
    </style>
</head>
<body>
    <div class="container">
        <h1>🧪 Test de Connexion - Gestion de Dossiers</h1>
        
        <?php
        // Test de la configuration
        echo "<div class='info'><strong>Configuration actuelle :</strong><br>";
        echo "PHP Version : " . phpversion() . "<br>";
        echo "Extension PDO : " . (extension_loaded('pdo') ? '✅ Installée' : '❌ Manquante') . "<br>";
        echo "Extension PDO MySQL : " . (extension_loaded('pdo_mysql') ? '✅ Installée' : '❌ Manquante') . "</div>";
        
        // Test 1 : Inclusion des fichiers
        try {
            require_once 'config/database.php';
            echo "<div class='success'>✅ Fichier database.php chargé avec succès</div>";
        } catch (Exception $e) {
            echo "<div class='error'>❌ Erreur lors du chargement : " . $e->getMessage() . "</div>";
            exit;
        }
        
        // Test 2 : Création de l'instance Database
        try {
            $database = new Database();
            echo "<div class='success'>✅ Instance Database créée</div>";
        } catch (Exception $e) {
            echo "<div class='error'>❌ Erreur création instance : " . $e->getMessage() . "</div>";
            exit;
        }
        
        // Test 3 : Connexion à la base de données
        try {
            $conn = $database->getConnection();
            echo "<div class='success'>✅ Connexion à la base de données réussie !</div>";
            
            // Test 4 : Information sur la base
            $stmt = $conn->query("SELECT DATABASE() as current_db, USER() as current_user, VERSION() as mysql_version");
            $info = $stmt->fetch();
            
            echo "<div class='info'>";
            echo "<strong>Informations de connexion :</strong><br>";
            echo "Base de données : " . ($info['current_db'] ?: 'Aucune') . "<br>";
            echo "Utilisateur : " . $info['current_user'] . "<br>";
            echo "Version MySQL : " . $info['mysql_version'] . "<br>";
            echo "</div>";
            
        } catch (Exception $e) {
            echo "<div class='error'>❌ Erreur de connexion : " . $e->getMessage() . "</div>";
            echo "<div class='info'><strong>Solutions possibles :</strong><br>";
            echo "1. Vérifier que MAMP est démarré<br>";
            echo "2. Vérifier le port MySQL (8889 par défaut)<br>";
            echo "3. Vérifier les identifiants (root/root par défaut)<br>";
            echo "4. Créer la base de données 'gestion_dossiers'</div>";
            exit;
        }
        
        // Test 5 : Vérification des tables
        try {
            $stmt = $conn->query("SHOW TABLES");
            $tables = $stmt->fetchAll(PDO::FETCH_COLUMN);
            
            if (empty($tables)) {
                echo "<div class='error'>⚠️ Aucune table trouvée. Vous devez importer les scripts SQL.</div>";
            } else {
                echo "<div class='success'>✅ Tables trouvées : " . implode(', ', $tables) . "</div>";
                
                // Test 6 : Comptage des données
                if (in_array('entites', $tables) && in_array('relations', $tables)) {
                    $stmt = $conn->query("SELECT 
                        (SELECT COUNT(*) FROM entites WHERE type='dossier') as nb_dossiers,
                        (SELECT COUNT(*) FROM entites WHERE type='tiers') as nb_tiers,
                        (SELECT COUNT(*) FROM entites WHERE type='contact') as nb_contacts,
                        (SELECT COUNT(*) FROM relations) as nb_relations
                    ");
                    $stats = $stmt->fetch();
                    
                    echo "<table>";
                    echo "<tr><th>Type</th><th>Nombre</th></tr>";
                    echo "<tr><td>Dossiers</td><td>" . $stats['nb_dossiers'] . "</td></tr>";
                    echo "<tr><td>Tiers</td><td>" . $stats['nb_tiers'] . "</td></tr>";
                    echo "<tr><td>Contacts</td><td>" . $stats['nb_contacts'] . "</td></tr>";
                    echo "<tr><td>Relations</td><td>" . $stats['nb_relations'] . "</td></tr>";
                    echo "</table>";
                }
            }
            
        } catch (Exception $e) {
            echo "<div class='error'>❌ Erreur lors de la vérification des tables : " . $e->getMessage() . "</div>";
        }
        
        // Test 7 : Vérification des vues (si elles existent)
        try {
            $stmt = $conn->query("SHOW FULL TABLES WHERE Table_type = 'VIEW'");
            $views = $stmt->fetchAll(PDO::FETCH_COLUMN);
            
            if (!empty($views)) {
                echo "<div class='success'>✅ Vues SQL trouvées : " . implode(', ', $views) . "</div>";
            } else {
                echo "<div class='info'>ℹ️ Aucune vue SQL trouvée (optionnel)</div>";
            }
            
        } catch (Exception $e) {
            // Ignore si les vues ne sont pas encore créées
        }
        ?>
    </div>
</body>
</html>