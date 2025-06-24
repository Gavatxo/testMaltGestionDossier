-- MySQL dump 10.13  Distrib 5.7.39, for osx10.12 (x86_64)
--
-- Host: localhost    Database: gestion_dossiers
-- ------------------------------------------------------
-- Server version	5.7.39

/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8 */;
/*!40103 SET @OLD_TIME_ZONE=@@TIME_ZONE */;
/*!40103 SET TIME_ZONE='+00:00' */;
/*!40014 SET @OLD_UNIQUE_CHECKS=@@UNIQUE_CHECKS, UNIQUE_CHECKS=0 */;
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;
/*!40111 SET @OLD_SQL_NOTES=@@SQL_NOTES, SQL_NOTES=0 */;

--
-- Table structure for table `entites`
--

DROP TABLE IF EXISTS `entites`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `entites` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `type` enum('dossier','tiers','contact') COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'Type d''entité',
  `reference` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT 'Référence unique pour les dossiers (ex: DOS-2025-001)',
  `denomination` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT 'Dénomination pour les tiers',
  `nom` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT 'Nom pour les contacts',
  `prenom` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT 'Prénom pour les contacts',
  `email` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT 'Email pour les contacts',
  `actif` tinyint(1) DEFAULT '1' COMMENT 'Soft delete',
  `date_creation` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `date_modification` datetime DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `reference` (`reference`),
  KEY `idx_type` (`type`),
  KEY `idx_reference` (`reference`),
  KEY `idx_denomination` (`denomination`),
  KEY `idx_email` (`email`),
  KEY `idx_nom_prenom` (`nom`,`prenom`),
  KEY `idx_actif` (`actif`),
  FULLTEXT KEY `denomination` (`denomination`,`nom`,`prenom`,`email`)
) ENGINE=InnoDB AUTO_INCREMENT=12 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Table principale stockant tous les types d''entités (dossiers, tiers, contacts) avec leurs attributs spécifiques';
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `entites`
--

