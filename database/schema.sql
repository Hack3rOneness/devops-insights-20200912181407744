-- MySQL dump 10.13  Distrib 5.5.44, for debian-linux-gnu (x86_64)
--
-- Host: localhost    Database: facebook-ctf
-- ------------------------------------------------------
-- Server version	5.5.44-0ubuntu0.14.04.1

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
-- Current Database: `facebook-ctf`
--

/*!40000 DROP DATABASE IF EXISTS `facebook-ctf`*/;

CREATE DATABASE /*!32312 IF NOT EXISTS*/ `facebook-ctf` /*!40100 DEFAULT CHARACTER SET latin1 */;

USE `facebook-ctf`;

--
-- Table structure for table `levels`
--

DROP TABLE IF EXISTS `levels`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `levels` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `active` tinyint(1) DEFAULT 0,
  `type` varchar(4) DEFAULT NULL,
  `description` text DEFAULT NULL,
  `entity_id` int(11) DEFAULT NULL,
  `category_id` int(11) NOT NULL DEFAULT '1',
  `points` int(11) NOT NULL DEFAULT '0',
  `bonus` int(11) NOT NULL DEFAULT '0',
  `bonus_dec` int(11) NOT NULL DEFAULT '0',
  `bonus_fix` int(11) NOT NULL DEFAULT '0',
  `flag` text DEFAULT NULL,
  `hint` text DEFAULT NULL,
  `penalty` int(11) NOT NULL DEFAULT '0',
  `created_ts` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `categories`
--

DROP TABLE IF EXISTS `categories`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `categories` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `category` text DEFAULT NULL,
  `created_ts` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

LOCK TABLES `categories` WRITE;
INSERT INTO `categories` (category, created_ts) VALUES("None", NOW());
INSERT INTO `categories` (category, created_ts) VALUES("Quiz", NOW());
UNLOCK TABLES;

--
-- Table structure for table `attachments`
--

DROP TABLE IF EXISTS `attachments`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `attachments` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `level_id` int(11) DEFAULT NULL,
  `filename` text NULL DEFAULT NULL,
  `type` text NULL DEFAULT NULL,
  `created_ts` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `links`
--

DROP TABLE IF EXISTS `links`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `links` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `level_id` int(11) DEFAULT NULL,
  `link` text NULL DEFAULT NULL,
  `created_ts` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `teams`
--

DROP TABLE IF EXISTS `teams`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `teams` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `active` tinyint(1) DEFAULT 1,
  `name` text DEFAULT NULL,
  `password_hash` text DEFAULT NULL,
  `points` int(11) NOT NULL DEFAULT '0',
  `last_score` timestamp NULL DEFAULT NULL,
  `logo` text DEFAULT NULL,
  `admin` tinyint(1) DEFAULT 0,
  `visible` tinyint(1) DEFAULT 1,
  `created_ts` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `sessions`
--

DROP TABLE IF EXISTS `sessions`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `sessions` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `cookie` text DEFAULT NULL,
  `data` text,
  `created_ts` timestamp NULL DEFAULT NULL,
  `last_access_ts` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `configuration`
--

DROP TABLE IF EXISTS `configuration`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `configuration` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `field` varchar(100) NOT NULL,
  `value` text DEFAULT NULL,
  `description` text DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `field` (`field`)
) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

