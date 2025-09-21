<?php
require_once 'config/database.php';
require_once 'classes/Auth.php';
require_once 'classes/QuoteManager.php';

$auth = new Auth();

// Vérifier si l'utilisateur est connecté
if(!$auth->isLoggedIn()) {
    header('Location: login.php');
    exit();
}

$quoteManager = new QuoteManager();
$message = '';
$error = '';

// Traitement des actions
if($_SERVER['REQUEST_METHOD'] == 'POST') {
    if(isset($_POST['update_quote'])) {
        $quote_id = $_POST['quote_id'];
        $data = [
            'status' => $_POST['status'],
            'priority' => $_POST['priority'],
            'admin_notes' => $_POST['admin_notes'],
            'assigned_to' => $_POST['assigned_to'] ?: null
        ];
        
        if($quoteManager->updateQuote($quote_id, $data, $_SESSION['admin_id'])) {
            $message = 'Devis mis à jour avec succès.';
        } else {
            $error = 'Erreur lors de la mise à jour du devis.';
        }
    }
    
    if(isset($_POST['delete_quote'])) {
        $quote_id = $_POST['quote_id'];
        if($quoteManager->deleteQuote($quote_id, $_SESSION['admin_id'])) {
            $message = 'Devis supprimé avec succès.';
        } else {
            $error = 'Erreur lors de la suppression du devis.';
        }
    }
}

// Filtres et recherche
$status_filter = $_GET['status'] ?? '';
$search = $_GET['search'] ?? '';
$page = max(1, intval($_GET['page'] ?? 1));
$limit = 20;
$offset = ($page - 1) * $limit;

// Récupérer les devis
if($search) {
    $quotes = $quoteManager->searchQuotes($search, $status_filter ?: null);
} else {
    $quotes = $quoteManager->getAllQuotes($status_filter ?: null, $limit, $offset);
}

// Récupérer la liste des admins pour l'assignation
$database = new Database();
$conn = $database->getConnection();
$query = "SELECT id, full_name FROM admins WHERE is_active = 1 ORDER BY full_name";
$stmt = $conn->prepare($query);
$stmt->execute();
$admins = $stmt->fetchAll();

