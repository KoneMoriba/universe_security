<?php
session_start();
require_once 'config/database.php';
require_once 'classes/Auth.php';
require_once 'classes/OfferManager.php';
require_once 'classes/ImageUploader.php';

$auth = new Auth();

// Vérifier si l'utilisateur est connecté
if(!$auth->isLoggedIn()) {
    header('Location: login.php');
    exit();
}

$database = new Database();
$conn = $database->getConnection();
$offerManager = new OfferManager($conn);
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
                $uploadResult = $imageUploader->uploadImage($_FILES['image'], 'offers', 'offer');
                if ($uploadResult['success']) {
                    $imagePath = $uploadResult['filename'];
                } else {
                    $error_message = $uploadResult['message'];
                    break;
                }
            }
            
            $data = [
                'title' => $_POST['title'],
                'subtitle' => $_POST['subtitle'],
                'description' => $_POST['description'],
                'features' => json_encode(array_filter(explode("\n", $_POST['features']))),
                'price' => !empty($_POST['price']) ? floatval($_POST['price']) : null,
                'price_text' => $_POST['price_text'],
                'icon' => $_POST['icon'],
                'image' => $imagePath,
                'is_featured' => isset($_POST['is_featured']),
                'is_active' => isset($_POST['is_active']),
                'display_order' => intval($_POST['display_order']),
                'created_by' => $_SESSION['admin_id']
            ];
            
            if ($offerManager->createOffer($data)) {
                $success_message = "Offre créée avec succès !";
            } else {
                $error_message = "Erreur lors de la création de l'offre.";
                // Supprimer l'image si la création a échoué
                if ($imagePath) {
                    $imageUploader->deleteImage($imagePath);
                }
            }
            break;
            
        case 'update':
            $id = intval($_POST['id']);
            $currentOffer = $offerManager->getOfferById($id);
            $imagePath = $currentOffer['image']; // Garder l'image actuelle par défaut
            
            // Gérer l'upload d'une nouvelle image si présent
            if (isset($_FILES['image']) && $_FILES['image']['error'] !== UPLOAD_ERR_NO_FILE) {
                $uploadResult = $imageUploader->uploadImage($_FILES['image'], 'offers', 'offer');
                if ($uploadResult['success']) {
                    // Supprimer l'ancienne image si elle existe
                    if ($currentOffer['image']) {
                        $imageUploader->deleteImage($currentOffer['image']);
                    }
                    $imagePath = $uploadResult['filename'];
                } else {
                    $error_message = $uploadResult['message'];
                    break;
                }
            }
            
            $data = [
                'title' => $_POST['title'],
                'subtitle' => $_POST['subtitle'],
                'description' => $_POST['description'],
                'features' => json_encode(array_filter(explode("\n", $_POST['features']))),
                'price' => !empty($_POST['price']) ? floatval($_POST['price']) : null,
                'price_text' => $_POST['price_text'],
                'icon' => $_POST['icon'],
                'image' => $imagePath,
                'is_featured' => isset($_POST['is_featured']),
                'is_active' => isset($_POST['is_active']),
                'display_order' => intval($_POST['display_order'])
            ];
            
            if ($offerManager->updateOffer($id, $data)) {
                $success_message = "Offre mise à jour avec succès !";
            } else {
                $error_message = "Erreur lors de la mise à jour de l'offre.";
            }
            break;
            
        case 'delete':
            $id = intval($_POST['id']);
            if ($offerManager->deleteOffer($id)) {
                $success_message = "Offre supprimée avec succès !";
            } else {
                $error_message = "Erreur lors de la suppression de l'offre.";
            }
            break;
            
        case 'toggle_status':
            $id = intval($_POST['id']);
            if ($offerManager->toggleOfferStatus($id)) {
                $success_message = "Statut de l'offre modifié avec succès !";
            } else {
                $error_message = "Erreur lors de la modification du statut.";
            }
            break;
    }
}

// Récupérer toutes les offres
$offers = $offerManager->getAllOffers();
$total_offers = $offerManager->countOffers();
$active_offers = $offerManager->countOffers(true);

