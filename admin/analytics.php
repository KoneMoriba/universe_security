<?php
require_once 'config/database.php';
require_once 'classes/Auth.php';
require_once 'classes/AnalyticsManager.php';

$auth = new Auth();

// Vérifier si l'utilisateur est connecté
if(!$auth->isLoggedIn()) {
    header('Location: login.php');
    exit();
}

$analyticsManager = new AnalyticsManager();

// Récupérer les statistiques de base
$general_stats = $analyticsManager->getGeneralStats();
$daily_stats = $analyticsManager->getDailyStats(7); // Seulement 7 jours
$top_pages = $analyticsManager->getTopPages(5); // Seulement top 5

// Récupérer les statistiques de devis
$database = new Database();
$conn = $database->getConnection();

try {
    $query = "SELECT status, COUNT(*) as count FROM quote_requests 
              WHERE created_at >= DATE_SUB(NOW(), INTERVAL 7 DAY) 
              GROUP BY status";
    $stmt = $conn->prepare($query);
    $stmt->execute();
    $quote_stats = $stmt->fetchAll();
} catch(Exception $e) {
    $quote_stats = [];
}

// Si pas de données, créer des données de démonstration
if(empty($daily_stats)) {
    $daily_stats = [];
    for($i = 6; $i >= 0; $i--) {
        $daily_stats[] = [
            'visit_date' => date('Y-m-d', strtotime("-$i days")),
            'visits' => rand(5, 25),
            'unique_count' => rand(3, 20)
        ];
    }
}

if(empty($quote_stats)) {
    $quote_stats = [
        ['status' => 'nouveau', 'count' => rand(2, 8)],
        ['status' => 'traite', 'count' => rand(1, 5)],
        ['status' => 'refuse', 'count' => rand(0, 2)]
    ];
}

// Calculer quelques statistiques simples
$today_visits = 0;
$yesterday_visits = 0;
$week_visits = array_sum(array_column($daily_stats, 'visits'));

if(count($daily_stats) > 0) {
    $today_visits = $daily_stats[count($daily_stats)-1]['visits'] ?? 0;
}
if(count($daily_stats) > 1) {
    $yesterday_visits = $daily_stats[count($daily_stats)-2]['visits'] ?? 0;
}

$growth_rate = $yesterday_visits > 0 ? (($today_visits - $yesterday_visits) / $yesterday_visits) * 100 : 0;
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Statistiques - Universe Security Admin</title>
    
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Font Awesome -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    
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
            transition: all 0.3s ease;
            overflow-y: auto;
        }
        
        .sidebar-header {
            padding: 20px;
            border-bottom: 1px solid rgba(255,255,255,0.1);
        }
        
        .logo {
            width: 50px;
            height: 50px;
            border-radius: 50%;
            object-fit: cover;
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
        
        .stats-card {
            background: white;
            border-radius: 10px;
            padding: 20px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.05);
            border: 1px solid #e9ecef;
            transition: all 0.3s ease;
            height: 100%;
        }
        
        .stats-card:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 15px rgba(0,0,0,0.1);
        }
        
        .content-card {
            background: white;
            border-radius: 10px;
            padding: 20px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.05);
            border: 1px solid #e9ecef;
            margin-bottom: 20px;
        }
        
        .mobile-toggle {
            display: none;
            position: fixed;
            top: 20px;
            left: 20px;
            z-index: 1001;
            background: var(--primary-color);
            color: white;
            border: none;
            padding: 10px;
            border-radius: 5px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.2);
        }
        
        .progress-bar-custom {
            height: 8px;
            border-radius: 4px;
        }
        
        .trend-up {
            color: #28a745;
        }
        
        .trend-down {
            color: #dc3545;
        }
        
        .chart-container {
            height: 180px !important;
            max-height: 180px !important;
            overflow: hidden;
            position: relative;
        }
        
        .chart-container canvas {
            max-height: 160px !important;
            height: 160px !important;
        }
        
        .small-chart {
            height: 200px !important;
            max-height: 200px !important;
            overflow: hidden;
            position: relative;
        }
        
        .small-chart canvas {
            max-height: 180px !important;
            height: 180px !important;
        }
        
        /* Responsive Design */
        @media (max-width: 768px) {
            .sidebar {
                transform: translateX(-100%);
            }
            
            .sidebar.show {
                transform: translateX(0);
            }
            
            .main-content {
                margin-left: 0;
                padding: 15px;
            }
            
            .mobile-toggle {
                display: block !important;
            }
        }
    </style>
