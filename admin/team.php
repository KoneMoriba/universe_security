<?php
session_start();
require_once 'config/database.php';
require_once 'classes/Auth.php';
require_once 'classes/TeamManager.php';
require_once 'classes/ImageUploader.php';

$auth = new Auth();

// Vérifier si l'utilisateur est connecté
if(!$auth->isLoggedIn()) {
    header('Location: login.php');
    exit();
}

$database = new Database();
$conn = $database->getConnection();
$teamManager = new TeamManager($conn);
$imageUploader = new ImageUploader();

$success_message = '';
$error_message = '';

// Traitement des actions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    
    switch ($action) {
        case 'create':
            $imagePath = null;
            
            // Gérer l'upload d'image si présent
            if (isset($_FILES['image']) && $_FILES['image']['error'] !== UPLOAD_ERR_NO_FILE) {
                $uploadResult = $imageUploader->uploadImage($_FILES['image'], 'team', 'member');
                if ($uploadResult['success']) {
                    $imagePath = $uploadResult['filename'];
                } else {
                    $error_message = $uploadResult['message'];
                    break;
                }
            }
            
            $data = [
                'name' => $_POST['name'],
                'position' => $_POST['position'],
                'bio' => $_POST['bio'],
                'image' => $imagePath,
                'email' => $_POST['email'],
                'phone' => $_POST['phone'],
                'social_facebook' => $_POST['social_facebook'],
                'social_twitter' => $_POST['social_twitter'],
                'social_linkedin' => $_POST['social_linkedin'],
                'social_instagram' => $_POST['social_instagram'],
                'is_active' => isset($_POST['is_active']),
                'display_order' => intval($_POST['display_order']),
                'created_by' => $_SESSION['admin_id']
            ];
            
            if ($teamManager->createMember($data)) {
                $success_message = "Membre d'équipe ajouté avec succès !";
            } else {
                $error_message = "Erreur lors de l'ajout du membre.";
                // Supprimer l'image si la création a échoué
                if ($imagePath) {
                    $imageUploader->deleteImage($imagePath);
                }
            }
            break;
            
        case 'update':
            $id = intval($_POST['id']);
            $currentMember = $teamManager->getMemberById($id);
            $imagePath = $currentMember['image']; // Garder l'image actuelle par défaut
            
            // Gérer l'upload d'une nouvelle image si présent
            if (isset($_FILES['image']) && $_FILES['image']['error'] !== UPLOAD_ERR_NO_FILE) {
                $uploadResult = $imageUploader->uploadImage($_FILES['image'], 'team', 'member');
                if ($uploadResult['success']) {
                    // Supprimer l'ancienne image si elle existe
                    if ($currentMember['image']) {
                        $imageUploader->deleteImage($currentMember['image']);
                    }
                    $imagePath = $uploadResult['filename'];
                } else {
                    $error_message = $uploadResult['message'];
                    break;
                }
            }
            
            $data = [
                'name' => $_POST['name'],
                'position' => $_POST['position'],
                'bio' => $_POST['bio'],
                'image' => $imagePath,
                'email' => $_POST['email'],
                'phone' => $_POST['phone'],
                'social_facebook' => $_POST['social_facebook'],
                'social_twitter' => $_POST['social_twitter'],
                'social_linkedin' => $_POST['social_linkedin'],
                'social_instagram' => $_POST['social_instagram'],
                'is_active' => isset($_POST['is_active']),
                'display_order' => intval($_POST['display_order'])
            ];
            
            if ($teamManager->updateMember($id, $data)) {
                $success_message = "Membre mis à jour avec succès !";
            } else {
                $error_message = "Erreur lors de la mise à jour du membre.";
            }
            break;
            
        case 'delete':
            $id = intval($_POST['id']);
            if ($teamManager->deleteMember($id)) {
                $success_message = "Membre supprimé avec succès !";
            } else {
                $error_message = "Erreur lors de la suppression du membre.";
            }
            break;
            
        case 'toggle_status':
            $id = intval($_POST['id']);
            if ($teamManager->toggleMemberStatus($id)) {
                $success_message = "Statut du membre modifié avec succès !";
            } else {
                $error_message = "Erreur lors de la modification du statut.";
            }
            break;
    }
}

