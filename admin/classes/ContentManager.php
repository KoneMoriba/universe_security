<?php
/**
 * Gestionnaire du contenu "À propos" et autres contenus dynamiques
 * Universe Security Admin Panel
 */

class ContentManager {
    private $conn;
    
    public function __construct($database = null) {
        if ($database) {
            $this->conn = $database;
        }
    }
    
    /**
     * Récupérer tout le contenu "À propos"
     */
    public function getAboutContent() {
        try {
            $query = "SELECT * FROM about_content WHERE is_active = 1 ORDER BY display_order ASC";
            $stmt = $this->conn->prepare($query);
            $stmt->execute();
            return $stmt->fetchAll();
            
        } catch(Exception $e) {
            error_log("Erreur récupération contenu à propos: " . $e->getMessage());
            return [];
        }
    }
    
    /**
     * Récupérer un contenu par sa clé de section
     */
    public function getContentByKey($section_key) {
        try {
            $query = "SELECT * FROM about_content WHERE section_key = :key AND is_active = 1";
            $stmt = $this->conn->prepare($query);
            $stmt->execute([':key' => $section_key]);
            return $stmt->fetch();
            
        } catch(Exception $e) {
            error_log("Erreur récupération contenu par clé: " . $e->getMessage());
            return null;
        }
    }
    
