<?php
/**
 * Gestionnaire des services
 * Universe Security Admin Panel
 */

require_once __DIR__ . '/../config/database.php';

class ServiceManager {
    private $conn;
    private $table_name = "services";
    
    public function __construct() {
        $database = new Database();
        $this->conn = $database->getConnection();
    }
    
    /**
     * Récupérer tous les services
     */
    public function getAllServices($active_only = false) {
        $query = "SELECT s.*, a.full_name as created_by_name 
                  FROM " . $this->table_name . " s 
                  LEFT JOIN admins a ON s.created_by = a.id";
        
        if($active_only) {
            $query .= " WHERE s.is_active = 1";
        }
        
        $query .= " ORDER BY s.display_order ASC, s.created_at DESC";
        
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        
        return $stmt->fetchAll();
    }
    
    /**
     * Récupérer un service par ID
     */
    public function getServiceById($id) {
        $query = "SELECT s.*, a.full_name as created_by_name 
                  FROM " . $this->table_name . " s 
                  LEFT JOIN admins a ON s.created_by = a.id 
                  WHERE s.id = :id";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':id', $id);
        $stmt->execute();
        
        return $stmt->fetch();
    }
    
    /**
     * Créer un nouveau service
     */
    public function createService($data, $admin_id) {
        // Vérifier si les colonnes icon et display_order existent
        $query_check = "SHOW COLUMNS FROM " . $this->table_name . " LIKE 'icon'";
        $stmt_check = $this->conn->prepare($query_check);
        $stmt_check->execute();
        $has_icon = $stmt_check->rowCount() > 0;
        
        $query_check = "SHOW COLUMNS FROM " . $this->table_name . " LIKE 'display_order'";
        $stmt_check = $this->conn->prepare($query_check);
        $stmt_check->execute();
        $has_display_order = $stmt_check->rowCount() > 0;
        
        if ($has_icon && $has_display_order) {
            // Version complète avec toutes les colonnes
            $query = "INSERT INTO " . $this->table_name . " 
                      (title, description, icon, display_order, is_active, created_by) 
                      VALUES (:title, :description, :icon, :display_order, :is_active, :created_by)";
            
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(':title', $data['title']);
            $stmt->bindParam(':description', $data['description']);
            $stmt->bindValue(':icon', $data['icon'] ?? '');
            $stmt->bindValue(':display_order', $data['display_order'] ?? 0);
            $stmt->bindValue(':is_active', $data['is_active'] ?? 1);
            $stmt->bindParam(':created_by', $admin_id);
        } else {
            // Version de base sans les colonnes manquantes
            $query = "INSERT INTO " . $this->table_name . " 
                      (title, description, is_active, created_by) 
                      VALUES (:title, :description, :is_active, :created_by)";
            
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(':title', $data['title']);
            $stmt->bindParam(':description', $data['description']);
            $stmt->bindValue(':is_active', $data['is_active'] ?? 1);
            $stmt->bindParam(':created_by', $admin_id);
        }
        
        if($stmt->execute()) {
            $service_id = $this->conn->lastInsertId();
            $this->logActivity($admin_id, 'Création service', 'services', $service_id);
            return $service_id;
        }
        
        return false;
    }
    
    /**
     * Mettre à jour un service
     */
    public function updateService($id, $data, $admin_id) {
        // Vérifier si les colonnes icon et display_order existent
        $query_check = "SHOW COLUMNS FROM " . $this->table_name . " LIKE 'icon'";
        $stmt_check = $this->conn->prepare($query_check);
        $stmt_check->execute();
        $has_icon = $stmt_check->rowCount() > 0;
        
        $query_check = "SHOW COLUMNS FROM " . $this->table_name . " LIKE 'display_order'";
        $stmt_check = $this->conn->prepare($query_check);
        $stmt_check->execute();
        $has_display_order = $stmt_check->rowCount() > 0;
        
        if ($has_icon && $has_display_order) {
            // Version complète avec toutes les colonnes
            $query = "UPDATE " . $this->table_name . " 
                      SET title = :title, description = :description, icon = :icon, display_order = :display_order, is_active = :is_active, updated_at = CURRENT_TIMESTAMP 
                      WHERE id = :id";
            
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(':title', $data['title']);
            $stmt->bindParam(':description', $data['description']);
            $stmt->bindValue(':icon', $data['icon'] ?? '');
            $stmt->bindValue(':display_order', $data['display_order'] ?? 0);
            $stmt->bindValue(':is_active', $data['is_active'] ?? 1);
            $stmt->bindParam(':id', $id);
        } else {
            // Version de base sans les colonnes manquantes
            $query = "UPDATE " . $this->table_name . " 
                      SET title = :title, description = :description, is_active = :is_active, updated_at = CURRENT_TIMESTAMP 
                      WHERE id = :id";
            
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(':title', $data['title']);
            $stmt->bindParam(':description', $data['description']);
            $stmt->bindValue(':is_active', $data['is_active'] ?? 1);
            $stmt->bindParam(':id', $id);
        }
        
        if($stmt->execute()) {
            $this->logActivity($admin_id, 'Modification service', 'services', $id);
            return true;
        }
        
        return false;
    }
    