// Récupérer tous les membres
$members = $teamManager->getAllMembers();
$total_members = $teamManager->countMembers();
$active_members = $teamManager->countMembers(true);

// Récupérer un membre pour édition
$edit_member = null;
if (isset($_GET['edit'])) {
    $edit_member = $teamManager->getMemberById(intval($_GET['edit']));
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="utf-8">
    <title>Gestion de l'Équipe - Universe Security Admin</title>
    
    <!-- Favicon -->
    <link href="../img/logo universe security.jpg" rel="icon">
    
    <!-- Bootstrap CSS -->
    <meta content="width=device-width, initial-scale=1.0" name="viewport">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.0.0/dist/css/bootstrap.min.css" rel="stylesheet">
    
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
        
        @media (max-width: 768px) {
            .mobile-toggle {
                display: block;
            }
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
    $current_page = 'team';
    // Inclure la navbar commune
    include 'includes/admin_navbar.php'; 
    ?>

    <!-- Main Content -->
    <div class="main-content">
        <!-- Header -->
        <div class="d-flex justify-content-between align-items-center mb-4">
            <div>
                <h2 class="mb-0">Gestion de l'Équipe</h2>
                <small class="text-muted">Gérez les membres de votre équipe</small>
            </div>
            <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#memberModal">
                <i class="fa fa-plus me-2"></i>Nouveau Membre
            </button>
        </div>

        <!-- Content -->
        <div class="row g-4">
            <div class="col-12">
                <div class="bg-light rounded h-100 p-4">

                            <!-- Messages -->
                            <?php if ($success_message): ?>
                                <div class="alert alert-success alert-dismissible fade show" role="alert">
                                    <i class="fa fa-check-circle me-2"></i><?php echo $success_message; ?>
                                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                                </div>
                            <?php endif; ?>

                            <?php if ($error_message): ?>
                                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                                    <i class="fa fa-exclamation-triangle me-2"></i><?php echo $error_message; ?>
                                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                                </div>
                            <?php endif; ?>

                            <!-- Statistiques -->
                            <div class="row mb-4">
                                <div class="col-md-6">
                                    <div class="card bg-info text-white">
                                        <div class="card-body">
                                            <div class="d-flex align-items-center">
                                                <i class="fa fa-users fa-2x me-3"></i>
                                                <div>
                                                    <h4 class="mb-0"><?php echo $total_members; ?></h4>
                                                    <p class="mb-0">Total Membres</p>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="card bg-success text-white">
                                        <div class="card-body">
                                            <div class="d-flex align-items-center">
                                                <i class="fa fa-user-check fa-2x me-3"></i>
                                                <div>
                                                    <h4 class="mb-0"><?php echo $active_members; ?></h4>
                                                    <p class="mb-0">Membres Actifs</p>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Cartes des membres -->
                            <div class="row">
                                <?php foreach ($members as $member): ?>
                                    <div class="col-md-6 col-lg-4 mb-4">
                                        <div class="card h-100">
                                            <div class="card-body text-center">
                                                <div class="mb-3">
                                                    <?php if (!empty($member['image'])): ?>
                                                        <img src="../<?php echo htmlspecialchars($member['image']); ?>" 
                                                             class="rounded-circle" style="width: 80px; height: 80px; object-fit: cover;" 
                                                             alt="<?php echo htmlspecialchars($member['name']); ?>">
                                                    <?php else: ?>
                                                        <div class="bg-primary rounded-circle d-inline-flex align-items-center justify-content-center" 
                                                             style="width: 80px; height: 80px;">
                                                            <i class="fa fa-user fa-2x text-white"></i>
                                                        </div>
                                                    <?php endif; ?>
                                                </div>
                                                
                                                <h5 class="card-title"><?php echo htmlspecialchars($member['name']); ?></h5>
                                                <p class="text-primary"><?php echo htmlspecialchars($member['position']); ?></p>
                                                
                                                <?php if (!empty($member['bio'])): ?>
                                                    <p class="card-text small text-muted">
                                                        <?php echo htmlspecialchars(substr($member['bio'], 0, 100)); ?>
                                                        <?php if (strlen($member['bio']) > 100): ?>...<?php endif; ?>
                                                    </p>
                                                <?php endif; ?>
                                                
                                                <div class="mb-3">
                                                    <?php if (!empty($member['email'])): ?>
                                                        <small class="text-muted d-block">
                                                            <i class="fa fa-envelope me-1"></i>
                                                            <?php echo htmlspecialchars($member['email']); ?>
                                                        </small>
                                                    <?php endif; ?>
                                                    
                                                    <?php if (!empty($member['phone'])): ?>
                                                        <small class="text-muted d-block">
                                                            <i class="fa fa-phone me-1"></i>
                                                            <?php echo htmlspecialchars($member['phone']); ?>
                                                        </small>
                                                    <?php endif; ?>
                                                </div>
                                                
                                                <div class="mb-3">
                                                    <span class="badge bg-<?php echo $member['is_active'] ? 'success' : 'danger'; ?>">
                                                        <?php echo $member['is_active'] ? 'Actif' : 'Inactif'; ?>
                                                    </span>
                                                    <span class="badge bg-secondary">Ordre: <?php echo $member['display_order']; ?></span>
                                                </div>
                                                
                                                <!-- Réseaux sociaux -->
                                                <div class="mb-3">
                                                    <?php if (!empty($member['social_facebook'])): ?>
                                                        <a href="<?php echo htmlspecialchars($member['social_facebook']); ?>" 
                                                           class="btn btn-sm btn-outline-primary me-1" target="_blank">
                                                            <i class="fab fa-facebook-f"></i>
                                                        </a>
                                                    <?php endif; ?>
                                                    
                                                    <?php if (!empty($member['social_twitter'])): ?>
                                                        <a href="<?php echo htmlspecialchars($member['social_twitter']); ?>" 
                                                           class="btn btn-sm btn-outline-info me-1" target="_blank">
                                                            <i class="fab fa-twitter"></i>
                                                        </a>
                                                    <?php endif; ?>
                                                    
                                                    <?php if (!empty($member['social_linkedin'])): ?>
                                                        <a href="<?php echo htmlspecialchars($member['social_linkedin']); ?>" 
                                                           class="btn btn-sm btn-outline-primary me-1" target="_blank">
                                                            <i class="fab fa-linkedin-in"></i>
                                                        </a>
                                                    <?php endif; ?>
                                                    
                                                    <?php if (!empty($member['social_instagram'])): ?>
                                                        <a href="<?php echo htmlspecialchars($member['social_instagram']); ?>" 
                                                           class="btn btn-sm btn-outline-danger me-1" target="_blank">
                                                            <i class="fab fa-instagram"></i>
                                                        </a>
                                                    <?php endif; ?>
                                                </div>
                                                
                                                <!-- Actions -->
                                                <div class="btn-group w-100" role="group">
                                                    <a href="?edit=<?php echo $member['id']; ?>" class="btn btn-sm btn-outline-primary">
                                                        <i class="fa fa-edit"></i> Modifier
                                                    </a>
                                                    <form method="POST" style="display: inline;" onsubmit="return confirm('Changer le statut de ce membre ?')">
                                                        <input type="hidden" name="action" value="toggle_status">
                                                        <input type="hidden" name="id" value="<?php echo $member['id']; ?>">
                                                        <button type="submit" class="btn btn-sm btn-outline-warning">
                                                            <i class="fa fa-toggle-<?php echo $member['is_active'] ? 'on' : 'off'; ?>"></i>
                                                        </button>
                                                    </form>
                                                    <form method="POST" style="display: inline;" onsubmit="return confirm('Supprimer ce membre ?')">
                                                        <input type="hidden" name="action" value="delete">
                                                        <input type="hidden" name="id" value="<?php echo $member['id']; ?>">
                                                        <button type="submit" class="btn btn-sm btn-outline-danger">
                                                            <i class="fa fa-trash"></i>
                                                        </button>
                                                    </form>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal Membre -->
    <div class="modal fade" id="memberModal" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <form method="POST" enctype="multipart/form-data">
                    <div class="modal-header">
                        <h5 class="modal-title"><?php echo $edit_member ? 'Modifier le Membre' : 'Nouveau Membre'; ?></h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        <input type="hidden" name="action" value="<?php echo $edit_member ? 'update' : 'create'; ?>">
                        <?php if ($edit_member): ?>
                            <input type="hidden" name="id" value="<?php echo $edit_member['id']; ?>">
                        <?php endif; ?>

                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label class="form-label">Nom complet *</label>
                                    <input type="text" class="form-control" name="name" required 
                                           value="<?php echo $edit_member ? htmlspecialchars($edit_member['name']) : ''; ?>">
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label class="form-label">Poste *</label>
                                    <input type="text" class="form-control" name="position" required
                                           value="<?php echo $edit_member ? htmlspecialchars($edit_member['position']) : ''; ?>">
                                </div>
                            </div>
                        </div>

                        <!-- Upload d'image -->
                        <div class="mb-3">
                            <label class="form-label">Photo du membre</label>
                            <input type="file" class="form-control" name="image" accept="image/*">
                            <div class="form-text">Formats acceptés: JPG, PNG, GIF, WebP (max 5MB)</div>
                            <?php if ($edit_member && !empty($edit_member['image'])): ?>
                                <div class="mt-2">
                                    <small class="text-muted">Photo actuelle:</small><br>
                                    <img src="../<?php echo htmlspecialchars($edit_member['image']); ?>" 
                                         alt="Photo actuelle" style="max-width: 100px; max-height: 100px;" class="img-thumbnail rounded-circle">
                                </div>
                            <?php endif; ?>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Biographie</label>
                            <textarea class="form-control" name="bio" rows="3"><?php echo $edit_member ? htmlspecialchars($edit_member['bio']) : ''; ?></textarea>
                        </div>

                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label class="form-label">Email</label>
                                    <input type="email" class="form-control" name="email"
                                           value="<?php echo $edit_member ? htmlspecialchars($edit_member['email']) : ''; ?>">
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label class="form-label">Téléphone</label>
                                    <input type="tel" class="form-control" name="phone"
                                           value="<?php echo $edit_member ? htmlspecialchars($edit_member['phone']) : ''; ?>">
                                </div>
                            </div>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Photo (chemin vers l'image)</label>
                            <input type="text" class="form-control" name="image" placeholder="img/team-1.jpg"
                                   value="<?php echo $edit_member ? htmlspecialchars($edit_member['image']) : ''; ?>">
                        </div>

                        <h6 class="mb-3">Réseaux sociaux</h6>
                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label class="form-label">Facebook</label>
                                    <input type="url" class="form-control" name="social_facebook"
                                           value="<?php echo $edit_member ? htmlspecialchars($edit_member['social_facebook']) : ''; ?>">
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label class="form-label">Twitter</label>
                                    <input type="url" class="form-control" name="social_twitter"
                                           value="<?php echo $edit_member ? htmlspecialchars($edit_member['social_twitter']) : ''; ?>">
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label class="form-label">LinkedIn</label>
                                    <input type="url" class="form-control" name="social_linkedin"
                                           value="<?php echo $edit_member ? htmlspecialchars($edit_member['social_linkedin']) : ''; ?>">
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label class="form-label">Instagram</label>
                                    <input type="url" class="form-control" name="social_instagram"
                                           value="<?php echo $edit_member ? htmlspecialchars($edit_member['social_instagram']) : ''; ?>">
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label class="form-label">Ordre d'affichage</label>
                                    <input type="number" class="form-control" name="display_order"
                                           value="<?php echo $edit_member ? $edit_member['display_order'] : '0'; ?>">
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <div class="form-check">
                                        <input class="form-check-input" type="checkbox" name="is_active" id="is_active"
                                               <?php echo (!$edit_member || $edit_member['is_active']) ? 'checked' : ''; ?>>
                                        <label class="form-check-label" for="is_active">
                                            Membre actif
                                        </label>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Annuler</button>
                        <button type="submit" class="btn btn-primary">
                            <?php echo $edit_member ? 'Mettre à jour' : 'Créer'; ?>
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Scripts -->
    <script src="https://code.jquery.com/jquery-3.4.1.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.0.0/dist/js/bootstrap.bundle.min.js"></script>
    
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

    <?php if ($edit_member): ?>
    <script>
        $(document).ready(function() {
            $('#memberModal').modal('show');
        });
    </script>
    <?php endif; ?>
</body>
</html>
