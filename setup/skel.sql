-- MySQL dump 10.13  Distrib 5.7.19, for Linux (x86_64)
--
-- Host: localhost    Database: phpant
-- ------------------------------------------------------
-- Server version	5.7.19

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
-- Table structure for table `acls`
--

DROP TABLE IF EXISTS `acls`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `acls` (
  `acls_id` int(11) NOT NULL AUTO_INCREMENT,
  `users_roles_id` int(11) NOT NULL,
  `acls_event` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`acls_id`),
  KEY `fk_acls_users_roles1_idx` (`users_roles_id`),
  CONSTRAINT `fk_acls_users_roles1` FOREIGN KEY (`users_roles_id`) REFERENCES `users_roles` (`users_roles_id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=6 DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `acls`
--

LOCK TABLES `acls` WRITE;
/*!40000 ALTER TABLE `acls` DISABLE KEYS */;
INSERT INTO `acls` VALUES (1,3,'cli-load-grammar'),(2,4,'app-hook-test'),(3,4,'uploader-uri-test'),(4,4,'history-uri-test'),(5,4,'testasdf-uri-test');
/*!40000 ALTER TABLE `acls` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `api_keys`
--

DROP TABLE IF EXISTS `api_keys`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `api_keys` (
  `api_keys_id` int(11) NOT NULL AUTO_INCREMENT,
  `api_keys_key` varchar(41) DEFAULT NULL,
  `api_keys_info` varchar(255) DEFAULT NULL,
  `api_keys_enabled` varchar(1) DEFAULT 'Y' COMMENT 'Y to enable, N to disable.',
  `api_keys_generated` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`api_keys_id`)
) ENGINE=InnoDB AUTO_INCREMENT=21 DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `api_keys`
--

LOCK TABLES `api_keys` WRITE;
/*!40000 ALTER TABLE `api_keys` DISABLE KEYS */;
INSERT INTO `api_keys` VALUES (9,'dpzavyngfhgzbawbuhsxvbrgtshncmxgywhtuvzac','asdfasdf','N','2016-02-29 19:20:10'),(10,'tqvrvqtupfwdnmruhvngfzcerpwtxfmrwfervykhe','Test key','N','2016-02-29 19:34:59'),(11,'refehfrywbwadpcnhrhvntrtwqhsptmcyshgyyeva','asdf','N','2016-02-29 19:59:09'),(12,'bxaseqebkeuqahagfvvwtwzfqfzyqhxseygkppnxt','1234','N','2016-02-29 20:00:48'),(13,'hfzfdghfxzxvnxpdnycexynfunnntwdaccffmrpgq','1234','N','2016-02-29 20:01:06'),(14,'fctczwgwnbsgxevvdunmqtwwtxwsxxvcznfzhqvvr','asdf123','N','2016-02-29 20:01:41'),(15,'kvthubxvqwpmsucceyzctctbsnrfmtwwnqdfsaaew','asdf123','N','2016-02-29 20:02:01'),(16,'vhmrrrqzpnhsyacfuaayfksrvqtsvwarenfvcvvrg','Dev','Y','2016-02-29 20:02:51'),(17,'zpfdymmfywzepfzugrzdrxvmcacddwgdkpggztpxq','Foo App','N','2016-02-29 20:03:18'),(18,'cgmckutavuhbdqhmkwgekqhrdxhqyadagqctgwtcq','Bar App','Y','2016-02-29 20:11:01'),(19,'mgsekgfttwtanbqfeqthcybsrzrxqbdzhweucmmvf','Some User','Y','2016-02-29 20:14:47'),(20,'gentxyezqtxuuafgkhhmrdawgmstarvgwfaueeuuy','Something else','Y','2016-03-01 17:17:32');
/*!40000 ALTER TABLE `api_keys` ENABLE KEYS */;
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
  CONSTRAINT `fk_email_log_1` FOREIGN KEY (`users_id`) REFERENCES `users` (`users_id`) ON DELETE CASCADE ON UPDATE CASCADE
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
-- Table structure for table `logs`
--

DROP TABLE IF EXISTS `logs`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `logs` (
  `logs_id` int(11) NOT NULL AUTO_INCREMENT,
  `logs_timestamp` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `logs_component` varchar(45) DEFAULT NULL,
  `logs_message` text,
  PRIMARY KEY (`logs_id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `logs`
--

LOCK TABLES `logs` WRITE;
/*!40000 ALTER TABLE `logs` DISABLE KEYS */;
/*!40000 ALTER TABLE `logs` ENABLE KEYS */;
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
) ENGINE=InnoDB DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `settings`
--

LOCK TABLES `settings` WRITE;
/*!40000 ALTER TABLE `settings` DISABLE KEYS */;
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
  CONSTRAINT `fk_user_tokens_1` FOREIGN KEY (`users_id`) REFERENCES `users` (`users_id`) ON DELETE CASCADE ON UPDATE CASCADE
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
  `users_token` varchar(65) DEFAULT NULL,
  `users_active` varchar(1) DEFAULT 'Y',
  `users_last_login` int(11) DEFAULT NULL,
  `users_mobile_token` varchar(8) DEFAULT NULL COMMENT 'The token for mobile. This allows a user to log in via the browser AND their mobile phone.',
  `users_public_key` text COMMENT 'Holds the RSA or PGP public key for hashing.',
  `users_owner_id` int(11) DEFAULT NULL,
  `users_timezone` varchar(100) DEFAULT NULL,
  `users_roles_id` int(11) NOT NULL,
  `users_guid` varchar(45) DEFAULT NULL,
  PRIMARY KEY (`users_id`,`users_roles_id`),
  KEY `fk_users_users_roles_idx` (`users_roles_id`),
  CONSTRAINT `fk_users_1` FOREIGN KEY (`users_roles_id`) REFERENCES `users_roles` (`users_roles_id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `users`
--

LOCK TABLES `users` WRITE;
/*!40000 ALTER TABLE `users` DISABLE KEYS */;
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
  PRIMARY KEY (`users_roles_id`),
  UNIQUE KEY `users_roles_role_UNIQUE` (`users_roles_role`)
) ENGINE=InnoDB AUTO_INCREMENT=6 DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `users_roles`
--

LOCK TABLES `users_roles` WRITE;
/*!40000 ALTER TABLE `users_roles` DISABLE KEYS */;
INSERT INTO `users_roles` VALUES (1,'Admin','A'),(2,'User','U'),(3,'CLI Users','C'),(4,'Test Users','T'),(5,'AddRoleTest','D');
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

-- Dump completed on 2017-09-05 15:33:48
