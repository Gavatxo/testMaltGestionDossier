<?php
// controllers/DossierController.php
require_once 'config/config.php';
require_once 'models/Dossier.php';
require_once 'models/Tiers.php';
require_once 'models/Contact.php';

class DossierController {
    
    private $dossierModel;
    private $tiersModel;
    private $contactModel;
    
    public function __construct() {
        $this->dossierModel = new Dossier();
        $this->tiersModel = new Tiers();
        $this->contactModel = new Contact();
    }
    
    /**
     * Afficher la liste des dossiers
     */
    public function index() {
        try {
            $dossiers = $this->dossierModel->getAll();
            $stats = $this->getGlobalStats();
            
            $this->renderView('dossiers/list', [
                'dossiers' => $dossiers,
                'stats' => $stats,
                'title' => 'Liste des dossiers'
            ]);
            
        } catch (Exception $e) {
            flash('error', 'Erreur lors du chargement des dossiers : ' . $e->getMessage());
            $this->renderView('dossiers/list', [
                'dossiers' => [],
                'stats' => [],
                'title' => 'Liste des dossiers'
            ]);
        }
    }
    
    /**
     * Afficher le détail d'un dossier
     */
    public function show($id) {
        try {
            $dossier = $this->dossierModel->getById($id);
            
            if (!$dossier) {
                flash('error', 'Dossier non trouvé');
                redirect('');
                return;
            }
            
            // Récupérer les tiers disponibles pour l'ajout
            $tiersDisponibles = $this->tiersModel->getAvailableForDossier($id);
            
            $this->renderView('dossiers/detail', [
                'dossier' => $dossier,
                'tiersDisponibles' => $tiersDisponibles,
                'title' => 'Dossier ' . $dossier['reference']
            ]);
            
        } catch (Exception $e) {
            flash('error', 'Erreur lors du chargement du dossier : ' . $e->getMessage());
            redirect('');
        }
    }
    
    /**
     * Afficher le formulaire de création
     */
    public function create() {
        try {
            // Récupérer tous les tiers pour association optionnelle
            $tiers = $this->tiersModel->getAll();
            
            $this->renderView('dossiers/create', [
                'tiers' => $tiers,
                'title' => 'Créer un nouveau dossier'
            ]);
            
        } catch (Exception $e) {
            flash('error', 'Erreur lors du chargement du formulaire : ' . $e->getMessage());
            redirect('');
        }
    }
    
    /**
     * Traiter la création d'un dossier
     */
    public function store() {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            redirect('dossier/create');
            return;
        }
        
        // Vérification CSRF
        if (!verify_csrf($_POST['csrf_token'] ?? '')) {
            flash('error', 'Token de sécurité invalide');
            redirect('dossier/create');
            return;
        }
        
