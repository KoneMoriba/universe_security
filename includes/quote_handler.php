<?php
/**
 * Gestionnaire du formulaire de devis
 * Universe Security
 */

if($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['submit_quote'])) {
    require_once __DIR__ . '/../admin/classes/QuoteManager.php';
    
    try {
        $quoteManager = new QuoteManager();
        
        // Validation des données
        $name = trim($_POST['name'] ?? '');
        $email = trim($_POST['email'] ?? '');
        $phone = trim($_POST['phone'] ?? '');
        $service = $_POST['service'] ?? '';
        $message = trim($_POST['message'] ?? '');
        
        $errors = [];
        
        if(empty($name)) {
            $errors[] = 'Le nom est requis.';
        }
        
        if(empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $errors[] = 'Un email valide est requis.';
        }
        
        if(empty($service)) {
            $errors[] = 'Veuillez sélectionner un service.';
        }
        
        if(empty($message)) {
            $errors[] = 'Le message est requis.';
        }
        
        if(empty($errors)) {
            // Préparer les données
            $quote_data = [
                'name' => $name,
                'email' => $email,
                'phone' => $phone,
                'service' => $service,
                'message' => $message,
                'status' => 'nouveau',
                'priority' => 'normale'
            ];
            
            // Enregistrer le devis
            if($quoteManager->createQuote($quote_data)) {
                $quote_success = 'Votre demande de devis a été envoyée avec succès. Nous vous contacterons dans les plus brefs délais.';
                
                // Optionnel: Envoyer un email de notification à l'admin
                $admin_email = 'admin@universesecurity.com';
                $subject = 'Nouvelle demande de devis - Universe Security';
                $admin_message = "Nouvelle demande de devis reçue:\n\n";
                $admin_message .= "Nom: $name\n";
                $admin_message .= "Email: $email\n";
                $admin_message .= "Téléphone: $phone\n";
                $admin_message .= "Service: $service\n";
                $admin_message .= "Message: $message\n";
                
                @mail($admin_email, $subject, $admin_message);
                
            } else {
                $quote_error = 'Une erreur est survenue lors de l\'envoi de votre demande. Veuillez réessayer.';
            }
        } else {
            $quote_error = implode('<br>', $errors);
        }
        
    } catch(Exception $e) {
        $quote_error = 'Une erreur technique est survenue. Veuillez réessayer plus tard.';
        error_log('Erreur quote: ' . $e->getMessage());
    }
}
?>
