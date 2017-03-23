-- MySQL dump 10.13  Distrib 5.7.11, for Linux (x86_64)
--
-- Host: localhost    Database: maps
-- ------------------------------------------------------
-- Server version	5.7.11-0ubuntu6

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
-- Table structure for table `adjacencies`
--

DROP TABLE IF EXISTS `adjacencies`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `adjacencies` (
  `nodeuid` double NOT NULL,
  `node` varchar(45) NOT NULL DEFAULT '',
  `nodeTouid` double NOT NULL DEFAULT '0',
  `nodeTo` varchar(45) DEFAULT NULL,
  `rxrate` varchar(45) DEFAULT NULL,
  `senyal` varchar(45) DEFAULT NULL,
  `canal` varchar(45) NOT NULL DEFAULT '0',
  `ping` varchar(45) DEFAULT NULL,
  `ample` varchar(45) DEFAULT NULL,
  `timestamp_captura` int(11) NOT NULL DEFAULT '0',
  PRIMARY KEY (`nodeuid`,`nodeTouid`,`canal`,`timestamp_captura`),
  KEY `index` (`nodeuid`,`nodeTouid`,`timestamp_captura`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `channels`
--

DROP TABLE IF EXISTS `channels`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `channels` (
  `canal` int(11) unsigned NOT NULL,
  `centre` int(11) unsigned NOT NULL,
  PRIMARY KEY (`canal`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `community_paths`
--

DROP TABLE IF EXISTS `community_paths`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `community_paths` (
  `uid` double NOT NULL,
  `gwpath` varchar(1024) DEFAULT NULL,
  `timestamp_captura` int(11) NOT NULL,
  PRIMARY KEY (`uid`,`timestamp_captura`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `excluded_channels`
--

DROP TABLE IF EXISTS `excluded_channels`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `excluded_channels` (
  `name` varchar(50) NOT NULL DEFAULT '',
  PRIMARY KEY (`name`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `excluded_links`
--

DROP TABLE IF EXISTS `excluded_links`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `excluded_links` (
  `origen` varchar(50) NOT NULL DEFAULT '',
  `desti` varchar(50) NOT NULL DEFAULT '',
  `canal` varchar(20) NOT NULL DEFAULT '',
  PRIMARY KEY (`origen`,`desti`,`canal`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `inet_paths`
--

DROP TABLE IF EXISTS `inet_paths`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `inet_paths` (
  `uid` double NOT NULL,
  `gwpath` varchar(1024) DEFAULT NULL,
  `timestamp_captura` int(11) NOT NULL,
  PRIMARY KEY (`uid`,`timestamp_captura`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `ip`
--

DROP TABLE IF EXISTS `ip`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `ip` (
  `uid` double NOT NULL DEFAULT '0',
  `ip` varchar(45) NOT NULL,
  `tipus` varchar(45) DEFAULT NULL,
  `timestamp_captura` int(11) NOT NULL DEFAULT '0',
  PRIMARY KEY (`uid`,`ip`,`timestamp_captura`),
  KEY `index` (`ip`,`timestamp_captura`),
  FULLTEXT KEY `fulltext` (`ip`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `nodes`
--

DROP TABLE IF EXISTS `nodes`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `nodes` (
  `uid` double NOT NULL DEFAULT '0',
  `id` varchar(45) DEFAULT NULL,
  `gwmeshid` int(11) DEFAULT '0',
  `gwinetid` int(11) DEFAULT '0',
  `gdev` int(11) DEFAULT NULL,
  `name` varchar(45) NOT NULL,
  `system` varchar(45) DEFAULT NULL,
  `lon` varchar(45) DEFAULT NULL,
  `lat` varchar(45) DEFAULT NULL,
  `zona` int(11) DEFAULT NULL,
  `uptime` varchar(45) DEFAULT NULL,
  `timestamp_captura` int(11) NOT NULL DEFAULT '0',
  `timestamp_json` int(11) DEFAULT '0',
  PRIMARY KEY (`uid`,`timestamp_captura`),
  KEY `index` (`name`,`timestamp_captura`),
  FULLTEXT KEY `FULLTEXT` (`name`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `zones`
--

DROP TABLE IF EXISTS `zones`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `zones` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `zona` varchar(60) DEFAULT NULL,
  `url` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=12 DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40103 SET TIME_ZONE=@OLD_TIME_ZONE */;

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;

-- Dump completed on 2017-03-23  8:45:22
