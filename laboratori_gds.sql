-- MySQL dump 10.13  Distrib 5.5.62, for Win64 (AMD64)
--
-- Host: 127.0.0.1    Database: laboratori_gds
-- ------------------------------------------------------
-- Server version	5.7.42-0ubuntu0.18.04.1

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
-- Table structure for table `attivita`
--

/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `attivita` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `luogo` varchar(64) NOT NULL,
  `posti` int(11) NOT NULL DEFAULT '8',
  `id_laboratorio` int(11) NOT NULL,
  `id_giorno` int(11) NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `id_laboratorio_2` (`id_laboratorio`,`id_giorno`),
  KEY `id_laboratorio` (`id_laboratorio`),
  KEY `id_giorno` (`id_giorno`),
  CONSTRAINT `attivita_giorno` FOREIGN KEY (`id_giorno`) REFERENCES `giorni` (`id`),
  CONSTRAINT `attivita_laboratorio` FOREIGN KEY (`id_laboratorio`) REFERENCES `laboratori` (`id`) ON DELETE NO ACTION ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `attivita`
--

LOCK TABLES `attivita` WRITE;
/*!40000 ALTER TABLE `attivita` DISABLE KEYS */;
/*!40000 ALTER TABLE `attivita` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `laboratori`
--

/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `laboratori` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `nome` varchar(32) NOT NULL,
  `descrizione` varchar(512) DEFAULT NULL,
  `gif` varchar(512) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=23 DEFAULT CHARSET=utf8mb4;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `laboratori`
--

LOCK TABLES `laboratori` WRITE;
/*!40000 ALTER TABLE `laboratori` DISABLE KEYS */;
INSERT INTO `laboratori` VALUES (1,'Braccialetti','Crea braccialetti colorati con piccoli elastici o perline.','assets/img/gif/braccialetti.gif');
INSERT INTO `laboratori` VALUES (2,'Antistress','Crea i tuoi antistress riempiendo palloncini con farina o riso. Poi decorali con pennarelli e fili colorati.','assets/img/gif/antistress.gif');
INSERT INTO `laboratori` VALUES (3,'Vetrate di carta','Intaglia un cartoncino creando una figura, poi applica la carta velina per completare la vetrata.','assets/img/gif/vetrate di carta.gif');
INSERT INTO `laboratori` VALUES (4,'Disegni nascosti','Ricopri con due strati di cera un cartoncino e, con un bastoncino, rimuovi il primo strato. In questo modo farai apparire un fantastico disegno.','assets/img/gif/disegni nascosti.gif');
INSERT INTO `laboratori` VALUES (5,'Glitter art','Disegna una figura sul foglio e ricoprila di colla. Poi cospargila con i glitter per rendere scintillante il tuo disegno. ','assets/img/gif/glitter art.gif');
INSERT INTO `laboratori` VALUES (6,'Origami',NULL,'assets/img/gif/origami.gif');
INSERT INTO `laboratori` VALUES (7,'Pasta sale','Unendo sale, farina e acqua crea una pasta. Modella la forma che preferisci e, dopo che si sarà seccata, colorala. In questo modo otterrai una fantastica statuina.','assets/img/gif/pasta sale.gif');
INSERT INTO `laboratori` VALUES (8,'Slime',' In una ciotola mescola diversi ingredienti per creare lo slime perfetto. ','assets/img/gif/slime.gif');
INSERT INTO `laboratori` VALUES (9,'Rompicapi','Divertiti e stimola la tua logica cercando di risolvere rompicapi di legno e metallo. ','assets/img/gif/rompicapi.gif');
INSERT INTO `laboratori` VALUES (10,'Magliette tie dye','Arrotola la tua maglietta, fermandola con degli elastici. Poi colorala con diversi coloranti. Infine srotola la tua maglia per scoprire il pattern che hai creato. ','assets/img/gif/magliette tie-dye.gif');
INSERT INTO `laboratori` VALUES (11,'Filografia','Impianta dei chiodini su una  tavoletta di legno e uniscili con del filo. In questo modo potrai creare fantastici disegni.','assets/img/gif/filografia.gif');
INSERT INTO `laboratori` VALUES (12,'Just Dance',NULL,'assets/img/gif/just dance.gif');
INSERT INTO `laboratori` VALUES (13,'Perline','Su un apposito telaio posiziona le perline colorate in modo da formare dei disegni. Con il ferro da stiro le perline vengono sciolte e si saldano insieme, così potrai ottenere colorate decorazioni. ','assets/img/gif/perline.gif');
INSERT INTO `laboratori` VALUES (14,'Robotica','Divertiti ad assemblare e programmare un robot in lego.','assets/img/gif/robotica.gif');
INSERT INTO `laboratori` VALUES (15,'Oculus','Indossando l\'apposito oculare, divertiti a visitare un mondo virtuale.','assets/img/gif/oculus.gif');
INSERT INTO `laboratori` VALUES (16,'Esperimenti scientifici',NULL,NULL);
INSERT INTO `laboratori` VALUES (17,'Giocoleria','Prova anche tu a l\'arte della giocoleria! Diabli, palline, trampoli e molto altro!','assets/img/gif/giocoleria.gif');
INSERT INTO `laboratori` VALUES (18,'Pirografia','Con il pirografo incidi una tavoletta di legno per creare un bellissimi disegni.','assets/img/gif/pirografia.gif');
INSERT INTO `laboratori` VALUES (19,'Apicoltura','Divertiti tra le arnie e scopri tutto sulle api insieme ad esperti.','assets/img/gif/apicoltura.gif');
INSERT INTO `laboratori` VALUES (20,'Traforo','Crea da delle seplici tavolette di legno dei fantastici lavoretti in legno!','assets/img/gif/traforo.gif');
INSERT INTO `laboratori` VALUES (21,'Mosaici','Tassello dopo tassello, crea dei fantastici mosaici colorati!','assets/img/gif/mosaici.gif');
INSERT INTO `laboratori` VALUES (22,'Cucito','Impara a cucire con le nostre appasionatissime animatrici!','assets/img/gif/cucito.gif');
/*!40000 ALTER TABLE `laboratori` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `codici`
--

/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `codici` (
  `codice` varchar(48) NOT NULL,
  `iscritto` int(11) NOT NULL,
  `id_settimana` int(11) NOT NULL,
  `expired` tinyint(1) NOT NULL DEFAULT '0',
  PRIMARY KEY (`codice`),
  UNIQUE KEY `iscritto` (`iscritto`,`id_settimana`),
  KEY `id_settimana` (`id_settimana`),
  CONSTRAINT `codice_settimana` FOREIGN KEY (`id_settimana`) REFERENCES `settimane` (`id`) ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `codici`
--

LOCK TABLES `codici` WRITE;
/*!40000 ALTER TABLE `codici` DISABLE KEYS */;
INSERT INTO `codici` VALUES ('00000000-0000-0000-0000-222222222222',0,2,0);
/*!40000 ALTER TABLE `codici` ENABLE KEYS */;
UNLOCK TABLES;
/*!50003 SET @saved_cs_client      = @@character_set_client */ ;
/*!50003 SET @saved_cs_results     = @@character_set_results */ ;
/*!50003 SET @saved_col_connection = @@collation_connection */ ;
/*!50003 SET character_set_client  = utf8mb4 */ ;
/*!50003 SET character_set_results = utf8mb4 */ ;
/*!50003 SET collation_connection  = utf8mb4_general_ci */ ;
/*!50003 SET @saved_sql_mode       = @@sql_mode */ ;
/*!50003 SET sql_mode              = 'ONLY_FULL_GROUP_BY,STRICT_TRANS_TABLES,NO_ZERO_IN_DATE,NO_ZERO_DATE,ERROR_FOR_DIVISION_BY_ZERO,NO_AUTO_CREATE_USER,NO_ENGINE_SUBSTITUTION' */ ;
DELIMITER ;;
/*!50003 CREATE*/ /*!50017 DEFINER=`kevin`@`localhost`*/ /*!50003 TRIGGER before_insert_mytable

  BEFORE INSERT ON `codici`

  FOR EACH ROW

  SET new.`codice` = BIN_TO_UUID(RANDOM_BYTES(16)) */;;
