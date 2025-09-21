<?php
require_once 'config/database.php';
require_once 'classes/Auth.php';
require_once 'classes/ContactManager.php';

$auth = new Auth();

// Vérifier si l'utilisateur est connecté
if(!$auth->isLoggedIn()) {
    header('Location: login.php');
    exit();
}

$database = new Database();
$conn = $database->getConnection();
$contactManager = new ContactManager($conn);

$message = '';
$error = '';

// Traitement des actions
if($_SERVER['REQUEST_METHOD'] == 'POST') {
    if(isset($_POST['mark_read'])) {
        $message_id = $_POST['message_id'];
        if($contactManager->markAsRead($message_id)) {
            $message = 'Message marqué comme lu.';
        } else {
            $error = 'Erreur lors du marquage du message.';
        }
    }
    
    if(isset($_POST['mark_replied'])) {
        $message_id = $_POST['message_id'];
        $admin_notes = trim($_POST['admin_notes']);
        if($contactManager->markAsReplied($message_id, $admin_notes)) {
            $message = 'Message marqué comme répondu.';
        } else {
            $error = 'Erreur lors du marquage du message.';
        }
    }
    
    if(isset($_POST['delete_message'])) {
        $message_id = $_POST['message_id'];
        if($contactManager->deleteMessage($message_id)) {
            $message = 'Message supprimé avec succès.';
        } else {
            $error = 'Erreur lors de la suppression du message.';
        }
    }
}

// Récupérer les paramètres de recherche et filtrage
$search = $_GET['search'] ?? '';
$filter = $_GET['filter'] ?? 'all';
$page = max(1, intval($_GET['page'] ?? 1));
$limit = 10;
$offset = ($page - 1) * $limit;

// Récupérer les messages
if (!empty($search)) {
    $messages = $contactManager->searchMessages($search, $filter);
    $total_messages = count($messages);
    $messages = array_slice($messages, $offset, $limit);
} else {
    $messages = $contactManager->getAllMessages($limit, $offset);
    $stats = $contactManager->getMessageStats();
    $total_messages = $stats['total'];
}

// Calculer la pagination
$total_pages = ceil($total_messages / $limit);

