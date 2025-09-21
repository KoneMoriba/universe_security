<?php
require_once 'config/database.php';
require_once 'classes/Auth.php';
require_once 'classes/TestimonialManager.php';
require_once 'classes/FileUploader.php';

$auth = new Auth();

// Vérifier si l'utilisateur est connecté
if(!$auth->isLoggedIn()) {
    header('Location: login.php');
    exit();
}

$testimonialManager = new TestimonialManager();
$fileUploader = new FileUploader();
$message = '';
$error = '';

// Traitement des actions
if($_SERVER['REQUEST_METHOD'] == 'POST') {
    if(isset($_POST['create_testimonial'])) {
        $data = [
            'client_name' => trim($_POST['client_name']),
            'client_position' => trim($_POST['client_position']),
            'client_company' => trim($_POST['client_company']),
            'content' => trim($_POST['content']),
            'rating' => intval($_POST['rating']),
            'client_image' => trim($_POST['client_image']),
            'is_approved' => isset($_POST['is_approved']) ? 1 : 0,
            'is_featured' => isset($_POST['is_featured']) ? 1 : 0
        ];
        
        if($testimonialManager->createTestimonial($data)) {
            $message = 'Témoignage créé avec succès.';
        } else {
            $error = 'Erreur lors de la création du témoignage.';
        }
    }
    
    if(isset($_POST['update_testimonial'])) {
        $testimonial_id = $_POST['testimonial_id'];
        $data = [
            'client_name' => trim($_POST['client_name']),
            'client_position' => trim($_POST['client_position']),
            'client_company' => trim($_POST['client_company']),
            'content' => trim($_POST['content']),
            'rating' => intval($_POST['rating']),
            'client_image' => trim($_POST['client_image']),
            'is_approved' => isset($_POST['is_approved']) ? 1 : 0,
            'is_featured' => isset($_POST['is_featured']) ? 1 : 0
        ];
        
        if($testimonialManager->updateTestimonial($testimonial_id, $data, $_SESSION['admin_id'])) {
            $message = 'Témoignage mis à jour avec succès.';
        } else {
            $error = 'Erreur lors de la mise à jour du témoignage.';
        }
    }
    
    if(isset($_POST['delete_testimonial'])) {
        $testimonial_id = $_POST['testimonial_id'];
        if($testimonialManager->deleteTestimonial($testimonial_id, $_SESSION['admin_id'])) {
            $message = 'Témoignage supprimé avec succès.';
        } else {
            $error = 'Erreur lors de la suppression du témoignage.';
        }
    }
    
    if(isset($_POST['approve_testimonial'])) {
        $testimonial_id = $_POST['testimonial_id'];
        if($testimonialManager->approveTestimonial($testimonial_id, $_SESSION['admin_id'])) {
            $message = 'Témoignage approuvé avec succès.';
        } else {
            $error = 'Erreur lors de l\'approbation du témoignage.';
        }
    }
    
    if(isset($_POST['toggle_featured'])) {
        $testimonial_id = $_POST['testimonial_id'];
        if($testimonialManager->toggleFeatured($testimonial_id, $_SESSION['admin_id'])) {
            $message = 'Statut vedette modifié avec succès.';
        } else {
            $error = 'Erreur lors de la modification du statut.';
        }
    }
}

// Récupérer les témoignages
$search = $_GET['search'] ?? '';
if($search) {
    $testimonials = $testimonialManager->searchTestimonials($search);
} else {
    $testimonials = $testimonialManager->getAllTestimonials();
}

