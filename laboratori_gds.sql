-- phpMyAdmin SQL Dump
-- version 5.2.0
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Creato il: Giu 27, 2023 alle 23:09
-- Versione del server: 10.4.27-MariaDB
-- Versione PHP: 8.2.0

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

-- --------------------------------------------------------

--
-- Struttura della tabella `codici`
--

CREATE TABLE `codici` (
  `codice` varchar(48) NOT NULL,
  `iscritto` int(11) NOT NULL,
  `id_settimana` int(11) NOT NULL,
  `expired` tinyint(1) NOT NULL DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dump dei dati per la tabella `codici`
--

INSERT INTO `codici` (`codice`, `iscritto`, `id_settimana`, `expired`) VALUES
('00000000-0000-0000-0000-222222222222', 0, 2, 0);

--
-- Trigger `codici`
--
DELIMITER $$
CREATE TRIGGER `before_insert_mytable` BEFORE INSERT ON `codici` FOR EACH ROW SET new.`codice` = BIN_TO_UUID(RANDOM_BYTES(16))
$$
DELIMITER ;

-- --------------------------------------------------------

--
-- Struttura della tabella `laboratori`
--

CREATE TABLE `laboratori` (
  `id` int(11) NOT NULL,
  `nome` varchar(32) NOT NULL,
  `descrizione` varchar(512) DEFAULT NULL,
  `gif` varchar(512) DEFAULT NULL,
  `posti` int(11) NOT NULL DEFAULT 10
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dump dei dati per la tabella `laboratori`
--

INSERT INTO `laboratori` (`id`, `nome`, `descrizione`, `gif`, `posti`) VALUES
(1, 'Pasta sale', 'Sale, farina e acqua… sono ingredienti che non mancano mai in una dispensa! La pasta sale è un’alternativa naturale alla plastilina, che ti permette di divertirti modellando e decorando le proprie creazioni. ', 'assets/img/gif/pasta sale.gif', 10),
(2, 'Braccialetti', 'Fili, elastici, perline e tanta voglia di fare sono gli unici ingredienti necessari per creare un bellissimo braccialetto. Potrai realizzare un gioiello per te stesso oppure portare un regalo fai da te ad amici e parenti. ', 'assets/img/gif/braccialetti.gif', 10),
(3, 'Pirografia', 'Prova anche tu una tecnica che, per mezzo di una fonte di calore, permette di incidere su una superficie di legno. Lasciati alle spalle i banali fogli di carta e iniziate a disegnare sul legno, così da essere più originali che mai. ', 'assets/img/gif/pirografia.gif', 10),
(4, 'Magliette tie dye', 'Sei stufi delle banali magliette bianche? In questo laboratorio potrai creare la tua maglietta come più ti piace, con colori sgargianti e fantasie alla moda.', 'assets/img/gif/magliette.gif', 10),
(5, 'Rompicapi', 'Tra tanti laboratori creativi ci vuole uno per mettere in gioco la propria intelligenza, cercando di risolvere rompicapi, dai più semplici ai più complessi.', 'assets/img/gif/rompicapi.gif', 10),
(6, 'Magliette tie dye', 'Se sei amante degli animali, in particolare degli insetti, devi sapere che al quinto piano del Rainerum ci sono decine di alveari! In questo laboratorio potrai entrare in contatto direttamente con le api e scoprire di più del loro fantastico mondo. ', 'assets/img/gif/apicoltura.gif', 10),
(7, 'Stecchini', 'Dopo aver mangiato un ghiacciolo siamo abituati a buttare lo stecchino, ma se ti dicessi che c’è un modo per utilizzarli in modo creativo? In questo laboratorio potrai creare moltissime costruzioni con gli stecchini!', 'assets/img/gif/stecchini.gif', 10),
(8, 'Oculus', 'Indossando l\'apposito oculare, potrai giocare e immergerti in un\'esperienza in realtà virtuale.', 'assets/img/gif/oculus.gif', 5),
(9, 'Giocoleria', 'Avete presenti i giocolieri che si esibiscono nei circhi? In questo laboratorio si può imparare ad esibirsi come loro, usando il piattino, il diablo e molto altro. ', 'assets/img/gif/giocoleria.gif', 10),
(10, 'Antistress', 'Creare antistress è un’attività molto utile, in vista dello stress che la vita può procurare. Inserendo riso oppure farina all’interno di un palloncino si può creare un oggetto morbido, da schiacciare nei momenti di stress. ', 'assets/img/gif/pirografia.gif', 10),
(11, 'Perline', 'Con delle perline di plastica si possono creare figure colorate e buffe. Successivamente, passandoci sopra il ferro da stiro, le creazioni si solidificano e si possono usare come decorazioni casalinghe.', 'assets/img/gif/perline.gif', 10),
(12, 'Acchiappasogni', 'Gli acchiappasogni sono creazioni associate alle tribù indigene del nord, con fili, piume e perline potrete creare il vostro e fare sogni tranquilli ogni notte!', 'assets/img/gif/acchiappasogni.gif', 10),
(13, 'Filografia', 'Scopri uno splendido modo di decorare una lastra di legno: si crea un disegno piantando alcuni chiodi e si fa passare il filo intorno ad essi. Al termine del lavoro otterrete un disegno colorato e originale.', 'assets/img/gif/filografia.gif', 10);

-- --------------------------------------------------------

--
-- Struttura della tabella `scelte`
--

CREATE TABLE `scelte` (
  `id` int(11) NOT NULL,
  `timestamp` datetime NOT NULL DEFAULT current_timestamp(),
  `codice` varchar(48) NOT NULL,
  `id_laboratorio` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dump dei dati per la tabella `scelte`
--

INSERT INTO `scelte` (`id`, `timestamp`, `codice`, `id_laboratorio`) VALUES
(18, '2023-06-27 22:59:29', '00000000-0000-0000-0000-222222222222', 13);

--
-- Trigger `scelte`
--
DELIMITER $$
CREATE TRIGGER `limite_per_codice` BEFORE INSERT ON `scelte` FOR EACH ROW BEGIN
DECLARE numero INTEGER;

SELECT COUNT(*) INTO numero FROM scelte
WHERE scelte.codice = NEW.codice;

IF numero >= 4 THEN
   SIGNAL SQLSTATE '45000' SET message_text = 'Sono già state espresse le preferenze per questo codice';
END IF;
END
$$
DELIMITER ;
DELIMITER $$
CREATE TRIGGER `limite_per_laboratorio` BEFORE INSERT ON `scelte` FOR EACH ROW BEGIN
DECLARE numero INTEGER;
DECLARE disponibili INTEGER;

SELECT COUNT(*) INTO numero FROM scelte
    INNER JOIN codici
WHERE codici.codice = scelte.codice
	AND scelte.id_laboratorio = NEW.id_laboratorio
    AND codici.id_settimana = (
    	SELECT codici.id_settimana FROM codici
        WHERE codici.codice = NEW.codice
    );

SELECT posti INTO disponibili FROM laboratori
WHERE laboratori.id = NEW.id_laboratorio; 

IF numero >= disponibili THEN
   SIGNAL SQLSTATE '45000' SET message_text = 'Il laboratorio scelto è già al completo.';
END IF;
END
$$
DELIMITER ;

-- --------------------------------------------------------

--
-- Struttura della tabella `settimane`
--

CREATE TABLE `settimane` (
  `id` int(11) NOT NULL,
  `nome` varchar(64) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

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
-- Indici per le tabelle `codici`
--
ALTER TABLE `codici`
  ADD PRIMARY KEY (`codice`),
  ADD UNIQUE KEY `iscritto` (`iscritto`,`id_settimana`),
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
  ADD UNIQUE KEY `codice_2` (`codice`,`id_laboratorio`),
  ADD KEY `codice` (`codice`),
  ADD KEY `scelte_laboratorio` (`id_laboratorio`) USING BTREE;

--
-- Indici per le tabelle `settimane`
--
ALTER TABLE `settimane`
  ADD PRIMARY KEY (`id`);

--
-- AUTO_INCREMENT per le tabelle scaricate
--

--
-- AUTO_INCREMENT per la tabella `laboratori`
--
ALTER TABLE `laboratori`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=26;

--
-- AUTO_INCREMENT per la tabella `scelte`
--
ALTER TABLE `scelte`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=19;

--
-- AUTO_INCREMENT per la tabella `settimane`
--
ALTER TABLE `settimane`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- Limiti per le tabelle scaricate
--

--
-- Limiti per la tabella `codici`
--
ALTER TABLE `codici`
  ADD CONSTRAINT `codice_settimana` FOREIGN KEY (`id_settimana`) REFERENCES `settimane` (`id`) ON UPDATE CASCADE;

--
-- Limiti per la tabella `scelte`
--
ALTER TABLE `scelte`
  ADD CONSTRAINT `scelta_codice` FOREIGN KEY (`codice`) REFERENCES `codici` (`codice`) ON UPDATE CASCADE,
  ADD CONSTRAINT `scelte_laboratorio` FOREIGN KEY (`id_laboratorio`) REFERENCES `laboratori` (`id`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
