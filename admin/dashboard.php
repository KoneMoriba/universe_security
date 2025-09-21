<?php
require_once 'config/database.php';
require_once 'classes/Auth.php';
require_once 'classes/ContactManager.php';
require_once 'classes/ContentManager.php';

$auth = new Auth();

// Vérifier si l'utilisateur est connecté
if(!$auth->isLoggedIn()) {
    header('Location: login.php');
    exit();
}

$database = new Database();
$conn = $database->getConnection();

// Récupérer les statistiques de base
$quote_stats = ['total' => 0, 'new' => 0];
$service_stats = ['total' => 0];
$testimonial_stats = ['total' => 0];
$visit_stats = ['today' => 0];

try {
    // Statistiques des devis
    $query = "SELECT COUNT(*) as total FROM quote_requests";
    $stmt = $conn->prepare($query);
    $stmt->execute();
    $quote_stats['total'] = $stmt->fetch()['total'];
    
    $query = "SELECT COUNT(*) as new FROM quote_requests WHERE status = 'nouveau'";
    $stmt = $conn->prepare($query);
    $stmt->execute();
    $quote_stats['new'] = $stmt->fetch()['new'];
    
    // Statistiques des services
    $query = "SELECT COUNT(*) as total FROM services WHERE is_active = 1";
    $stmt = $conn->prepare($query);
    $stmt->execute();
    $service_stats['total'] = $stmt->fetch()['total'];
    
    // Statistiques des témoignages
    $query = "SELECT COUNT(*) as total FROM testimonials WHERE is_approved = 1";
    $stmt = $conn->prepare($query);
    $stmt->execute();
    $testimonial_stats['total'] = $stmt->fetch()['total'];
    
    // Statistiques des médias
    $media_stats = ['total' => 0, 'photos' => 0, 'videos' => 0];
    try {
        $query = "SELECT COUNT(*) as total FROM medias WHERE is_active = 1";
        $stmt = $conn->prepare($query);
        $stmt->execute();
        $media_stats['total'] = $stmt->fetch()['total'];
        
        $query = "SELECT COUNT(*) as photos FROM medias WHERE is_active = 1 AND media_type = 'photo'";
        $stmt = $conn->prepare($query);
        $stmt->execute();
        $media_stats['photos'] = $stmt->fetch()['photos'];
        
        $query = "SELECT COUNT(*) as videos FROM medias WHERE is_active = 1 AND media_type = 'video'";
        $stmt = $conn->prepare($query);
        $stmt->execute();
        $media_stats['videos'] = $stmt->fetch()['videos'];
    } catch(Exception $e) {
        // Table medias n'existe peut-être pas encore
        error_log("Erreur statistiques médias: " . $e->getMessage());
    }
    
    // Statistiques des offres
    $offer_stats = ['total' => 0, 'active' => 0];
    try {
        $query = "SELECT COUNT(*) as total FROM offers";
        $stmt = $conn->prepare($query);
        $stmt->execute();
        $offer_stats['total'] = $stmt->fetch()['total'];
        
        $query = "SELECT COUNT(*) as active FROM offers WHERE is_active = 1";
        $stmt = $conn->prepare($query);
        $stmt->execute();
        $offer_stats['active'] = $stmt->fetch()['active'];
    } catch(Exception $e) {
        error_log("Erreur statistiques offres: " . $e->getMessage());
    }
    
    // Statistiques des messages de contact
    $contactManager = new ContactManager($conn);
    $contact_stats = $contactManager->getMessageStats();
    
    // Statistiques des vendors
    $vendorManager = new VendorManager($conn);
    $vendor_stats = $vendorManager->getVendorStats();
    
    // Statistiques de l'équipe
    $team_stats = ['total' => 0, 'active' => 0];
    try {
        $query = "SELECT COUNT(*) as total FROM team_members";
        $stmt = $conn->prepare($query);
        $stmt->execute();
        $team_stats['total'] = $stmt->fetch()['total'];
        
        $query = "SELECT COUNT(*) as active FROM team_members WHERE is_active = 1";
        $stmt = $conn->prepare($query);
        $stmt->execute();
        $team_stats['active'] = $stmt->fetch()['active'];
    } catch(Exception $e) {
        error_log("Erreur statistiques équipe: " . $e->getMessage());
    }
    
    // Statistiques du blog
    $blog_stats = ['total' => 0, 'published' => 0];
    try {
        $query = "SELECT COUNT(*) as total FROM blog_articles";
        $stmt = $conn->prepare($query);
        $stmt->execute();
        $blog_stats['total'] = $stmt->fetch()['total'];
        
        $query = "SELECT COUNT(*) as published FROM blog_articles WHERE is_published = 1";
        $stmt = $conn->prepare($query);
        $stmt->execute();
        $blog_stats['published'] = $stmt->fetch()['published'];
    } catch(Exception $e) {
        error_log("Erreur statistiques blog: " . $e->getMessage());
    }
    
    // Statistiques des visites
    $query = "SELECT COUNT(*) as today FROM site_visits WHERE visit_date = CURDATE()";
    $stmt = $conn->prepare($query);
    $stmt->execute();
    $visit_stats['today'] = $stmt->fetch()['today'];
    
    // Dernières demandes de devis
    $query = "SELECT * FROM quote_requests ORDER BY created_at DESC LIMIT 5";
    $stmt = $conn->prepare($query);
    $stmt->execute();
    $recent_quotes = $stmt->fetchAll();
    
    // Dernières activités
    $query = "SELECT * FROM admin_logs WHERE admin_id = :admin_id ORDER BY created_at DESC LIMIT 5";
    $stmt = $conn->prepare($query);
    $stmt->bindParam(':admin_id', $_SESSION['admin_id']);
    $stmt->execute();
    $recent_activities = $stmt->fetchAll();
    
} catch(Exception $e) {
    // En cas d'erreur, utiliser des valeurs par défaut
    $recent_quotes = [];
    $recent_activities = [];
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tableau de Bord - Universe Security Admin</title>
    
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
        }
        
        .logo {
            width: 50px;
            height: 50px;
            border-radius: 50%;
            object-fit: cover;
            border: 2px solid rgba(255,255,255,0.3);
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
            min-height: 100vh;
        }
        
        .stats-card {
            background: white;
            border-radius: 10px;
            padding: 20px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.05);
            border: 1px solid #e9ecef;
            transition: all 0.3s ease;
            height: 100%;
        }
        
        .stats-card:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 15px rgba(0,0,0,0.1);
        }
        
        .content-card {
            background: white;
            border-radius: 10px;
            padding: 20px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.05);
            border: 1px solid #e9ecef;
            margin-bottom: 20px;
        }
        
        .mobile-toggle {
            display: none;
            position: fixed;
            top: 20px;
            left: 20px;
            z-index: 1001;
            background: var(--primary-color);
            color: white;
            border: none;
            padding: 10px;
            border-radius: 5px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.2);
        }
        
        /* Responsive Design */
        @media (max-width: 768px) {
            .sidebar {
                transform: translateX(-100%);
            }
            
            .sidebar.show {
                transform: translateX(0);
            }
            
            .main-content {
                margin-left: 0;
                padding: 15px;
            }
            
            .mobile-toggle {
                display: block !important;
            }
        }
    </style>
