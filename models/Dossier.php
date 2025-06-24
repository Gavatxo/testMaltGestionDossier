<?php
// models/Dossier.php
require_once 'Database.php';

class Dossier extends BaseModel {
    
    /**
     * Récupérer tous les dossiers avec leurs statistiques
     */
    public function getAll() {
        $sql = "SELECT 
                    d.id,
                    d.reference,
                    d.date_creation,
                    d.date_modification,
                    COUNT(DISTINCT rt.id_enfant) as nb_tiers,
                    COUNT(DISTINCT rc.id_enfant) as nb_contacts
                FROM entites d
                LEFT JOIN relations rt ON (d.id = rt.id_parent AND rt.type_relation = 'dossier_tiers' AND rt.actif = TRUE)
                LEFT JOIN relations rc ON (rt.id_enfant = rc.id_parent AND rc.type_relation = 'tiers_contact' AND rc.actif = TRUE)
                WHERE d.type = 'dossier' AND d.actif = TRUE
                GROUP BY d.id, d.reference, d.date_creation, d.date_modification
                ORDER BY d.date_creation DESC";
        
        return $this->fetchAll($sql);
    }
    
    /**
     * Récupérer un dossier par son ID avec ses détails complets
     */
    public function getById($id) {
        // Informations de base du dossier
        $sql = "SELECT * FROM entites WHERE id = :id AND type = 'dossier' AND actif = TRUE";
        $dossier = $this->fetch($sql, ['id' => $id]);
        
        if (!$dossier) {
            return null;
        }
        
        // Ajouter les tiers et leurs contacts
        $dossier['tiers'] = $this->getTiersByDossierId($id);
        
        return $dossier;
    }
    
    /**
     * Récupérer un dossier par sa référence
     */
    public function getByReference($reference) {
        $sql = "SELECT * FROM entites WHERE reference = :reference AND type = 'dossier' AND actif = TRUE";
        return $this->fetch($sql, ['reference' => $reference]);
    }
    
    /**
     * Créer un nouveau dossier
     */
    public function create($data = []) {
        try {
            $this->beginTransaction();
            
            // Générer une référence unique
            $reference = $this->generateReference();
            
            // Données du dossier
            $dossierData = [
                'type' => 'dossier',
                'reference' => $reference,
                'date_creation' => date('Y-m-d H:i:s')
            ];
            
            // Insérer le dossier
            $dossierId = $this->insert($dossierData);
            
            // Associer les tiers existants si fournis
            if (!empty($data['tiers_ids']) && is_array($data['tiers_ids'])) {
                foreach ($data['tiers_ids'] as $tiersId) {
                    $this->addTiers($dossierId, $tiersId);
                }
            }
            
            $this->commit();
            return $dossierId;
            
        } catch (Exception $e) {
            $this->rollback();
            throw $e;
        }
    }
    
    /**
     * Mettre à jour un dossier
     */
    public function update($id, $data) {
        $updateData = [
            'date_modification' => date('Y-m-d H:i:s')
        ];
        
        // On ne peut pas modifier la référence une fois créée
        // Ajouter d'autres champs modifiables si nécessaire
        
        return parent::update($id, $updateData);
    }
    
    /**
     * Supprimer un dossier (soft delete)
     */
    public function delete($id) {
        try {
            $this->beginTransaction();
            
            // Supprimer toutes les relations du dossier
            $sql = "UPDATE relations SET actif = FALSE 
                    WHERE id_parent = :id AND type_relation = 'dossier_tiers'";
            $this->query($sql, ['id' => $id]);
            
            // Supprimer le dossier
            $this->softDelete($id);
            
            $this->commit();
            return true;
            
        } catch (Exception $e) {
            $this->rollback();
            throw $e;
        }
    }
    
    /**
     * Récupérer les tiers d'un dossier avec leurs contacts
     */
    public function getTiersByDossierId($dossierId) {
        $sql = "SELECT 
                    t.id as tiers_id,
                    t.denomination as tiers_denomination,
                    r.date_creation as date_association
                FROM entites t
                INNER JOIN relations r ON (t.id = r.id_enfant AND r.type_relation = 'dossier_tiers' AND r.actif = TRUE)
                WHERE r.id_parent = :dossier_id 
                AND t.type = 'tiers' 
                AND t.actif = TRUE
                ORDER BY t.denomination";
        
        $tiers = $this->fetchAll($sql, ['dossier_id' => $dossierId]);
        
        // Pour chaque tiers, récupérer ses contacts
        foreach ($tiers as &$tier) {
            $tier['contacts'] = $this->getContactsByTiersId($tier['tiers_id']);
        }
        
        return $tiers;
    }
    
