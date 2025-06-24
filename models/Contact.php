<?php
require_once 'Database.php';

class Contact extends BaseModel {
    

    public function getAll() {
        $sql = "SELECT 
                    c.id,
                    c.nom,
                    c.prenom,
                    c.email,
                    c.date_creation,
                    c.date_modification,
                    COUNT(DISTINCT rt.id_parent) as nb_tiers
                FROM entites c
                LEFT JOIN relations rt ON (c.id = rt.id_enfant AND rt.type_relation = 'tiers_contact' AND rt.actif = TRUE)
                WHERE c.type = 'contact' AND c.actif = TRUE
                GROUP BY c.id, c.nom, c.prenom, c.email, c.date_creation, c.date_modification
                ORDER BY c.nom, c.prenom";
        
        return $this->fetchAll($sql);
    }
    

    public function getById($id) {
        $sql = "SELECT * FROM entites WHERE id = :id AND type = 'contact' AND actif = TRUE";
        $contact = $this->fetch($sql, ['id' => $id]);
        
        if (!$contact) {
            return null;
        }
        
        $contact['tiers'] = $this->getTiersByContactId($id);
        
        return $contact;
    }
    

    public function getByEmail($email) {
        $sql = "SELECT * FROM entites WHERE email = :email AND type = 'contact' AND actif = TRUE";
        return $this->fetch($sql, ['email' => $email]);
    }
    

    public function create($nom, $prenom, $email, $tiersIds = []) {
        try {
            $this->beginTransaction();
            
            // Validation des données
            $nom = trim($nom);
            $prenom = trim($prenom);
            $email = trim($email);
            
            if (empty($nom)) {
                throw new Exception("Le nom du contact est obligatoire");
            }
            
            if (empty($prenom)) {
                throw new Exception("Le prénom du contact est obligatoire");
            }
            
            if (empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
                throw new Exception("Un email valide est obligatoire");
            }
            
            // Vérifier l'unicité de l'email
            if ($this->emailExists($email)) {
                throw new Exception("Cet email est déjà utilisé par un autre contact");
            }
            
            // Données du contact
            $contactData = [
                'type' => 'contact',
                'nom' => $nom,
                'prenom' => $prenom,
                'email' => $email,
                'date_creation' => date('Y-m-d H:i:s')
            ];
            
            // Insérer le contact
            $contactId = $this->insert($contactData);
            
            // Associer aux tiers existants si fournis
            if (!empty($tiersIds) && is_array($tiersIds)) {
                foreach ($tiersIds as $tiersId) {
                    $this->addToTiers($contactId, $tiersId);
                }
            }
            
            $this->commit();
            return $contactId;
            
        } catch (Exception $e) {
            $this->rollback();
            throw $e;
        }
    }
    
    /**
     * Mettre à jour un contact
     */
      public function update($id, $data) {
     
      $nom = $data['nom'] ?? '';
      $prenom = $data['prenom'] ?? '';
      $email = $data['email'] ?? '';
        
        if (empty($nom)) {
            throw new Exception("Le nom du contact est obligatoire");
        }
        
        if (empty($prenom)) {
            throw new Exception("Le prénom du contact est obligatoire");
        }
        
        if (empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
            throw new Exception("Un email valide est obligatoire");
        }
        
        // Vérifier l'unicité de l'email (exclure le contact actuel)
        if ($this->emailExists($email, $id)) {
            throw new Exception("Cet email est déjà utilisé par un autre contact");
        }
        
        $updateData = [
            'nom' => $nom,
            'prenom' => $prenom,
            'email' => $email,
            'date_modification' => date('Y-m-d H:i:s')
        ];
        
        return parent::update($id, $updateData);
    }
    
    /**
     * Supprimer un contact (soft delete)
     */
    public function delete($id) {
        try {
            $this->beginTransaction();
            
            // Supprimer toutes les relations du contact
            $sql = "UPDATE relations SET actif = FALSE 
                    WHERE id_enfant = :id AND type_relation = 'tiers_contact'";
            $this->query($sql, ['id' => $id]);
            
            // Supprimer le contact
            $this->softDelete($id);
            
            $this->commit();
            return true;
            
        } catch (Exception $e) {
            $this->rollback();
            throw $e;
        }
    }
    
    /**
     * Récupérer les tiers associés à un contact
     */
    public function getTiersByContactId($contactId) {
        $sql = "SELECT 
                    t.id as tiers_id,
                    t.denomination as tiers_denomination,
                    r.date_creation as date_association
                FROM entites t
                INNER JOIN relations r ON (t.id = r.id_parent AND r.type_relation = 'tiers_contact' AND r.actif = TRUE)
                WHERE r.id_enfant = :contact_id 
                AND t.type = 'tiers' 
                AND t.actif = TRUE
                ORDER BY t.denomination";
        
        return $this->fetchAll($sql, ['contact_id' => $contactId]);
    }
    
    /**
     * Ajouter un contact à un tiers
     */
    public function addToTiers($contactId, $tiersId) {
        // Vérifier que le contact existe
        if (!$this->findById($contactId)) {
            throw new Exception("Contact non trouvé");
        }
        
        // Vérifier que le tiers existe
        $sql = "SELECT id FROM entites WHERE id = :id AND type = 'tiers' AND actif = TRUE";
        $tiers = $this->fetch($sql, ['id' => $tiersId]);
        if (!$tiers) {
            throw new Exception("Tiers non trouvé");
        }
        
        // Créer la relation (le parent est le tiers, l'enfant est le contact)
        return $this->createRelation($tiersId, $contactId, 'tiers_contact');
    }
    
    /**
     * Supprimer un contact d'un tiers
     */
    public function removeFromTiers($contactId, $tiersId) {
        return $this->deleteRelation($tiersId, $contactId, 'tiers_contact');
    }
    
