<?php
require_once 'config/database.php';
require_once 'classes/Auth.php';

$auth = new Auth();

// Vérifier si l'utilisateur est connecté
if(!$auth->isLoggedIn()) {
    header('Location: login.php');
    exit();
}

$database = new Database();
$conn = $database->getConnection();
$message = '';
$error = '';

// Traitement des actions
if($_SERVER['REQUEST_METHOD'] == 'POST') {
    if(isset($_POST['update_settings'])) {
        $settings = [
            'site_name' => trim($_POST['site_name']),
            'site_description' => trim($_POST['site_description']),
            'contact_email' => trim($_POST['contact_email']),
            'contact_phone' => trim($_POST['contact_phone']),
            'contact_address' => trim($_POST['contact_address']),
            'facebook_url' => trim($_POST['facebook_url']),
            'twitter_url' => trim($_POST['twitter_url']),
            'linkedin_url' => trim($_POST['linkedin_url']),
            'instagram_url' => trim($_POST['instagram_url']),
            'maintenance_mode' => isset($_POST['maintenance_mode']) ? 1 : 0
        ];
        
        $success_count = 0;
        foreach($settings as $key => $value) {
            $query = "UPDATE site_settings SET setting_value = :value WHERE setting_key = :key";
            $stmt = $conn->prepare($query);
            $stmt->bindParam(':value', $value);
            $stmt->bindParam(':key', $key);
            if($stmt->execute()) {
                $success_count++;
            }
        }
        
        if($success_count > 0) {
            $message = 'Paramètres mis à jour avec succès.';
        } else {
            $error = 'Erreur lors de la mise à jour des paramètres.';
        }
    }
}

// Récupérer les paramètres actuels
$query = "SELECT setting_key, setting_value FROM site_settings";
$stmt = $conn->prepare($query);
$stmt->execute();
$settings_data = $stmt->fetchAll();

$settings = [];
foreach($settings_data as $setting) {
    $settings[$setting['setting_key']] = $setting['setting_value'];
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Paramètres - Universe Security Admin</title>
    
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
        }
        
        .sidebar-menu {
            list-style: none;
            padding: 0;
            margin: 0;
        }
        
        .sidebar-menu li {
            border-bottom: 1px solid rgba(255,255,255,0.1);
        }
        
        .sidebar-menu a {
            display: block;
            padding: 15px 20px;
            color: white;
            text-decoration: none;
            transition: all 0.3s ease;
        }
        
        .sidebar-menu a:hover,
        .sidebar-menu a.active {
            background-color: rgba(255,255,255,0.1);
            padding-left: 30px;
        }
        
        .main-content {
            margin-left: var(--sidebar-width);
            padding: 20px;
            min-height: 100vh;
        }
        
        .content-card {
            background: white;
            border-radius: 15px;
            padding: 30px;
            box-shadow: 0 5px 15px rgba(0,0,0,0.08);
            margin-bottom: 20px;
        }
        
        /* Responsive Design */
        @media (max-width: 768px) {
            .sidebar {
                transform: translateX(-100%);
            }
            
            .sidebar.show {
                transform: translateX(0);
            }
            
            .main-content {
                margin-left: 0;
            }
            
            .mobile-toggle {
                display: block !important;
            }
        }
        
        .mobile-toggle {
            display: none;
            position: fixed;
            top: 20px;
            left: 20px;
            z-index: 1001;
            background: var(--primary-color);
            color: white;
            border: none;
            padding: 10px;
            border-radius: 5px;
        }
        
        .setting-section {
            border-bottom: 1px solid #eee;
            padding-bottom: 30px;
            margin-bottom: 30px;
        }
        
        .setting-section:last-child {
            border-bottom: none;
            margin-bottom: 0;
        }
        
        .section-title {
            color: var(--primary-color);
            font-weight: 600;
            margin-bottom: 20px;
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
            .mobile-toggle {
                display: block;
            }
        }
    </style>
