<?php
/**
 * Navbar d'administration pour Universe Security
 * Fichier réutilisable pour toutes les pages admin
 * 
 * Variables attendues :
 * - $current_page : nom de la page actuelle (ex: 'dashboard', 'offers', 'team', etc.)
 */

// Définir la page actuelle si non définie
if (!isset($current_page)) {
    $current_page = '';
}
?>

<!-- Mobile Toggle Button -->
<button class="mobile-toggle btn" onclick="toggleSidebar()">
    <i class="fas fa-bars"></i>
</button>

<!-- Sidebar -->
<div class="sidebar" id="sidebar">
    <div class="sidebar-header text-center">
        <img src="../img/logo universe security.jpg" alt="Universe Security" class="logo mb-2">
        <h5 class="mb-0">Universe Security</h5>
        <small>Admin Panel</small>
    </div>
    
    <ul class="sidebar-menu">
        <li>
            <a href="dashboard.php" <?php echo ($current_page === 'dashboard') ? 'class="active"' : ''; ?>>
                <i class="fas fa-tachometer-alt me-2"></i>Tableau de Bord
            </a>
        </li>
        <li>
            <a href="quotes.php" <?php echo ($current_page === 'quotes') ? 'class="active"' : ''; ?>>
                <i class="fas fa-file-invoice me-2"></i>Demandes de Devis
            </a>
        </li>
        <li>
            <a href="services.php" <?php echo ($current_page === 'services') ? 'class="active"' : ''; ?>>
                <i class="fas fa-cogs me-2"></i>Services
            </a>
        </li>
        <li>
            <a href="offers.php" <?php echo ($current_page === 'offers') ? 'class="active"' : ''; ?>>
                <i class="fas fa-box me-2"></i>Offres
            </a>
        </li>
        <li>
            <a href="team.php" <?php echo ($current_page === 'team') ? 'class="active"' : ''; ?>>
                <i class="fas fa-users me-2"></i>Équipe
            </a>
        </li>
        <li>
            <a href="blog.php" <?php echo ($current_page === 'blog') ? 'class="active"' : ''; ?>>
                <i class="fas fa-newspaper me-2"></i>Blog
            </a>
        </li>
        <li>
            <a href="medias.php" <?php echo ($current_page === 'medias') ? 'class="active"' : ''; ?>>
                <i class="fas fa-photo-video me-2"></i>Médias
            </a>
        </li>
        <li>
            <a href="testimonials.php" <?php echo ($current_page === 'testimonials') ? 'class="active"' : ''; ?>>
                <i class="fas fa-comments me-2"></i>Témoignages
            </a>
        </li>
        <li>
            <a href="contact_messages.php" <?php echo ($current_page === 'contact_messages') ? 'class="active"' : ''; ?>>
                <i class="fas fa-envelope me-2"></i>Messages Contact
            </a>
        </li>
        <li>
            <a href="about_content.php" <?php echo ($current_page === 'about_content') ? 'class="active"' : ''; ?>>
                <i class="fas fa-info-circle me-2"></i>À Propos
            </a>
        </li>
        <li>
            <a href="vendors.php" <?php echo ($current_page === 'vendors') ? 'class="active"' : ''; ?>>
                <i class="fas fa-handshake me-2"></i>Vendors/Partenaires
            </a>
        </li>
        <li>
            <a href="analytics.php" <?php echo ($current_page === 'analytics') ? 'class="active"' : ''; ?>>
                <i class="fas fa-chart-bar me-2"></i>Statistiques
            </a>
        </li>
        <li>
            <a href="settings.php" <?php echo ($current_page === 'settings') ? 'class="active"' : ''; ?>>
                <i class="fas fa-cog me-2"></i>Paramètres
            </a>
        </li>
        <?php 
        // Démarrer la session si pas déjà fait
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        
        // Afficher le lien Utilisateurs seulement pour Super Admin et Admin
        $admin_role = $_SESSION['admin_role'] ?? '';
        if($admin_role === 'Super Admin' || $admin_role === 'Admin' || 
           $admin_role === 'super_admin' || $admin_role === 'admin'): 
        ?>
        <li>
            <a href="users.php" <?php echo ($current_page === 'users') ? 'class="active"' : ''; ?>>
                <i class="fas fa-users-cog me-2"></i>Utilisateurs
            </a>
        </li>
        <?php endif; ?>
        
        <li>
            <a href="profile.php" <?php echo ($current_page === 'profile') ? 'class="active"' : ''; ?>>
                <i class="fas fa-user me-2"></i>Profil
            </a>
        </li>
        <li>
            <a href="logout.php">
                <i class="fas fa-sign-out-alt me-2"></i>Déconnexion
            </a>
        </li>
    </ul>
</div>
