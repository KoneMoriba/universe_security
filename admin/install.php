<?php
/**
 * Script d'installation automatique
 * Universe Security Admin Panel
 */

// Vérifier si l'installation a déjà été effectuée
if(file_exists('config/.installed')) {
    die('L\'installation a déjà été effectuée. Supprimez le fichier config/.installed pour réinstaller.');
}

$step = $_GET['step'] ?? 1;
$error = '';
$success = '';

// Étape 1: Vérification des prérequis
if($step == 1) {
    $checks = [
        'PHP Version >= 7.4' => version_compare(PHP_VERSION, '7.4.0', '>='),
        'Extension PDO' => extension_loaded('pdo'),
        'Extension PDO MySQL' => extension_loaded('pdo_mysql'),
        'Extension Session' => extension_loaded('session'),
        'Dossier config/ writable' => is_writable('config/'),
    ];
}

// Étape 2: Configuration de la base de données
if($step == 2 && $_SERVER['REQUEST_METHOD'] == 'POST') {
    $host = $_POST['host'] ?? 'localhost';
    $dbname = $_POST['dbname'] ?? 'universe_security_admin';
    $username = $_POST['username'] ?? 'root';
    $password = $_POST['password'] ?? '';
    
    try {
        // Test de connexion
        $pdo = new PDO("mysql:host=$host", $username, $password);
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        
        // Créer la base de données si elle n'existe pas
        $pdo->exec("CREATE DATABASE IF NOT EXISTS `$dbname` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci");
        $pdo->exec("USE `$dbname`");
        
        // Exécuter le script SQL
        $sql = file_get_contents('database/database.sql');
        $pdo->exec($sql);
        
        // Sauvegarder la configuration
        $config = "<?php
class Database {
    private \$host = '$host';
    private \$db_name = '$dbname';
    private \$username = '$username';
    private \$password = '$password';
    private \$conn;
    
    public function getConnection() {
        \$this->conn = null;
        
        try {
            \$this->conn = new PDO(
                \"mysql:host=\" . \$this->host . \";dbname=\" . \$this->db_name . \";charset=utf8\",
                \$this->username,
                \$this->password,
                array(
                    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                    PDO::ATTR_EMULATE_PREPARES => false
                )
            );
        } catch(PDOException \$exception) {
            echo \"Erreur de connexion: \" . \$exception->getMessage();
        }
        
        return \$this->conn;
    }
    
    public function closeConnection() {
        \$this->conn = null;
    }
}

// Configuration générale
define('SITE_URL', 'http://localhost/universe-security/');
define('ADMIN_URL', 'http://localhost/universe-security/admin/');
define('UPLOAD_PATH', '../uploads/');
define('MAX_FILE_SIZE', 5 * 1024 * 1024); // 5MB

// Configuration de session
ini_set('session.cookie_httponly', 1);
ini_set('session.use_only_cookies', 1);
ini_set('session.cookie_secure', 0); // Mettre à 1 en HTTPS
session_start();

// Timezone
date_default_timezone_set('Africa/Abidjan');
?>";
        
        file_put_contents('config/database.php', $config);
        
        $success = 'Base de données configurée avec succès !';
        $step = 3;
        
    } catch(Exception $e) {
        $error = 'Erreur de configuration : ' . $e->getMessage();
    }
}