        try {
            $data = [];
            
            // Récupérer les tiers sélectionnés (optionnel)
            if (!empty($_POST['tiers_ids']) && is_array($_POST['tiers_ids'])) {
                $data['tiers_ids'] = array_map('intval', $_POST['tiers_ids']);
            }
            
            // Créer le dossier
            $dossierId = $this->dossierModel->create($data);
            
            flash('success', 'Dossier créé avec succès');
            redirect('dossier/' . $dossierId);
            
        } catch (Exception $e) {
            flash('error', 'Erreur lors de la création : ' . $e->getMessage());
            flashInputs($_POST);
            redirect('dossier/create');
        }
    }
    
    /**
     * Ajouter un tiers à un dossier (AJAX)
     */
    public function addTiers() {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            jsonResponse(['success' => false, 'message' => 'Méthode non autorisée'], 405);
            return;
        }
        
        if (!verify_csrf($_POST['csrf_token'] ?? '')) {
            jsonResponse(['success' => false, 'message' => 'Token de sécurité invalide'], 403);
            return;
        }
        
        try {
            $dossierId = (int)($_POST['dossier_id'] ?? 0);
            $tiersId = (int)($_POST['tiers_id'] ?? 0);
            
            if (!$dossierId || !$tiersId) {
                jsonResponse(['success' => false, 'message' => 'Paramètres manquants']);
                return;
            }
            
            $success = $this->dossierModel->addTiers($dossierId, $tiersId);
            
            if ($success) {
                // Récupérer les infos du tiers ajouté
                $tiers = $this->tiersModel->getById($tiersId);
                jsonResponse([
                    'success' => true, 
                    'message' => 'Tiers ajouté avec succès',
                    'tiers' => $tiers
                ]);
            } else {
                jsonResponse(['success' => false, 'message' => 'Tiers déjà associé au dossier']);
            }
            
        } catch (Exception $e) {
            jsonResponse(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }
    
    /**
     * Supprimer un tiers d'un dossier (AJAX)
     */
    public function removeTiers() {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            jsonResponse(['success' => false, 'message' => 'Méthode non autorisée'], 405);
            return;
        }
        
        if (!verify_csrf($_POST['csrf_token'] ?? '')) {
            jsonResponse(['success' => false, 'message' => 'Token de sécurité invalide'], 403);
            return;
        }
        
        try {
            $dossierId = (int)($_POST['dossier_id'] ?? 0);
            $tiersId = (int)($_POST['tiers_id'] ?? 0);
            
            if (!$dossierId || !$tiersId) {
                jsonResponse(['success' => false, 'message' => 'Paramètres manquants']);
                return;
            }
            
            $this->dossierModel->removeTiers($dossierId, $tiersId);
            
            jsonResponse([
                'success' => true, 
                'message' => 'Tiers supprimé du dossier'
            ]);
            
        } catch (Exception $e) {
            jsonResponse(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }
    
    /**
     * Supprimer un dossier
     */
    public function delete($id) {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            redirect('');
            return;
        }
        
        if (!verify_csrf($_POST['csrf_token'] ?? '')) {
            flash('error', 'Token de sécurité invalide');
            redirect('');
            return;
        }
        
        try {
            $dossier = $this->dossierModel->getById($id);
            if (!$dossier) {
                flash('error', 'Dossier non trouvé');
                redirect('');
                return;
            }
            
            $this->dossierModel->delete($id);
            flash('success', 'Dossier "' . $dossier['reference'] . '" supprimé avec succès');
            redirect('');
            
        } catch (Exception $e) {
            flash('error', 'Erreur lors de la suppression : ' . $e->getMessage());
            redirect('dossier/' . $id);
        }
    }
    
    /**
     * Rechercher des dossiers (AJAX)
     */
    public function search() {
        if (!isAjax()) {
            redirect('');
            return;
        }
        
        try {
            $query = trim($_GET['q'] ?? '');
            
            if (empty($query)) {
                jsonResponse(['success' => true, 'results' => []]);
                return;
            }
            
            $results = $this->dossierModel->search($query);
            
            jsonResponse([
                'success' => true, 
                'results' => $results,
                'count' => count($results)
            ]);
            
        } catch (Exception $e) {
            jsonResponse(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }
    
    /**
     * API : Récupérer tous les dossiers (JSON)
     */
    public function api() {
        try {
            $dossiers = $this->dossierModel->getAll();
            
            jsonResponse([
                'success' => true,
                'data' => $dossiers,
                'count' => count($dossiers)
            ]);
            
        } catch (Exception $e) {
            jsonResponse(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }
    
    /**
     * Récupérer les statistiques globales
     */
    private function getGlobalStats() {
        try {
            $statsDossiers = $this->dossierModel->getStats();
            $statsTiers = $this->tiersModel->getStats();
            $statsContacts = $this->contactModel->getStats();
            
            return [
                'total_dossiers' => $statsDossiers['total_dossiers'] ?? 0,
                'dossiers_ce_mois' => $statsDossiers['dossiers_ce_mois'] ?? 0,
                'total_tiers' => $statsTiers['total_tiers'] ?? 0,
                'total_contacts' => $statsContacts['total_contacts'] ?? 0,
                'tiers_sans_contacts' => $statsTiers['tiers_sans_contacts'] ?? 0,
                'contacts_sans_tiers' => $statsContacts['contacts_sans_tiers'] ?? 0
            ];
            
        } catch (Exception $e) {
            return [];
        }
    }
    
    /**
     * Rendre une vue avec des données
     */
    private function renderView($view, $data = []) {
    extract($data);
    
    $data['view'] = $view;
    extract($data);
    
    include 'views/layouts/main.php';
}
}