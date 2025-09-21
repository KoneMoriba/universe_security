<?php
require_once 'config/database.php';
require_once 'classes/Auth.php';
require_once 'classes/ContentManager.php';
require_once 'classes/ImageUploader.php';

$auth = new Auth();

// Vérifier si l'utilisateur est connecté
if(!$auth->isLoggedIn()) {
    header('Location: login.php');
    exit();
}

$database = new Database();
$conn = $database->getConnection();
$vendorManager = new VendorManager($conn);
$imageUploader = new ImageUploader();

$message = '';
$error = '';

// Traitement des actions
if($_SERVER['REQUEST_METHOD'] == 'POST') {
    if(isset($_POST['create_vendor'])) {
        $data = [
            'name' => trim($_POST['name']),
            'website' => trim($_POST['website']),
            'description' => trim($_POST['description']),
            'display_order' => intval($_POST['display_order']),
            'logo' => null
        ];
        
        // Gestion de l'upload du logo
        if(isset($_FILES['logo']) && $_FILES['logo']['error'] === UPLOAD_ERR_OK) {
            $upload_result = $imageUploader->uploadImage($_FILES['logo'], 'vendors');
            if($upload_result['success']) {
                $data['logo'] = $upload_result['file_path'];
            } else {
                $error = $upload_result['error'];
            }
        }
        
        if(empty($error) && $vendorManager->createVendor($data)) {
            $message = 'Vendor créé avec succès.';
        } elseif(empty($error)) {
            $error = 'Erreur lors de la création du vendor.';
        }
    }
    
    if(isset($_POST['update_vendor'])) {
        $vendor_id = $_POST['vendor_id'];
        $data = [
            'name' => trim($_POST['name']),
            'website' => trim($_POST['website']),
            'description' => trim($_POST['description']),
            'display_order' => intval($_POST['display_order']),
            'logo' => $_POST['existing_logo']
        ];
        
        // Gestion de l'upload du nouveau logo
        if(isset($_FILES['logo']) && $_FILES['logo']['error'] === UPLOAD_ERR_OK) {
            $upload_result = $imageUploader->uploadImage($_FILES['logo'], 'vendors');
            if($upload_result['success']) {
                // Supprimer l'ancien logo si il existe
                if($data['logo']) {
                    $imageUploader->deleteImage($data['logo']);
                }
                $data['logo'] = $upload_result['file_path'];
            } else {
                $error = $upload_result['error'];
            }
        }
        
        if(empty($error) && $vendorManager->updateVendor($vendor_id, $data)) {
            $message = 'Vendor mis à jour avec succès.';
        } elseif(empty($error)) {
            $error = 'Erreur lors de la mise à jour du vendor.';
        }
    }
    
    if(isset($_POST['delete_vendor'])) {
        $vendor_id = $_POST['vendor_id'];
        $vendor = $vendorManager->getVendorById($vendor_id);
        
        if($vendor && $vendorManager->deleteVendor($vendor_id)) {
            // Supprimer le logo si il existe
            if($vendor['logo']) {
                $imageUploader->deleteImage($vendor['logo']);
            }
            $message = 'Vendor supprimé avec succès.';
        } else {
            $error = 'Erreur lors de la suppression du vendor.';
        }
    }
    
    if(isset($_POST['toggle_status'])) {
        $vendor_id = $_POST['vendor_id'];
        if($vendorManager->toggleVendorStatus($vendor_id)) {
            $message = 'Statut du vendor modifié avec succès.';
        } else {
            $error = 'Erreur lors de la modification du statut.';
        }
    }
}

