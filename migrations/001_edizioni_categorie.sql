-- =============================================
-- Migrazione: Edizioni, Categorie, Ordine scelte
-- Eseguire su database laboratori_gds esistente
-- =============================================

-- 1. Tabella edizioni (§2)
CREATE TABLE IF NOT EXISTS `edizioni` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `anno` int(4) NOT NULL,
  `nome` varchar(128) NOT NULL,
  `is_active` tinyint(1) NOT NULL DEFAULT 0,
  PRIMARY KEY (`id`),
  UNIQUE KEY `anno_nome` (`anno`, `nome`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Inserimento edizione di default per i dati esistenti
INSERT INTO `edizioni` (`anno`, `nome`, `is_active`) VALUES (2023, 'Giorni del Sole 2023', 0);
INSERT INTO `edizioni` (`anno`, `nome`, `is_active`) VALUES (2026, 'Giorni del Sole 2026', 1);

-- 2. Aggiunta FK id_edizione su settimane (§2)
ALTER TABLE `settimane`
  ADD COLUMN `id_edizione` int(11) NOT NULL DEFAULT 1 AFTER `nome`;

ALTER TABLE `settimane`
  ADD CONSTRAINT `settimana_edizione` FOREIGN KEY (`id_edizione`) REFERENCES `edizioni` (`id`) ON UPDATE CASCADE;

-- Associa le settimane esistenti all'edizione 2023
UPDATE `settimane` SET `id_edizione` = 1;

-- 3. Tabella categorie (§3)
CREATE TABLE IF NOT EXISTS `categorie` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `nome` varchar(64) NOT NULL,
  `max_scelte` int(11) NOT NULL DEFAULT 5,
  `descrizione` varchar(512) DEFAULT NULL,
  `id_edizione` int(11) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `categoria_edizione` (`id_edizione`),
  CONSTRAINT `categoria_edizione` FOREIGN KEY (`id_edizione`) REFERENCES `edizioni` (`id`) ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Categoria di default per i laboratori esistenti
INSERT INTO `categorie` (`nome`, `max_scelte`, `descrizione`, `id_edizione`) VALUES ('Laboratori Manuali', 5, 'Laboratori creativi e manuali', 1);

-- 4. Aggiunta FK id_categoria su laboratori (§3)
ALTER TABLE `laboratori`
  ADD COLUMN `id_categoria` int(11) DEFAULT NULL AFTER `posti`;

ALTER TABLE `laboratori`
  ADD CONSTRAINT `laboratorio_categoria` FOREIGN KEY (`id_categoria`) REFERENCES `categorie` (`id`) ON UPDATE CASCADE ON DELETE SET NULL;

-- Associa i laboratori esistenti alla categoria default
UPDATE `laboratori` SET `id_categoria` = 1;

-- 5. Aggiunta colonna ordine su scelte (§1, §5)
ALTER TABLE `scelte`
  ADD COLUMN `ordine` int(11) DEFAULT NULL AFTER `id_laboratorio`;

-- 6. Aggiornamento trigger limite_per_codice per supporto categorie dinamiche (§3)
DROP TRIGGER IF EXISTS `limite_per_codice`;

DELIMITER $$
CREATE TRIGGER `limite_per_codice` BEFORE INSERT ON `scelte` FOR EACH ROW
BEGIN
  DECLARE numero INTEGER;
  DECLARE limite INTEGER;
  DECLARE cat_id INTEGER;

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
    SIGNAL SQLSTATE '45000' SET message_text = 'Raggiunto il limite di scelte per questa categoria';
  END IF;
END
$$
DELIMITER ;

-- Il trigger limite_per_laboratorio resta invariato (controlla i posti disponibili)
