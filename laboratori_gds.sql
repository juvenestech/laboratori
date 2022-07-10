-- phpMyAdmin SQL Dump
-- version 5.2.0
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Creato il: Giu 13, 2022 alle 00:23
-- Versione del server: 10.4.24-MariaDB
-- Versione PHP: 7.4.29

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `laboratori_gds`
--

DELIMITER $$
--
-- Funzioni
--
CREATE DEFINER=`root`@`localhost` FUNCTION `BIN_TO_UUID` (`b` BINARY(16)) RETURNS CHAR(36) CHARSET utf8mb4  BEGIN
   DECLARE hexStr CHAR(32);
   SET hexStr = HEX(b);
   RETURN LOWER(CONCAT(
        SUBSTR(hexStr, 1, 8), '-',
        SUBSTR(hexStr, 9, 4), '-',
        SUBSTR(hexStr, 13, 4), '-',
        SUBSTR(hexStr, 17, 4), '-',
        SUBSTR(hexStr, 21)
    ));
END$$

CREATE DEFINER=`root`@`localhost` FUNCTION `UUID_TO_BIN` (`uuid` CHAR(36)) RETURNS BINARY(16)  BEGIN
    RETURN UNHEX(REPLACE(uuid, '-', ''));
END$$

DELIMITER ;

-- --------------------------------------------------------

--
-- Struttura della tabella `attivita`
--

