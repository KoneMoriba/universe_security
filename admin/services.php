<?php
require_once 'config/database.php';
require_once 'classes/Auth.php';
require_once 'classes/ServiceManager.php';

$auth = new Auth();

// Vérifier si l'utilisateur est connecté
if(!$auth->isLoggedIn()) {
    header('Location: login.php');
    exit();
}

$serviceManager = new ServiceManager();
$message = '';
$error = '';

// Traitement des actions
if($_SERVER['REQUEST_METHOD'] == 'POST') {
    if(isset($_POST['create_service'])) {
        $data = [
            'title' => trim($_POST['title']),
            'description' => trim($_POST['description']),
            'icon' => trim($_POST['icon']),
            'display_order' => intval($_POST['display_order']),
            'is_active' => isset($_POST['is_active']) ? 1 : 0
        ];
        
        if(empty($data['title']) || empty($data['description'])) {
            $error = 'Le titre et la description sont obligatoires.';
        } else {
            if($serviceManager->createService($data, $_SESSION['admin_id'])) {
                $message = 'Service créé avec succès !';
            } else {
                $error = 'Erreur lors de la création du service.';
            }
        }
    }
    
    if(isset($_POST['update_service'])) {
        $service_id = intval($_POST['service_id']);
        $data = [
            'title' => trim($_POST['title']),
            'description' => trim($_POST['description']),
            'icon' => trim($_POST['icon']),
            'display_order' => intval($_POST['display_order']),
            'is_active' => isset($_POST['is_active']) ? 1 : 0
        ];
        
        if(empty($data['title']) || empty($data['description'])) {
            $error = 'Le titre et la description sont obligatoires.';
        } else {
            if($serviceManager->updateService($service_id, $data, $_SESSION['admin_id'])) {
                $message = 'Service mis à jour avec succès !';
            } else {
                $error = 'Erreur lors de la mise à jour du service.';
            }
        }
    }
    
    if(isset($_POST['delete_service'])) {
        $service_id = intval($_POST['service_id']);
        if($serviceManager->deleteService($service_id, $_SESSION['admin_id'])) {
            $message = 'Service supprimé avec succès !';
        } else {
            $error = 'Erreur lors de la suppression du service.';
        }
    }
    
    if(isset($_POST['toggle_status'])) {
        $service_id = intval($_POST['service_id']);
        if($serviceManager->toggleServiceStatus($service_id, $_SESSION['admin_id'])) {
            $message = 'Statut du service mis à jour avec succès !';
        } else {
            $error = 'Erreur lors de la mise à jour du statut.';
        }
    }
}

// Récupération des données
$search = isset($_GET['search']) ? trim($_GET['search']) : '';
if(!empty($search)) {
    $services = $serviceManager->searchServices($search);
} else {
    $services = $serviceManager->getAllServices();
}

$stats = $serviceManager->getServiceStats();
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gestion des Services - Universe Security Admin</title>
    
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
        
        .stats-card {
            background: white;
            border-radius: 10px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            padding: 20px;
            text-align: center;
            transition: transform 0.3s ease;
        }
        
        .stats-card:hover {
            transform: translateY(-5px);
        }
        
        .stats-number {
            font-size: 2rem;
            font-weight: bold;
            color: var(--primary-color);
        }
        
        .service-card {
            background: white;
            border-radius: 10px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            padding: 20px;
            margin-bottom: 15px;
            transition: transform 0.3s ease;
        }
        
        .service-card:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 15px rgba(0,0,0,0.15);
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
        
        .alert-custom {
            border-radius: 10px;
            border: none;
        }
        
        .modal-content {
            border-radius: 15px;
            border: none;
        }
        
        .form-control:focus {
            border-color: var(--primary-color);
            box-shadow: 0 0 0 0.2rem rgba(102, 126, 234, 0.25);
        }
        
        .table-hover tbody tr:hover {
            background-color: rgba(102, 126, 234, 0.05);
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
        }
    </style>
</head>

