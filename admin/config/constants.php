<?php
/**
 * Constantes de configuration
 * Universe Security Admin Panel
 */

// Informations de l'application
define('APP_NAME', 'Universe Security Admin');
define('APP_VERSION', '1.0.0');
define('APP_AUTHOR', 'Universe Security Team');

// URLs
define('BASE_URL', 'http://localhost/universe-security/');
define('ADMIN_BASE_URL', 'http://localhost/universe-security/admin/');

// Chemins
define('ROOT_PATH', dirname(dirname(__DIR__)) . '/');
define('ADMIN_PATH', __DIR__ . '/../');
define('UPLOADS_PATH', ROOT_PATH . 'uploads/');
define('LOGS_PATH', ADMIN_PATH . 'logs/');

// Limites
define('MAX_UPLOAD_SIZE', 5 * 1024 * 1024); // 5MB
define('MAX_LOGIN_ATTEMPTS', 5);
define('SESSION_TIMEOUT', 3600); // 1 heure
define('PAGINATION_LIMIT', 20);

// Formats de date
define('DATE_FORMAT', 'd/m/Y');
define('DATETIME_FORMAT', 'd/m/Y H:i');
define('TIME_FORMAT', 'H:i');

// Types de fichiers autorisés
define('ALLOWED_IMAGE_TYPES', ['jpg', 'jpeg', 'png', 'gif', 'webp']);
define('ALLOWED_DOCUMENT_TYPES', ['pdf', 'doc', 'docx', 'xls', 'xlsx']);

// Configuration email
define('SMTP_HOST', 'localhost');
define('SMTP_PORT', 587);
define('SMTP_USERNAME', '');
define('SMTP_PASSWORD', '');
define('FROM_EMAIL', 'noreply@universesecurity.com');
define('FROM_NAME', 'Universe Security');

// Statuts des devis
define('QUOTE_STATUS_NEW', 'nouveau');
define('QUOTE_STATUS_PROCESSING', 'en_cours');
define('QUOTE_STATUS_COMPLETED', 'traite');
define('QUOTE_STATUS_REJECTED', 'refuse');

// Priorités des devis
define('QUOTE_PRIORITY_LOW', 'basse');
define('QUOTE_PRIORITY_NORMAL', 'normale');
define('QUOTE_PRIORITY_HIGH', 'haute');
define('QUOTE_PRIORITY_URGENT', 'urgente');

// Rôles utilisateur
define('ROLE_MODERATOR', 'moderator');
define('ROLE_ADMIN', 'admin');
define('ROLE_SUPER_ADMIN', 'super_admin');

// Messages par défaut
define('MSG_SUCCESS_SAVE', 'Données sauvegardées avec succès.');
define('MSG_SUCCESS_DELETE', 'Élément supprimé avec succès.');
define('MSG_SUCCESS_UPDATE', 'Mise à jour effectuée avec succès.');
define('MSG_ERROR_SAVE', 'Erreur lors de la sauvegarde.');
define('MSG_ERROR_DELETE', 'Erreur lors de la suppression.');
define('MSG_ERROR_UPDATE', 'Erreur lors de la mise à jour.');
define('MSG_ERROR_ACCESS', 'Accès non autorisé.');
define('MSG_ERROR_NOT_FOUND', 'Élément non trouvé.');

// Configuration de sécurité
define('BCRYPT_ROUNDS', 12);
define('CSRF_TOKEN_LENGTH', 32);
define('PASSWORD_MIN_LENGTH', 6);

// Configuration des logs
define('LOG_LEVEL_ERROR', 'ERROR');
define('LOG_LEVEL_WARNING', 'WARNING');
define('LOG_LEVEL_INFO', 'INFO');
define('LOG_LEVEL_DEBUG', 'DEBUG');

// Devises supportées
define('SUPPORTED_CURRENCIES', ['XOF', 'EUR', 'USD', 'GBP']);
define('DEFAULT_CURRENCY', 'XOF');

// Configuration des graphiques
define('CHART_COLORS', [
    'primary' => '#667eea',
    'secondary' => '#764ba2',
    'success' => '#28a745',
    'info' => '#17a2b8',
    'warning' => '#ffc107',
    'danger' => '#dc3545'
]);

// Configuration des notifications
define('NOTIFICATION_TYPES', [
    'success' => 'success',
    'error' => 'danger',
    'warning' => 'warning',
    'info' => 'info'
]);

// Timezone par défaut
define('DEFAULT_TIMEZONE', 'Africa/Abidjan');

// Configuration de cache (si implémenté)
define('CACHE_ENABLED', false);
define('CACHE_TTL', 3600); // 1 heure

// Mode debug
define('DEBUG_MODE', true); // À désactiver en production
define('SHOW_ERRORS', DEBUG_MODE);

// Configuration des backups
define('BACKUP_PATH', ADMIN_PATH . 'backups/');
define('BACKUP_RETENTION_DAYS', 30);

// Langues supportées (pour évolution future)
define('SUPPORTED_LANGUAGES', ['fr']);
define('DEFAULT_LANGUAGE', 'fr');
?>