</head>
<body>
    <?php 
    // Définir la page actuelle pour la navbar
    $current_page = 'dashboard';
    // Inclure la navbar commune
    include 'includes/admin_navbar.php'; 
    ?>
    
    <!-- Main Content -->
    <div class="main-content">
        <!-- Header -->
        <div class="d-flex justify-content-between align-items-center mb-4">
            <div>
                <h1 class="h3 mb-0">Tableau de Bord</h1>
                <p class="text-muted">Bienvenue, <?php echo htmlspecialchars($_SESSION['admin_name']); ?></p>
            </div>
            <div class="text-end">
                <small class="text-muted"><?php echo date('d/m/Y H:i'); ?></small>
            </div>
        </div>
        
        <!-- Statistiques principales -->
        <div class="row mb-4">
            <div class="col-lg-3 col-md-6 mb-3">
                <div class="stats-card">
                    <div class="d-flex align-items-center">
                        <div class="me-3">
                            <i class="fas fa-file-invoice text-primary fa-2x"></i>
                        </div>
                        <div>
                            <h4 class="mb-0"><?php echo $quote_stats['total']; ?></h4>
                            <small class="text-muted">Demandes de Devis</small>
                            <?php if($quote_stats['new'] > 0): ?>
                                <br><span class="badge bg-danger"><?php echo $quote_stats['new']; ?> nouvelles</span>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="col-lg-3 col-md-6 mb-3">
                <div class="stats-card">
                    <div class="d-flex align-items-center">
                        <div class="me-3">
                            <i class="fas fa-cogs text-success fa-2x"></i>
                        </div>
                        <div>
                            <h4 class="mb-0"><?php echo $service_stats['total']; ?></h4>
                            <small class="text-muted">Services Actifs</small>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="col-lg-3 col-md-6 mb-3">
                <div class="stats-card">
                    <div class="d-flex align-items-center">
                        <div class="me-3">
                            <i class="fas fa-comments text-info fa-2x"></i>
                        </div>
                        <div>
                            <h4 class="mb-0"><?php echo $testimonial_stats['total']; ?></h4>
                            <small class="text-muted">Témoignages</small>
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
                            <h4 class="mb-0"><?php echo $visit_stats['today']; ?></h4>
                            <small class="text-muted">Visites Aujourd'hui</small>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Statistiques des contenus -->
        <div class="row mb-4">
            <div class="col-lg-2 col-md-4 mb-3">
                <div class="stats-card">
                    <div class="d-flex align-items-center">
                        <div class="me-3">
                            <i class="fas fa-box text-primary fa-2x"></i>
                        </div>
                        <div>
                            <h4 class="mb-0"><?php echo $offer_stats['active']; ?></h4>
                            <small class="text-muted">Offres Actives</small>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="col-lg-2 col-md-4 mb-3">
                <div class="stats-card">
                    <div class="d-flex align-items-center">
                        <div class="me-3">
                            <i class="fas fa-users text-success fa-2x"></i>
                        </div>
                        <div>
                            <h4 class="mb-0"><?php echo $team_stats['active']; ?></h4>
                            <small class="text-muted">Membres Équipe</small>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="col-lg-2 col-md-4 mb-3">
                <div class="stats-card">
                    <div class="d-flex align-items-center">
                        <div class="me-3">
                            <i class="fas fa-newspaper text-info fa-2x"></i>
                        </div>
                        <div>
                            <h4 class="mb-0"><?php echo $blog_stats['published']; ?></h4>
                            <small class="text-muted">Articles Publiés</small>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="col-lg-2 col-md-4 mb-3">
                <div class="stats-card">
                    <div class="d-flex align-items-center">
                        <div class="me-3">
                            <i class="fas fa-photo-video text-warning fa-2x"></i>
                        </div>
                        <div>
                            <h4 class="mb-0"><?php echo $media_stats['total']; ?></h4>
                            <small class="text-muted">Médias</small>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="col-lg-2 col-md-4 mb-3">
                <div class="stats-card">
                    <div class="d-flex align-items-center">
                        <div class="me-3">
                            <i class="fas fa-images text-success fa-2x"></i>
                        </div>
                        <div>
                            <h4 class="mb-0"><?php echo $media_stats['photos']; ?></h4>
                            <small class="text-muted">Photos</small>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="col-lg-2 col-md-4 mb-3">
                <div class="stats-card">
                    <div class="d-flex align-items-center">
                        <div class="me-3">
                            <i class="fas fa-video text-danger fa-2x"></i>
                        </div>
                        <div>
                            <h4 class="mb-0"><?php echo $media_stats['videos']; ?></h4>
                            <small class="text-muted">Vidéos</small>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Nouvelles statistiques -->
        <div class="row mb-4">
            <div class="col-lg-3 col-md-6 mb-3">
                <div class="stats-card">
                    <div class="d-flex align-items-center">
                        <div class="me-3">
                            <i class="fas fa-envelope text-primary fa-2x"></i>
                        </div>
                        <div>
                            <h4 class="mb-0"><?php echo $contact_stats['total']; ?></h4>
                            <small class="text-muted">Messages Contact</small>
                            <?php if($contact_stats['unread'] > 0): ?>
                                <div><span class="badge bg-danger"><?php echo $contact_stats['unread']; ?> non lus</span></div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="col-lg-3 col-md-6 mb-3">
                <div class="stats-card">
                    <div class="d-flex align-items-center">
                        <div class="me-3">
                            <i class="fas fa-handshake text-success fa-2x"></i>
                        </div>
                        <div>
                            <h4 class="mb-0"><?php echo $vendor_stats['active']; ?></h4>
                            <small class="text-muted">Partenaires Actifs</small>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="col-lg-3 col-md-6 mb-3">
                <div class="stats-card">
                    <div class="d-flex align-items-center">
                        <div class="me-3">
                            <i class="fas fa-info-circle text-info fa-2x"></i>
                        </div>
                        <div>
                            <h4 class="mb-0"><?php 
                                try {
                                    $contentManager = new ContentManager($conn);
                                    $about_content = $contentManager->getAboutContent();
                                    echo count($about_content);
                                } catch(Exception $e) {
                                    echo '0';
                                }
                            ?></h4>
                            <small class="text-muted">Contenus À Propos</small>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="col-lg-3 col-md-6 mb-3">
                <div class="stats-card">
                    <div class="d-flex align-items-center">
                        <div class="me-3">
                            <i class="fas fa-reply text-warning fa-2x"></i>
                        </div>
                        <div>
                            <h4 class="mb-0"><?php echo $contact_stats['replied']; ?></h4>
                            <small class="text-muted">Messages Répondus</small>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Actions rapides -->
        <div class="row mb-4">
            <div class="col-12">
                <div class="content-card">
                    <h5 class="mb-3"><i class="fas fa-bolt me-2"></i>Actions Rapides</h5>
                    <div class="row">
                        <div class="col-lg-3 col-md-6 mb-3">
                            <a href="quotes.php" class="btn btn-outline-primary w-100 py-3 text-decoration-none">
                                <i class="fas fa-file-invoice fa-2x mb-2 d-block"></i>
                                Gérer les Devis
                            </a>
                        </div>
                        <div class="col-lg-3 col-md-6 mb-3">
                            <a href="services.php" class="btn btn-outline-success w-100 py-3 text-decoration-none">
                                <i class="fas fa-cogs fa-2x mb-2 d-block"></i>
                                Gérer les Services
                            </a>
                        </div>
                        <div class="col-lg-3 col-md-6 mb-3">
                            <a href="offers.php" class="btn btn-outline-primary w-100 py-3 text-decoration-none">
                                <i class="fas fa-box fa-2x mb-2 d-block"></i>
                                Gérer les Offres
                            </a>
                        </div>
                        <div class="col-lg-3 col-md-6 mb-3">
                            <a href="team.php" class="btn btn-outline-success w-100 py-3 text-decoration-none">
                                <i class="fas fa-users fa-2x mb-2 d-block"></i>
                                Gérer l'Équipe
                            </a>
                        </div>
                        <div class="col-lg-3 col-md-6 mb-3">
                            <a href="blog.php" class="btn btn-outline-info w-100 py-3 text-decoration-none">
                                <i class="fas fa-newspaper fa-2x mb-2 d-block"></i>
                                Gérer le Blog
                            </a>
                        </div>
                        <div class="col-lg-3 col-md-6 mb-3">
                            <a href="testimonials.php" class="btn btn-outline-warning w-100 py-3 text-decoration-none">
                                <i class="fas fa-comments fa-2x mb-2 d-block"></i>
                                Modérer Témoignages
                            </a>
                        </div>
                        <div class="col-lg-3 col-md-6 mb-3">
                            <a href="medias.php" class="btn btn-outline-secondary w-100 py-3 text-decoration-none">
                                <i class="fas fa-photo-video fa-2x mb-2 d-block"></i>
                                Gérer Médias
                            </a>
                        </div>
                        <div class="col-lg-3 col-md-6 mb-3">
                            <a href="contact_messages.php" class="btn btn-outline-primary w-100 py-3 text-decoration-none">
                                <i class="fas fa-envelope fa-2x mb-2 d-block"></i>
                                Messages Contact
                                <?php if($contact_stats['unread'] > 0): ?>
                                    <span class="badge bg-danger"><?php echo $contact_stats['unread']; ?></span>
                                <?php endif; ?>
                            </a>
                        </div>
                        <div class="col-lg-3 col-md-6 mb-3">
                            <a href="about_content.php" class="btn btn-outline-info w-100 py-3 text-decoration-none">
                                <i class="fas fa-info-circle fa-2x mb-2 d-block"></i>
                                Contenu À Propos
                            </a>
                        </div>
                        <div class="col-lg-3 col-md-6 mb-3">
                            <a href="vendors.php" class="btn btn-outline-success w-100 py-3 text-decoration-none">
                                <i class="fas fa-handshake fa-2x mb-2 d-block"></i>
                                Gérer Partenaires
                            </a>
                        </div>
                        <div class="col-lg-3 col-md-6 mb-3">
                            <a href="analytics.php" class="btn btn-outline-danger w-100 py-3 text-decoration-none">
                                <i class="fas fa-chart-bar fa-2x mb-2 d-block"></i>
                                Voir Statistiques
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Aperçu rapide -->
        <div class="row">
            <div class="col-lg-6">
                <div class="content-card">
                    <h5 class="mb-3"><i class="fas fa-clock me-2"></i>Dernières Demandes de Devis</h5>
                    <?php if(empty($recent_quotes)): ?>
                        <div class="text-center py-4">
                            <i class="fas fa-inbox fa-3x text-muted mb-3"></i>
                            <p class="text-muted">Aucune demande récente</p>
                        </div>
                    <?php else: ?>
                        <?php foreach($recent_quotes as $quote): ?>
                            <div class="d-flex align-items-center mb-3 p-3 border rounded">
                                <div class="flex-grow-1">
                                    <h6 class="mb-1"><?php echo htmlspecialchars($quote['name']); ?></h6>
                                    <small class="text-muted"><?php echo htmlspecialchars($quote['service']); ?></small>
                                </div>
                                <div class="text-end">
                                    <span class="badge bg-<?php echo $quote['status'] === 'nouveau' ? 'primary' : 'secondary'; ?>">
                                        <?php echo ucfirst($quote['status']); ?>
                                    </span>
                                    <br><small class="text-muted"><?php echo date('d/m', strtotime($quote['created_at'])); ?></small>
                                </div>
                            </div>
                        <?php endforeach; ?>
                        <div class="text-center">
                            <a href="quotes.php" class="btn btn-primary btn-sm">Voir Tous les Devis</a>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
            
            <div class="col-lg-6">
                <div class="content-card">
                    <h5 class="mb-3"><i class="fas fa-history me-2"></i>Activités Récentes</h5>
                    <?php if(empty($recent_activities)): ?>
                        <div class="text-center py-4">
                            <i class="fas fa-clock fa-3x text-muted mb-3"></i>
                            <p class="text-muted">Aucune activité récente</p>
                        </div>
                    <?php else: ?>
                        <?php foreach($recent_activities as $activity): ?>
                            <div class="d-flex align-items-center mb-3">
                                <div class="me-3">
                                    <i class="fas fa-circle text-primary" style="font-size: 8px;"></i>
                                </div>
                                <div class="flex-grow-1">
                                    <p class="mb-0 small"><?php echo htmlspecialchars($activity['action']); ?></p>
                                    <small class="text-muted"><?php echo date('d/m H:i', strtotime($activity['created_at'])); ?></small>
                                </div>
                            </div>
                        <?php endforeach; ?>
                        <div class="text-center">
                            <a href="profile.php" class="btn btn-outline-primary btn-sm">Voir Plus</a>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    
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
</body>
</html>
