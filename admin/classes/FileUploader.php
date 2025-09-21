<?php
/**
 * Gestionnaire d'upload de fichiers
 * Universe Security Admin Panel
 */

class FileUploader {
    private $upload_path;
    private $allowed_types;
    private $max_size;
    
    public function __construct() {
        $this->upload_path = '../uploads/';
        $this->allowed_types = ['jpg', 'jpeg', 'png', 'gif', 'webp'];
        $this->allowed_video_types = ['mp4', 'avi', 'mov', 'wmv', 'flv', 'webm'];
        $this->max_size = 5 * 1024 * 1024; // 5MB pour images
        $this->max_video_size = 50 * 1024 * 1024; // 50MB pour vidéos
        
        // Créer le dossier d'upload s'il n'existe pas
        if (!file_exists($this->upload_path)) {
            mkdir($this->upload_path, 0755, true);
        }
    }
    
    /**
     * Uploader une image
     */
    public function uploadImage($file, $subfolder = '') {
        if (!isset($file) || $file['error'] !== UPLOAD_ERR_OK) {
            return ['success' => false, 'message' => 'Aucun fichier sélectionné ou erreur d\'upload.'];
        }
        
        // Vérifier la taille
        if ($file['size'] > $this->max_size) {
            return ['success' => false, 'message' => 'Le fichier est trop volumineux (max 5MB).'];
        }
        
        // Vérifier le type
        $file_extension = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
        if (!in_array($file_extension, $this->allowed_types)) {
            return ['success' => false, 'message' => 'Type de fichier non autorisé. Formats acceptés: ' . implode(', ', $this->allowed_types)];
        }
        
        // Vérifier que c'est vraiment une image
        $image_info = getimagesize($file['tmp_name']);
        if ($image_info === false) {
            return ['success' => false, 'message' => 'Le fichier n\'est pas une image valide.'];
        }
        
        // Vérifier si l'extension GD est disponible
        $gd_available = extension_loaded('gd');
        
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
            // Redimensionner l'image si nécessaire et si GD est disponible
            if ($gd_available) {
                $this->resizeImage($target_file, $image_info[2]);
            }
            
            // Retourner le chemin relatif
            $relative_path = 'uploads/';
            if ($subfolder) {
                $relative_path .= $subfolder . '/';
            }
            $relative_path .= $filename;
            
            return [
                'success' => true, 
                'message' => 'Image uploadée avec succès.',
                'filename' => $filename,
                'path' => $relative_path,
                'full_path' => $target_file
            ];
        } else {
            return ['success' => false, 'message' => 'Erreur lors de l\'upload du fichier.'];
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
        $filename = $base_name . '_' . $timestamp . '.' . $file_extension;
        
        // Vérifier l'unicité
        $counter = 1;
        while (file_exists($target_dir . $filename)) {
            $filename = $base_name . '_' . $timestamp . '_' . $counter . '.' . $file_extension;
            $counter++;
        }
        
        return $filename;
    }
    
    /**
     * Redimensionner une image si elle est trop grande
     */
    private function resizeImage($file_path, $image_type) {
        // Vérifier si l'extension GD est disponible
        if (!extension_loaded('gd')) {
            return false;
        }
        
        $max_width = 1200;
        $max_height = 800;
        
        list($width, $height) = getimagesize($file_path);
        
        // Si l'image est déjà assez petite, ne rien faire
        if ($width <= $max_width && $height <= $max_height) {
            return;
        }
        
        // Calculer les nouvelles dimensions
        $ratio = min($max_width / $width, $max_height / $height);
        $new_width = round($width * $ratio);
        $new_height = round($height * $ratio);
        
        // Créer une nouvelle image
        $new_image = imagecreatetruecolor($new_width, $new_height);
        
        // Préserver la transparence pour PNG et GIF
        if ($image_type == IMAGETYPE_PNG || $image_type == IMAGETYPE_GIF) {
            imagealphablending($new_image, false);
            imagesavealpha($new_image, true);
            $transparent = imagecolorallocatealpha($new_image, 255, 255, 255, 127);
            imagefill($new_image, 0, 0, $transparent);
        }
        
        // Charger l'image source
        switch ($image_type) {
            case IMAGETYPE_JPEG:
                $source = imagecreatefromjpeg($file_path);
                break;
            case IMAGETYPE_PNG:
                $source = imagecreatefrompng($file_path);
                break;
            case IMAGETYPE_GIF:
                $source = imagecreatefromgif($file_path);
                break;
            case IMAGETYPE_WEBP:
                $source = imagecreatefromwebp($file_path);
                break;
            default:
                return; // Type non supporté
        }
        
        // Redimensionner
        imagecopyresampled($new_image, $source, 0, 0, 0, 0, $new_width, $new_height, $width, $height);
        
        // Sauvegarder
        switch ($image_type) {
            case IMAGETYPE_JPEG:
                imagejpeg($new_image, $file_path, 85);
                break;
            case IMAGETYPE_PNG:
                imagepng($new_image, $file_path, 8);
                break;
            case IMAGETYPE_GIF:
                imagegif($new_image, $file_path);
                break;
            case IMAGETYPE_WEBP:
                imagewebp($new_image, $file_path, 85);
                break;
        }
        
        // Libérer la mémoire
        imagedestroy($new_image);
        imagedestroy($source);
    }
    
    /**
     * Supprimer un fichier uploadé
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
                'type' => mime_content_type($full_path)
            ];
        }
        return ['exists' => false];
    }
    
    /**
     * Uploader une vidéo
     */
    public function uploadVideo($file, $subfolder = '') {
        if (!isset($file) || $file['error'] !== UPLOAD_ERR_OK) {
            return ['success' => false, 'message' => 'Aucun fichier sélectionné ou erreur d\'upload.'];
        }
        
        // Vérifier la taille
        if ($file['size'] > $this->max_video_size) {
            return ['success' => false, 'message' => 'Le fichier vidéo est trop volumineux (max 50MB).'];
        }
        
        // Vérifier le type
        $file_extension = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
        if (!in_array($file_extension, $this->allowed_video_types)) {
            return ['success' => false, 'message' => 'Type de fichier vidéo non autorisé. Formats acceptés: ' . implode(', ', $this->allowed_video_types)];
        }
        
        // Vérifier le type MIME
        $mime_type = mime_content_type($file['tmp_name']);
        $allowed_mimes = ['video/mp4', 'video/avi', 'video/quicktime', 'video/x-msvideo', 'video/x-flv', 'video/webm'];
        if (!in_array($mime_type, $allowed_mimes)) {
            return ['success' => false, 'message' => 'Le fichier n\'est pas une vidéo valide.'];
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
                'message' => 'Vidéo uploadée avec succès.',
                'filename' => $filename,
                'path' => $relative_path,
                'full_path' => $target_file
            ];
        } else {
            return ['success' => false, 'message' => 'Erreur lors de l\'upload du fichier vidéo.'];
        }
    }
}
?>
