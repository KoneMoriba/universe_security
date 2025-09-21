<?php
/**
 * Gestionnaire des statistiques et analytics
 * Universe Security Admin Panel
 */

require_once __DIR__ . '/../config/database.php';

class AnalyticsManager {
    private $conn;
    private $visits_table = "site_visits";
    
    public function __construct() {
        $database = new Database();
        $this->conn = $database->getConnection();
    }
    
    /**
     * Enregistrer une visite
     */
    public function recordVisit($page_url, $referrer = null) {
        $ip_address = $this->getClientIP();
        $user_agent = $_SERVER['HTTP_USER_AGENT'] ?? '';
        $session_id = session_id();
        
        // Vérifier si cette IP a déjà visité aujourd'hui
        $today = date('Y-m-d');
        $query = "SELECT id FROM " . $this->visits_table . " 
                  WHERE ip_address = :ip AND visit_date = :date AND page_url = :page";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':ip', $ip_address);
        $stmt->bindParam(':date', $today);
        $stmt->bindParam(':page', $page_url);
        $stmt->execute();
        
        // Si pas de visite aujourd'hui pour cette page, l'enregistrer
        if($stmt->rowCount() == 0) {
            $query = "INSERT INTO " . $this->visits_table . " 
                      (ip_address, user_agent, page_url, referrer, visit_date, visit_time, session_id) 
                      VALUES (:ip, :user_agent, :page_url, :referrer, :visit_date, :visit_time, :session_id)";
            
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(':ip', $ip_address);
            $stmt->bindParam(':user_agent', $user_agent);
            $stmt->bindParam(':page_url', $page_url);
            $stmt->bindParam(':referrer', $referrer);
            $stmt->bindValue(':visit_date', $today);
            $stmt->bindValue(':visit_time', date('H:i:s'));
            $stmt->bindParam(':session_id', $session_id);
            
            return $stmt->execute();
        }
        
        return true;
    }
    
    /**
     * Obtenir les statistiques générales
     */
    public function getGeneralStats() {
        $stats = [];
        
        // Visites totales
        $query = "SELECT COUNT(*) as total FROM " . $this->visits_table;
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        $stats['total_visits'] = $stmt->fetch()['total'];
        
        // Visiteurs uniques
        $query = "SELECT COUNT(DISTINCT ip_address) as unique_count FROM " . $this->visits_table;
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        $stats['unique_visitors'] = $stmt->fetch()['unique_count'];
        
        // Visites aujourd'hui
        $query = "SELECT COUNT(*) as today FROM " . $this->visits_table . " 
                  WHERE visit_date = CURDATE()";
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        $stats['visits_today'] = $stmt->fetch()['today'];
        
        // Visites cette semaine
        $query = "SELECT COUNT(*) as week FROM " . $this->visits_table . " 
                  WHERE visit_date >= DATE_SUB(CURDATE(), INTERVAL 7 DAY)";
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        $stats['visits_week'] = $stmt->fetch()['week'];
        
        // Visites ce mois
        $query = "SELECT COUNT(*) as month FROM " . $this->visits_table . " 
                  WHERE MONTH(visit_date) = MONTH(CURDATE()) 
                  AND YEAR(visit_date) = YEAR(CURDATE())";
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        $stats['visits_month'] = $stmt->fetch()['month'];
        
        return $stats;
    }
    
    /**
     * Obtenir les statistiques par jour (30 derniers jours)
     */
    public function getDailyStats($days = 30) {
        $query = "SELECT visit_date, COUNT(*) as visits, COUNT(DISTINCT ip_address) as unique_count 
                  FROM " . $this->visits_table . " 
                  WHERE visit_date >= DATE_SUB(CURDATE(), INTERVAL :days DAY) 
                  GROUP BY visit_date 
                  ORDER BY visit_date ASC";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':days', $days, PDO::PARAM_INT);
        $stmt->execute();
        
        return $stmt->fetchAll();
    }
    
    /**
     * Obtenir les pages les plus visitées
     */
    public function getTopPages($limit = 10) {
        $query = "SELECT page_url, COUNT(*) as visits 
                  FROM " . $this->visits_table . " 
                  GROUP BY page_url 
                  ORDER BY visits DESC 
                  LIMIT :limit";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':limit', $limit, PDO::PARAM_INT);
        $stmt->execute();
        
        return $stmt->fetchAll();
    }
    