CREATE TABLE `attivita` (
  `id` int(11) NOT NULL,
  `luogo` varchar(64) NOT NULL,
  `posti` int(11) NOT NULL DEFAULT 8,
  `id_laboratorio` int(11) NOT NULL,
  `id_giorno` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Dump dei dati per la tabella `attivita`
--

INSERT INTO `attivita` (`id`, `luogo`, `posti`, `id_laboratorio`, `id_giorno`) VALUES
(1, 'Casa mia', 8, 1, 1),
(2, 'casa di sam', 8, 1, 2),
(3, 'A casa di marco', 8, 3, 3);

-- --------------------------------------------------------

--
-- Struttura della tabella `codici`
--

CREATE TABLE `codici` (
  `codice` varchar(48) NOT NULL DEFAULT uuid(),
  `iscritto` int(11) NOT NULL,
  `id_settimana` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Dump dei dati per la tabella `codici`
--

INSERT INTO `codici` (`codice`, `iscritto`, `id_settimana`) VALUES
('93b1e307-e39f-11ec-9ba7-40167e880aaf', 1, 1),
('ccf9837b-e3a0-11ec-9ba7-40167e880aaf', 2, 1),
('d1923ca5-e3a0-11ec-9ba7-40167e880aaf', 3, 1),
('c8b440bf-e3db-11ec-9ba7-40167e880aaf', 3, 2),
('ec362384-e3a0-11ec-9ba7-40167e880aaf', 32, 1);

-- --------------------------------------------------------

--
-- Struttura della tabella `giorni`
--

CREATE TABLE `giorni` (
  `id` int(11) NOT NULL,
  `nome` varchar(32) NOT NULL,
  `breve` varchar(10) NOT NULL,
  `durata` int(11) NOT NULL DEFAULT 1,
  `id_settimana` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Dump dei dati per la tabella `giorni`
--

INSERT INTO `giorni` (`id`, `nome`, `breve`, `durata`, `id_settimana`) VALUES
(1, 'Lunedì', 'LUN', 1, 1),
(2, 'Martedì', 'MAR', 1, 1),
(3, 'Giovedì', 'GIO', 1, 1),
(4, 'Venerdì', 'VEN', 1, 1),
(5, 'Lunedì e martedì', 'LUN - MAR', 2, 1),
(6, 'Giovedì e venerdì', 'GIO - VEN', 2, 1),
(7, 'Da lunedì a venerdì', 'LUN - VEN', 4, 1),
(8, 'Lunedì', 'LUN', 1, 2),
(9, 'Martedì', 'MAR', 1, 2),
(10, 'Giovedì', 'GIO', 1, 2),
(11, 'Venerdì', 'VEN', 1, 2),
(12, 'Lunedì e martedì', 'LUN - MAR', 2, 2),
(13, 'Giovedì e venerdì', 'GIO - VEN', 2, 2),
(14, 'Da lunedì a venerdì', 'LUN - VEN', 4, 2),
(15, 'Lunedì', 'LUN', 1, 3),
(16, 'Martedì', 'MAR', 1, 3),
(17, 'Giovedì', 'GIO', 1, 3),
(18, 'Venerdì', 'VEN', 1, 3),
(19, 'Lunedì e martedì', 'LUN - MAR', 2, 3),
(20, 'Giovedì e venerdì', 'GIO - VEN', 2, 3),
(21, 'Da lunedì a venerdì', 'LUN - VEN', 4, 3);

-- --------------------------------------------------------

--
-- Struttura della tabella `laboratori`
--

CREATE TABLE `laboratori` (
  `id` int(11) NOT NULL,
  `nome` varchar(32) NOT NULL,
  `descrizione` varchar(512) DEFAULT NULL,
  `gif` varchar(512) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Dump dei dati per la tabella `laboratori`
--

INSERT INTO `laboratori` (`id`, `nome`, `descrizione`, `gif`) VALUES
(1, 'Test', 'Primo laboratorio di test', 'https://drive.google.com/uc?export=view&id=1ed1RG56eHU_Wlc8mJ6N7Y8RhaREJVnba'),
(2, 'Non ci sono', 'Non c\'è la 1^ sett', NULL),
(3, 'Pulizia denti', 'Impariamo a lavarci i denti', 'https://drive.google.com/uc?export=view&id=1WRJ98YP8nTUHvELpxGdmJM4Dnv7TcBp_');

-- --------------------------------------------------------

--
-- Struttura della tabella `scelte`
--

CREATE TABLE `scelte` (
  `id` int(11) NOT NULL,
  `timestamp` datetime NOT NULL DEFAULT current_timestamp(),
  `id_laboratorio` int(11) NOT NULL,
  `codice` varchar(48) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Dump dei dati per la tabella `scelte`
--

INSERT INTO `scelte` (`id`, `timestamp`, `id_laboratorio`, `codice`) VALUES
(8, '2022-06-04 16:11:53', 1, '93b1e307-e39f-11ec-9ba7-40167e880aaf'),
(9, '2022-06-04 16:11:53', 3, '93b1e307-e39f-11ec-9ba7-40167e880aaf');

-- --------------------------------------------------------

--
-- Struttura della tabella `settimane`
--

CREATE TABLE `settimane` (
  `id` int(11) NOT NULL,
  `nome` varchar(64) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Dump dei dati per la tabella `settimane`
--

INSERT INTO `settimane` (`id`, `nome`) VALUES
(1, 'Prima Settimana'),
(2, 'Seconda Settimana'),
(3, 'Terza Settimana');

--
-- Indici per le tabelle scaricate
--

--
-- Indici per le tabelle `attivita`
--
ALTER TABLE `attivita`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `id_laboratorio_2` (`id_laboratorio`,`id_giorno`),
  ADD KEY `id_laboratorio` (`id_laboratorio`),
  ADD KEY `id_giorno` (`id_giorno`);

--
-- Indici per le tabelle `codici`
--
ALTER TABLE `codici`
  ADD PRIMARY KEY (`codice`),
  ADD UNIQUE KEY `iscritto` (`iscritto`,`id_settimana`),
  ADD KEY `id_settimana` (`id_settimana`);

--
-- Indici per le tabelle `giorni`
--
ALTER TABLE `giorni`
  ADD PRIMARY KEY (`id`),
  ADD KEY `id_settimana` (`id_settimana`);

--
-- Indici per le tabelle `laboratori`
--
ALTER TABLE `laboratori`
  ADD PRIMARY KEY (`id`);

--
-- Indici per le tabelle `scelte`
--
ALTER TABLE `scelte`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `id_laboratorio` (`id_laboratorio`,`codice`),
  ADD KEY `id_attivita` (`id_laboratorio`),
  ADD KEY `codice` (`codice`);

--
-- Indici per le tabelle `settimane`
--
ALTER TABLE `settimane`
  ADD PRIMARY KEY (`id`);

--
-- AUTO_INCREMENT per le tabelle scaricate
--

--
-- AUTO_INCREMENT per la tabella `attivita`
--
ALTER TABLE `attivita`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT per la tabella `giorni`
--
ALTER TABLE `giorni`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=22;

--
-- AUTO_INCREMENT per la tabella `laboratori`
--
ALTER TABLE `laboratori`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT per la tabella `scelte`
--
ALTER TABLE `scelte`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=13;

--
-- AUTO_INCREMENT per la tabella `settimane`
--
ALTER TABLE `settimane`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- Limiti per le tabelle scaricate
--

--
-- Limiti per la tabella `attivita`
--
ALTER TABLE `attivita`
  ADD CONSTRAINT `attivita_giorno` FOREIGN KEY (`id_giorno`) REFERENCES `giorni` (`id`),
  ADD CONSTRAINT `attivita_laboratorio` FOREIGN KEY (`id_laboratorio`) REFERENCES `laboratori` (`id`) ON DELETE NO ACTION ON UPDATE CASCADE;

--
-- Limiti per la tabella `codici`
--
ALTER TABLE `codici`
  ADD CONSTRAINT `codice_settimana` FOREIGN KEY (`id_settimana`) REFERENCES `settimane` (`id`) ON UPDATE CASCADE;

--
-- Limiti per la tabella `giorni`
--
ALTER TABLE `giorni`
  ADD CONSTRAINT `giorno_settimana` FOREIGN KEY (`id_settimana`) REFERENCES `settimane` (`id`) ON UPDATE CASCADE;

--
-- Limiti per la tabella `scelte`
--
ALTER TABLE `scelte`
  ADD CONSTRAINT `scelta_codice` FOREIGN KEY (`codice`) REFERENCES `codici` (`codice`) ON UPDATE CASCADE,
  ADD CONSTRAINT `scelta_laboratorio` FOREIGN KEY (`id_laboratorio`) REFERENCES `laboratori` (`id`) ON UPDATE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