LOCK TABLES `entites` WRITE;
/*!40000 ALTER TABLE `entites` DISABLE KEYS */;
INSERT INTO `entites` VALUES (1,'dossier','DOS-2025-004',NULL,NULL,NULL,NULL,1,'2025-01-15 09:30:00','2025-06-24 16:15:17'),(2,'dossier','DOS-2025-002',NULL,NULL,NULL,NULL,1,'2025-01-18 14:20:00',NULL),(3,'dossier','DOS-2025-003',NULL,NULL,NULL,NULL,1,'2025-01-22 11:45:00',NULL),(4,'tiers',NULL,'Entreprise Technologie Avancée',NULL,NULL,NULL,1,'2025-01-10 08:00:00',NULL),(5,'tiers',NULL,'Solutions Informatiques Pro',NULL,NULL,NULL,1,'2025-01-12 10:15:00',NULL),(6,'tiers',NULL,'Conseil & Stratégie SARL',NULL,NULL,NULL,1,'2025-01-16 16:30:00',NULL),(7,'tiers',NULL,'Innovation Digital Group',NULL,NULL,NULL,1,'2025-01-20 09:45:00',NULL),(8,'contact',NULL,NULL,'Martin','Jean','jean.martin@eta.com',1,'2025-01-10 08:30:00',NULL),(9,'contact',NULL,NULL,'Dubois','Marie','marie.dubois@eta.com',1,'2025-01-10 08:45:00',NULL),(10,'contact',NULL,NULL,'Lefebvre','Pierre','pierre.lefebvre@sipro.fr',1,'2025-01-12 10:30:00',NULL),(11,'contact',NULL,NULL,'Moreau','Sophie','sophie.moreau@conseil-strategie.fr',1,'2025-01-16 17:00:00',NULL);
/*!40000 ALTER TABLE `entites` ENABLE KEYS */;
UNLOCK TABLES;
/*!50003 SET @saved_cs_client      = @@character_set_client */ ;
/*!50003 SET @saved_cs_results     = @@character_set_results */ ;
/*!50003 SET @saved_col_connection = @@collation_connection */ ;
/*!50003 SET character_set_client  = utf8mb4 */ ;
/*!50003 SET character_set_results = utf8mb4 */ ;
/*!50003 SET collation_connection  = utf8mb4_unicode_ci */ ;
/*!50003 SET @saved_sql_mode       = @@sql_mode */ ;
/*!50003 SET sql_mode              = 'ONLY_FULL_GROUP_BY,STRICT_TRANS_TABLES,NO_ZERO_IN_DATE,NO_ZERO_DATE,ERROR_FOR_DIVISION_BY_ZERO,NO_ENGINE_SUBSTITUTION' */ ;
DELIMITER ;;
/*!50003 CREATE*/ /*!50017 DEFINER=`root`@`localhost`*/ /*!50003 TRIGGER `entites_before_insert` 
BEFORE INSERT ON `entites`
FOR EACH ROW
BEGIN
    -- Validation selon le type
    CASE NEW.type
        WHEN 'dossier' THEN
            -- Un dossier doit avoir une référence
            IF NEW.reference IS NULL OR NEW.reference = '' THEN
                SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'Un dossier doit avoir une référence';
            END IF;
            -- Nettoyer les champs non utilisés
            SET NEW.denomination = NULL, NEW.nom = NULL, NEW.prenom = NULL, NEW.email = NULL;
            
        WHEN 'tiers' THEN
            -- Un tiers doit avoir une dénomination
            IF NEW.denomination IS NULL OR NEW.denomination = '' THEN
                SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'Un tiers doit avoir une dénomination';
            END IF;
            -- Nettoyer les champs non utilisés
            SET NEW.reference = NULL, NEW.nom = NULL, NEW.prenom = NULL, NEW.email = NULL;
            
        WHEN 'contact' THEN
            -- Un contact doit avoir nom, prénom et email
            IF NEW.nom IS NULL OR NEW.nom = '' THEN
                SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'Un contact doit avoir un nom';
            END IF;
            IF NEW.prenom IS NULL OR NEW.prenom = '' THEN
                SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'Un contact doit avoir un prénom';
            END IF;
            IF NEW.email IS NULL OR NEW.email = '' THEN
                SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'Un contact doit avoir un email';
            END IF;
            -- Vérifier le format de l'email (basique)
            IF NEW.email NOT REGEXP '^[A-Za-z0-9._%+-]+@[A-Za-z0-9.-]+\.[A-Za-z]{2,}$' THEN
                SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'Format d\'email invalide';
            END IF;
            -- Nettoyer les champs non utilisés
            SET NEW.reference = NULL, NEW.denomination = NULL;
    END CASE;
END */;;
DELIMITER ;
/*!50003 SET sql_mode              = @saved_sql_mode */ ;
/*!50003 SET character_set_client  = @saved_cs_client */ ;
/*!50003 SET character_set_results = @saved_cs_results */ ;
/*!50003 SET collation_connection  = @saved_col_connection */ ;
/*!50003 SET @saved_cs_client      = @@character_set_client */ ;
/*!50003 SET @saved_cs_results     = @@character_set_results */ ;
/*!50003 SET @saved_col_connection = @@collation_connection */ ;
/*!50003 SET character_set_client  = utf8mb4 */ ;
/*!50003 SET character_set_results = utf8mb4 */ ;
/*!50003 SET collation_connection  = utf8mb4_unicode_ci */ ;
/*!50003 SET @saved_sql_mode       = @@sql_mode */ ;
/*!50003 SET sql_mode              = 'ONLY_FULL_GROUP_BY,STRICT_TRANS_TABLES,NO_ZERO_IN_DATE,NO_ZERO_DATE,ERROR_FOR_DIVISION_BY_ZERO,NO_ENGINE_SUBSTITUTION' */ ;
DELIMITER ;;
/*!50003 CREATE*/ /*!50017 DEFINER=`root`@`localhost`*/ /*!50003 TRIGGER `entites_before_update` 
BEFORE UPDATE ON `entites`
FOR EACH ROW
BEGIN
    -- Même validation que pour l'insertion
    CASE NEW.type
        WHEN 'dossier' THEN
            IF NEW.reference IS NULL OR NEW.reference = '' THEN
                SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'Un dossier doit avoir une référence';
            END IF;
            SET NEW.denomination = NULL, NEW.nom = NULL, NEW.prenom = NULL, NEW.email = NULL;
            
        WHEN 'tiers' THEN
            IF NEW.denomination IS NULL OR NEW.denomination = '' THEN
                SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'Un tiers doit avoir une dénomination';
            END IF;
            SET NEW.reference = NULL, NEW.nom = NULL, NEW.prenom = NULL, NEW.email = NULL;
            
        WHEN 'contact' THEN
            IF NEW.nom IS NULL OR NEW.nom = '' THEN
                SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'Un contact doit avoir un nom';
            END IF;
            IF NEW.prenom IS NULL OR NEW.prenom = '' THEN
                SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'Un contact doit avoir un prénom';
            END IF;
            IF NEW.email IS NULL OR NEW.email = '' THEN
                SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'Un contact doit avoir un email';
            END IF;
            IF NEW.email NOT REGEXP '^[A-Za-z0-9._%+-]+@[A-Za-z0-9.-]+\.[A-Za-z]{2,}$' THEN
                SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'Format d\'email invalide';
            END IF;
            SET NEW.reference = NULL, NEW.denomination = NULL;
    END CASE;
END */;;
DELIMITER ;
/*!50003 SET sql_mode              = @saved_sql_mode */ ;
/*!50003 SET character_set_client  = @saved_cs_client */ ;
/*!50003 SET character_set_results = @saved_cs_results */ ;
/*!50003 SET collation_connection  = @saved_col_connection */ ;

