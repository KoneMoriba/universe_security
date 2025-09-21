<?php
/**
 * Classe d'authentification
 * Universe Security Admin Panel
 */

require_once __DIR__ . '/../config/database.php';

class Auth {
    private $conn;
    private $table_name = "admins";
    
    public function __construct() {
        // Démarrer la session si pas déjà fait
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        
        $database = new Database();
        $this->conn = $database->getConnection();
    }
    
    /**
     * Connexion d'un utilisateur
     */
    public function login($username, $password) {
        $query = "SELECT id, username, email, password, full_name, role, is_active 
                  FROM " . $this->table_name . " 
                  WHERE (username = :username OR email = :email) AND is_active = 1";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':username', $username);
        $stmt->bindParam(':email', $username);
        $stmt->execute();
        
        if($stmt->rowCount() > 0) {
            $row = $stmt->fetch();
            
            if(password_verify($password, $row['password'])) {
                // Mise à jour de la dernière connexion
                $this->updateLastLogin($row['id']);
                
                // Création de la session
                $_SESSION['admin_id'] = $row['id'];
                $_SESSION['admin_username'] = $row['username'];
                $_SESSION['admin_email'] = $row['email'];
                $_SESSION['admin_name'] = $row['full_name'];
                $_SESSION['admin_role'] = $row['role'];
                $_SESSION['admin_logged_in'] = true;
                
                // Log de l'activité
                $this->logActivity($row['id'], 'Connexion', 'admins', $row['id']);
                
                return true;
            }
        }
        
        return false;
    }
    
    /**
     * Inscription d'un nouvel administrateur
     */
    public function register($username, $email, $password, $full_name, $role = 'admin') {
        // Vérifier si l'utilisateur existe déjà
        if($this->userExists($username, $email)) {
            return false;
        }
        
        $query = "INSERT INTO " . $this->table_name . " 
                  (username, email, password, full_name, role) 
                  VALUES (:username, :email, :password, :full_name, :role)";
        
        $stmt = $this->conn->prepare($query);
        
        // Hash du mot de passe
        $hashed_password = password_hash($password, PASSWORD_DEFAULT);
        
        $stmt->bindParam(':username', $username);
        $stmt->bindParam(':email', $email);
        $stmt->bindParam(':password', $hashed_password);
        $stmt->bindParam(':full_name', $full_name);
        $stmt->bindParam(':role', $role);
        
        if($stmt->execute()) {
            $user_id = $this->conn->lastInsertId();
            $this->logActivity($user_id, 'Inscription', 'admins', $user_id);
            return true;
        }
        
        return false;
    }
    
    /**
     * Déconnexion
     */
    public function logout() {
        if(isset($_SESSION['admin_id'])) {
            $this->logActivity($_SESSION['admin_id'], 'Déconnexion', 'admins', $_SESSION['admin_id']);
        }
        
        session_destroy();
        return true;
    }
    
    /**
     * Vérifier si l'utilisateur est connecté
     */
    public function isLoggedIn() {
        return isset($_SESSION['admin_logged_in']) && $_SESSION['admin_logged_in'] === true;
    }
    
    /**
     * Vérifier si l'utilisateur a un rôle spécifique
     */
    public function hasRole($required_role) {
        if(!$this->isLoggedIn()) {
            return false;
        }
        
        $roles = ['moderator' => 1, 'admin' => 2, 'super_admin' => 3];
        $user_role_level = $roles[$_SESSION['admin_role']] ?? 0;
        $required_role_level = $roles[$required_role] ?? 0;
        
        return $user_role_level >= $required_role_level;
    }
    
    /**
     * Vérifier si l'utilisateur existe
     */
    private function userExists($username, $email) {
        $query = "SELECT id FROM " . $this->table_name . " 
                  WHERE username = :username OR email = :email";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':username', $username);
        $stmt->bindParam(':email', $email);
        $stmt->execute();
        
        return $stmt->rowCount() > 0;
    }
    
    /**
     * Mettre à jour la dernière connexion
     */
    private function updateLastLogin($user_id) {
        $query = "UPDATE " . $this->table_name . " 
                  SET last_login = CURRENT_TIMESTAMP 
                  WHERE id = :id";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':id', $user_id);
        $stmt->execute();
    }
    
    /**
     * Enregistrer l'activité
     */
    private function logActivity($admin_id, $action, $table_name = null, $record_id = null) {
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
    
    /**
     * Changer le mot de passe
     */
    public function changePassword($user_id, $old_password, $new_password) {
        // Vérifier l'ancien mot de passe
        $query = "SELECT password FROM " . $this->table_name . " WHERE id = :id";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':id', $user_id);
        $stmt->execute();
        
        if($stmt->rowCount() > 0) {
            $row = $stmt->fetch();
            
            if(password_verify($old_password, $row['password'])) {
                // Mettre à jour avec le nouveau mot de passe
                $query = "UPDATE " . $this->table_name . " 
                          SET password = :password 
                          WHERE id = :id";
                
                $stmt = $this->conn->prepare($query);
                $hashed_password = password_hash($new_password, PASSWORD_DEFAULT);
                $stmt->bindParam(':password', $hashed_password);
                $stmt->bindParam(':id', $user_id);
                
                if($stmt->execute()) {
                    $this->logActivity($user_id, 'Changement de mot de passe', 'admins', $user_id);
                    return true;
                }
            }
        }
        
        return false;
    }
    
    /**
     * Récupérer les informations de l'utilisateur connecté
     */
    public function getCurrentUser() {
        if(!$this->isLoggedIn()) {
            return null;
        }
        
        $query = "SELECT id, username, email, full_name, role, created_at, last_login 
                  FROM " . $this->table_name . " 
                  WHERE id = :id";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':id', $_SESSION['admin_id']);
        $stmt->execute();
        
        if($stmt->rowCount() > 0) {
            return $stmt->fetch();
        }
        
        return null;
    }
}
?>
