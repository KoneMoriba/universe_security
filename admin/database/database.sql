-- Base de données pour l'espace administrateur Universe Security
CREATE DATABASE IF NOT EXISTS universe_security_admin;
USE universe_security_admin;

-- Table des administrateurs
CREATE TABLE IF NOT EXISTS admins (
    id INT PRIMARY KEY AUTO_INCREMENT,
    username VARCHAR(50) UNIQUE NOT NULL,
    email VARCHAR(100) UNIQUE NOT NULL,
    password VARCHAR(255) NOT NULL,
    full_name VARCHAR(100) NOT NULL,
    role ENUM('super_admin', 'admin', 'moderator') DEFAULT 'admin',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    last_login TIMESTAMP NULL,
    is_active BOOLEAN DEFAULT TRUE
);

-- Table des demandes de devis
CREATE TABLE IF NOT EXISTS quote_requests (
    id INT PRIMARY KEY AUTO_INCREMENT,
    name VARCHAR(100) NOT NULL,
    email VARCHAR(100) NOT NULL,
    phone VARCHAR(20),
    service VARCHAR(100) NOT NULL,
    message TEXT,
    status ENUM('nouveau', 'en_cours', 'traite', 'refuse') DEFAULT 'nouveau',
    priority ENUM('basse', 'normale', 'haute', 'urgente') DEFAULT 'normale',
    admin_notes TEXT,
    assigned_to INT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (assigned_to) REFERENCES admins(id) ON DELETE SET NULL
);

-- Table des services
CREATE TABLE IF NOT EXISTS services (
    id INT PRIMARY KEY AUTO_INCREMENT,
    title VARCHAR(100) NOT NULL,
    description TEXT NOT NULL,
    icon VARCHAR(50),
    price DECIMAL(10,2),
    currency VARCHAR(10) DEFAULT 'XOF',
    is_active BOOLEAN DEFAULT TRUE,
    display_order INT DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    created_by INT,
    FOREIGN KEY (created_by) REFERENCES admins(id) ON DELETE SET NULL
);

-- Table des produits
CREATE TABLE IF NOT EXISTS products (
    id INT PRIMARY KEY AUTO_INCREMENT,
    name VARCHAR(100) NOT NULL,
    description TEXT,
    category VARCHAR(50),
    price DECIMAL(10,2) DEFAULT 0,
    currency VARCHAR(10) DEFAULT 'XOF',
    stock_quantity INT DEFAULT 0,
    product_image VARCHAR(255),
    specifications TEXT,
    is_active BOOLEAN DEFAULT TRUE,
    is_featured BOOLEAN DEFAULT FALSE,
    display_order INT DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    created_by INT,
    FOREIGN KEY (created_by) REFERENCES admins(id) ON DELETE SET NULL
);

-- Table des commentaires/témoignages
CREATE TABLE IF NOT EXISTS testimonials (
    id INT PRIMARY KEY AUTO_INCREMENT,
    client_name VARCHAR(100) NOT NULL,
    client_position VARCHAR(100),
    client_company VARCHAR(100),
    content TEXT NOT NULL,
    rating INT DEFAULT 5 CHECK (rating >= 1 AND rating <= 5),
    client_image VARCHAR(255),
    is_approved BOOLEAN DEFAULT FALSE,
    is_featured BOOLEAN DEFAULT FALSE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    approved_by INT,
    FOREIGN KEY (approved_by) REFERENCES admins(id) ON DELETE SET NULL
);

-- Table des statistiques de visites
CREATE TABLE IF NOT EXISTS site_visits (
    id INT PRIMARY KEY AUTO_INCREMENT,
    ip_address VARCHAR(45),
    user_agent TEXT,
    page_url VARCHAR(255),
    referrer VARCHAR(255),
    country VARCHAR(50),
    city VARCHAR(50),
    visit_date DATE,
    visit_time TIME,
    session_id VARCHAR(100),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Table des paramètres du site
CREATE TABLE IF NOT EXISTS site_settings (
    id INT PRIMARY KEY AUTO_INCREMENT,
    setting_key VARCHAR(50) UNIQUE NOT NULL,
    setting_value TEXT,
    setting_type ENUM('text', 'number', 'boolean', 'json') DEFAULT 'text',
    description TEXT,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    updated_by INT,
    FOREIGN KEY (updated_by) REFERENCES admins(id) ON DELETE SET NULL
);

-- Table des logs d'activité admin
CREATE TABLE IF NOT EXISTS admin_logs (
    id INT PRIMARY KEY AUTO_INCREMENT,
    admin_id INT NOT NULL,
    action VARCHAR(100) NOT NULL,
    table_name VARCHAR(50),
    record_id INT,
    old_values JSON,
    new_values JSON,
    ip_address VARCHAR(45),
    user_agent TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (admin_id) REFERENCES admins(id) ON DELETE CASCADE
);

-- Insertion de l'administrateur par défaut
INSERT INTO admins (username, email, password, full_name, role) VALUES 
('admin', 'admin@universesecurity.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Administrateur Principal', 'super_admin');

-- Insertion des paramètres par défaut
INSERT INTO site_settings (setting_key, setting_value, setting_type, description) VALUES 
('site_name', 'Universe Security', 'text', 'Nom du site'),
('contact_email', 'contact@universesecurity.com', 'text', 'Email de contact principal'),
('contact_phone', '+225 0101012501', 'text', 'Téléphone de contact'),
('address', 'Cocody Angré 8ième, Abidjan, Côte d\'Ivoire', 'text', 'Adresse de l\'entreprise'),
('maintenance_mode', '0', 'boolean', 'Mode maintenance du site'),
('google_analytics_id', '', 'text', 'ID Google Analytics');

-- Insertion des services par défaut
INSERT INTO services (title, description, icon, price, display_order) VALUES 
('Cybersécurité', 'Protection complète de vos données et systèmes contre les menaces cybernétiques', 'fa-shield-alt', 150000.00, 1),
('Analyse de Données', 'Transformez vos données en insights stratégiques pour optimiser vos performances', 'fa-chart-pie', 200000.00, 2),
('Développement Web et mobile', 'Création de sites web modernes et applications web et mobiles sur mesure pour votre entreprise', 'fa-code', 200000.00, 3),
('Matériels informatiques', 'Un service complet de vente et maintenance de matériels informatiques adaptés à vos besoins', 'fa-laptop', 150000.00, 4),
('Optimisation SEO', 'Améliorez votre visibilité en ligne et augmentez votre trafic organique', 'fa-search', 100000.00, 5);
