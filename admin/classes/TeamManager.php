<?php
/**
 * Gestionnaire des membres de l'équipe
 * Universe Security Admin Panel
 */

class TeamManager {
    private $conn;
    private $table_name = "team_members";
    
    public function __construct($db) {
        $this->conn = $db;
    }
    
    /**
     * Récupérer tous les membres actifs
     */
    public function getActiveMembers($limit = null) {
        $sql = "SELECT * FROM " . $this->table_name . " 
                WHERE is_active = 1 
                ORDER BY display_order ASC, created_at DESC";
        
        if ($limit) {
            $sql .= " LIMIT " . intval($limit);
        }
        
        $stmt = $this->conn->prepare($sql);
        $stmt->execute();
        return $stmt->fetchAll();
    }
    
    /**
     * Récupérer tous les membres (pour admin)
     */
    public function getAllMembers() {
        $sql = "SELECT * FROM " . $this->table_name . " 
                ORDER BY display_order ASC, created_at DESC";
        
        $stmt = $this->conn->prepare($sql);
        $stmt->execute();
        return $stmt->fetchAll();
    }
    
    /**
     * Récupérer un membre par ID
     */
    public function getMemberById($id) {
        $sql = "SELECT * FROM " . $this->table_name . " WHERE id = ?";
        $stmt = $this->conn->prepare($sql);
        $stmt->execute([$id]);
        return $stmt->fetch();
    }
    
    /**
     * Créer un nouveau membre
     */
    public function createMember($data) {
        $sql = "INSERT INTO " . $this->table_name . " 
                (name, position, bio, image, email, phone, social_facebook, social_twitter, 
                 social_linkedin, social_instagram, is_active, display_order, created_by) 
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
        
        $stmt = $this->conn->prepare($sql);
        return $stmt->execute([
            $data['name'],
            $data['position'],
            $data['bio'] ?? null,
            $data['image'] ?? null,
            $data['email'] ?? null,
            $data['phone'] ?? null,
            $data['social_facebook'] ?? null,
            $data['social_twitter'] ?? null,
            $data['social_linkedin'] ?? null,
            $data['social_instagram'] ?? null,
            $data['is_active'] ?? true,
            $data['display_order'] ?? 0,
            $data['created_by'] ?? null
        ]);
    }
    
    /**
     * Mettre à jour un membre
     */
    public function updateMember($id, $data) {
        $sql = "UPDATE " . $this->table_name . " 
                SET name = ?, position = ?, bio = ?, image = ?, email = ?, phone = ?,
                    social_facebook = ?, social_twitter = ?, social_linkedin = ?, 
                    social_instagram = ?, is_active = ?, display_order = ?, 
                    updated_at = CURRENT_TIMESTAMP 
                WHERE id = ?";
        
        $stmt = $this->conn->prepare($sql);
        return $stmt->execute([
            $data['name'],
            $data['position'],
            $data['bio'] ?? null,
            $data['image'] ?? null,
            $data['email'] ?? null,
            $data['phone'] ?? null,
            $data['social_facebook'] ?? null,
            $data['social_twitter'] ?? null,
            $data['social_linkedin'] ?? null,
            $data['social_instagram'] ?? null,
            $data['is_active'] ?? true,
            $data['display_order'] ?? 0,
            $id
        ]);
    }
    
    /**
     * Supprimer un membre
     */
    public function deleteMember($id) {
        $sql = "DELETE FROM " . $this->table_name . " WHERE id = ?";
        $stmt = $this->conn->prepare($sql);
        return $stmt->execute([$id]);
    }
    
    /**
     * Activer/Désactiver un membre
     */
    public function toggleMemberStatus($id) {
        $sql = "UPDATE " . $this->table_name . " 
                SET is_active = NOT is_active, updated_at = CURRENT_TIMESTAMP 
                WHERE id = ?";
        $stmt = $this->conn->prepare($sql);
        return $stmt->execute([$id]);
    }
    
    /**
     * Compter les membres
     */
    public function countMembers($active_only = false) {
        $sql = "SELECT COUNT(*) as count FROM " . $this->table_name;
        if ($active_only) {
            $sql .= " WHERE is_active = 1";
        }
        
        $stmt = $this->conn->prepare($sql);
        $stmt->execute();
        $result = $stmt->fetch();
        return $result['count'];
    }
    
    /**
     * Récupérer les membres pour la page d'accueil (limité)
     */
    public function getMembersForHomepage($limit = 3) {
        return $this->getActiveMembers($limit);
    }
}
?>
