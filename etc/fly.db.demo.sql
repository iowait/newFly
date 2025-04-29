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
INSERT INTO `Glider` VALUES (1,'Schleicher','ASK 21','Glider','151 KIAS','97 KIAS','49 KIAS + wind speed','49K duel 46K solo','KIAS','37 KIAS','34-1','NA','The ASK 21 is a glass-reinforced plastic (GRP) two-seater mid-wing glider aircraft with a T-tail. The ASK 21 is designed for beginner instruction, cross-country flying and aerobatic instruction.    \r\nIt can be used for spin training with tailweights added.','glider_1.jpg','http://flsc.org/portals/12/PDF/POH/ASK-21_POH.pdf',2);
INSERT INTO `Glider` VALUES (4,'Schweizer','SGS 1-26A','Glider','104 MPH','65 MPH','45 MPH + wind','45 MPH','38 MPH','28 MPH','23-1','0','The Schweizer SGS 1-26 is a United States One-Design, single-seat, mid-wing glider built by Schweizer Aircraft of Elmira, New York. The SGS 1-26 enjoyed a very long production run from its first flight in 1954 until 1979, when production was ended. The 1-26 was replaced in production by the Schweizer SGS 1-36 Sprite. The 1-26 is the most numerous sailplane found in the US. Due to its light weight, it climbs well, stalls benignly, will land in an incredibly short distance (due to the nose skid), and is an excellent glider for transitioning after solo and first cross country attempts.','glider_4.jpg','http://flsc.org/portals/12/PDF/POH/POH_1-26.pdf',1);
INSERT INTO `Glider` VALUES (5,'Grob','G 102 Astir CS','Glider','135k','92k','51k + wind','51k','41k','33k','34-1','','The G102 Astir is a single seat fibreglas 15m Standard Class sailplane with a retractable wheel, designed by and built by Grob Aircraft, with the first flight in December 1974. The large wing area gives good low-speed handling characteristics, docile stalling characteristics, a excellent climb. It is also certified for aerobatic flight.','glider_5.jpg','http://flsc.org/portals/12/PDF/POH/Grob_POH.pdf',1);
INSERT INTO `Glider` VALUES (6,'Blanik','L-13 AC','Glider','124k','86k','46k + wind','46k','38k','34k','28-1','','The two-seat all-metal L13AC BlanÃ­k is an upgraded version of the L13 Blanik.  It has no outstanding ADs requiring compliance. \r\n It is an excellent robust aircraft for glider training, spin training, and aerobatics. The main wheel is semi-retractable equipped with a shock absorber.  It has the same cockpit as the L23 Super Blanik with a one piece canopy, the L13 tail, and shortened L23 wings without flaps. Our glider has a radio and upgraded (strengthened) tailboom modification.','glider_6.jpg','http://flsc.org/portals/12/PDF/POH/L13AC_POH_1.pdf',2);
INSERT INTO `Glider` VALUES (7,'Schweizer','SGS 2-33A','Glider','98 MPH','65 MPH','50 MPH +wind','50 MPH','42 MPH','33 MPH duel 31 MPH s','23-1','','The Schweizer SGS 2-33 is an American two-seat, high-wing, strut-braced, training glider that was built by Schweizer Aircraft of Elmira, New York. From its introduction until the late 1980s, the 2-33 was the main training glider used in North America.  Ours was built in 1984 and purchased from the Wings of Eagles Museum. \r\n\r\nThe 2-33 was designed to replace the Schweizer 2-22, from which it was derived. The aircraft first flew in 1965 and production was started in 1967. Production was completed in 1981.   \r\nThe glider is exceptionally rugged, and is noted for its low stall speed, docile stalling characteristics, is capable of a very short landing distance, and wide range of weight in each cockpit.    \r\nOur glider has blocks that can be rotated in front of the rudder pedals to accomodate shorter legged people.','glider_7.jpg','http://flsc.org/portals/12/PDF/POH/SGS_2-33_Flight_MX_Assembly.pdf',2);
INSERT INTO `Glider` VALUES (8,'Piper','PA-25 Pawnee','Tow','n/a','n/a','n/a','n/a','n/a','n/a','n/a','n/a','The PA-25 Pawnee was an agricultural aircraft produced by Piper Aircraft between 1959 and 1981. It remains a widely used aircraft in agricultural spraying and is also used as a tow plane, or tug, for launching gliders or for towing banners. In 1988 the design rights and support responsibility were sold to Latino Americana de AviaciÃ³n of Argentina.','glider_8.jpg','http://flsc.org/portals/12/PDF/POH/Pawnee_POH.pdf',1);
INSERT INTO `Glider` VALUES (9,'American Champion','Citabria','Tow','n/a','n/a','n/a','n/a','n/a','n/a','n/a','n/a','The Citabria is a light single-engine, two-seat, fixed conventional gear airplane which entered production in the United States in 1964. Designed for flight training, utility and personal use, it is capable of sustaining aerobatic stresses from +5g to -2g. \r\nWe use the aircraft for Spin training, cross country field selection, tow pilot checkouts, and as a backup towplane for our lighter gliders.\r\nIts name spelled backwards is, &quot;airbatic&quot;.','glider_9.jpg','http://flsc.org/Links.php#poh',2);
INSERT INTO `Glider` VALUES (10,'Centrair','Pegasus 101B','Glider','135 KIAS / 119 KIAS','92 KIAS','49 KIAS','52 KIAS','44 KIAS','39 KIAS','41-1','+5 / +4 winglets','The Centrair Pegasus 101 is a single-seat, Standard Class, retractable wheel, competition sailplane, developed in France using a fuselage based on the ASW-19 with the wing profile sections being an improved new design. Our FLSC Pegasus is a B model that has a carbon fiber main spar, \r\nWhen imported it was registered in the USA  - as an A model. \r\nThis beautiful high-performance single-seat sailplane has excellent handling and benign stalling characteristics. It has a claimed maximum L/D = 41.  We purchased it from Doug Cline in 2019.','glider_10.jpg','http://flsc.org/portals/12/PDF/POH/PegasusPOH.pdf',1);
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
INSERT INTO `GliderStatus` VALUES ('101A0202',10,'2019-02-01','2019-01-01','ORANGE','Brakes are very weak, use caution.','2020-06-23 15:19:58',24);
INSERT INTO `GliderStatus` VALUES ('ASKBONGO',1,'2020-08-10','2020-08-05','ORANGE','needs new knobs\r\nstill haven&#039;t made progress, new knobs didn&#039;t fit.\r\n---\r\nSTILL NOT FIXED','2020-07-08 13:38:26',101);
INSERT INTO `GliderStatus` VALUES ('BONGO',1,'2020-07-10','2020-07-05','GREEN','','2020-06-23 15:19:58',24);
INSERT INTO `GliderStatus` VALUES ('N20737',5,'0000-00-00','2019-04-30','GREEN','The dip-rod from the wiz-bang needs replacement soon.','2020-06-23 15:19:58',24);
INSERT INTO `GliderStatus` VALUES ('N234WE',7,'0000-00-00','2019-04-30','GREEN','A hand held radio will be used for now.','2020-06-23 15:19:58',24);
INSERT INTO `GliderStatus` VALUES ('N374BA',6,'0000-00-00','2019-04-30','RED','Needs a brighter coat of yellow. \r\nThe wings don\'t work, don\'t fly it.','2020-06-23 15:19:58',24);
INSERT INTO `GliderStatus` VALUES ('N3822A',4,'0000-00-00','2019-04-30','GREEN','','2020-06-23 15:19:58',24);
INSERT INTO `GliderStatus` VALUES ('N574KS',1,'0000-00-00','2019-04-30','RED','Needs some pixie dust and softer  seats.','2020-06-23 18:23:01',24);
INSERT INTO `GliderStatus` VALUES ('N7409Z',8,'0000-00-00','2019-03-31','GREEN','Recommend repaint to fire-engine red.','2020-06-23 15:19:58',24);
INSERT INTO `GliderStatus` VALUES ('N7519F',9,'0000-00-00','2019-08-31','GREEN','Needs a brighter coat of yellow.','2020-06-23 15:19:58',24);
INSERT INTO `GliderStatus` VALUES ('N75765',7,'2020-05-10','2019-10-10','RED','Needs new wings.','2020-06-23 15:19:58',24);
INSERT INTO `GliderStatus` VALUES ('NDBIG',7,'2020-07-10','2020-07-05','GREEN','','2020-06-23 15:19:58',24);
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
INSERT INTO `GliderStatusLog` VALUES (10,'101A0202','2019-02-01','2019-01-01','ORANGE','Brakes are very weak, use caution.','2020-06-23 15:19:58',24);
INSERT INTO `GliderStatusLog` VALUES (1,'ASKBONGO','2020-08-10','2020-08-05','ORANGE','needs new nobs','2020-06-23 15:19:58',24);
INSERT INTO `GliderStatusLog` VALUES (1,'BONGO','2020-07-10','2020-07-05','GREEN','','2020-06-23 15:19:58',24);
INSERT INTO `GliderStatusLog` VALUES (5,'N20737','0000-00-00','2019-04-30','GREEN','The dip-rod from the wiz-bang needs replacement soon.','2020-06-23 15:19:58',24);
INSERT INTO `GliderStatusLog` VALUES (7,'N234WE','0000-00-00','2019-04-30','GREEN','A hand held radio will be used for now.','2020-06-23 15:19:58',24);
INSERT INTO `GliderStatusLog` VALUES (1,'N3333333','2020-09-10','2020-10-10','GREEN','lovely plane','2020-06-23 15:38:46',24);
INSERT INTO `GliderStatusLog` VALUES (6,'N374BA','0000-00-00','2019-04-30','RED','Needs a brighter coat of yellow. \r\nThe wings don\'t work, don\'t fly it.','2020-06-23 15:19:58',24);
INSERT INTO `GliderStatusLog` VALUES (4,'N3822A','0000-00-00','2019-04-30','GREEN','','2020-06-23 15:19:58',24);
INSERT INTO `GliderStatusLog` VALUES (1,'N574KS','0000-00-00','2019-04-30','GREEN','Needs some pixie dust and softer seats.','2020-06-23 15:19:58',24);
INSERT INTO `GliderStatusLog` VALUES (8,'N7409Z','0000-00-00','2019-03-31','GREEN','Recommend repaint to fire-engine red.','2020-06-23 15:19:58',24);
INSERT INTO `GliderStatusLog` VALUES (9,'N7519F','0000-00-00','2019-08-31','GREEN','Needs a brighter coat of yellow.','2020-06-23 15:19:58',24);
INSERT INTO `GliderStatusLog` VALUES (7,'N75765','2020-05-10','2019-10-10','RED','Needs new wings.','2020-06-23 15:19:58',24);
INSERT INTO `GliderStatusLog` VALUES (7,'NDBIG','2020-07-10','2020-07-05','GREEN','','2020-06-23 15:19:58',24);
INSERT INTO `GliderStatusLog` VALUES (1,'N3333333','2020-09-10','2020-10-10','GREEN','lovely plane','2020-06-23 15:39:54',24);
INSERT INTO `GliderStatusLog` VALUES (1,'ASKBONGO','2020-08-10','2020-08-05','ORANGE','needs new nobs','2020-06-23 15:48:45',24);
INSERT INTO `GliderStatusLog` VALUES (1,'ASKBONGO','2020-08-10','2020-08-05','ORANGE','needs new nobs','2020-06-23 15:50:36',24);
INSERT INTO `GliderStatusLog` VALUES (1,'N574KS','0000-00-00','2019-04-30','GREEN','Needs some pixie dust and softer seats.','2020-06-23 17:37:14',24);
INSERT INTO `GliderStatusLog` VALUES (1,'N574KS','0000-00-00','2019-04-30','GREEN','Needs some pixie dust and softer  seats.','2020-06-23 17:38:29',24);
INSERT INTO `GliderStatusLog` VALUES (1,'N574KS','0000-00-00','2019-04-30','RED','Needs some pixie dust and softer  seats.','2020-06-23 18:23:01',24);
INSERT INTO `GliderStatusLog` VALUES (1,'ASKBONGO','2020-08-10','2020-08-05','ORANGE','needs new knobs','2020-06-23 18:24:48',24);
INSERT INTO `GliderStatusLog` VALUES (1,'ASKBONGO','2020-08-10','2020-08-05','ORANGE','needs new knobs','2020-06-30 12:37:02',24);
INSERT INTO `GliderStatusLog` VALUES (1,'ASKBONGO','2020-08-10','2020-08-05','ORANGE','needs new knobs','2020-06-30 18:23:04',24);
INSERT INTO `GliderStatusLog` VALUES (1,'ASKBONGO','2020-08-10','2020-08-05','ORANGE','needs new knobs\r\nstill haven&#039;t made progress, new knobs didn&#039;t fit.','2020-07-08 12:43:49',101);
INSERT INTO `GliderStatusLog` VALUES (1,'ASKBONGO','2020-08-10','2020-08-05','ORANGE','needs new knobs\r\nstill haven&#039;t made progress, new knobs didn&#039;t fit.','2020-07-08 12:44:10',101);
INSERT INTO `GliderStatusLog` VALUES (1,'ASKBONGO','2020-08-10','2020-08-05','ORANGE','needs new knobs\r\nstill haven&#039;t made progress, new knobs didn&#039;t fit.\r\n---\r\nSTILL NOT FIXED','2020-07-08 13:38:26',101);
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
INSERT INTO `Members` VALUES (20,'Rick20@Rickville.biz','CFI-towboard','Rick','Regular','CFI-G','None','L13AC,L13AC_back,ASK21,ASK21_back,Grobe102,SGS_1-26,SGS_2-33,SGS_2-33_Back,Citabria,Pawnee,Ground_launch','MEMBER,CFIG,BOARD,TOW','','A,B,C,Silver,Silver Altitude,Silver Distance,Silver Duration,Gold,Gold Altitude,Gold Distance,Diamond Goal','1aa036742e0cefd16e0ba130e52b6749','6e2a75309e9ac1a2ae4118235b888c83','CFI-towboard_20.JPG','Ann','','20 Easy st.','Rickville','New York','10020','704-482-0022','(202)358-001 (wrk)','2020-08-10 17:13:10','98.10.7.58','2020-04-29 11:34:16','',0,'','0000-00-00','0000-00-00','','','');
INSERT INTO `Members` VALUES (24,'John24@Johnville.biz','Admin','John','Regular','Private','None','L13AC,L13AC_back,ASK21,ASK21_back,Grobe102,SGS_1-26,SGS_2-33,SGS_2-33_Back,Ground_launch','ADMIN,MEMBER,BOARD','','A,B,C','1aa036742e0cefd16e0ba130e52b6749','28b2c2c78ef0aa1e3f99acb477f44717','Admin-board_24.JPG','','','24 Easy st.','Johnville','New York','10024','704-482-0022','(202)358-001 (wrk)','2020-10-05 19:41:06','98.10.7.58','2020-10-05 15:41:06','',20,'','0000-00-00','0000-00-00','','(only superusers) The club is happy to have a former \r\nStarship Captain as our new leader, need to get all \r\nthe text in here and keep the line breaks.','(members)  I like piÃ±a coladas And getting caught in the rain -&gt; \r\nwww.getalife.com');
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
