Test Technique - Gestion de Dossiers
📋 Description du projet
Application web de gestion de dossiers avec système de relations entre dossiers, tiers et contacts. Développée dans le cadre d'un test technique utilisant une architecture contrainte à 2 tables uniquement.
🏗️ Architecture
Base de données

2 tables uniquement : entites et relations
Entités : stocke dossiers, tiers et contacts dans une même table
Relations : gère les liens hiérarchiques entre entités
Vues SQL : simplifient les requêtes complexes

Technologies

Backend : PHP 7.4+ (orienté objet)
Frontend : HTML5, CSS3, JavaScript, Bootstrap 5
Base de données : MySQL 5.7+
Serveur : Apache (MAMP/XAMPP)

🚀 Installation
Prérequis

MAMP/XAMPP avec PHP 7.4+ et MySQL
Navigateur web moderne

Étapes d'installation

Cloner le projet
bashgit clone [URL_DU_REPO]
cd testMaltGestionDossier

Configuration MAMP

Démarrer Apache et MySQL
Placer le projet dans le dossier htdocs


Base de données

Accéder à phpMyAdmin : http://localhost:8888/phpMyAdmin/
Créer la base gestion_dossiers
Importer les fichiers SQL dans l'ordre :
sql/create_tables.sql
sql/sample_data.sql



Configuration

Adapter config/database.php si nécessaire (port MAMP : 8889)


Accès à l'application
http://localhost:8888/testMaltGestionDossier/


🎯 Fonctionnalités
✅ Implémentées

 Affichage liste des dossiers avec tiers associés
 Détail d'un dossier avec tiers et contacts
 Création de dossiers avec référence auto-générée
 Modification de dossiers (ajout/suppression de tiers)
 Modification de tiers (ajout/suppression de contacts)
 Recherche par tiers ou contact (bonus)

🔄 Architecture technique

 Modélisation avec contrainte 2 tables
 Relations complexes via table générique
 Vues SQL pour optimiser les jointures
 Code PHP orienté objet et structuré
 Interface Bootstrap responsive
 Gestion des contraintes métier (emails uniques, références logiques)

📁 Structure du projet
testMaltGestionDossier/
├── index.php                 # Point d'entrée
├── config/
│   ├── config.php           # Configuration générale
│   └── database.php         # Connexion base de données
├── models/
│   ├── Database.php         # Classe de base
│   ├── Dossier.php         # Modèle Dossier
│   ├── Tiers.php           # Modèle Tiers
│   └── Contact.php         # Modèle Contact
├── controllers/
│   ├── DossierController.php
│   ├── TierController.php
│   └── ContactController.php
├── views/
│   ├── layouts/            # Templates de base
│   └── dossiers/          # Vues spécifiques
├── assets/
│   ├── css/style.css      # Styles personnalisés
│   └── js/app.js          # JavaScript
└── sql/                   # Scripts base de données
🛠️ Utilisation
Création d'un dossier

Accéder à "Nouveau Dossier"
La référence est générée automatiquement (DOS-YYYY-XXX)
Optionnel : associer des tiers existants

Gestion des relations

Dossier → Tiers : ajouter/supprimer depuis le détail du dossier
Tiers → Contacts : gérer depuis la vue détaillée de chaque tiers

Recherche

Recherche globale par nom de tiers ou email de contact
Filtrage en temps réel des résultats

🔧 Développement
Choix techniques justifiés

Architecture 2 tables : respecte la contrainte tout en gardant la flexibilité
Vues SQL : simplifient les requêtes complexes et améliorent les performances
POO : code structuré et réutilisable
Bootstrap 5 : interface moderne et responsive
PDO : sécurité et performance pour l'accès aux données

Contraintes métier respectées

Unicité des références de dossiers
Unicité des emails de contacts
Génération automatique des références
Soft delete pour préserver l'intégrité

👨‍💻 Auteur
Développé dans le cadre d'un test technique pour une mission freelance.
📄 License
Projet de test technique - Usage libre pour évaluation.