--
-- Table structure for table `relations`
--

DROP TABLE IF EXISTS `relations`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `relations` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `id_parent` int(11) NOT NULL COMMENT 'ID de l''entité parent',
  `id_enfant` int(11) NOT NULL COMMENT 'ID de l''entité enfant',
  `type_relation` enum('dossier_tiers','tiers_contact') COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'Type de relation',
  `actif` tinyint(1) DEFAULT '1' COMMENT 'Soft delete',
  `date_creation` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `date_modification` datetime DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `unique_relation` (`id_parent`,`id_enfant`,`type_relation`),
  KEY `idx_parent` (`id_parent`),
  KEY `idx_enfant` (`id_enfant`),
  KEY `idx_type_relation` (`type_relation`),
  KEY `idx_actif` (`actif`),
  CONSTRAINT `relations_ibfk_1` FOREIGN KEY (`id_parent`) REFERENCES `entites` (`id`) ON DELETE CASCADE,
  CONSTRAINT `relations_ibfk_2` FOREIGN KEY (`id_enfant`) REFERENCES `entites` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=9 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Table de liaison gérant les relations hiérarchiques entre les entités';
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `relations`
--

LOCK TABLES `relations` WRITE;
/*!40000 ALTER TABLE `relations` DISABLE KEYS */;
INSERT INTO `relations` VALUES (1,1,4,'dossier_tiers',1,'2025-01-15 10:00:00',NULL),(2,1,5,'dossier_tiers',1,'2025-01-15 10:30:00',NULL),(3,2,4,'dossier_tiers',1,'2025-01-18 15:00:00',NULL),(4,3,6,'dossier_tiers',1,'2025-01-22 12:00:00',NULL),(5,4,8,'tiers_contact',1,'2025-01-10 09:00:00',NULL),(6,4,9,'tiers_contact',1,'2025-01-10 09:15:00',NULL),(7,5,10,'tiers_contact',1,'2025-01-12 11:00:00',NULL),(8,6,11,'tiers_contact',1,'2025-01-16 17:30:00',NULL);
/*!40000 ALTER TABLE `relations` ENABLE KEYS */;
UNLOCK TABLES;
/*!50003 SET @saved_cs_client      = @@character_set_client */ ;
/*!50003 SET @saved_cs_results     = @@character_set_results */ ;
/*!50003 SET @saved_col_connection = @@collation_connection */ ;
/*!50003 SET character_set_client  = utf8mb4 */ ;
/*!50003 SET character_set_results = utf8mb4 */ ;
/*!50003 SET collation_connection  = utf8mb4_unicode_ci */ ;
/*!50003 SET @saved_sql_mode       = @@sql_mode */ ;
/*!50003 SET sql_mode              = 'ONLY_FULL_GROUP_BY,STRICT_TRANS_TABLES,NO_ZERO_IN_DATE,NO_ZERO_DATE,ERROR_FOR_DIVISION_BY_ZERO,NO_ENGINE_SUBSTITUTION' */ ;
DELIMITER ;;
/*!50003 CREATE*/ /*!50017 DEFINER=`root`@`localhost`*/ /*!50003 TRIGGER `relations_before_insert` 
BEFORE INSERT ON `relations`
FOR EACH ROW
BEGIN
    DECLARE parent_type VARCHAR(20);
    DECLARE enfant_type VARCHAR(20);
    
    -- Récupérer les types des entités
    SELECT type INTO parent_type FROM entites WHERE id = NEW.id_parent;
    SELECT type INTO enfant_type FROM entites WHERE id = NEW.id_enfant;
    
    -- Valider les types de relations
    CASE NEW.type_relation
        WHEN 'dossier_tiers' THEN
            -- Le parent doit être un dossier et l'enfant un tiers
            IF parent_type != 'dossier' OR enfant_type != 'tiers' THEN
                SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'Relation dossier_tiers: parent doit être dossier, enfant doit être tiers';
            END IF;
            
        WHEN 'tiers_contact' THEN
            -- Le parent doit être un tiers et l'enfant un contact
            IF parent_type != 'tiers' OR enfant_type != 'contact' THEN
                SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'Relation tiers_contact: parent doit être tiers, enfant doit être contact';
            END IF;
    END CASE;
END */;;
DELIMITER ;
/*!50003 SET sql_mode              = @saved_sql_mode */ ;
/*!50003 SET character_set_client  = @saved_cs_client */ ;
/*!50003 SET character_set_results = @saved_cs_results */ ;
/*!50003 SET collation_connection  = @saved_col_connection */ ;

