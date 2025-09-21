<?php
/**
 * Scripts JavaScript communs pour l'administration Universe Security
 * Fichier réutilisable pour toutes les pages admin
 */
?>

<script>
    /**
     * Fonction pour basculer l'affichage de la sidebar sur mobile
     */
    function toggleSidebar() {
        document.getElementById('sidebar').classList.toggle('show');
    }
    
    /**
     * Fermer la sidebar en cliquant en dehors sur mobile
     */
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
    
    /**
     * Gestion du redimensionnement de la fenêtre
     */
    window.addEventListener('resize', function() {
        const sidebar = document.getElementById('sidebar');
        if (window.innerWidth > 768) {
            sidebar.classList.remove('show');
        }
    });
    
    /**
     * Initialisation au chargement de la page
     */
    document.addEventListener('DOMContentLoaded', function() {
        // Ajouter des animations aux éléments de la sidebar
        const menuItems = document.querySelectorAll('.sidebar-menu a');
        menuItems.forEach(function(item, index) {
            item.style.animationDelay = (index * 0.1) + 's';
        });
        
        // Auto-hide des alertes après 5 secondes
        setTimeout(function() {
            const alerts = document.querySelectorAll('.alert');
            alerts.forEach(function(alert) {
                if (alert.classList.contains('show')) {
                    const bsAlert = bootstrap.Alert.getOrCreateInstance(alert);
                    if (bsAlert) {
                        bsAlert.close();
                    }
                }
            });
        }, 5000);
    });
</script>