<body>
    <?php 
    // Définir la page actuelle pour la navbar
    $current_page = 'services';
    // Inclure la navbar commune
    include 'includes/admin_navbar.php'; 
    ?>

    <!-- Main Content -->
    <div class="main-content">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h1><i class="fas fa-cogs me-2"></i>Gestion des Services</h1>
            <button class="btn btn-gradient" data-bs-toggle="modal" data-bs-target="#createServiceModal">
                <i class="fas fa-plus me-2"></i>Nouveau Service
            </button>
        </div>

        <!-- Messages -->
        <?php if($message): ?>
            <div class="alert alert-success alert-custom alert-dismissible fade show" role="alert">
                <i class="fas fa-check-circle me-2"></i><?php echo htmlspecialchars($message); ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        <?php endif; ?>

        <?php if($error): ?>
            <div class="alert alert-danger alert-custom alert-dismissible fade show" role="alert">
                <i class="fas fa-exclamation-circle me-2"></i><?php echo htmlspecialchars($error); ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        <?php endif; ?>

        <!-- Statistiques -->
        <div class="row mb-4">
            <div class="col-md-4">
                <div class="stats-card">
                    <div class="stats-number text-primary"><?php echo $stats['total']; ?></div>
                    <div class="text-muted">Total Services</div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="stats-card">
                    <div class="stats-number text-success"><?php echo $stats['active']; ?></div>
                    <div class="text-muted">Services Actifs</div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="stats-card">
                    <div class="stats-number text-warning"><?php echo $stats['inactive']; ?></div>
                    <div class="text-muted">Services Inactifs</div>
                </div>
            </div>
        </div>
        
        <!-- Recherche -->
        <div class="content-card">
            <form method="GET" class="row g-3">
                <div class="col-md-8">
                    <input type="text" class="form-control" name="search" 
                           value="<?php echo htmlspecialchars($search); ?>" 
                           placeholder="Rechercher un service...">
                </div>
                <div class="col-md-4">
                    <button type="submit" class="btn btn-gradient w-100">
                        <i class="fas fa-search me-2"></i>Rechercher
                    </button>
                </div>
            </form>
        </div>

        <!-- Liste des services -->
        <div class="content-card">
            <h5 class="mb-3"><i class="fas fa-list me-2"></i>Liste des Services</h5>
            
            <?php if(empty($services)): ?>
                <div class="text-center py-5">
                    <i class="fas fa-cogs fa-3x text-muted mb-3"></i>
                    <h5 class="text-muted">Aucun service trouvé</h5>
                    <p class="text-muted">Commencez par créer votre premier service.</p>
                </div>
            <?php else: ?>
                <?php foreach($services as $service): ?>
                    <div class="service-card">
                        <div class="row align-items-center">
                            <div class="col-md-1 text-center">
                                <i class="<?php echo htmlspecialchars($service['icon'] ?: 'fas fa-cog'); ?> fa-2x text-primary"></i>
                            </div>
                            <div class="col-md-6">
                                <h6 class="mb-1"><?php echo htmlspecialchars($service['title']); ?></h6>
                                <p class="text-muted mb-0 small">
                                    <?php echo htmlspecialchars(substr($service['description'], 0, 100)); ?>
                                    <?php if(strlen($service['description']) > 100): ?>...<?php endif; ?>
                                </p>
                            </div>
                            <div class="col-md-2">
                                <span class="badge <?php echo $service['is_active'] ? 'bg-success' : 'bg-secondary'; ?>">
                                    <?php echo $service['is_active'] ? 'Actif' : 'Inactif'; ?>
                                </span><br>
                                <small class="text-muted">Ordre: <?php echo $service['display_order']; ?></small>
                            </div>
                            <div class="col-md-3 text-end">
                                <div class="btn-group" role="group">
                                    <button type="button" class="btn btn-sm btn-outline-primary" 
                                            onclick="editService(<?php echo $service['id']; ?>)">
                                        <i class="fas fa-edit"></i>
                                    </button>
                                    <form method="POST" class="d-inline" 
                                          onsubmit="return confirm('Changer le statut de ce service ?')">
                                        <input type="hidden" name="service_id" value="<?php echo $service['id']; ?>">
                                        <button type="submit" name="toggle_status" class="btn btn-sm btn-outline-warning">
                                            <i class="fas fa-<?php echo $service['is_active'] ? 'eye-slash' : 'eye'; ?>"></i>
                                        </button>
                                    </form>
                                    <form method="POST" class="d-inline" 
                                          onsubmit="return confirm('Êtes-vous sûr de vouloir supprimer ce service ?')">
                                        <input type="hidden" name="service_id" value="<?php echo $service['id']; ?>">
                                        <button type="submit" name="delete_service" class="btn btn-sm btn-outline-danger">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    </form>
                                </div>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
    </div>

    <!-- Modal Création Service -->
    <div class="modal fade" id="createServiceModal" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title"><i class="fas fa-plus me-2"></i>Nouveau Service</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <form method="POST">
                    <div class="modal-body">
                        <div class="row">
                            <div class="col-md-8">
                                <div class="mb-3">
                                    <label class="form-label">Titre du service *</label>
                                    <input type="text" class="form-control" name="title" required>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="mb-3">
                                    <label class="form-label">Ordre d'affichage</label>
                                    <input type="number" class="form-control" name="display_order" value="0">
                                </div>
                            </div>
                        </div>
                        
                        <div class="mb-3">
                            <label class="form-label">Description *</label>
                            <textarea class="form-control" name="description" rows="4" required></textarea>
                        </div>
                        
                        <div class="row">
                            <div class="col-md-8">
                                <div class="mb-3">
                                    <label class="form-label">Icône FontAwesome</label>
                                    <input type="text" class="form-control" name="icon" 
                                           placeholder="Ex: fas fa-shield-alt">
                                    <small class="text-muted">Consultez <a href="https://fontawesome.com/icons" target="_blank">FontAwesome</a> pour les icônes</small>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="mb-3">
                                    <div class="form-check mt-4">
                                        <input class="form-check-input" type="checkbox" name="is_active" checked>
                                        <label class="form-check-label">Service actif</label>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Annuler</button>
                        <button type="submit" name="create_service" class="btn btn-gradient">
                            <i class="fas fa-save me-2"></i>Créer le Service
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Modal Édition Service -->
    <div class="modal fade" id="editServiceModal" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title"><i class="fas fa-edit me-2"></i>Modifier le Service</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <form method="POST" id="editServiceForm">
                    <input type="hidden" name="service_id" id="edit_service_id">
                    <div class="modal-body">
                        <div class="row">
                            <div class="col-md-8">
                                <div class="mb-3">
                                    <label class="form-label">Titre du service *</label>
                                    <input type="text" class="form-control" name="title" id="edit_title" required>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="mb-3">
                                    <label class="form-label">Ordre d'affichage</label>
                                    <input type="number" class="form-control" name="display_order" id="edit_display_order">
                                </div>
                            </div>
                        </div>
                        
                        <div class="mb-3">
                            <label class="form-label">Description *</label>
                            <textarea class="form-control" name="description" rows="4" id="edit_description" required></textarea>
                        </div>
                        
                        <div class="row">
                            <div class="col-md-8">
                                <div class="mb-3">
                                    <label class="form-label">Icône FontAwesome</label>
                                    <input type="text" class="form-control" name="icon" id="edit_icon"
                                           placeholder="Ex: fas fa-shield-alt">
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="mb-3">
                                    <div class="form-check mt-4">
                                        <input class="form-check-input" type="checkbox" name="is_active" id="edit_is_active">
                                        <label class="form-check-label">Service actif</label>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Annuler</button>
                        <button type="submit" name="update_service" class="btn btn-gradient">
                            <i class="fas fa-save me-2"></i>Mettre à Jour
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    
    <script>
        // Fonction pour éditer un service
        function editService(serviceId) {
            // Récupérer les données du service via AJAX ou depuis les données PHP
            <?php if(!empty($services)): ?>
            const services = <?php echo json_encode($services); ?>;
            const service = services.find(s => s.id == serviceId);
            
            if(service) {
                document.getElementById('edit_service_id').value = service.id;
                document.getElementById('edit_title').value = service.title;
                document.getElementById('edit_description').value = service.description;
                document.getElementById('edit_icon').value = service.icon || '';
                document.getElementById('edit_display_order').value = service.display_order || 0;
                document.getElementById('edit_is_active').checked = service.is_active == 1;
                
                new bootstrap.Modal(document.getElementById('editServiceModal')).show();
            }
            <?php endif; ?>
        }
        
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