--
-- Temporary table structure for view `v_activites_recentes`
--

DROP TABLE IF EXISTS `v_activites_recentes`;
/*!50001 DROP VIEW IF EXISTS `v_activites_recentes`*/;
SET @saved_cs_client     = @@character_set_client;
SET character_set_client = utf8;
/*!50001 CREATE VIEW `v_activites_recentes` AS SELECT 
 1 AS `type_activite`,
 1 AS `entite_id`,
 1 AS `type_entite`,
 1 AS `description`,
 1 AS `date_activite`*/;
SET character_set_client = @saved_cs_client;

--
-- Temporary table structure for view `v_contacts_stats`
--

DROP TABLE IF EXISTS `v_contacts_stats`;
/*!50001 DROP VIEW IF EXISTS `v_contacts_stats`*/;
SET @saved_cs_client     = @@character_set_client;
SET character_set_client = utf8;
/*!50001 CREATE VIEW `v_contacts_stats` AS SELECT 
 1 AS `id`,
 1 AS `nom`,
 1 AS `prenom`,
 1 AS `email`,
 1 AS `date_creation`,
 1 AS `date_modification`,
 1 AS `actif`,
 1 AS `nb_tiers`,
 1 AS `nb_dossiers`*/;
SET character_set_client = @saved_cs_client;

--
-- Temporary table structure for view `v_dossiers_stats`
--

DROP TABLE IF EXISTS `v_dossiers_stats`;
/*!50001 DROP VIEW IF EXISTS `v_dossiers_stats`*/;
SET @saved_cs_client     = @@character_set_client;
SET character_set_client = utf8;
/*!50001 CREATE VIEW `v_dossiers_stats` AS SELECT 
 1 AS `id`,
 1 AS `reference`,
 1 AS `date_creation`,
 1 AS `date_modification`,
 1 AS `actif`,
 1 AS `nb_tiers`,
 1 AS `nb_contacts`*/;
SET character_set_client = @saved_cs_client;

--
-- Temporary table structure for view `v_hierarchie_complete`
--

DROP TABLE IF EXISTS `v_hierarchie_complete`;
/*!50001 DROP VIEW IF EXISTS `v_hierarchie_complete`*/;
SET @saved_cs_client     = @@character_set_client;
SET character_set_client = utf8;
/*!50001 CREATE VIEW `v_hierarchie_complete` AS SELECT 
 1 AS `dossier_id`,
 1 AS `dossier_reference`,
 1 AS `dossier_date_creation`,
 1 AS `tiers_id`,
 1 AS `tiers_denomination`,
 1 AS `contact_id`,
 1 AS `contact_nom`,
 1 AS `contact_prenom`,
 1 AS `contact_email`,
 1 AS `relation_tiers_date`,
 1 AS `relation_contact_date`*/;
SET character_set_client = @saved_cs_client;

--
-- Temporary table structure for view `v_recherche_globale`
--

DROP TABLE IF EXISTS `v_recherche_globale`;
/*!50001 DROP VIEW IF EXISTS `v_recherche_globale`*/;
SET @saved_cs_client     = @@character_set_client;
SET character_set_client = utf8;
/*!50001 CREATE VIEW `v_recherche_globale` AS SELECT 
 1 AS `type_entite`,
 1 AS `id`,
 1 AS `titre`,
 1 AS `sous_titre`,
 1 AS `date_creation`,
 1 AS `categorie`,
 1 AS `url`*/;