    /**
     * Supprimer un service
     */
    public function deleteService($id, $admin_id) {
        $query = "DELETE FROM " . $this->table_name . " WHERE id = :id";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':id', $id);
        
        if($stmt->execute()) {
            $this->logActivity($admin_id, 'Suppression service', 'services', $id);
            return true;
        }
        
        return false;
    }
    
    /**
     * Activer/Désactiver un service
     */
    public function toggleServiceStatus($id, $admin_id) {
        $query = "UPDATE " . $this->table_name . " 
                  SET is_active = NOT is_active, updated_at = CURRENT_TIMESTAMP 
                  WHERE id = :id";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':id', $id);
        
        if($stmt->execute()) {
            $this->logActivity($admin_id, 'Changement statut service', 'services', $id);
            return true;
        }
        
        return false;
    }
    
    /**
     * Réorganiser l'ordre des services
     */
    public function reorderServices($service_orders, $admin_id) {
        try {
            $this->conn->beginTransaction();
            
            foreach($service_orders as $id => $order) {
                $query = "UPDATE " . $this->table_name . " 
                          SET display_order = :order 
                          WHERE id = :id";
                
                $stmt = $this->conn->prepare($query);
                $stmt->bindParam(':order', $order);
                $stmt->bindParam(':id', $id);
                $stmt->execute();
            }
            
            $this->conn->commit();
            $this->logActivity($admin_id, 'Réorganisation services', 'services', null);
            return true;
            
        } catch(Exception $e) {
            $this->conn->rollBack();
            return false;
        }
    }
    
    /**
     * Rechercher des services
     */
    public function searchServices($search_term) {
        $query = "SELECT s.*, a.full_name as created_by_name 
                  FROM " . $this->table_name . " s 
                  LEFT JOIN admins a ON s.created_by = a.id 
                  WHERE s.title LIKE :search1 OR s.description LIKE :search2 
                  ORDER BY s.display_order ASC";
        
        $stmt = $this->conn->prepare($query);
        $search_param = '%' . $search_term . '%';
        $stmt->bindParam(':search1', $search_param);
        $stmt->bindParam(':search2', $search_param);
        $stmt->execute();
        
        return $stmt->fetchAll();
    }
    
    /**
     * Obtenir les statistiques des services
     */
    public function getServiceStats() {
        $stats = [];
        
        // Total des services
        $query = "SELECT COUNT(*) as total FROM " . $this->table_name;
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        $stats['total'] = $stmt->fetch()['total'];
        
        // Services actifs
        $query = "SELECT COUNT(*) as count FROM " . $this->table_name . " WHERE is_active = 1";
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        $stats['active'] = $stmt->fetch()['count'];
        
        // Services inactifs
        $stats['inactive'] = $stats['total'] - $stats['active'];
        
        return $stats;
    }
    
    /**
     * Enregistrer l'activité
     */
    private function logActivity($admin_id, $action, $table_name, $record_id) {
        $query = "INSERT INTO admin_logs (admin_id, action, table_name, record_id, ip_address, user_agent) 
                  VALUES (:admin_id, :action, :table_name, :record_id, :ip_address, :user_agent)";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':admin_id', $admin_id);
        $stmt->bindParam(':action', $action);
        $stmt->bindParam(':table_name', $table_name);
        $stmt->bindParam(':record_id', $record_id);
        $stmt->bindValue(':ip_address', $_SERVER['REMOTE_ADDR'] ?? '');
        $stmt->bindValue(':user_agent', $_SERVER['HTTP_USER_AGENT'] ?? '');
        $stmt->execute();
    }
}
?>
