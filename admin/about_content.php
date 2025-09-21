<?php
require_once 'config/database.php';
require_once 'classes/Auth.php';
require_once 'classes/ContentManager.php';
require_once 'classes/FileUploader.php';

$auth = new Auth();

// Vérifier si l'utilisateur est connecté
if(!$auth->isLoggedIn()) {
    header('Location: login.php');
    exit();
}

$database = new Database();
$conn = $database->getConnection();
$contentManager = new ContentManager($conn);
$fileUploader = new FileUploader();

$message = '';
$error = '';

// Traitement des actions
if($_SERVER['REQUEST_METHOD'] == 'POST') {
    if(isset($_POST['save_content'])) {
        $data = [
            'section_key' => trim($_POST['section_key']),
            'title' => trim($_POST['title']),
            'content' => trim($_POST['content']),
            'display_order' => intval($_POST['display_order']),
            'image' => null
        ];
        
        // Gestion de l'upload d'image
        if(isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
            $upload_result = $fileUploader->uploadFile($_FILES['image'], 'about');
            if($upload_result['success']) {
                $data['image'] = $upload_result['file_path'];
            } else {
                $error = $upload_result['error'];
            }
        } elseif(isset($_POST['existing_image'])) {
            $data['image'] = $_POST['existing_image'];
        }
        
        if(empty($error) && $contentManager->saveContent($data)) {
            $message = 'Contenu sauvegardé avec succès.';
        } elseif(empty($error)) {
            $error = 'Erreur lors de la sauvegarde du contenu.';
        }
    }
    
    if(isset($_POST['delete_content'])) {
        $section_key = $_POST['section_key'];
        if($contentManager->deleteContent($section_key)) {
            $message = 'Contenu supprimé avec succès.';
        } else {
            $error = 'Erreur lors de la suppression du contenu.';
        }
    }
    
    if(isset($_POST['toggle_status'])) {
        $section_key = $_POST['section_key'];
        if($contentManager->toggleContentStatus($section_key)) {
            $message = 'Statut du contenu modifié avec succès.';
        } else {
            $error = 'Erreur lors de la modification du statut.';
        }
    }
}

