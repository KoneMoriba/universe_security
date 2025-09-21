<?php
class ImageUploader {
    private $allowedTypes = ['image/jpeg', 'image/jpg', 'image/png', 'image/gif', 'image/webp'];
    private $maxFileSize = 5242880; // 5MB
    private $uploadPath;
    
    public function __construct($uploadPath = '../uploads/') {
        $this->uploadPath = rtrim($uploadPath, '/') . '/';
        
        // Créer le dossier s'il n'existe pas
        if (!is_dir($this->uploadPath)) {
            mkdir($this->uploadPath, 0755, true);
        }
    }
    
    /**
     * Upload une image
     * @param array $file - $_FILES['field_name']
     * @param string $subfolder - Sous-dossier (offers, team, blog)
     * @param string $prefix - Préfixe pour le nom du fichier
     * @return array - ['success' => bool, 'message' => string, 'filename' => string|null]
     */
    public function uploadImage($file, $subfolder = '', $prefix = '') {
        try {
            // Vérifications de base
            if (!isset($file['tmp_name']) || empty($file['tmp_name'])) {
                return ['success' => false, 'message' => 'Aucun fichier sélectionné', 'filename' => null];
            }
            
            if ($file['error'] !== UPLOAD_ERR_OK) {
                return ['success' => false, 'message' => 'Erreur lors de l\'upload: ' . $this->getUploadErrorMessage($file['error']), 'filename' => null];
            }
            
            // Vérifier la taille
            if ($file['size'] > $this->maxFileSize) {
                return ['success' => false, 'message' => 'Le fichier est trop volumineux (max 5MB)', 'filename' => null];
            }
            
            // Vérifier le type MIME
            $finfo = finfo_open(FILEINFO_MIME_TYPE);
            $mimeType = finfo_file($finfo, $file['tmp_name']);
            finfo_close($finfo);
            
            if (!in_array($mimeType, $this->allowedTypes)) {
                return ['success' => false, 'message' => 'Type de fichier non autorisé. Utilisez JPG, PNG, GIF ou WebP', 'filename' => null];
            }
            
            // Vérifier que c'est vraiment une image
            $imageInfo = getimagesize($file['tmp_name']);
            if ($imageInfo === false) {
                return ['success' => false, 'message' => 'Le fichier n\'est pas une image valide', 'filename' => null];
            }
            
            // Créer le sous-dossier si nécessaire
            $targetDir = $this->uploadPath;
            if (!empty($subfolder)) {
                $targetDir .= $subfolder . '/';
                if (!is_dir($targetDir)) {
                    mkdir($targetDir, 0755, true);
                }
            }
            
            // Générer un nom de fichier unique
            $extension = $this->getExtensionFromMimeType($mimeType);
            $filename = $this->generateUniqueFilename($prefix, $extension);
            $targetPath = $targetDir . $filename;
            
            // Déplacer le fichier
            if (move_uploaded_file($file['tmp_name'], $targetPath)) {
                // Optimiser l'image
                $this->optimizeImage($targetPath, $mimeType);
                
                // Retourner le chemin relatif depuis la racine du site
                $relativePath = 'uploads/';
                if (!empty($subfolder)) {
                    $relativePath .= $subfolder . '/';
                }
                $relativePath .= $filename;
                
                return [
                    'success' => true, 
                    'message' => 'Image uploadée avec succès', 
                    'filename' => $relativePath
                ];
            } else {
                return ['success' => false, 'message' => 'Erreur lors de la sauvegarde du fichier', 'filename' => null];
            }
            
        } catch (Exception $e) {
            error_log("Erreur upload image: " . $e->getMessage());
            return ['success' => false, 'message' => 'Erreur interne lors de l\'upload', 'filename' => null];
        }
    }
    
    /**
     * Supprimer une image
     * @param string $filename - Nom du fichier à supprimer
     * @return bool
     */
    public function deleteImage($filename) {
        if (empty($filename)) {
            return false;
        }
        
        // Construire le chemin complet
        $fullPath = '../' . $filename;
        
        if (file_exists($fullPath)) {
            return unlink($fullPath);
        }
        
        return false;
    }
    
    /**
     * Générer un nom de fichier unique
     */
    private function generateUniqueFilename($prefix = '', $extension = 'jpg') {
        $timestamp = time();
        $random = mt_rand(1000, 9999);
        $prefix = !empty($prefix) ? $prefix . '_' : '';
        
        return $prefix . $timestamp . '_' . $random . '.' . $extension;
    }
    
