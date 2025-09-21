<?php
session_start();
require_once 'config/database.php';
require_once 'classes/Auth.php';
require_once 'classes/BlogManager.php';
require_once 'classes/ImageUploader.php';

$auth = new Auth();

// Vérifier si l'utilisateur est connecté
if(!$auth->isLoggedIn()) {
    header('Location: login.php');
    exit();
}

$database = new Database();
$conn = $database->getConnection();
$blogManager = new BlogManager($conn);
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
            if (isset($_FILES['featured_image']) && $_FILES['featured_image']['error'] !== UPLOAD_ERR_NO_FILE) {
                $uploadResult = $imageUploader->uploadImage($_FILES['featured_image'], 'blog', 'article');
                if ($uploadResult['success']) {
                    $imagePath = $uploadResult['filename'];
                } else {
                    $error_message = $uploadResult['message'];
                    break;
                }
            }
            
            $data = [
                'title' => $_POST['title'],
                'excerpt' => $_POST['excerpt'],
                'content' => $_POST['content'],
                'featured_image' => $imagePath,
                'category' => $_POST['category'],
                'tags' => json_encode(array_filter(array_map('trim', explode(',', $_POST['tags'])))),
                'author_id' => $_SESSION['admin_id'],
                'is_published' => isset($_POST['is_published']),
                'is_featured' => isset($_POST['is_featured'])
            ];
            
            if ($blogManager->createArticle($data)) {
                $success_message = "Article créé avec succès !";
            } else {
                $error_message = "Erreur lors de la création de l'article.";
                // Supprimer l'image si la création a échoué
                if ($imagePath) {
                    $imageUploader->deleteImage($imagePath);
                }
            }
            break;
            
        case 'update':
            $id = intval($_POST['id']);
            $currentArticle = $blogManager->getArticleById($id);
            $imagePath = $currentArticle['featured_image']; // Garder l'image actuelle par défaut
            
            // Gérer l'upload d'une nouvelle image si présent
            if (isset($_FILES['featured_image']) && $_FILES['featured_image']['error'] !== UPLOAD_ERR_NO_FILE) {
                $uploadResult = $imageUploader->uploadImage($_FILES['featured_image'], 'blog', 'article');
                if ($uploadResult['success']) {
                    // Supprimer l'ancienne image si elle existe
                    if ($currentArticle['featured_image']) {
                        $imageUploader->deleteImage($currentArticle['featured_image']);
                    }
                    $imagePath = $uploadResult['filename'];
                } else {
                    $error_message = $uploadResult['message'];
                    break;
                }
            }
            
            $data = [
                'title' => $_POST['title'],
                'excerpt' => $_POST['excerpt'],
                'content' => $_POST['content'],
                'featured_image' => $imagePath,
                'category' => $_POST['category'],
                'tags' => json_encode(array_filter(array_map('trim', explode(',', $_POST['tags'])))),
                'is_published' => isset($_POST['is_published']),
                'is_featured' => isset($_POST['is_featured'])
            ];
            
            if ($blogManager->updateArticle($id, $data)) {
                $success_message = "Article mis à jour avec succès !";
            } else {
                $error_message = "Erreur lors de la mise à jour de l'article.";
            }
            break;
            
        case 'delete':
            $id = intval($_POST['id']);
            if ($blogManager->deleteArticle($id)) {
                $success_message = "Article supprimé avec succès !";
            } else {
                $error_message = "Erreur lors de la suppression de l'article.";
            }
            break;
            
        case 'toggle_status':
            $id = intval($_POST['id']);
            if ($blogManager->toggleArticleStatus($id)) {
                $success_message = "Statut de l'article modifié avec succès !";
            } else {
                $error_message = "Erreur lors de la modification du statut.";
            }
            break;
    }
}

// Récupérer tous les articles
$articles = $blogManager->getAllArticles();
$total_articles = $blogManager->countArticles();
$published_articles = $blogManager->countArticles(true);
$categories = $blogManager->getCategories();