    /**
     * Récupérer les contacts d'un tiers
     */
    private function getContactsByTiersId($tiersId) {
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
     * Ajouter un tiers à un dossier
     */
    public function addTiers($dossierId, $tiersId) {
        // Vérifier que le dossier existe
        if (!$this->findById($dossierId)) {
            throw new Exception("Dossier non trouvé");
        }
        
        // Vérifier que le tiers existe
        $sql = "SELECT id FROM entites WHERE id = :id AND type = 'tiers' AND actif = TRUE";
        $tiers = $this->fetch($sql, ['id' => $tiersId]);
        if (!$tiers) {
            throw new Exception("Tiers non trouvé");
        }
        
        // Créer la relation
        return $this->createRelation($dossierId, $tiersId, 'dossier_tiers');
    }
    
    /**
     * Supprimer un tiers d'un dossier
     */
    public function removeTiers($dossierId, $tiersId) {
        return $this->deleteRelation($dossierId, $tiersId, 'dossier_tiers');
    }
    
    /**
     * Rechercher des dossiers par tiers ou contact
     */
    public function search($query) {
        $searchTerm = '%' . $query . '%';
        
        $sql = "SELECT DISTINCT
                    d.id,
                    d.reference,
                    d.date_creation,
                    'Trouvé via tiers' as match_type,
                    t.denomination as match_detail
                FROM entites d
                INNER JOIN relations rt ON (d.id = rt.id_parent AND rt.type_relation = 'dossier_tiers' AND rt.actif = TRUE)
                INNER JOIN entites t ON (rt.id_enfant = t.id AND t.type = 'tiers' AND t.actif = TRUE)
                WHERE d.type = 'dossier' 
                AND d.actif = TRUE
                AND t.denomination LIKE :search_tiers
                
                UNION
                
                SELECT DISTINCT
                    d.id,
                    d.reference,
                    d.date_creation,
                    'Trouvé via contact' as match_type,
                    CONCAT(c.nom, ' ', c.prenom, ' (', c.email, ')') as match_detail
                FROM entites d
                INNER JOIN relations rt ON (d.id = rt.id_parent AND rt.type_relation = 'dossier_tiers' AND rt.actif = TRUE)
                INNER JOIN relations rc ON (rt.id_enfant = rc.id_parent AND rc.type_relation = 'tiers_contact' AND rc.actif = TRUE)
                INNER JOIN entites c ON (rc.id_enfant = c.id AND c.type = 'contact' AND c.actif = TRUE)
                WHERE d.type = 'dossier' 
                AND d.actif = TRUE
                AND (c.nom LIKE :search_contact 
                     OR c.prenom LIKE :search_contact 
                     OR c.email LIKE :search_contact)
                
                ORDER BY reference DESC";
        
        return $this->fetchAll($sql, [
            'search_tiers' => $searchTerm,
            'search_contact' => $searchTerm
        ]);
    }
    
    /**
     * Obtenir des statistiques générales
     */
    public function getStats() {
        $stats = [];
        
        // Nombre total de dossiers
        $sql = "SELECT COUNT(*) as total FROM entites WHERE type = 'dossier' AND actif = TRUE";
        $result = $this->fetch($sql);
        $stats['total_dossiers'] = $result['total'];
        
        // Nombre de dossiers créés ce mois
        $sql = "SELECT COUNT(*) as total FROM entites 
                WHERE type = 'dossier' 
                AND actif = TRUE 
                AND MONTH(date_creation) = MONTH(CURRENT_DATE())
                AND YEAR(date_creation) = YEAR(CURRENT_DATE())";
        $result = $this->fetch($sql);
        $stats['dossiers_ce_mois'] = $result['total'];
        
        // Dossier avec le plus de tiers
        $sql = "SELECT d.reference, COUNT(rt.id_enfant) as nb_tiers
                FROM entites d
                LEFT JOIN relations rt ON (d.id = rt.id_parent AND rt.type_relation = 'dossier_tiers' AND rt.actif = TRUE)
                WHERE d.type = 'dossier' AND d.actif = TRUE
                GROUP BY d.id, d.reference
                ORDER BY nb_tiers DESC
                LIMIT 1";
        $result = $this->fetch($sql);
        $stats['dossier_plus_tiers'] = $result;
        
        return $stats;
    }
    
    /**
     * Vérifier si une référence existe déjà
     */
    public function referenceExists($reference) {
        $sql = "SELECT COUNT(*) as count FROM entites 
                WHERE reference = :reference AND type = 'dossier'";
        $result = $this->fetch($sql, ['reference' => $reference]);
        return $result['count'] > 0;
    }
    
    /**
     * Récupérer les derniers dossiers créés
     */
      public function getRecent($limit = 5) {
        $sql = "SELECT id, nom, prenom, email, date_creation 
                FROM entites 
                WHERE type = 'contact' AND actif = TRUE
                ORDER BY date_creation DESC 
                LIMIT " . (int)$limit;
        
        return $this->fetchAll($sql);
    }

    public function getNextReference() {
    return $this->generateReference();
}
}