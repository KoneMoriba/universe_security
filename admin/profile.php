<?php
require_once 'config/database.php';
require_once 'classes/Auth.php';

$auth = new Auth();

// Vérifier si l'utilisateur est connecté
if(!$auth->isLoggedIn()) {
    header('Location: login.php');
    exit();
}

$message = '';
$error = '';

// Récupérer les informations de l'utilisateur
$database = new Database();
$conn = $database->getConnection();

$query = "SELECT * FROM admins WHERE id = :id";
$stmt = $conn->prepare($query);
$stmt->bindParam(':id', $_SESSION['admin_id']);
$stmt->execute();
$user = $stmt->fetch();

// Traitement des actions
if($_SERVER['REQUEST_METHOD'] == 'POST') {
    if(isset($_POST['update_profile'])) {
        $full_name = trim($_POST['full_name']);
        $email = trim($_POST['email']);
        $username = trim($_POST['username']);
        
        // Validation
        $errors = [];
        
        if(empty($full_name)) {
            $errors[] = 'Le nom complet est requis.';
        }
        
        if(empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $errors[] = 'Un email valide est requis.';
        }
        
        if(empty($username)) {
            $errors[] = 'Le nom d\'utilisateur est requis.';
        }
        
        // Vérifier si l'email ou username existe déjà (sauf pour l'utilisateur actuel)
        $query = "SELECT id FROM admins WHERE (email = :email OR username = :username) AND id != :current_id";
        $stmt = $conn->prepare($query);
        $stmt->bindParam(':email', $email);
        $stmt->bindParam(':username', $username);
        $stmt->bindParam(':current_id', $_SESSION['admin_id']);
        $stmt->execute();
        
        if($stmt->rowCount() > 0) {
            $errors[] = 'Cet email ou nom d\'utilisateur est déjà utilisé.';
        }
        
        if(empty($errors)) {
            $query = "UPDATE admins SET full_name = :full_name, email = :email, username = :username, updated_at = CURRENT_TIMESTAMP WHERE id = :id";
            $stmt = $conn->prepare($query);
            $stmt->bindParam(':full_name', $full_name);
            $stmt->bindParam(':email', $email);
            $stmt->bindParam(':username', $username);
            $stmt->bindParam(':id', $_SESSION['admin_id']);
            
            if($stmt->execute()) {
                // Mettre à jour les variables de session
                $_SESSION['admin_name'] = $full_name;
                $_SESSION['admin_email'] = $email;
                $_SESSION['admin_username'] = $username;
                
                $message = 'Profil mis à jour avec succès.';
                
                // Recharger les données utilisateur
                $query = "SELECT * FROM admins WHERE id = :id";
                $stmt = $conn->prepare($query);
                $stmt->bindParam(':id', $_SESSION['admin_id']);
                $stmt->execute();
                $user = $stmt->fetch();
            } else {
                $error = 'Erreur lors de la mise à jour du profil.';
            }
        } else {
            $error = implode('<br>', $errors);
        }
    }
    
    if(isset($_POST['change_password'])) {
        $current_password = $_POST['current_password'];
        $new_password = $_POST['new_password'];
        $confirm_password = $_POST['confirm_password'];
        
        // Validation
        $errors = [];
        
        if(empty($current_password) || empty($new_password) || empty($confirm_password)) {
            $errors[] = 'Tous les champs sont requis.';
        }
        
        if($new_password !== $confirm_password) {
            $errors[] = 'Les nouveaux mots de passe ne correspondent pas.';
        }
        
        if(strlen($new_password) < 6) {
            $errors[] = 'Le nouveau mot de passe doit contenir au moins 6 caractères.';
        }
        
        if(empty($errors)) {
            if($auth->changePassword($_SESSION['admin_id'], $current_password, $new_password)) {
                $message = 'Mot de passe modifié avec succès.';
            } else {
                $error = 'Mot de passe actuel incorrect.';
            }
        } else {
            $error = implode('<br>', $errors);
        }
    }
}