    /**
     * Créer ou mettre à jour un contenu
     */
    public function saveContent($data) {
        try {
            // Vérifier si le contenu existe déjà
            $existing = $this->getContentByKey($data['section_key']);
            
            if ($existing) {
                // Mise à jour
                $query = "UPDATE about_content SET 
                         title = :title, 
                         content = :content, 
                         image = :image, 
                         display_order = :display_order,
                         updated_at = CURRENT_TIMESTAMP
                         WHERE section_key = :section_key";
            } else {
                // Création
                $query = "INSERT INTO about_content (section_key, title, content, image, display_order, created_by) 
                         VALUES (:section_key, :title, :content, :image, :display_order, :created_by)";
            }
            
            $stmt = $this->conn->prepare($query);
            $params = [
                ':section_key' => $data['section_key'],
                ':title' => $data['title'],
                ':content' => $data['content'],
                ':image' => $data['image'] ?? null,
                ':display_order' => $data['display_order'] ?? 0
            ];
            
            if (!$existing) {
                $params[':created_by'] = $_SESSION['admin_id'] ?? null;
            }
            
            return $stmt->execute($params);
            
        } catch(Exception $e) {
            error_log("Erreur sauvegarde contenu: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Supprimer un contenu
     */
    public function deleteContent($section_key) {
        try {
            $query = "DELETE FROM about_content WHERE section_key = :key";
            $stmt = $this->conn->prepare($query);
            return $stmt->execute([':key' => $section_key]);
            
        } catch(Exception $e) {
            error_log("Erreur suppression contenu: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Activer/désactiver un contenu
     */
    public function toggleContentStatus($section_key) {
        try {
            $query = "UPDATE about_content SET is_active = NOT is_active WHERE section_key = :key";
            $stmt = $this->conn->prepare($query);
            return $stmt->execute([':key' => $section_key]);
            
        } catch(Exception $e) {
            error_log("Erreur changement statut contenu: " . $e->getMessage());
            return false;
        }
    }
}

/**
 * Gestionnaire des vendors/partenaires
 */
class VendorManager {
    private $conn;
    
    public function __construct($database = null) {
        if ($database) {
            $this->conn = $database;
        }
    }
    
    /**
     * Récupérer tous les vendors actifs
     */
    public function getActiveVendors() {
        try {
            $query = "SELECT * FROM vendors WHERE is_active = 1 ORDER BY display_order ASC";
            $stmt = $this->conn->prepare($query);
            $stmt->execute();
            return $stmt->fetchAll();
            
        } catch(Exception $e) {
            error_log("Erreur récupération vendors: " . $e->getMessage());
            return [];
        }
    }
    
    /**
     * Récupérer tous les vendors (pour l'admin)
     */
    public function getAllVendors() {
        try {
            $query = "SELECT * FROM vendors ORDER BY display_order ASC";
            $stmt = $this->conn->prepare($query);
            $stmt->execute();
            return $stmt->fetchAll();
            
        } catch(Exception $e) {
            error_log("Erreur récupération tous vendors: " . $e->getMessage());
            return [];
        }
    }
    
    /**
     * Récupérer un vendor par ID
     */
    public function getVendorById($vendor_id) {
        try {
            $query = "SELECT * FROM vendors WHERE id = :id";
            $stmt = $this->conn->prepare($query);
            $stmt->execute([':id' => $vendor_id]);
            return $stmt->fetch();
            
        } catch(Exception $e) {
            error_log("Erreur récupération vendor: " . $e->getMessage());
            return null;
        }
    }
    
    /**
     * Créer un nouveau vendor
     */
    public function createVendor($data) {
        try {
            $query = "INSERT INTO vendors (name, logo, website, description, display_order, created_by) 
                     VALUES (:name, :logo, :website, :description, :display_order, :created_by)";
            
            $stmt = $this->conn->prepare($query);
            return $stmt->execute([
                ':name' => $data['name'],
                ':logo' => $data['logo'],
                ':website' => $data['website'] ?? null,
                ':description' => $data['description'] ?? null,
                ':display_order' => $data['display_order'] ?? 0,
                ':created_by' => $_SESSION['admin_id'] ?? null
            ]);
            
        } catch(Exception $e) {
            error_log("Erreur création vendor: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Mettre à jour un vendor
     */
    public function updateVendor($vendor_id, $data) {
        try {
            $query = "UPDATE vendors SET 
                     name = :name, 
                     logo = :logo, 
                     website = :website, 
                     description = :description, 
                     display_order = :display_order,
                     updated_at = CURRENT_TIMESTAMP
                     WHERE id = :id";
            
            $stmt = $this->conn->prepare($query);
            return $stmt->execute([
                ':id' => $vendor_id,
                ':name' => $data['name'],
                ':logo' => $data['logo'],
                ':website' => $data['website'] ?? null,
                ':description' => $data['description'] ?? null,
                ':display_order' => $data['display_order'] ?? 0
            ]);
            
        } catch(Exception $e) {
            error_log("Erreur mise à jour vendor: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Supprimer un vendor
     */
    public function deleteVendor($vendor_id) {
        try {
            $query = "DELETE FROM vendors WHERE id = :id";
            $stmt = $this->conn->prepare($query);
            return $stmt->execute([':id' => $vendor_id]);
            
        } catch(Exception $e) {
            error_log("Erreur suppression vendor: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Activer/désactiver un vendor
     */
    public function toggleVendorStatus($vendor_id) {
        try {
            $query = "UPDATE vendors SET is_active = NOT is_active WHERE id = :id";
            $stmt = $this->conn->prepare($query);
            return $stmt->execute([':id' => $vendor_id]);
            
        } catch(Exception $e) {
            error_log("Erreur changement statut vendor: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Obtenir les statistiques des vendors
     */
    public function getVendorStats() {
        try {
            $stats = [];
            
            // Total des vendors
            $query = "SELECT COUNT(*) as total FROM vendors";
            $stmt = $this->conn->prepare($query);
            $stmt->execute();
            $stats['total'] = $stmt->fetch()['total'];
            
            // Vendors actifs
            $query = "SELECT COUNT(*) as active FROM vendors WHERE is_active = 1";
            $stmt = $this->conn->prepare($query);
            $stmt->execute();
            $stats['active'] = $stmt->fetch()['active'];
            
            return $stats;
            
        } catch(Exception $e) {
            error_log("Erreur statistiques vendors: " . $e->getMessage());
            return ['total' => 0, 'active' => 0];
        }
    }
}
?>