    /**
     * Obtenir l'extension depuis le type MIME
     */
    private function getExtensionFromMimeType($mimeType) {
        $extensions = [
            'image/jpeg' => 'jpg',
            'image/jpg' => 'jpg',
            'image/png' => 'png',
            'image/gif' => 'gif',
            'image/webp' => 'webp'
        ];
        
        return $extensions[$mimeType] ?? 'jpg';
    }
    
    /**
     * Optimiser l'image (redimensionner si trop grande)
     */
    private function optimizeImage($imagePath, $mimeType) {
        $maxWidth = 1200;
        $maxHeight = 800;
        $quality = 85;
        
        $imageInfo = getimagesize($imagePath);
        $width = $imageInfo[0];
        $height = $imageInfo[1];
        
        // Si l'image est déjà petite, ne rien faire
        if ($width <= $maxWidth && $height <= $maxHeight) {
            return;
        }
        
        // Calculer les nouvelles dimensions
        $ratio = min($maxWidth / $width, $maxHeight / $height);
        $newWidth = intval($width * $ratio);
        $newHeight = intval($height * $ratio);
        
        // Créer l'image source
        switch ($mimeType) {
            case 'image/jpeg':
            case 'image/jpg':
                $sourceImage = imagecreatefromjpeg($imagePath);
                break;
            case 'image/png':
                $sourceImage = imagecreatefrompng($imagePath);
                break;
            case 'image/gif':
                $sourceImage = imagecreatefromgif($imagePath);
                break;
            case 'image/webp':
                $sourceImage = imagecreatefromwebp($imagePath);
                break;
            default:
                return;
        }
        
        if (!$sourceImage) {
            return;
        }
        
        // Créer l'image redimensionnée
        $resizedImage = imagecreatetruecolor($newWidth, $newHeight);
        
        // Préserver la transparence pour PNG et GIF
        if ($mimeType === 'image/png' || $mimeType === 'image/gif') {
            imagealphablending($resizedImage, false);
            imagesavealpha($resizedImage, true);
            $transparent = imagecolorallocatealpha($resizedImage, 255, 255, 255, 127);
            imagefill($resizedImage, 0, 0, $transparent);
        }
        
        // Redimensionner
        imagecopyresampled($resizedImage, $sourceImage, 0, 0, 0, 0, $newWidth, $newHeight, $width, $height);
        
        // Sauvegarder
        switch ($mimeType) {
            case 'image/jpeg':
            case 'image/jpg':
                imagejpeg($resizedImage, $imagePath, $quality);
                break;
            case 'image/png':
                imagepng($resizedImage, $imagePath, 9);
                break;
            case 'image/gif':
                imagegif($resizedImage, $imagePath);
                break;
            case 'image/webp':
                imagewebp($resizedImage, $imagePath, $quality);
                break;
        }
        
        // Libérer la mémoire
        imagedestroy($sourceImage);
        imagedestroy($resizedImage);
    }
    
    /**
     * Obtenir le message d'erreur d'upload
     */
    private function getUploadErrorMessage($errorCode) {
        switch ($errorCode) {
            case UPLOAD_ERR_INI_SIZE:
                return 'Le fichier dépasse la taille maximale autorisée par le serveur';
            case UPLOAD_ERR_FORM_SIZE:
                return 'Le fichier dépasse la taille maximale autorisée par le formulaire';
            case UPLOAD_ERR_PARTIAL:
                return 'Le fichier n\'a été que partiellement uploadé';
            case UPLOAD_ERR_NO_FILE:
                return 'Aucun fichier n\'a été uploadé';
            case UPLOAD_ERR_NO_TMP_DIR:
                return 'Dossier temporaire manquant';
            case UPLOAD_ERR_CANT_WRITE:
                return 'Impossible d\'écrire le fichier sur le disque';
            case UPLOAD_ERR_EXTENSION:
                return 'Upload arrêté par une extension PHP';
            default:
                return 'Erreur inconnue';
        }
    }
    
    /**
     * Valider une image existante
     */
    public function validateExistingImage($imagePath) {
        if (empty($imagePath)) {
            return false;
        }
        
        $fullPath = '../' . $imagePath;
        return file_exists($fullPath) && is_file($fullPath);
    }
    
    /**
     * Obtenir les informations d'une image
     */
    public function getImageInfo($imagePath) {
        if (!$this->validateExistingImage($imagePath)) {
            return null;
        }
        
        $fullPath = '../' . $imagePath;
        $imageInfo = getimagesize($fullPath);
        
        if ($imageInfo === false) {
            return null;
        }
        
        return [
            'width' => $imageInfo[0],
            'height' => $imageInfo[1],
            'type' => $imageInfo['mime'],
            'size' => filesize($fullPath)
        ];
    }
}
?>
