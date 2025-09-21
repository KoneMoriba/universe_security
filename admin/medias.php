<?php
require_once 'config/database.php';
require_once 'classes/Auth.php';
require_once 'classes/MediaManager.php';
// Utiliser SimpleFileUploader si GD n'est pas disponible
if (extension_loaded('gd')) {
    require_once 'classes/FileUploader.php';
    $fileUploader = new FileUploader();
} else {
    require_once 'classes/SimpleFileUploader.php';
    $fileUploader = new SimpleFileUploader();
}

$auth = new Auth();

// Vérifier si l'utilisateur est connecté
if(!$auth->isLoggedIn()) {
    header('Location: login.php');
    exit();
}

$mediaManager = new MediaManager();
$message = '';
$error = '';

// Avertissement si GD n'est pas disponible
if (!extension_loaded('gd')) {
    $error = 'Extension GD non disponible : les images ne seront pas redimensionnées automatiquement. <a href="check_requirements.php">Voir les prérequis</a>';
}

// Traitement des actions
if($_SERVER['REQUEST_METHOD'] == 'POST') {
    if(isset($_POST['create_media'])) {
        $file_path = '';
        $file_type = '';
        
        // Gérer l'upload de fichier
        if(isset($_FILES['media_file']) && $_FILES['media_file']['error'] == UPLOAD_ERR_OK) {
            $media_type = $_POST['media_type'];
            $folder = $media_type === 'photo' ? 'photos' : 'videos';
            
            if($media_type === 'photo') {
                $upload_result = $fileUploader->uploadImage($_FILES['media_file'], $folder);
            } else {
                $upload_result = $fileUploader->uploadVideo($_FILES['media_file'], $folder);
            }
            
            if($upload_result['success']) {
                $file_path = $upload_result['path'];
                $file_type = pathinfo($_FILES['media_file']['name'], PATHINFO_EXTENSION);
            } else {
                $error = $upload_result['message'];
            }
        }
        
        if(!$error) {
            $data = [
                'title' => trim($_POST['title']),
                'description' => trim($_POST['description']),
                'file_path' => $file_path,
                'file_type' => $file_type,
                'media_type' => $_POST['media_type'],
                'alt_text' => trim($_POST['alt_text'] ?? ''),
                'is_active' => isset($_POST['is_active']) ? 1 : 0,
                'display_order' => intval($_POST['display_order'] ?? 0)
            ];
            
            if($mediaManager->createMedia($data, $_SESSION['admin_id'])) {
                $message = 'Média créé avec succès.';
            } else {
                $error = 'Erreur lors de la création du média.';
            }
        }
    }
    
    if(isset($_POST['update_media'])) {
        $media_id = $_POST['media_id'];
        $file_path = $_POST['existing_file_path'];
        $file_type = $_POST['existing_file_type'];
        
        // Gérer l'upload de nouveau fichier si fourni
        if(isset($_FILES['media_file']) && $_FILES['media_file']['error'] == UPLOAD_ERR_OK) {
            $media_type = $_POST['media_type'];
            $folder = $media_type === 'photo' ? 'photos' : 'videos';
            
            if($media_type === 'photo') {
                $upload_result = $fileUploader->uploadImage($_FILES['media_file'], $folder);
            } else {
                $upload_result = $fileUploader->uploadVideo($_FILES['media_file'], $folder);
            }
            
            if($upload_result['success']) {
                $file_path = $upload_result['path'];
                $file_type = pathinfo($_FILES['media_file']['name'], PATHINFO_EXTENSION);
            } else {
                $error = $upload_result['message'];
            }
        }
        
        if(!$error) {
            $data = [
                'title' => trim($_POST['title']),
                'description' => trim($_POST['description']),
                'file_path' => $file_path,
                'file_type' => $file_type,
                'media_type' => $_POST['media_type'],
                'alt_text' => trim($_POST['alt_text'] ?? ''),
                'is_active' => isset($_POST['is_active']) ? 1 : 0,
                'display_order' => intval($_POST['display_order'] ?? 0)
            ];
            
            if($mediaManager->updateMedia($media_id, $data, $_SESSION['admin_id'])) {
                $message = 'Média modifié avec succès.';
            } else {
                $error = 'Erreur lors de la modification du média.';
            }
        }
    }
    
    if(isset($_POST['delete_media'])) {
        $media_id = $_POST['media_id'];
        if($mediaManager->deleteMedia($media_id, $_SESSION['admin_id'])) {
            $message = 'Média supprimé avec succès.';
        } else {
            $error = 'Erreur lors de la suppression du média.';
        }
    }
    
    if(isset($_POST['toggle_status'])) {
        $media_id = $_POST['media_id'];
        if($mediaManager->toggleMediaStatus($media_id, $_SESSION['admin_id'])) {
            $message = 'Statut du média modifié avec succès.';
        } else {
            $error = 'Erreur lors de la modification du statut.';
        }
    }
}

// Récupérer les médias
$search = $_GET['search'] ?? '';
$media_type_filter = $_GET['type'] ?? '';