DELIMITER ;
/*!50003 SET sql_mode              = @saved_sql_mode */ ;
/*!50003 SET character_set_client  = @saved_cs_client */ ;
/*!50003 SET character_set_results = @saved_cs_results */ ;
/*!50003 SET collation_connection  = @saved_col_connection */ ;

--
-- Table structure for table `giorni`
--

/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `giorni` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `nome` varchar(32) NOT NULL,
  `breve` varchar(10) NOT NULL,
  `id_settimana` int(11) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `id_settimana` (`id_settimana`),
  CONSTRAINT `giorno_settimana` FOREIGN KEY (`id_settimana`) REFERENCES `settimane` (`id`) ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=27 DEFAULT CHARSET=utf8mb4;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `giorni`
--

LOCK TABLES `giorni` WRITE;
/*!40000 ALTER TABLE `giorni` DISABLE KEYS */;
INSERT INTO `giorni` VALUES (1,'Lunedì','LUN',1);
INSERT INTO `giorni` VALUES (2,'Martedì','MAR',1);
INSERT INTO `giorni` VALUES (3,'Giovedì','GIO',1);
INSERT INTO `giorni` VALUES (4,'Venerdì','VEN',1);
INSERT INTO `giorni` VALUES (5,'Lunedì','LUN',2);
INSERT INTO `giorni` VALUES (6,'Martedì','MAR',2);
INSERT INTO `giorni` VALUES (7,'Giovedì','GIO',2);
INSERT INTO `giorni` VALUES (8,'Venerdì','VEN',2);
INSERT INTO `giorni` VALUES (9,'Lunedì','LUN',3);
INSERT INTO `giorni` VALUES (10,'Martedì','MAR',3);
INSERT INTO `giorni` VALUES (11,'Giovedì','GIO',3);
INSERT INTO `giorni` VALUES (12,'Venerdì','VEN',3);
/*!40000 ALTER TABLE `giorni` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `settimane`
--

/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `settimane` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `nome` varchar(64) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8mb4;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `settimane`
--

LOCK TABLES `settimane` WRITE;
/*!40000 ALTER TABLE `settimane` DISABLE KEYS */;
INSERT INTO `settimane` VALUES (1,'Prima Settimana');
INSERT INTO `settimane` VALUES (2,'Seconda Settimana');
INSERT INTO `settimane` VALUES (3,'Terza Settimana');
/*!40000 ALTER TABLE `settimane` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `scelte`
--

/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `scelte` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `timestamp` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `codice` varchar(48) NOT NULL,
  `id_attivita` int(11) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `codice` (`codice`),
  KEY `scelte_attivita` (`id_attivita`),
  CONSTRAINT `scelta_codice` FOREIGN KEY (`codice`) REFERENCES `codici` (`codice`) ON UPDATE CASCADE,
  CONSTRAINT `scelte_attivita` FOREIGN KEY (`id_attivita`) REFERENCES `attivita` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `scelte`
--

LOCK TABLES `scelte` WRITE;
/*!40000 ALTER TABLE `scelte` DISABLE KEYS */;
/*!40000 ALTER TABLE `scelte` ENABLE KEYS */;
UNLOCK TABLES;
/*!40103 SET TIME_ZONE=@OLD_TIME_ZONE */;

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;

-- Dump completed on 2023-06-26 18:16:08
