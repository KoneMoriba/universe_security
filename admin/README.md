# Espace Administrateur - Universe Security

## 📋 Description

Espace administrateur complet pour le site Universe Security permettant de gérer :
- Les demandes de devis
- Les services et produits
- Les témoignages clients
- Les statistiques de visites
- Les paramètres du site

## 🚀 Installation

### Prérequis
- XAMPP (Apache + MySQL + PHP 7.4+)
- Navigateur web moderne

### Étapes d'installation

1. **Configuration de la base de données**
   ```sql
   -- Exécuter le fichier database/database.sql dans phpMyAdmin
   -- Ou via ligne de commande :
   mysql -u root -p < admin/database/database.sql
   ```

2. **Configuration des paramètres**
   - Modifier `admin/config/database.php` si nécessaire
   - Ajuster les paramètres de connexion MySQL

3. **Permissions**
   - Créer le dossier `uploads/` à la racine si nécessaire
   - S'assurer que PHP a les droits d'écriture

## 👤 Connexion par défaut

- **URL d'accès :** `http://localhost/universe-security/admin/login.php`
- **Nom d'utilisateur :** `admin`
- **Email :** `admin@universesecurity.com`
- **Mot de passe :** `password` (à changer lors de la première connexion)

## 🔧 Fonctionnalités

### Tableau de Bord
- Vue d'ensemble des statistiques
- Graphiques des demandes de devis
- Activités récentes

### Gestion des Devis
- Liste complète des demandes
- Filtrage par statut et recherche
- Assignation aux administrateurs
- Suivi des priorités
- Notes administrateur

### Gestion des Services
- Création/modification/suppression
- Gestion des prix et devises
- Ordre d'affichage personnalisable
- Activation/désactivation

### Gestion des Témoignages
- Modération des témoignages
- Système d'approbation
- Mise en vedette
- Gestion des notes (1-5 étoiles)

### Statistiques et Analytics
- Suivi des visites en temps réel
- Graphiques de performance
- Pages les plus visitées
- Sources de trafic
- Statistiques par navigateur
- Répartition horaire des visites

### Gestion des Utilisateurs
- Profils administrateur
- Changement de mot de passe
- Historique des activités
- Gestion des rôles (Super Admin, Admin, Modérateur)

## 🔐 Sécurité

### Authentification
- Hashage sécurisé des mots de passe (bcrypt)
- Sessions sécurisées
- Protection CSRF

### Autorisations
- Système de rôles hiérarchique
- Logs d'activité complets
- Validation des données côté serveur

### Protection
- Échappement des données (XSS)
- Requêtes préparées (SQL Injection)
- Validation des entrées utilisateur

## 📊 Structure de la Base de Données

### Tables principales
- `admins` : Comptes administrateur
- `quote_requests` : Demandes de devis
- `services` : Services proposés
- `products` : Produits (extensible)
- `testimonials` : Témoignages clients
- `site_visits` : Statistiques de visites
- `site_settings` : Paramètres du site
- `admin_logs` : Logs d'activité

## 🎨 Interface

### Design
- Interface moderne et responsive
- Sidebar de navigation fixe
- Thème cohérent avec le site principal
- Graphiques interactifs (Chart.js)

### Compatibilité
- Bootstrap 5.1.3
- Font Awesome 6.0
- Compatible tous navigateurs modernes
- Responsive mobile/tablette

## 📱 Intégration Site Principal

### Tracking automatique
- Enregistrement des visites
- Géolocalisation basique
- Suivi des pages populaires

### Formulaire de devis
- Intégration transparente
- Validation côté client et serveur
- Notifications email automatiques

### Lien d'accès admin
- Icône discrète dans la navigation
- Accès sécurisé

## 🔄 Maintenance

### Nettoyage automatique
- Suppression des anciennes données de visite (>1 an)
- Optimisation des performances

### Sauvegarde
- Exporter régulièrement la base de données
- Sauvegarder les fichiers uploadés

### Mise à jour
- Vérifier les logs d'erreur PHP
- Mettre à jour les dépendances si nécessaire

## 🆘 Support

### Logs d'erreur
- Vérifier les logs Apache/PHP
- Consulter `admin_logs` pour l'activité utilisateur

### Problèmes courants
1. **Erreur de connexion base de données**
   - Vérifier les paramètres dans `config/database.php`
   - S'assurer que MySQL est démarré

2. **Page blanche**
   - Activer l'affichage des erreurs PHP
   - Vérifier les permissions de fichiers

3. **Session expirée**
   - Vérifier la configuration des sessions PHP
   - Augmenter `session.gc_maxlifetime` si nécessaire

## 📈 Évolutions Possibles

### Fonctionnalités avancées
- Système de notifications push
- API REST pour intégrations
- Gestion multi-langue
- Système de cache
- Backup automatique
- Intégration CRM

### Améliorations UX
- Mode sombre
- Personnalisation du tableau de bord
- Raccourcis clavier
- Recherche globale

## 📞 Contact Technique

Pour toute question technique ou demande d'évolution, contactez l'équipe de développement.

---

**Version :** 1.0  
**Dernière mise à jour :** Janvier 2024  
**Développé pour :** Universe Security
