<?php
require_once 'config/config.php';
require_once 'models/Tiers.php';
require_once 'models/Contact.php';

class TierController {
    
    private $tiersModel;
    private $contactModel;
    
    public function __construct() {
        $this->tiersModel = new Tiers();
        $this->contactModel = new Contact();
    }
    
    
    public function create() {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            jsonResponse(['success' => false, 'message' => 'Méthode non autorisée'], 405);
            return;
        }
        
        if (!verify_csrf($_POST['csrf_token'] ?? '')) {
            jsonResponse(['success' => false, 'message' => 'Token de sécurité invalide'], 403);
            return;
        }
        
        try {
            $denomination = trim($_POST['denomination'] ?? '');
            
            if (empty($denomination)) {
                jsonResponse(['success' => false, 'message' => 'La dénomination est obligatoire']);
                return;
            }
            
            // Vérifier l'unicité
            if ($this->tiersModel->denominationExists($denomination)) {
                jsonResponse(['success' => false, 'message' => 'Cette dénomination existe déjà']);
                return;
            }
            
            // Créer le tiers
            $tiersId = $this->tiersModel->create($denomination);
            
            // Récupérer le tiers créé
            $tiers = $this->tiersModel->getById($tiersId);
            
            jsonResponse([
                'success' => true,
                'message' => 'Tiers créé avec succès',
                'tiers' => $tiers
            ]);
            
        } catch (Exception $e) {
            jsonResponse(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }
    
    /**
     * Mettre à jour un tiers (AJAX)
     */
    public function update() {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            jsonResponse(['success' => false, 'message' => 'Méthode non autorisée'], 405);
            return;
        }
        
        if (!verify_csrf($_POST['csrf_token'] ?? '')) {
            jsonResponse(['success' => false, 'message' => 'Token de sécurité invalide'], 403);
            return;
        }
        
        try {
            $tiersId = (int)($_POST['tiers_id'] ?? 0);
            $denomination = trim($_POST['denomination'] ?? '');
            
            if (!$tiersId || empty($denomination)) {
                jsonResponse(['success' => false, 'message' => 'Paramètres manquants']);
                return;
            }
            
            // Vérifier que le tiers existe
            $tiers = $this->tiersModel->getById($tiersId);
            if (!$tiers) {
                jsonResponse(['success' => false, 'message' => 'Tiers non trouvé']);
                return;
            }
            
            // Vérifier l'unicité (exclure le tiers actuel)
            if ($this->tiersModel->denominationExists($denomination, $tiersId)) {
                jsonResponse(['success' => false, 'message' => 'Cette dénomination existe déjà']);
                return;
            }
            
            // Mettre à jour
            $this->tiersModel->update($tiersId, $denomination);
            
            // Récupérer le tiers mis à jour
            $tiersUpdated = $this->tiersModel->getById($tiersId);
            
            jsonResponse([
                'success' => true,
                'message' => 'Tiers mis à jour avec succès',
                'tiers' => $tiersUpdated
            ]);
            
        } catch (Exception $e) {
            jsonResponse(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }
    
    /**
     * Ajouter un contact à un tiers (AJAX)
     */
    public function addContact() {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            jsonResponse(['success' => false, 'message' => 'Méthode non autorisée'], 405);
            return;
        }
        
        if (!verify_csrf($_POST['csrf_token'] ?? '')) {
            jsonResponse(['success' => false, 'message' => 'Token de sécurité invalide'], 403);
            return;
        }
        
        try {
            $tiersId = (int)($_POST['tiers_id'] ?? 0);
            $contactId = (int)($_POST['contact_id'] ?? 0);
            
            if (!$tiersId || !$contactId) {
                jsonResponse(['success' => false, 'message' => 'Paramètres manquants']);
                return;
            }
            
            $success = $this->tiersModel->addContact($tiersId, $contactId);
            
            if ($success) {
                // Récupérer les infos du contact ajouté
                $contact = $this->contactModel->getById($contactId);
                jsonResponse([
                    'success' => true,
                    'message' => 'Contact ajouté avec succès',
                    'contact' => $contact
                ]);
            } else {
                jsonResponse(['success' => false, 'message' => 'Contact déjà associé au tiers']);
            }
            
        } catch (Exception $e) {
            jsonResponse(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }
    
    /**
     * Supprimer un contact d'un tiers (AJAX)
     */
    public function removeContact() {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            jsonResponse(['success' => false, 'message' => 'Méthode non autorisée'], 405);
            return;
        }
        
        if (!verify_csrf($_POST['csrf_token'] ?? '')) {
            jsonResponse(['success' => false, 'message' => 'Token de sécurité invalide'], 403);
            return;
        }
        
        try {
            $tiersId = (int)($_POST['tiers_id'] ?? 0);
            $contactId = (int)($_POST['contact_id'] ?? 0);
            
            if (!$tiersId || !$contactId) {
                jsonResponse(['success' => false, 'message' => 'Paramètres manquants']);
                return;
            }
            
            $this->tiersModel->removeContact($tiersId, $contactId);
            
            jsonResponse([
                'success' => true,
                'message' => 'Contact supprimé du tiers'
            ]);
            
        } catch (Exception $e) {
            jsonResponse(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }
    
    /**
     * Supprimer un tiers (AJAX)
     */
    public function delete() {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            jsonResponse(['success' => false, 'message' => 'Méthode non autorisée'], 405);
            return;
        }
        
        if (!verify_csrf($_POST['csrf_token'] ?? '')) {
            jsonResponse(['success' => false, 'message' => 'Token de sécurité invalide'], 403);
            return;
        }
        
        try {
            $tiersId = (int)($_POST['tiers_id'] ?? 0);
            
            if (!$tiersId) {
                jsonResponse(['success' => false, 'message' => 'ID du tiers manquant']);
                return;
            }
            
            // Vérifier que le tiers existe
            $tiers = $this->tiersModel->getById($tiersId);
            if (!$tiers) {
                jsonResponse(['success' => false, 'message' => 'Tiers non trouvé']);
                return;
            }
            
            // Supprimer le tiers
            $this->tiersModel->delete($tiersId);
            
            jsonResponse([
                'success' => true,
                'message' => 'Tiers "' . $tiers['denomination'] . '" supprimé avec succès'
            ]);
            
        } catch (Exception $e) {
            jsonResponse(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }
    
    /**
     * Rechercher des tiers (AJAX)
     */
    public function search() {
        if (!isAjax()) {
            jsonResponse(['success' => false, 'message' => 'Requête AJAX requise'], 400);
            return;
        }
        
        try {
            $query = trim($_GET['q'] ?? '');
            
            if (empty($query)) {
                jsonResponse(['success' => true, 'results' => []]);
                return;
            }
            
            $results = $this->tiersModel->search($query);
            
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
     * API : Récupérer tous les tiers (JSON)
     */
    public function api() {
        try {
            $tiers = $this->tiersModel->getAll();
            
            jsonResponse([
                'success' => true,
                'data' => $tiers,
                'count' => count($tiers)
            ]);
            
        } catch (Exception $e) {
            jsonResponse(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }
    
    /**
     * API : Récupérer les tiers disponibles pour un dossier
     */
    public function available($dossierId) {
        try {
            $dossierId = (int)$dossierId;
            
            if (!$dossierId) {
                jsonResponse(['success' => false, 'message' => 'ID du dossier manquant'], 400);
                return;
            }
            
            $tiers = $this->tiersModel->getAvailableForDossier($dossierId);
            
            jsonResponse([
                'success' => true,
                'data' => $tiers,
                'count' => count($tiers)
            ]);
            
        } catch (Exception $e) {
            jsonResponse(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }
    
    /**
     * Récupérer un tiers avec ses détails (AJAX)
     */
    public function show($id) {
        try {
            $tiersId = (int)$id;
            
            if (!$tiersId) {
                jsonResponse(['success' => false, 'message' => 'ID du tiers manquant'], 400);
                return;
            }
            
            $tiers = $this->tiersModel->getById($tiersId);
            
            if (!$tiers) {
                jsonResponse(['success' => false, 'message' => 'Tiers non trouvé'], 404);
                return;
            }
            
            jsonResponse([
                'success' => true,
                'data' => $tiers
            ]);
            
        } catch (Exception $e) {
            jsonResponse(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }
}
