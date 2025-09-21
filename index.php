<?php
// Inclure le système de tracking
include_once 'includes/tracking.php';

// Inclure le gestionnaire de devis
include_once 'includes/quote_handler.php';

// Inclure la connexion à la base de données pour récupérer les services et témoignages
require_once 'admin/config/database.php';
require_once 'admin/classes/ContentManager.php';

// Récupérer les données dynamiques depuis la base de données
$services = [];
$testimonials = [];
$photos = [];
$videos = [];
$offers = [];
$team_members = [];
$blog_articles = [];

try {
    $database = new Database();
    $conn = $database->getConnection();
    
    // Inclure les gestionnaires
    require_once 'admin/classes/OfferManager.php';
    require_once 'admin/classes/TeamManager.php';
    require_once 'admin/classes/BlogManager.php';
    
    // Initialiser les gestionnaires
    $offerManager = new OfferManager($conn);
    $teamManager = new TeamManager($conn);
    $blogManager = new BlogManager($conn);
    
    // Récupérer les services actifs, ordonnés par display_order
    $query = "SELECT * FROM services WHERE is_active = 1 ORDER BY display_order ASC, created_at DESC LIMIT 6";
    $stmt = $conn->prepare($query);
    $stmt->execute();
    $services = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Récupérer les témoignages approuvés
    $query = "SELECT * FROM testimonials WHERE is_approved = 1 ORDER BY is_featured DESC, created_at DESC LIMIT 10";
    $stmt = $conn->prepare($query);
    $stmt->execute();
    $testimonials = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Récupérer les photos actives
    $query = "SELECT * FROM medias WHERE is_active = 1 AND media_type = 'photo' ORDER BY display_order ASC, created_at DESC LIMIT 8";
    $stmt = $conn->prepare($query);
    $stmt->execute();
    $photos = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Récupérer les vidéos actives
    $query = "SELECT * FROM medias WHERE is_active = 1 AND media_type = 'video' ORDER BY display_order ASC, created_at DESC LIMIT 4";
    $stmt = $conn->prepare($query);
    $stmt->execute();
    $videos = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Récupérer les offres actives
    $offers = $offerManager->getActiveOffers(3);
    
    // Récupérer les membres de l'équipe pour la page d'accueil
    $team_members = $teamManager->getMembersForHomepage(3);
    
    // Récupérer les articles de blog récents
    $blog_articles = $blogManager->getPublishedArticles(3);
    
    // Récupérer les vendors/partenaires actifs
    $vendorManager = new VendorManager($conn);
    $vendors = $vendorManager->getActiveVendors();
    
} catch(Exception $e) {
    // En cas d'erreur, utiliser des données par défaut (services statiques actuels)
    error_log("Erreur lors de la récupération des données: " . $e->getMessage());
}
?>
<!DOCTYPE html>
<html lang="fr">

<head>
    <meta charset="utf-8">
    <title>Universe Security - Solutions de Cybersécurité et Sécurité Informatique en Côte d'Ivoire</title>
    <meta content="width=device-width, initial-scale=1.0" name="viewport">
    <meta content="cybersécurité, sécurité informatique, protection données, développement web, solutions IT, Abidjan, Côte d'Ivoire, Universe Security, audit sécurité, consultation IT" name="keywords">
    <meta content="Universe Security - Leader en cybersécurité et solutions informatiques en Côte d'Ivoire. Services de protection des données, développement web, audit sécurité et consultation IT à Abidjan." name="description">
    
    <!-- Open Graph / Facebook -->
    <meta property="og:type" content="website">
    <meta property="og:url" content="https://universe-security.ci/">
    <meta property="og:title" content="Universe Security - Solutions de Cybersécurité en Côte d'Ivoire">
    <meta property="og:description" content="Leader en cybersécurité et solutions informatiques en Côte d'Ivoire. Protection des données, développement web, audit sécurité.">
    <meta property="og:image" content="https://universe-security.ci/img/logo universe security.jpg">
    
    <!-- Twitter -->
    <meta property="twitter:card" content="summary_large_image">
    <meta property="twitter:url" content="https://universe-security.ci/">
    <meta property="twitter:title" content="Universe Security - Solutions de Cybersécurité en Côte d'Ivoire">
    <meta property="twitter:description" content="Leader en cybersécurité et solutions informatiques en Côte d'Ivoire. Protection des données, développement web, audit sécurité.">
    <meta property="twitter:image" content="https://universe-security.ci/img/logo universe security.jpg">
    
    <!-- Données structurées JSON-LD -->
    <script type="application/ld+json">
    {
      "@context": "https://schema.org",
      "@type": "Organization",
      "name": "Universe Security",
      "url": "https://universe-security.ci",
      "logo": "https://universe-security.ci/img/logo universe security.jpg",
      "description": "Entreprise spécialisée en cybersécurité et solutions informatiques en Côte d'Ivoire",
      "address": {
        "@type": "PostalAddress",
        "streetAddress": "Cocody Angré 8ième tranche",
        "addressLocality": "Abidjan",
        "addressCountry": "CI"
      },
      "contactPoint": {
        "@type": "ContactPoint",
        "telephone": "+225-0101012501",
        "contactType": "customer service",
        "email": "info@universe-security.ci"
      },
      "sameAs": [
        "https://www.facebook.com/universesecurity",
        "https://www.linkedin.com/company/universe-security",
        "https://twitter.com/universesecurity"
      ]
    }
    </script>

    <!-- Favicon -->
    <link href="img/logo universe security.jpg" rel="icon">

    <!-- Google Web Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Nunito:wght@400;600;700;800&family=Rubik:wght@400;500;600;700&display=swap" rel="stylesheet">

    <!-- Icon Font Stylesheet -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.10.0/css/all.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.4.1/font/bootstrap-icons.css" rel="stylesheet">

    <!-- Libraries Stylesheet -->
    <link href="lib/owlcarousel/assets/owl.carousel.min.css" rel="stylesheet">
    <link href="lib/animate/animate.min.css" rel="stylesheet">

    <!-- Customized Bootstrap Stylesheet -->
    <link href="css/bootstrap.min.css" rel="stylesheet">

    <!-- Template Stylesheet -->
    <link href="css/style.css" rel="stylesheet">
</head>

