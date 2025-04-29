-- MySQL dump 10.13  Distrib 5.5.62, for Linux (x86_64)
--
-- Host: localhost    Database: fly
-- ------------------------------------------------------
-- Server version	5.5.62-log

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
-- Table structure for table `Calendar`
--

DROP TABLE IF EXISTS `Calendar`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `Calendar` (
  `CalendarID` int(11) NOT NULL AUTO_INCREMENT,
  `date` datetime NOT NULL,
  `etype` enum('FLYING','SPECIAL','PLANE') NOT NULL DEFAULT 'FLYING',
  `title` char(80) NOT NULL DEFAULT 'n/a',
  `notes` text NOT NULL,
  PRIMARY KEY (`CalendarID`)
) ENGINE=InnoDB AUTO_INCREMENT=736 DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `Calendar`
--

LOCK TABLES `Calendar` WRITE;
/*!40000 ALTER TABLE `Calendar` DISABLE KEYS */;
/*!40000 ALTER TABLE `Calendar` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `Duty`
--

DROP TABLE IF EXISTS `Duty`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `Duty` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `CalendarID` int(11) NOT NULL,
  `MemberID` int(11) NOT NULL,
  `type` enum('OPS','TOW','CFIG','COORD','HELP','SCHEDA','SCHEDB','BOARD','CREW') NOT NULL DEFAULT 'HELP',
  `ack` tinyint(4) NOT NULL DEFAULT '0',
  `ackCode` char(40) NOT NULL DEFAULT '0',
  `ackSuper` char(40) NOT NULL DEFAULT '0',
  `notes` text NOT NULL,
  PRIMARY KEY (`id`),
  KEY `fk_cal` (`CalendarID`),
  CONSTRAINT `fk_cal` FOREIGN KEY (`CalendarID`) REFERENCES `Calendar` (`CalendarID`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=11702 DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `Duty`
--

LOCK TABLES `Duty` WRITE;
/*!40000 ALTER TABLE `Duty` DISABLE KEYS */;
/*!40000 ALTER TABLE `Duty` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `EventLog`
--

DROP TABLE IF EXISTS `EventLog`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `EventLog` (
  `time` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `type` enum('DELETE','ERROR','INFO','INSERT','LOGIN_FAIL','LOGIN_FORGOT','LOGIN_OKAY','PHOTO','DEBUG','UPDATE','USER_MSG','WARN') NOT NULL,
  `details` varchar(200) NOT NULL DEFAULT ' ',
  `subID` int(11) NOT NULL,
  `ipAddr` char(15) NOT NULL,
  `url` char(80) NOT NULL
) ENGINE=MyISAM DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `EventLog`
--

LOCK TABLES `EventLog` WRITE;
/*!40000 ALTER TABLE `EventLog` DISABLE KEYS */;
/*!40000 ALTER TABLE `EventLog` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `Glider`
--

DROP TABLE IF EXISTS `Glider`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `Glider` (
  `GliderID` int(11) NOT NULL AUTO_INCREMENT,
  `mfg` char(20) NOT NULL,
  `model` char(20) NOT NULL,
  `type` enum('Glider','Tow','Other') NOT NULL DEFAULT 'Glider',
  `vne` char(20) NOT NULL,
  `vrough` char(20) NOT NULL,
  `vfinal` char(20) NOT NULL,
  `vglide` char(20) NOT NULL,
  `vsink` char(20) NOT NULL,
  `vstall` char(20) NOT NULL,
  `glideRatio` char(20) NOT NULL,
  `maxG` char(20) NOT NULL,
  `descrip` text NOT NULL,
  `photo` char(20) NOT NULL DEFAULT 'glider_0.jpg',
  `pohURL` char(120) NOT NULL DEFAULT 'http://flsc.org/Links.php#poh',
  `seats` tinyint(4) NOT NULL DEFAULT '1',
  PRIMARY KEY (`GliderID`),
  UNIQUE KEY `GliderID` (`GliderID`),
  UNIQUE KEY `nameIndex` (`model`)
) ENGINE=InnoDB AUTO_INCREMENT=11 DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `Glider`
--

LOCK TABLES `Glider` WRITE;
/*!40000 ALTER TABLE `Glider` DISABLE KEYS */;
/*!40000 ALTER TABLE `Glider` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `GliderStatus`
--

DROP TABLE IF EXISTS `GliderStatus`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `GliderStatus` (
  `tailNum` char(20) NOT NULL,
  `GliderID` int(11) NOT NULL,
  `regExpire` date NOT NULL,
  `annualExpire` date NOT NULL,
  `status` enum('GREEN','ORANGE','RED') NOT NULL DEFAULT 'GREEN',
  `mxNotes` text NOT NULL,
  `updated` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `updatedBy` mediumint(9) NOT NULL,
  PRIMARY KEY (`tailNum`),
  UNIQUE KEY `tailIndex` (`tailNum`),
  KEY `fk_gid` (`GliderID`),
  KEY `fk_memberID` (`updatedBy`),
  CONSTRAINT `fk_gid` FOREIGN KEY (`GliderID`) REFERENCES `Glider` (`GliderID`) ON DELETE CASCADE,
  CONSTRAINT `fk_memberID` FOREIGN KEY (`updatedBy`) REFERENCES `Members` (`MemberID`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `GliderStatus`
--

LOCK TABLES `GliderStatus` WRITE;
/*!40000 ALTER TABLE `GliderStatus` DISABLE KEYS */;
/*!40000 ALTER TABLE `GliderStatus` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `GliderStatusLog`
--

DROP TABLE IF EXISTS `GliderStatusLog`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `GliderStatusLog` (
  `GliderID` int(11) NOT NULL,
  `tailNum` char(20) NOT NULL,
  `regExpire` date NOT NULL,
  `annualExpire` date NOT NULL,
  `status` enum('GREEN','ORANGE','RED') NOT NULL DEFAULT 'GREEN',
  `mxNotes` text NOT NULL,
  `updated` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `updatedBy` mediumint(9) NOT NULL,
  KEY `fk_gid` (`GliderID`),
  KEY `fk_memberID` (`updatedBy`),
  KEY `tailIdx` (`tailNum`),
  CONSTRAINT `fkLog_gid` FOREIGN KEY (`GliderID`) REFERENCES `Glider` (`GliderID`) ON DELETE CASCADE,
  CONSTRAINT `fkLog_memberID` FOREIGN KEY (`updatedBy`) REFERENCES `Members` (`MemberID`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `GliderStatusLog`
--

LOCK TABLES `GliderStatusLog` WRITE;
/*!40000 ALTER TABLE `GliderStatusLog` DISABLE KEYS */;
/*!40000 ALTER TABLE `GliderStatusLog` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `Members`
--

DROP TABLE IF EXISTS `Members`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `Members` (
  `MemberID` mediumint(9) NOT NULL AUTO_INCREMENT,
  `emailAddr` varchar(50) DEFAULT NULL,
  `lastName` varchar(30) NOT NULL,
  `firstName` varchar(30) NOT NULL,
  `memberType` enum('Regular','Special Tow Pilot','Social','Junior','Distant','Emeritur','Other','Inactive','None') NOT NULL,
  `pilot` enum('Pre Solo','Private','Commercial','Post Solo','Not Applicable','CFI-G') NOT NULL,
  `otherPilot` set('SEL','MEL','Commercial SEL','Commercial MEL','CFI','CFII','ATP','Instrument','Tail Wheel','Tow','None') NOT NULL DEFAULT 'None',
  `checkout` set('L13AC','L13AC_back','ASK21','ASK21_back','Grobe102','SGS_1-26','SGS_2-33','SGS_2-33_Back','Citabria','Pawnee','Ground_launch') NOT NULL,
  `access` set('ADMIN','MEMBER','SCHEDULES','NONE','CFIG','BOARD','TOW') NOT NULL DEFAULT 'MEMBER',
  `coord` set('OPS','TOW','CFIG') NOT NULL,
  `badge` set('A','B','C','Bronze','Silver','Silver Altitude','Silver Distance','Silver Duration','Gold','Gold Altitude','Gold Distance','Diamond','Diamond Altitude','Diamond Distance','Diamond Goal','750K Diploma','1000K Diploma','2000K Diploma','None') NOT NULL DEFAULT 'None',
  `password` varchar(40) DEFAULT NULL,
  `reset` char(40) DEFAULT 'n/a',
  `photo` char(80) NOT NULL DEFAULT 'no_photo.png',
  `family` varchar(30) DEFAULT NULL,
  `middleName` varchar(30) DEFAULT NULL,
  `street` varchar(30) NOT NULL,
  `city` varchar(30) NOT NULL,
  `state` char(20) NOT NULL DEFAULT '',
  `zip` varchar(10) NOT NULL,
  `phone` varchar(30) NOT NULL,
  `phone2` varchar(30) DEFAULT NULL,
  `lastUpdate` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `lastIP` char(20) NOT NULL DEFAULT 'n/a',
  `lastActive` datetime NOT NULL DEFAULT '2000-01-01 00:00:00',
  `keepLogin` char(60) NOT NULL DEFAULT '',
  `mentorID` mediumint(9) DEFAULT '0',
  `memberSince` char(4) NOT NULL DEFAULT '',
  `towMed` date NOT NULL,
  `bfr` date NOT NULL,
  `suffix` char(4) NOT NULL,
  `notes` text NOT NULL,
  `about` text NOT NULL,
  PRIMARY KEY (`MemberID`),
  UNIQUE KEY `emailAddr` (`emailAddr`),
  UNIQUE KEY `nameIndex` (`firstName`,`middleName`,`lastName`,`suffix`)
) ENGINE=InnoDB AUTO_INCREMENT=103 DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `Members`
--

LOCK TABLES `Members` WRITE;
/*!40000 ALTER TABLE `Members` DISABLE KEYS */;
/*!40000 ALTER TABLE `Members` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `Reservations`
--

DROP TABLE IF EXISTS `Reservations`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `Reservations` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `CalendarID` int(11) NOT NULL,
  `tailNum` char(20) NOT NULL,
  `startTime` datetime NOT NULL,
  `stopTime` datetime NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uniq_res` (`tailNum`,`startTime`,`stopTime`),
  KEY `fk_cal2` (`CalendarID`),
  CONSTRAINT `fk_cal2` FOREIGN KEY (`CalendarID`) REFERENCES `Calendar` (`CalendarID`) ON DELETE CASCADE,
  CONSTRAINT `fk_res_tailNum` FOREIGN KEY (`tailNum`) REFERENCES `GliderStatus` (`tailNum`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=184 DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `Reservations`
--

LOCK TABLES `Reservations` WRITE;
/*!40000 ALTER TABLE `Reservations` DISABLE KEYS */;
/*!40000 ALTER TABLE `Reservations` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `Sessions`
--

DROP TABLE IF EXISTS `Sessions`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `Sessions` (
  `SessionID` varchar(32) COLLATE utf8_unicode_ci DEFAULT NULL,
  `DateTouched` int(11) NOT NULL DEFAULT '0',
  `Data` text COLLATE utf8_unicode_ci,
  UNIQUE KEY `id` (`SessionID`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `Sessions`
--

LOCK TABLES `Sessions` WRITE;
/*!40000 ALTER TABLE `Sessions` DISABLE KEYS */;
/*!40000 ALTER TABLE `Sessions` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `hfl`
--

DROP TABLE IF EXISTS `hfl`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `hfl` (
  `hflID` int(11) NOT NULL AUTO_INCREMENT,
  `updated` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `date` datetime NOT NULL DEFAULT '2000-01-01 00:00:00',
  `pilot` char(50) NOT NULL DEFAULT '',
  `glider` enum('ASK 21','Discus CS','Duo Discus','G 102','L-13','SGS 1-26A','SGS 2-33A','SGS 1-34') NOT NULL,
  `extraName` char(50) NOT NULL DEFAULT '' COMMENT 'Any other crew member',
  `fileName` char(80) NOT NULL DEFAULT '' COMMENT 'Base name of IGC file as uploaded',
  `units` enum('US','METRIC','INVALID') NOT NULL DEFAULT 'INVALID',
  `lat` decimal(9,6) NOT NULL DEFAULT '0.000000' COMMENT 'Lat, Lon, and Elev of field from mode.inc',
  `lon` decimal(9,6) NOT NULL DEFAULT '0.000000',
  `elev` mediumint(8) unsigned NOT NULL DEFAULT '0',
  `timezone` mediumint(9) NOT NULL DEFAULT '0' COMMENT 'Seconds offset from GMT, mode.inc',
  `high` mediumint(8) unsigned NOT NULL DEFAULT '0' COMMENT 'high/far values as defined by units',
  `far` decimal(5,2) unsigned NOT NULL DEFAULT '0.00',
  `long` decimal(4,1) NOT NULL DEFAULT '0.0' COMMENT 'Minutes',
  `notes` char(255) NOT NULL DEFAULT '',
  PRIMARY KEY (`hflID`),
  UNIQUE KEY `theEntry` (`fileName`),
  KEY `theDate` (`date`)
) ENGINE=InnoDB AUTO_INCREMENT=20 DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `hfl`
--

LOCK TABLES `hfl` WRITE;
/*!40000 ALTER TABLE `hfl` DISABLE KEYS */;
/*!40000 ALTER TABLE `hfl` ENABLE KEYS */;
UNLOCK TABLES;
/*!40103 SET TIME_ZONE=@OLD_TIME_ZONE */;
/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;

-- Dump completed on 2020-10-09 11:56:02