// Récupérer tous les vendors
$vendors = $vendorManager->getAllVendors();
$stats = $vendorManager->getVendorStats();
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Vendors/Partenaires - Universe Security Admin</title>
    
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
        
        .content-card {
            background: white;
            border-radius: 15px;
            padding: 25px;
            box-shadow: 0 5px 15px rgba(0,0,0,0.08);
            margin-bottom: 20px;
        }
        
        .stats-card {
            background: white;
            border-radius: 10px;
            padding: 20px;
            box-shadow: 0 5px 15px rgba(0,0,0,0.08);
            text-align: center;
            margin-bottom: 20px;
        }
        
        .stats-number {
            font-size: 2rem;
            font-weight: bold;
            color: var(--primary-color);
        }
        
        .vendor-card {
            border: 1px solid #e9ecef;
            border-radius: 8px;
            padding: 20px;
            margin-bottom: 15px;
            transition: all 0.3s ease;
        }
        
        .vendor-card:hover {
            box-shadow: 0 4px 12px rgba(0,0,0,0.1);
        }
        
        .vendor-logo {
            width: 80px;
            height: 80px;
            object-fit: contain;
            border-radius: 8px;
            border: 1px solid #e9ecef;
        }
        
        .vendor-inactive {
            opacity: 0.6;
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
    $current_page = 'vendors';
    // Inclure la navbar commune
    include 'includes/admin_navbar.php'; 
    ?>
    
    <!-- Main Content -->
    <div class="main-content">
        <!-- Header -->
        <div class="d-flex justify-content-between align-items-center mb-4">
            <div>
                <h1 class="h3 mb-0">Vendors/Partenaires</h1>
                <p class="text-muted">Gérez vos partenaires et fournisseurs</p>
            </div>
            <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#vendorModal">
                <i class="fas fa-plus me-2"></i>Nouveau Vendor
            </button>
        </div>
        
        <!-- Messages d'alerte -->
        <?php if($message): ?>
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                <?php echo htmlspecialchars($message); ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        <?php endif; ?>
        
        <?php if($error): ?>
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                <?php echo htmlspecialchars($error); ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        <?php endif; ?>
        
        <!-- Statistiques -->
        <div class="row mb-4">
            <div class="col-lg-6 col-md-6">
                <div class="stats-card">
                    <div class="stats-number"><?php echo $stats['total']; ?></div>
                    <div class="text-muted">Total Vendors</div>
                </div>
            </div>
            <div class="col-lg-6 col-md-6">
                <div class="stats-card">
                    <div class="stats-number text-success"><?php echo $stats['active']; ?></div>
                    <div class="text-muted">Actifs</div>
                </div>
            </div>
        </div>
        
        <!-- Liste des vendors -->
        <div class="content-card">
            <?php if(empty($vendors)): ?>
                <div class="text-center py-5">
                    <i class="fas fa-handshake fa-4x text-muted mb-3"></i>
                    <h5 class="text-muted">Aucun vendor créé</h5>
                    <p class="text-muted">Ajoutez vos premiers partenaires et fournisseurs.</p>
                </div>
            <?php else: ?>
                <?php foreach($vendors as $vendor): ?>
                    <div class="vendor-card <?php echo !$vendor['is_active'] ? 'vendor-inactive' : ''; ?>">
                        <div class="row align-items-center">
                            <div class="col-md-2">
                                <?php if($vendor['logo']): ?>
                                    <img src="../<?php echo htmlspecialchars($vendor['logo']); ?>" alt="Logo" class="vendor-logo">
                                <?php else: ?>
                                    <div class="vendor-logo bg-light d-flex align-items-center justify-content-center">
                                        <i class="fas fa-building fa-2x text-muted"></i>
                                    </div>
                                <?php endif; ?>
                            </div>
                            <div class="col-md-7">
                                <h6 class="mb-1">
                                    <?php echo htmlspecialchars($vendor['name']); ?>
                                    <?php if(!$vendor['is_active']): ?>
                                        <span class="badge bg-secondary ms-2">Inactif</span>
                                    <?php endif; ?>
                                </h6>
                                <?php if($vendor['website']): ?>
                                    <small class="text-muted">
                                        <i class="fas fa-globe me-1"></i>
                                        <a href="<?php echo htmlspecialchars($vendor['website']); ?>" target="_blank">
                                            <?php echo htmlspecialchars($vendor['website']); ?>
                                        </a>
                                    </small>
                                <?php endif; ?>
                                <?php if($vendor['description']): ?>
                                    <p class="mb-0 mt-2"><?php echo htmlspecialchars(substr($vendor['description'], 0, 150)); ?>...</p>
                                <?php endif; ?>
                                <small class="text-muted">Ordre: <?php echo $vendor['display_order']; ?></small>
                            </div>
                            <div class="col-md-3 text-end">
                                <div class="btn-group" role="group">
                                    <button type="button" class="btn btn-sm btn-outline-primary" 
                                            onclick="editVendor(<?php echo $vendor['id']; ?>)">
                                        <i class="fas fa-edit"></i>
                                    </button>
                                    <form method="POST" class="d-inline">
                                        <input type="hidden" name="vendor_id" value="<?php echo $vendor['id']; ?>">
                                        <button type="submit" name="toggle_status" class="btn btn-sm btn-outline-warning">
                                            <i class="fas fa-<?php echo $vendor['is_active'] ? 'eye-slash' : 'eye'; ?>"></i>
                                        </button>
                                    </form>
                                    <form method="POST" class="d-inline" onsubmit="return confirm('Êtes-vous sûr de vouloir supprimer ce vendor ?')">
                                        <input type="hidden" name="vendor_id" value="<?php echo $vendor['id']; ?>">
                                        <button type="submit" name="delete_vendor" class="btn btn-sm btn-outline-danger">
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
    
    <!-- Modal pour créer/modifier un vendor -->
    <div class="modal fade" id="vendorModal" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Nouveau Vendor</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <form method="POST" enctype="multipart/form-data">
                    <div class="modal-body">
                        <input type="hidden" name="vendor_id" id="vendor_id">
                        <input type="hidden" name="existing_logo" id="existing_logo">
                        
                        <div class="row">
                            <div class="col-md-8">
                                <div class="mb-3">
                                    <label class="form-label">Nom du vendor *</label>
                                    <input type="text" class="form-control" name="name" id="name" required>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="mb-3">
                                    <label class="form-label">Ordre d'affichage</label>
                                    <input type="number" class="form-control" name="display_order" id="display_order" value="0">
                                </div>
                            </div>
                        </div>
                        
                        <div class="mb-3">
                            <label class="form-label">Site web</label>
                            <input type="url" class="form-control" name="website" id="website" placeholder="https://example.com">
                        </div>
                        
                        <div class="mb-3">
                            <label class="form-label">Description</label>
                            <textarea class="form-control" name="description" id="description" rows="3"></textarea>
                        </div>
                        
                        <div class="mb-3">
                            <label class="form-label">Logo</label>
                            <input type="file" class="form-control" name="logo" accept="image/*">
                            <small class="text-muted">Formats acceptés: JPG, PNG, GIF, WebP (max 5MB)</small>
                            <div id="current_logo" class="mt-2" style="display: none;">
                                <small class="text-muted">Logo actuel:</small><br>
                                <img id="logo_preview" src="" alt="Logo actuel" style="max-width: 100px; max-height: 60px;">
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Annuler</button>
                        <button type="submit" name="create_vendor" id="submit_btn" class="btn btn-primary">Créer</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    
    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    
    <script>
        // Données des vendors pour l'édition
        const vendors = <?php echo json_encode($vendors); ?>;
        
        function editVendor(vendorId) {
            const vendor = vendors.find(v => v.id == vendorId);
            if (vendor) {
                document.getElementById('vendor_id').value = vendor.id;
                document.getElementById('name').value = vendor.name;
                document.getElementById('website').value = vendor.website || '';
                document.getElementById('description').value = vendor.description || '';
                document.getElementById('display_order').value = vendor.display_order;
                document.getElementById('existing_logo').value = vendor.logo || '';
                
                // Afficher le logo actuel si il existe
                if (vendor.logo) {
                    document.getElementById('current_logo').style.display = 'block';
                    document.getElementById('logo_preview').src = '../' + vendor.logo;
                } else {
                    document.getElementById('current_logo').style.display = 'none';
                }
                
                document.querySelector('#vendorModal .modal-title').textContent = 'Modifier le Vendor';
                document.getElementById('submit_btn').textContent = 'Mettre à jour';
                document.getElementById('submit_btn').name = 'update_vendor';
                
                new bootstrap.Modal(document.getElementById('vendorModal')).show();
            }
        }
        
        // Réinitialiser le modal lors de sa fermeture
        document.getElementById('vendorModal').addEventListener('hidden.bs.modal', function () {
            document.querySelector('#vendorModal form').reset();
            document.querySelector('#vendorModal .modal-title').textContent = 'Nouveau Vendor';
            document.getElementById('submit_btn').textContent = 'Créer';
            document.getElementById('submit_btn').name = 'create_vendor';
            document.getElementById('current_logo').style.display = 'none';
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
