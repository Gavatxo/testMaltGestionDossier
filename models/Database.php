<?php
if (!class_exists('Database')) {
    require_once __DIR__ . '/../config/database.php';
}
abstract class BaseModel {
    protected $db;
    protected $table = 'entites'; 
    
    public function __construct() {
        $database = new Database();
        $this->db = $database->getConnection();
    }
    

    protected function query($sql, $params = []) {
        try {
            $stmt = $this->db->prepare($sql);
            $stmt->execute($params);
            return $stmt;
        } catch (PDOException $e) {
            throw new Exception("Erreur de requête : " . $e->getMessage());
        }
    }
    

    protected function fetchAll($sql, $params = []) {
        $stmt = $this->query($sql, $params);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
 
    protected function fetch($sql, $params = []) {
        $stmt = $this->query($sql, $params);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }
    
  
    protected function insert($data) {
        $columns = implode(', ', array_keys($data));
        $placeholders = ':' . implode(', :', array_keys($data));
        
        $sql = "INSERT INTO {$this->table} ({$columns}) VALUES ({$placeholders})";
        $this->query($sql, $data);
        
        return $this->db->lastInsertId();
    }
    

    protected function update($id, $data) {
        $setParts = [];
        foreach (array_keys($data) as $column) {
            $setParts[] = "{$column} = :{$column}";
        }
        $setClause = implode(', ', $setParts);
        
        $sql = "UPDATE {$this->table} SET {$setClause} WHERE id = :id";
        $data['id'] = $id;
        
        return $this->query($sql, $data);
    }
    
    /**
     * Supprimer (soft delete)
     */
    protected function softDelete($id) {
        return $this->update($id, ['actif' => false]);
    }
    
    /**
     * Supprimer définitivement
     */
    protected function delete($id) {
        $sql = "DELETE FROM {$this->table} WHERE id = :id";
        return $this->query($sql, ['id' => $id]);
    }
    
    /**
     * Trouver par ID
     */
    protected function findById($id) {
        $sql = "SELECT * FROM {$this->table} WHERE id = :id AND actif = TRUE";
        return $this->fetch($sql, ['id' => $id]);
    }
    
    /**
     * Commencer une transaction
     */
    protected function beginTransaction() {
        return $this->db->beginTransaction();
    }
    
    /**
     * Valider une transaction
     */
    protected function commit() {
        return $this->db->commit();
    }
    
    /**
     * Annuler une transaction
     */
    protected function rollback() {
        return $this->db->rollback();
    }
    
    /**
     * Générer une référence unique pour les dossiers
     */
    protected function generateReference($prefix = 'DOS') {
        $year = date('Y');
        
        // Trouver le dernier numéro pour l'année en cours
        $sql = "SELECT reference FROM entites 
                WHERE type = 'dossier' 
                AND reference LIKE :pattern 
                ORDER BY reference DESC 
                LIMIT 1";
        
        $pattern = $prefix . '-' . $year . '-%';
        $result = $this->fetch($sql, ['pattern' => $pattern]);
        
        if ($result) {
            // Extraire le numéro de la référence existante
            $parts = explode('-', $result['reference']);
            $lastNumber = intval(end($parts));
            $nextNumber = $lastNumber + 1;
        } else {
            $nextNumber = 1;
        }
        
        // Format: DOS-2025-001
        return $prefix . '-' . $year . '-' . str_pad($nextNumber, 3, '0', STR_PAD_LEFT);
    }
    
    /**
     * Vérifier l'existence d'une relation
     */
    protected function relationExists($parentId, $childId, $type) {
        $sql = "SELECT COUNT(*) as count FROM relations 
                WHERE id_parent = :parent 
                AND id_enfant = :child 
                AND type_relation = :type 
                AND actif = TRUE";
        
        $result = $this->fetch($sql, [
            'parent' => $parentId,
            'child' => $childId,
            'type' => $type
        ]);
        
        return $result['count'] > 0;
    }
    
    /**
     * Créer une relation
     */
    protected function createRelation($parentId, $childId, $type) {
        // Vérifier que la relation n'existe pas déjà
        if ($this->relationExists($parentId, $childId, $type)) {
            return false; // Relation déjà existante
        }
        
        $sql = "INSERT INTO relations (id_parent, id_enfant, type_relation) 
                VALUES (:parent, :child, :type)";
        
        $this->query($sql, [
            'parent' => $parentId,
            'child' => $childId,
            'type' => $type
        ]);
        
        return true;
    }
    
    /**
     * Supprimer une relation
     */
    protected function deleteRelation($parentId, $childId, $type) {
        $sql = "UPDATE relations 
                SET actif = FALSE 
                WHERE id_parent = :parent 
                AND id_enfant = :child 
                AND type_relation = :type";
        
        return $this->query($sql, [
            'parent' => $parentId,
            'child' => $childId,
            'type' => $type
        ]);
    }
}
