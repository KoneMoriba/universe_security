<?php
/**
 * Gestionnaire des articles de blog
 * Universe Security Admin Panel
 */

class BlogManager {
    private $conn;
    private $table_name = "blog_articles";
    
    public function __construct($db) {
        $this->conn = $db;
    }
    
    /**
     * Récupérer tous les articles publiés
     */
    public function getPublishedArticles($limit = null) {
        $sql = "SELECT ba.*, a.username as author_name 
                FROM " . $this->table_name . " ba 
                LEFT JOIN admins a ON ba.author_id = a.id 
                WHERE ba.is_published = 1 
                ORDER BY ba.published_at DESC, ba.created_at DESC";
        
        if ($limit) {
            $sql .= " LIMIT " . intval($limit);
        }
        
        $stmt = $this->conn->prepare($sql);
        $stmt->execute();
        return $stmt->fetchAll();
    }
    
    /**
     * Récupérer tous les articles (pour admin)
     */
    public function getAllArticles() {
        $sql = "SELECT ba.*, a.username as author_name 
                FROM " . $this->table_name . " ba 
                LEFT JOIN admins a ON ba.author_id = a.id 
                ORDER BY ba.created_at DESC";
        
        $stmt = $this->conn->prepare($sql);
        $stmt->execute();
        return $stmt->fetchAll();
    }
    
    /**
     * Récupérer un article par ID
     */
    public function getArticleById($id) {
        $sql = "SELECT ba.*, a.username as author_name 
                FROM " . $this->table_name . " ba 
                LEFT JOIN admins a ON ba.author_id = a.id 
                WHERE ba.id = ?";
        $stmt = $this->conn->prepare($sql);
        $stmt->execute([$id]);
        return $stmt->fetch();
    }
    
    /**
     * Récupérer un article par slug
     */
    public function getArticleBySlug($slug) {
        $sql = "SELECT ba.*, a.username as author_name 
                FROM " . $this->table_name . " ba 
                LEFT JOIN admins a ON ba.author_id = a.id 
                WHERE ba.slug = ? AND ba.is_published = 1";
        $stmt = $this->conn->prepare($sql);
        $stmt->execute([$slug]);
        return $stmt->fetch();
    }
    
    /**
     * Créer un nouveau slug unique
     */
    private function generateUniqueSlug($title, $id = null) {
        $slug = strtolower(trim(preg_replace('/[^A-Za-z0-9-]+/', '-', $title)));
        $original_slug = $slug;
        $counter = 1;
        
        while ($this->slugExists($slug, $id)) {
            $slug = $original_slug . '-' . $counter;
            $counter++;
        }
        
        return $slug;
    }
    
    /**
     * Vérifier si un slug existe
     */
    private function slugExists($slug, $exclude_id = null) {
        $sql = "SELECT COUNT(*) as count FROM " . $this->table_name . " WHERE slug = ?";
        $params = [$slug];
        
        if ($exclude_id) {
            $sql .= " AND id != ?";
            $params[] = $exclude_id;
        }
        
        $stmt = $this->conn->prepare($sql);
        $stmt->execute($params);
        $result = $stmt->fetch();
        return $result['count'] > 0;
    }
    
    /**
     * Créer un nouvel article
     */
    public function createArticle($data) {
        // Générer un slug unique
        $slug = $this->generateUniqueSlug($data['title']);
        
        $sql = "INSERT INTO " . $this->table_name . " 
                (title, slug, excerpt, content, featured_image, category, tags, 
                 author_id, is_published, is_featured, published_at) 
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
        
        $published_at = null;
        if (!empty($data['is_published']) && $data['is_published']) {
            $published_at = date('Y-m-d H:i:s');
        }
        
        $stmt = $this->conn->prepare($sql);
        return $stmt->execute([
            $data['title'],
            $slug,
            $data['excerpt'] ?? null,
            $data['content'],
            $data['featured_image'] ?? null,
            $data['category'] ?? null,
            $data['tags'] ?? null, // JSON
            $data['author_id'] ?? null,
            $data['is_published'] ?? false,
            $data['is_featured'] ?? false,
            $published_at
        ]);
    }
    
