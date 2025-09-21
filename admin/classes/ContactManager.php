<?php
/**
 * Gestionnaire des messages de contact
 * Universe Security Admin Panel
 */

class ContactManager {
    private $conn;
    
    public function __construct($database = null) {
        if ($database) {
            $this->conn = $database;
        }
    }
    
    /**
     * Créer un nouveau message de contact
     */
    public function createMessage($data) {
        try {
            $query = "INSERT INTO contact_messages (name, email, subject, message, phone, company) 
                     VALUES (:name, :email, :subject, :message, :phone, :company)";
            
            $stmt = $this->conn->prepare($query);
            
            return $stmt->execute([
                ':name' => $data['name'],
                ':email' => $data['email'],
                ':subject' => $data['subject'],
                ':message' => $data['message'],
                ':phone' => $data['phone'] ?? null,
                ':company' => $data['company'] ?? null
            ]);
            
        } catch(Exception $e) {
            error_log("Erreur création message contact: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Récupérer tous les messages
     */
    public function getAllMessages($limit = null, $offset = 0) {
        try {
            $query = "SELECT * FROM contact_messages ORDER BY created_at DESC";
            
            if ($limit) {
                $query .= " LIMIT :limit OFFSET :offset";
            }
            
            $stmt = $this->conn->prepare($query);
            
            if ($limit) {
                $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
                $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
            }
            
            $stmt->execute();
            return $stmt->fetchAll();
            
        } catch(Exception $e) {
            error_log("Erreur récupération messages: " . $e->getMessage());
            return [];
        }
    }
    
    /**
     * Récupérer les messages non lus
     */
    public function getUnreadMessages() {
        try {
            $query = "SELECT * FROM contact_messages WHERE is_read = 0 ORDER BY created_at DESC";
            $stmt = $this->conn->prepare($query);
            $stmt->execute();
            return $stmt->fetchAll();
            
        } catch(Exception $e) {
            error_log("Erreur récupération messages non lus: " . $e->getMessage());
            return [];
        }
    }
    
    /**
     * Marquer un message comme lu
     */
    public function markAsRead($message_id) {
        try {
            $query = "UPDATE contact_messages SET is_read = 1, updated_at = CURRENT_TIMESTAMP WHERE id = :id";
            $stmt = $this->conn->prepare($query);
            return $stmt->execute([':id' => $message_id]);
            
        } catch(Exception $e) {
            error_log("Erreur marquage message lu: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Marquer un message comme répondu
     */
    public function markAsReplied($message_id, $admin_notes = null) {
        try {
            $query = "UPDATE contact_messages SET is_replied = 1, admin_notes = :notes, updated_at = CURRENT_TIMESTAMP WHERE id = :id";
            $stmt = $this->conn->prepare($query);
            return $stmt->execute([
                ':id' => $message_id,
                ':notes' => $admin_notes
            ]);
            
        } catch(Exception $e) {
            error_log("Erreur marquage message répondu: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Supprimer un message
     */
    public function deleteMessage($message_id) {
        try {
            $query = "DELETE FROM contact_messages WHERE id = :id";
            $stmt = $this->conn->prepare($query);
            return $stmt->execute([':id' => $message_id]);
            
        } catch(Exception $e) {
            error_log("Erreur suppression message: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Récupérer un message par ID
     */
    public function getMessageById($message_id) {
        try {
            $query = "SELECT * FROM contact_messages WHERE id = :id";
            $stmt = $this->conn->prepare($query);
            $stmt->execute([':id' => $message_id]);
            return $stmt->fetch();
            
        } catch(Exception $e) {
            error_log("Erreur récupération message: " . $e->getMessage());
            return null;
        }
    }
    
    /**
     * Obtenir les statistiques des messages
     */
    public function getMessageStats() {
        try {
            $stats = [];
            
            // Total des messages
            $query = "SELECT COUNT(*) as total FROM contact_messages";
            $stmt = $this->conn->prepare($query);
            $stmt->execute();
            $stats['total'] = $stmt->fetch()['total'];
            
            // Messages non lus
            $query = "SELECT COUNT(*) as unread FROM contact_messages WHERE is_read = 0";
            $stmt = $this->conn->prepare($query);
            $stmt->execute();
            $stats['unread'] = $stmt->fetch()['unread'];
            
            // Messages répondus
            $query = "SELECT COUNT(*) as replied FROM contact_messages WHERE is_replied = 1";
            $stmt = $this->conn->prepare($query);
            $stmt->execute();
            $stats['replied'] = $stmt->fetch()['replied'];
            
            // Messages aujourd'hui
            $query = "SELECT COUNT(*) as today FROM contact_messages WHERE DATE(created_at) = CURDATE()";
            $stmt = $this->conn->prepare($query);
            $stmt->execute();
            $stats['today'] = $stmt->fetch()['today'];
            
            return $stats;
            
        } catch(Exception $e) {
            error_log("Erreur statistiques messages: " . $e->getMessage());
            return [
                'total' => 0,
                'unread' => 0,
                'replied' => 0,
                'today' => 0
            ];
        }
    }
    
    /**
     * Rechercher des messages
     */
    public function searchMessages($search_term, $filter = 'all') {
        try {
            $query = "SELECT * FROM contact_messages WHERE 
                     (name LIKE :search OR email LIKE :search OR subject LIKE :search OR message LIKE :search)";
            
            // Ajouter des filtres
            switch($filter) {
                case 'unread':
                    $query .= " AND is_read = 0";
                    break;
                case 'replied':
                    $query .= " AND is_replied = 1";
                    break;
                case 'unreplied':
                    $query .= " AND is_replied = 0";
                    break;
            }
            
            $query .= " ORDER BY created_at DESC";
            
            $stmt = $this->conn->prepare($query);
            $search_param = '%' . $search_term . '%';
            $stmt->execute([':search' => $search_param]);
            
            return $stmt->fetchAll();
            
        } catch(Exception $e) {
            error_log("Erreur recherche messages: " . $e->getMessage());
            return [];
        }
    }
}
?>
