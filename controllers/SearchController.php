<?php
require_once 'config/config.php';
require_once 'models/Dossier.php';
require_once 'models/Tiers.php';
require_once 'models/Contact.php';

class SearchController {
    
    private $dossierModel;
    private $tiersModel;
    private $contactModel;
    
    public function __construct() {
        $this->dossierModel = new Dossier();
        $this->tiersModel = new Tiers();
        $this->contactModel = new Contact();
    }
    
    /**
     * Recherche globale dans toutes les entités
     */
    public function global() {
        if (!isAjax()) {
            jsonResponse(['success' => false, 'message' => 'Requête AJAX requise'], 400);
            return;
        }
        
        try {
            $query = trim($_GET['q'] ?? '');
            
            if (empty($query)) {
                jsonResponse([
                    'success' => true,
                    'results' => [
                        'dossiers' => [],
                        'tiers' => [],
                        'contacts' => []
                    ],
                    'total' => 0
                ]);
                return;
            }
            
            $dossiers = $this->dossierModel->search($query);
            
            $tiers = $this->tiersModel->search($query);
            
            $contacts = $this->contactModel->search($query);
            
            $totalResults = count($dossiers) + count($tiers) + count($contacts);
            
            jsonResponse([
                'success' => true,
                'results' => [
                    'dossiers' => $dossiers,
                    'tiers' => $tiers,
                    'contacts' => $contacts
                ],
                'total' => $totalResults,
                'query' => $query
            ]);
            
        } catch (Exception $e) {
            jsonResponse(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }
    
    /**
     * Recherche avancée avec filtres
     */
    public function advanced() {
        if (!isAjax()) {
            jsonResponse(['success' => false, 'message' => 'Requête AJAX requise'], 400);
            return;
        }
        
        try {
            $query = trim($_GET['q'] ?? '');
            $type = $_GET['type'] ?? 'all'; // all, dossier, tiers, contact
            $limit = min((int)($_GET['limit'] ?? 10), 50); // Maximum 50 résultats
            
            if (empty($query)) {
                jsonResponse([
                    'success' => true,
                    'results' => [],
                    'total' => 0,
                    'type' => $type
                ]);
                return;
            }
            
            $results = [];
            
            switch ($type) {
                case 'dossier':
                    $results = $this->dossierModel->search($query);
                    break;
                    
                case 'tiers':
                    $results = $this->tiersModel->search($query);
                    break;
                    
                case 'contact':
                    $results = $this->contactModel->search($query);
                    break;
                    
                case 'all':
                default:
                    // Recherche dans tous les types
                    $dossiers = $this->dossierModel->search($query);
                    $tiers = $this->tiersModel->search($query);
                    $contacts = $this->contactModel->search($query);
                    
                    // Combiner et limiter les résultats
                    $allResults = [];
                    
                    // Ajouter les dossiers avec un type
                    foreach ($dossiers as $dossier) {
                        $dossier['search_type'] = 'dossier';
                        $allResults[] = $dossier;
                    }
                    
                    // Ajouter les tiers avec un type
                    foreach ($tiers as $tier) {
                        $tier['search_type'] = 'tiers';
                        $allResults[] = $tier;
                    }
                    
                    // Ajouter les contacts avec un type
                    foreach ($contacts as $contact) {
                        $contact['search_type'] = 'contact';
                        $allResults[] = $contact;
                    }
                    
                    // Trier par pertinence (ici par date de création décroissante)
                    usort($allResults, function($a, $b) {
                        return strtotime($b['date_creation']) - strtotime($a['date_creation']);
                    });
                    
                    $results = array_slice($allResults, 0, $limit);
                    break;
            }
            
            // Limiter les résultats si ce n'est pas déjà fait
            if ($type !== 'all') {
                $results = array_slice($results, 0, $limit);
            }
            
            jsonResponse([
                'success' => true,
                'results' => $results,
                'total' => count($results),
                'query' => $query,
                'type' => $type,
                'limit' => $limit
            ]);
            
        } catch (Exception $e) {
            jsonResponse(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }
    
    /**
     * Suggestions de recherche (autocomplétion)
     */
    public function suggestions() {
        if (!isAjax()) {
            jsonResponse(['success' => false, 'message' => 'Requête AJAX requise'], 400);
            return;
        }
        
        try {
            $query = trim($_GET['q'] ?? '');
            $limit = min((int)($_GET['limit'] ?? 5), 10);
            
            if (strlen($query) < 2) {
                jsonResponse([
                    'success' => true,
                    'suggestions' => []
                ]);
                return;
            }
            
            $suggestions = [];
            
            // Suggestions de tiers (dénominations)
            $tiers = $this->tiersModel->search($query);
            foreach (array_slice($tiers, 0, $limit) as $tier) {
                $suggestions[] = [
                    'type' => 'tiers',
                    'text' => $tier['denomination'],
                    'id' => $tier['id'],
                    'category' => 'Tiers'
                ];
            }
            
            // Suggestions de contacts (noms + emails)
            $contacts = $this->contactModel->search($query);
            foreach (array_slice($contacts, 0, $limit) as $contact) {
                $suggestions[] = [
                    'type' => 'contact',
                    'text' => $contact['prenom'] . ' ' . $contact['nom'],
                    'subtext' => $contact['email'],
                    'id' => $contact['id'],
                    'category' => 'Contacts'
                ];
            }
            
            // Limiter le total
            $suggestions = array_slice($suggestions, 0, $limit);
            
            jsonResponse([
                'success' => true,
                'suggestions' => $suggestions,
                'query' => $query
            ]);
            
        } catch (Exception $e) {
            jsonResponse(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }
    
    /**
     * Recherche récente (historique)
     */
    public function recent() {
        if (!isAjax()) {
            jsonResponse(['success' => false, 'message' => 'Requête AJAX requise'], 400);
            return;
        }
        
        try {
            // Récupérer les derniers éléments créés
            $recentDossiers = $this->dossierModel->getRecent(3);
            $recentTiers = $this->tiersModel->getRecent(3);
            $recentContacts = $this->contactModel->getRecent(3);
            
            jsonResponse([
                'success' => true,
                'recent' => [
                    'dossiers' => $recentDossiers,
                    'tiers' => $recentTiers,
                    'contacts' => $recentContacts
                ]
            ]);
            
        } catch (Exception $e) {
            jsonResponse(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }
    
    /**
     * Statistiques de recherche
     */
    public function stats() {
        try {
            $stats = [
                'total_dossiers' => 0,
                'total_tiers' => 0,
                'total_contacts' => 0,
                'total_relations' => 0
            ];
            
            // Compter les entités
            $dossierStats = $this->dossierModel->getStats();
            $tiersStats = $this->tiersModel->getStats();
            $contactStats = $this->contactModel->getStats();
            
            $stats['total_dossiers'] = $dossierStats['total_dossiers'] ?? 0;
            $stats['total_tiers'] = $tiersStats['total_tiers'] ?? 0;
            $stats['total_contacts'] = $contactStats['total_contacts'] ?? 0;
            
            jsonResponse([
                'success' => true,
                'stats' => $stats
            ]);
            
        } catch (Exception $e) {
            jsonResponse(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }
}