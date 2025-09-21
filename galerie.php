<?php
// Connexion à la base de données
require_once 'admin/config/database.php';

try {
    $database = new Database();
    $conn = $database->getConnection();
    
    // Récupérer les photos actives
    $query_photos = "SELECT * FROM medias WHERE is_active = 1 AND media_type = 'photo' ORDER BY display_order ASC, created_at DESC";
    $stmt_photos = $conn->prepare($query_photos);
    $stmt_photos->execute();
    $photos = $stmt_photos->fetchAll(PDO::FETCH_ASSOC);
    
    // Récupérer les vidéos actives
    $query_videos = "SELECT * FROM medias WHERE is_active = 1 AND media_type = 'video' ORDER BY display_order ASC, created_at DESC";
    $stmt_videos = $conn->prepare($query_videos);
    $stmt_videos->execute();
    $videos = $stmt_videos->fetchAll(PDO::FETCH_ASSOC);
    
} catch(Exception $e) {
    error_log("Erreur récupération médias: " . $e->getMessage());
    $photos = [];
    $videos = [];
}
?>

<!DOCTYPE html>
<html lang="fr">

<head>
    <meta charset="utf-8">
    <title>Galerie - Universe Security</title>
    <meta content="width=device-width, initial-scale=1.0" name="viewport">
    <meta content="Galerie photos et vidéos Universe Security" name="keywords">
    <meta content="Découvrez notre galerie complète de photos et vidéos présentant nos services et réalisations" name="description">

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
                    <small class="me-3 text-light"><i class="fa fa-map-marker-alt me-2"></i>Cocody Angré, Abidjan, Côte d'Ivoire</small>
                    <small class="me-3 text-light"><i class="fa fa-phone-alt me-2"></i>+225 0101012501</small>
                    <small class="text-light"><i class="fa fa-envelope-open me-2"></i>info@universesecurity.ci</small>
                </div>
            </div>
            <div class="col-lg-4 text-center text-lg-end">
                <div class="d-inline-flex align-items-center" style="height: 45px;">
                    <a class="btn btn-sm btn-outline-light btn-sm-square rounded-circle me-2" href=""><i class="fab fa-twitter fw-normal"></i></a>
                    <a class="btn btn-sm btn-outline-light btn-sm-square rounded-circle me-2" href=""><i class="fab fa-facebook-f fw-normal"></i></a>
                    <a class="btn btn-sm btn-outline-light btn-sm-square rounded-circle me-2" href=""><i class="fab fa-linkedin-in fw-normal"></i></a>
                    <a class="btn btn-sm btn-outline-light btn-sm-square rounded-circle me-2" href=""><i class="fab fa-instagram fw-normal"></i></a>
                    <a class="btn btn-sm btn-outline-light btn-sm-square rounded-circle" href=""><i class="fab fa-youtube fw-normal"></i></a>
                </div>
            </div>
        </div>
    </div>
    <!-- Topbar End -->


    <!-- Navbar & Carousel Start -->
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
                        <h1 class="display-3 text-white animated slideInDown">Galerie Complète</h1>
                        <nav aria-label="breadcrumb">
                            <ol class="breadcrumb justify-content-center">
                                <li class="breadcrumb-item"><a class="text-white" href="index.php">Accueil</a></li>
                                <li class="breadcrumb-item text-white active" aria-current="page">Galerie</li>
                            </ol>
                        </nav>
                    </div>
                </div>
            </div>
        </div>
        <!-- Header End -->
    </div>
    <!-- Navbar & Carousel End -->


    <!-- Gallery Start -->
    <div class="container-fluid py-5 wow fadeInUp" data-wow-delay="0.1s">
        <div class="container py-5">
            <!-- Navigation Tabs -->
            <div class="text-center mb-5">
                <ul class="nav nav-pills justify-content-center" id="galleryTabs" role="tablist">
                    <li class="nav-item" role="presentation">
                        <button class="nav-link active" id="all-tab" data-bs-toggle="pill" data-bs-target="#all" type="button" role="tab">
                            <i class="fas fa-th me-2"></i>Tout Afficher
                        </button>
                    </li>
                    <li class="nav-item" role="presentation">
                        <button class="nav-link" id="photos-tab" data-bs-toggle="pill" data-bs-target="#photos-content" type="button" role="tab">
                            <i class="fas fa-image me-2"></i>Photos (<?php echo count($photos); ?>)
                        </button>
                    </li>
                    <li class="nav-item" role="presentation">
                        <button class="nav-link" id="videos-tab" data-bs-toggle="pill" data-bs-target="#videos-content" type="button" role="tab">
                            <i class="fas fa-video me-2"></i>Vidéos (<?php echo count($videos); ?>)
                        </button>
                    </li>
                </ul>
            </div>

            <!-- Tab Content -->
            <div class="tab-content" id="galleryTabContent">
                <!-- Tout Afficher -->
                <div class="tab-pane fade show active" id="all" role="tabpanel">
                    <div class="row g-4">
                        <!-- Photos Section -->
                        <?php if (!empty($photos)): ?>
                            <div class="col-12">
                                <h3 class="text-primary mb-4"><i class="fas fa-image me-2"></i>Photos</h3>
                            </div>
                            <?php foreach($photos as $index => $photo): ?>
                                <div class="col-lg-4 col-md-6 wow zoomIn" data-wow-delay="<?php echo ($index * 0.1 + 0.1); ?>s">
                                    <div class="photo-grid-item">
                                        <img class="photo-grid-image" 
                                             src="<?php echo htmlspecialchars($photo['file_path']); ?>" 
                                             alt="<?php echo htmlspecialchars($photo['alt_text'] ?? $photo['title']); ?>"
                                             loading="lazy"
                                             data-bs-toggle="modal" 
                                             data-bs-target="#mediaModal"
                                             data-media-src="<?php echo htmlspecialchars($photo['file_path']); ?>"
                                             data-media-title="<?php echo htmlspecialchars($photo['title']); ?>"
                                             data-media-description="<?php echo htmlspecialchars($photo['description']); ?>"
                                             data-media-type="photo"
                                             style="cursor: pointer;"
                                             onerror="this.style.display='none'; this.nextElementSibling.style.display='flex';">
                                        
                                        <div class="photo-grid-fallback" style="display: none;">
                                            <i class="fas fa-image"></i>
                                            <h4><?php echo htmlspecialchars($photo['title']); ?></h4>
                                            <p>Image non disponible</p>
                                        </div>
                                        
                                        <div class="photo-grid-content">
                                            <h5><?php echo htmlspecialchars($photo['title']); ?></h5>
                                            <?php if (!empty($photo['description'])): ?>
                                                <p><?php echo htmlspecialchars(substr($photo['description'], 0, 80)); ?><?php if(strlen($photo['description']) > 80): ?>...<?php endif; ?></p>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        <?php endif; ?>

                        <!-- Videos Section -->
                        <?php if (!empty($videos)): ?>
                            <div class="col-12 mt-5">
                                <h3 class="text-primary mb-4"><i class="fas fa-video me-2"></i>Vidéos</h3>
                            </div>
                            <?php foreach($videos as $index => $video): ?>
                                <div class="col-lg-4 col-md-6 wow zoomIn" data-wow-delay="<?php echo ($index * 0.1 + 0.1); ?>s">
                                    <div class="photo-grid-item">
                                        <div class="video-container">
                                            <video class="photo-grid-image" 
                                                   controls 
                                                   preload="metadata"
                                                   poster="img/video-placeholder.jpg">
                                                <source src="<?php echo htmlspecialchars($video['file_path']); ?>" type="video/mp4">
                                                Votre navigateur ne supporte pas la lecture vidéo.
                                            </video>
                                            <div class="video-overlay">
                                                <i class="fas fa-play-circle fa-3x text-white"></i>
                                            </div>
                                        </div>
                                        
                                        <div class="photo-grid-content">
                                            <h5><?php echo htmlspecialchars($video['title']); ?></h5>
                                            <?php if (!empty($video['description'])): ?>
                                                <p><?php echo htmlspecialchars(substr($video['description'], 0, 80)); ?><?php if(strlen($video['description']) > 80): ?>...<?php endif; ?></p>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        <?php endif; ?>

                        <?php if (empty($photos) && empty($videos)): ?>
                            <div class="col-12 text-center">
                                <div class="alert alert-info">
                                    <i class="fas fa-info-circle me-2"></i>
                                    Aucun média disponible pour le moment. Revenez bientôt pour découvrir nos dernières réalisations !
                                </div>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>

                <!-- Photos Only -->
                <div class="tab-pane fade" id="photos-content" role="tabpanel">
                    <div class="row g-4">
                        <?php if (!empty($photos)): ?>
                            <?php foreach($photos as $index => $photo): ?>
                                <div class="col-lg-4 col-md-6 wow zoomIn" data-wow-delay="<?php echo ($index * 0.1 + 0.1); ?>s">
                                    <div class="photo-grid-item">
                                        <img class="photo-grid-image" 
                                             src="<?php echo htmlspecialchars($photo['file_path']); ?>" 
                                             alt="<?php echo htmlspecialchars($photo['alt_text'] ?? $photo['title']); ?>"
                                             loading="lazy"
                                             data-bs-toggle="modal" 
                                             data-bs-target="#mediaModal"
                                             data-media-src="<?php echo htmlspecialchars($photo['file_path']); ?>"
                                             data-media-title="<?php echo htmlspecialchars($photo['title']); ?>"
                                             data-media-description="<?php echo htmlspecialchars($photo['description']); ?>"
                                             data-media-type="photo"
                                             style="cursor: pointer;"
                                             onerror="this.style.display='none'; this.nextElementSibling.style.display='flex';">
                                        
                                        <div class="photo-grid-fallback" style="display: none;">
                                            <i class="fas fa-image"></i>
                                            <h4><?php echo htmlspecialchars($photo['title']); ?></h4>
                                            <p>Image non disponible</p>
                                        </div>
                                        
                                        <div class="photo-grid-content">
                                            <h5><?php echo htmlspecialchars($photo['title']); ?></h5>
                                            <?php if (!empty($photo['description'])): ?>
                                                <p><?php echo htmlspecialchars(substr($photo['description'], 0, 80)); ?><?php if(strlen($photo['description']) > 80): ?>...<?php endif; ?></p>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <div class="col-12 text-center">
                                <div class="alert alert-info">
                                    <i class="fas fa-camera me-2"></i>
                                    Aucune photo disponible pour le moment.
                                </div>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>

                <!-- Videos Only -->
                <div class="tab-pane fade" id="videos-content" role="tabpanel">
                    <div class="row g-4">
                        <?php if (!empty($videos)): ?>
                            <?php foreach($videos as $index => $video): ?>
                                <div class="col-lg-4 col-md-6 wow zoomIn" data-wow-delay="<?php echo ($index * 0.1 + 0.1); ?>s">
                                    <div class="photo-grid-item">
                                        <div class="video-container">
                                            <video class="photo-grid-image" 
                                                   controls 
                                                   preload="metadata"
                                                   poster="img/video-placeholder.jpg">
                                                <source src="<?php echo htmlspecialchars($video['file_path']); ?>" type="video/mp4">
                                                Votre navigateur ne supporte pas la lecture vidéo.
                                            </video>
                                            <div class="video-overlay">
                                                <i class="fas fa-play-circle fa-3x text-white"></i>
                                            </div>
                                        </div>
                                        
                                        <div class="photo-grid-content">
                                            <h5><?php echo htmlspecialchars($video['title']); ?></h5>
                                            <?php if (!empty($video['description'])): ?>
                                                <p><?php echo htmlspecialchars(substr($video['description'], 0, 80)); ?><?php if(strlen($video['description']) > 80): ?>...<?php endif; ?></p>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <div class="col-12 text-center">
                                <div class="alert alert-info">
                                    <i class="fas fa-video me-2"></i>
                                    Aucune vidéo disponible pour le moment.
                                </div>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <!-- Gallery End -->


    <!-- Modal for Media Preview -->
    <div class="modal fade" id="mediaModal" tabindex="-1" aria-labelledby="mediaModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="mediaModalLabel">Aperçu Média</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body text-center">
                    <img id="modalImage" class="img-fluid" style="display: none;" alt="">
                    <video id="modalVideo" class="img-fluid" controls style="display: none;">
                        <source id="modalVideoSource" src="" type="video/mp4">
                    </video>
                    <div id="modalDescription" class="mt-3"></div>
                </div>
            </div>
        </div>
    </div>


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

    <!-- Gallery Modal Script -->
    <script>
        // Modal pour prévisualiser les médias
        $('#mediaModal').on('show.bs.modal', function (event) {
            var button = $(event.relatedTarget);
            var mediaSrc = button.data('media-src');
            var mediaTitle = button.data('media-title');
            var mediaDescription = button.data('media-description');
            var mediaType = button.data('media-type');
            
            var modal = $(this);
            modal.find('.modal-title').text(mediaTitle);
            modal.find('#modalDescription').html('<h6>' + mediaTitle + '</h6><p>' + mediaDescription + '</p>');
            
            if (mediaType === 'photo') {
                modal.find('#modalImage').attr('src', mediaSrc).show();
                modal.find('#modalVideo').hide();
            } else {
                modal.find('#modalVideoSource').attr('src', mediaSrc);
                modal.find('#modalVideo')[0].load();
                modal.find('#modalVideo').show();
                modal.find('#modalImage').hide();
            }
        });

        // Réinitialiser le modal à la fermeture
        $('#mediaModal').on('hidden.bs.modal', function () {
            $(this).find('#modalVideo')[0].pause();
            $(this).find('#modalVideo')[0].currentTime = 0;
        });
    </script>
</body>

</html>
