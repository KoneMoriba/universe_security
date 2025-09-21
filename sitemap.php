<?php
header('Content-Type: application/xml; charset=utf-8');
echo '<?xml version="1.0" encoding="UTF-8"?>';

// Connexion à la base pour récupérer les articles dynamiques
try {
    require_once 'admin/config/database.php';
    $database = new Database();
    $conn = $database->getConnection();
    
    // Récupérer les articles de blog publiés
    $query = "SELECT slug, updated_at FROM blog_articles WHERE is_published = 1 ORDER BY updated_at DESC";
    $stmt = $conn->prepare($query);
    $stmt->execute();
    $articles = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch(Exception $e) {
    $articles = [];
}
?>
<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">
    <!-- Pages principales -->
    <url>
        <loc>https://universe-security.ci/</loc>
        <lastmod><?php echo date('Y-m-d'); ?></lastmod>
        <changefreq>weekly</changefreq>
        <priority>1.0</priority>
    </url>
    <url>
        <loc>https://universe-security.ci/services.php</loc>
        <lastmod><?php echo date('Y-m-d'); ?></lastmod>
        <changefreq>weekly</changefreq>
        <priority>0.9</priority>
    </url>
    <url>
        <loc>https://universe-security.ci/tarification.php</loc>
        <lastmod><?php echo date('Y-m-d'); ?></lastmod>
        <changefreq>monthly</changefreq>
        <priority>0.8</priority>
    </url>
    <url>
        <loc>https://universe-security.ci/equipe.php</loc>
        <lastmod><?php echo date('Y-m-d'); ?></lastmod>
        <changefreq>monthly</changefreq>
        <priority>0.7</priority>
    </url>
    <url>
        <loc>https://universe-security.ci/blog.php</loc>
        <lastmod><?php echo date('Y-m-d'); ?></lastmod>
        <changefreq>weekly</changefreq>
        <priority>0.8</priority>
    </url>
    <url>
        <loc>https://universe-security.ci/galerie.php</loc>
        <lastmod><?php echo date('Y-m-d'); ?></lastmod>
        <changefreq>monthly</changefreq>
        <priority>0.6</priority>
    </url>
    
    <!-- Articles de blog dynamiques -->
    <?php foreach($articles as $article): ?>
    <url>
        <loc>https://universe-security.ci/blog.php?article=<?php echo urlencode($article['slug']); ?></loc>
        <lastmod><?php echo date('Y-m-d', strtotime($article['updated_at'])); ?></lastmod>
        <changefreq>monthly</changefreq>
        <priority>0.6</priority>
    </url>
    <?php endforeach; ?>
</urlset>
