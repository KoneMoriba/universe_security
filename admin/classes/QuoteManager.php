<?php
/**
 * Gestionnaire des demandes de devis
 * Universe Security Admin Panel
 */

require_once __DIR__ . '/../config/database.php';

class QuoteManager {
    private $conn;
    private $table_name = "quote_requests";
    
    public function __construct() {
        $database = new Database();
        $this->conn = $database->getConnection();
    }
    
    /**
     * Récupérer toutes les demandes de devis
     */
    public function getAllQuotes($status = null, $limit = 50, $offset = 0) {
        $query = "SELECT qr.*, a.full_name as assigned_admin 
                  FROM " . $this->table_name . " qr 
                  LEFT JOIN admins a ON qr.assigned_to = a.id";
        
        if($status) {
            $query .= " WHERE qr.status = :status";
        }
        
        $query .= " ORDER BY qr.created_at DESC LIMIT :limit OFFSET :offset";
        
        $stmt = $this->conn->prepare($query);
        
        if($status) {
            $stmt->bindParam(':status', $status);
        }
        
        $stmt->bindParam(':limit', $limit, PDO::PARAM_INT);
        $stmt->bindParam(':offset', $offset, PDO::PARAM_INT);
        $stmt->execute();
        
        return $stmt->fetchAll();
    }
    
    /**
     * Récupérer une demande de devis par ID
     */
    public function getQuoteById($id) {
        $query = "SELECT qr.*, a.full_name as assigned_admin 
                  FROM " . $this->table_name . " qr 
                  LEFT JOIN admins a ON qr.assigned_to = a.id 
                  WHERE qr.id = :id";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':id', $id);
        $stmt->execute();
        
        return $stmt->fetch();
    }
    
    /**
     * Créer une nouvelle demande de devis
     */
    public function createQuote($data) {
        $query = "INSERT INTO " . $this->table_name . " 
                  (name, email, phone, service, message, status, priority) 
                  VALUES (:name, :email, :phone, :service, :message, :status, :priority)";
        
        $stmt = $this->conn->prepare($query);
        
        $stmt->bindParam(':name', $data['name']);
        $stmt->bindParam(':email', $data['email']);
        $stmt->bindParam(':phone', $data['phone']);
        $stmt->bindParam(':service', $data['service']);
        $stmt->bindParam(':message', $data['message']);
        $stmt->bindValue(':status', $data['status'] ?? 'nouveau');
        $stmt->bindValue(':priority', $data['priority'] ?? 'normale');
        
        return $stmt->execute();
    }
    
    /**
     * Mettre à jour une demande de devis
     */
    public function updateQuote($id, $data, $admin_id) {
        $query = "UPDATE " . $this->table_name . " 
                  SET status = :status, priority = :priority, admin_notes = :admin_notes, 
                      assigned_to = :assigned_to, updated_at = CURRENT_TIMESTAMP 
                  WHERE id = :id";
        
        $stmt = $this->conn->prepare($query);
        
        $stmt->bindParam(':id', $id);
        $stmt->bindParam(':status', $data['status']);
        $stmt->bindParam(':priority', $data['priority']);
        $stmt->bindParam(':admin_notes', $data['admin_notes']);
        $stmt->bindParam(':assigned_to', $data['assigned_to']);
        
        if($stmt->execute()) {
            $this->logActivity($admin_id, 'Mise à jour devis', 'quote_requests', $id);
            return true;
        }
        
        return false;
    }
    
    /**
     * Supprimer une demande de devis
     */
    public function deleteQuote($id, $admin_id) {
        $query = "DELETE FROM " . $this->table_name . " WHERE id = :id";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':id', $id);
        
        if($stmt->execute()) {
            $this->logActivity($admin_id, 'Suppression devis', 'quote_requests', $id);
            return true;
        }
        
        return false;
    }
    
    /**
     * Obtenir les statistiques des devis
     */
    public function getQuoteStats() {
        $stats = [];
        
        // Total des devis
        $query = "SELECT COUNT(*) as total FROM " . $this->table_name;
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        $stats['total'] = $stmt->fetch()['total'];
        
        // Devis par statut
        $query = "SELECT status, COUNT(*) as count FROM " . $this->table_name . " GROUP BY status";
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        $stats['by_status'] = $stmt->fetchAll();
        
        // Devis ce mois
        $query = "SELECT COUNT(*) as count FROM " . $this->table_name . " 
                  WHERE MONTH(created_at) = MONTH(CURRENT_DATE()) 
                  AND YEAR(created_at) = YEAR(CURRENT_DATE())";
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        $stats['this_month'] = $stmt->fetch()['count'];
        
        // Devis cette semaine
        $query = "SELECT COUNT(*) as count FROM " . $this->table_name . " 
                  WHERE WEEK(created_at) = WEEK(CURRENT_DATE()) 
                  AND YEAR(created_at) = YEAR(CURRENT_DATE())";
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        $stats['this_week'] = $stmt->fetch()['count'];
        
        return $stats;
    }
    
    /**
     * Rechercher des devis
     */
    public function searchQuotes($search_term, $status = null) {
        $query = "SELECT qr.*, a.full_name as assigned_admin 
                  FROM " . $this->table_name . " qr 
                  LEFT JOIN admins a ON qr.assigned_to = a.id 
                  WHERE (qr.name LIKE :search OR qr.email LIKE :search OR qr.service LIKE :search)";
        
        if($status) {
            $query .= " AND qr.status = :status";
        }
        
        $query .= " ORDER BY qr.created_at DESC";
        
        $stmt = $this->conn->prepare($query);
        $search_param = '%' . $search_term . '%';
        $stmt->bindParam(':search', $search_param);
        
        if($status) {
            $stmt->bindParam(':status', $status);
        }
        
        $stmt->execute();
        return $stmt->fetchAll();
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