<body>
    <!-- Spinner Start -->
    <div id="spinner" class="show bg-white position-fixed translate-middle w-100 vh-100 top-50 start-50 d-flex align-items-center justify-content-center">
        <div class="spinner"></div>
    </div>
    <!-- Spinner End -->


    <!-- Topbar Start -->
    <div class="container-fluid bg-dark px-5 d-none d-lg-block">
        <div class="row gx-0">
            <div class="col-lg-8 text-center text-lg-start mb-2 mb-lg-0">
                <div class="d-inline-flex align-items-center" style="height: 45px;">
                    <small class="me-3 text-light"><i class="fa fa-map-marker-alt me-2"></i>Cocody Angré 8ièm, Abidjan, Côte d'Ivoire</small>
                    <small class="me-3 text-light"><i class="fa fa-phone-alt me-2"></i>+225 0101012501</small>
                </div>
            </div>
            <div class="col-lg-4 text-center text-lg-end">
                <div class="d-inline-flex align-items-center" style="height: 45px;">
                    <a class="btn btn-sm btn-outline-light btn-sm-square rounded-circle me-2" href="#"><i class="fab fa-twitter fw-normal"></i></a>
                    <a class="btn btn-sm btn-outline-light btn-sm-square rounded-circle me-2" href="#"><i class="fab fa-facebook-f fw-normal"></i></a>
                    <a class="btn btn-sm btn-outline-light btn-sm-square rounded-circle me-2" href="#"><i class="fab fa-linkedin-in fw-normal"></i></a>
                    <a class="btn btn-sm btn-outline-light btn-sm-square rounded-circle me-2" href="#"><i class="fab fa-instagram fw-normal"></i></a>
                    <a class="btn btn-sm btn-outline-light btn-sm-square rounded-circle" href="#"><i class="fab fa-youtube fw-normal"></i></a>
                </div>
            </div>
        </div>
    </div>
    <!-- Topbar End -->


    <!-- Navbar & Carousel Start -->
    <div class="container-fluid position-relative p-0">
        <nav class="navbar navbar-expand-lg navbar-dark px-5 py-3 py-lg-0">
            <a href="index.php" class="navbar-brand p-0">
                <img src="img/logo universe security.png" alt="Universe Security - Logo entreprise cybersécurité Côte d'Ivoire" style="height: 100px; width: 150px;">
            </a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarCollapse">
                <span class="fa fa-bars"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarCollapse">
                <div class="navbar-nav ms-auto py-0">
                    <a href="#home" class="nav-item nav-link">Accueil</a>
                    <a href="#about" class="nav-item nav-link">À propos</a>
                    <a href="services.php" class="nav-item nav-link">Services</a>
                    <div class="nav-item dropdown">
                        <a href="#" class="nav-link dropdown-toggle" data-bs-toggle="dropdown">Galerie</a>
                        <div class="dropdown-menu fade-up m-0">
                            <a href="galerie.php#photos-tab" class="dropdown-item"><i class="fas fa-images me-2"></i>Photos</a>
                            <a href="galerie.php#videos-tab" class="dropdown-item"><i class="fas fa-video me-2"></i>Vidéos</a>
                        </div>
                    </div>
                    <a href="tarification.php" class="nav-item nav-link">Tarification</a>
                    <a href="equipe.php" class="nav-item nav-link">Équipe</a>
                    <a href="blog.php" class="nav-item nav-link">Blog</a>
                    <a href="#contact" class="nav-item nav-link">Contact</a>
                </div>
            </div>
        </nav>

        <!-- Carousel Start -->
        <div id="home" class="carousel slide carousel-fade" data-bs-ride="carousel">
            <!-- Video Background -->
            <div class="video-background">
                <video autoplay muted loop id="background-video">
                    <source src="img/carousel.mp4" type="video/mp4">
                </video>
            </div>
            
            <div class="carousel-inner">
                <div class="carousel-item active">
                    <div class="carousel-caption d-flex flex-column align-items-center justify-content-center">
                        <div class="p-3" style="max-width: 900px;">
                            <h5 class="text-white text-uppercase mb-3 animated slideInDown">Universe Security</h5>
                            <h1 class="display-1 text-white mb-md-4 animated zoomIn">Solutions IT Créatives & Innovantes</h1>
                            <a href="#quote" class="btn btn-primary py-md-3 px-md-5 me-3 animated slideInLeft">Devis Gratuit</a>
                            <a href="#contact" class="btn btn-outline-light py-md-3 px-md-5 animated slideInRight">Nous Contacter</a>
                        </div>
                    </div>
                </div>
                <div class="carousel-item">
                    <div class="carousel-caption d-flex flex-column align-items-center justify-content-center">
                        <div class="p-3" style="max-width: 900px;">
                            <h5 class="text-white text-uppercase mb-3 animated slideInDown">Universe Security</h5>
                            <h1 class="display-1 text-white mb-md-4 animated zoomIn">Materiels technologiques de pointe</h1>
                            <a href="#quote" class="btn btn-primary py-md-3 px-md-5 me-3 animated slideInLeft">Devis Gratuit</a>
                            <a href="#contact" class="btn btn-outline-light py-md-3 px-md-5 animated slideInRight">Nous Contacter</a>
                        </div>
                    </div>
                </div>
            </div>
            <button class="carousel-control-prev" type="button" data-bs-target="#home"
                data-bs-slide="prev">
                <span class="carousel-control-prev-icon" aria-hidden="true"></span>
                <span class="visually-hidden">Précédent</span>
            </button>
            <button class="carousel-control-next" type="button" data-bs-target="#home"
                data-bs-slide="next">
                <span class="carousel-control-next-icon" aria-hidden="true"></span>
                <span class="visually-hidden">Suivant</span>
            </button>
        </div>
    </div>
    <!-- Navbar & Carousel End -->


    <!-- Full Screen Search Start -->
    <div class="modal fade" id="searchModal" tabindex="-1">
        <div class="modal-dialog modal-fullscreen">
            <div class="modal-content" style="background: rgba(9, 30, 62, .7);">
                <div class="modal-header border-0">
                    <button type="button" class="btn bg-white btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body d-flex align-items-center justify-content-center">
                    <div class="input-group" style="max-width: 600px;">
                        <input type="text" class="form-control bg-transparent border-primary p-3" placeholder="Type search keyword">
                        <button class="btn btn-primary px-4"><i class="bi bi-search"></i></button>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <!-- Full Screen Search End -->


    <!-- Facts Start -->
    <div class="container-fluid facts py-5 pt-lg-0">
        <div class="container py-5 pt-lg-0">
            <div class="row gx-0">
                <div class="col-lg-4 wow zoomIn" data-wow-delay="0.1s">
                    <div class="bg-primary shadow d-flex align-items-center justify-content-center p-4" style="height: 150px;">
                        <div class="bg-white d-flex align-items-center justify-content-center rounded mb-2" style="width: 60px; height: 60px;">
                            <i class="fa fa-users text-primary"></i>
                        </div>
                        <div class="ps-4">
                            <h5 class="text-white mb-0">Clients Satisfaits</h5>
                            <h1 class="text-white mb-0" data-toggle="counter-up">10</h1>
                        </div>
                    </div>
                </div>
                <div class="col-lg-4 wow zoomIn" data-wow-delay="0.3s">
                    <div class="bg-light shadow d-flex align-items-center justify-content-center p-4" style="height: 150px;">
                        <div class="bg-primary d-flex align-items-center justify-content-center rounded mb-2" style="width: 60px; height: 60px;">
                            <i class="fa fa-check text-white"></i>
                        </div>
                        <div class="ps-4">
                            <h5 class="text-primary mb-0">Projets Réalisés</h5>
                            <h1 class="mb-0" data-toggle="counter-up">50</h1>
                        </div>
                    </div>
                </div>
                <div class="col-lg-4 wow zoomIn" data-wow-delay="0.6s">
                    <div class="bg-primary shadow d-flex align-items-center justify-content-center p-4" style="height: 150px;">
                        <div class="bg-white d-flex align-items-center justify-content-center rounded mb-2" style="width: 60px; height: 60px;">
                            <i class="fa fa-award text-primary"></i>
                        </div>
                        <div class="ps-4">
                            <h5 class="text-white mb-0">Années d'expériences</h5>
                            <h1 class="text-white mb-0" data-toggle="counter-up">2</h1>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <!-- Facts Start -->


    <!-- About Start -->
    <div id="about" class="container-fluid py-5 wow fadeInUp neural-section" data-wow-delay="0.1s">
        <div class="neural-bg">
            <div class="neuron1"></div>
            <div class="neuron2"></div>
            <div class="neuron3"></div>
            <div class="neuron4"></div>
            <div class="connection1"></div>
            <div class="connection2"></div>
        </div>
        <div class="container py-5">
            <div class="row g-5">
                <div class="col-lg-7">
                    <div class="section-title position-relative pb-3 mb-5">
                        <h5 class="fw-bold text-secondary text-uppercase">À Propos de Nous</h5>
                        <h1 class="mb-0">La Meilleure Solution IT avec +15 Ans d'Expérience</h1>
                    </div>
                    <p class="mb-4">Les services d’UNIVERSE SECURE offrent aux
                        entreprises un avantage sur la concurrence grâce à une
                        variété d’avantage. Opter pour des services informatiques
                        externalisés améliore l’efficacité de l’entreprise et renforce
                        la confiance avec les clients. Nos services peuvent être
                        adaptés pour répondre à des besoins spécifiques et
                        correspondre à vos objectifs spécifiques.
                       </p>
                    <div class="row g-0 mb-3">
                        <div class="col-sm-6 wow zoomIn" data-wow-delay="0.2s">
                            <h5 class="mb-3"><i class="fa fa-check text-primary me-3"></i>Primé et Reconnu</h5>
                            <h5 class="mb-3"><i class="fa fa-check text-primary me-3"></i>Équipe Professionnelle</h5>
                        </div>
                        <div class="col-sm-6 wow zoomIn" data-wow-delay="0.4s">
                            <h5 class="mb-3"><i class="fa fa-check text-primary me-3"></i>Support 24h/24 7j/7</h5>
                            <h5 class="mb-3"><i class="fa fa-check text-primary me-3"></i>Prix Équitables</h5>
                        </div>
                    </div>
                    <div class="d-flex align-items-center mb-4 wow fadeIn" data-wow-delay="0.6s">
                        <div class="bg-primary d-flex align-items-center justify-content-center rounded" style="width: 60px; height: 60px;">
                            <i class="fa fa-phone-alt text-white"></i>
                        </div>
                        <div class="ps-4">
                            <h5 class="mb-2">Appelez pour toute question</h5>
                            <h4 class="text-primary mb-0">+225 0101012501</h4>
                        </div>
                    </div>
                    <a href="#quote" class="btn btn-primary py-3 px-5 mt-3 wow zoomIn" data-wow-delay="0.9s">Demander un Devis</a>
                </div>
                <div class="col-lg-5" style="min-height: 500px;">
                    <div class="position-relative h-100">
                        <img class="position-absolute w-100 h-100 rounded wow zoomIn" data-wow-delay="0.9s" src="img/about.jpg" style="object-fit: cover;">
                    </div>
                </div>
            </div>
        </div>
    </div>
    <!-- About End -->


    <!-- Features Start -->
    <div id="features" class="container-fluid py-5 wow fadeInUp" data-wow-delay="0.1s">
        <div class="container py-5">
            <div class="section-title text-center position-relative pb-3 mb-5 mx-auto" style="max-width: 600px;">
                <h5 class="fw-bold text-secondary text-uppercase">Pourquoi Nous Choisir</h5>
                <h1 class="mb-0">Nous Sommes Là pour Faire Croître Votre Entreprise de Manière Exponentielle</h1>
            </div>
            <div class="row g-5">
                <div class="col-lg-4">
                    <div class="row g-5">
                        <div class="col-12 wow zoomIn" data-wow-delay="0.2s">
                            <div class="bg-primary rounded d-flex align-items-center justify-content-center mb-3" style="width: 60px; height: 60px;">
                                <i class="fa fa-cubes text-white"></i>
                            </div>
                            <h4>Acteur de confiance</h4>
                            <p class="mb-0">Nous offrons les meilleures solutions technologiques avec une expertise reconnue dans le secteur</p>
                        </div>
                        <div class="col-12 wow zoomIn" data-wow-delay="0.6s">
                            <div class="bg-primary rounded d-flex align-items-center justify-content-center mb-3" style="width: 60px; height: 60px;">
                                <i class="fa fa-award text-white"></i>
                            </div>
                            <h4>Primé et Reconnu</h4>
                            <p class="mb-0">Notre excellence est reconnue par de nombreux prix et certifications dans le domaine IT</p>
                        </div>
                    </div>
                </div>
                <div class="col-lg-4  wow zoomIn" data-wow-delay="0.9s" style="min-height: 350px;">
                    <div class="position-relative h-100">
                        <img class="position-absolute w-100 h-100 rounded wow zoomIn" data-wow-delay="0.1s" src="img/feature.jpg" style="object-fit: cover;">
                    </div>
                </div>
                <div class="col-lg-4">
                    <div class="row g-5">
                        <div class="col-12 wow zoomIn" data-wow-delay="0.4s">
                            <div class="bg-primary rounded d-flex align-items-center justify-content-center mb-3" style="width: 60px; height: 60px;">
                                <i class="fa fa-users-cog text-white"></i>
                            </div>
                            <h4>Équipe Professionnelle</h4>
                            <p class="mb-0">Notre équipe d'experts qualifiés vous accompagne avec professionnalisme et expertise</p>
                        </div>
                        <div class="col-12 wow zoomIn" data-wow-delay="0.8s">
                            <div class="bg-primary rounded d-flex align-items-center justify-content-center mb-3" style="width: 60px; height: 60px;">
                                <i class="fa fa-phone-alt text-white"></i>
                            </div>
                            <h4>Support 24h/24 7j/7</h4>
                            <p class="mb-0">Un support technique disponible en permanence pour répondre à tous vos besoins</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <!-- Features Start -->


    <!-- Services Section Start - Version Simplifiée -->
    <div id="services" class="container-fluid py-5 wow fadeInUp" data-wow-delay="0.1s">
        <div class="container py-5">
            <div class="section-title text-center position-relative pb-3 mb-5 mx-auto" style="max-width: 600px;">
                <h5 class="fw-bold text-primary text-uppercase">Nos Services</h5>
                <h1 class="mb-0">Ce Que Nous Faisons</h1>
            </div>
            <div class="row g-4">
                <?php if (!empty($services)): ?>
                    <?php foreach($services as $index => $service): ?>
                        <div class="col-lg-4 col-md-6 wow zoomIn" data-wow-delay="<?php echo ($index * 0.1 + 0.1); ?>s">
                            <div class="service-item bg-light rounded d-flex flex-column align-items-center justify-content-center text-center h-100 p-4">
                                <div class="service-icon">
                                    <i class="<?php echo htmlspecialchars($service['icon'] ?: 'fas fa-cogs'); ?> fa-2x text-primary mb-3"></i>
                                </div>
                                <h4 class="mb-3"><?php echo htmlspecialchars($service['title']); ?></h4>
                                <p class="m-0"><?php echo htmlspecialchars($service['description']); ?></p>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php else: ?>
                    <!-- Message si aucun service -->
                    <div class="col-12 text-center py-5">
                        <div class="bg-light rounded p-5">
                            <i class="fas fa-tools fa-4x text-muted mb-4"></i>
                            <h4 class="text-muted mb-3">Nos services arrivent bientôt</h4>
                            <p class="text-muted mb-4">Nous préparons actuellement notre catalogue de services complet.</p>
                            <a href="#contact" class="btn btn-primary">
                                <i class="fas fa-envelope me-2"></i>Nous Contacter
                            </a>
                        </div>
                    </div>
                <?php endif; ?>
            </div>
            
            <!-- Bouton Voir Tous les Services -->
            <div class="text-center mt-5">
                <a href="services.php" class="btn btn-primary btn-lg px-5 py-3">
                    <i class="fas fa-cogs me-2"></i>Voir Tous Nos Services
                </a>
            </div>
        </div>
    </div>
    <!-- Services Section End -->


    <!-- Services Packages Start -->
    <div id="pricing" class="container-fluid py-5 wow fadeInUp" data-wow-delay="0.1s">
        <div class="container py-5">
            <div class="section-title text-center position-relative pb-3 mb-5 mx-auto" style="max-width: 600px;">
                <h5 class="fw-bold text-secondary text-uppercase">Nos Offres</h5>
                <h1 class="mb-0">Solutions Complètes pour Votre Entreprise</h1>
            </div>
            <div class="row g-4">
                <?php if (!empty($offers)): ?>
                    <?php foreach($offers as $index => $offer): ?>
                        <?php
                        $features = json_decode($offer['features'], true) ?? [];
                        $delay = ($index * 0.3 + 0.3) . 's';
                        $animation = ['rotateInUpLeft', 'pulse', 'rotateInUpRight'][$index % 3];
                        $cardClass = $offer['is_featured'] ? 'bg-white rounded shadow position-relative' : 'bg-light rounded';
                        $zIndex = $offer['is_featured'] ? 'style="z-index: 1;"' : '';
                        ?>
                        <div class="col-lg-4 wow <?php echo $animation; ?>" data-wow-delay="<?php echo $delay; ?>">
                            <div class="<?php echo $cardClass; ?>" <?php echo $zIndex; ?>>
                                <div class="border-bottom py-4 px-5 mb-4">
                                    <?php if (!empty($offer['image'])): ?>
                                        <div class="text-center mb-3">
                                            <img src="<?php echo htmlspecialchars($offer['image']); ?>" 
                                                 alt="<?php echo htmlspecialchars($offer['title']); ?>" 
                                                 style="width: 80px; height: 80px; object-fit: cover;" 
                                                 class="rounded">
                                        </div>
                                    <?php elseif (!empty($offer['icon'])): ?>
                                        <div class="text-center mb-3">
                                            <i class="fa <?php echo htmlspecialchars($offer['icon']); ?> fa-2x text-primary"></i>
                                        </div>
                                    <?php endif; ?>
                                    <h4 class="text-primary mb-1"><?php echo htmlspecialchars($offer['title']); ?></h4>
                                    <?php if (!empty($offer['subtitle'])): ?>
                                        <small class="text-uppercase"><?php echo htmlspecialchars($offer['subtitle']); ?></small>
                                    <?php endif; ?>
                                    <?php if (!empty($offer['price'])): ?>
                                        <div class="mt-2">
                                            <span class="h5 text-primary"><?php echo number_format($offer['price'], 0, ',', ' '); ?> FCFA</span>
                                        </div>
                                    <?php elseif (!empty($offer['price_text'])): ?>
                                        <div class="mt-2">
                                            <span class="h6 text-primary"><?php echo htmlspecialchars($offer['price_text']); ?></span>
                                        </div>
                                    <?php endif; ?>
                                </div>
                                <div class="p-5 pt-0">
                                    <?php if (!empty($offer['description'])): ?>
                                        <p class="mb-3"><?php echo htmlspecialchars($offer['description']); ?></p>
                                    <?php endif; ?>
                                    <?php if (!empty($features)): ?>
                                        <?php foreach($features as $feature): ?>
                                            <div class="d-flex align-items-start mb-3">
                                                <i class="fa fa-check text-primary me-3 mt-1"></i>
                                                <span><?php echo htmlspecialchars($feature); ?></span>
                                            </div>
                                        <?php endforeach; ?>
                                    <?php endif; ?>
                                    <a href="#quote" class="btn btn-primary py-2 px-4 mt-4">Demander un Devis</a>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php else: ?>
                    <!-- Message si aucune offre publiée -->
                    <div class="col-12 text-center py-5">
                        <div class="bg-light rounded p-5">
                            <i class="fas fa-box-open fa-4x text-muted mb-4"></i>
                            <h4 class="text-muted mb-3">Nos offres arrivent bientôt</h4>
                            <p class="text-muted mb-4">Découvrez nos solutions personnalisées en nous contactant directement.</p>
                            <a href="#contact" class="btn btn-primary">
                                <i class="fas fa-envelope me-2"></i>Nous Contacter
                            </a>
                        </div>
                    </div>
                <?php endif; ?>
            </div>
            
            <!-- Bouton Voir Nos Tarifs -->
            <div class="text-center mt-5">
                <a href="tarification.php" class="btn btn-primary btn-lg px-5 py-3">
                    <i class="fas fa-tags me-2"></i>Voir Nos Tarifs Complets
                </a>
            </div>
        </div>
    </div>
    <!-- Services Packages End -->


    <!-- Quote Start -->
    <div id="quote" class="container-fluid py-5 wow fadeInUp" data-wow-delay="0.1s">
        <div class="container py-5">
            <div class="row g-5">
                <div class="col-lg-7">
                    <div class="section-title position-relative pb-3 mb-5">
                        <h5 class="fw-bold text-primary text-uppercase">Demander un Devis</h5>
                        <h1 class="mb-0">Besoin d'un Devis Gratuit ? N'hésitez pas à Nous Contacter</h1>
                    </div>
                    <div class="row gx-3">
                        <div class="col-sm-6 wow zoomIn" data-wow-delay="0.2s">
                            <h5 class="mb-4"><i class="fa fa-reply text-primary me-3"></i>Réponse sous 24h</h5>
                        </div>
                        <div class="col-sm-6 wow zoomIn" data-wow-delay="0.4s">
                            <h5 class="mb-4"><i class="fa fa-phone-alt text-primary me-3"></i>Support téléphonique 24h/24</h5>
                        </div>
                    </div>
                    <p class="mb-4">Notre équipe d'experts est à votre disposition pour évaluer vos besoins et vous proposer des solutions personnalisées. Nous nous engageons à vous fournir un devis détaillé et transparent, adapté à votre budget et à vos objectifs.</p>
                    <div class="d-flex align-items-center mt-2 wow zoomIn" data-wow-delay="0.6s">
                        <div class="bg-primary d-flex align-items-center justify-content-center rounded" style="width: 60px; height: 60px;">
                            <i class="fa fa-phone-alt text-white"></i>
                        </div>
                        <div class="ps-4">
                            <h5 class="mb-2">Appelez pour toute question</h5>
                            <h4 class="text-primary mb-0">+225 0101012501</h4>
                        </div>
                    </div>
                </div>
                <div class="col-lg-5">
                    <div class="bg-primary rounded h-100 d-flex align-items-center p-5 wow zoomIn" data-wow-delay="0.9s">
                        <?php if(isset($quote_success)): ?>
                            <div class="alert alert-success w-100">
                                <i class="fas fa-check-circle me-2"></i>
                                <?php echo $quote_success; ?>
                            </div>
                        <?php elseif(isset($quote_error)): ?>
                            <div class="alert alert-danger w-100">
                                <i class="fas fa-exclamation-triangle me-2"></i>
                                <?php echo $quote_error; ?>
                            </div>
                        <?php else: ?>
                            <form method="POST" class="w-100">
                                <div class="row g-3">
                                    <div class="col-xl-12">
                                        <input type="text" name="name" class="form-control bg-light border-0" placeholder="Votre Nom" style="height: 55px;" required>
                                    </div>
                                    <div class="col-12">
                                        <input type="email" name="email" class="form-control bg-light border-0" placeholder="Votre Email" style="height: 55px;" required>
                                    </div>
                                    <div class="col-12">
                                        <input type="tel" name="phone" class="form-control bg-light border-0" placeholder="Votre Téléphone (optionnel)" style="height: 55px;">
                                    </div>
                                    <div class="col-12">
                                        <select name="service" class="form-select bg-light border-0" style="height: 55px;" required>
                                            <option value="">Sélectionnez un Service</option>
                                            <option value="Cybersécurité">Cybersécurité</option>
                                            <option value="Développement Web et Mobile">Développement Web et Mobile</option>
                                            <option value="Analyse de Données">Analyse de Données</option>
                                            <option value="Matériels informatiques">Matériels informatiques</option>
                                            <option value="Optimisation SEO">Optimisation SEO</option>
                                            <option value="Intelligence Artificielle">Intelligence Artificielle & Chatbots</option>
                                        </select>
                                    </div>
                                    <div class="col-12">
                                        <textarea name="message" class="form-control bg-light border-0" rows="3" placeholder="Décrivez votre projet ou vos besoins..." required></textarea>
                                    </div>
                                    <div class="col-12">
                                        <button name="submit_quote" class="btn btn-dark w-100 py-3" type="submit">
                                            <i class="fas fa-paper-plane me-2"></i>Demander un Devis
                                        </button>
                                    </div>
                                </div>
                            </form>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <!-- Quote End -->



    <!-- Testimonial Start -->
    <div id="testimonials" class="container-fluid py-5 wow fadeInUp" data-wow-delay="0.1s">
        <div class="container py-5">
            <div class="section-title text-center position-relative pb-3 mb-4 mx-auto" style="max-width: 600px;">
                <h5 class="fw-bold text-primary text-uppercase">Témoignages</h5>
                <h1 class="mb-0">Ce Que Nos Clients Disent de Nos Services Numériques</h1>
            </div>
            <div class="owl-carousel testimonial-carousel wow fadeInUp" data-wow-delay="0.6s">
                <?php if (!empty($testimonials)): ?>
                    <?php foreach($testimonials as $testimonial): ?>
                        <div class="testimonial-item bg-light my-4">
                            <div class="d-flex align-items-center border-bottom pt-5 pb-4 px-5">
                                <?php if (!empty($testimonial['client_image'])): ?>
                                    <img class="img-fluid rounded" src="<?php echo htmlspecialchars($testimonial['client_image']); ?>" style="width: 60px; height: 60px; object-fit: cover;" alt="Photo client">
                                <?php else: ?>
                                    <div class="bg-primary rounded d-flex align-items-center justify-content-center" style="width: 60px; height: 60px;">
                                        <i class="fa fa-user text-white"></i>
                                    </div>
                                <?php endif; ?>
                                <div class="ps-4">
                                    <h4 class="text-primary mb-1"><?php echo htmlspecialchars($testimonial['client_name']); ?></h4>
                                    <small class="text-uppercase"><?php echo htmlspecialchars($testimonial['client_position'] ?? 'Client'); ?></small>
                                    <?php if ($testimonial['is_featured']): ?>
                                        <div class="mt-1">
                                            <span class="badge bg-warning text-dark">
                                                <i class="fa fa-star"></i> Témoignage vedette
                                            </span>
                                        </div>
                                    <?php endif; ?>
                                </div>
                            </div>
                            <div class="pt-4 pb-5 px-5">
                                <?php echo nl2br(htmlspecialchars($testimonial['content'])); ?>
                                <?php if (!empty($testimonial['rating']) && $testimonial['rating'] > 0): ?>
                                    <div class="mt-3">
                                        <?php for($i = 1; $i <= 5; $i++): ?>
                                            <i class="fa fa-star <?php echo $i <= $testimonial['rating'] ? 'text-warning' : 'text-muted'; ?>"></i>
                                        <?php endfor; ?>
                                    </div>
                                <?php endif; ?>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php else: ?>
                    <!-- Message si aucun témoignage -->
                    <div class="col-12 text-center py-5">
                        <div class="bg-light rounded p-5">
                            <i class="fas fa-comments fa-4x text-muted mb-4"></i>
                            <h4 class="text-muted mb-3">Témoignages à venir</h4>
                            <p class="text-muted mb-4">Nous collectons actuellement les retours de nos clients satisfaits.</p>
                            <a href="#contact" class="btn btn-primary">
                                <i class="fas fa-envelope me-2"></i>Partagez Votre Expérience
                            </a>
                        </div>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
    <!-- Testimonial End -->


    <!-- Photos Section Start - Format Grid Simple -->
    <div id="photos" class="photos-grid-section">
        <div class="photos-grid-container">
            <div class="photos-grid-title">
                <h2>Galerie Photos</h2>
                <p>Découvrez notre univers à travers nos réalisations</p>
            </div>
            
            <div class="photos-grid">
                <?php if (!empty($photos)): ?>
                    <?php foreach($photos as $index => $photo): ?>
                        <div class="photo-grid-item">
                            <img class="photo-grid-image" 
                                 src="<?php echo htmlspecialchars($photo['file_path']); ?>" 
                                 alt="<?php echo htmlspecialchars($photo['alt_text'] ?? $photo['title']); ?>"
                                 loading="lazy"
                                 onerror="this.style.display='none'; this.nextElementSibling.style.display='flex';">
                            
                            <!-- Fallback simple -->
                            <div class="photo-grid-fallback" style="display: none;">
                                <i class="fas fa-image"></i>
                                <h4><?php echo htmlspecialchars($photo['title']); ?></h4>
                                <p>Image non disponible</p>
                            </div>
                            
                            <div class="photo-grid-content">
                                <h3><?php echo htmlspecialchars($photo['title']); ?></h3>
                                <?php if (!empty($photo['description'])): ?>
                                    <p><?php echo htmlspecialchars(substr($photo['description'], 0, 100)); ?><?php if(strlen($photo['description']) > 100): ?>...<?php endif; ?></p>
                                <?php endif; ?>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php else: ?>
                    <!-- Photos par défaut en format grid -->
                    <div class="photo-grid-item">
                        <img class="photo-grid-image" src="img/team-1.jpg" alt="Équipe Universe Security" loading="lazy">
                        <div class="photo-grid-content">
                            <h3>Notre Équipe</h3>
                            <p>Professionnels expérimentés et certifiés</p>
                        </div>
                    </div>
                    
                    <div class="photo-grid-item">
                        <img class="photo-grid-image" src="img/team-2.jpg" alt="Bureaux Universe Security" loading="lazy">
                        <div class="photo-grid-content">
                            <h3>Nos Bureaux</h3>
                            <p>Locaux modernes à Cocody Angré</p>
                        </div>
                    </div>
                    
                    <div class="photo-grid-item">
                        <img class="photo-grid-image" src="img/team-3.jpg" alt="Technologies" loading="lazy">
                        <div class="photo-grid-content">
                            <h3>Technologies</h3>
                            <p>Matériel high-tech et solutions innovantes</p>
                        </div>
                    </div>
                    
                    <div class="photo-grid-item">
                        <img class="photo-grid-image" src="img/logo universe security.jpg" alt="Universe Security" loading="lazy">
                        <div class="photo-grid-content">
                            <h3>Universe Security</h3>
                            <p>Excellence et innovation depuis 2022</p>
                        </div>
                    </div>
                <?php endif; ?>
            </div>
            
            <!-- Bouton Voir Plus Photos -->
            <div class="text-center mt-5">
                <a href="galerie.php#photos-tab" class="btn btn-primary btn-lg px-5 py-3">
                    <i class="fas fa-images me-2"></i>Voir Toutes les Photos
                </a>
            </div>
        </div>
    </div>
    <!-- Photos Section End -->


    <!-- Videos Section Start -->
    <div id="videos" class="container-fluid py-5 wow fadeInUp neural-section" data-wow-delay="0.1s">
        <div class="neural-bg">
            <div class="neuron1"></div>
            <div class="neuron2"></div>
            <div class="neuron3"></div>
            <div class="neuron4"></div>
            <div class="connection1"></div>
            <div class="connection2"></div>
        </div>
        <div class="container py-5">
            <div class="section-title text-center position-relative pb-3 mb-5 mx-auto" style="max-width: 600px;">
                <h5 class="fw-bold text-secondary text-uppercase">Galerie Vidéos</h5>
                <h1 class="mb-0">Découvrez Nos Services en Action</h1>
            </div>
            
            <div class="row g-4">
                <?php if (!empty($videos)): ?>
                    <?php foreach($videos as $index => $video): ?>
                        <div class="col-lg-6 col-md-12 wow fadeInUp" data-wow-delay="<?php echo ($index * 0.2 + 0.1); ?>s">
                            <div class="position-relative overflow-hidden rounded bg-dark">
                                <video class="w-100" style="height: 300px; object-fit: cover;" controls poster="">
                                    <source src="<?php echo htmlspecialchars($video['file_path']); ?>" type="video/<?php echo htmlspecialchars($video['file_type']); ?>">
                                    Votre navigateur ne supporte pas la lecture de vidéos.
                                </video>
                                <div class="position-absolute bottom-0 start-0 w-100 bg-gradient-dark p-3">
                                    <h6 class="text-white mb-1"><?php echo htmlspecialchars($video['title']); ?></h6>
                                    <?php if (!empty($video['description'])): ?>
                                        <p class="text-light small mb-0"><?php echo htmlspecialchars(substr($video['description'], 0, 100)); ?><?php if(strlen($video['description']) > 100): ?>...<?php endif; ?></p>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php else: ?>
                    <!-- Message si aucune vidéo -->
                    <div class="col-12 text-center py-5">
                        <div class="bg-light rounded p-5">
                            <i class="fas fa-video fa-4x text-muted mb-4"></i>
                            <h4 class="text-muted mb-3">Nos vidéos arrivent bientôt</h4>
                            <p class="text-muted mb-4">Nous préparons du contenu vidéo exclusif pour vous présenter nos services et réalisations.</p>
                            <a href="galerie.php" class="btn btn-primary">
                                <i class="fas fa-images me-2"></i>Voir Notre Galerie
                            </a>
                        </div>
                    </div>
                <?php endif; ?>
            </div>
            
            <!-- Bouton Voir Plus Vidéos -->
            <div class="text-center mt-5">
                <a href="galerie.php#videos-tab" class="btn btn-secondary btn-lg px-5 py-3">
                    <i class="fas fa-video me-2"></i>Voir Toutes les Vidéos
                </a>
            </div>
        </div>
    </div>
    <!-- Videos Section End -->


    <!-- Team Section Start -->
    <div id="team" class="container-fluid py-5 wow fadeInUp" data-wow-delay="0.1s">
        <div class="container py-5">
            <div class="section-title text-center position-relative pb-3 mb-5 mx-auto" style="max-width: 600px;">
                <h5 class="fw-bold text-secondary text-uppercase">Notre Équipe</h5>
                <h1 class="mb-0">Rencontrez Nos Professionnels</h1>
            </div>
            
            <?php if (!empty($team_members)): ?>
                <div class="row g-5">
                    <?php foreach($team_members as $index => $member): ?>
                        <?php $delay = ($index * 0.2 + 0.2) . 's'; ?>
                        <div class="col-lg-4 wow slideInUp" data-wow-delay="<?php echo $delay; ?>">
                            <div class="team-item bg-light rounded overflow-hidden">
                                <div class="team-img position-relative overflow-hidden">
                                    <?php if (!empty($member['image'])): ?>
                                        <img class="img-fluid w-100" src="<?php echo htmlspecialchars($member['image']); ?>" alt="<?php echo htmlspecialchars($member['name']); ?>">
                                    <?php else: ?>
                                        <div class="bg-primary d-flex align-items-center justify-content-center" style="height: 300px;">
                                            <i class="fa fa-user fa-5x text-white"></i>
                                        </div>
                                    <?php endif; ?>
                                    <div class="team-social">
                                        <?php if (!empty($member['social_twitter'])): ?>
                                            <a class="btn btn-outline-primary btn-square" href="<?php echo htmlspecialchars($member['social_twitter']); ?>" target="_blank">
                                                <i class="fab fa-twitter"></i>
                                            </a>
                                        <?php endif; ?>
                                        <?php if (!empty($member['social_facebook'])): ?>
                                            <a class="btn btn-outline-primary btn-square" href="<?php echo htmlspecialchars($member['social_facebook']); ?>" target="_blank">
                                                <i class="fab fa-facebook-f"></i>
                                            </a>
                                        <?php endif; ?>
                                        <?php if (!empty($member['social_linkedin'])): ?>
                                            <a class="btn btn-outline-primary btn-square" href="<?php echo htmlspecialchars($member['social_linkedin']); ?>" target="_blank">
                                                <i class="fab fa-linkedin-in"></i>
                                            </a>
                                        <?php endif; ?>
                                        <?php if (!empty($member['social_instagram'])): ?>
                                            <a class="btn btn-outline-primary btn-square" href="<?php echo htmlspecialchars($member['social_instagram']); ?>" target="_blank">
                                                <i class="fab fa-instagram"></i>
                                            </a>
                                        <?php endif; ?>
                                    </div>
                                </div>
                                <div class="text-center py-4">
                                    <h4 class="text-primary"><?php echo htmlspecialchars($member['name']); ?></h4>
                                    <p class="text-uppercase m-0"><?php echo htmlspecialchars($member['position']); ?></p>
                                    <?php if (!empty($member['bio'])): ?>
                                        <p class="mt-3 text-muted"><?php echo htmlspecialchars(substr($member['bio'], 0, 100)); ?><?php if (strlen($member['bio']) > 100): ?>...<?php endif; ?></p>
                                    <?php endif; ?>
                                    <?php if (!empty($member['email'])): ?>
                                        <div class="mt-3">
                                            <a href="mailto:<?php echo htmlspecialchars($member['email']); ?>" class="btn btn-sm btn-outline-primary">
                                                <i class="fa fa-envelope me-2"></i>Contact
                                            </a>
                                        </div>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php else: ?>
                <!-- Message si aucun membre d'équipe -->
                <div class="text-center py-5">
                    <div class="bg-light rounded p-5">
                        <i class="fas fa-users fa-4x text-muted mb-4"></i>
                        <h4 class="text-muted mb-3">Notre équipe se présente bientôt</h4>
                        <p class="text-muted mb-4">Découvrez nos professionnels expérimentés en nous contactant directement.</p>
                        <a href="#contact" class="btn btn-primary">
                            <i class="fas fa-envelope me-2"></i>Nous Contacter
                        </a>
                    </div>
                </div>
            <?php endif; ?>
            
            <!-- Bouton Voir Plus -->
            <div class="text-center mt-5">
                <a href="equipe.php" class="btn btn-primary btn-lg px-5 py-3">
                    <i class="fas fa-users me-2"></i>Découvrir Toute l'Équipe
                </a>
            </div>
        </div>
    </div>
    <!-- Team Section End -->


    <!-- Blog Section Start -->
    <div id="blog" class="container-fluid py-5 wow fadeInUp" data-wow-delay="0.1s">
        <div class="container py-5">
            <div class="section-title text-center position-relative pb-3 mb-5 mx-auto" style="max-width: 600px;">
                <h5 class="fw-bold text-secondary text-uppercase">Blog & Actualités</h5>
                <h1 class="mb-0">Nos Derniers Articles</h1>
            </div>
            
            <?php if (!empty($blog_articles)): ?>
                <div class="row g-5">
                    <?php foreach($blog_articles as $index => $article): ?>
                        <?php $delay = ($index * 0.2 + 0.2) . 's'; ?>
                        <div class="col-lg-4 wow slideInUp" data-wow-delay="<?php echo $delay; ?>">
                            <div class="blog-item bg-light rounded overflow-hidden">
                                <div class="blog-img position-relative overflow-hidden">
                                    <?php if (!empty($article['featured_image'])): ?>
                                        <img class="img-fluid" src="<?php echo htmlspecialchars($article['featured_image']); ?>" alt="<?php echo htmlspecialchars($article['title']); ?>">
                                    <?php else: ?>
                                        <div class="bg-primary d-flex align-items-center justify-content-center" style="height: 250px;">
                                            <i class="fa fa-newspaper fa-4x text-white"></i>
                                        </div>
                                    <?php endif; ?>
                                    <div class="blog-date">
                                        <?php if ($article['published_at']): ?>
                                            <?php $date = new DateTime($article['published_at']); ?>
                                            <span><?php echo $date->format('d'); ?></span>
                                            <small><?php echo $date->format('M'); ?></small>
                                        <?php endif; ?>
                                    </div>
                                </div>
                                <div class="p-4">
                                    <div class="d-flex mb-3">
                                        <small class="me-3">
                                            <i class="far fa-user text-primary me-2"></i>
                                            <?php echo htmlspecialchars($article['author_name'] ?? 'Admin'); ?>
                                        </small>
                                        <?php if ($article['published_at']): ?>
                                            <small>
                                                <i class="far fa-calendar-alt text-primary me-2"></i>
                                                <?php echo date('d M, Y', strtotime($article['published_at'])); ?>
                                            </small>
                                        <?php endif; ?>
                                    </div>
                                    
                                    <?php if (!empty($article['category'])): ?>
                                        <div class="mb-2">
                                            <span class="badge bg-primary"><?php echo htmlspecialchars($article['category']); ?></span>
                                        </div>
                                    <?php endif; ?>
                                    
                                    <h4 class="mb-3"><?php echo htmlspecialchars($article['title']); ?></h4>
                                    
                                    <?php if (!empty($article['excerpt'])): ?>
                                        <p><?php echo htmlspecialchars($article['excerpt']); ?></p>
                                    <?php else: ?>
                                        <p><?php echo htmlspecialchars(substr(strip_tags($article['content']), 0, 120)); ?>...</p>
                                    <?php endif; ?>
                                    
                                    <a class="text-uppercase" href="blog.php?article=<?php echo htmlspecialchars($article['slug']); ?>">
                                        Lire Plus <i class="bi bi-arrow-right"></i>
                                    </a>
                                    
                                    <?php if ($article['views_count'] > 0): ?>
                                        <div class="mt-2">
                                            <small class="text-muted">
                                                <i class="fa fa-eye me-1"></i>
                                                <?php echo $article['views_count']; ?> vues
                                            </small>
                                        </div>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php else: ?>
                <!-- Message si aucun article publié -->
                <div class="text-center py-5">
                    <div class="bg-light rounded p-5">
                        <i class="fas fa-newspaper fa-4x text-muted mb-4"></i>
                        <h4 class="text-muted mb-3">Aucun article publié</h4>
                        <p class="text-muted mb-4">Les articles de blog apparaîtront ici une fois publiés par l'équipe.</p>
                        <a href="#contact" class="btn btn-primary">
                            <i class="fas fa-envelope me-2"></i>Nous Contacter
                        </a>
                    </div>
                </div>
            <?php endif; ?>
            
            <!-- Bouton Voir Plus Blog -->
            <div class="text-center mt-5">
                <a href="blog.php" class="btn btn-info btn-lg px-5 py-3">
                    <i class="fas fa-blog me-2"></i>Voir Tous les Articles
                </a>
            </div>
        </div>
    </div>
    <!-- Blog Section End -->


    <!-- Vendor Start -->
    <div class="container-fluid py-5 wow fadeInUp" data-wow-delay="0.1s">
        <div class="container py-5 mb-5">
            <?php if (!empty($vendors)): ?>
                <div class="section-title text-center position-relative pb-3 mb-4 mx-auto" style="max-width: 600px;">
                    <h5 class="fw-bold text-primary text-uppercase">Nos Partenaires</h5>
                    <h1 class="mb-0">Ils Nous Font Confiance</h1>
                </div>
                <div class="bg-white">
                    <div class="owl-carousel vendor-carousel">
                        <?php foreach($vendors as $vendor): ?>
                            <?php if ($vendor['website'] && $vendor['website'] !== '#'): ?>
                                <a href="<?php echo htmlspecialchars($vendor['website']); ?>" target="_blank" title="<?php echo htmlspecialchars($vendor['name']); ?>">
                                    <img src="<?php echo htmlspecialchars($vendor['logo']); ?>" alt="<?php echo htmlspecialchars($vendor['name']); ?>" style="max-height: 80px; object-fit: contain;">
                                </a>
                            <?php else: ?>
                                <img src="<?php echo htmlspecialchars($vendor['logo']); ?>" alt="<?php echo htmlspecialchars($vendor['name']); ?>" style="max-height: 80px; object-fit: contain;">
                            <?php endif; ?>
                        <?php endforeach; ?>
                    </div>
                </div>
            <?php else: ?>
                <!-- Message si aucun partenaire -->
                <div class="text-center py-5">
                    <div class="bg-light rounded p-5">
                        <i class="fas fa-handshake fa-4x text-muted mb-4"></i>
                        <h4 class="text-muted mb-3">Nos partenaires arrivent bientôt</h4>
                        <p class="text-muted mb-4">Nous développons actuellement notre réseau de partenaires de confiance.</p>
                        <a href="#contact" class="btn btn-primary">
                            <i class="fas fa-envelope me-2"></i>Nous Contacter
                        </a>
                    </div>
                </div>
            <?php endif; ?>
        </div>
    </div>
    <!-- Vendor End -->
    

    <!-- Contact Start -->
    <div id="contact" class="container-fluid py-5 wow fadeInUp" data-wow-delay="0.1s">
        <div class="container py-5">
            <div class="section-title text-center position-relative pb-3 mb-5 mx-auto" style="max-width: 600px;">
                <h5 class="fw-bold text-primary text-uppercase">Nous Contacter</h5>
                <h1 class="mb-0">Si Vous Avez une Question, N'hésitez pas à Nous Contacter</h1>
            </div>
            
            <!-- Messages de contact -->
            <?php if (isset($_SESSION['contact_success'])): ?>
                <div class="alert alert-success alert-dismissible fade show mx-auto" role="alert" style="max-width: 600px;">
                    <i class="fas fa-check-circle me-2"></i><?php echo htmlspecialchars($_SESSION['contact_success']); ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
                <?php unset($_SESSION['contact_success']); ?>
            <?php endif; ?>
            
            <?php if (isset($_SESSION['contact_error'])): ?>
                <div class="alert alert-danger alert-dismissible fade show mx-auto" role="alert" style="max-width: 600px;">
                    <i class="fas fa-exclamation-triangle me-2"></i><?php echo htmlspecialchars($_SESSION['contact_error']); ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
                <?php unset($_SESSION['contact_error']); ?>
            <?php endif; ?>
            <div class="row g-5 mb-5">
                <div class="col-lg-4">
                    <div class="d-flex align-items-center wow fadeIn" data-wow-delay="0.1s">
                        <div class="bg-primary d-flex align-items-center justify-content-center rounded" style="width: 60px; height: 60px;">
                            <i class="fa fa-phone-alt text-white"></i>
                        </div>
                        <div class="ps-4">
                            <h5 class="mb-2">Appelez pour toute question</h5>
                            <h4 class="text-primary mb-0">+225 0101012501</h4>
                        </div>
                    </div>
                </div>
                <div class="col-lg-4">
                    <div class="d-flex align-items-center wow fadeIn" data-wow-delay="0.4s">
                        <div class="bg-primary d-flex align-items-center justify-content-center rounded" style="width: 60px; height: 60px;">
                            <i class="fa fa-envelope-open text-white"></i>
                        </div>
                        <div class="ps-4">
                            <h5 class="mb-2">Email pour un devis gratuit</h5>
                            <h4 class="text-primary mb-0">info@universe-security.ci</h4>
                        </div>
                    </div>
                </div>
                <div class="col-lg-4">
                    <div class="d-flex align-items-center wow fadeIn" data-wow-delay="0.8s">
                        <div class="bg-primary d-flex align-items-center justify-content-center rounded" style="width: 60px; height: 60px;">
                            <i class="fa fa-map-marker-alt text-white"></i>
                        </div>
                        <div class="ps-4">
                            <h5 class="mb-2">Visitez notre bureau</h5>
                            <h4 class="text-primary mb-0">Cocody Angré 8ième tranche, Abidjan</h4>
                        </div>
                    </div>
                </div>
            </div>
            <div class="row g-5">
                <div class="col-lg-6 wow slideInUp" data-wow-delay="0.3s">
                    <form method="POST" action="includes/contact_handler.php">
                        <div class="row g-3">
                            <div class="col-md-6">
                                <input type="text" name="name" class="form-control border-0 bg-light px-4" placeholder="Votre Nom" style="height: 55px;" required>
                            </div>
                            <div class="col-md-6">
                                <input type="email" name="email" class="form-control border-0 bg-light px-4" placeholder="Votre Email" style="height: 55px;" required>
                            </div>
                            <div class="col-12">
                                <input type="text" name="subject" class="form-control border-0 bg-light px-4" placeholder="Sujet" style="height: 55px;" required>
                            </div>
                            <div class="col-12">
                                <input type="tel" name="phone" class="form-control border-0 bg-light px-4" placeholder="Téléphone (optionnel)" style="height: 55px;">
                            </div>
                            <div class="col-12">
                                <input type="text" name="company" class="form-control border-0 bg-light px-4" placeholder="Entreprise (optionnel)" style="height: 55px;">
                            </div>
                            <div class="col-12">
                                <textarea name="message" class="form-control border-0 bg-light px-4 py-3" rows="4" placeholder="Votre Message" required></textarea>
                            </div>
                            <div class="col-12">
                                <button class="btn btn-primary w-100 py-3" type="submit">Envoyer le Message</button>
                            </div>
                        </div>
                    </form>
                </div>
                <div class="col-lg-6 wow slideInUp" data-wow-delay="0.6s">
                    <iframe class="position-relative rounded w-100 h-100"
                        src="https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d3972.2167493261896!2d-3.9707207!3d5.359785!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0xfc1eb1c9f8c5555%3A0x5aa8f8b8b8b8b8b8!2sCocody%20Angr%C3%A9%208%C3%A8me%20tranche%2C%20Abidjan%2C%20C%C3%B4te%20d%27Ivoire!5e0!3m2!1sfr!2sus!4v1705234567890!5m2!1sfr!2sus"
                        frameborder="0" style="min-height: 350px; border:0;" allowfullscreen="" aria-hidden="false"
                        tabindex="0" loading="lazy" referrerpolicy="no-referrer-when-downgrade"></iframe>
                </div>
            </div>
        </div>
    </div>
    <!-- Contact End -->

    <!-- Footer Start -->
    <div class="container-fluid bg-dark text-light mt-5 wow fadeInUp" data-wow-delay="0.1s">
        <div class="container">
            <div class="row gx-5">
                <div class="col-lg-4 col-md-6 footer-about">
                    <div class="d-flex flex-column align-items-center justify-content-center text-center h-100 bg-primary p-4">
                        <a href="index.html" class="navbar-brand">
                            <img src="img/logo universe security.jpg" alt="Logo" style="height: 100px; width: 150px;">
                        </a>
                        <p class="mt-3 mb-4">Nous sommes spécialisés dans les solutions technologiques innovantes pour accompagner la croissance de votre entreprise. Notre expertise et notre engagement font de nous le partenaire idéal pour vos projets digitaux.</p>
                        <form action="">
                            <div class="input-group">
                                <input type="text" class="form-control border-white p-3" placeholder="Votre Email">
                                <button class="btn btn-dark">S'inscrire</button>
                            </div>
                        </form>
                    </div>
                </div>
                <div class="col-lg-8 col-md-6">
                    <div class="row gx-5">
                        <div class="col-lg-4 col-md-12 pt-5 mb-5">
                            <div class="section-title section-title-sm position-relative pb-3 mb-4">
                                <h3 class="text-light mb-0">Nous Contacter</h3>
                            </div>
                            <div class="d-flex mb-2">
                                <i class="bi bi-geo-alt text-primary me-2"></i>
                                <p class="mb-0">Cocody Angré 8ième Tranche, Abidjan, Côte d'Ivoire</p>
                            </div>
                            <div class="d-flex mb-2">
                                <i class="bi bi-envelope-open text-primary me-2"></i>
                                <p class="mb-0">info@universe-security.ci</p>
                            </div>
                            <div class="d-flex mb-2">
                                <i class="bi bi-telephone text-primary me-2"></i>
                                <p class="mb-0">+225 0101012501</p>                            </div>
                            <div class="d-flex mt-4">
                                <a class="btn btn-primary btn-square me-2" href="#"><i class="fab fa-twitter fw-normal"></i></a>
                                <a class="btn btn-primary btn-square me-2" href="#"><i class="fab fa-facebook-f fw-normal"></i></a>
                                <a class="btn btn-primary btn-square me-2" href="#"><i class="fab fa-linkedin-in fw-normal"></i></a>
                                <a class="btn btn-primary btn-square" href="#"><i class="fab fa-instagram fw-normal"></i></a>
                            </div>
                        </div>
                        <div class="col-lg-4 col-md-12 pt-0 pt-lg-5 mb-5">
                            <div class="section-title section-title-sm position-relative pb-3 mb-4">
                                <h3 class="text-light mb-0">Liens Rapides</h3>
                            </div>
                            <div class="link-animated d-flex flex-column justify-content-start">
                                <a class="text-light mb-2" href="#home"><i class="bi bi-arrow-right text-primary me-2"></i>Accueil</a>
                                <a class="text-light mb-2" href="#about"><i class="bi bi-arrow-right text-primary me-2"></i>À Propos</a>
                                <a class="text-light mb-2" href="services.php"><i class="bi bi-arrow-right text-primary me-2"></i>Nos Services</a>
                                <a class="text-light mb-2" href="#team"><i class="bi bi-arrow-right text-primary me-2"></i>Notre Équipe</a>
                                <a class="text-light mb-2" href="#blog"><i class="bi bi-arrow-right text-primary me-2"></i>Blog</a>
                                <a class="text-light" href="#contact"><i class="bi bi-arrow-right text-primary me-2"></i>Contact</a>
                            </div>
                        </div>
                        <div class="col-lg-4 col-md-12 pt-0 pt-lg-5 mb-5">
                            <div class="section-title section-title-sm position-relative pb-3 mb-4">
                                <h3 class="text-light mb-0">Liens Populaires</h3>
                            </div>
                            <div class="link-animated d-flex flex-column justify-content-start">
                                <a class="text-light mb-2" href="#features"><i class="bi bi-arrow-right text-primary me-2"></i>Fonctionnalités</a>
                                <a class="text-light mb-2" href="#pricing"><i class="bi bi-arrow-right text-primary me-2"></i>Tarification</a>
                                <a class="text-light mb-2" href="#testimonials"><i class="bi bi-arrow-right text-primary me-2"></i>Témoignages</a>
                                <a class="text-light mb-2" href="#quote"><i class="bi bi-arrow-right text-primary me-2"></i>Devis Gratuit</a>
                                <a class="text-light mb-2" href="#blog"><i class="bi bi-arrow-right text-primary me-2"></i>Blog</a>
                                <a class="text-light" href="#contact"><i class="bi bi-arrow-right text-primary me-2"></i>Support</a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="container-fluid text-white" style="background: #061429;">
        <div class="container text-center">
            <div class="row justify-content-end">
                <div class="col-lg-8 col-md-6">
                    <div class="d-flex align-items-center justify-content-center" style="height: 75px;">
                        <p class="mb-0">&copy; <a class="text-white border-bottom" href="#">universe-security</a>. Tous Droits Réservés. 
						
						<!--/*** This template is free as long as you keep the footer author's credit link/attribution link/backlink. If you'd like to use the template without the footer author's credit link/attribution link/backlink, you can purchase the Credit Removal License from "https://htmlcodex.com/credit-removal". Thank you for your support. ***/-->
						Designed by<a class="text-white border-bottom" href="#">universe-security</a> | 
						<a class="text-white border-bottom" href="admin/login.php" title="Espace Administrateur">Admin</a></p>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <!-- Footer End -->


    <!-- Back to Top -->
    <a href="#" class="btn btn-lg btn-primary btn-lg-square rounded back-to-top"><i class="bi bi-arrow-up"></i></a>

    <!-- Theme Toggle Button -->
    <button id="theme-toggle" class="theme-toggle" title="Passer en mode sombre">
        <i class="bi bi-moon-fill"></i>
    </button>


    <!-- JavaScript Libraries -->
    <script src="https://code.jquery.com/jquery-3.4.1.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.0.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="lib/wow/wow.min.js"></script>
    <script src="lib/easing/easing.min.js"></script>
    <script src="lib/waypoints/waypoints.min.js"></script>
    <script src="lib/counterup/counterup.min.js"></script>
    <script src="lib/owlcarousel/owl.carousel.min.js"></script>

    <!-- Template Javascript -->
    <script src="js/main.js"></script>
    <script src="js/neural-animation.js"></script>
    <script src="js/active-nav.js"></script>
    <script src="js/theme-toggle.js"></script>
</body>

</html>