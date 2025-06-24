<?php
// controllers/ContactController.php
require_once 'config/config.php';
require_once 'models/Contact.php';
require_once 'models/Tiers.php';

class ContactController {
    
    private $contactModel;
    private $tiersModel;
    
    public function __construct() {
        $this->contactModel = new Contact();
        $this->tiersModel = new Tiers();
    }
    
    /**
     * Créer un nouveau contact (AJAX)
     */
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
            $nom = trim($_POST['nom'] ?? '');
            $prenom = trim($_POST['prenom'] ?? '');
            $email = trim($_POST['email'] ?? '');
            
            // Validation
            $errors = Contact::validate(['nom' => $nom, 'prenom' => $prenom, 'email' => $email]);
            
            if (!empty($errors)) {
                jsonResponse([
                    'success' => false, 
                    'message' => 'Données invalides',
                    'errors' => $errors
                ], 400);
                return;
            }
            
            // Vérifier l'unicité de l'email
            if ($this->contactModel->emailExists($email)) {
                jsonResponse(['success' => false, 'message' => 'Cet email est déjà utilisé']);
                return;
            }
            
            // Créer le contact
            $contactId = $this->contactModel->create($nom, $prenom, $email);
            
            // Récupérer le contact créé
            $contact = $this->contactModel->getById($contactId);
            
            jsonResponse([
                'success' => true,
                'message' => 'Contact créé avec succès',
                'contact' => $contact
            ]);
            
        } catch (Exception $e) {
            jsonResponse(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }
    
    /**
     * Mettre à jour un contact (AJAX)
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
            $contactId = (int)($_POST['contact_id'] ?? 0);
            $nom = trim($_POST['nom'] ?? '');
            $prenom = trim($_POST['prenom'] ?? '');
            $email = trim($_POST['email'] ?? '');
            
            if (!$contactId) {
                jsonResponse(['success' => false, 'message' => 'ID du contact manquant']);
                return;
            }
            
            // Validation
            $errors = Contact::validate(['nom' => $nom, 'prenom' => $prenom, 'email' => $email]);
            
            if (!empty($errors)) {
                jsonResponse([
                    'success' => false, 
                    'message' => 'Données invalides',
                    'errors' => $errors
                ], 400);
                return;
            }
            
            // Vérifier que le contact existe
            $contact = $this->contactModel->getById($contactId);
            if (!$contact) {
                jsonResponse(['success' => false, 'message' => 'Contact non trouvé']);
                return;
            }
            
            // Vérifier l'unicité de l'email (exclure le contact actuel)
            if ($this->contactModel->emailExists($email, $contactId)) {
                jsonResponse(['success' => false, 'message' => 'Cet email est déjà utilisé']);
                return;
            }
            
            // Mettre à jour
            $this->contactModel->update($contactId, [
                'nom' => $nom,
                'prenom' => $prenom, 
                'email' => $email
            ]);
            
            // Récupérer le contact mis à jour
            $contactUpdated = $this->contactModel->getById($contactId);
            
            jsonResponse([
                'success' => true,
                'message' => 'Contact mis à jour avec succès',
                'contact' => $contactUpdated
            ]);
            
        } catch (Exception $e) {
            jsonResponse(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }
    
    /**
     * Supprimer un contact (AJAX)
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
            $contactId = (int)($_POST['contact_id'] ?? 0);
            
            if (!$contactId) {
                jsonResponse(['success' => false, 'message' => 'ID du contact manquant']);
                return;
            }
            
            // Vérifier que le contact existe
            $contact = $this->contactModel->getById($contactId);
            if (!$contact) {
                jsonResponse(['success' => false, 'message' => 'Contact non trouvé']);
                return;
            }
            
            // Supprimer le contact
            $this->contactModel->delete($contactId);
            
            jsonResponse([
                'success' => true,
                'message' => 'Contact "' . $contact['prenom'] . ' ' . $contact['nom'] . '" supprimé avec succès'
            ]);
            
        } catch (Exception $e) {
            jsonResponse(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }
    
    /**
     * Rechercher des contacts (AJAX)
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
            
            $results = $this->contactModel->search($query);
            
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
     * API : Récupérer tous les contacts (JSON)
     */
    public function api() {
        try {
            $contacts = $this->contactModel->getAll();
            
            jsonResponse([
                'success' => true,
                'data' => $contacts,
                'count' => count($contacts)
            ]);
            
        } catch (Exception $e) {
            jsonResponse(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }
    
    /**
     * API : Récupérer les contacts disponibles pour un tiers
     */
    public function available($tiersId) {
        try {
            $tiersId = (int)$tiersId;
            
            if (!$tiersId) {
                jsonResponse(['success' => false, 'message' => 'ID du tiers manquant'], 400);
                return;
            }
            
            $contacts = $this->contactModel->getAvailableForTiers($tiersId);
            
            jsonResponse([
                'success' => true,
                'data' => $contacts,
                'count' => count($contacts)
            ]);
            
        } catch (Exception $e) {
            jsonResponse(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }
    
    /**
     * Récupérer un contact avec ses détails (AJAX)
     */
    public function show($id) {
        try {
            $contactId = (int)$id;
            
            if (!$contactId) {
                jsonResponse(['success' => false, 'message' => 'ID du contact manquant'], 400);
                return;
            }
            
            $contact = $this->contactModel->getById($contactId);
            
            if (!$contact) {
                jsonResponse(['success' => false, 'message' => 'Contact non trouvé'], 404);
                return;
            }
            
            jsonResponse([
                'success' => true,
                'data' => $contact
            ]);
            
        } catch (Exception $e) {
            jsonResponse(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }
    
    /**
     * Valider un email en temps réel (AJAX)
     */
    public function validateEmail() {
        if (!isAjax()) {
            jsonResponse(['success' => false, 'message' => 'Requête AJAX requise'], 400);
            return;
        }
        
        try {
            $email = trim($_GET['email'] ?? '');
            $excludeId = (int)($_GET['exclude_id'] ?? 0);
            
            if (empty($email)) {
                jsonResponse(['success' => true, 'available' => false, 'message' => 'Email requis']);
                return;
            }
            
            // Validation du format
            if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
                jsonResponse(['success' => true, 'available' => false, 'message' => 'Format d\'email invalide']);
                return;
            }
            
            // Vérifier la disponibilité
            $exists = $this->contactModel->emailExists($email, $excludeId ?: null);
            
            jsonResponse([
                'success' => true,
                'available' => !$exists,
                'message' => $exists ? 'Cet email est déjà utilisé' : 'Email disponible'
            ]);
            
        } catch (Exception $e) {
            jsonResponse(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }
    
    /**
     * Obtenir les contacts par première lettre (AJAX)
     */
    public function byLetter($letter = 'A') {
        try {
            $letter = strtoupper(substr($letter, 0, 1));
            
            if (!preg_match('/[A-Z]/', $letter)) {
                $letter = 'A';
            }
            
            $contacts = $this->contactModel->getByFirstLetter($letter);
            
            jsonResponse([
                'success' => true,
                'data' => $contacts,
                'letter' => $letter,
                'count' => count($contacts)
            ]);
            
        } catch (Exception $e) {
            jsonResponse(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }
}