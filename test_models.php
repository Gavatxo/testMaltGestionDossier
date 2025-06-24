<?php
require_once 'config/config.php';
require_once 'config/database.php';
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Test des Modèles PHP</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 40px; background: #f5f5f5; }
        .container { max-width: 1000px; margin: 0 auto; background: white; padding: 30px; border-radius: 8px; box-shadow: 0 2px 10px rgba(0,0,0,0.1); }
        .success { color: #28a745; background: #d4edda; padding: 15px; border: 1px solid #c3e6cb; border-radius: 4px; margin: 10px 0; }
        .error { color: #dc3545; background: #f8d7da; padding: 15px; border: 1px solid #f5c6cb; border-radius: 4px; margin: 10px 0; }
        .info { color: #0c5460; background: #d1ecf1; padding: 15px; border: 1px solid #bee5eb; border-radius: 4px; margin: 10px 0; }
        .test-section { margin: 20px 0; padding: 20px; border: 1px solid #dee2e6; border-radius: 8px; }
        table { width: 100%; border-collapse: collapse; margin-top: 10px; }
        th, td { border: 1px solid #ddd; padding: 8px; text-align: left; font-size: 12px; }
        th { background-color: #f2f2f2; }
        pre { background: #f8f9fa; padding: 10px; border-radius: 4px; overflow-x: auto; font-size: 11px; }
        .btn { padding: 8px 16px; background: #007bff; color: white; text-decoration: none; border-radius: 4px; margin: 5px; }
    </style>
</head>
<body>
    <div class="container">
        <h1>🧪 Test des Modèles PHP</h1>
        
        <?php
        try {
            echo "<div class='info'>Chargement des modèles...</div>";
            
            require_once 'models/Database.php';
            require_once 'models/Dossier.php';
            require_once 'models/Tiers.php';
            require_once 'models/Contact.php';
            
            echo "<div class='success'>✅ Tous les modèles sont chargés avec succès</div>";
            
            $dossierModel = new Dossier();
            $tiersModel = new Tiers();
            $contactModel = new Contact();
            
            echo "<div class='success'>✅ Instances des modèles créées</div>";
            
        } catch (Exception $e) {
            echo "<div class='error'>❌ Erreur lors du chargement : " . $e->getMessage() . "</div>";
            exit;
        }
        ?>
        
        <div class="test-section">
            <h3>📁 Test du modèle Dossier</h3>
            <?php
            try {
                $dossiers = $dossierModel->getAll();
                echo "<div class='success'>✅ Récupération de " . count($dossiers) . " dossier(s)</div>";
                
                if (!empty($dossiers)) {
                    echo "<table>";
                    echo "<tr><th>ID</th><th>Référence</th><th>Date création</th><th>Nb Tiers</th><th>Nb Contacts</th></tr>";
                    foreach (array_slice($dossiers, 0, 3) as $dossier) {
                        echo "<tr>";
                        echo "<td>" . e($dossier['id']) . "</td>";
                        echo "<td>" . e($dossier['reference']) . "</td>";
                        echo "<td>" . formatDateShort($dossier['date_creation']) . "</td>";
                        echo "<td>" . e($dossier['nb_tiers']) . "</td>";
                        echo "<td>" . e($dossier['nb_contacts']) . "</td>";
                        echo "</tr>";
                    }
                    echo "</table>";
                }
                
                if (!empty($dossiers)) {
                    $premier = $dossiers[0];
                    $detail = $dossierModel->getById($premier['id']);
                    if ($detail) {
                        echo "<div class='info'>📋 Détail du dossier " . e($detail['reference']) . " : " . count($detail['tiers']) . " tiers associé(s)</div>";
                    }
                }
                
             
                $nouvelleRef = $dossierModel->getNextReference();
                echo "<div class='info'>🆔 Prochaine référence disponible : " . e($nouvelleRef) . "</div>";
                
            } catch (Exception $e) {
                echo "<div class='error'>❌ Erreur modèle Dossier : " . $e->getMessage() . "</div>";
            }
            ?>
        </div>
        
        <div class="test-section">
            <h3>🏢 Test du modèle Tiers</h3>
            <?php
            try {
                // Récupérer tous les tiers
                $tiers = $tiersModel->getAll();
                echo "<div class='success'>✅ Récupération de " . count($tiers) . " tiers</div>";
                
                if (!empty($tiers)) {
                    echo "<table>";
                    echo "<tr><th>ID</th><th>Dénomination</th><th>Nb Contacts</th><th>Nb Dossiers</th></tr>";
                    foreach (array_slice($tiers, 0, 5) as $tier) {
                        echo "<tr>";
                        echo "<td>" . e($tier['id']) . "</td>";
                        echo "<td>" . e($tier['denomination']) . "</td>";
                        echo "<td>" . e($tier['nb_contacts']) . "</td>";
                        echo "<td>" . e($tier['nb_dossiers']) . "</td>";
                        echo "</tr>";
                    }
                    echo "</table>";
                }
                
                $recherche = $tiersModel->search('Entreprise');
                echo "<div class='info'>🔍 Recherche 'Entreprise' : " . count($recherche) . " résultat(s)</div>";
                
            } catch (Exception $e) {
                echo "<div class='error'>❌ Erreur modèle Tiers : " . $e->getMessage() . "</div>";
            }
            ?>
        </div>
        
        <div class="test-section">
            <h3>👥 Test du modèle Contact</h3>
            <?php
            try {
                $contacts = $contactModel->getAll();
                echo "<div class='success'>✅ Récupération de " . count($contacts) . " contact(s)</div>";
                
                if (!empty($contacts)) {
                    echo "<table>";
                    echo "<tr><th>ID</th><th>Nom</th><th>Prénom</th><th>Email</th><th>Nb Tiers</th></tr>";
                    foreach (array_slice($contacts, 0, 5) as $contact) {
                        echo "<tr>";
                        echo "<td>" . e($contact['id']) . "</td>";
                        echo "<td>" . e($contact['nom']) . "</td>";
                        echo "<td>" . e($contact['prenom']) . "</td>";
                        echo "<td>" . e($contact['email']) . "</td>";
                        echo "<td>" . e($contact['nb_tiers']) . "</td>";
                        echo "</tr>";
                    }
                    echo "</table>";
                }
                
                $recherche = $contactModel->search('martin');
                echo "<div class='info'>🔍 Recherche 'martin' : " . count($recherche) . " résultat(s)</div>";
                
                $testValidation = Contact::validate([
                    'nom' => 'Test',
                    'prenom' => 'User',
                    'email' => 'invalid-email'
                ]);
                
                if (!empty($testValidation)) {
                    echo "<div class='info'>✅ Validation fonctionne : " . count($testValidation) . " erreur(s) détectée(s)</div>";
                }
                
            } catch (Exception $e) {
                echo "<div class='error'>❌ Erreur modèle Contact : " . $e->getMessage() . "</div>";
            }
            ?>
        </div>
        
        <div class="test-section">
            <h3>🔍 Test de recherche globale</h3>
            <?php
            try {
                $rechercheGlobale = $dossierModel->search('Entreprise');
                echo "<div class='success'>✅ Recherche globale 'Entreprise' : " . count($rechercheGlobale) . " dossier(s) trouvé(s)</div>";
                
                if (!empty($rechercheGlobale)) {
                    echo "<table>";
                    echo "<tr><th>Référence</th><th>Type de correspondance</th><th>Détail</th></tr>";
                    foreach ($rechercheGlobale as $resultat) {
                        echo "<tr>";
                        echo "<td>" . e($resultat['reference']) . "</td>";
                        echo "<td>" . e($resultat['match_type']) . "</td>";
                        echo "<td>" . e($resultat['match_detail']) . "</td>";
                        echo "</tr>";
                    }
                    echo "</table>";
                }
                
            } catch (Exception $e) {
                echo "<div class='error'>❌ Erreur recherche globale : " . $e->getMessage() . "</div>";
            }
            ?>
        </div>
        
        <div class="test-section">
            <h3>📊 Statistiques</h3>
            <?php
            try {
                $statsDossiers = $dossierModel->getStats();
                $statsTiers = $tiersModel->getStats();
                $statsContacts = $contactModel->getStats();
                
                echo "<div class='info'>";
                echo "<strong>Statistiques générales :</strong><br>";
                echo "• Dossiers : " . $statsDossiers['total_dossiers'] . " (dont " . $statsDossiers['dossiers_ce_mois'] . " ce mois)<br>";
                echo "• Tiers : " . $statsTiers['total_tiers'] . " (dont " . $statsTiers['tiers_sans_contacts'] . " sans contacts)<br>";
                echo "• Contacts : " . $statsContacts['total_contacts'] . " (dont " . $statsContacts['contacts_sans_tiers'] . " sans tiers)<br>";
                echo "</div>";
                
            } catch (Exception $e) {
                echo "<div class='error'>❌ Erreur statistiques : " . $e->getMessage() . "</div>";
            }
            ?>
        </div>
        
        <div style="margin-top: 30px; padding: 20px; background: #e9ecef; border-radius: 4px;">
            <strong>🎯 Résultat :</strong><br>
            <?php if (!isset($e)): ?>
                <span style="color: #28a745;">✅ Tous les modèles fonctionnent correctement !</span><br><br>
                <strong>📋 Prochaines étapes :</strong><br>
                1. Créer les contrôleurs PHP<br>
                2. Développer les vues HTML<br>
                3. Ajouter le JavaScript et CSS<br>
                <br>
                <a href="index.php" class="btn">🚀 Voir l'application</a>
                <a href="test_connection.php" class="btn">🔧 Test connexion</a>
            <?php else: ?>
                <span style="color: #dc3545;">❌ Des erreurs ont été détectées. Vérifiez les modèles.</span>
            <?php endif; ?>
        </div>
    </div>
</body>
</html>