// Devis sélectionné pour affichage détaillé
$selected_quote = null;
if(isset($_GET['view'])) {
    $selected_quote = $quoteManager->getQuoteById($_GET['view']);
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Demandes de Devis - Universe Security Admin</title>
    
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
        }
        
        .content-card {
            background: white;
            border-radius: 15px;
            padding: 25px;
            box-shadow: 0 5px 15px rgba(0,0,0,0.08);
            margin-bottom: 20px;
        }
        
        .status-badge {
            padding: 5px 12px;
            border-radius: 20px;
            font-size: 0.8rem;
            font-weight: 600;
        }
        
        .status-nouveau { background-color: #e3f2fd; color: #1976d2; }
        .status-en_cours { background-color: #fff3e0; color: #f57c00; }
        .status-traite { background-color: #e8f5e8; color: #388e3c; }
        .status-refuse { background-color: #ffebee; color: #d32f2f; }
        
        .priority-badge {
            padding: 3px 8px;
            border-radius: 15px;
            font-size: 0.7rem;
            font-weight: 600;
        }
        
        .priority-haute { background-color: #ffebee; color: #d32f2f; }
        .priority-normale { background-color: #e3f2fd; color: #1976d2; }
        .priority-basse { background-color: #e8f5e8; color: #388e3c; }
        .priority-urgente { background-color: #fff3e0; color: #f57c00; }
        
        .quote-card {
            border: 1px solid #dee2e6;
            border-radius: 10px;
            padding: 15px;
            margin-bottom: 15px;
            transition: all 0.3s ease;
        }
        
        .quote-card:hover {
            box-shadow: 0 5px 15px rgba(0,0,0,0.1);
            transform: translateY(-2px);
        }
        
        .filter-section {
            background: white;
            border-radius: 10px;
            padding: 20px;
            margin-bottom: 20px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.05);
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
    $current_page = 'quotes';
    // Inclure la navbar commune
    include 'includes/admin_navbar.php'; 
    ?>
    
    <!-- Main Content -->
    <div class="main-content">
        <!-- Header -->
        <div class="d-flex justify-content-between align-items-center mb-4">
            <div>
                <h1 class="h3 mb-0">Gestion des Demandes de Devis</h1>
                <p class="text-muted">Gérez et suivez toutes les demandes de devis</p>
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
        
        <!-- Filtres -->
        <div class="filter-section">
            <form method="GET" class="row g-3">
                <div class="col-md-4">
                    <label for="search" class="form-label">Rechercher</label>
                    <input type="text" class="form-control" id="search" name="search" 
                           value="<?php echo htmlspecialchars($search); ?>" 
                           placeholder="Nom, email, service...">
                </div>
                <div class="col-md-3">
                    <label for="status" class="form-label">Statut</label>
                    <select class="form-select" id="status" name="status">
                        <option value="">Tous les statuts</option>
                        <option value="nouveau" <?php echo $status_filter === 'nouveau' ? 'selected' : ''; ?>>Nouveau</option>
                        <option value="en_cours" <?php echo $status_filter === 'en_cours' ? 'selected' : ''; ?>>En cours</option>
                        <option value="traite" <?php echo $status_filter === 'traite' ? 'selected' : ''; ?>>Traité</option>
                        <option value="refuse" <?php echo $status_filter === 'refuse' ? 'selected' : ''; ?>>Refusé</option>
                    </select>
                </div>
                <div class="col-md-3 d-flex align-items-end">
                    <button type="submit" class="btn btn-primary me-2">
                        <i class="fas fa-search me-1"></i>Filtrer
                    </button>
                    <a href="quotes.php" class="btn btn-outline-secondary">
                        <i class="fas fa-times me-1"></i>Reset
                    </a>
                </div>
            </form>
        </div>
        
        <div class="row">
            <!-- Liste des devis -->
            <div class="col-lg-<?php echo $selected_quote ? '8' : '12'; ?>">
                <div class="content-card">
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <h5 class="mb-0">Liste des Devis (<?php echo count($quotes); ?>)</h5>
                    </div>
                    
                    <?php if(empty($quotes)): ?>
                        <div class="text-center py-5">
                            <i class="fas fa-inbox fa-3x text-muted mb-3"></i>
                            <p class="text-muted">Aucun devis trouvé</p>
                        </div>
                    <?php else: ?>
                        <?php foreach($quotes as $quote): ?>
                            <div class="quote-card">
                                <div class="row align-items-center">
                                    <div class="col-md-3">
                                        <strong><?php echo htmlspecialchars($quote['name']); ?></strong><br>
                                        <small class="text-muted"><?php echo htmlspecialchars($quote['email']); ?></small><br>
                                        <?php if($quote['phone']): ?>
                                            <small class="text-muted"><i class="fas fa-phone me-1"></i><?php echo htmlspecialchars($quote['phone']); ?></small>
                                        <?php endif; ?>
                                    </div>
                                    <div class="col-md-2">
                                        <span class="badge bg-secondary"><?php echo htmlspecialchars($quote['service']); ?></span>
                                    </div>
                                    <div class="col-md-2">
                                        <span class="status-badge status-<?php echo $quote['status']; ?>">
                                            <?php echo ucfirst(str_replace('_', ' ', $quote['status'])); ?>
                                        </span><br>
                                        <span class="priority-badge priority-<?php echo $quote['priority']; ?>">
                                            <?php echo ucfirst($quote['priority']); ?>
                                        </span>
                                    </div>
                                    <div class="col-md-2">
                                        <small class="text-muted">
                                            <?php echo date('d/m/Y', strtotime($quote['created_at'])); ?><br>
                                            <?php echo date('H:i', strtotime($quote['created_at'])); ?>
                                        </small>
                                    </div>
                                    <div class="col-md-3 text-end">
                                        <a href="?view=<?php echo $quote['id']; ?>" class="btn btn-sm btn-primary">
                                            <i class="fas fa-eye"></i>
                                        </a>
                                        <button type="button" class="btn btn-sm btn-warning" 
                                                data-bs-toggle="modal" 
                                                data-bs-target="#editModal<?php echo $quote['id']; ?>">
                                            <i class="fas fa-edit"></i>
                                        </button>
                                        <button type="button" class="btn btn-sm btn-danger" 
                                                onclick="confirmDelete(<?php echo $quote['id']; ?>)">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    </div>
                                </div>
                                
                                <?php if($quote['message']): ?>
                                    <div class="mt-2">
                                        <small class="text-muted">
                                            <strong>Message:</strong> 
                                            <?php echo htmlspecialchars(substr($quote['message'], 0, 100)); ?>
                                            <?php if(strlen($quote['message']) > 100): ?>...<?php endif; ?>
                                        </small>
                                    </div>
                                <?php endif; ?>
                            </div>
                            
                            <!-- Modal d'édition -->
                            <div class="modal fade" id="editModal<?php echo $quote['id']; ?>" tabindex="-1">
                                <div class="modal-dialog">
                                    <div class="modal-content">
                                        <div class="modal-header">
                                            <h5 class="modal-title">Modifier le Devis</h5>
                                            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                        </div>
                                        <form method="POST">
                                            <div class="modal-body">
                                                <input type="hidden" name="quote_id" value="<?php echo $quote['id']; ?>">
                                                
                                                <div class="mb-3">
                                                    <label class="form-label">Statut</label>
                                                    <select class="form-select" name="status" required>
                                                        <option value="nouveau" <?php echo $quote['status'] === 'nouveau' ? 'selected' : ''; ?>>Nouveau</option>
                                                        <option value="en_cours" <?php echo $quote['status'] === 'en_cours' ? 'selected' : ''; ?>>En cours</option>
                                                        <option value="traite" <?php echo $quote['status'] === 'traite' ? 'selected' : ''; ?>>Traité</option>
                                                        <option value="refuse" <?php echo $quote['status'] === 'refuse' ? 'selected' : ''; ?>>Refusé</option>
                                                    </select>
                                                </div>
                                                
                                                <div class="mb-3">
                                                    <label class="form-label">Priorité</label>
                                                    <select class="form-select" name="priority" required>
                                                        <option value="basse" <?php echo $quote['priority'] === 'basse' ? 'selected' : ''; ?>>Basse</option>
                                                        <option value="normale" <?php echo $quote['priority'] === 'normale' ? 'selected' : ''; ?>>Normale</option>
                                                        <option value="haute" <?php echo $quote['priority'] === 'haute' ? 'selected' : ''; ?>>Haute</option>
                                                        <option value="urgente" <?php echo $quote['priority'] === 'urgente' ? 'selected' : ''; ?>>Urgente</option>
                                                    </select>
                                                </div>
                                                
                                                <div class="mb-3">
                                                    <label class="form-label">Assigner à</label>
                                                    <select class="form-select" name="assigned_to">
                                                        <option value="">Non assigné</option>
                                                        <?php foreach($admins as $admin): ?>
                                                            <option value="<?php echo $admin['id']; ?>" 
                                                                    <?php echo $quote['assigned_to'] == $admin['id'] ? 'selected' : ''; ?>>
                                                                <?php echo htmlspecialchars($admin['full_name']); ?>
                                                            </option>
                                                        <?php endforeach; ?>
                                                    </select>
                                                </div>
                                                
                                                <div class="mb-3">
                                                    <label class="form-label">Notes administrateur</label>
                                                    <textarea class="form-control" name="admin_notes" rows="3"><?php echo htmlspecialchars($quote['admin_notes'] ?? ''); ?></textarea>
                                                </div>
                                            </div>
                                            <div class="modal-footer">
                                                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Annuler</button>
                                                <button type="submit" name="update_quote" class="btn btn-primary">Sauvegarder</button>
                                            </div>
                                        </form>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>
            </div>
            
            <!-- Détails du devis sélectionné -->
            <?php if($selected_quote): ?>
                <div class="col-lg-4">
                    <div class="content-card">
                        <div class="d-flex justify-content-between align-items-center mb-3">
                            <h5 class="mb-0">Détails du Devis</h5>
                            <a href="quotes.php" class="btn btn-sm btn-outline-secondary">
                                <i class="fas fa-times"></i>
                            </a>
                        </div>
                        
                        <div class="mb-3">
                            <strong>Client:</strong><br>
                            <?php echo htmlspecialchars($selected_quote['name']); ?><br>
                            <small class="text-muted"><?php echo htmlspecialchars($selected_quote['email']); ?></small>
                            <?php if($selected_quote['phone']): ?>
                                <br><small class="text-muted"><?php echo htmlspecialchars($selected_quote['phone']); ?></small>
                            <?php endif; ?>
                        </div>
                        
                        <div class="mb-3">
                            <strong>Service:</strong><br>
                            <span class="badge bg-secondary"><?php echo htmlspecialchars($selected_quote['service']); ?></span>
                        </div>
                        
                        <div class="mb-3">
                            <strong>Statut:</strong><br>
                            <span class="status-badge status-<?php echo $selected_quote['status']; ?>">
                                <?php echo ucfirst(str_replace('_', ' ', $selected_quote['status'])); ?>
                            </span>
                        </div>
                        
                        <div class="mb-3">
                            <strong>Priorité:</strong><br>
                            <span class="priority-badge priority-<?php echo $selected_quote['priority']; ?>">
                                <?php echo ucfirst($selected_quote['priority']); ?>
                            </span>
                        </div>
                        
                        <?php if($selected_quote['assigned_admin']): ?>
                            <div class="mb-3">
                                <strong>Assigné à:</strong><br>
                                <?php echo htmlspecialchars($selected_quote['assigned_admin']); ?>
                            </div>
                        <?php endif; ?>
                        
                        <div class="mb-3">
                            <strong>Date de création:</strong><br>
                            <?php echo date('d/m/Y H:i', strtotime($selected_quote['created_at'])); ?>
                        </div>
                        
                        <?php if($selected_quote['message']): ?>
                            <div class="mb-3">
                                <strong>Message:</strong><br>
                                <div class="bg-light p-3 rounded">
                                    <?php echo nl2br(htmlspecialchars($selected_quote['message'])); ?>
                                </div>
                            </div>
                        <?php endif; ?>
                        
                        <?php if($selected_quote['admin_notes']): ?>
                            <div class="mb-3">
                                <strong>Notes administrateur:</strong><br>
                                <div class="bg-warning bg-opacity-10 p-3 rounded">
                                    <?php echo nl2br(htmlspecialchars($selected_quote['admin_notes'])); ?>
                                </div>
                            </div>
                        <?php endif; ?>
                        
                        <div class="d-grid gap-2">
                            <button type="button" class="btn btn-primary" 
                                    data-bs-toggle="modal" 
                                    data-bs-target="#editModal<?php echo $selected_quote['id']; ?>">
                                <i class="fas fa-edit me-2"></i>Modifier
                            </button>
                            <a href="mailto:<?php echo htmlspecialchars($selected_quote['email']); ?>" class="btn btn-outline-primary">
                                <i class="fas fa-envelope me-2"></i>Répondre par email
                            </a>
                        </div>
                    </div>
                </div>
            <?php endif; ?>
        </div>
    </div>
    
    <!-- Modal de suppression -->
    <form method="POST" id="deleteForm">
        <input type="hidden" name="quote_id" id="deleteQuoteId">
        <input type="hidden" name="delete_quote" value="1">
    </form>
    
    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    
    <script>
        function confirmDelete(quoteId) {
            if(confirm('Êtes-vous sûr de vouloir supprimer ce devis ?')) {
                document.getElementById('deleteQuoteId').value = quoteId;
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