// Récupérer les dernières activités de l'utilisateur
$query = "SELECT * FROM admin_logs WHERE admin_id = :admin_id ORDER BY created_at DESC LIMIT 10";
$stmt = $conn->prepare($query);
$stmt->bindParam(':admin_id', $_SESSION['admin_id']);
$stmt->execute();
$recent_activities = $stmt->fetchAll();
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Mon Profil - Universe Security Admin</title>
    
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
        
        .sidebar-header {
            padding: 20px;
            border-bottom: 1px solid rgba(255,255,255,0.1);
            text-align: center;
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
        
        .main-content {
            margin-left: var(--sidebar-width);
            padding: 20px;
            transition: all 0.3s ease;
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
        
        .profile-card {
            background: white;
            border-radius: 15px;
            padding: 30px;
            box-shadow: 0 5px 15px rgba(0,0,0,0.08);
            margin-bottom: 20px;
        }
        
        .profile-avatar {
            width: 120px;
            height: 120px;
            border-radius: 50%;
            background: linear-gradient(135deg, var(--primary-color) 0%, var(--secondary-color) 100%);
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 48px;
            color: white;
            margin: 0 auto 20px;
        }
        
        .info-card {
            background: white;
            border-radius: 15px;
            padding: 25px;
            box-shadow: 0 5px 15px rgba(0,0,0,0.08);
            margin-bottom: 20px;
        }
        
        .activity-item {
            padding: 10px 0;
            border-bottom: 1px solid #eee;
        }
        
        .activity-item:last-child {
            border-bottom: none;
        }
        
        .role-badge {
            padding: 8px 16px;
            border-radius: 20px;
            font-size: 0.9rem;
            font-weight: 600;
        }
        
        .role-super_admin {
            background-color: #dc3545;
            color: white;
        }
        
        .role-admin {
            background-color: #007bff;
            color: white;
        }
        
        .role-moderator {
            background-color: #28a745;
            color: white;
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
    $current_page = 'profile';
    // Inclure la navbar commune
    include 'includes/admin_navbar.php'; 
    ?>
    
    <!-- Main Content -->
    <div class="main-content">
        <!-- Header -->
        <div class="d-flex justify-content-between align-items-center mb-4">
            <div>
                <h1 class="h3 mb-0">Mon Profil</h1>
                <p class="text-muted">Gérez vos informations personnelles et paramètres</p>
            </div>
        </div>
        
        <!-- Messages -->
        <?php if($message): ?>
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                <i class="fas fa-check-circle me-2"></i><?php echo $message; ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        <?php endif; ?>
        
        <?php if($error): ?>
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                <i class="fas fa-exclamation-triangle me-2"></i><?php echo $error; ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        <?php endif; ?>
        
        <div class="row">
            <!-- Profil principal -->
            <div class="col-lg-4">
                <div class="profile-card text-center">
                    <div class="profile-avatar">
                        <i class="fas fa-user"></i>
                    </div>
                    
                    <h4 class="mb-2"><?php echo htmlspecialchars($user['full_name']); ?></h4>
                    <p class="text-muted mb-3">@<?php echo htmlspecialchars($user['username']); ?></p>
                    
                    <span class="role-badge role-<?php echo $user['role']; ?>">
                        <?php echo ucfirst(str_replace('_', ' ', $user['role'])); ?>
                    </span>
                    
                    <hr class="my-4">
                    
                    <div class="row text-center">
                        <div class="col-6">
                            <strong><?php echo date('d/m/Y', strtotime($user['created_at'])); ?></strong>
                            <p class="text-muted small mb-0">Membre depuis</p>
                        </div>
                        <div class="col-6">
                            <strong>
                                <?php echo $user['last_login'] ? date('d/m/Y', strtotime($user['last_login'])) : 'Jamais'; ?>
                            </strong>
                            <p class="text-muted small mb-0">Dernière connexion</p>
                        </div>
                    </div>
                </div>
                
                <!-- Activité récente -->
                <div class="info-card">
                    <h5 class="mb-3">Activité Récente</h5>
                    
                    <?php if(empty($recent_activities)): ?>
                        <p class="text-muted text-center">Aucune activité récente</p>
                    <?php else: ?>
                        <?php foreach($recent_activities as $activity): ?>
                            <div class="activity-item">
                                <div class="d-flex justify-content-between align-items-start">
                                    <div>
                                        <strong><?php echo htmlspecialchars($activity['action']); ?></strong>
                                        <?php if($activity['table_name']): ?>
                                            <br><small class="text-muted"><?php echo htmlspecialchars($activity['table_name']); ?></small>
                                        <?php endif; ?>
                                    </div>
                                    <small class="text-muted">
                                        <?php echo date('d/m H:i', strtotime($activity['created_at'])); ?>
                                    </small>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>
            </div>
            
            <!-- Formulaires -->
            <div class="col-lg-8">
                <!-- Modifier le profil -->
                <div class="info-card">
                    <h5 class="mb-3">Informations Personnelles</h5>
                    
                    <form method="POST">
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="full_name" class="form-label">Nom complet</label>
                                <input type="text" class="form-control" id="full_name" name="full_name" 
                                       value="<?php echo htmlspecialchars($user['full_name']); ?>" required>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label for="username" class="form-label">Nom d'utilisateur</label>
                                <input type="text" class="form-control" id="username" name="username" 
                                       value="<?php echo htmlspecialchars($user['username']); ?>" required>
                            </div>
                        </div>
                        
                        <div class="mb-3">
                            <label for="email" class="form-label">Email</label>
                            <input type="email" class="form-control" id="email" name="email" 
                                   value="<?php echo htmlspecialchars($user['email']); ?>" required>
                        </div>
                        
                        <div class="mb-3">
                            <label class="form-label">Rôle</label>
                            <input type="text" class="form-control" 
                                   value="<?php echo ucfirst(str_replace('_', ' ', $user['role'])); ?>" 
                                   readonly>
                            <small class="text-muted">Le rôle ne peut être modifié que par un super administrateur</small>
                        </div>
                        
                        <button type="submit" name="update_profile" class="btn btn-primary">
                            <i class="fas fa-save me-2"></i>Sauvegarder les modifications
                        </button>
                    </form>
                </div>
                
                <!-- Changer le mot de passe -->
                <div class="info-card">
                    <h5 class="mb-3">Changer le Mot de Passe</h5>
                    
                    <form method="POST">
                        <div class="mb-3">
                            <label for="current_password" class="form-label">Mot de passe actuel</label>
                            <input type="password" class="form-control" id="current_password" name="current_password" required>
                        </div>
                        
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="new_password" class="form-label">Nouveau mot de passe</label>
                                <input type="password" class="form-control" id="new_password" name="new_password" required>
                                <small class="text-muted">Minimum 6 caractères</small>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label for="confirm_password" class="form-label">Confirmer le nouveau mot de passe</label>
                                <input type="password" class="form-control" id="confirm_password" name="confirm_password" required>
                            </div>
                        </div>
                        
                        <button type="submit" name="change_password" class="btn btn-warning">
                            <i class="fas fa-key me-2"></i>Changer le mot de passe
                        </button>
                    </form>
                </div>
                
                <!-- Informations du compte -->
                <div class="info-card">
                    <h5 class="mb-3">Informations du Compte</h5>
                    
                    <div class="row">
                        <div class="col-md-6">
                            <p><strong>ID du compte:</strong> #<?php echo $user['id']; ?></p>
                            <p><strong>Statut:</strong> 
                                <span class="badge <?php echo $user['is_active'] ? 'bg-success' : 'bg-danger'; ?>">
                                    <?php echo $user['is_active'] ? 'Actif' : 'Inactif'; ?>
                                </span>
                            </p>
                        </div>
                        <div class="col-md-6">
                            <p><strong>Compte créé:</strong> <?php echo date('d/m/Y à H:i', strtotime($user['created_at'])); ?></p>
                            <p><strong>Dernière mise à jour:</strong> <?php echo date('d/m/Y à H:i', strtotime($user['updated_at'])); ?></p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    
    <script>
        // Validation du formulaire de changement de mot de passe
        document.querySelector('form').addEventListener('submit', function(e) {
            const newPassword = document.getElementById('new_password').value;
            const confirmPassword = document.getElementById('confirm_password').value;
            
            if(newPassword !== confirmPassword) {
                e.preventDefault();
                alert('Les nouveaux mots de passe ne correspondent pas.');
            }
        });
        
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
