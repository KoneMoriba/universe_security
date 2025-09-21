<?php
class MediaManager {
    private $conn;
    
    public function __construct() {
        $database = new Database();
        $this->conn = $database->getConnection();
    }
    
    // Créer un nouveau média
    public function createMedia($data, $admin_id) {
        try {
            $query = "INSERT INTO medias (title, description, file_path, file_type, media_type, alt_text, is_active, display_order, created_by, created_at) 
                      VALUES (:title, :description, :file_path, :file_type, :media_type, :alt_text, :is_active, :display_order, :created_by, NOW())";
            
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(':title', $data['title']);
            $stmt->bindParam(':description', $data['description']);
            $stmt->bindParam(':file_path', $data['file_path']);
            $stmt->bindParam(':file_type', $data['file_type']);
            $stmt->bindParam(':media_type', $data['media_type']);
            $stmt->bindParam(':alt_text', $data['alt_text']);
            $stmt->bindParam(':is_active', $data['is_active']);
            $stmt->bindParam(':display_order', $data['display_order']);
            $stmt->bindParam(':created_by', $admin_id);
            
            if($stmt->execute()) {
                $this->logActivity($admin_id, 'CREATE', 'Média créé: ' . $data['title']);
                return true;
            }
            return false;
        } catch(Exception $e) {
            error_log("Erreur création média: " . $e->getMessage());
            return false;
        }
    }
    
    // Récupérer tous les médias
    public function getAllMedias($media_type = null, $limit = null, $offset = 0) {
        try {
            $query = "SELECT m.*, a.username as created_by_name 
                      FROM medias m 
                      LEFT JOIN admins a ON m.created_by = a.id";
            
            if($media_type) {
                $query .= " WHERE m.media_type = :media_type";
            }
            
            $query .= " ORDER BY m.display_order ASC, m.created_at DESC";
            
            if($limit) {
                $query .= " LIMIT :limit OFFSET :offset";
            }
            
            $stmt = $this->conn->prepare($query);
            
            if($media_type) {
                $stmt->bindParam(':media_type', $media_type);
            }
            
            if($limit) {
                $stmt->bindParam(':limit', $limit, PDO::PARAM_INT);
                $stmt->bindParam(':offset', $offset, PDO::PARAM_INT);
            }
            
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch(Exception $e) {
            error_log("Erreur récupération médias: " . $e->getMessage());
            return [];
        }
    }
    
    // Récupérer un média par ID
    public function getMediaById($id) {
        try {
            $query = "SELECT * FROM medias WHERE id = :id";
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(':id', $id);
            $stmt->execute();
            return $stmt->fetch(PDO::FETCH_ASSOC);
        } catch(Exception $e) {
            error_log("Erreur récupération média: " . $e->getMessage());
            return false;
        }
    }
    
    // Mettre à jour un média
    public function updateMedia($id, $data, $admin_id) {
        try {
            $query = "UPDATE medias SET 
                      title = :title,
                      description = :description,
                      file_path = :file_path,
                      file_type = :file_type,
                      media_type = :media_type,
                      alt_text = :alt_text,
                      is_active = :is_active,
                      display_order = :display_order,
                      updated_at = NOW()
                      WHERE id = :id";
            
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(':id', $id);
            $stmt->bindParam(':title', $data['title']);
            $stmt->bindParam(':description', $data['description']);
            $stmt->bindParam(':file_path', $data['file_path']);
            $stmt->bindParam(':file_type', $data['file_type']);
            $stmt->bindParam(':media_type', $data['media_type']);
            $stmt->bindParam(':alt_text', $data['alt_text']);
            $stmt->bindParam(':is_active', $data['is_active']);
            $stmt->bindParam(':display_order', $data['display_order']);
            
            if($stmt->execute()) {
                $this->logActivity($admin_id, 'UPDATE', 'Média modifié: ' . $data['title']);
                return true;
            }
            return false;
        } catch(Exception $e) {
            error_log("Erreur mise à jour média: " . $e->getMessage());
            return false;
        }
    }
    
    // Supprimer un média
    public function deleteMedia($id, $admin_id) {
        try {
            // Récupérer les infos du média avant suppression
            $media = $this->getMediaById($id);
            if(!$media) return false;
            
            // Supprimer le fichier physique
            if(file_exists($media['file_path'])) {
                unlink($media['file_path']);
            }
            
            $query = "DELETE FROM medias WHERE id = :id";
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(':id', $id);
            
            if($stmt->execute()) {
                $this->logActivity($admin_id, 'DELETE', 'Média supprimé: ' . $media['title']);
                return true;
            }
            return false;
        } catch(Exception $e) {
            error_log("Erreur suppression média: " . $e->getMessage());
            return false;
        }
    }
    
    // Récupérer les statistiques des médias
    public function getMediaStats() {
        try {
            $stats = [];
            
            // Total des médias
            $query = "SELECT COUNT(*) as total FROM medias";
            $stmt = $this->conn->prepare($query);
            $stmt->execute();
            $stats['total'] = $stmt->fetch()['total'];
            
            // Médias actifs
            $query = "SELECT COUNT(*) as active FROM medias WHERE is_active = 1";
            $stmt = $this->conn->prepare($query);
            $stmt->execute();
            $stats['active'] = $stmt->fetch()['active'];
            
            // Photos
            $query = "SELECT COUNT(*) as photos FROM medias WHERE media_type = 'photo' AND is_active = 1";
            $stmt = $this->conn->prepare($query);
            $stmt->execute();
            $stats['photos'] = $stmt->fetch()['photos'];
            
            // Vidéos
            $query = "SELECT COUNT(*) as videos FROM medias WHERE media_type = 'video' AND is_active = 1";
            $stmt = $this->conn->prepare($query);
            $stmt->execute();
            $stats['videos'] = $stmt->fetch()['videos'];
            
            return $stats;
        } catch(Exception $e) {
            error_log("Erreur statistiques médias: " . $e->getMessage());
            return ['total' => 0, 'active' => 0, 'photos' => 0, 'videos' => 0];
        }
    }
    
    // Changer le statut d'un média
    public function toggleMediaStatus($id, $admin_id) {
        try {
            $media = $this->getMediaById($id);
            if(!$media) return false;
            
            $new_status = $media['is_active'] ? 0 : 1;
            
            $query = "UPDATE medias SET is_active = :status, updated_at = NOW() WHERE id = :id";
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(':status', $new_status);
            $stmt->bindParam(':id', $id);
            
            if($stmt->execute()) {
                $action = $new_status ? 'activé' : 'désactivé';
                $this->logActivity($admin_id, 'UPDATE', 'Média ' . $action . ': ' . $media['title']);
                return true;
            }
            return false;
        } catch(Exception $e) {
            error_log("Erreur changement statut média: " . $e->getMessage());
            return false;
        }
    }
    
    // Logger les activités
    private function logActivity($admin_id, $action, $description) {
        try {
            $query = "INSERT INTO activity_logs (admin_id, action, description, created_at) VALUES (:admin_id, :action, :description, NOW())";
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(':admin_id', $admin_id);
            $stmt->bindParam(':action', $action);
            $stmt->bindParam(':description', $description);
            $stmt->execute();
        } catch(Exception $e) {
            error_log("Erreur log activité: " . $e->getMessage());
        }
    }
}
?>
