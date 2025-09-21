<?php
/**
 * Gestionnaire des offres/packages
 * Universe Security Admin Panel
 */

class OfferManager {
    private $conn;
    private $table_name = "offers";
    
    public function __construct($db) {
        $this->conn = $db;
    }
    
    /**
     * Récupérer toutes les offres actives
     */
    public function getActiveOffers($limit = null) {
        try {
            // Vérifier si la table existe
            $checkTable = "SHOW TABLES LIKE '" . $this->table_name . "'";
            $stmt = $this->conn->prepare($checkTable);
            $stmt->execute();
            
            if ($stmt->rowCount() == 0) {
                // Table n'existe pas, retourner un tableau vide
                return [];
            }
            
            $sql = "SELECT * FROM " . $this->table_name . " 
                    WHERE is_active = 1 
                    ORDER BY display_order ASC, created_at DESC";
            
            if ($limit) {
                $sql .= " LIMIT " . intval($limit);
            }
            
            $stmt = $this->conn->prepare($sql);
            $stmt->execute();
            return $stmt->fetchAll();
        } catch (Exception $e) {
            // En cas d'erreur, retourner un tableau vide
            error_log("Erreur OfferManager: " . $e->getMessage());
            return [];
        }
    }
    
    /**
     * Récupérer toutes les offres (pour admin)
     */
    public function getAllOffers() {
        $sql = "SELECT * FROM " . $this->table_name . " 
                ORDER BY display_order ASC, created_at DESC";
        
        $stmt = $this->conn->prepare($sql);
        $stmt->execute();
        return $stmt->fetchAll();
    }
    
    /**
     * Récupérer une offre par ID
     */
    public function getOfferById($id) {
        $sql = "SELECT * FROM " . $this->table_name . " WHERE id = ?";
        $stmt = $this->conn->prepare($sql);
        $stmt->execute([$id]);
        return $stmt->fetch();
    }
    
    /**
     * Créer une nouvelle offre
     */
    public function createOffer($data) {
        $sql = "INSERT INTO " . $this->table_name . " 
                (title, subtitle, description, features, price, price_text, icon, is_featured, is_active, display_order, created_by) 
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
        
        $stmt = $this->conn->prepare($sql);
        return $stmt->execute([
            $data['title'],
            $data['subtitle'] ?? null,
            $data['description'] ?? null,
            $data['features'] ?? null, // JSON
            $data['price'] ?? null,
            $data['price_text'] ?? null,
            $data['icon'] ?? null,
            $data['is_featured'] ?? false,
            $data['is_active'] ?? true,
            $data['display_order'] ?? 0,
            $data['created_by'] ?? null
        ]);
    }
    
    /**
     * Mettre à jour une offre
     */
    public function updateOffer($id, $data) {
        $sql = "UPDATE " . $this->table_name . " 
                SET title = ?, subtitle = ?, description = ?, features = ?, 
                    price = ?, price_text = ?, icon = ?, is_featured = ?, 
                    is_active = ?, display_order = ?, updated_at = CURRENT_TIMESTAMP 
                WHERE id = ?";
        
        $stmt = $this->conn->prepare($sql);
        return $stmt->execute([
            $data['title'],
            $data['subtitle'] ?? null,
            $data['description'] ?? null,
            $data['features'] ?? null,
            $data['price'] ?? null,
            $data['price_text'] ?? null,
            $data['icon'] ?? null,
            $data['is_featured'] ?? false,
            $data['is_active'] ?? true,
            $data['display_order'] ?? 0,
            $id
        ]);
    }
    
    /**
     * Supprimer une offre
     */
    public function deleteOffer($id) {
        $sql = "DELETE FROM " . $this->table_name . " WHERE id = ?";
        $stmt = $this->conn->prepare($sql);
        return $stmt->execute([$id]);
    }
    
    /**
     * Activer/Désactiver une offre
     */
    public function toggleOfferStatus($id) {
        $sql = "UPDATE " . $this->table_name . " 
                SET is_active = NOT is_active, updated_at = CURRENT_TIMESTAMP 
                WHERE id = ?";
        $stmt = $this->conn->prepare($sql);
        return $stmt->execute([$id]);
    }
    
    /**
     * Compter les offres
     */
    public function countOffers($active_only = false) {
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
     * Récupérer les offres en vedette
     */
    public function getFeaturedOffers() {
        $sql = "SELECT * FROM " . $this->table_name . " 
                WHERE is_active = 1 AND is_featured = 1 
                ORDER BY display_order ASC";
        
        $stmt = $this->conn->prepare($sql);
        $stmt->execute();
        return $stmt->fetchAll();
    }
}
?>
