<?php
// Récupération dynamique des articles de blog depuis la base de données
try {
    require_once 'admin/config/database.php';
    require_once 'admin/classes/BlogManager.php';
    
    $database = new Database();
    $conn = $database->getConnection();
    $blogManager = new BlogManager($conn);
    
    // Vérifier s'il s'agit d'un article spécifique
    $current_article = null;
    if (isset($_GET['article']) && !empty($_GET['article'])) {
        $slug = trim($_GET['article']);
        $current_article = $blogManager->getArticleBySlug($slug);
        
        // Incrémenter le nombre de vues si l'article existe
        if ($current_article) {
            $blogManager->incrementViews($current_article['id']);
        }
    }
    
    // Récupérer tous les articles publiés pour la liste
    $blog_articles = $blogManager->getPublishedArticles();
    
} catch(Exception $e) {
    $blog_articles = [];
    $current_article = null;
    error_log("Erreur récupération blog: " . $e->getMessage());
}
?>

<!DOCTYPE html>
<html lang="fr">

<head>
    <meta charset="utf-8">
    <title>Blog Cybersécurité et Actualités IT - Universe Security Côte d'Ivoire</title>
    <meta content="width=device-width, initial-scale=1.0" name="viewport">
    
    <!-- Favicon -->
    <link href="img/logo universe security.jpg" rel="icon">
    <meta content="blog cybersécurité, actualités sécurité informatique, conseils IT, tendances cybersécurité, Côte d'Ivoire, articles sécurité" name="keywords">
    <meta content="Blog spécialisé en cybersécurité et sécurité informatique. Actualités, conseils et tendances IT en Côte d'Ivoire par les experts d'Universe Security." name="description">
    
    <!-- Open Graph -->
    <meta property="og:type" content="website">
    <meta property="og:url" content="https://universe-security.ci/blog.php">
    <meta property="og:title" content="Blog Cybersécurité - Universe Security">
    <meta property="og:description" content="Blog spécialisé en cybersécurité et sécurité informatique. Actualités, conseils et tendances IT.">
    <meta property="og:image" content="https://universe-security.ci/img/logo universe security.jpg">

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
                        <h1 class="display-3 text-white animated slideInDown">Notre Blog</h1>
                        <nav aria-label="breadcrumb">
                            <ol class="breadcrumb justify-content-center">
                                <li class="breadcrumb-item"><a class="text-white" href="index.php">Accueil</a></li>
                                <li class="breadcrumb-item text-white active" aria-current="page">Blog</li>
                            </ol>
                        </nav>
                    </div>
                </div>
            </div>
        </div>
        <!-- Header End -->
    </div>
    <!-- Navbar End -->

    <!-- Blog Start -->
    <div class="container-xxl py-5">
        <div class="container">
            <div class="text-center wow fadeInUp" data-wow-delay="0.1s">
                <h6 class="section-title bg-white text-center text-primary px-3">Blog</h6>
                <?php if ($current_article): ?>
                    <h1 class="mb-5">Article</h1>
                <?php else: ?>
                    <h1 class="mb-5">Actualités et Conseils Sécurité</h1>
                <?php endif; ?>
            </div>
            
            <?php if ($current_article): ?>
                <!-- Affichage de l'article individuel -->
                <div class="row">
                    <div class="col-lg-8 mx-auto">
                        <article class="blog-single">
                            <div class="blog-single-header mb-4">
                                <?php if (!empty($current_article['featured_image'])): ?>
                                    <img class="img-fluid rounded mb-4" src="<?php echo htmlspecialchars($current_article['featured_image']); ?>" alt="<?php echo htmlspecialchars($current_article['title']); ?>">
                                <?php endif; ?>
                                
                                <div class="blog-meta mb-3">
                                    <span class="text-primary"><i class="fas fa-calendar me-2"></i>
                                        <?php if ($current_article['published_at']): ?>
                                            <?php $date = new DateTime($current_article['published_at']); ?>
                                            <?php echo $date->format('d F Y'); ?>
                                        <?php endif; ?>
                                    </span>
                                    <span class="text-muted ms-4"><i class="fas fa-user me-2"></i><?php echo htmlspecialchars($current_article['author'] ?? 'Universe Security'); ?></span>
                                    <?php if (!empty($current_article['category'])): ?>
                                        <span class="text-muted ms-4"><i class="fas fa-tag me-2"></i><?php echo htmlspecialchars($current_article['category']); ?></span>
                                    <?php endif; ?>
                                    <?php if (isset($current_article['views_count'])): ?>
                                        <span class="text-muted ms-4"><i class="fas fa-eye me-2"></i><?php echo $current_article['views_count']; ?> vues</span>
                                    <?php endif; ?>
                                </div>
                                
                                <h1 class="mb-4"><?php echo htmlspecialchars($current_article['title']); ?></h1>
                            </div>
                            
                            <div class="blog-single-content">
                                <?php echo $current_article['content']; ?>
                            </div>
                            
                            <div class="blog-single-footer mt-5 pt-4 border-top">
                                <div class="row align-items-center">
                                    <div class="col-md-6">
                                        <a href="blog.php" class="btn btn-outline-primary">
                                            <i class="fas fa-arrow-left me-2"></i>Retour aux Articles
                                        </a>
                                    </div>
                                    <div class="col-md-6 text-end">
                                        <div class="blog-share">
                                            <span class="me-3">Partager :</span>
                                            <a href="#" class="btn btn-sm btn-outline-primary me-2"><i class="fab fa-facebook-f"></i></a>
                                            <a href="#" class="btn btn-sm btn-outline-info me-2"><i class="fab fa-twitter"></i></a>
                                            <a href="#" class="btn btn-sm btn-outline-success"><i class="fab fa-whatsapp"></i></a>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </article>
                    </div>
                </div>
            <?php elseif (!empty($blog_articles)): ?>
                <div class="row g-4">
                    <?php foreach ($blog_articles as $index => $article): ?>
                        <div class="col-lg-4 col-md-6 wow fadeInUp" data-wow-delay="<?php echo 0.1 + ($index * 0.2); ?>s">
                            <div class="blog-item h-100">
                                <div class="blog-img position-relative overflow-hidden">
                                    <?php if (!empty($article['featured_image'])): ?>
                                        <img class="img-fluid" src="<?php echo htmlspecialchars($article['featured_image']); ?>" alt="<?php echo htmlspecialchars($article['title']); ?>">
                                    <?php else: ?>
                                        <div class="bg-primary d-flex align-items-center justify-content-center" style="height: 250px;">
                                            <i class="fas fa-newspaper fa-4x text-white"></i>
                                        </div>
                                    <?php endif; ?>
                                    <div class="blog-date">
                                        <?php if ($article['published_at']): ?>
                                            <?php $date = new DateTime($article['published_at']); ?>
                                            <span><?php echo $date->format('d'); ?></span>
                                            <small><?php echo $date->format('M'); ?></small>
                                        <?php else: ?>
                                            <span><?php echo date('d'); ?></span>
                                            <small><?php echo date('M'); ?></small>
                                        <?php endif; ?>
                                    </div>
                                </div>
                                <div class="blog-content p-4">
                                    <div class="blog-meta mb-3">
                                        <small class="text-primary"><i class="fas fa-user me-1"></i><?php echo htmlspecialchars($article['author'] ?? 'Universe Security'); ?></small>
                                        <?php if (!empty($article['category'])): ?>
                                            <small class="text-muted ms-3"><i class="fas fa-tag me-1"></i><?php echo htmlspecialchars($article['category']); ?></small>
                                        <?php endif; ?>
                                        <?php if (isset($article['views_count'])): ?>
                                            <small class="text-muted ms-3"><i class="fas fa-eye me-1"></i><?php echo $article['views_count']; ?> vues</small>
                                        <?php endif; ?>
                                    </div>
                                    <h5 class="mb-3"><?php echo htmlspecialchars($article['title']); ?></h5>
                                    <p class="text-muted"><?php echo htmlspecialchars(substr(strip_tags($article['content']), 0, 120)); ?>...</p>
                                    <a href="blog.php?article=<?php echo htmlspecialchars($article['slug']); ?>" class="btn btn-primary btn-sm">
                                        Lire Plus <i class="fas fa-arrow-right ms-1"></i>
                                    </a>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php else: ?>
                <!-- Message si aucun article publié -->
                <div class="text-center py-5">
                    <div class="bg-light rounded p-5">
                        <i class="fas fa-newspaper fa-5x text-muted mb-4"></i>
                        <h3 class="text-muted mb-3">Aucun article publié</h3>
                        <p class="text-muted mb-4">Les articles de blog apparaîtront ici une fois publiés par notre équipe.</p>
                        <a href="index.php" class="btn btn-primary me-3">
                            <i class="fas fa-home me-2"></i>Retour à l'Accueil
                        </a>
                        <a href="index.php#contact" class="btn btn-outline-primary">
                            <i class="fas fa-envelope me-2"></i>Nous Contacter
                        </a>
                    </div>
                </div>
            <?php endif; ?>
            
            
            <!-- Pagination -->
            <div class="row mt-5">
                <div class="col-12">
                    <nav aria-label="Blog pagination">
                        <ul class="pagination justify-content-center">
                            <li class="page-item disabled">
                                <a class="page-link" href="#" tabindex="-1">Précédent</a>
                            </li>
                            <li class="page-item active"><a class="page-link" href="#">1</a></li>
                            <li class="page-item"><a class="page-link" href="#">2</a></li>
                            <li class="page-item"><a class="page-link" href="#">3</a></li>
                            <li class="page-item">
                                <a class="page-link" href="#">Suivant</a>
                            </li>
                        </ul>
                    </nav>
                </div>
            </div>
            
            <!-- Newsletter -->
            <div class="row mt-5">
                <div class="col-12 text-center">
                    <div class="bg-light rounded p-5">
                        <h3 class="mb-3">Restez Informé</h3>
                        <p class="mb-4">Abonnez-vous à notre newsletter pour recevoir nos derniers articles et conseils sécurité</p>
                        <div class="position-relative mx-auto" style="max-width: 400px;">
                            <input class="form-control border-0 w-100 py-3 ps-4 pe-5" type="email" placeholder="Votre adresse email">
                            <button type="button" class="btn btn-primary py-2 position-absolute top-0 end-0 mt-2 me-2">
                                <i class="fas fa-paper-plane me-1"></i>S'abonner
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <!-- Blog End -->

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
