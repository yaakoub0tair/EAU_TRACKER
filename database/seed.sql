-- database/seed.sql
USE eautrack_rural;

-- Insérer les activités de référence
INSERT INTO activity_references (name, volume_eco, volume_max, category, alert_weight) VALUES
('Douche', 50, 100, 'domestique', 2),
('Vaisselle', 15, 30, 'domestique', 1),
('Lessive', 40, 80, 'domestique', 1),
('WC (chasse)', 6, 12, 'domestique', 1),
('Lavage mains', 3, 5, 'domestique', 1),
('Arrosage jardin', 20, 50, 'domestique', 2),
('Irrigation champ', 200, 500, 'agricole', 3),
('Abreuvoir bétail', 100, 300, 'agricole', 3),
('Nettoyage étable', 50, 150, 'agricole', 2),
('Fontaine publique', 10, 20, 'collectif', 1);

-- Insérer un profil de test
INSERT INTO user_profiles (nom, nb_personnes, type_habitation, quota_jour, village) VALUES
('Famille Test', 4, 'maison', 150, 'Nador'),
('Ahmed Alami', 5, 'ferme', 200, 'Zaio'),
('Résidence Universitaire', 20, 'residence', 100, 'Selouane');