SET character_set_client = @saved_cs_client;

--
-- Temporary table structure for view `v_stats_globales`
--

DROP TABLE IF EXISTS `v_stats_globales`;
/*!50001 DROP VIEW IF EXISTS `v_stats_globales`*/;
SET @saved_cs_client     = @@character_set_client;
SET character_set_client = utf8;
/*!50001 CREATE VIEW `v_stats_globales` AS SELECT 
 1 AS `total_dossiers`,
 1 AS `total_tiers`,
 1 AS `total_contacts`,
 1 AS `total_relations`,
 1 AS `dossiers_ce_mois`,
 1 AS `tiers_sans_contacts`,
 1 AS `contacts_sans_tiers`,
 1 AS `moyenne_tiers_par_dossier`,
 1 AS `moyenne_contacts_par_tiers`*/;
SET character_set_client = @saved_cs_client;

--
-- Temporary table structure for view `v_tiers_stats`
--

DROP TABLE IF EXISTS `v_tiers_stats`;
/*!50001 DROP VIEW IF EXISTS `v_tiers_stats`*/;
SET @saved_cs_client     = @@character_set_client;
SET character_set_client = utf8;
/*!50001 CREATE VIEW `v_tiers_stats` AS SELECT 
 1 AS `id`,
 1 AS `denomination`,
 1 AS `date_creation`,
 1 AS `date_modification`,
 1 AS `actif`,
 1 AS `nb_contacts`,
 1 AS `nb_dossiers`*/;
SET character_set_client = @saved_cs_client;

--
-- Final view structure for view `v_activites_recentes`
--

/*!50001 DROP VIEW IF EXISTS `v_activites_recentes`*/;
/*!50001 SET @saved_cs_client          = @@character_set_client */;
/*!50001 SET @saved_cs_results         = @@character_set_results */;
/*!50001 SET @saved_col_connection     = @@collation_connection */;
/*!50001 SET character_set_client      = utf8mb4 */;
/*!50001 SET character_set_results     = utf8mb4 */;
/*!50001 SET collation_connection      = utf8mb4_unicode_ci */;
/*!50001 CREATE ALGORITHM=UNDEFINED */
/*!50013 DEFINER=`root`@`localhost` SQL SECURITY DEFINER */
/*!50001 VIEW `v_activites_recentes` AS select 'creation_dossier' AS `type_activite`,`d`.`id` AS `entite_id`,'dossier' AS `type_entite`,concat('Création du dossier ',`d`.`reference`) AS `description`,`d`.`date_creation` AS `date_activite` from `entites` `d` where ((`d`.`type` = 'dossier') and (`d`.`actif` = TRUE)) union all select 'creation_tiers' AS `type_activite`,`t`.`id` AS `entite_id`,'tiers' AS `type_entite`,concat('Création du tiers "',`t`.`denomination`,'"') AS `description`,`t`.`date_creation` AS `date_activite` from `entites` `t` where ((`t`.`type` = 'tiers') and (`t`.`actif` = TRUE)) union all select 'creation_contact' AS `type_activite`,`c`.`id` AS `entite_id`,'contact' AS `type_entite`,concat('Création du contact "',`c`.`prenom`,' ',`c`.`nom`,'"') AS `description`,`c`.`date_creation` AS `date_activite` from `entites` `c` where ((`c`.`type` = 'contact') and (`c`.`actif` = TRUE)) union all select concat('creation_relation_',`r`.`type_relation`) AS `type_activite`,`r`.`id` AS `entite_id`,'relation' AS `type_entite`,(case `r`.`type_relation` when 'dossier_tiers' then concat('Association tiers à dossier') when 'tiers_contact' then concat('Association contact à tiers') end) AS `description`,`r`.`date_creation` AS `date_activite` from `relations` `r` where (`r`.`actif` = TRUE) order by `date_activite` desc */;
/*!50001 SET character_set_client      = @saved_cs_client */;
/*!50001 SET character_set_results     = @saved_cs_results */;
/*!50001 SET collation_connection      = @saved_col_connection */;

--
-- Final view structure for view `v_contacts_stats`
--

