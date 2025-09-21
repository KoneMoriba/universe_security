<?php
/**
 * Système de tracking des visites
 * Universe Security
 */

// Inclure le gestionnaire d'analytics seulement si on n'est pas dans l'admin
if(!strpos($_SERVER['REQUEST_URI'], '/admin/')) {
    require_once __DIR__ . '/../admin/classes/AnalyticsManager.php';
    
    try {
        $analytics = new AnalyticsManager();
        
        // Enregistrer la visite
        $page_url = $_SERVER['REQUEST_URI'] ?? '/';
        $referrer = $_SERVER['HTTP_REFERER'] ?? null;
        
        $analytics->recordVisit($page_url, $referrer);
    } catch(Exception $e) {
        // En cas d'erreur, ne pas interrompre le site
        error_log('Erreur tracking: ' . $e->getMessage());
    }
}
?>
