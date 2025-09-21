<?php
// Connexion à la base de données pour récupérer l'équipe
try {
    require_once 'admin/config/database.php';
    require_once 'admin/classes/TeamManager.php';
    
    $database = new Database();
    $conn = $database->getConnection();
    $teamManager = new TeamManager($conn);
    
    // Récupérer tous les membres actifs de l'équipe
    $team_members = $teamManager->getActiveMembers();
    
} catch(Exception $e) {
    $team_members = [];
    error_log("Erreur récupération équipe: " . $e->getMessage());
}
?>

<!DOCTYPE html>
<html lang="fr">

<head>
    <meta charset="utf-8">
    <title>Notre Équipe - Universe Security</title>
    <meta content="width=device-width, initial-scale=1.0" name="viewport">
    <meta content="" name="keywords">
    <meta content="" name="description">

    <!-- Favicon -->
    <link href="img/logo universe security.jpg" rel="icon">

    <!-- Google Web Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Heebo:wght@400;500;600&family=Nunito:wght@600;700;800&display=swap" rel="stylesheet">

    <!-- Icon Font Stylesheet -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.10.0/css/all.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.4.1/font/bootstrap-icons.css" rel="stylesheet">

    <!-- Libraries Stylesheet -->
    <link href="lib/animate/animate.min.css" rel="stylesheet">
    <link href="lib/owlcarousel/assets/owl.carousel.min.css" rel="stylesheet">

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

    <!-- Navbar Start -->
    <div class="container-fluid position-relative p-0">
        <nav class="navbar navbar-expand-lg navbar-dark px-5 py-3 py-lg-0">
            <a href="index.php" class="navbar-brand p-0">
                <img src="img/logo universe security.png" alt="Logo" style="height: 100px; width: 150px;">
            </a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarCollapse">
                <span class="fa fa-bars"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarCollapse">
                <div class="navbar-nav ms-auto py-0">
                    <a href="index.php" class="nav-item nav-link">Accueil</a>
                    <!-- Bouton de basculement de thème -->
                    <button id="theme-toggle" class="btn btn-outline-light ms-3" title="Basculer le thème">
                        <i class="fas fa-moon" id="theme-icon"></i>
                    </button>
                </div>
            </div>
        </nav>

        <!-- Header Start -->
        <div class="container-fluid bg-primary py-5 mb-5 page-header">
            <div class="container py-5">
                <div class="row justify-content-center">
                    <div class="col-lg-10 text-center">
                        <h1 class="display-3 text-white animated slideInDown">Notre Équipe</h1>
                        <nav aria-label="breadcrumb">
                            <ol class="breadcrumb justify-content-center">
                                <li class="breadcrumb-item"><a class="text-white" href="index.php">Accueil</a></li>
                                <li class="breadcrumb-item text-white active" aria-current="page">Équipe</li>
                            </ol>
                        </nav>
                    </div>
                </div>
            </div>
        </div>
        <!-- Header End -->
    </div>
    <!-- Navbar End -->

    <!-- Team Start -->
    <div class="container-xxl py-5">
        <div class="container">
            <div class="text-center wow fadeInUp" data-wow-delay="0.1s">
                <h6 class="section-title bg-white text-center text-primary px-3">Notre Équipe</h6>
                <h1 class="mb-5">Des Professionnels Expérimentés</h1>
            </div>
            
            <div class="row g-4">
                <?php if (!empty($team_members)): ?>
                    <?php foreach ($team_members as $index => $member): ?>
                        <div class="col-lg-4 col-md-6 wow fadeInUp" data-wow-delay="<?php echo 0.1 + ($index * 0.2); ?>s">
                            <div class="team-item h-100">
                                <div class="team-img position-relative overflow-hidden">
                                    <?php if (!empty($member['image'])): ?>
                                        <img class="img-fluid" src="<?php echo htmlspecialchars($member['image']); ?>" alt="<?php echo htmlspecialchars($member['name']); ?>">
                                    <?php else: ?>
                                        <div class="bg-primary d-flex align-items-center justify-content-center" style="height: 300px;">
                                            <i class="fa fa-user fa-5x text-white"></i>
                                        </div>
                                    <?php endif; ?>
                                    <div class="team-social">
                                        <?php if (!empty($member['social_facebook'])): ?>
                                            <a class="btn btn-square btn-primary" href="<?php echo htmlspecialchars($member['social_facebook']); ?>" target="_blank">
                                                <i class="fab fa-facebook-f"></i>
                                            </a>
                                        <?php endif; ?>
                                        <?php if (!empty($member['social_twitter'])): ?>
                                            <a class="btn btn-square btn-primary" href="<?php echo htmlspecialchars($member['social_twitter']); ?>" target="_blank">
                                                <i class="fab fa-twitter"></i>
                                            </a>
                                        <?php endif; ?>
                                        <?php if (!empty($member['social_linkedin'])): ?>
                                            <a class="btn btn-square btn-primary" href="<?php echo htmlspecialchars($member['social_linkedin']); ?>" target="_blank">
                                                <i class="fab fa-linkedin-in"></i>
                                            </a>
                                        <?php endif; ?>
                                        <?php if (!empty($member['social_instagram'])): ?>
                                            <a class="btn btn-square btn-primary" href="<?php echo htmlspecialchars($member['social_instagram']); ?>" target="_blank">
                                                <i class="fab fa-instagram"></i>
                                            </a>
                                        <?php endif; ?>
                                    </div>
                                </div>
                                <div class="team-content text-center p-4">
                                    <h5 class="mb-1"><?php echo htmlspecialchars($member['name']); ?></h5>
                                    <small class="text-primary"><?php echo htmlspecialchars($member['position']); ?></small>
                                    <?php if (!empty($member['bio'])): ?>
                                        <p class="mt-3 text-muted"><?php echo htmlspecialchars($member['bio']); ?></p>
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
                <?php else: ?>
                    <!-- Message si aucun membre d'équipe -->
                    <div class="col-12 text-center py-5">
                        <div class="bg-light rounded p-5">
                            <i class="fas fa-users fa-4x text-muted mb-4"></i>
                            <h4 class="text-muted mb-3">Notre équipe se présente bientôt</h4>
                            <p class="text-muted mb-4">Nous constituons actuellement notre équipe de professionnels expérimentés.</p>
                            <a href="index.php#contact" class="btn btn-primary">
                                <i class="fas fa-envelope me-2"></i>Nous Contacter
                            </a>
                        </div>
                    </div>
                <?php endif; ?>
            </div>
            
            <!-- Section Rejoindre l'équipe -->
            <div class="row mt-5">
                <div class="col-12 text-center">
                    <div class="bg-light rounded p-5">
                        <h3 class="mb-3">Rejoignez Notre Équipe</h3>
                        <p class="mb-4">Nous recherchons constamment des professionnels talentueux pour renforcer notre équipe</p>
                        <a href="index.php#contact" class="btn btn-primary py-3 px-5">
                            <i class="fas fa-user-plus me-2"></i>Postuler
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <!-- Team End -->

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
                                <a class="text-light mb-2" href="#services"><i class="bi bi-arrow-right text-primary me-2"></i>Nos Services</a>
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
						Designed by<a class="text-white border-bottom" href="#">universe-security</a></p>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <!-- Footer End -->


    <!-- Back to Top -->
    <a href="#" class="btn btn-lg btn-primary btn-lg-square back-to-top"><i class="bi bi-arrow-up"></i></a>

    <!-- JavaScript Libraries -->
    <script src="https://code.jquery.com/jquery-3.4.1.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.0.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="lib/wow/wow.min.js"></script>
    <script src="lib/easing/easing.min.js"></script>
    <script src="lib/waypoints/waypoints.min.js"></script>
    <script src="lib/owlcarousel/owl.carousel.min.js"></script>

    <!-- Template Javascript -->
    <script src="js/main.js"></script>
    
    <!-- Theme Toggle Script -->
    <script>
        // Gestion du thème sombre/clair
        const themeToggle = document.getElementById('theme-toggle');
        const themeIcon = document.getElementById('theme-icon');
        const body = document.body;

        // Charger le thème sauvegardé
        const savedTheme = localStorage.getItem('theme') || 'light';
        body.setAttribute('data-theme', savedTheme);
        updateThemeIcon(savedTheme);

        themeToggle.addEventListener('click', () => {
            const currentTheme = body.getAttribute('data-theme');
            const newTheme = currentTheme === 'dark' ? 'light' : 'dark';
            
            body.setAttribute('data-theme', newTheme);
            localStorage.setItem('theme', newTheme);
            updateThemeIcon(newTheme);
        });

        function updateThemeIcon(theme) {
            themeIcon.className = theme === 'dark' ? 'fas fa-sun' : 'fas fa-moon';
        }
    </script>
</body>

</html>