    /**
     * Rechercher des contacts par nom, prénom ou email
     */
    public function search($query) {
        $searchTerm = '%' . $query . '%';
        
        $sql = "SELECT 
                    c.id,
                    c.nom,
                    c.prenom,
                    c.email,
                    c.date_creation,
                    COUNT(DISTINCT rt.id_parent) as nb_tiers
                FROM entites c
                LEFT JOIN relations rt ON (c.id = rt.id_enfant AND rt.type_relation = 'tiers_contact' AND rt.actif = TRUE)
                WHERE c.type = 'contact' 
                AND c.actif = TRUE
                AND (c.nom LIKE :search 
                     OR c.prenom LIKE :search 
                     OR c.email LIKE :search)
                GROUP BY c.id, c.nom, c.prenom, c.email, c.date_creation
                ORDER BY c.nom, c.prenom";
        
        return $this->fetchAll($sql, ['search' => $searchTerm]);
    }
    
    /**
     * Récupérer les contacts disponibles (non associés à un tiers spécifique)
     */
    public function getAvailableForTiers($tiersId) {
        $sql = "SELECT c.id, c.nom, c.prenom, c.email
                FROM entites c
                WHERE c.type = 'contact' 
                AND c.actif = TRUE
                AND c.id NOT IN (
                    SELECT r.id_enfant 
                    FROM relations r 
                    WHERE r.id_parent = :tiers_id 
                    AND r.type_relation = 'tiers_contact' 
                    AND r.actif = TRUE
                )
                ORDER BY c.nom, c.prenom";
        
        return $this->fetchAll($sql, ['tiers_id' => $tiersId]);
    }
    
    /**
     * Vérifier si un email existe déjà
     */
    public function emailExists($email, $excludeId = null) {
        $sql = "SELECT COUNT(*) as count FROM entites 
                WHERE email = :email 
                AND type = 'contact'
                AND actif = TRUE";
        
        $params = ['email' => $email];
        
        if ($excludeId) {
            $sql .= " AND id != :exclude_id";
            $params['exclude_id'] = $excludeId;
        }
        
        $result = $this->fetch($sql, $params);
        return $result['count'] > 0;
    }
    
    /**
     * Obtenir des statistiques sur les contacts
     */
    public function getStats() {
        $stats = [];
        
        // Nombre total de contacts
        $sql = "SELECT COUNT(*) as total FROM entites WHERE type = 'contact' AND actif = TRUE";
        $result = $this->fetch($sql);
        $stats['total_contacts'] = $result['total'];
        
        // Contacts sans tiers associé
        $sql = "SELECT COUNT(*) as total
                FROM entites c
                LEFT JOIN relations rt ON (c.id = rt.id_enfant AND rt.type_relation = 'tiers_contact' AND rt.actif = TRUE)
                WHERE c.type = 'contact' 
                AND c.actif = TRUE
                AND rt.id_parent IS NULL";
        $result = $this->fetch($sql);
        $stats['contacts_sans_tiers'] = $result['total'];
        
        // Domaines d'email les plus fréquents
        $sql = "SELECT 
                    SUBSTRING_INDEX(email, '@', -1) as domaine,
                    COUNT(*) as nb_contacts
                FROM entites 
                WHERE type = 'contact' 
                AND actif = TRUE
                GROUP BY domaine
                ORDER BY nb_contacts DESC
                LIMIT 5";
        $result = $this->fetchAll($sql);
        $stats['domaines_frequents'] = $result;
        
        return $stats;
    }
    
    /**
     * Récupérer les derniers contacts créés
     */
     public function getRecent($limit = 5) {
        $sql = "SELECT id, nom, prenom, email, date_creation 
                FROM entites 
                WHERE type = 'contact' AND actif = TRUE
                ORDER BY date_creation DESC 
                LIMIT " . (int)$limit;
        
        return $this->fetchAll($sql);
    }
    
    /**
     * Valider les données d'un contact
     */
    public static function validate($data) {
        $errors = [];
        
        // Validation du nom
        if (empty(trim($data['nom'] ?? ''))) {
            $errors['nom'] = 'Le nom est obligatoire';
        } elseif (strlen(trim($data['nom'])) < 2) {
            $errors['nom'] = 'Le nom doit contenir au moins 2 caractères';
        }
        
        // Validation du prénom
        if (empty(trim($data['prenom'] ?? ''))) {
            $errors['prenom'] = 'Le prénom est obligatoire';
        } elseif (strlen(trim($data['prenom'])) < 2) {
            $errors['prenom'] = 'Le prénom doit contenir au moins 2 caractères';
        }
        
        // Validation de l'email
        if (empty(trim($data['email'] ?? ''))) {
            $errors['email'] = 'L\'email est obligatoire';
        } elseif (!filter_var(trim($data['email']), FILTER_VALIDATE_EMAIL)) {
            $errors['email'] = 'L\'email n\'est pas valide';
        }
        
        return $errors;
    }
    
    /**
     * Formater le nom complet d'un contact
     */
    public static function formatFullName($contact) {
        if (is_array($contact)) {
            return trim(($contact['prenom'] ?? '') . ' ' . ($contact['nom'] ?? ''));
        }
        return '';
    }
    
    /**
     * Obtenir les contacts par première lettre du nom
     */
    public function getByFirstLetter($letter = 'A') {
        $sql = "SELECT id, nom, prenom, email
                FROM entites 
                WHERE type = 'contact' 
                AND actif = TRUE
                AND UPPER(LEFT(nom, 1)) = :letter
                ORDER BY nom, prenom";
        
        return $this->fetchAll($sql, ['letter' => strtoupper($letter)]);
    }
}
