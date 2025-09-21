<?php
session_start();
require_once 'config/database.php';
require_once 'classes/Auth.php';

$auth = new Auth();

// Vérifier si l'utilisateur est connecté
if(!$auth->isLoggedIn()) {
    header('Location: login.php');
    exit();
}

// Vérifier si l'utilisateur a les droits (Super Admin ou Admin)
$current_user_role = $_SESSION['admin_role'] ?? '';
if($current_user_role !== 'Super Admin' && $current_user_role !== 'Admin' && 
   $current_user_role !== 'super_admin' && $current_user_role !== 'admin') {
    header('Location: dashboard.php?error=access_denied');
    exit();
}

$database = new Database();
$conn = $database->getConnection();

$message = '';
$error = '';

// Traitement des actions
if($_SERVER['REQUEST_METHOD'] == 'POST') {
    if(isset($_POST['create_user'])) {
        $username = trim($_POST['username']);
        $email = trim($_POST['email']);
        $password = $_POST['password'];
        $confirm_password = $_POST['confirm_password'];
        $full_name = trim($_POST['full_name']);
        $role = $_POST['role'];
        
        if(empty($username) || empty($email) || empty($password) || empty($full_name)) {
            $error = 'Veuillez remplir tous les champs.';
        } elseif($password !== $confirm_password) {
            $error = 'Les mots de passe ne correspondent pas.';
        } elseif(strlen($password) < 6) {
            $error = 'Le mot de passe doit contenir au moins 6 caractères.';
        } else {
            if($auth->register($username, $email, $password, $full_name, $role)) {
                $message = 'Utilisateur créé avec succès !';
                
                // Log de l'activité
                $query = "INSERT INTO admin_logs (admin_id, action, table_name, record_id, ip_address, user_agent) 
                          VALUES (:admin_id, :action, :table_name, :record_id, :ip_address, :user_agent)";
                $stmt = $conn->prepare($query);
                $stmt->execute([
                    ':admin_id' => $_SESSION['admin_id'],
                    ':action' => 'Création utilisateur: ' . $username,
                    ':table_name' => 'admins',
                    ':record_id' => null,
                    ':ip_address' => $_SERVER['REMOTE_ADDR'] ?? '',
                    ':user_agent' => $_SERVER['HTTP_USER_AGENT'] ?? ''
                ]);
            } else {
                $error = 'Ce nom d\'utilisateur ou email existe déjà.';
            }
        }
    }
    
    if(isset($_POST['delete_user'])) {
        $user_id = intval($_POST['user_id']);
        
        // Ne pas permettre la suppression de son propre compte
        if($user_id == $_SESSION['admin_id']) {
            $error = 'Vous ne pouvez pas supprimer votre propre compte.';
        } else {
            $query = "DELETE FROM admins WHERE id = :id";
            $stmt = $conn->prepare($query);
            if($stmt->execute([':id' => $user_id])) {
                $message = 'Utilisateur supprimé avec succès.';
                
                // Log de l'activité
                $query = "INSERT INTO admin_logs (admin_id, action, table_name, record_id, ip_address, user_agent) 
                          VALUES (:admin_id, :action, :table_name, :record_id, :ip_address, :user_agent)";
                $stmt = $conn->prepare($query);
                $stmt->execute([
                    ':admin_id' => $_SESSION['admin_id'],
                    ':action' => 'Suppression utilisateur ID: ' . $user_id,
                    ':table_name' => 'admins',
                    ':record_id' => $user_id,
                    ':ip_address' => $_SERVER['REMOTE_ADDR'] ?? '',
                    ':user_agent' => $_SERVER['HTTP_USER_AGENT'] ?? ''
                ]);
            } else {
                $error = 'Erreur lors de la suppression.';
            }
        }
    }
}