if($search) {
    $medias = $mediaManager->getAllMedias($media_type_filter);
    // Filtrer par recherche
    $medias = array_filter($medias, function($media) use ($search) {
        return stripos($media['title'], $search) !== false || 
               stripos($media['description'], $search) !== false;
    });
} else {
    $medias = $mediaManager->getAllMedias($media_type_filter);
}

$stats = $mediaManager->getMediaStats();
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gestion des Médias - Universe Security Admin</title>
    
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
        
        .media-card {
            border: none;
            border-radius: 15px;
            box-shadow: 0 4px 6px rgba(0,0,0,0.1);
            transition: all 0.3s ease;
            overflow: hidden;
        }
        
        .media-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 8px 25px rgba(0,0,0,0.15);
        }
        
        .media-preview {
            width: 100%;
            height: 200px;
            object-fit: cover;
            background: #f8f9fa;
        }
        
        .video-preview {
            position: relative;
            background: #000;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        
        .video-preview i {
            font-size: 3rem;
            color: white;
            opacity: 0.8;
        }
        
        .stats-card {
            background: white;
            border-radius: 15px;
            padding: 20px;
            box-shadow: 0 4px 6px rgba(0,0,0,0.1);
            border-left: 4px solid var(--primary-color);
        }
        
        .btn-primary {
            background: linear-gradient(135deg, var(--primary-color) 0%, var(--secondary-color) 100%);
            border: none;
        }
        
        .sidebar ul {
            list-style: none;
            padding: 0;
            margin: 0;
        }
        
        .sidebar ul li {
            margin: 0;
        }
        
        .sidebar ul li a {
            display: block;
            padding: 15px 20px;
            color: rgba(255,255,255,0.8);
            text-decoration: none;
            transition: all 0.3s ease;
            border-left: 3px solid transparent;
        }
        
        .sidebar ul li a:hover,
        .sidebar ul li a.active {
            background: rgba(255,255,255,0.1);
            color: white;
            border-left-color: white;
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
    $current_page = 'medias';
    // Inclure la navbar commune
    include 'includes/admin_navbar.php'; 
    ?>

    <!-- Main Content -->
    <div class="main-content">
        <!-- Header -->
        <div class="d-flex justify-content-between align-items-center mb-4">
            <div>
                <h1 class="h3 mb-0">Gestion des Médias</h1>
                <p class="text-muted">Gérez vos photos et vidéos</p>
            </div>
            <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addMediaModal">
                <i class="fas fa-plus me-2"></i>Ajouter un Média
            </button>
        </div>

        <!-- Messages -->
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
            <div class="col-lg-3 col-md-6 mb-3">
                <div class="stats-card">
                    <div class="d-flex align-items-center">
                        <div class="me-3">
                            <i class="fas fa-photo-video text-primary fa-2x"></i>
                        </div>
                        <div>
                            <h4 class="mb-0"><?php echo $stats['total']; ?></h4>
                            <small class="text-muted">Total Médias</small>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="col-lg-3 col-md-6 mb-3">
                <div class="stats-card">
                    <div class="d-flex align-items-center">
                        <div class="me-3">
                            <i class="fas fa-images text-success fa-2x"></i>
                        </div>
                        <div>
                            <h4 class="mb-0"><?php echo $stats['photos']; ?></h4>
                            <small class="text-muted">Photos</small>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="col-lg-3 col-md-6 mb-3">
                <div class="stats-card">
                    <div class="d-flex align-items-center">
                        <div class="me-3">
                            <i class="fas fa-video text-info fa-2x"></i>
                        </div>
                        <div>
                            <h4 class="mb-0"><?php echo $stats['videos']; ?></h4>
                            <small class="text-muted">Vidéos</small>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="col-lg-3 col-md-6 mb-3">
                <div class="stats-card">
                    <div class="d-flex align-items-center">
                        <div class="me-3">
                            <i class="fas fa-eye text-warning fa-2x"></i>
                        </div>
                        <div>
                            <h4 class="mb-0"><?php echo $stats['active']; ?></h4>
                            <small class="text-muted">Actifs</small>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Filtres et recherche -->
        <div class="card mb-4">
            <div class="card-body">
                <form method="GET" class="row g-3">
                    <div class="col-md-4">
                        <input type="text" class="form-control" name="search" placeholder="Rechercher..." value="<?php echo htmlspecialchars($search); ?>">
                    </div>
                    <div class="col-md-3">
                        <select class="form-select" name="type">
                            <option value="">Tous les types</option>
                            <option value="photo" <?php echo $media_type_filter === 'photo' ? 'selected' : ''; ?>>Photos</option>
                            <option value="video" <?php echo $media_type_filter === 'video' ? 'selected' : ''; ?>>Vidéos</option>
                        </select>
                    </div>
                    <div class="col-md-2">
                        <button type="submit" class="btn btn-outline-primary w-100">
                            <i class="fas fa-search me-1"></i>Filtrer
                        </button>
                    </div>
                    <div class="col-md-3">
                        <a href="medias.php" class="btn btn-outline-secondary w-100">
                            <i class="fas fa-times me-1"></i>Réinitialiser
                        </a>
                    </div>
                </form>
            </div>
        </div>

        <!-- Liste des médias -->
        <div class="row">
            <?php if(empty($medias)): ?>
                <div class="col-12">
                    <div class="text-center py-5">
                        <i class="fas fa-photo-video fa-3x text-muted mb-3"></i>
                        <h5 class="text-muted">Aucun média trouvé</h5>
                        <p class="text-muted">Commencez par ajouter votre premier média.</p>
                        <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addMediaModal">
                            <i class="fas fa-plus me-2"></i>Ajouter un Média
                        </button>
                    </div>
                </div>
            <?php else: ?>
                <?php foreach($medias as $media): ?>
                    <div class="col-lg-4 col-md-6 mb-4">
                        <div class="card media-card">
                            <div class="media-preview">
                                <?php if($media['media_type'] === 'photo'): ?>
                                    <img src="../<?php echo htmlspecialchars($media['file_path']); ?>" 
                                         alt="<?php echo htmlspecialchars($media['alt_text']); ?>" 
                                         class="media-preview">
                                <?php else: ?>
                                    <div class="media-preview video-preview">
                                        <i class="fas fa-play-circle"></i>
                                    </div>
                                <?php endif; ?>
                            </div>
                            
                            <div class="card-body">
                                <div class="d-flex justify-content-between align-items-start mb-2">
                                    <h6 class="card-title mb-0"><?php echo htmlspecialchars($media['title']); ?></h6>
                                    <span class="badge bg-<?php echo $media['is_active'] ? 'success' : 'secondary'; ?>">
                                        <?php echo $media['is_active'] ? 'Actif' : 'Inactif'; ?>
                                    </span>
                                </div>
                                
                                <p class="card-text text-muted small">
                                    <?php echo htmlspecialchars(substr($media['description'], 0, 100)); ?>
                                    <?php if(strlen($media['description']) > 100): ?>...<?php endif; ?>
                                </p>
                                
                                <div class="d-flex justify-content-between align-items-center">
                                    <small class="text-muted">
                                        <i class="fas fa-<?php echo $media['media_type'] === 'photo' ? 'image' : 'video'; ?> me-1"></i>
                                        <?php echo strtoupper($media['file_type']); ?>
                                    </small>
                                    
                                    <div class="btn-group btn-group-sm">
                                        <button class="btn btn-outline-primary btn-sm" 
                                                onclick="editMedia(<?php echo $media['id']; ?>)"
                                                title="Modifier">
                                            <i class="fas fa-edit"></i>
                                        </button>
                                        <form method="POST" class="d-inline" onsubmit="return confirm('Êtes-vous sûr ?')">
                                            <input type="hidden" name="media_id" value="<?php echo $media['id']; ?>">
                                            <button type="submit" name="toggle_status" class="btn btn-outline-warning btn-sm" title="Changer le statut">
                                                <i class="fas fa-<?php echo $media['is_active'] ? 'eye-slash' : 'eye'; ?>"></i>
                                            </button>
                                        </form>
                                        <form method="POST" class="d-inline" onsubmit="return confirm('Êtes-vous sûr de vouloir supprimer ce média ?')">
                                            <input type="hidden" name="media_id" value="<?php echo $media['id']; ?>">
                                            <button type="submit" name="delete_media" class="btn btn-outline-danger btn-sm" title="Supprimer">
                                                <i class="fas fa-trash"></i>
                                            </button>
                                        </form>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
    </div>

    <!-- Modal Ajouter Média -->
    <div class="modal fade" id="addMediaModal" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Ajouter un Média</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <form method="POST" enctype="multipart/form-data">
                    <div class="modal-body">
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Titre *</label>
                                <input type="text" class="form-control" name="title" required>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Type de média *</label>
                                <select class="form-select" name="media_type" required>
                                    <option value="">Sélectionner...</option>
                                    <option value="photo">Photo</option>
                                    <option value="video">Vidéo</option>
                                </select>
                            </div>
                        </div>
                        
                        <div class="mb-3">
                            <label class="form-label">Description</label>
                            <textarea class="form-control" name="description" rows="3"></textarea>
                        </div>
                        
                        <div class="mb-3">
                            <label class="form-label">Fichier *</label>
                            <input type="file" class="form-control" name="media_file" required accept="image/*,video/*">
                            <small class="text-muted">Formats acceptés: JPG, PNG, GIF, MP4, AVI, MOV</small>
                        </div>
                        
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Texte alternatif</label>
                                <input type="text" class="form-control" name="alt_text">
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Ordre d'affichage</label>
                                <input type="number" class="form-control" name="display_order" value="0">
                            </div>
                        </div>
                        
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" name="is_active" id="is_active" checked>
                            <label class="form-check-label" for="is_active">
                                Actif
                            </label>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Annuler</button>
                        <button type="submit" name="create_media" class="btn btn-primary">Créer</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    
    <script>
        function editMedia(id) {
            // Fonction pour éditer un média (à implémenter)
            alert('Fonction d\'édition à implémenter pour le média ID: ' + id);
        }
        
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
