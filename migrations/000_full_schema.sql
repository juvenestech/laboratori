-- =============================================
-- Schema completo: laboratori_gds
-- MySQL 8 — Drop & Recreate
-- =============================================

SET FOREIGN_KEY_CHECKS = 0;
SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";

-- Drop tabelle (ordine inverso di dipendenze)
DROP TABLE IF EXISTS `scelte`;
DROP TABLE IF EXISTS `codici`;
DROP TABLE IF EXISTS `laboratori`;
DROP TABLE IF EXISTS `categorie`;
DROP TABLE IF EXISTS `settimane`;
DROP TABLE IF EXISTS `edizioni`;

-- Drop trigger (se esistono)
DROP TRIGGER IF EXISTS `uuid_codici`;
DROP TRIGGER IF EXISTS `limite_per_codice`;
DROP TRIGGER IF EXISTS `limite_per_laboratorio`;

SET FOREIGN_KEY_CHECKS = 1;

-- =============================================
-- 1. EDIZIONI
-- =============================================
CREATE TABLE `edizioni` (
  `id` int NOT NULL AUTO_INCREMENT,
  `anno` int NOT NULL,
  `nome` varchar(128) NOT NULL,
  `is_active` tinyint(1) NOT NULL DEFAULT 0,
  PRIMARY KEY (`id`),
  UNIQUE KEY `anno_nome` (`anno`, `nome`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- =============================================
-- 2. SETTIMANE
-- =============================================
CREATE TABLE `settimane` (
  `id` int NOT NULL AUTO_INCREMENT,
  `nome` varchar(64) NOT NULL,
  `id_edizione` int NOT NULL,
  PRIMARY KEY (`id`),
  KEY `settimana_edizione` (`id_edizione`),
  CONSTRAINT `settimana_edizione` FOREIGN KEY (`id_edizione`) REFERENCES `edizioni` (`id`) ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- =============================================
-- 3. CATEGORIE
-- =============================================
CREATE TABLE `categorie` (
  `id` int NOT NULL AUTO_INCREMENT,
  `nome` varchar(64) NOT NULL,
  `max_scelte` int NOT NULL DEFAULT 5,
  `descrizione` varchar(512) DEFAULT NULL,
  `id_edizione` int NOT NULL,
  PRIMARY KEY (`id`),
  KEY `categoria_edizione` (`id_edizione`),
  CONSTRAINT `categoria_edizione` FOREIGN KEY (`id_edizione`) REFERENCES `edizioni` (`id`) ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- =============================================
-- 4. LABORATORI
-- =============================================
CREATE TABLE `laboratori` (
  `id` int NOT NULL AUTO_INCREMENT,
  `nome` varchar(32) NOT NULL,
  `descrizione` varchar(512) DEFAULT NULL,
  `gif` varchar(512) DEFAULT NULL,
  `posti` int NOT NULL DEFAULT 40,
  `id_categoria` int DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `laboratorio_categoria` (`id_categoria`),
  CONSTRAINT `laboratorio_categoria` FOREIGN KEY (`id_categoria`) REFERENCES `categorie` (`id`) ON UPDATE CASCADE ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- =============================================
-- 5. CODICI
-- =============================================
CREATE TABLE `codici` (
  `codice` varchar(48) NOT NULL,
  `iscritto` int NOT NULL,
  `id_settimana` int NOT NULL,
  `expired` tinyint(1) NOT NULL DEFAULT 0,
  PRIMARY KEY (`codice`),
  UNIQUE KEY `iscritto` (`iscritto`, `id_settimana`),
  KEY `id_settimana` (`id_settimana`),
  CONSTRAINT `codice_settimana` FOREIGN KEY (`id_settimana`) REFERENCES `settimane` (`id`) ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Trigger: genera UUID automatico per codice
DELIMITER $$
CREATE TRIGGER `uuid_codici` BEFORE INSERT ON `codici` FOR EACH ROW
BEGIN
  IF NEW.codice IS NULL OR NEW.codice = '' THEN
    SET NEW.codice = UUID();
  END IF;
END$$
DELIMITER ;

-- =============================================
-- 6. SCELTE
-- =============================================
CREATE TABLE `scelte` (
  `id` int NOT NULL AUTO_INCREMENT,
  `timestamp` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `codice` varchar(48) NOT NULL,
  `id_laboratorio` int NOT NULL,
  `ordine` int DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `codice_2` (`codice`, `id_laboratorio`),
  KEY `codice` (`codice`),
  KEY `scelte_laboratorio` (`id_laboratorio`),
  CONSTRAINT `scelta_codice` FOREIGN KEY (`codice`) REFERENCES `codici` (`codice`) ON UPDATE CASCADE,
  CONSTRAINT `scelte_laboratorio` FOREIGN KEY (`id_laboratorio`) REFERENCES `laboratori` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Trigger: limite scelte per categoria
DELIMITER $$
CREATE TRIGGER `limite_per_codice` BEFORE INSERT ON `scelte` FOR EACH ROW
BEGIN
  DECLARE numero INT;
  DECLARE limite INT;
  DECLARE cat_id INT;

  -- Trova la categoria del laboratorio richiesto
  SELECT `id_categoria` INTO cat_id FROM `laboratori` WHERE `id` = NEW.id_laboratorio;

  -- Conta le scelte già fatte per questa categoria
  SELECT COUNT(*) INTO numero FROM `scelte`
    INNER JOIN `laboratori` ON `scelte`.`id_laboratorio` = `laboratori`.`id`
  WHERE `scelte`.`codice` = NEW.codice
    AND `laboratori`.`id_categoria` = cat_id;

  -- Legge il limite dalla categoria
  SELECT `max_scelte` INTO limite FROM `categorie` WHERE `id` = cat_id;

  IF limite IS NOT NULL AND numero >= limite THEN
    SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'Raggiunto il limite di scelte per questa categoria';
  END IF;
END$$
DELIMITER ;

-- Trigger: limite posti per laboratorio
DELIMITER $$
CREATE TRIGGER `limite_per_laboratorio` BEFORE INSERT ON `scelte` FOR EACH ROW
BEGIN
  DECLARE numero INT;
  DECLARE disponibili INT;

  SELECT COUNT(*) INTO numero FROM `scelte`
    INNER JOIN `codici` ON `codici`.`codice` = `scelte`.`codice`
  WHERE `scelte`.`id_laboratorio` = NEW.id_laboratorio
    AND `codici`.`id_settimana` = (
      SELECT `codici`.`id_settimana` FROM `codici`
      WHERE `codici`.`codice` = NEW.codice
    );

  SELECT `posti` INTO disponibili FROM `laboratori`
  WHERE `laboratori`.`id` = NEW.id_laboratorio;

  IF numero >= disponibili THEN
    SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'Il laboratorio scelto è già al completo.';
  END IF;
END$$
DELIMITER ;

-- =============================================
-- PERMESSI
-- =============================================
GRANT SELECT, INSERT, UPDATE, DELETE ON `laboratori_gds`.* TO 'php'@'localhost';
FLUSH PRIVILEGES;