    /**
     * Mettre à jour un article
     */
    public function updateArticle($id, $data) {
        // Récupérer l'article actuel
        $current = $this->getArticleById($id);
        
        // Générer un nouveau slug si le titre a changé
        $slug = $current['slug'];
        if ($data['title'] !== $current['title']) {
            $slug = $this->generateUniqueSlug($data['title'], $id);
        }
        
        // Gérer la date de publication
        $published_at = $current['published_at'];
        if (!empty($data['is_published']) && $data['is_published'] && !$current['is_published']) {
            $published_at = date('Y-m-d H:i:s');
        } elseif (empty($data['is_published']) || !$data['is_published']) {
            $published_at = null;
        }
        
        $sql = "UPDATE " . $this->table_name . " 
                SET title = ?, slug = ?, excerpt = ?, content = ?, featured_image = ?, 
                    category = ?, tags = ?, is_published = ?, is_featured = ?, 
                    published_at = ?, updated_at = CURRENT_TIMESTAMP 
                WHERE id = ?";
        
        $stmt = $this->conn->prepare($sql);
        return $stmt->execute([
            $data['title'],
            $slug,
            $data['excerpt'] ?? null,
            $data['content'],
            $data['featured_image'] ?? null,
            $data['category'] ?? null,
            $data['tags'] ?? null,
            $data['is_published'] ?? false,
            $data['is_featured'] ?? false,
            $published_at,
            $id
        ]);
    }
    
    /**
     * Supprimer un article
     */
    public function deleteArticle($id) {
        $sql = "DELETE FROM " . $this->table_name . " WHERE id = ?";
        $stmt = $this->conn->prepare($sql);
        return $stmt->execute([$id]);
    }
    
    /**
     * Publier/Dépublier un article
     */
    public function toggleArticleStatus($id) {
        $sql = "UPDATE " . $this->table_name . " 
                SET is_published = NOT is_published, 
                    published_at = CASE 
                        WHEN is_published = 0 THEN NOW() 
                        ELSE NULL 
                    END,
                    updated_at = CURRENT_TIMESTAMP 
                WHERE id = ?";
        $stmt = $this->conn->prepare($sql);
        return $stmt->execute([$id]);
    }
    
    /**
     * Incrémenter le compteur de vues
     */
    public function incrementViews($id) {
        $sql = "UPDATE " . $this->table_name . " 
                SET views_count = views_count + 1 
                WHERE id = ?";
        $stmt = $this->conn->prepare($sql);
        return $stmt->execute([$id]);
    }
    
    /**
     * Compter les articles
     */
    public function countArticles($published_only = false) {
        $sql = "SELECT COUNT(*) as count FROM " . $this->table_name;
        if ($published_only) {
            $sql .= " WHERE is_published = 1";
        }
        
        $stmt = $this->conn->prepare($sql);
        $stmt->execute();
        $result = $stmt->fetch();
        return $result['count'];
    }
    
    /**
     * Récupérer les articles en vedette
     */
    public function getFeaturedArticles($limit = 3) {
        $sql = "SELECT ba.*, a.username as author_name 
                FROM " . $this->table_name . " ba 
                LEFT JOIN admins a ON ba.author_id = a.id 
                WHERE ba.is_published = 1 AND ba.is_featured = 1 
                ORDER BY ba.published_at DESC 
                LIMIT " . intval($limit);
        
        $stmt = $this->conn->prepare($sql);
        $stmt->execute();
        return $stmt->fetchAll();
    }
    
    /**
     * Récupérer les articles par catégorie
     */
    public function getArticlesByCategory($category, $limit = null) {
        $sql = "SELECT ba.*, a.username as author_name 
                FROM " . $this->table_name . " ba 
                LEFT JOIN admins a ON ba.author_id = a.id 
                WHERE ba.is_published = 1 AND ba.category = ? 
                ORDER BY ba.published_at DESC";
        
        if ($limit) {
            $sql .= " LIMIT " . intval($limit);
        }
        
        $stmt = $this->conn->prepare($sql);
        $stmt->execute([$category]);
        return $stmt->fetchAll();
    }
    
    /**
     * Récupérer toutes les catégories
     */
    public function getCategories() {
        $sql = "SELECT DISTINCT category 
                FROM " . $this->table_name . " 
                WHERE is_published = 1 AND category IS NOT NULL 
                ORDER BY category";
        
        $stmt = $this->conn->prepare($sql);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_COLUMN);
    }
}
?>
