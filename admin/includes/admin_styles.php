<?php
/**
 * Styles CSS communs pour l'administration Universe Security
 * Fichier réutilisable pour toutes les pages admin
 */
?>

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
        padding: 0;
        overflow-y: auto;
    }
    
    .sidebar-header {
        padding: 20px;
        border-bottom: 1px solid rgba(255,255,255,0.1);
    }
    
    .logo {
        width: 60px;
        height: 60px;
        object-fit: cover;
        border-radius: 50%;
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
    
    .mobile-toggle {
        position: fixed;
        top: 15px;
        left: 15px;
        z-index: 1001;
        background: var(--primary-color);
        color: white;
        border: none;
        border-radius: 5px;
        padding: 10px;
        display: none;
    }
    
    /* Responsive Design */
    @media (max-width: 768px) {
        .mobile-toggle {
            display: block;
        }
        
        .sidebar {
            transform: translateX(-100%);
        }
        
        .sidebar.show {
            transform: translateX(0);
        }
        
        .main-content {
            margin-left: 0;
        }
    }
</style>
