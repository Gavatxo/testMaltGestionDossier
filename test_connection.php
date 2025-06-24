<?php
// test_connection_simple.php - Version simplifiée du test de connexion
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Test Connexion Simple</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 40px; background: #f5f5f5; }
        .container { max-width: 600px; margin: 0 auto; background: white; padding: 30px; border-radius: 8px; box-shadow: 0 2px 10px rgba(0,0,0,0.1); }
        .success { color: #28a745; background: #d4edda; padding: 15px; border: 1px solid #c3e6cb; border-radius: 4px; margin: 10px 0; }
        .error { color: #dc3545; background: #f8d7da; padding: 15px; border: 1px solid #f5c6cb; border-radius: 4px; margin: 10px 0; }
        .info { color: #0c5460; background: #d1ecf1; padding: 15px; border: 1px solid #bee5eb; border-radius: 4px; margin: 10px 0; }
        table { width: 100%; border-collapse: collapse; margin-top: 20px; }
        th, td { border: 1px solid #ddd; padding: 8px; text-align: left; }
        th { background-color: #f2f2f2; }
    </style>
</head>
<body>
    <div class="container">
        <h1>🧪 Test de Connexion Simple</h1>
        
        <?php
        // Configuration
        echo "<div class='info'><strong>Configuration :</strong><br>";
        echo "PHP Version : " . phpversion() . "<br>";
        echo "Extension PDO : " . (extension_loaded('pdo') ? '✅ OK' : '❌ Manquante') . "<br>";
        echo "Extension PDO MySQL : " . (extension_loaded('pdo_mysql') ? '✅ OK' : '❌ Manquante') . "</div>";
        
        try {
            // Chargement de la classe Database
            require_once 'config/database.php';
            echo "<div class='success'>✅ Classe Database chargée</div>";
            
            // Test de connexion
            $database = new Database();
            $conn = $database->getConnection();
            echo "<div class='success'>✅ Connexion réussie !</div>";
            
            // Informations de base simples
            try {
                $result = $conn->query("SELECT DATABASE() as db_name")->fetch();
                echo "<div class='info'>Base de données : " . ($result['db_name'] ?: 'Aucune') . "</div>";
            } catch (Exception $e) {
                echo "<div class='error'>Erreur info DB : " . $e->getMessage() . "</div>";
            }
            
            // Test des tables
            try {
                $stmt = $conn->query("SHOW TABLES");
                $tables = $stmt->fetchAll(PDO::FETCH_COLUMN);
                
                if (empty($tables)) {
                    echo "<div class='error'>⚠️ Aucune table trouvée</div>";
                } else {
                    echo "<div class='success'>✅ Tables trouvées : " . implode(', ', $tables) . "</div>";
                    
                    // Compter les données
                    if (in_array('entites', $tables)) {
                        $stmt = $conn->query("SELECT type, COUNT(*) as nb FROM entites GROUP BY type");
                        $counts = $stmt->fetchAll();
                        
                        echo "<table>";
                        echo "<tr><th>Type</th><th>Nombre</th></tr>";
                        foreach ($counts as $count) {
                            echo "<tr><td>" . $count['type'] . "</td><td>" . $count['nb'] . "</td></tr>";
                        }
                        echo "</table>";
                    }
                }
            } catch (Exception $e) {
                echo "<div class='error'>Erreur tables : " . $e->getMessage() . "</div>";
            }
            
        } catch (Exception $e) {
            echo "<div class='error'>❌ Erreur : " . $e->getMessage() . "</div>";
            echo "<div class='info'><strong>Vérifiez :</strong><br>";
            echo "1. MAMP est démarré<br>";
            echo "2. Port MySQL = 8889<br>";
            echo "3. Base 'gestion_dossiers' existe</div>";
        }
        ?>
        
        <div style="margin-top: 30px; padding: 20px; background: #e9ecef; border-radius: 4px;">
            <strong>🔗 Liens utiles :</strong><br>
            <a href="index.php" style="color: #007bff;">🚀 Application principale</a><br>
            <a href="test_models.php" style="color: #007bff;">🧪 Test des modèles</a><br>
            <a href="http://localhost:8888/phpMyAdmin" style="color: #007bff;">🗄️ phpMyAdmin</a>
        </div>
    </div>
</body>
</html>