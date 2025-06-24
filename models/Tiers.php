<?php
// models/Tiers.php
require_once 'Database.php';

class Tiers extends BaseModel {
    
    /**
     * Récupérer tous les tiers
     */
    public function getAll() {
        $sql = "SELECT 
                    t.id,
                    t.denomination,
                    t.date_creation,
                    t.date_modification,
                    COUNT(DISTINCT rc.id_enfant) as nb_contacts,
                    COUNT(DISTINCT rd.id_parent) as nb_dossiers
                FROM entites t
                LEFT JOIN relations rc ON (t.id = rc.id_parent AND rc.type_relation = 'tiers_contact' AND rc.actif = TRUE)
                LEFT JOIN relations rd ON (t.id = rd.id_enfant AND rd.type_relation = 'dossier_tiers' AND rd.actif = TRUE)
                WHERE t.type = 'tiers' AND t.actif = TRUE
                GROUP BY t.id, t.denomination, t.date_creation, t.date_modification
                ORDER BY t.denomination";
        
        return $this->fetchAll($sql);
    }
    
    /**
     * Récupérer un tiers par son ID avec ses détails
     */
    public function getById($id) {
        $sql = "SELECT * FROM entites WHERE id = :id AND type = 'tiers' AND actif = TRUE";
        $tiers = $this->fetch($sql, ['id' => $id]);
        
        if (!$tiers) {
            return null;
        }
        
        // Ajouter les contacts
        $tiers['contacts'] = $this->getContactsByTiersId($id);
        
        // Ajouter les dossiers associés
        $tiers['dossiers'] = $this->getDossiersByTiersId($id);
        
        return $tiers;
    }
    
    /**
     * Créer un nouveau tiers
     */
    public function create($denomination, $contactsIds = []) {
        try {
            $this->beginTransaction();
            
            // Vérifier que la dénomination n'est pas vide
            $denomination = trim($denomination);
            if (empty($denomination)) {
                throw new Exception("La dénomination du tiers est obligatoire");
            }
            
            // Données du tiers
            $tiersData = [
                'type' => 'tiers',
                'denomination' => $denomination,
                'date_creation' => date('Y-m-d H:i:s')
            ];
            
            // Insérer le tiers
            $tiersId = $this->insert($tiersData);
            
            // Associer les contacts existants si fournis
            if (!empty($contactsIds) && is_array($contactsIds)) {
                foreach ($contactsIds as $contactId) {
                    $this->addContact($tiersId, $contactId);
                }
            }
            
            $this->commit();
            return $tiersId;
            
        } catch (Exception $e) {
            $this->rollback();
            throw $e;
        }
    }
    
    /**
     * Mettre à jour un tiers
     */
    public function update($id, $denomination) {
        $denomination = trim($denomination);
        if (empty($denomination)) {
            throw new Exception("La dénomination du tiers est obligatoire");
        }
        
        $updateData = [
            'denomination' => $denomination,
            'date_modification' => date('Y-m-d H:i:s')
        ];
        
        return parent::update($id, $updateData);
    }
    
    /**
     * Supprimer un tiers (soft delete)
     */
    public function delete($id) {
        try {
            $this->beginTransaction();
            
            // Supprimer toutes les relations du tiers
            $sql = "UPDATE relations SET actif = FALSE 
                    WHERE (id_parent = :id AND type_relation = 'tiers_contact')
                    OR (id_enfant = :id AND type_relation = 'dossier_tiers')";
            $this->query($sql, ['id' => $id]);
            
            // Supprimer le tiers
            $this->softDelete($id);
            
            $this->commit();
            return true;
            
        } catch (Exception $e) {
            $this->rollback();
            throw $e;
        }
    }
    
    /**
     * Récupérer les contacts d'un tiers
     */
    public function getContactsByTiersId($tiersId) {
        $sql = "SELECT 
                    c.id as contact_id,
                    c.nom as contact_nom,
                    c.prenom as contact_prenom,
                    c.email as contact_email,
                    r.date_creation as date_association
                FROM entites c
                INNER JOIN relations r ON (c.id = r.id_enfant AND r.type_relation = 'tiers_contact' AND r.actif = TRUE)
                WHERE r.id_parent = :tiers_id 
                AND c.type = 'contact' 
                AND c.actif = TRUE
                ORDER BY c.nom, c.prenom";
        
        return $this->fetchAll($sql, ['tiers_id' => $tiersId]);
    }
    
    /**
     * Récupérer les dossiers associés à un tiers
     */
    public function getDossiersByTiersId($tiersId) {
        $sql = "SELECT 
                    d.id as dossier_id,
                    d.reference as dossier_reference,
                    d.date_creation as dossier_date_creation,
                    r.date_creation as date_association
                FROM entites d
                INNER JOIN relations r ON (d.id = r.id_parent AND r.type_relation = 'dossier_tiers' AND r.actif = TRUE)
                WHERE r.id_enfant = :tiers_id 
                AND d.type = 'dossier' 
                AND d.actif = TRUE
                ORDER BY d.date_creation DESC";
        
        return $this->fetchAll($sql, ['tiers_id' => $tiersId]);
    }
    
