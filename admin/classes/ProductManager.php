<?php
/**
 * Gestionnaire des produits
 * Universe Security Admin Panel
 */

require_once __DIR__ . '/../config/database.php';

class ProductManager {
    private $conn;
    private $table_name = "products";
    
    public function __construct() {
        $database = new Database();
        $this->conn = $database->getConnection();
    }
    
    /**
     * Récupérer tous les produits
     */
    public function getAllProducts() {
        // Vérifier d'abord si les colonnes existent
        $query = "SHOW COLUMNS FROM " . $this->table_name . " LIKE 'display_order'";
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        $has_display_order = $stmt->rowCount() > 0;
        
        $query = "SHOW COLUMNS FROM " . $this->table_name . " LIKE 'product_image'";
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        $has_product_image = $stmt->rowCount() > 0;
        
        // Construire la requête selon les colonnes disponibles
        $order_clause = $has_display_order ? "p.display_order ASC, p.created_at DESC" : "p.created_at DESC";
        
        $query = "SELECT p.*, a.full_name as created_by_name 
                  FROM " . $this->table_name . " p 
                  LEFT JOIN admins a ON p.created_by = a.id 
                  ORDER BY " . $order_clause;
        
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        
        $products = $stmt->fetchAll();
        
        // Ajouter des valeurs par défaut pour les colonnes manquantes
        foreach($products as &$product) {
            if(!$has_product_image) {
                $product['product_image'] = $product['image_url'] ?? '';
            }
            if(!$has_display_order) {
                $product['display_order'] = 0;
            }
            if(!isset($product['specifications'])) {
                $product['specifications'] = '';
            }
            if(!isset($product['is_featured'])) {
                $product['is_featured'] = 0;
            }
        }
        
        return $products;
    }
    
    /**
     * Récupérer un produit par ID
     */
    public function getProductById($id) {
        $query = "SELECT p.*, a.full_name as created_by_name 
                  FROM " . $this->table_name . " p 
                  LEFT JOIN admins a ON p.created_by = a.id 
                  WHERE p.id = :id";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':id', $id);
        $stmt->execute();
        
        return $stmt->fetch();
    }
    
    /**
     * Créer un nouveau produit
     */
    public function createProduct($data, $admin_id) {
        // Vérifier les colonnes disponibles
        $columns = $this->getAvailableColumns();
        
        // Construire la requête dynamiquement
        $insert_columns = ['name', 'description', 'category', 'price', 'currency', 'stock_quantity', 'is_active', 'created_by'];
        $insert_values = [':name', ':description', ':category', ':price', ':currency', ':stock_quantity', ':is_active', ':created_by'];
        
        if(in_array('product_image', $columns)) {
            $insert_columns[] = 'product_image';
            $insert_values[] = ':product_image';
        } elseif(in_array('image_url', $columns)) {
            $insert_columns[] = 'image_url';
            $insert_values[] = ':product_image';
        }
        
        if(in_array('specifications', $columns)) {
            $insert_columns[] = 'specifications';
            $insert_values[] = ':specifications';
        }
        
        if(in_array('is_featured', $columns)) {
            $insert_columns[] = 'is_featured';
            $insert_values[] = ':is_featured';
        }
        
        if(in_array('display_order', $columns)) {
            $insert_columns[] = 'display_order';
            $insert_values[] = ':display_order';
        }
        
        $query = "INSERT INTO " . $this->table_name . " 
                  (" . implode(', ', $insert_columns) . ") 
                  VALUES (" . implode(', ', $insert_values) . ")";
        
        $stmt = $this->conn->prepare($query);
        
        $stmt->bindParam(':name', $data['name']);
        $stmt->bindParam(':description', $data['description']);
        $stmt->bindParam(':category', $data['category']);
        $stmt->bindParam(':price', $data['price']);
        $stmt->bindParam(':currency', $data['currency']);
        $stmt->bindParam(':stock_quantity', $data['stock_quantity']);
        $stmt->bindParam(':is_active', $data['is_active']);
        $stmt->bindParam(':created_by', $admin_id);
        
        if(in_array('product_image', $columns) || in_array('image_url', $columns)) {
            $stmt->bindParam(':product_image', $data['product_image']);
        }
        
        if(in_array('specifications', $columns)) {
            $stmt->bindParam(':specifications', $data['specifications']);
        }
        
        if(in_array('is_featured', $columns)) {
            $stmt->bindParam(':is_featured', $data['is_featured']);
        }
        
        if(in_array('display_order', $columns)) {
            $stmt->bindParam(':display_order', $data['display_order']);
        }
        
        if($stmt->execute()) {
            $this->logActivity($admin_id, 'Création produit', 'products', $this->conn->lastInsertId());
            return $this->conn->lastInsertId();
        }
        
        return false;
    }
    