// Récupérer tout le contenu
$contents = $contentManager->getAboutContent();
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Contenu À Propos - Universe Security Admin</title>
    
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
        
        .content-item {
            border: 1px solid #e9ecef;
            border-radius: 8px;
            padding: 20px;
            margin-bottom: 15px;
            transition: all 0.3s ease;
        }
        
        .content-item:hover {
            box-shadow: 0 4px 12px rgba(0,0,0,0.1);
        }
        
        .content-image {
            max-width: 100px;
            height: 60px;
            object-fit: cover;
            border-radius: 5px;
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
    $current_page = 'about_content';
    // Inclure la navbar commune
    include 'includes/admin_navbar.php'; 
    ?>
    
    <!-- Main Content -->
    <div class="main-content">
        <!-- Header -->
        <div class="d-flex justify-content-between align-items-center mb-4">
            <div>
                <h1 class="h3 mb-0">Contenu "À Propos"</h1>
                <p class="text-muted">Gérez le contenu de la section "À propos de nous"</p>
            </div>
            <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#contentModal">
                <i class="fas fa-plus me-2"></i>Nouveau Contenu
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
        
        <!-- Liste des contenus -->
        <div class="content-card">
            <?php if(empty($contents)): ?>
                <div class="text-center py-5">
                    <i class="fas fa-file-alt fa-4x text-muted mb-3"></i>
                    <h5 class="text-muted">Aucun contenu créé</h5>
                    <p class="text-muted">Créez votre premier contenu "À propos".</p>
                </div>
            <?php else: ?>
                <?php foreach($contents as $content): ?>
                    <div class="content-item">
                        <div class="row align-items-center">
                            <div class="col-md-2">
                                <?php if($content['image']): ?>
                                    <img src="../<?php echo htmlspecialchars($content['image']); ?>" alt="Image" class="content-image">
                                <?php else: ?>
                                    <div class="bg-light d-flex align-items-center justify-content-center content-image">
                                        <i class="fas fa-image text-muted"></i>
                                    </div>
                                <?php endif; ?>
                            </div>
                            <div class="col-md-7">
                                <h6 class="mb-1"><?php echo htmlspecialchars($content['title']); ?></h6>
                                <small class="text-muted">Clé: <?php echo htmlspecialchars($content['section_key']); ?></small>
                                <p class="mb-0 mt-2"><?php echo htmlspecialchars(substr($content['content'], 0, 150)); ?>...</p>
                            </div>
                            <div class="col-md-3 text-end">
                                <div class="btn-group" role="group">
                                    <button type="button" class="btn btn-sm btn-outline-primary" 
                                            onclick="editContent('<?php echo htmlspecialchars($content['section_key']); ?>')">
                                        <i class="fas fa-edit"></i>
                                    </button>
                                    <form method="POST" class="d-inline">
                                        <input type="hidden" name="section_key" value="<?php echo htmlspecialchars($content['section_key']); ?>">
                                        <button type="submit" name="toggle_status" class="btn btn-sm btn-outline-warning">
                                            <i class="fas fa-<?php echo $content['is_active'] ? 'eye-slash' : 'eye'; ?>"></i>
                                        </button>
                                    </form>
                                    <form method="POST" class="d-inline" onsubmit="return confirm('Êtes-vous sûr de vouloir supprimer ce contenu ?')">
                                        <input type="hidden" name="section_key" value="<?php echo htmlspecialchars($content['section_key']); ?>">
                                        <button type="submit" name="delete_content" class="btn btn-sm btn-outline-danger">
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
    
    <!-- Modal pour créer/modifier un contenu -->
    <div class="modal fade" id="contentModal" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Nouveau Contenu</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <form method="POST" enctype="multipart/form-data">
                    <div class="modal-body">
                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label class="form-label">Clé de section *</label>
                                    <input type="text" class="form-control" name="section_key" id="section_key" required>
                                    <small class="text-muted">Identifiant unique (ex: main_about, our_mission)</small>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label class="form-label">Ordre d'affichage</label>
                                    <input type="number" class="form-control" name="display_order" id="display_order" value="0">
                                </div>
                            </div>
                        </div>
                        
                        <div class="mb-3">
                            <label class="form-label">Titre *</label>
                            <input type="text" class="form-control" name="title" id="title" required>
                        </div>
                        
                        <div class="mb-3">
                            <label class="form-label">Contenu *</label>
                            <textarea class="form-control" name="content" id="content" rows="6" required></textarea>
                        </div>
                        
                        <div class="mb-3">
                            <label class="form-label">Image (optionnelle)</label>
                            <input type="file" class="form-control" name="image" accept="image/*">
                            <input type="hidden" name="existing_image" id="existing_image">
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Annuler</button>
                        <button type="submit" name="save_content" class="btn btn-primary">Sauvegarder</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    
    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    
    <script>
        // Données des contenus pour l'édition
        const contents = <?php echo json_encode($contents); ?>;
        
        function editContent(sectionKey) {
            const content = contents.find(c => c.section_key === sectionKey);
            if (content) {
                document.getElementById('section_key').value = content.section_key;
                document.getElementById('title').value = content.title;
                document.getElementById('content').value = content.content;
                document.getElementById('display_order').value = content.display_order;
                document.getElementById('existing_image').value = content.image || '';
                
                document.querySelector('#contentModal .modal-title').textContent = 'Modifier le Contenu';
                new bootstrap.Modal(document.getElementById('contentModal')).show();
            }
        }
        
        // Réinitialiser le modal lors de sa fermeture
        document.getElementById('contentModal').addEventListener('hidden.bs.modal', function () {
            document.querySelector('#contentModal form').reset();
            document.querySelector('#contentModal .modal-title').textContent = 'Nouveau Contenu';
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
