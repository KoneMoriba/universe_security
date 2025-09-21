<?php
/**
 * Configuration de la base de données
 * Universe Security Admin Panel
 */

class Database {
    private $host = 'localhost';
    private $db_name = 'universe_security_admin';
    private $username = 'root';
    private $password = '';
    private $conn;
    
    public function getConnection() {
        $this->conn = null;
        
        try {
            $this->conn = new PDO(
                "mysql:host=" . $this->host . ";dbname=" . $this->db_name . ";charset=utf8",
                $this->username,
                $this->password,
                array(
                    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                    PDO::ATTR_EMULATE_PREPARES => false
                )
            );
        } catch(PDOException $exception) {
            echo "Erreur de connexion: " . $exception->getMessage();
        }
        
        return $this->conn;
    }
    
    public function closeConnection() {
        $this->conn = null;
    }
}

// Configuration générale
define('SITE_URL', 'http://localhost/universe-security/');
define('ADMIN_URL', 'http://localhost/universe-security/admin/');
define('UPLOAD_PATH', '../uploads/');
define('MAX_FILE_SIZE', 5 * 1024 * 1024); // 5MB

// Configuration de session
// ini_set removed to avoid session warnings
// ini_set removed to avoid session warnings
// ini_set removed to avoid session warnings // Mettre à 1 en HTTPS
// session_start moved to individual pages

// Timezone
date_default_timezone_set('Africa/Abidjan');
?>