// Étape 3: Création du compte administrateur
if($step == 3 && $_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['create_admin'])) {
    require_once 'config/database.php';
    
    $admin_username = $_POST['admin_username'];
    $admin_email = $_POST['admin_email'];
    $admin_password = $_POST['admin_password'];
    $admin_name = $_POST['admin_name'];
    
    try {
        $database = new Database();
        $conn = $database->getConnection();
        
        // Supprimer l'admin par défaut
        $conn->exec("DELETE FROM admins WHERE username = 'admin'");
        
        // Créer le nouvel admin
        $query = "INSERT INTO admins (username, email, password, full_name, role) VALUES (?, ?, ?, ?, 'super_admin')";
        $stmt = $conn->prepare($query);
        $hashed_password = password_hash($admin_password, PASSWORD_DEFAULT);
        $stmt->execute([$admin_username, $admin_email, $hashed_password, $admin_name]);
        
        // Marquer l'installation comme terminée
        file_put_contents('config/.installed', date('Y-m-d H:i:s'));
        
        $success = 'Installation terminée avec succès !';
        $step = 4;
        
    } catch(Exception $e) {
        $error = 'Erreur lors de la création de l\'administrateur : ' . $e->getMessage();
    }
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Installation - Universe Security Admin</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    
    <style>
        body {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }
        
        .install-container {
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 20px;
        }
        
        .install-card {
            background: white;
            border-radius: 20px;
            box-shadow: 0 15px 35px rgba(0, 0, 0, 0.1);
            overflow: hidden;
            width: 100%;
            max-width: 600px;
        }
        
        .install-header {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 30px;
            text-align: center;
        }
        
        .install-body {
            padding: 30px;
        }
        
        .step-indicator {
            display: flex;
            justify-content: center;
            margin-bottom: 30px;
        }
        
        .step {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 10px;
            font-weight: bold;
        }
        
        .step.active {
            background: #667eea;
            color: white;
        }
        
        .step.completed {
            background: #28a745;
            color: white;
        }
        
        .step.pending {
            background: #e9ecef;
            color: #6c757d;
        }
        
        .check-item {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 10px 0;
            border-bottom: 1px solid #eee;
        }
        
        .check-item:last-child {
            border-bottom: none;
        }
    </style>
</head>
<body>
    <div class="install-container">
        <div class="install-card">
            <div class="install-header">
                <h2><i class="fas fa-shield-alt me-2"></i>Universe Security</h2>
                <p class="mb-0">Installation de l'Espace Administrateur</p>
            </div>
            
            <div class="install-body">
                <!-- Indicateur d'étapes -->
                <div class="step-indicator">
                    <div class="step <?php echo $step >= 1 ? ($step > 1 ? 'completed' : 'active') : 'pending'; ?>">1</div>
                    <div class="step <?php echo $step >= 2 ? ($step > 2 ? 'completed' : 'active') : 'pending'; ?>">2</div>
                    <div class="step <?php echo $step >= 3 ? ($step > 3 ? 'completed' : 'active') : 'pending'; ?>">3</div>
                    <div class="step <?php echo $step >= 4 ? 'completed' : 'pending'; ?>">4</div>
                </div>
                
                <?php if($error): ?>
                    <div class="alert alert-danger">
                        <i class="fas fa-exclamation-triangle me-2"></i><?php echo $error; ?>
                    </div>
                <?php endif; ?>
                
                <?php if($success): ?>
                    <div class="alert alert-success">
                        <i class="fas fa-check-circle me-2"></i><?php echo $success; ?>
                    </div>
                <?php endif; ?>
                
                <?php if($step == 1): ?>
                    <!-- Étape 1: Vérification des prérequis -->
                    <h4 class="mb-3">Étape 1: Vérification des Prérequis</h4>
                    
                    <?php foreach($checks as $name => $status): ?>
                        <div class="check-item">
                            <span><?php echo $name; ?></span>
                            <span>
                                <?php if($status): ?>
                                    <i class="fas fa-check text-success"></i>
                                <?php else: ?>
                                    <i class="fas fa-times text-danger"></i>
                                <?php endif; ?>
                            </span>
                        </div>
                    <?php endforeach; ?>
                    
                    <?php if(array_product($checks)): ?>
                        <div class="text-center mt-4">
                            <a href="?step=2" class="btn btn-primary">
                                <i class="fas fa-arrow-right me-2"></i>Continuer
                            </a>
                        </div>
                    <?php else: ?>
                        <div class="alert alert-warning mt-3">
                            Veuillez corriger les problèmes ci-dessus avant de continuer.
                        </div>
                    <?php endif; ?>
                    
                <?php elseif($step == 2): ?>
                    <!-- Étape 2: Configuration de la base de données -->
                    <h4 class="mb-3">Étape 2: Configuration de la Base de Données</h4>
                    
                    <form method="POST">
                        <div class="mb-3">
                            <label class="form-label">Serveur MySQL</label>
                            <input type="text" class="form-control" name="host" value="localhost" required>
                        </div>
                        
                        <div class="mb-3">
                            <label class="form-label">Nom de la base de données</label>
                            <input type="text" class="form-control" name="dbname" value="universe_security_admin" required>
                        </div>
                        
                        <div class="mb-3">
                            <label class="form-label">Nom d'utilisateur MySQL</label>
                            <input type="text" class="form-control" name="username" value="root" required>
                        </div>
                        
                        <div class="mb-3">
                            <label class="form-label">Mot de passe MySQL</label>
                            <input type="password" class="form-control" name="password">
                        </div>
                        
                        <div class="text-center">
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-database me-2"></i>Configurer la Base de Données
                            </button>
                        </div>
                    </form>
                    
                <?php elseif($step == 3): ?>
                    <!-- Étape 3: Création du compte administrateur -->
                    <h4 class="mb-3">Étape 3: Compte Administrateur</h4>
                    
                    <form method="POST">
                        <div class="mb-3">
                            <label class="form-label">Nom complet</label>
                            <input type="text" class="form-control" name="admin_name" required>
                        </div>
                        
                        <div class="mb-3">
                            <label class="form-label">Nom d'utilisateur</label>
                            <input type="text" class="form-control" name="admin_username" required>
                        </div>
                        
                        <div class="mb-3">
                            <label class="form-label">Email</label>
                            <input type="email" class="form-control" name="admin_email" required>
                        </div>
                        
                        <div class="mb-3">
                            <label class="form-label">Mot de passe</label>
                            <input type="password" class="form-control" name="admin_password" required>
                            <small class="text-muted">Minimum 6 caractères</small>
                        </div>
                        
                        <div class="text-center">
                            <button type="submit" name="create_admin" class="btn btn-primary">
                                <i class="fas fa-user-plus me-2"></i>Créer le Compte
                            </button>
                        </div>
                    </form>
                    
                <?php elseif($step == 4): ?>
                    <!-- Étape 4: Installation terminée -->
                    <div class="text-center">
                        <i class="fas fa-check-circle text-success" style="font-size: 4rem;"></i>
                        <h4 class="mt-3 mb-3">Installation Terminée !</h4>
                        
                        <p class="text-muted mb-4">
                            L'espace administrateur a été installé avec succès. 
                            Vous pouvez maintenant vous connecter et commencer à gérer votre site.
                        </p>
                        
                        <div class="d-grid gap-2">
                            <a href="login.php" class="btn btn-primary">
                                <i class="fas fa-sign-in-alt me-2"></i>Accéder à l'Administration
                            </a>
                            <a href="../index.php" class="btn btn-outline-secondary">
                                <i class="fas fa-home me-2"></i>Retour au Site Principal
                            </a>
                        </div>
                        
                        <div class="alert alert-info mt-4">
                            <strong>Important :</strong> Pour des raisons de sécurité, supprimez le fichier 
                            <code>install.php</code> après l'installation.
                        </div>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
