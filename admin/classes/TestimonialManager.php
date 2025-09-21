<?php
/**
 * Gestionnaire des témoignages
 * Universe Security Admin Panel
 */

require_once __DIR__ . '/../config/database.php';

class TestimonialManager {
    private $conn;
    private $table_name = "testimonials";
    
    public function __construct() {
        $database = new Database();
        $this->conn = $database->getConnection();
    }
    
    /**
     * Récupérer tous les témoignages
     */
    public function getAllTestimonials($approved_only = false) {
        $query = "SELECT t.*, a.full_name as approved_by_name 
                  FROM " . $this->table_name . " t 
                  LEFT JOIN admins a ON t.approved_by = a.id";
        
        if($approved_only) {
            $query .= " WHERE t.is_approved = 1";
        }
        
        $query .= " ORDER BY t.is_featured DESC, t.created_at DESC";
        
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        
        return $stmt->fetchAll();
    }
    
    /**
     * Récupérer un témoignage par ID
     */
    public function getTestimonialById($id) {
        $query = "SELECT t.*, a.full_name as approved_by_name 
                  FROM " . $this->table_name . " t 
                  LEFT JOIN admins a ON t.approved_by = a.id 
                  WHERE t.id = :id";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':id', $id);
        $stmt->execute();
        
        return $stmt->fetch();
    }
    
    /**
     * Créer un nouveau témoignage
     */
    public function createTestimonial($data) {
        $query = "INSERT INTO " . $this->table_name . " 
                  (client_name, client_position, client_company, content, rating, client_image, is_approved, is_featured) 
                  VALUES (:client_name, :client_position, :client_company, :content, :rating, :client_image, :is_approved, :is_featured)";
        
        $stmt = $this->conn->prepare($query);
        
        $stmt->bindParam(':client_name', $data['client_name']);
        $stmt->bindParam(':client_position', $data['client_position']);
        $stmt->bindParam(':client_company', $data['client_company']);
        $stmt->bindParam(':content', $data['content']);
        $stmt->bindValue(':rating', $data['rating'] ?? 5);
        $stmt->bindParam(':client_image', $data['client_image']);
        $stmt->bindValue(':is_approved', $data['is_approved'] ?? 0);
        $stmt->bindValue(':is_featured', $data['is_featured'] ?? 0);
        
        if($stmt->execute()) {
            return $this->conn->lastInsertId();
        }
        
        return false;
    }
    
    /**
     * Mettre à jour un témoignage
     */
    public function updateTestimonial($id, $data, $admin_id) {
        $query = "UPDATE " . $this->table_name . " 
                  SET client_name = :client_name, client_position = :client_position, 
                      client_company = :client_company, content = :content, rating = :rating, 
                      client_image = :client_image, is_approved = :is_approved, 
                      is_featured = :is_featured, updated_at = CURRENT_TIMESTAMP";
        
        // Si on approuve le témoignage, enregistrer qui l'a approuvé
        if($data['is_approved'] && !$this->isApproved($id)) {
            $query .= ", approved_by = :approved_by";
        }
        
        $query .= " WHERE id = :id";
        
        $stmt = $this->conn->prepare($query);
        
        $stmt->bindParam(':id', $id);
        $stmt->bindParam(':client_name', $data['client_name']);
        $stmt->bindParam(':client_position', $data['client_position']);
        $stmt->bindParam(':client_company', $data['client_company']);
        $stmt->bindParam(':content', $data['content']);
        $stmt->bindParam(':rating', $data['rating']);
        $stmt->bindParam(':client_image', $data['client_image']);
        $stmt->bindParam(':is_approved', $data['is_approved']);
        $stmt->bindParam(':is_featured', $data['is_featured']);
        
        if($data['is_approved'] && !$this->isApproved($id)) {
            $stmt->bindParam(':approved_by', $admin_id);
        }
        
        if($stmt->execute()) {
            $this->logActivity($admin_id, 'Mise à jour témoignage', 'testimonials', $id);
            return true;
        }
        
        return false;
    }
    
    /**
     * Supprimer un témoignage
     */
    public function deleteTestimonial($id, $admin_id) {
        $query = "DELETE FROM " . $this->table_name . " WHERE id = :id";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':id', $id);
        
        if($stmt->execute()) {
            $this->logActivity($admin_id, 'Suppression témoignage', 'testimonials', $id);
            return true;
        }
        
        return false;
    }
    
    /**
     * Approuver un témoignage
     */
    public function approveTestimonial($id, $admin_id) {
        $query = "UPDATE " . $this->table_name . " 
                  SET is_approved = 1, approved_by = :approved_by, updated_at = CURRENT_TIMESTAMP 
                  WHERE id = :id";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':id', $id);
        $stmt->bindParam(':approved_by', $admin_id);
        
        if($stmt->execute()) {
            $this->logActivity($admin_id, 'Approbation témoignage', 'testimonials', $id);
            return true;
        }
        
        return false;
    }
    
    /**
     * Mettre en vedette un témoignage
     */
    public function toggleFeatured($id, $admin_id) {
        $query = "UPDATE " . $this->table_name . " 
                  SET is_featured = NOT is_featured, updated_at = CURRENT_TIMESTAMP 
                  WHERE id = :id";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':id', $id);
        
        if($stmt->execute()) {
            $this->logActivity($admin_id, 'Changement vedette témoignage', 'testimonials', $id);
            return true;
        }
        
        return false;
    }
    
    /**
     * Rechercher des témoignages
     */
    public function searchTestimonials($search_term) {
        $query = "SELECT t.*, a.full_name as approved_by_name 
                  FROM " . $this->table_name . " t 
                  LEFT JOIN admins a ON t.approved_by = a.id 
                  WHERE t.client_name LIKE :search OR t.client_company LIKE :search OR t.content LIKE :search 
                  ORDER BY t.is_featured DESC, t.created_at DESC";
        
        $stmt = $this->conn->prepare($query);
        $search_param = '%' . $search_term . '%';
        $stmt->bindParam(':search', $search_param);
        $stmt->execute();
        
        return $stmt->fetchAll();
    }
    
    /**
     * Obtenir les statistiques des témoignages
     */
    public function getTestimonialStats() {
        $stats = [];
        
        // Total des témoignages
        $query = "SELECT COUNT(*) as total FROM " . $this->table_name;
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        $stats['total'] = $stmt->fetch()['total'];
        
        // Témoignages approuvés
        $query = "SELECT COUNT(*) as count FROM " . $this->table_name . " WHERE is_approved = 1";
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        $stats['approved'] = $stmt->fetch()['count'];
        
        // Témoignages en attente
        $query = "SELECT COUNT(*) as count FROM " . $this->table_name . " WHERE is_approved = 0";
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        $stats['pending'] = $stmt->fetch()['count'];
        
        // Témoignages en vedette
        $query = "SELECT COUNT(*) as count FROM " . $this->table_name . " WHERE is_featured = 1";
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        $stats['featured'] = $stmt->fetch()['count'];
        
        // Note moyenne
        $query = "SELECT AVG(rating) as avg_rating FROM " . $this->table_name . " WHERE is_approved = 1";
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        $stats['avg_rating'] = round($stmt->fetch()['avg_rating'] ?? 0, 1);
        
        return $stats;
    }
    
    /**
     * Vérifier si un témoignage est approuvé
     */
    private function isApproved($id) {
        $query = "SELECT is_approved FROM " . $this->table_name . " WHERE id = :id";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':id', $id);
        $stmt->execute();
        
        $result = $stmt->fetch();
        return $result ? (bool)$result['is_approved'] : false;
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
