-- MySQL dump 10.13  Distrib 5.5.52, for debian-linux-gnu (x86_64)
--
-- Host: 127.0.0.1    Database: phpant
-- ------------------------------------------------------
-- Server version	5.5.52-0+deb8u1

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
-- Table structure for table `Version`
--

DROP TABLE IF EXISTS `Version`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `Version` (
  `VersionId` int(11) NOT NULL DEFAULT '1',
  PRIMARY KEY (`VersionId`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `Version`
--

LOCK TABLES `Version` WRITE;
/*!40000 ALTER TABLE `Version` DISABLE KEYS */;
/*!40000 ALTER TABLE `Version` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `email_log`
--

DROP TABLE IF EXISTS `email_log`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `email_log` (
  `email_log_id` int(11) NOT NULL AUTO_INCREMENT,
  `email_log_to` varchar(255) DEFAULT NULL COMMENT 'The address to whom this person was sent. This is historical since emails can change.',
  `email_log_from` varchar(255) DEFAULT NULL,
  `email_log_subject` varchar(255) DEFAULT NULL,
  `email_log_body` text,
  `email_log_headers` text,
  `email_log_disposition` varchar(255) DEFAULT NULL,
  `users_id` int(11) NOT NULL COMMENT 'The user to whom this email was sent.',
  `users_roles_id` int(11) NOT NULL COMMENT 'The role of the person to whom this was sent.\n',
  `email_log_timestamp_sent` int(11) DEFAULT NULL,
  `email_log_timestamp_sent_local` varchar(45) DEFAULT NULL COMMENT 'The local time (for the user) that the message was sent.\n',
  PRIMARY KEY (`email_log_id`,`users_id`,`users_roles_id`),
  KEY `fk_email_log_users1_idx` (`users_id`,`users_roles_id`),
  CONSTRAINT `fk_email_log_users1` FOREIGN KEY (`users_id`, `users_roles_id`) REFERENCES `users` (`users_id`, `users_roles_id`) ON DELETE NO ACTION ON UPDATE NO ACTION
) ENGINE=InnoDB DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `email_log`
--

LOCK TABLES `email_log` WRITE;
/*!40000 ALTER TABLE `email_log` DISABLE KEYS */;
/*!40000 ALTER TABLE `email_log` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `settings`
--

DROP TABLE IF EXISTS `settings`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `settings` (
  `settings_id` int(11) NOT NULL AUTO_INCREMENT,
  `settings_key` varchar(255) DEFAULT NULL,
  `settings_value` text,
  PRIMARY KEY (`settings_id`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `settings`
--

LOCK TABLES `settings` WRITE;
/*!40000 ALTER TABLE `settings` DISABLE KEYS */;
INSERT INTO `settings` VALUES (1,'credentials-valid-for','2592000'),(2,'uri-whitelist','[\"\\/login\\/\"]');
/*!40000 ALTER TABLE `settings` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `user_tokens`
--

DROP TABLE IF EXISTS `user_tokens`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `user_tokens` (
  `user_tokens_id` int(11) NOT NULL AUTO_INCREMENT,
  `user_tokens_token` varchar(100) DEFAULT NULL,
  `user_tokens_expiry` timestamp NULL DEFAULT NULL,
  `user_tokens_user_agent` varchar(45) DEFAULT NULL,
  `users_id` int(11) NOT NULL,
  `users_roles_id` int(11) NOT NULL,
  PRIMARY KEY (`user_tokens_id`,`users_id`,`users_roles_id`),
  KEY `fk_user_tokens_users1_idx` (`users_id`,`users_roles_id`),
  CONSTRAINT `fk_user_tokens_users1` FOREIGN KEY (`users_id`, `users_roles_id`) REFERENCES `users` (`users_id`, `users_roles_id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `user_tokens`
--

LOCK TABLES `user_tokens` WRITE;
/*!40000 ALTER TABLE `user_tokens` DISABLE KEYS */;
/*!40000 ALTER TABLE `user_tokens` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `users`
--

DROP TABLE IF EXISTS `users`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `users` (
  `users_id` int(11) NOT NULL AUTO_INCREMENT,
  `users_email` varchar(255) DEFAULT NULL,
  `users_password` varchar(60) DEFAULT NULL,
  `users_first` varchar(45) DEFAULT NULL,
  `users_last` varchar(45) DEFAULT NULL,
  `users_setup` varchar(1) DEFAULT 'N',
  `users_nonce` varchar(32) DEFAULT NULL,
  `users_token` varchar(8) DEFAULT NULL,
  `users_active` varchar(1) DEFAULT 'Y',
  `users_last_login` int(11) DEFAULT NULL,
  `users_mobile_token` varchar(8) DEFAULT NULL COMMENT 'The token for mobile. This allows a user to log in via the browser AND their mobile phone.',
  `users_public_key` text COMMENT 'Holds the RSA or PGP public key for hashing.',
  `users_owner_id` int(11) DEFAULT NULL,
  `users_timezone` varchar(100) DEFAULT NULL,
  `users_roles_id` int(11) NOT NULL,
  PRIMARY KEY (`users_id`,`users_roles_id`),
  KEY `fk_users_users_roles_idx` (`users_roles_id`),
  CONSTRAINT `fk_users_users_roles` FOREIGN KEY (`users_roles_id`) REFERENCES `users_roles` (`users_roles_id`) ON DELETE NO ACTION ON UPDATE NO ACTION
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `users`
--

LOCK TABLES `users` WRITE;
/*!40000 ALTER TABLE `users` DISABLE KEYS */;
INSERT INTO `users` VALUES (1,'michael@highpoweredhelp.com','$2y$10$Eu6meP.M6uaEM5zya7QFTedGsiYmnmC6ZRiPNn8OcemcOqjBTSAxu','Michael','Munger','N',NULL,NULL,'Y',NULL,NULL,NULL,NULL,NULL,1);
/*!40000 ALTER TABLE `users` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `users_roles`
--

DROP TABLE IF EXISTS `users_roles`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `users_roles` (
  `users_roles_id` int(11) NOT NULL AUTO_INCREMENT,
  `users_roles_title` varchar(45) DEFAULT NULL,
  `users_roles_role` varchar(1) DEFAULT 'U' COMMENT 'A - Administrator\nU - Standard User',
  PRIMARY KEY (`users_roles_id`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `users_roles`
--

LOCK TABLES `users_roles` WRITE;
/*!40000 ALTER TABLE `users_roles` DISABLE KEYS */;
INSERT INTO `users_roles` VALUES (1,'Admin','A');
/*!40000 ALTER TABLE `users_roles` ENABLE KEYS */;
UNLOCK TABLES;
/*!40103 SET TIME_ZONE=@OLD_TIME_ZONE */;

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;

-- Dump completed on 2016-10-23 20:48:52