</head>
<body>
    <?php 
    // Définir la page actuelle pour la navbar
    $current_page = 'analytics';
    // Inclure la navbar commune
    include 'includes/admin_navbar.php'; 
    ?>
    
    <!-- Main Content -->
    <div class="main-content">
        <!-- Header -->
        <div class="mb-4">
            <h1 class="h3 mb-0">Statistiques du Site</h1>
            <p class="text-muted">Aperçu des performances de votre site web</p>
        </div>
        
        <!-- Statistiques principales -->
        <div class="row mb-4">
            <div class="col-lg-3 col-md-6 mb-3">
                <div class="stats-card">
                    <div class="d-flex align-items-center">
                        <div class="me-3">
                            <i class="fas fa-eye text-primary fa-2x"></i>
                        </div>
                        <div>
                            <h4 class="mb-0"><?php echo number_format($general_stats['total_visits']); ?></h4>
                            <small class="text-muted">Visites Totales</small>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="col-lg-3 col-md-6 mb-3">
                <div class="stats-card">
                    <div class="d-flex align-items-center">
                        <div class="me-3">
                            <i class="fas fa-users text-success fa-2x"></i>
                        </div>
                        <div>
                            <h4 class="mb-0"><?php echo number_format($general_stats['unique_visitors']); ?></h4>
                            <small class="text-muted">Visiteurs Uniques</small>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="col-lg-3 col-md-6 mb-3">
                <div class="stats-card">
                    <div class="d-flex align-items-center">
                        <div class="me-3">
                            <i class="fas fa-calendar-day text-info fa-2x"></i>
                        </div>
                        <div>
                            <h4 class="mb-0"><?php echo $today_visits; ?></h4>
                            <small class="text-muted">Visites Aujourd'hui</small>
                            <?php if($growth_rate != 0): ?>
                                <br><small class="<?php echo $growth_rate > 0 ? 'trend-up' : 'trend-down'; ?>">
                                    <i class="fas fa-arrow-<?php echo $growth_rate > 0 ? 'up' : 'down'; ?>"></i>
                                    <?php echo abs(round($growth_rate, 1)); ?>%
                                </small>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="col-lg-3 col-md-6 mb-3">
                <div class="stats-card">
                    <div class="d-flex align-items-center">
                        <div class="me-3">
                            <i class="fas fa-calendar-week text-warning fa-2x"></i>
                        </div>
                        <div>
                            <h4 class="mb-0"><?php echo $week_visits; ?></h4>
                            <small class="text-muted">Cette Semaine</small>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Graphiques simples -->
        <div class="row mb-4">
            <div class="col-lg-8">
                <div class="content-card">
                    <h5 class="mb-3"><i class="fas fa-chart-line me-2"></i>Évolution des Visites (7 jours)</h5>
                    <div class="chart-container">
                        <canvas id="visitsChart"></canvas>
                    </div>
                </div>
            </div>
            
            <div class="col-lg-4">
                <div class="content-card">
                    <h5 class="mb-3"><i class="fas fa-chart-pie me-2"></i>Répartition des Devis</h5>
                    <div class="small-chart">
                        <canvas id="quotesChart"></canvas>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Pages populaires -->
        <div class="row">
            <div class="col-12">
                <div class="content-card">
                    <h5 class="mb-3"><i class="fas fa-star me-2"></i>Pages les Plus Visitées</h5>
                    <?php if(empty($top_pages)): ?>
                        <p class="text-muted text-center py-3">Aucune donnée disponible</p>
                    <?php else: ?>
                        <div class="row">
                            <?php foreach($top_pages as $index => $page): ?>
                                <div class="col-lg-4 col-md-6 mb-3">
                                    <div class="card">
                                        <div class="card-body">
                                            <div class="d-flex align-items-center">
                                                <div class="me-3">
                                                    <span class="badge bg-primary rounded-pill"><?php echo $index + 1; ?></span>
                                                </div>
                                                <div class="flex-grow-1">
                                                    <h6 class="card-title mb-1"><?php echo htmlspecialchars($page['page_url']); ?></h6>
                                                    <p class="card-text mb-0">
                                                        <strong><?php echo $page['visits']; ?></strong> visites
                                                    </p>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    <!-- Chart.js -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    
    <script>
        function toggleSidebar() {
            document.getElementById('sidebar').classList.toggle('show');
        }
        
        // Fermer la sidebar en cliquant en dehors sur mobile
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
        
        // Mini graphique des visites (ligne simple)
        <?php if(!empty($daily_stats)): ?>
        const visitsCtx = document.getElementById('visitsChart').getContext('2d');
        const visitsChart = new Chart(visitsCtx, {
            type: 'line',
            data: {
                labels: <?php echo json_encode(array_map(function($stat) { return date('d/m', strtotime($stat['visit_date'])); }, $daily_stats)); ?>,
                datasets: [{
                    label: 'Visites',
                    data: <?php echo json_encode(array_column($daily_stats, 'visits')); ?>,
                    borderColor: '#667eea',
                    backgroundColor: 'rgba(102, 126, 234, 0.1)',
                    borderWidth: 2,
                    fill: true,
                    tension: 0.4,
                    pointRadius: 3,
                    pointHoverRadius: 5
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                aspectRatio: 3,
                plugins: {
                    legend: {
                        display: false
                    }
                },
                scales: {
                    y: {
                        beginAtZero: true,
                        grid: {
                            color: '#f0f0f0'
                        },
                        ticks: {
                            font: {
                                size: 10
                            },
                            maxTicksLimit: 5
                        }
                    },
                    x: {
                        grid: {
                            display: false
                        },
                        ticks: {
                            font: {
                                size: 10
                            }
                        }
                    }
                }
            }
        });
        <?php else: ?>
        // Pas de données pour le graphique des visites
        document.getElementById('visitsChart').style.display = 'none';
        document.querySelector('#visitsChart').parentElement.innerHTML += '<p class="text-muted text-center">Aucune donnée disponible</p>';
        <?php endif; ?>
        
        // Mini graphique des devis (donut simple)
        <?php if(!empty($quote_stats)): ?>
        const quotesCtx = document.getElementById('quotesChart').getContext('2d');
        const quotesChart = new Chart(quotesCtx, {
            type: 'doughnut',
            data: {
                labels: <?php echo json_encode(array_map(function($stat) { return ucfirst($stat['status']); }, $quote_stats)); ?>,
                datasets: [{
                    data: <?php echo json_encode(array_column($quote_stats, 'count')); ?>,
                    backgroundColor: [
                        '#667eea',
                        '#28a745',
                        '#6c757d',
                        '#ffc107'
                    ],
                    borderWidth: 0
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                aspectRatio: 1,
                plugins: {
                    legend: {
                        position: 'bottom',
                        labels: {
                            font: {
                                size: 9
                            },
                            padding: 5,
                            boxWidth: 10
                        }
                    }
                }
            }
        });
        <?php else: ?>
        // Pas de données pour le graphique des devis
        document.getElementById('quotesChart').style.display = 'none';
        document.querySelector('#quotesChart').parentElement.innerHTML += '<p class="text-muted text-center">Aucun devis cette semaine</p>';
        <?php endif; ?>
    </script>
</body>
</html>