/*!50001 DROP VIEW IF EXISTS `v_contacts_stats`*/;
/*!50001 SET @saved_cs_client          = @@character_set_client */;
/*!50001 SET @saved_cs_results         = @@character_set_results */;
/*!50001 SET @saved_col_connection     = @@collation_connection */;
/*!50001 SET character_set_client      = utf8mb4 */;
/*!50001 SET character_set_results     = utf8mb4 */;
/*!50001 SET collation_connection      = utf8mb4_unicode_ci */;
/*!50001 CREATE ALGORITHM=UNDEFINED */
/*!50013 DEFINER=`root`@`localhost` SQL SECURITY DEFINER */
/*!50001 VIEW `v_contacts_stats` AS select `c`.`id` AS `id`,`c`.`nom` AS `nom`,`c`.`prenom` AS `prenom`,`c`.`email` AS `email`,`c`.`date_creation` AS `date_creation`,`c`.`date_modification` AS `date_modification`,`c`.`actif` AS `actif`,count(distinct `rt`.`id_parent`) AS `nb_tiers`,count(distinct `rd`.`id_parent`) AS `nb_dossiers` from ((`entites` `c` left join `relations` `rt` on(((`c`.`id` = `rt`.`id_enfant`) and (`rt`.`type_relation` = 'tiers_contact') and (`rt`.`actif` = TRUE)))) left join `relations` `rd` on(((`rt`.`id_parent` = `rd`.`id_enfant`) and (`rd`.`type_relation` = 'dossier_tiers') and (`rd`.`actif` = TRUE)))) where (`c`.`type` = 'contact') group by `c`.`id`,`c`.`nom`,`c`.`prenom`,`c`.`email`,`c`.`date_creation`,`c`.`date_modification`,`c`.`actif` */;
/*!50001 SET character_set_client      = @saved_cs_client */;
/*!50001 SET character_set_results     = @saved_cs_results */;
/*!50001 SET collation_connection      = @saved_col_connection */;

--
-- Final view structure for view `v_dossiers_stats`
--

/*!50001 DROP VIEW IF EXISTS `v_dossiers_stats`*/;
/*!50001 SET @saved_cs_client          = @@character_set_client */;
/*!50001 SET @saved_cs_results         = @@character_set_results */;
/*!50001 SET @saved_col_connection     = @@collation_connection */;
/*!50001 SET character_set_client      = utf8mb4 */;
/*!50001 SET character_set_results     = utf8mb4 */;
/*!50001 SET collation_connection      = utf8mb4_unicode_ci */;
/*!50001 CREATE ALGORITHM=UNDEFINED */
/*!50013 DEFINER=`root`@`localhost` SQL SECURITY DEFINER */
/*!50001 VIEW `v_dossiers_stats` AS select `d`.`id` AS `id`,`d`.`reference` AS `reference`,`d`.`date_creation` AS `date_creation`,`d`.`date_modification` AS `date_modification`,`d`.`actif` AS `actif`,count(distinct `rt`.`id_enfant`) AS `nb_tiers`,count(distinct `rc`.`id_enfant`) AS `nb_contacts` from ((`entites` `d` left join `relations` `rt` on(((`d`.`id` = `rt`.`id_parent`) and (`rt`.`type_relation` = 'dossier_tiers') and (`rt`.`actif` = TRUE)))) left join `relations` `rc` on(((`rt`.`id_enfant` = `rc`.`id_parent`) and (`rc`.`type_relation` = 'tiers_contact') and (`rc`.`actif` = TRUE)))) where (`d`.`type` = 'dossier') group by `d`.`id`,`d`.`reference`,`d`.`date_creation`,`d`.`date_modification`,`d`.`actif` */;
/*!50001 SET character_set_client      = @saved_cs_client */;
/*!50001 SET character_set_results     = @saved_cs_results */;
/*!50001 SET collation_connection      = @saved_col_connection */;

--
-- Final view structure for view `v_hierarchie_complete`
--

