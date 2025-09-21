<?php
// Connexion à la base de données pour récupérer les services
try {
    require_once 'admin/config/database.php';
    
    $database = new Database();
    $conn = $database->getConnection();
    
    // Récupérer tous les services actifs
    $query = "SELECT * FROM services WHERE is_active = 1 ORDER BY display_order ASC, created_at DESC";
    $stmt = $conn->prepare($query);
    $stmt->execute();
    $services = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
} catch(Exception $e) {
    $services = [];
    error_log("Erreur récupération services: " . $e->getMessage());
}
?>

<!DOCTYPE html>
<html lang="fr">

<head>
    <meta charset="utf-8">
    <title>Services de Cybersécurité et Sécurité Informatique - Universe Security Côte d'Ivoire</title>
    <meta content="width=device-width, initial-scale=1.0" name="viewport">
    <meta content="services cybersécurité, audit sécurité informatique, protection données, consultation IT, développement sécurisé, Abidjan, Côte d'Ivoire, entreprise sécurité" name="keywords">
    <meta content="Services complets de cybersécurité en Côte d'Ivoire : audit sécurité, protection des données, développement sécurisé, consultation IT. Expertise reconnue à Abidjan." name="description">
    
    <!-- Open Graph -->
    <meta property="og:type" content="website">
    <meta property="og:url" content="https://universe-security.ci/services.php">
    <meta property="og:title" content="Services de Cybersécurité - Universe Security">
    <meta property="og:description" content="Services complets de cybersécurité en Côte d'Ivoire : audit sécurité, protection des données, développement sécurisé.">
    <meta property="og:image" content="https://universe-security.ci/img/logo universe security.jpg">
    
    <!-- Données structurées pour les services -->
    <script type="application/ld+json">
    {
      "@context": "https://schema.org",
      "@type": "Service",
      "name": "Services de Cybersécurité",
      "provider": {
        "@type": "Organization",
        "name": "Universe Security",
        "url": "https://universe-security.ci"
      },
      "description": "Services complets de cybersécurité et sécurité informatique en Côte d'Ivoire",
      "areaServed": "Côte d'Ivoire",
      "serviceType": "Cybersécurité"
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
                    <small class="me-3 text-light"><i class="fa fa-map-marker-alt me-2"></i>Cocody Angré 8ième tranche, Abidjan</small>
                    <small class="me-3 text-light"><i class="fa fa-phone-alt me-2"></i>+225 0101012501</small>
                    <small class="text-light"><i class="fa fa-envelope-open me-2"></i>info@universe-security.ci</small>
                </div>
            </div>
            <div class="col-lg-4 text-center text-lg-end">
                <div class="d-inline-flex align-items-center" style="height: 45px;">
                    <a class="btn btn-sm btn-outline-light btn-sm-square rounded-circle me-2" href=""><i class="fab fa-twitter fw-normal"></i></a>
                    <a class="btn btn-sm btn-outline-light btn-sm-square rounded-circle me-2" href=""><i class="fab fa-facebook-f fw-normal"></i></a>
                    <a class="btn btn-sm btn-outline-light btn-sm-square rounded-circle me-2" href=""><i class="fab fa-linkedin-in fw-normal"></i></a>
                    <a class="btn btn-sm btn-outline-light btn-sm-square rounded-circle" href=""><i class="fab fa-instagram fw-normal"></i></a>
                </div>
            </div>
        </div>
    </div>
    <!-- Topbar End -->

    <!-- Navbar & Carousel Start -->
    <div class="container-fluid position-relative p-0">
        <nav class="navbar navbar-expand-lg navbar-dark px-5 py-3 py-lg-0">
            <a href="index.php" class="navbar-brand p-0">
                <img src="img/logo universe security.jpg" alt="Logo" style="height: 60px;">
            </a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarCollapse">
                <span class="fa fa-bars"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarCollapse">
                <div class="navbar-nav ms-auto py-0">
                    <a href="index.php" class="nav-item nav-link">Accueil</a>
                    <a href="index.php#about" class="nav-item nav-link">À propos</a>
                    <a href="services.php" class="nav-item nav-link active">Services</a>
                    <div class="nav-item dropdown">
                        <a href="#" class="nav-link dropdown-toggle" data-bs-toggle="dropdown">Outils</a>
                        <div class="dropdown-menu fade-up m-0">
                            <a href="galerie.php#photos-tab" class="dropdown-item"><i class="fas fa-images me-2"></i>Photos</a>
                            <a href="galerie.php#videos-tab" class="dropdown-item"><i class="fas fa-video me-2"></i>Vidéos</a>
                        </div>
                    </div>
                    <a href="tarification.php" class="nav-item nav-link">Tarification</a>
                    <a href="equipe.php" class="nav-item nav-link">Équipe</a>
                    <a href="blog.php" class="nav-item nav-link">Blog</a>
                    <a href="index.php#contact" class="nav-item nav-link">Contact</a>
                </div>
            </div>
        </nav>

        <div class="container-fluid bg-primary py-5 bg-header" style="margin-bottom: 90px;">
            <div class="row py-5">
                <div class="col-12 pt-lg-5 mt-lg-5 text-center">
                    <h1 class="display-4 text-white animated zoomIn">Nos Services</h1>
                    <a href="index.php" class="h5 text-white">Accueil</a>
                    <i class="far fa-circle text-white px-2"></i>
                    <a href="services.php" class="h5 text-white">Services</a>
                </div>
            </div>
        </div>
    </div>
    <!-- Navbar & Carousel End -->

    <!-- Services Start -->
    <div class="container-fluid py-5 wow fadeInUp" data-wow-delay="0.1s">
        <div class="container py-5">
            <div class="section-title text-center position-relative pb-3 mb-5 mx-auto" style="max-width: 600px;">
                <h5 class="fw-bold text-primary text-uppercase">Nos Services</h5>
                <h1 class="mb-0">Solutions Complètes de Sécurité Informatique</h1>
            </div>
            
            <?php if (!empty($services)): ?>
                <div class="row g-5">
                    <?php foreach($services as $index => $service): ?>
                        <div class="col-lg-4 col-md-6 wow zoomIn" data-wow-delay="<?php echo ($index * 0.1 + 0.1); ?>s">
                            <div class="service-item bg-light rounded d-flex flex-column align-items-center justify-content-center text-center h-100 p-4">
                                <div class="service-icon">
                                    <i class="<?php echo htmlspecialchars($service['icon'] ?: 'fas fa-cogs'); ?> fa-3x text-primary mb-4"></i>
                                </div>
                                <h4 class="mb-3"><?php echo htmlspecialchars($service['title']); ?></h4>
                                <p class="m-0"><?php echo htmlspecialchars($service['description']); ?></p>
                                <?php if(!empty($service['price'])): ?>
                                    <div class="mt-3">
                                        <span class="h5 text-primary">À partir de <?php echo number_format($service['price'], 0, ',', ' '); ?> FCFA</span>
                                    </div>
                                <?php endif; ?>
                                <div class="mt-3">
                                    <a href="index.php#contact" class="btn btn-primary">Demander un Devis</a>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php else: ?>
                <!-- Message si aucun service -->
                <div class="text-center py-5">
                    <div class="bg-light rounded p-5">
                        <i class="fas fa-tools fa-4x text-muted mb-4"></i>
                        <h4 class="text-muted mb-3">Nos services arrivent bientôt</h4>
                        <p class="text-muted mb-4">Nous préparons actuellement notre catalogue de services complet.</p>
                        <a href="index.php#contact" class="btn btn-primary">
                            <i class="fas fa-envelope me-2"></i>Nous Contacter
                        </a>
                    </div>
                </div>
            <?php endif; ?>
        </div>
    </div>
    <!-- Services End -->

    <!-- Call to Action Start -->
    <div class="container-fluid py-5 wow fadeInUp" data-wow-delay="0.1s">
        <div class="container py-5">
            <div class="bg-primary rounded p-5">
                <div class="row g-4 align-items-center">
                    <div class="col-lg-8">
                        <h1 class="text-white mb-0">Besoin d'une Solution Personnalisée ?</h1>
                        <p class="text-white mb-0">Contactez-nous pour discuter de vos besoins spécifiques en sécurité informatique.</p>
                    </div>
                    <div class="col-lg-4 text-center text-lg-end">
                        <a href="index.php#contact" class="btn btn-outline-light py-3 px-5">Nous Contacter</a>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <!-- Call to Action End -->

    <!-- Footer Start -->
    <div class="container-fluid bg-dark text-light mt-5 wow fadeInUp" data-wow-delay="0.1s">
        <div class="container">
            <div class="row gx-5">
                <div class="col-lg-4 col-md-6 footer-about">
                    <div class="d-flex flex-column align-items-center justify-content-center text-center h-100 bg-primary p-4">
                        <a href="index.php" class="navbar-brand">
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
                                <p class="mb-0">+225 0101012501</p>
                            </div>
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
                                <a class="text-light mb-2" href="index.php"><i class="bi bi-arrow-right text-primary me-2"></i>Accueil</a>
                                <a class="text-light mb-2" href="index.php#about"><i class="bi bi-arrow-right text-primary me-2"></i>À Propos</a>
                                <a class="text-light mb-2" href="services.php"><i class="bi bi-arrow-right text-primary me-2"></i>Nos Services</a>
                                <a class="text-light mb-2" href="equipe.php"><i class="bi bi-arrow-right text-primary me-2"></i>Notre Équipe</a>
                                <a class="text-light mb-2" href="blog.php"><i class="bi bi-arrow-right text-primary me-2"></i>Blog</a>
                                <a class="text-light" href="index.php#contact"><i class="bi bi-arrow-right text-primary me-2"></i>Contact</a>
                            </div>
                        </div>
                        <div class="col-lg-4 col-md-12 pt-0 pt-lg-5 mb-5">
                            <div class="section-title section-title-sm position-relative pb-3 mb-4">
                                <h3 class="text-light mb-0">Liens Populaires</h3>
                            </div>
                            <div class="link-animated d-flex flex-column justify-content-start">
                                <a class="text-light mb-2" href="tarification.php"><i class="bi bi-arrow-right text-primary me-2"></i>Tarification</a>
                                <a class="text-light mb-2" href="galerie.php"><i class="bi bi-arrow-right text-primary me-2"></i>Galerie</a>
                                <a class="text-light mb-2" href="index.php#quote"><i class="bi bi-arrow-right text-primary me-2"></i>Devis Gratuit</a>
                                <a class="text-light" href="index.php#contact"><i class="bi bi-arrow-right text-primary me-2"></i>Support</a>
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
                        <p class="mb-0">&copy; <a class="text-white border-bottom" href="#">Universe Security</a>. Tous droits réservés.</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <!-- Footer End -->

    <!-- Back to Top -->
    <a href="#" class="btn btn-lg btn-primary btn-lg-square rounded back-to-top"><i class="bi bi-arrow-up"></i></a>

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
</body>

</html>