// Récupérer un article pour édition
$edit_article = null;
if (isset($_GET['edit'])) {
    $edit_article = $blogManager->getArticleById(intval($_GET['edit']));
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="utf-8">
    <title>Gestion du Blog - Universe Security Admin</title>
    
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
    $current_page = 'blog';
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
                                <h6 class="mb-0">Gestion du Blog</h6>
                                <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#articleModal">
                                    <i class="fa fa-plus me-2"></i>Nouvel Article
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
                                <div class="col-md-4">
                                    <div class="card bg-info text-white">
                                        <div class="card-body">
                                            <div class="d-flex align-items-center">
                                                <i class="fa fa-newspaper fa-2x me-3"></i>
                                                <div>
                                                    <h4 class="mb-0"><?php echo $total_articles; ?></h4>
                                                    <p class="mb-0">Total Articles</p>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="card bg-success text-white">
                                        <div class="card-body">
                                            <div class="d-flex align-items-center">
                                                <i class="fa fa-eye fa-2x me-3"></i>
                                                <div>
                                                    <h4 class="mb-0"><?php echo $published_articles; ?></h4>
                                                    <p class="mb-0">Articles Publiés</p>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="card bg-warning text-white">
                                        <div class="card-body">
                                            <div class="d-flex align-items-center">
                                                <i class="fa fa-tags fa-2x me-3"></i>
                                                <div>
                                                    <h4 class="mb-0"><?php echo count($categories); ?></h4>
                                                    <p class="mb-0">Catégories</p>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Table des articles -->
                            <div class="table-responsive">
                                <table class="table table-striped">
                                    <thead>
                                        <tr>
                                            <th>ID</th>
                                            <th>Titre</th>
                                            <th>Catégorie</th>
                                            <th>Auteur</th>
                                            <th>Statut</th>
                                            <th>Vedette</th>
                                            <th>Vues</th>
                                            <th>Date</th>
                                            <th>Actions</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($articles as $article): ?>
                                            <tr>
                                                <td><?php echo $article['id']; ?></td>
                                                <td>
                                                    <strong><?php echo htmlspecialchars($article['title']); ?></strong>
                                                    <?php if (!empty($article['featured_image'])): ?>
                                                        <i class="fa fa-image ms-2 text-primary"></i>
                                                    <?php endif; ?>
                                                </td>
                                                <td>
                                                    <?php if ($article['category']): ?>
                                                        <span class="badge bg-secondary"><?php echo htmlspecialchars($article['category']); ?></span>
                                                    <?php endif; ?>
                                                </td>
                                                <td><?php echo htmlspecialchars($article['author_name'] ?? 'Inconnu'); ?></td>
                                                <td>
                                                    <?php if ($article['is_published']): ?>
                                                        <span class="badge bg-success">Publié</span>
                                                    <?php else: ?>
                                                        <span class="badge bg-warning">Brouillon</span>
                                                    <?php endif; ?>
                                                </td>
                                                <td>
                                                    <?php if ($article['is_featured']): ?>
                                                        <span class="badge bg-warning">Vedette</span>
                                                    <?php else: ?>
                                                        <span class="badge bg-secondary">Normal</span>
                                                    <?php endif; ?>
                                                </td>
                                                <td><?php echo $article['views_count']; ?></td>
                                                <td>
                                                    <?php if ($article['published_at']): ?>
                                                        <?php echo date('d/m/Y', strtotime($article['published_at'])); ?>
                                                    <?php else: ?>
                                                        <span class="text-muted">Non publié</span>
                                                    <?php endif; ?>
                                                </td>
                                                <td>
                                                    <div class="btn-group" role="group">
                                                        <a href="?edit=<?php echo $article['id']; ?>" class="btn btn-sm btn-outline-primary">
                                                            <i class="fa fa-edit"></i>
                                                        </a>
                                                        <form method="POST" style="display: inline;" onsubmit="return confirm('Changer le statut de cet article ?')">
                                                            <input type="hidden" name="action" value="toggle_status">
                                                            <input type="hidden" name="id" value="<?php echo $article['id']; ?>">
                                                            <button type="submit" class="btn btn-sm btn-outline-warning">
                                                                <i class="fa fa-<?php echo $article['is_published'] ? 'eye-slash' : 'eye'; ?>"></i>
                                                            </button>
                                                        </form>
                                                        <form method="POST" style="display: inline;" onsubmit="return confirm('Supprimer cet article ?')">
                                                            <input type="hidden" name="action" value="delete">
                                                            <input type="hidden" name="id" value="<?php echo $article['id']; ?>">
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

    <!-- Modal Article -->
    <div class="modal fade" id="articleModal" tabindex="-1">
        <div class="modal-dialog modal-xl">
            <div class="modal-content">
                <form method="POST" enctype="multipart/form-data">
                    <div class="modal-header">
                        <h5 class="modal-title"><?php echo $edit_article ? 'Modifier l\'Article' : 'Nouvel Article'; ?></h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        <input type="hidden" name="action" value="<?php echo $edit_article ? 'update' : 'create'; ?>">
                        <?php if ($edit_article): ?>
                            <input type="hidden" name="id" value="<?php echo $edit_article['id']; ?>">
                        <?php endif; ?>

                        <div class="row">
                            <div class="col-md-8">
                                <div class="mb-3">
                                    <label class="form-label">Titre *</label>
                                    <input type="text" class="form-control" name="title" required 
                                           value="<?php echo $edit_article ? htmlspecialchars($edit_article['title']) : ''; ?>">
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="mb-3">
                                    <label class="form-label">Catégorie</label>
                                    <input type="text" class="form-control" name="category" list="categories"
                                           value="<?php echo $edit_article ? htmlspecialchars($edit_article['category']) : ''; ?>">
                                    <datalist id="categories">
                                        <?php foreach ($categories as $cat): ?>
                                            <option value="<?php echo htmlspecialchars($cat); ?>">
                                        <?php endforeach; ?>
                                    </datalist>
                                </div>
                            </div>
                        </div>

                        <!-- Upload d'image -->
                        <div class="mb-3">
                            <label class="form-label">Image à la une</label>
                            <input type="file" class="form-control" name="featured_image" accept="image/*">
                            <div class="form-text">Formats acceptés: JPG, PNG, GIF, WebP (max 5MB)</div>
                            <?php if ($edit_article && !empty($edit_article['featured_image'])): ?>
                                <div class="mt-2">
                                    <small class="text-muted">Image actuelle:</small><br>
                                    <img src="../<?php echo htmlspecialchars($edit_article['featured_image']); ?>" 
                                         alt="Image actuelle" style="max-width: 200px; max-height: 150px;" class="img-thumbnail">
                                </div>
                            <?php endif; ?>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Résumé</label>
                            <textarea class="form-control" name="excerpt" rows="2"><?php echo $edit_article ? htmlspecialchars($edit_article['excerpt']) : ''; ?></textarea>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Contenu *</label>
                            <textarea class="form-control" name="content" rows="10" required><?php echo $edit_article ? htmlspecialchars($edit_article['content']) : ''; ?></textarea>
                        </div>

                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label class="form-label">Image mise en avant</label>
                                    <input type="text" class="form-control" name="featured_image" placeholder="img/blog-1.jpg"
                                           value="<?php echo $edit_article ? htmlspecialchars($edit_article['featured_image']) : ''; ?>">
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label class="form-label">Tags (séparés par des virgules)</label>
                                    <input type="text" class="form-control" name="tags" placeholder="sécurité, technologie, conseils"
                                           value="<?php 
                                               if ($edit_article && $edit_article['tags']) {
                                                   $tags = json_decode($edit_article['tags'], true);
                                                   echo htmlspecialchars(implode(', ', $tags));
                                               }
                                           ?>">
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <div class="form-check">
                                        <input class="form-check-input" type="checkbox" name="is_published" id="is_published"
                                               <?php echo ($edit_article && $edit_article['is_published']) ? 'checked' : ''; ?>>
                                        <label class="form-check-label" for="is_published">
                                            Publier l'article
                                        </label>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <div class="form-check">
                                        <input class="form-check-input" type="checkbox" name="is_featured" id="is_featured"
                                               <?php echo ($edit_article && $edit_article['is_featured']) ? 'checked' : ''; ?>>
                                        <label class="form-check-label" for="is_featured">
                                            Article en vedette
                                        </label>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Annuler</button>
                        <button type="submit" class="btn btn-primary">
                            <?php echo $edit_article ? 'Mettre à jour' : 'Créer'; ?>
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

    <?php if ($edit_article): ?>
    <script>
        $(document).ready(function() {
            $('#articleModal').modal('show');
        });
    </script>
    <?php endif; ?>
</body>
</html>