// Récupérer les statistiques
$stats = $contactManager->getMessageStats();
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Messages de Contact - Universe Security Admin</title>
    
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
        
        .message-card {
            border: 1px solid #e9ecef;
            border-radius: 8px;
            margin-bottom: 15px;
            transition: all 0.3s ease;
        }
        
        .message-card:hover {
            box-shadow: 0 4px 12px rgba(0,0,0,0.1);
        }
        
        .message-header {
            background: #f8f9fa;
            padding: 15px;
            border-bottom: 1px solid #e9ecef;
            border-radius: 8px 8px 0 0;
        }
        
        .message-body {
            padding: 15px;
        }
        
        .message-unread {
            border-left: 4px solid #dc3545;
        }
        
        .message-replied {
            border-left: 4px solid #28a745;
        }
        
        .badge-status {
            font-size: 0.75rem;
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
    $current_page = 'contact_messages';
    // Inclure la navbar commune
    include 'includes/admin_navbar.php'; 
    ?>
    
    <!-- Main Content -->
    <div class="main-content">
        <!-- Header -->
        <div class="d-flex justify-content-between align-items-center mb-4">
            <div>
                <h1 class="h3 mb-0">Messages de Contact</h1>
                <p class="text-muted">Gérez les messages reçus via le formulaire de contact</p>
            </div>
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
            <div class="col-lg-3 col-md-6">
                <div class="stats-card">
                    <div class="stats-number"><?php echo $stats['total']; ?></div>
                    <div class="text-muted">Total Messages</div>
                </div>
            </div>
            <div class="col-lg-3 col-md-6">
                <div class="stats-card">
                    <div class="stats-number text-danger"><?php echo $stats['unread']; ?></div>
                    <div class="text-muted">Non Lus</div>
                </div>
            </div>
            <div class="col-lg-3 col-md-6">
                <div class="stats-card">
                    <div class="stats-number text-success"><?php echo $stats['replied']; ?></div>
                    <div class="text-muted">Répondus</div>
                </div>
            </div>
            <div class="col-lg-3 col-md-6">
                <div class="stats-card">
                    <div class="stats-number text-info"><?php echo $stats['today']; ?></div>
                    <div class="text-muted">Aujourd'hui</div>
                </div>
            </div>
        </div>
        
        <!-- Filtres et recherche -->
        <div class="content-card">
            <form method="GET" class="row g-3">
                <div class="col-md-6">
                    <input type="text" class="form-control" name="search" placeholder="Rechercher par nom, email, sujet..." value="<?php echo htmlspecialchars($search); ?>">
                </div>
                <div class="col-md-3">
                    <select class="form-control" name="filter">
                        <option value="all" <?php echo $filter === 'all' ? 'selected' : ''; ?>>Tous les messages</option>
                        <option value="unread" <?php echo $filter === 'unread' ? 'selected' : ''; ?>>Non lus</option>
                        <option value="replied" <?php echo $filter === 'replied' ? 'selected' : ''; ?>>Répondus</option>
                        <option value="unreplied" <?php echo $filter === 'unreplied' ? 'selected' : ''; ?>>Non répondus</option>
                    </select>
                </div>
                <div class="col-md-3">
                    <button type="submit" class="btn btn-primary w-100">
                        <i class="fas fa-search me-2"></i>Rechercher
                    </button>
                </div>
            </form>
        </div>
        
        <!-- Liste des messages -->
        <div class="content-card">
            <?php if(empty($messages)): ?>
                <div class="text-center py-5">
                    <i class="fas fa-inbox fa-4x text-muted mb-3"></i>
                    <h5 class="text-muted">Aucun message trouvé</h5>
                    <p class="text-muted">Les messages de contact apparaîtront ici.</p>
                </div>
            <?php else: ?>
                <?php foreach($messages as $msg): ?>
                    <div class="message-card <?php echo !$msg['is_read'] ? 'message-unread' : ($msg['is_replied'] ? 'message-replied' : ''); ?>">
                        <div class="message-header">
                            <div class="row align-items-center">
                                <div class="col-md-8">
                                    <h6 class="mb-1">
                                        <?php echo htmlspecialchars($msg['name']); ?>
                                        <?php if(!$msg['is_read']): ?>
                                            <span class="badge bg-danger badge-status ms-2">Nouveau</span>
                                        <?php endif; ?>
                                        <?php if($msg['is_replied']): ?>
                                            <span class="badge bg-success badge-status ms-2">Répondu</span>
                                        <?php endif; ?>
                                    </h6>
                                    <small class="text-muted">
                                        <i class="fas fa-envelope me-1"></i><?php echo htmlspecialchars($msg['email']); ?>
                                        <?php if($msg['phone']): ?>
                                            | <i class="fas fa-phone me-1"></i><?php echo htmlspecialchars($msg['phone']); ?>
                                        <?php endif; ?>
                                        <?php if($msg['company']): ?>
                                            | <i class="fas fa-building me-1"></i><?php echo htmlspecialchars($msg['company']); ?>
                                        <?php endif; ?>
                                    </small>
                                </div>
                                <div class="col-md-4 text-end">
                                    <small class="text-muted">
                                        <i class="fas fa-clock me-1"></i><?php echo date('d/m/Y H:i', strtotime($msg['created_at'])); ?>
                                    </small>
                                </div>
                            </div>
                        </div>
                        <div class="message-body">
                            <h6><?php echo htmlspecialchars($msg['subject']); ?></h6>
                            <p class="mb-3"><?php echo nl2br(htmlspecialchars($msg['message'])); ?></p>
                            
                            <?php if($msg['admin_notes']): ?>
                                <div class="alert alert-info">
                                    <strong>Notes admin :</strong> <?php echo nl2br(htmlspecialchars($msg['admin_notes'])); ?>
                                </div>
                            <?php endif; ?>
                            
                            <div class="btn-group" role="group">
                                <?php if(!$msg['is_read']): ?>
                                    <form method="POST" class="d-inline">
                                        <input type="hidden" name="message_id" value="<?php echo $msg['id']; ?>">
                                        <button type="submit" name="mark_read" class="btn btn-sm btn-outline-primary">
                                            <i class="fas fa-eye me-1"></i>Marquer lu
                                        </button>
                                    </form>
                                <?php endif; ?>
                                
                                <?php if(!$msg['is_replied']): ?>
                                    <button type="button" class="btn btn-sm btn-outline-success" data-bs-toggle="modal" data-bs-target="#replyModal<?php echo $msg['id']; ?>">
                                        <i class="fas fa-reply me-1"></i>Marquer répondu
                                    </button>
                                <?php endif; ?>
                                
                                <a href="mailto:<?php echo htmlspecialchars($msg['email']); ?>?subject=Re: <?php echo urlencode($msg['subject']); ?>" class="btn btn-sm btn-outline-info">
                                    <i class="fas fa-envelope me-1"></i>Répondre
                                </a>
                                
                                <form method="POST" class="d-inline" onsubmit="return confirm('Êtes-vous sûr de vouloir supprimer ce message ?')">
                                    <input type="hidden" name="message_id" value="<?php echo $msg['id']; ?>">
                                    <button type="submit" name="delete_message" class="btn btn-sm btn-outline-danger">
                                        <i class="fas fa-trash me-1"></i>Supprimer
                                    </button>
                                </form>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Modal pour marquer comme répondu -->
                    <div class="modal fade" id="replyModal<?php echo $msg['id']; ?>" tabindex="-1">
                        <div class="modal-dialog">
                            <div class="modal-content">
                                <div class="modal-header">
                                    <h5 class="modal-title">Marquer comme répondu</h5>
                                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                </div>
                                <form method="POST">
                                    <div class="modal-body">
                                        <input type="hidden" name="message_id" value="<?php echo $msg['id']; ?>">
                                        <div class="mb-3">
                                            <label class="form-label">Notes administratives (optionnel)</label>
                                            <textarea class="form-control" name="admin_notes" rows="3" placeholder="Ajoutez des notes sur la réponse donnée..."></textarea>
                                        </div>
                                    </div>
                                    <div class="modal-footer">
                                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Annuler</button>
                                        <button type="submit" name="mark_replied" class="btn btn-success">Marquer répondu</button>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
                
                <!-- Pagination -->
                <?php if($total_pages > 1): ?>
                    <nav class="mt-4">
                        <ul class="pagination justify-content-center">
                            <?php for($i = 1; $i <= $total_pages; $i++): ?>
                                <li class="page-item <?php echo $i === $page ? 'active' : ''; ?>">
                                    <a class="page-link" href="?page=<?php echo $i; ?>&search=<?php echo urlencode($search); ?>&filter=<?php echo urlencode($filter); ?>">
                                        <?php echo $i; ?>
                                    </a>
                                </li>
                            <?php endfor; ?>
                        </ul>
                    </nav>
                <?php endif; ?>
            <?php endif; ?>
        </div>
    </div>
    
    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    
    <script>
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