    /**
     * Mettre à jour un produit
     */
    public function updateProduct($id, $data, $admin_id) {
        $query = "UPDATE " . $this->table_name . " 
                  SET name = :name, description = :description, category = :category, 
                      price = :price, currency = :currency, stock_quantity = :stock_quantity, 
                      product_image = :product_image, specifications = :specifications, 
                      is_active = :is_active, is_featured = :is_featured, 
                      display_order = :display_order, updated_at = CURRENT_TIMESTAMP 
                  WHERE id = :id";
        
        $stmt = $this->conn->prepare($query);
        
        $stmt->bindParam(':id', $id);
        $stmt->bindParam(':name', $data['name']);
        $stmt->bindParam(':description', $data['description']);
        $stmt->bindParam(':category', $data['category']);
        $stmt->bindParam(':price', $data['price']);
        $stmt->bindParam(':currency', $data['currency']);
        $stmt->bindParam(':stock_quantity', $data['stock_quantity']);
        $stmt->bindParam(':product_image', $data['product_image']);
        $stmt->bindParam(':specifications', $data['specifications']);
        $stmt->bindParam(':is_active', $data['is_active']);
        $stmt->bindParam(':is_featured', $data['is_featured']);
        $stmt->bindParam(':display_order', $data['display_order']);
        
        if($stmt->execute()) {
            $this->logActivity($admin_id, 'Mise à jour produit', 'products', $id);
            return true;
        }
        
        return false;
    }
    
    /**
     * Supprimer un produit
     */
    public function deleteProduct($id, $admin_id) {
        $query = "DELETE FROM " . $this->table_name . " WHERE id = :id";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':id', $id);
        
        if($stmt->execute()) {
            $this->logActivity($admin_id, 'Suppression produit', 'products', $id);
            return true;
        }
        
        return false;
    }
    
    /**
     * Basculer le statut d'un produit
     */
    public function toggleProductStatus($id, $admin_id) {
        $query = "UPDATE " . $this->table_name . " 
                  SET is_active = NOT is_active, updated_at = CURRENT_TIMESTAMP 
                  WHERE id = :id";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':id', $id);
        
        if($stmt->execute()) {
            $this->logActivity($admin_id, 'Changement statut produit', 'products', $id);
            return true;
        }
        
        return false;
    }
    
    /**
     * Basculer le statut vedette d'un produit
     */
    public function toggleFeatured($id, $admin_id) {
        $query = "UPDATE " . $this->table_name . " 
                  SET is_featured = NOT is_featured, updated_at = CURRENT_TIMESTAMP 
                  WHERE id = :id";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':id', $id);
        
        if($stmt->execute()) {
            $this->logActivity($admin_id, 'Changement vedette produit', 'products', $id);
            return true;
        }
        
        return false;
    }
    
    /**
     * Rechercher des produits
     */
    public function searchProducts($search_term) {
        $query = "SELECT p.*, a.full_name as created_by_name 
                  FROM " . $this->table_name . " p 
                  LEFT JOIN admins a ON p.created_by = a.id 
                  WHERE p.name LIKE :search OR p.description LIKE :search OR p.category LIKE :search 
                  ORDER BY p.display_order ASC, p.created_at DESC";
        
        $stmt = $this->conn->prepare($query);
        $search_param = '%' . $search_term . '%';
        $stmt->bindParam(':search', $search_param);
        $stmt->execute();
        
        return $stmt->fetchAll();
    }
    
    /**
     * Obtenir les statistiques des produits
     */
    public function getProductStats() {
        $stats = [];
        
        // Total des produits
        $query = "SELECT COUNT(*) as total FROM " . $this->table_name;
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        $stats['total'] = $stmt->fetch()['total'];
        
        // Produits actifs
        $query = "SELECT COUNT(*) as count FROM " . $this->table_name . " WHERE is_active = 1";
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        $stats['active'] = $stmt->fetch()['count'];
        
        // Produits inactifs
        $query = "SELECT COUNT(*) as count FROM " . $this->table_name . " WHERE is_active = 0";
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        $stats['inactive'] = $stmt->fetch()['count'];
        
        // Produits en vedette
        $query = "SELECT COUNT(*) as count FROM " . $this->table_name . " WHERE is_featured = 1";
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        $stats['featured'] = $stmt->fetch()['count'];
        
        // Prix moyen
        $query = "SELECT AVG(price) as avg_price FROM " . $this->table_name . " WHERE is_active = 1 AND price > 0";
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        $stats['avg_price'] = $stmt->fetch()['avg_price'] ?? 0;
        
        // Stock total
        $query = "SELECT SUM(stock_quantity) as total_stock FROM " . $this->table_name . " WHERE is_active = 1";
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        $stats['total_stock'] = $stmt->fetch()['total_stock'] ?? 0;
        
        return $stats;
    }
    
    /**
     * Obtenir les catégories de produits
     */
    public function getCategories() {
        $query = "SELECT DISTINCT category FROM " . $this->table_name . " WHERE category IS NOT NULL AND category != '' ORDER BY category";
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        
        return $stmt->fetchAll(PDO::FETCH_COLUMN);
    }
    
    /**
     * Obtenir les colonnes disponibles dans la table
     */
    private function getAvailableColumns() {
        $query = "SHOW COLUMNS FROM " . $this->table_name;
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        $columns = $stmt->fetchAll(PDO::FETCH_COLUMN);
        return $columns;
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
