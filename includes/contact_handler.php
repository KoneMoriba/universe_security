<?php
/**
 * Gestionnaire des messages de contact
 * Universe Security - Site Principal
 */

require_once '../admin/config/database.php';
require_once '../admin/classes/ContactManager.php';

// Démarrer la session pour les messages flash
session_start();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        // Validation des données
        $name = trim($_POST['name'] ?? '');
        $email = trim($_POST['email'] ?? '');
        $subject = trim($_POST['subject'] ?? '');
        $message = trim($_POST['message'] ?? '');
        $phone = trim($_POST['phone'] ?? '');
        $company = trim($_POST['company'] ?? '');
        
        // Validation basique
        if (empty($name) || empty($email) || empty($subject) || empty($message)) {
            $_SESSION['contact_error'] = 'Tous les champs obligatoires doivent être remplis.';
            header('Location: ../index.php#contact');
            exit();
        }
        
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $_SESSION['contact_error'] = 'L\'adresse email n\'est pas valide.';
            header('Location: ../index.php#contact');
            exit();
        }
        
        // Connexion à la base de données
        $database = new Database();
        $conn = $database->getConnection();
        $contactManager = new ContactManager($conn);
        
        // Préparer les données
        $data = [
            'name' => $name,
            'email' => $email,
            'subject' => $subject,
            'message' => $message,
            'phone' => !empty($phone) ? $phone : null,
            'company' => !empty($company) ? $company : null
        ];
        
        // Enregistrer le message
        if ($contactManager->createMessage($data)) {
            $_SESSION['contact_success'] = 'Votre message a été envoyé avec succès ! Nous vous répondrons dans les plus brefs délais.';
            
            // Log de l'activité
            error_log("Nouveau message de contact reçu de: " . $email . " - Sujet: " . $subject);
            
        } else {
            $_SESSION['contact_error'] = 'Une erreur est survenue lors de l\'envoi de votre message. Veuillez réessayer.';
        }
        
    } catch (Exception $e) {
        error_log("Erreur lors du traitement du message de contact: " . $e->getMessage());
        $_SESSION['contact_error'] = 'Une erreur technique est survenue. Veuillez réessayer plus tard.';
    }
} else {
    $_SESSION['contact_error'] = 'Méthode de requête non autorisée.';
}

// Redirection vers la page de contact
header('Location: ../index.php#contact');
exit();
?>