// Récupérer tous les utilisateurs
$query = "SELECT id, username, email, full_name, role, created_at, last_login FROM admins ORDER BY created_at DESC";
$stmt = $conn->prepare($query);
$stmt->execute();
$users = $stmt->fetchAll();
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gestion des Utilisateurs - Universe Security Admin</title>
    
    <!-- Favicon -->
    <link href="../img/logo universe security.jpg" rel="icon">
    
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Font Awesome -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    
    <style>
        :root {
            --primary-color: #667eea;
            --secondary-color: #764ba2;
            --sidebar-width: 250px;
        }
        
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background-color: #f8f9fa;
        }
        
        .sidebar {
            position: fixed;
            top: 0;
            left: 0;
            height: 100vh;
            width: var(--sidebar-width);
            background: linear-gradient(135deg, var(--primary-color) 0%, var(--secondary-color) 100%);
            color: white;
            z-index: 1000;
            transition: all 0.3s ease;
            overflow-y: auto;
        }
        
        .main-content {
            margin-left: var(--sidebar-width);
            padding: 20px;
            transition: all 0.3s ease;
        }
        
        .content-card {
            background: white;
            border-radius: 10px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            padding: 20px;
            margin-bottom: 20px;
        }
        
        .user-card {
            background: white;
            border-radius: 10px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            padding: 20px;
            margin-bottom: 15px;
            transition: transform 0.3s ease;
        }
        
        .user-card:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 15px rgba(0,0,0,0.15);
        }
        
        .role-badge {
            font-size: 0.8rem;
            padding: 0.3rem 0.6rem;
        }
        
        .sidebar-header {
            padding: 20px;
            border-bottom: 1px solid rgba(255,255,255,0.1);
        }
        
        .sidebar-menu {
            padding: 0;
            margin: 0;
            list-style: none;
        }
        
        .sidebar-menu li {
            border-bottom: 1px solid rgba(255,255,255,0.1);
        }
        
        .sidebar-menu a {
            display: block;
            padding: 15px 20px;
            color: rgba(255,255,255,0.8);
            text-decoration: none;
            transition: all 0.3s ease;
        }
        
        .sidebar-menu a:hover,
        .sidebar-menu a.active {
            background: rgba(255,255,255,0.1);
            color: white;
            padding-left: 30px;
        }
        
        .sidebar-menu i {
            width: 20px;
            margin-right: 10px;
        }
        
        .logo {
            width: 60px;
            height: 60px;
            object-fit: cover;
            border-radius: 50%;
            border: 2px solid rgba(255,255,255,0.3);
        }
        
        .mobile-toggle {
            position: fixed;
            top: 15px;
            left: 15px;
            z-index: 1001;
            background: var(--primary-color);
            color: white;
            border: none;
            border-radius: 5px;
            padding: 10px;
            display: none;
        }
        
        .btn-gradient {
            background: linear-gradient(135deg, var(--primary-color) 0%, var(--secondary-color) 100%);
            border: none;
            color: white;
        }
        
        .btn-gradient:hover {
            background: linear-gradient(135deg, var(--secondary-color) 0%, var(--primary-color) 100%);
            color: white;
        }
        
        @media (max-width: 768px) {
            .sidebar {
                transform: translateX(-100%);
            }
            
            .main-content {
                margin-left: 0;
            }
            
            .sidebar.show {
                transform: translateX(0);
            }
            
            .mobile-toggle {
                display: block;
            }
        }
    </style>
</head>