</head>
<body>
    <?php 
    // Définir la page actuelle pour la navbar
    $current_page = 'settings';
    // Inclure la navbar commune
    include 'includes/admin_navbar.php'; 
    ?>
    
    <!-- Main Content -->
    <div class="main-content">
        <!-- Header -->
        <div class="d-flex justify-content-between align-items-center mb-4">
            <div>
                <h1 class="h3 mb-0">Paramètres du Site</h1>
                <p class="text-muted">Configurez les paramètres généraux de votre site</p>
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
        
        <!-- Formulaire des paramètres -->
        <div class="content-card">
            <form method="POST">
                <!-- Informations générales -->
                <div class="setting-section">
                    <h5 class="section-title"><i class="fas fa-info-circle me-2"></i>Informations Générales</h5>
                    
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="site_name" class="form-label">Nom du site</label>
                            <input type="text" class="form-control" id="site_name" name="site_name" 
                                   value="<?php echo htmlspecialchars($settings['site_name'] ?? 'Universe Security'); ?>">
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="contact_email" class="form-label">Email de contact</label>
                            <input type="email" class="form-control" id="contact_email" name="contact_email" 
                                   value="<?php echo htmlspecialchars($settings['contact_email'] ?? ''); ?>">
                        </div>
                    </div>
                    
                    <div class="mb-3">
                        <label for="site_description" class="form-label">Description du site</label>
                        <textarea class="form-control" id="site_description" name="site_description" rows="3"><?php echo htmlspecialchars($settings['site_description'] ?? ''); ?></textarea>
                    </div>
                </div>
                
                <!-- Informations de contact -->
                <div class="setting-section">
                    <h5 class="section-title"><i class="fas fa-phone me-2"></i>Informations de Contact</h5>
                    
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="contact_phone" class="form-label">Téléphone</label>
                            <input type="tel" class="form-control" id="contact_phone" name="contact_phone" 
                                   value="<?php echo htmlspecialchars($settings['contact_phone'] ?? ''); ?>">
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="contact_address" class="form-label">Adresse</label>
                            <input type="text" class="form-control" id="contact_address" name="contact_address" 
                                   value="<?php echo htmlspecialchars($settings['contact_address'] ?? ''); ?>">
                        </div>
                    </div>
                </div>
                
                <!-- Réseaux sociaux -->
                <div class="setting-section">
                    <h5 class="section-title"><i class="fas fa-share-alt me-2"></i>Réseaux Sociaux</h5>
                    
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="facebook_url" class="form-label">
                                <i class="fab fa-facebook text-primary me-2"></i>Facebook
                            </label>
                            <input type="url" class="form-control" id="facebook_url" name="facebook_url" 
                                   value="<?php echo htmlspecialchars($settings['facebook_url'] ?? ''); ?>" 
                                   placeholder="https://facebook.com/votrepage">
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="twitter_url" class="form-label">
                                <i class="fab fa-twitter text-info me-2"></i>Twitter
                            </label>
                            <input type="url" class="form-control" id="twitter_url" name="twitter_url" 
                                   value="<?php echo htmlspecialchars($settings['twitter_url'] ?? ''); ?>" 
                                   placeholder="https://twitter.com/votrepage">
                        </div>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="linkedin_url" class="form-label">
                                <i class="fab fa-linkedin text-primary me-2"></i>LinkedIn
                            </label>
                            <input type="url" class="form-control" id="linkedin_url" name="linkedin_url" 
                                   value="<?php echo htmlspecialchars($settings['linkedin_url'] ?? ''); ?>" 
                                   placeholder="https://linkedin.com/company/votrepage">
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="instagram_url" class="form-label">
                                <i class="fab fa-instagram text-danger me-2"></i>Instagram
                            </label>
                            <input type="url" class="form-control" id="instagram_url" name="instagram_url" 
                                   value="<?php echo htmlspecialchars($settings['instagram_url'] ?? ''); ?>" 
                                   placeholder="https://instagram.com/votrepage">
                        </div>
                    </div>
                </div>
                
                <!-- Options avancées -->
                <div class="setting-section">
                    <h5 class="section-title"><i class="fas fa-tools me-2"></i>Options Avancées</h5>
                    
                    <div class="form-check form-switch">
                        <input class="form-check-input" type="checkbox" id="maintenance_mode" name="maintenance_mode" 
                               <?php echo (isset($settings['maintenance_mode']) && $settings['maintenance_mode']) ? 'checked' : ''; ?>>
                        <label class="form-check-label" for="maintenance_mode">
                            <strong>Mode Maintenance</strong>
                            <br><small class="text-muted">Active le mode maintenance pour le site principal</small>
                        </label>
                    </div>
                </div>
                
                <!-- Boutons d'action -->
                <div class="d-flex justify-content-end">
                    <button type="submit" name="update_settings" class="btn btn-primary btn-lg">
                        <i class="fas fa-save me-2"></i>Sauvegarder les Paramètres
                    </button>
                </div>
            </form>
        </div>
        
        <!-- Informations système -->
        <div class="content-card">
            <h5 class="section-title"><i class="fas fa-server me-2"></i>Informations Système</h5>
            
            <div class="row">
                <div class="col-md-6">
                    <table class="table table-borderless">
                        <tr>
                            <td><strong>Version PHP:</strong></td>
                            <td><?php echo PHP_VERSION; ?></td>
                        </tr>
                        <tr>
                            <td><strong>Serveur Web:</strong></td>
                            <td><?php echo $_SERVER['SERVER_SOFTWARE'] ?? 'Inconnu'; ?></td>
                        </tr>
                        <tr>
                            <td><strong>Base de données:</strong></td>
                            <td>MySQL/MariaDB</td>
                        </tr>
                    </table>
                </div>
                <div class="col-md-6">
                    <table class="table table-borderless">
                        <tr>
                            <td><strong>Espace disque uploads:</strong></td>
                            <td>
                                <?php 
                                $upload_dir = '../uploads/';
                                if(is_dir($upload_dir)) {
                                    $size = 0;
                                    $files = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($upload_dir));
                                    foreach($files as $file) {
                                        $size += $file->getSize();
                                    }
                                    echo number_format($size / 1024 / 1024, 2) . ' MB';
                                } else {
                                    echo 'Dossier non trouvé';
                                }
                                ?>
                            </td>
                        </tr>
                        <tr>
                            <td><strong>Limite upload:</strong></td>
                            <td><?php echo ini_get('upload_max_filesize'); ?></td>
                        </tr>
                        <tr>
                            <td><strong>Mémoire PHP:</strong></td>
                            <td><?php echo ini_get('memory_limit'); ?></td>
                        </tr>
                    </table>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    
    <script>
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
