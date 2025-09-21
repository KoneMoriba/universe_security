<?php
/**
 * Gestionnaire d'upload de fichiers simplifié (sans GD)
 * Universe Security Admin Panel
 */

class SimpleFileUploader {
    private $upload_path;
    private $allowed_image_types;
    private $allowed_video_types;
    private $max_image_size;
    private $max_video_size;
    
    public function __construct() {
        $this->upload_path = '../uploads/';
        $this->allowed_image_types = ['jpg', 'jpeg', 'png', 'gif', 'webp'];
        $this->allowed_video_types = ['mp4', 'avi', 'mov', 'wmv', 'flv', 'webm'];
        $this->max_image_size = 10 * 1024 * 1024; // 10MB pour images
        $this->max_video_size = 100 * 1024 * 1024; // 100MB pour vidéos
        
        // Créer le dossier d'upload s'il n'existe pas
        if (!file_exists($this->upload_path)) {
            mkdir($this->upload_path, 0755, true);
        }
    }
    
    /**
     * Uploader une image (version simplifiée)
     */
    public function uploadImage($file, $subfolder = '') {
        return $this->uploadFile($file, $subfolder, 'image');
    }
    
    /**
     * Uploader une vidéo (version simplifiée)
     */
    public function uploadVideo($file, $subfolder = '') {
        return $this->uploadFile($file, $subfolder, 'video');
    }
    
    /**
     * Méthode générique d'upload
     */
    private function uploadFile($file, $subfolder, $type) {
        if (!isset($file) || $file['error'] !== UPLOAD_ERR_OK) {
            return ['success' => false, 'message' => 'Aucun fichier sélectionné ou erreur d\'upload.'];
        }
        
        // Déterminer les paramètres selon le type
        if ($type === 'image') {
            $allowed_types = $this->allowed_image_types;
            $max_size = $this->max_image_size;
            $type_name = 'image';
        } else {
            $allowed_types = $this->allowed_video_types;
            $max_size = $this->max_video_size;
            $type_name = 'vidéo';
        }
        
        // Vérifier la taille
        if ($file['size'] > $max_size) {
            $max_mb = round($max_size / (1024 * 1024));
            return ['success' => false, 'message' => "Le fichier $type_name est trop volumineux (max {$max_mb}MB)."];
        }
        
        // Vérifier l'extension
        $file_extension = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
        if (!in_array($file_extension, $allowed_types)) {
            return ['success' => false, 'message' => "Type de fichier $type_name non autorisé. Formats acceptés: " . implode(', ', $allowed_types)];
        }
        
        // Vérification MIME basique (si fileinfo est disponible)
        if (function_exists('mime_content_type')) {
            $mime_type = mime_content_type($file['tmp_name']);
            
            if ($type === 'image') {
                $allowed_mimes = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];
            } else {
                $allowed_mimes = ['video/mp4', 'video/avi', 'video/quicktime', 'video/x-msvideo', 'video/x-flv', 'video/webm'];
            }
            
            if (!in_array($mime_type, $allowed_mimes)) {
                return ['success' => false, 'message' => "Le fichier n'est pas un $type_name valide."];
            }
        }
        
        // Créer le sous-dossier si nécessaire
        $target_dir = $this->upload_path;
        if ($subfolder) {
            $target_dir .= $subfolder . '/';
            if (!file_exists($target_dir)) {
                mkdir($target_dir, 0755, true);
            }
        }
        
        // Générer un nom unique
        $filename = $this->generateUniqueFilename($file['name'], $target_dir);
        $target_file = $target_dir . $filename;
        
        // Déplacer le fichier
        if (move_uploaded_file($file['tmp_name'], $target_file)) {
            // Retourner le chemin relatif
            $relative_path = 'uploads/';
            if ($subfolder) {
                $relative_path .= $subfolder . '/';
            }
            $relative_path .= $filename;
            
            return [
                'success' => true, 
                'message' => ucfirst($type_name) . ' uploadée avec succès.',
                'filename' => $filename,
                'path' => $relative_path,
                'full_path' => $target_file,
                'size' => $file['size'],
                'type' => $file_extension
            ];
        } else {
            return ['success' => false, 'message' => "Erreur lors de l'upload du fichier $type_name."];
        }
    }
    
    /**
     * Générer un nom de fichier unique
     */
    private function generateUniqueFilename($original_name, $target_dir) {
        $file_extension = strtolower(pathinfo($original_name, PATHINFO_EXTENSION));
        $base_name = pathinfo($original_name, PATHINFO_FILENAME);
        
        // Nettoyer le nom de base
        $base_name = preg_replace('/[^a-zA-Z0-9_-]/', '_', $base_name);
        $base_name = substr($base_name, 0, 50); // Limiter la longueur
        
        // Ajouter timestamp pour unicité
        $timestamp = time();
        $random = mt_rand(1000, 9999);
        $filename = $base_name . '_' . $timestamp . '_' . $random . '.' . $file_extension;
        
        // Vérifier l'unicité
        $counter = 1;
        while (file_exists($target_dir . $filename)) {
            $filename = $base_name . '_' . $timestamp . '_' . $random . '_' . $counter . '.' . $file_extension;
            $counter++;
        }
        
        return $filename;
    }
    
    /**
     * Supprimer un fichier
     */
    public function deleteFile($file_path) {
        $full_path = '../' . $file_path;
        if (file_exists($full_path)) {
            return unlink($full_path);
        }
        return false;
    }
    
    /**
     * Obtenir les informations d'un fichier
     */
    public function getFileInfo($file_path) {
        $full_path = '../' . $file_path;
        if (file_exists($full_path)) {
            return [
                'exists' => true,
                'size' => filesize($full_path),
                'modified' => filemtime($full_path),
                'type' => function_exists('mime_content_type') ? mime_content_type($full_path) : 'unknown'
            ];
        }
        return ['exists' => false];
    }
    
    /**
     * Vérifier les prérequis système
     */
    public static function checkRequirements() {
        $requirements = [
            'upload_enabled' => ini_get('file_uploads'),
            'max_upload_size' => ini_get('upload_max_filesize'),
            'max_post_size' => ini_get('post_max_size'),
            'gd_available' => extension_loaded('gd'),
            'fileinfo_available' => extension_loaded('fileinfo')
        ];
        
        return $requirements;
    }
}
?>