LOCK TABLES `configuration` WRITE;
INSERT INTO `configuration` (field, value, description) VALUES("game", "0", "(Boolean) Game is ongoing");
INSERT INTO `configuration` (field, value, description) VALUES("next_game", "0", "(Date) Next game to happen");
INSERT INTO `configuration` (field, value, description) VALUES("game_duration", "10800", "(Integer) Duration of game");
INSERT INTO `configuration` (field, value, description) VALUES("scoring", "0", "(Boolean) Ability score levels");
INSERT INTO `configuration` (field, value, description) VALUES("teams", "1", "(Boolean) Display current active teams");
INSERT INTO `configuration` (field, value, description) VALUES("teams_cycle", "5000", "(Integer) Refresh rate for teams in milliseconds");
INSERT INTO `configuration` (field, value, description) VALUES("map", "1", "(Boolean) Display current map");
INSERT INTO `configuration` (field, value, description) VALUES("map_cycle", "5000", "(Integer) Refresh rate for map in milliseconds");
INSERT INTO `configuration` (field, value, description) VALUES("conf", "1", "(Boolean) Send current configuration");
INSERT INTO `configuration` (field, value, description) VALUES("conf_cycle", "30000", "(Integer) Refresh rate for configuration values in milliseconds");
INSERT INTO `configuration` (field, value, description) VALUES("cmd", "1", "(Boolean) Send current commands");
INSERT INTO `configuration` (field, value, description) VALUES("cmd_cycle", "30000", "(Integer) Refresh rate for commands in milliseconds");
INSERT INTO `configuration` (field, value, description) VALUES("ranking", "0", "(Boolean) Progressive ranking is enabled");
INSERT INTO `configuration` (field, value, description) VALUES("ranking_cycle", "300", "(Integer) Frequency to take ranking in seconds");
INSERT INTO `configuration` (field, value, description) VALUES("registration", "0", "(Boolean) Ability to register teams");
INSERT INTO `configuration` (field, value, description) VALUES("registration_names", "0", "(Boolean) Registration will ask for names");
INSERT INTO `configuration` (field, value, description) VALUES("registration_login", "1", "(Boolean) Registration will automatically login");
INSERT INTO `configuration` (field, value, description) VALUES("registration_type", "1", "(Integer) Type of registration: 1 - Open; 2 - Tokenized; 3 - Needs approval");
INSERT INTO `configuration` (field, value, description) VALUES("registration_players", "3", "(Integer) Number of players per team");
INSERT INTO `configuration` (field, value, description) VALUES("login", "1", "(Boolean) Ability to login");
INSERT INTO `configuration` (field, value, description) VALUES("login_select", "1", "(Boolean) Login selecting the team");
INSERT INTO `configuration` (field, value, description) VALUES("login_strongpasswords", "0", "(Boolean) Enforce using strong passwords");
INSERT INTO `configuration` (field, value, description) VALUES("default_bonus", "30", "(Integer) Default value for bonus in levels");
INSERT INTO `configuration` (field, value, description) VALUES("default_bonusdec", "10", "(Integer) Default bonus decrement in levels");
UNLOCK TABLES;

--
-- Table structure for table `registration_log`
--

DROP TABLE IF EXISTS `registration_log`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `registration_log` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `team_name` text DEFAULT NULL,
  `team_email` text DEFAULT NULL,
  `team_logo` text DEFAULT NULL,
  `team_password` text DEFAULT NULL,
  `team_token` text DEFAULT NULL,
  `ts` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `registration_tokens`
--

DROP TABLE IF EXISTS `registration_tokens`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `registration_tokens` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `token` text DEFAULT NULL,
  `used` tinyint(1) DEFAULT 0,
  `team_id` int(11) DEFAULT NULL,
  `use_ts` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `scores_log`
--

DROP TABLE IF EXISTS `scores_log`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `scores_log` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `ts` timestamp NULL DEFAULT NULL,
  `team_id` int(11) DEFAULT NULL,
  `points` int(11) NOT NULL DEFAULT '0',
  `level_id` int(11) DEFAULT NULL,
  `type` varchar(4) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `failures_log`
--

DROP TABLE IF EXISTS `failures_log`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `failures_log` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `ts` timestamp NULL DEFAULT NULL,
  `team_id` int(11) DEFAULT NULL,
  `level_id` int(11) DEFAULT NULL,
  `flag` text DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `attacks_log`
--

DROP TABLE IF EXISTS `attacks_log`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `attacks_log` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `ts` timestamp NULL DEFAULT NULL,
  `team_id` int(11) DEFAULT NULL,
  `level_id` int(11) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `hints_log`
--

DROP TABLE IF EXISTS `hints_log`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `hints_log` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `ts` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `level_id` int(11) NOT NULL DEFAULT '0',
  `team_id` int(11) NOT NULL DEFAULT '0',
  `penalty` int(11) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;


--
-- Table structure for table `ranking_log`
--

DROP TABLE IF EXISTS `ranking_log`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `ranking_log` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `ts` timestamp NULL DEFAULT NULL,
  `team_name` text DEFAULT NULL,
  `points` int(11) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;