// Récupérer une offre pour édition
$edit_offer = null;
if (isset($_GET['edit'])) {
    $edit_offer = $offerManager->getOfferById(intval($_GET['edit']));
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="utf-8">
    <title>Gestion des Offres - Universe Security Admin</title>
    
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
    $current_page = 'offers';
    // Inclure la navbar commune
    include 'includes/admin_navbar.php'; 
    ?>

        <!-- Content -->
        <div class="main-content">

            <!-- Main Content -->
            <div class="container-fluid pt-4 px-4">
                <div class="row g-4">
                    <div class="col-12">
                        <div class="bg-light rounded h-100 p-4">
                            <div class="d-flex align-items-center justify-content-between mb-4">
                                <h6 class="mb-0">Gestion des Offres</h6>
                                <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#offerModal">
                                    <i class="fa fa-plus me-2"></i>Nouvelle Offre
                                </button>
                            </div>

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
                                    <div class="card bg-primary text-white">
                                        <div class="card-body">
                                            <div class="d-flex align-items-center">
                                                <i class="fa fa-box fa-2x me-3"></i>
                                                <div>
                                                    <h4 class="mb-0"><?php echo $total_offers; ?></h4>
                                                    <p class="mb-0">Total Offres</p>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="card bg-success text-white">
                                        <div class="card-body">
                                            <div class="d-flex align-items-center">
                                                <i class="fa fa-check-circle fa-2x me-3"></i>
                                                <div>
                                                    <h4 class="mb-0"><?php echo $active_offers; ?></h4>
                                                    <p class="mb-0">Offres Actives</p>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Table des offres -->
                            <div class="table-responsive">
                                <table class="table table-striped">
                                    <thead>
                                        <tr>
                                            <th>ID</th>
                                            <th>Image</th>
                                            <th>Titre</th>
                                            <th>Sous-titre</th>
                                            <th>Prix</th>
                                            <th>Vedette</th>
                                            <th>Statut</th>
                                            <th>Ordre</th>
                                            <th>Actions</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($offers as $offer): ?>
                                            <tr>
                                                <td><?php echo $offer['id']; ?></td>
                                                <td>
                                                    <?php if (!empty($offer['image'])): ?>
                                                        <img src="../<?php echo htmlspecialchars($offer['image']); ?>" 
                                                             alt="<?php echo htmlspecialchars($offer['title']); ?>" 
                                                             style="width: 50px; height: 50px; object-fit: cover;" 
                                                             class="rounded">
                                                    <?php else: ?>
                                                        <div class="bg-light d-flex align-items-center justify-content-center rounded" 
                                                             style="width: 50px; height: 50px;">
                                                            <i class="fa fa-image text-muted"></i>
                                                        </div>
                                                    <?php endif; ?>
                                                </td>
                                                <td>
                                                    <strong><?php echo htmlspecialchars($offer['title']); ?></strong>
                                                    <?php if (!empty($offer['icon'])): ?>
                                                        <i class="fa <?php echo htmlspecialchars($offer['icon']); ?> ms-2 text-primary"></i>
                                                    <?php endif; ?>
                                                </td>
                                                <td><?php echo htmlspecialchars($offer['subtitle']); ?></td>
                                                <td>
                                                    <?php if ($offer['price']): ?>
                                                        <?php echo number_format($offer['price'], 0, ',', ' '); ?> FCFA
                                                    <?php elseif ($offer['price_text']): ?>
                                                        <?php echo htmlspecialchars($offer['price_text']); ?>
                                                    <?php else: ?>
                                                        <span class="text-muted">Non défini</span>
                                                    <?php endif; ?>
                                                </td>
                                                <td>
                                                    <?php if ($offer['is_featured']): ?>
                                                        <span class="badge bg-warning">Vedette</span>
                                                    <?php else: ?>
                                                        <span class="badge bg-secondary">Normal</span>
                                                    <?php endif; ?>
                                                </td>
                                                <td>
                                                    <?php if ($offer['is_active']): ?>
                                                        <span class="badge bg-success">Actif</span>
                                                    <?php else: ?>
                                                        <span class="badge bg-danger">Inactif</span>
                                                    <?php endif; ?>
                                                </td>
                                                <td><?php echo $offer['display_order']; ?></td>
                                                <td>
                                                    <div class="btn-group" role="group">
                                                        <a href="?edit=<?php echo $offer['id']; ?>" class="btn btn-sm btn-outline-primary">
                                                            <i class="fa fa-edit"></i>
                                                        </a>
                                                        <form method="POST" style="display: inline;" onsubmit="return confirm('Changer le statut de cette offre ?')">
                                                            <input type="hidden" name="action" value="toggle_status">
                                                            <input type="hidden" name="id" value="<?php echo $offer['id']; ?>">
                                                            <button type="submit" class="btn btn-sm btn-outline-warning">
                                                                <i class="fa fa-toggle-<?php echo $offer['is_active'] ? 'on' : 'off'; ?>"></i>
                                                            </button>
                                                        </form>
                                                        <form method="POST" style="display: inline;" onsubmit="return confirm('Supprimer cette offre ?')">
                                                            <input type="hidden" name="action" value="delete">
                                                            <input type="hidden" name="id" value="<?php echo $offer['id']; ?>">
                                                            <button type="submit" class="btn btn-sm btn-outline-danger">
                                                                <i class="fa fa-trash"></i>
                                                            </button>
                                                        </form>
                                                    </div>
                                                </td>
                                            </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal Offre -->
    <div class="modal fade" id="offerModal" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <form method="POST" enctype="multipart/form-data">
                    <div class="modal-header">
                        <h5 class="modal-title"><?php echo $edit_offer ? 'Modifier l\'Offre' : 'Nouvelle Offre'; ?></h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        <input type="hidden" name="action" value="<?php echo $edit_offer ? 'update' : 'create'; ?>">
                        <?php if ($edit_offer): ?>
                            <input type="hidden" name="id" value="<?php echo $edit_offer['id']; ?>">
                        <?php endif; ?>

                        <div class="row">
                            <div class="col-md-8">
                                <div class="mb-3">
                                    <label class="form-label">Titre *</label>
                                    <input type="text" class="form-control" name="title" required 
                                           value="<?php echo $edit_offer ? htmlspecialchars($edit_offer['title']) : ''; ?>">
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="mb-3">
                                    <label class="form-label">Icône</label>
                                    <input type="text" class="form-control" name="icon" placeholder="fa-code"
                                           value="<?php echo $edit_offer ? htmlspecialchars($edit_offer['icon']) : ''; ?>">
                                </div>
                            </div>
                        </div>

                        <!-- Upload d'image -->
                        <div class="mb-3">
                            <label class="form-label">Image de l'offre</label>
                            <input type="file" class="form-control" name="image" accept="image/*">
                            <div class="form-text">Formats acceptés: JPG, PNG, GIF, WebP (max 5MB)</div>
                            <?php if ($edit_offer && !empty($edit_offer['image'])): ?>
                                <div class="mt-2">
                                    <small class="text-muted">Image actuelle:</small><br>
                                    <img src="../<?php echo htmlspecialchars($edit_offer['image']); ?>" 
                                         alt="Image actuelle" style="max-width: 150px; max-height: 100px;" class="img-thumbnail">
                                </div>
                            <?php endif; ?>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Sous-titre</label>
                            <input type="text" class="form-control" name="subtitle"
                                   value="<?php echo $edit_offer ? htmlspecialchars($edit_offer['subtitle']) : ''; ?>">
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Description</label>
                            <textarea class="form-control" name="description" rows="3"><?php echo $edit_offer ? htmlspecialchars($edit_offer['description']) : ''; ?></textarea>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Fonctionnalités (une par ligne)</label>
                            <textarea class="form-control" name="features" rows="5"><?php 
                                if ($edit_offer && $edit_offer['features']) {
                                    $features = json_decode($edit_offer['features'], true);
                                    echo htmlspecialchars(implode("\n", $features));
                                }
                            ?></textarea>
                        </div>

                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label class="form-label">Prix (FCFA)</label>
                                    <input type="number" class="form-control" name="price" step="0.01"
                                           value="<?php echo $edit_offer ? $edit_offer['price'] : ''; ?>">
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label class="form-label">Texte du prix</label>
                                    <input type="text" class="form-control" name="price_text" placeholder="Sur devis"
                                           value="<?php echo $edit_offer ? htmlspecialchars($edit_offer['price_text']) : ''; ?>">
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-4">
                                <div class="mb-3">
                                    <label class="form-label">Ordre d'affichage</label>
                                    <input type="number" class="form-control" name="display_order"
                                           value="<?php echo $edit_offer ? $edit_offer['display_order'] : '0'; ?>">
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="mb-3">
                                    <div class="form-check">
                                        <input class="form-check-input" type="checkbox" name="is_featured" id="is_featured"
                                               <?php echo ($edit_offer && $edit_offer['is_featured']) ? 'checked' : ''; ?>>
                                        <label class="form-check-label" for="is_featured">
                                            Offre en vedette
                                        </label>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="mb-3">
                                    <div class="form-check">
                                        <input class="form-check-input" type="checkbox" name="is_active" id="is_active"
                                               <?php echo (!$edit_offer || $edit_offer['is_active']) ? 'checked' : ''; ?>>
                                        <label class="form-check-label" for="is_active">
                                            Offre active
                                        </label>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Annuler</button>
                        <button type="submit" class="btn btn-primary">
                            <?php echo $edit_offer ? 'Mettre à jour' : 'Créer'; ?>
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

    <?php if ($edit_offer): ?>
    <script>
        $(document).ready(function() {
            $('#offerModal').modal('show');
        });
    </script>
    <?php endif; ?>
</body>
</html>