/*!50001 DROP VIEW IF EXISTS `v_hierarchie_complete`*/;
/*!50001 SET @saved_cs_client          = @@character_set_client */;
/*!50001 SET @saved_cs_results         = @@character_set_results */;
/*!50001 SET @saved_col_connection     = @@collation_connection */;
/*!50001 SET character_set_client      = utf8mb4 */;
/*!50001 SET character_set_results     = utf8mb4 */;
/*!50001 SET collation_connection      = utf8mb4_unicode_ci */;
/*!50001 CREATE ALGORITHM=UNDEFINED */
/*!50013 DEFINER=`root`@`localhost` SQL SECURITY DEFINER */
/*!50001 VIEW `v_hierarchie_complete` AS select `d`.`id` AS `dossier_id`,`d`.`reference` AS `dossier_reference`,`d`.`date_creation` AS `dossier_date_creation`,`t`.`id` AS `tiers_id`,`t`.`denomination` AS `tiers_denomination`,`c`.`id` AS `contact_id`,`c`.`nom` AS `contact_nom`,`c`.`prenom` AS `contact_prenom`,`c`.`email` AS `contact_email`,`rt`.`date_creation` AS `relation_tiers_date`,`rc`.`date_creation` AS `relation_contact_date` from ((((`entites` `d` join `relations` `rt` on(((`d`.`id` = `rt`.`id_parent`) and (`rt`.`type_relation` = 'dossier_tiers') and (`rt`.`actif` = TRUE)))) join `entites` `t` on(((`rt`.`id_enfant` = `t`.`id`) and (`t`.`type` = 'tiers') and (`t`.`actif` = TRUE)))) left join `relations` `rc` on(((`t`.`id` = `rc`.`id_parent`) and (`rc`.`type_relation` = 'tiers_contact') and (`rc`.`actif` = TRUE)))) left join `entites` `c` on(((`rc`.`id_enfant` = `c`.`id`) and (`c`.`type` = 'contact') and (`c`.`actif` = TRUE)))) where ((`d`.`type` = 'dossier') and (`d`.`actif` = TRUE)) */;
/*!50001 SET character_set_client      = @saved_cs_client */;
/*!50001 SET character_set_results     = @saved_cs_results */;
/*!50001 SET collation_connection      = @saved_col_connection */;

--
-- Final view structure for view `v_recherche_globale`
--

/*!50001 DROP VIEW IF EXISTS `v_recherche_globale`*/;
/*!50001 SET @saved_cs_client          = @@character_set_client */;
/*!50001 SET @saved_cs_results         = @@character_set_results */;
/*!50001 SET @saved_col_connection     = @@collation_connection */;
/*!50001 SET character_set_client      = utf8mb4 */;
/*!50001 SET character_set_results     = utf8mb4 */;
/*!50001 SET collation_connection      = utf8mb4_unicode_ci */;
/*!50001 CREATE ALGORITHM=UNDEFINED */
/*!50013 DEFINER=`root`@`localhost` SQL SECURITY DEFINER */
/*!50001 VIEW `v_recherche_globale` AS select 'dossier' AS `type_entite`,`d`.`id` AS `id`,`d`.`reference` AS `titre`,NULL AS `sous_titre`,`d`.`date_creation` AS `date_creation`,'dossier' AS `categorie`,concat('/dossier/',`d`.`id`) AS `url` from `entites` `d` where ((`d`.`type` = 'dossier') and (`d`.`actif` = TRUE)) union all select 'tiers' AS `type_entite`,`t`.`id` AS `id`,`t`.`denomination` AS `titre`,concat(`vs`.`nb_contacts`,' contact(s), ',`vs`.`nb_dossiers`,' dossier(s)') AS `sous_titre`,`t`.`date_creation` AS `date_creation`,'tiers' AS `categorie`,concat('#tiers-',`t`.`id`) AS `url` from (`entites` `t` join `v_tiers_stats` `vs` on((`t`.`id` = `vs`.`id`))) where ((`t`.`type` = 'tiers') and (`t`.`actif` = TRUE)) union all select 'contact' AS `type_entite`,`c`.`id` AS `id`,concat(`c`.`prenom`,' ',`c`.`nom`) AS `titre`,`c`.`email` AS `sous_titre`,`c`.`date_creation` AS `date_creation`,'contact' AS `categorie`,concat('#contact-',`c`.`id`) AS `url` from `entites` `c` where ((`c`.`type` = 'contact') and (`c`.`actif` = TRUE)) */;
/*!50001 SET character_set_client      = @saved_cs_client */;
/*!50001 SET character_set_results     = @saved_cs_results */;
/*!50001 SET collation_connection      = @saved_col_connection */;

--
-- Final view structure for view `v_stats_globales`
--