    /**
     * Ajouter un contact à un tiers
     */
    public function addContact($tiersId, $contactId) {
        // Vérifier que le tiers existe
        if (!$this->findById($tiersId)) {
            throw new Exception("Tiers non trouvé");
        }
        
        // Vérifier que le contact existe
        $sql = "SELECT id FROM entites WHERE id = :id AND type = 'contact' AND actif = TRUE";
        $contact = $this->fetch($sql, ['id' => $contactId]);
        if (!$contact) {
            throw new Exception("Contact non trouvé");
        }
        
        // Créer la relation
        return $this->createRelation($tiersId, $contactId, 'tiers_contact');
    }
    
    /**
     * Supprimer un contact d'un tiers
     */
    public function removeContact($tiersId, $contactId) {
        return $this->deleteRelation($tiersId, $contactId, 'tiers_contact');
    }
    
    /**
     * Rechercher des tiers par dénomination
     */
    public function search($query) {
        $searchTerm = '%' . $query . '%';
        
        $sql = "SELECT 
                    t.id,
                    t.denomination,
                    t.date_creation,
                    COUNT(DISTINCT rc.id_enfant) as nb_contacts
                FROM entites t
                LEFT JOIN relations rc ON (t.id = rc.id_parent AND rc.type_relation = 'tiers_contact' AND rc.actif = TRUE)
                WHERE t.type = 'tiers' 
                AND t.actif = TRUE
                AND t.denomination LIKE :search
                GROUP BY t.id, t.denomination, t.date_creation
                ORDER BY t.denomination";
        
        return $this->fetchAll($sql, ['search' => $searchTerm]);
    }
    
    /**
     * Récupérer les tiers disponibles (non associés à un dossier spécifique)
     */
    public function getAvailableForDossier($dossierId) {
        $sql = "SELECT t.id, t.denomination
                FROM entites t
                WHERE t.type = 'tiers' 
                AND t.actif = TRUE
                AND t.id NOT IN (
                    SELECT r.id_enfant 
                    FROM relations r 
                    WHERE r.id_parent = :dossier_id 
                    AND r.type_relation = 'dossier_tiers' 
                    AND r.actif = TRUE
                )
                ORDER BY t.denomination";
        
        return $this->fetchAll($sql, ['dossier_id' => $dossierId]);
    }
    
    /**
     * Vérifier si une dénomination existe déjà
     */
    public function denominationExists($denomination, $excludeId = null) {
        $sql = "SELECT COUNT(*) as count FROM entites 
                WHERE denomination = :denomination 
                AND type = 'tiers'
                AND actif = TRUE";
        
        $params = ['denomination' => $denomination];
        
        if ($excludeId) {
            $sql .= " AND id != :exclude_id";
            $params['exclude_id'] = $excludeId;
        }
        
        $result = $this->fetch($sql, $params);
        return $result['count'] > 0;
    }
    
    /**
     * Obtenir des statistiques sur les tiers
     */
    public function getStats() {
        $stats = [];
        
        // Nombre total de tiers
        $sql = "SELECT COUNT(*) as total FROM entites WHERE type = 'tiers' AND actif = TRUE";
        $result = $this->fetch($sql);
        $stats['total_tiers'] = $result['total'];
        
        // Tiers avec le plus de contacts
        $sql = "SELECT t.denomination, COUNT(rc.id_enfant) as nb_contacts
                FROM entites t
                LEFT JOIN relations rc ON (t.id = rc.id_parent AND rc.type_relation = 'tiers_contact' AND rc.actif = TRUE)
                WHERE t.type = 'tiers' AND t.actif = TRUE
                GROUP BY t.id, t.denomination
                ORDER BY nb_contacts DESC
                LIMIT 1";
        $result = $this->fetch($sql);
        $stats['tiers_plus_contacts'] = $result;
        
        // Tiers sans contacts
        $sql = "SELECT COUNT(*) as total
                FROM entites t
                LEFT JOIN relations rc ON (t.id = rc.id_parent AND rc.type_relation = 'tiers_contact' AND rc.actif = TRUE)
                WHERE t.type = 'tiers' 
                AND t.actif = TRUE
                AND rc.id_enfant IS NULL";
        $result = $this->fetch($sql);
        $stats['tiers_sans_contacts'] = $result['total'];
        
        return $stats;
    }
    
    /**
     * Récupérer les derniers tiers créés
     */
    public function getRecent($limit = 5) {
        $sql = "SELECT id, nom, prenom, email, date_creation 
                FROM entites 
                WHERE type = 'contact' AND actif = TRUE
                ORDER BY date_creation DESC 
                LIMIT " . (int)$limit;
        
        return $this->fetchAll($sql);
    }
}