$stats = $testimonialManager->getTestimonialStats();
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gestion des Témoignages - Universe Security Admin</title>
    
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
            text-center: center;
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
        
        .testimonial-card {
            border: 1px solid #dee2e6;
            border-radius: 10px;
            padding: 20px;
            margin-bottom: 20px;
            transition: all 0.3s ease;
        }
        
        .testimonial-card:hover {
            box-shadow: 0 5px 15px rgba(0,0,0,0.1);
            transform: translateY(-2px);
        }
        
        .testimonial-pending {
            border-left: 4px solid #ffc107;
            background-color: #fff3cd;
        }
        
        .testimonial-approved {
            border-left: 4px solid #28a745;
        }
        
        .testimonial-featured {
            border: 2px solid #667eea;
            background: linear-gradient(135deg, rgba(102, 126, 234, 0.05) 0%, rgba(118, 75, 162, 0.05) 100%);
        }
        
        .rating-stars {
            color: #ffc107;
        }
        
        .client-avatar {
            width: 50px;
            height: 50px;
            border-radius: 50%;
            object-fit: cover;
            border: 2px solid #dee2e6;
        }
        
        .stats-card {
            background: white;
            border-radius: 15px;
            padding: 20px;
            box-shadow: 0 5px 15px rgba(0,0,0,0.08);
            text-align: center;
        }
        
        .stats-number {
            font-size: 2rem;
            font-weight: bold;
            color: var(--primary-color);
        }
        
        .logo {
            width: 60px;
            height: 60px;
            object-fit: cover;
            border-radius: 50%;
            border: 2px solid rgba(255,255,255,0.3);
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
    $current_page = 'testimonials';
    // Inclure la navbar commune
    include 'includes/admin_navbar.php'; 
    ?>
    
    <!-- Main Content -->
    <div class="main-content">
        <!-- Header -->
        <div class="d-flex justify-content-between align-items-center mb-4">
            <div>
                <h1 class="h3 mb-0">Gestion des Témoignages</h1>
                <p class="text-muted">Gérez les témoignages et avis clients</p>
            </div>
            <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#createTestimonialModal">
                <i class="fas fa-plus me-2"></i>Nouveau Témoignage
            </button>
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
        
        <!-- Statistiques -->
        <div class="row mb-4">
            <div class="col-md-3">
                <div class="stats-card">
                    <div class="stats-number"><?php echo $stats['total']; ?></div>
                    <div class="text-muted">Total Témoignages</div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="stats-card">
                    <div class="stats-number text-success"><?php echo $stats['approved']; ?></div>
                    <div class="text-muted">Approuvés</div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="stats-card">
                    <div class="stats-number text-warning"><?php echo $stats['pending']; ?></div>
                    <div class="text-muted">En Attente</div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="stats-card">
                    <div class="stats-number text-primary"><?php echo $stats['featured']; ?></div>
                    <div class="text-muted">En Vedette</div>
                </div>
            </div>
        </div>
        
        <!-- Note moyenne -->
        <div class="content-card">
            <div class="row align-items-center">
                <div class="col-md-6">
                    <h5 class="mb-0">Note moyenne des témoignages approuvés</h5>
                </div>
                <div class="col-md-6 text-end">
                    <div class="rating-stars">
                        <?php for($i = 1; $i <= 5; $i++): ?>
                            <i class="fas fa-star <?php echo $i <= $stats['avg_rating'] ? '' : 'text-muted'; ?>"></i>
                        <?php endfor; ?>
                        <span class="ms-2 text-dark"><?php echo $stats['avg_rating']; ?>/5</span>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Recherche -->
        <div class="content-card">
            <form method="GET" class="row g-3">
                <div class="col-md-8">
                    <input type="text" class="form-control" name="search" 
                           value="<?php echo htmlspecialchars($search); ?>" 
                           placeholder="Rechercher un témoignage...">
                </div>
                <div class="col-md-4">
                    <button type="submit" class="btn btn-primary me-2">
                        <i class="fas fa-search me-1"></i>Rechercher
                    </button>
                    <a href="testimonials.php" class="btn btn-outline-secondary">
                        <i class="fas fa-times me-1"></i>Reset
                    </a>
                </div>
            </form>
        </div>
        
        <!-- Liste des témoignages -->
        <div class="content-card">
            <div class="d-flex justify-content-between align-items-center mb-3">
                <h5 class="mb-0">Liste des Témoignages (<?php echo count($testimonials); ?>)</h5>
            </div>
            
            <?php if(empty($testimonials)): ?>
                <div class="text-center py-5">
                    <i class="fas fa-comments fa-3x text-muted mb-3"></i>
                    <p class="text-muted">Aucun témoignage trouvé</p>
                </div>
            <?php else: ?>
                <?php foreach($testimonials as $testimonial): ?>
                    <div class="testimonial-card 
                         <?php echo !$testimonial['is_approved'] ? 'testimonial-pending' : 'testimonial-approved'; ?>
                         <?php echo $testimonial['is_featured'] ? 'testimonial-featured' : ''; ?>">
                        <div class="row">
                            <div class="col-md-2 text-center">
                                <?php if($testimonial['client_image']): ?>
                                    <img src="<?php echo htmlspecialchars($testimonial['client_image']); ?>" 
                                         alt="<?php echo htmlspecialchars($testimonial['client_name']); ?>" 
                                         class="client-avatar">
                                <?php else: ?>
                                    <div class="client-avatar d-flex align-items-center justify-content-center bg-secondary text-white">
                                        <i class="fas fa-user"></i>
                                    </div>
                                <?php endif; ?>
                                
                                <div class="rating-stars mt-2">
                                    <?php for($i = 1; $i <= 5; $i++): ?>
                                        <i class="fas fa-star <?php echo $i <= $testimonial['rating'] ? '' : 'text-muted'; ?>"></i>
                                    <?php endfor; ?>
                                </div>
                            </div>
                            
                            <div class="col-md-7">
                                <h6 class="mb-1">
                                    <?php echo htmlspecialchars($testimonial['client_name']); ?>
                                    <?php if($testimonial['is_featured']): ?>
                                        <span class="badge bg-primary ms-2">Vedette</span>
                                    <?php endif; ?>
                                    <?php if(!$testimonial['is_approved']): ?>
                                        <span class="badge bg-warning ms-2">En attente</span>
                                    <?php endif; ?>
                                </h6>
                                
                                <?php if($testimonial['client_position'] || $testimonial['client_company']): ?>
                                    <small class="text-muted">
                                        <?php echo htmlspecialchars($testimonial['client_position']); ?>
                                        <?php if($testimonial['client_position'] && $testimonial['client_company']): ?> - <?php endif; ?>
                                        <?php echo htmlspecialchars($testimonial['client_company']); ?>
                                    </small><br>
                                <?php endif; ?>
                                
                                <p class="mt-2 mb-1">
                                    "<?php echo htmlspecialchars($testimonial['content']); ?>"
                                </p>
                                
                                <small class="text-muted">
                                    Créé le <?php echo date('d/m/Y à H:i', strtotime($testimonial['created_at'])); ?>
                                    <?php if($testimonial['approved_by_name']): ?>
                                        - Approuvé par <?php echo htmlspecialchars($testimonial['approved_by_name']); ?>
                                    <?php endif; ?>
                                </small>
                            </div>
                            
                            <div class="col-md-3 text-end">
                                <div class="btn-group-vertical" role="group">
                                    <button type="button" class="btn btn-sm btn-outline-primary mb-1" 
                                            data-bs-toggle="modal" 
                                            data-bs-target="#editTestimonialModal<?php echo $testimonial['id']; ?>">
                                        <i class="fas fa-edit me-1"></i>Modifier
                                    </button>
                                    
                                    <?php if(!$testimonial['is_approved']): ?>
                                        <form method="POST" class="d-inline mb-1">
                                            <input type="hidden" name="testimonial_id" value="<?php echo $testimonial['id']; ?>">
                                            <button type="submit" name="approve_testimonial" class="btn btn-sm btn-success w-100">
                                                <i class="fas fa-check me-1"></i>Approuver
                                            </button>
                                        </form>
                                    <?php endif; ?>
                                    
                                    <form method="POST" class="d-inline mb-1">
                                        <input type="hidden" name="testimonial_id" value="<?php echo $testimonial['id']; ?>">
                                        <button type="submit" name="toggle_featured" 
                                                class="btn btn-sm <?php echo $testimonial['is_featured'] ? 'btn-warning' : 'btn-outline-warning'; ?> w-100">
                                            <i class="fas fa-star me-1"></i><?php echo $testimonial['is_featured'] ? 'Retirer' : 'Vedette'; ?>
                                        </button>
                                    </form>
                                    
                                    <button type="button" class="btn btn-sm btn-outline-danger" 
                                            onclick="confirmDelete(<?php echo $testimonial['id']; ?>)">
                                        <i class="fas fa-trash me-1"></i>Supprimer
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Modal d'édition -->
                    <div class="modal fade" id="editTestimonialModal<?php echo $testimonial['id']; ?>" tabindex="-1">
                        <div class="modal-dialog modal-lg">
                            <div class="modal-content">
                                <div class="modal-header">
                                    <h5 class="modal-title">Modifier le Témoignage</h5>
                                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                </div>
                                <form method="POST">
                                    <div class="modal-body">
                                        <input type="hidden" name="testimonial_id" value="<?php echo $testimonial['id']; ?>">
                                        
                                        <div class="row">
                                            <div class="col-md-6 mb-3">
                                                <label class="form-label">Nom du client</label>
                                                <input type="text" class="form-control" name="client_name" 
                                                       value="<?php echo htmlspecialchars($testimonial['client_name']); ?>" required>
                                            </div>
                                            <div class="col-md-6 mb-3">
                                                <label class="form-label">Poste</label>
                                                <input type="text" class="form-control" name="client_position" 
                                                       value="<?php echo htmlspecialchars($testimonial['client_position']); ?>">
                                            </div>
                                        </div>
                                        
                                        <div class="row">
                                            <div class="col-md-6 mb-3">
                                                <label class="form-label">Entreprise</label>
                                                <input type="text" class="form-control" name="client_company" 
                                                       value="<?php echo htmlspecialchars($testimonial['client_company']); ?>">
                                            </div>
                                            <div class="col-md-6 mb-3">
                                                <label class="form-label">Note</label>
                                                <select class="form-select" name="rating">
                                                    <?php for($i = 1; $i <= 5; $i++): ?>
                                                        <option value="<?php echo $i; ?>" <?php echo $testimonial['rating'] == $i ? 'selected' : ''; ?>>
                                                            <?php echo $i; ?> étoile<?php echo $i > 1 ? 's' : ''; ?>
                                                        </option>
                                                    <?php endfor; ?>
                                                </select>
                                            </div>
                                        </div>
                                        
                                        <div class="mb-3">
                                            <label class="form-label">Image du client</label>
                                            <input type="file" class="form-control" name="client_image_file" accept="image/*">
                                            <small class="text-muted">Ou utilisez une URL :</small>
                                            <input type="url" class="form-control mt-2" name="client_image" 
                                                   value="<?php echo htmlspecialchars($testimonial['client_image']); ?>" 
                                                   placeholder="https://exemple.com/image.jpg">
                                            <?php if($testimonial['client_image']): ?>
                                                <img src="<?php echo htmlspecialchars($testimonial['client_image']); ?>" 
                                                     class="image-preview mt-2" alt="Image actuelle" style="max-width: 100px; max-height: 100px; object-fit: cover; border-radius: 50%;">
                                            <?php endif; ?>
                                        </div>
                                        
                                        <div class="mb-3">
                                            <label class="form-label">Contenu du témoignage</label>
                                            <textarea class="form-control" name="content" rows="4" required><?php echo htmlspecialchars($testimonial['content']); ?></textarea>
                                        </div>
                                        
                                        <div class="row">
                                            <div class="col-md-6">
                                                <div class="form-check">
                                                    <input class="form-check-input" type="checkbox" name="is_approved" 
                                                           <?php echo $testimonial['is_approved'] ? 'checked' : ''; ?>>
                                                    <label class="form-check-label">Approuvé</label>
                                                </div>
                                            </div>
                                            <div class="col-md-6">
                                                <div class="form-check">
                                                    <input class="form-check-input" type="checkbox" name="is_featured" 
                                                           <?php echo $testimonial['is_featured'] ? 'checked' : ''; ?>>
                                                    <label class="form-check-label">En vedette</label>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="modal-footer">
                                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Annuler</button>
                                        <button type="submit" name="update_testimonial" class="btn btn-primary">Sauvegarder</button>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
    </div>
    
    <!-- Modal de création -->
    <div class="modal fade" id="createTestimonialModal" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Nouveau Témoignage</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <form method="POST">
                    <div class="modal-body">
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Nom du client</label>
                                <input type="text" class="form-control" name="client_name" required>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Poste</label>
                                <input type="text" class="form-control" name="client_position">
                            </div>
                        </div>
                        
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Entreprise</label>
                                <input type="text" class="form-control" name="client_company">
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Note</label>
                                <select class="form-select" name="rating">
                                    <option value="5" selected>5 étoiles</option>
                                    <option value="4">4 étoiles</option>
                                    <option value="3">3 étoiles</option>
                                    <option value="2">2 étoiles</option>
                                    <option value="1">1 étoile</option>
                                </select>
                            </div>
                        </div>
                        
                        <div class="mb-3">
                            <label class="form-label">URL de l'image</label>
                            <input type="url" class="form-control" name="client_image">
                        </div>
                        
                        <div class="mb-3">
                            <label class="form-label">Contenu du témoignage</label>
                            <textarea class="form-control" name="content" rows="4" required></textarea>
                        </div>
                        
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" name="is_approved">
                                    <label class="form-check-label">Approuvé</label>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" name="is_featured">
                                    <label class="form-check-label">En vedette</label>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Annuler</button>
                        <button type="submit" name="create_testimonial" class="btn btn-primary">Créer</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    
    <!-- Form de suppression -->
    <form method="POST" id="deleteForm">
        <input type="hidden" name="testimonial_id" id="deleteTestimonialId">
        <input type="hidden" name="delete_testimonial" value="1">
    </form>
    
    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    
    <script>
        function confirmDelete(testimonialId) {
            if(confirm('Êtes-vous sûr de vouloir supprimer ce témoignage ?')) {
                document.getElementById('deleteTestimonialId').value = testimonialId;
                document.getElementById('deleteForm').submit();
            }
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