/*!50001 DROP VIEW IF EXISTS `v_stats_globales`*/;
/*!50001 SET @saved_cs_client          = @@character_set_client */;
/*!50001 SET @saved_cs_results         = @@character_set_results */;
/*!50001 SET @saved_col_connection     = @@collation_connection */;
/*!50001 SET character_set_client      = utf8mb4 */;
/*!50001 SET character_set_results     = utf8mb4 */;
/*!50001 SET collation_connection      = utf8mb4_unicode_ci */;
/*!50001 CREATE ALGORITHM=UNDEFINED */
/*!50013 DEFINER=`root`@`localhost` SQL SECURITY DEFINER */
/*!50001 VIEW `v_stats_globales` AS select (select count(0) from `entites` where ((`entites`.`type` = 'dossier') and (`entites`.`actif` = TRUE))) AS `total_dossiers`,(select count(0) from `entites` where ((`entites`.`type` = 'tiers') and (`entites`.`actif` = TRUE))) AS `total_tiers`,(select count(0) from `entites` where ((`entites`.`type` = 'contact') and (`entites`.`actif` = TRUE))) AS `total_contacts`,(select count(0) from `relations` where (`relations`.`actif` = TRUE)) AS `total_relations`,(select count(0) from `entites` where ((`entites`.`type` = 'dossier') and (`entites`.`actif` = TRUE) and (year(`entites`.`date_creation`) = year(curdate())) and (month(`entites`.`date_creation`) = month(curdate())))) AS `dossiers_ce_mois`,(select count(0) from (`entites` `t` left join `relations` `r` on(((`t`.`id` = `r`.`id_parent`) and (`r`.`type_relation` = 'tiers_contact') and (`r`.`actif` = TRUE)))) where ((`t`.`type` = 'tiers') and (`t`.`actif` = TRUE) and isnull(`r`.`id`))) AS `tiers_sans_contacts`,(select count(0) from (`entites` `c` left join `relations` `r` on(((`c`.`id` = `r`.`id_enfant`) and (`r`.`type_relation` = 'tiers_contact') and (`r`.`actif` = TRUE)))) where ((`c`.`type` = 'contact') and (`c`.`actif` = TRUE) and isnull(`r`.`id`))) AS `contacts_sans_tiers`,(select avg(`v_dossiers_stats`.`nb_tiers`) from `v_dossiers_stats` where (`v_dossiers_stats`.`actif` = TRUE)) AS `moyenne_tiers_par_dossier`,(select avg(`v_tiers_stats`.`nb_contacts`) from `v_tiers_stats` where (`v_tiers_stats`.`actif` = TRUE)) AS `moyenne_contacts_par_tiers` */;
/*!50001 SET character_set_client      = @saved_cs_client */;
/*!50001 SET character_set_results     = @saved_cs_results */;
/*!50001 SET collation_connection      = @saved_col_connection */;

--
-- Final view structure for view `v_tiers_stats`
--

/*!50001 DROP VIEW IF EXISTS `v_tiers_stats`*/;
/*!50001 SET @saved_cs_client          = @@character_set_client */;
/*!50001 SET @saved_cs_results         = @@character_set_results */;
/*!50001 SET @saved_col_connection     = @@collation_connection */;
/*!50001 SET character_set_client      = utf8mb4 */;
/*!50001 SET character_set_results     = utf8mb4 */;
/*!50001 SET collation_connection      = utf8mb4_unicode_ci */;
/*!50001 CREATE ALGORITHM=UNDEFINED */
/*!50013 DEFINER=`root`@`localhost` SQL SECURITY DEFINER */
/*!50001 VIEW `v_tiers_stats` AS select `t`.`id` AS `id`,`t`.`denomination` AS `denomination`,`t`.`date_creation` AS `date_creation`,`t`.`date_modification` AS `date_modification`,`t`.`actif` AS `actif`,count(distinct `rc`.`id_enfant`) AS `nb_contacts`,count(distinct `rd`.`id_parent`) AS `nb_dossiers` from ((`entites` `t` left join `relations` `rc` on(((`t`.`id` = `rc`.`id_parent`) and (`rc`.`type_relation` = 'tiers_contact') and (`rc`.`actif` = TRUE)))) left join `relations` `rd` on(((`t`.`id` = `rd`.`id_enfant`) and (`rd`.`type_relation` = 'dossier_tiers') and (`rd`.`actif` = TRUE)))) where (`t`.`type` = 'tiers') group by `t`.`id`,`t`.`denomination`,`t`.`date_creation`,`t`.`date_modification`,`t`.`actif` */;
/*!50001 SET character_set_client      = @saved_cs_client */;
/*!50001 SET character_set_results     = @saved_cs_results */;
/*!50001 SET collation_connection      = @saved_col_connection */;
/*!40103 SET TIME_ZONE=@OLD_TIME_ZONE */;

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;

-- Dump completed on 2025-06-24 16:55:12
