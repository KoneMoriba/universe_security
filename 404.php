<?php
http_response_code(404);
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="utf-8">
    <title>Page non trouvée - Universe Security</title>
    <meta content="width=device-width, initial-scale=1.0" name="viewport">
    <meta content="Page non trouvée, erreur 404, Universe Security" name="keywords">
    <meta content="La page que vous recherchez n'existe pas ou a été déplacée. Retournez à l'accueil d'Universe Security." name="description">
    
    <!-- Favicon -->
    <link href="img/logo universe security.jpg" rel="icon">
    
    <!-- Bootstrap CSS -->
    <link href="css/bootstrap.min.css" rel="stylesheet">
    <!-- Font Awesome -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.10.0/css/all.min.css" rel="stylesheet">
    <!-- Custom CSS -->
    <link href="css/style.css" rel="stylesheet">
</head>

<body>
    <div class="container-fluid bg-primary py-5">
        <div class="container text-center py-5">
            <div class="row justify-content-center">
                <div class="col-lg-6">
                    <i class="fas fa-exclamation-triangle fa-5x text-white mb-4"></i>
                    <h1 class="display-1 text-white">404</h1>
                    <h2 class="text-white mb-4">Page Non Trouvée</h2>
                    <p class="text-white mb-4">
                        Désolé, la page que vous recherchez n'existe pas ou a été déplacée.
                    </p>
                    <div class="d-flex justify-content-center gap-3">
                        <a href="index.php" class="btn btn-light btn-lg">
                            <i class="fas fa-home me-2"></i>Retour à l'Accueil
                        </a>
                        <a href="services.php" class="btn btn-outline-light btn-lg">
                            <i class="fas fa-cogs me-2"></i>Nos Services
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Suggestions -->
    <div class="container py-5">
        <div class="row">
            <div class="col-12 text-center mb-5">
                <h3>Vous pourriez être intéressé par :</h3>
            </div>
            <div class="col-lg-4 col-md-6 mb-4">
                <div class="card h-100">
                    <div class="card-body text-center">
                        <i class="fas fa-shield-alt fa-3x text-primary mb-3"></i>
                        <h5 class="card-title">Nos Services</h5>
                        <p class="card-text">Découvrez notre gamme complète de services de cybersécurité.</p>
                        <a href="services.php" class="btn btn-primary">Voir les Services</a>
                    </div>
                </div>
            </div>
            <div class="col-lg-4 col-md-6 mb-4">
                <div class="card h-100">
                    <div class="card-body text-center">
                        <i class="fas fa-users fa-3x text-primary mb-3"></i>
                        <h5 class="card-title">Notre Équipe</h5>
                        <p class="card-text">Rencontrez nos experts en sécurité informatique.</p>
                        <a href="equipe.php" class="btn btn-primary">Voir l'Équipe</a>
                    </div>
                </div>
            </div>
            <div class="col-lg-4 col-md-6 mb-4">
                <div class="card h-100">
                    <div class="card-body text-center">
                        <i class="fas fa-envelope fa-3x text-primary mb-3"></i>
                        <h5 class="card-title">Nous Contacter</h5>
                        <p class="card-text">Besoin d'aide ? Contactez-nous directement.</p>
                        <a href="index.php#contact" class="btn btn-primary">Nous Contacter</a>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- JavaScript -->
    <script src="https://code.jquery.com/jquery-3.4.1.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.0.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