<body>
    <?php 
    // Définir la page actuelle pour la navbar
    $current_page = 'users';
    // Inclure la navbar commune
    include 'includes/admin_navbar.php'; 
    ?>

    <!-- Main Content -->
    <div class="main-content">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h1><i class="fas fa-users me-2"></i>Gestion des Utilisateurs</h1>
            <button class="btn btn-gradient" data-bs-toggle="modal" data-bs-target="#createUserModal">
                <i class="fas fa-user-plus me-2"></i>Nouvel Utilisateur
            </button>
        </div>

        <!-- Messages -->
        <?php if($message): ?>
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                <i class="fas fa-check-circle me-2"></i><?php echo htmlspecialchars($message); ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        <?php endif; ?>

        <?php if($error): ?>
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                <i class="fas fa-exclamation-circle me-2"></i><?php echo htmlspecialchars($error); ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        <?php endif; ?>

        <!-- Liste des utilisateurs -->
        <div class="content-card">
            <h5 class="mb-3"><i class="fas fa-list me-2"></i>Utilisateurs Administrateurs</h5>
            
            <?php if(empty($users)): ?>
                <div class="text-center py-5">
                    <i class="fas fa-users fa-3x text-muted mb-3"></i>
                    <h5 class="text-muted">Aucun utilisateur trouvé</h5>
                </div>
            <?php else: ?>
                <?php foreach($users as $user): ?>
                    <div class="user-card">
                        <div class="row align-items-center">
                            <div class="col-md-1 text-center">
                                <i class="fas fa-user-circle fa-2x text-primary"></i>
                            </div>
                            <div class="col-md-4">
                                <h6 class="mb-1"><?php echo htmlspecialchars($user['full_name']); ?></h6>
                                <p class="text-muted mb-0 small">
                                    <i class="fas fa-user me-1"></i><?php echo htmlspecialchars($user['username']); ?><br>
                                    <i class="fas fa-envelope me-1"></i><?php echo htmlspecialchars($user['email']); ?>
                                </p>
                            </div>
                            <div class="col-md-2">
                                <?php
                                $badge_class = 'bg-secondary';
                                if($user['role'] == 'Super Admin') $badge_class = 'bg-danger';
                                elseif($user['role'] == 'Admin') $badge_class = 'bg-primary';
                                elseif($user['role'] == 'Modérateur') $badge_class = 'bg-warning';
                                ?>
                                <span class="badge role-badge <?php echo $badge_class; ?>">
                                    <?php echo htmlspecialchars($user['role']); ?>
                                </span>
                            </div>
                            <div class="col-md-2">
                                <small class="text-muted">
                                    Créé le: <?php echo date('d/m/Y', strtotime($user['created_at'])); ?><br>
                                    <?php if($user['last_login']): ?>
                                        Dernière connexion: <?php echo date('d/m/Y H:i', strtotime($user['last_login'])); ?>
                                    <?php else: ?>
                                        Jamais connecté
                                    <?php endif; ?>
                                </small>
                            </div>
                            <div class="col-md-3 text-end">
                                <?php if($user['id'] != $_SESSION['admin_id']): ?>
                                    <form method="POST" class="d-inline" 
                                          onsubmit="return confirm('Êtes-vous sûr de vouloir supprimer cet utilisateur ?')">
                                        <input type="hidden" name="user_id" value="<?php echo $user['id']; ?>">
                                        <button type="submit" name="delete_user" class="btn btn-sm btn-outline-danger">
                                            <i class="fas fa-trash"></i> Supprimer
                                        </button>
                                    </form>
                                <?php else: ?>
                                    <span class="badge bg-info">Vous</span>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
    </div>

    <!-- Modal Création Utilisateur -->
    <div class="modal fade" id="createUserModal" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title"><i class="fas fa-user-plus me-2"></i>Créer un Nouvel Utilisateur</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <form method="POST">
                    <div class="modal-body">
                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label class="form-label">Nom complet *</label>
                                    <input type="text" class="form-control" name="full_name" required>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label class="form-label">Nom d'utilisateur *</label>
                                    <input type="text" class="form-control" name="username" required>
                                </div>
                            </div>
                        </div>
                        
                        <div class="mb-3">
                            <label class="form-label">Email *</label>
                            <input type="email" class="form-control" name="email" required>
                        </div>
                        
                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label class="form-label">Mot de passe *</label>
                                    <input type="password" class="form-control" name="password" required>
                                    <small class="text-muted">Minimum 6 caractères</small>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label class="form-label">Confirmer le mot de passe *</label>
                                    <input type="password" class="form-control" name="confirm_password" required>
                                </div>
                            </div>
                        </div>
                        
                        <div class="mb-3">
                            <label class="form-label">Rôle *</label>
                            <select class="form-control" name="role" required>
                                <?php if($current_user_role == 'Super Admin' || $current_user_role == 'super_admin'): ?>
                                    <option value="Super Admin">Super Admin</option>
                                <?php endif; ?>
                                <option value="Admin">Admin</option>
                                <option value="Modérateur">Modérateur</option>
                            </select>
                            <small class="text-muted">
                                Super Admin: Tous les droits | Admin: Gestion complète | Modérateur: Modération uniquement
                            </small>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Annuler</button>
                        <button type="submit" name="create_user" class="btn btn-gradient">
                            <i class="fas fa-save me-2"></i>Créer l'Utilisateur
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    
    <script>
        // Auto-hide alerts
        setTimeout(function() {
            const alerts = document.querySelectorAll('.alert');
            alerts.forEach(alert => {
                if(alert.classList.contains('show')) {
                    bootstrap.Alert.getOrCreateInstance(alert).close();
                }
            });
        }, 5000);
        
        // Fonctions pour la sidebar mobile
        function toggleSidebar() {
            document.getElementById('sidebar').classList.toggle('show');
        }
        
        // Fermer la sidebar en cliquant en dehors sur mobile
        document.addEventListener('click', function(event) {
            const sidebar = document.getElementById('sidebar');
            const toggle = document.querySelector('.mobile-toggle');
            
            if (window.innerWidth <= 768 && 
                !sidebar.contains(event.target) && 
                !toggle.contains(event.target) && 
                sidebar.classList.contains('show')) {
                sidebar.classList.remove('show');
            }
        });
    </script>
</body>
</html>