    /**
     * Obtenir les référents principaux
     */
    public function getTopReferrers($limit = 10) {
        $query = "SELECT referrer, COUNT(*) as visits 
                  FROM " . $this->visits_table . " 
                  WHERE referrer IS NOT NULL AND referrer != '' 
                  GROUP BY referrer 
                  ORDER BY visits DESC 
                  LIMIT :limit";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':limit', $limit, PDO::PARAM_INT);
        $stmt->execute();
        
        return $stmt->fetchAll();
    }
    
    /**
     * Obtenir les statistiques par heure
     */
    public function getHourlyStats() {
        $query = "SELECT HOUR(visit_time) as hour, COUNT(*) as visits 
                  FROM " . $this->visits_table . " 
                  WHERE visit_date = CURDATE() 
                  GROUP BY HOUR(visit_time) 
                  ORDER BY hour ASC";
        
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        
        $hourly_stats = [];
        $results = $stmt->fetchAll();
        
        // Initialiser toutes les heures à 0
        for($i = 0; $i < 24; $i++) {
            $hourly_stats[$i] = 0;
        }
        
        // Remplir avec les données réelles
        foreach($results as $result) {
            $hourly_stats[$result['hour']] = $result['visits'];
        }
        
        return $hourly_stats;
    }
    
    /**
     * Obtenir les navigateurs les plus utilisés
     */
    public function getBrowserStats() {
        $query = "SELECT user_agent, COUNT(*) as count 
                  FROM " . $this->visits_table . " 
                  GROUP BY user_agent 
                  ORDER BY count DESC 
                  LIMIT 10";
        
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        
        $browsers = [];
        $results = $stmt->fetchAll();
        
        foreach($results as $result) {
            $browser = $this->parseBrowser($result['user_agent']);
            if(isset($browsers[$browser])) {
                $browsers[$browser] += $result['count'];
            } else {
                $browsers[$browser] = $result['count'];
            }
        }
        
        arsort($browsers);
        return array_slice($browsers, 0, 5, true);
    }
    
    /**
     * Nettoyer les anciennes données (plus de 1 an)
     */
    public function cleanOldData() {
        $query = "DELETE FROM " . $this->visits_table . " 
                  WHERE visit_date < DATE_SUB(CURDATE(), INTERVAL 1 YEAR)";
        
        $stmt = $this->conn->prepare($query);
        return $stmt->execute();
    }
    
    /**
     * Obtenir l'adresse IP du client
     */
    private function getClientIP() {
        $ip_keys = ['HTTP_CLIENT_IP', 'HTTP_X_FORWARDED_FOR', 'REMOTE_ADDR'];
        
        foreach($ip_keys as $key) {
            if(array_key_exists($key, $_SERVER) === true) {
                foreach(explode(',', $_SERVER[$key]) as $ip) {
                    $ip = trim($ip);
                    
                    if(filter_var($ip, FILTER_VALIDATE_IP, 
                       FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE) !== false) {
                        return $ip;
                    }
                }
            }
        }
        
        return $_SERVER['REMOTE_ADDR'] ?? '0.0.0.0';
    }
    
    /**
     * Parser le navigateur depuis le User-Agent
     */
    private function parseBrowser($user_agent) {
        if(strpos($user_agent, 'Chrome') !== false) {
            return 'Chrome';
        } elseif(strpos($user_agent, 'Firefox') !== false) {
            return 'Firefox';
        } elseif(strpos($user_agent, 'Safari') !== false) {
            return 'Safari';
        } elseif(strpos($user_agent, 'Edge') !== false) {
            return 'Edge';
        } elseif(strpos($user_agent, 'Opera') !== false) {
            return 'Opera';
        } else {
            return 'Autre';
        }
    }
    
    /**
     * Obtenir les statistiques des devis par période
     */
    public function getQuoteStats($period = 30) {
        $query = "SELECT DATE(created_at) as date, COUNT(*) as count 
                  FROM quote_requests 
                  WHERE created_at >= DATE_SUB(CURDATE(), INTERVAL :period DAY) 
                  GROUP BY DATE(created_at) 
                  ORDER BY date ASC";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':period', $period, PDO::PARAM_INT);
        $stmt->execute();
        
        return $stmt->fetchAll();
    }
}
?>
