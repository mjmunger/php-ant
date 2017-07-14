-- MySQL dump 10.13  Distrib 5.7.17, for Linux (x86_64)
--
-- Host: localhost    Database: phpant
-- ------------------------------------------------------
-- Server version	5.7.17

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
  `api_keys_generated` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`api_keys_id`)
) ENGINE=InnoDB AUTO_INCREMENT=21 DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `api_keys`
--

LOCK TABLES `api_keys` WRITE;
/*!40000 ALTER TABLE `api_keys` DISABLE KEYS */;
INSERT INTO `api_keys` VALUES (9,'dpzavyngfhgzbawbuhsxvbrgtshncmxgywhtuvzac','asdfasdf','N','2016-02-29 04:20:10'),(10,'tqvrvqtupfwdnmruhvngfzcerpwtxfmrwfervykhe','Test key','N','2016-02-29 04:34:59'),(11,'refehfrywbwadpcnhrhvntrtwqhsptmcyshgyyeva','asdf','N','2016-02-29 04:59:09'),(12,'bxaseqebkeuqahagfvvwtwzfqfzyqhxseygkppnxt','1234','N','2016-02-29 05:00:48'),(13,'hfzfdghfxzxvnxpdnycexynfunnntwdaccffmrpgq','1234','N','2016-02-29 05:01:06'),(14,'fctczwgwnbsgxevvdunmqtwwtxwsxxvcznfzhqvvr','asdf123','N','2016-02-29 05:01:41'),(15,'kvthubxvqwpmsucceyzctctbsnrfmtwwnqdfsaaew','asdf123','N','2016-02-29 05:02:01'),(16,'vhmrrrqzpnhsyacfuaayfksrvqtsvwarenfvcvvrg','Dev','Y','2016-02-29 05:02:51'),(17,'zpfdymmfywzepfzugrzdrxvmcacddwgdkpggztpxq','Foo App','N','2016-02-29 05:03:18'),(18,'cgmckutavuhbdqhmkwgekqhrdxhqyadagqctgwtcq','Bar App','Y','2016-02-29 05:11:01'),(19,'mgsekgfttwtanbqfeqthcybsrzrxqbdzhweucmmvf','Some User','Y','2016-02-29 05:14:47'),(20,'gentxyezqtxuuafgkhhmrdawgmstarvgwfaueeuuy','Something else','Y','2016-03-01 02:17:32');
/*!40000 ALTER TABLE `api_keys` ENABLE KEYS */;
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
) ENGINE=InnoDB AUTO_INCREMENT=7 DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `users`
--

LOCK TABLES `users` WRITE;
/*!40000 ALTER TABLE `users` DISABLE KEYS */;
INSERT INTO `users` VALUES (1,'michael@highpoweredhelp.com','sha256:1000:ZAI+EiDE+NMdXPAspmpcDuiiNPOc5H72:f/NnjE4uD0G+HkN','Michael','Munger','Y',NULL,'463f4b5ff3885ebc0d33ee2c5ec732d2af69f6152cefd4becbffb7bf74cd9ac8','Y',1475520393,NULL,NULL,NULL,NULL,1,NULL),(2,'itatartrate@precompounding.co.uk','$2y$10$eM4iUk/NqBf3Gjd5m0GetevniEKZuui02ml6oW0rs/B/s4A/vkvwC','Susanna','Whtie','N',NULL,'d6cbec51334a8054a1ef46508bf92badce119bd7fb6441bcb434ebc91f212f9d','Y',NULL,NULL,NULL,NULL,NULL,3,NULL),(3,'tephramancy@gatewise.co.uk','$2y$10$TBuc/1gDoi3ynm6fZuGJTe0Xc7sgZO0N4TLPykdpueUcCekUA0Ofy','Augustus','Stilwagen','N',NULL,'d48c3659bdf66e2eccce42f4111c95e8505ed3da2706c740890f62daf3f2a213','Y',NULL,NULL,NULL,NULL,NULL,2,NULL),(4,'unsanctimoniousness@coenoecic.edu','$2y$10$tPEKFJo6YUXslXqFex/NzuNKwXVjPJKNE7zVdcm2eQC9R1nBOD2j2','Jesenia','Scanio','N',NULL,'309e228a3c7d252f0939d8d0517d04d9b9f874bab4eec4c4218f952edd4d85d9','Y',NULL,NULL,NULL,NULL,NULL,4,NULL),(5,'extranatural@possumwood.net','$2y$10$Sw1JUKy1LsSXJI12GlqtB.LhySi9WNPgRvhB8y0p1CAco2PL1ZMbe','Geoffrey','Orlander','N',NULL,'e1261474304406f7a14844227fe4c74261544c2427b5b765ce6225f076879827','Y',NULL,NULL,NULL,NULL,NULL,5,NULL),(6,'ureteral@amorphy.edu','$2y$10$J5aXat5qPoP6Y6O.oXkLn.HsLLPB0N9ChDKk.0mNTZS/zRgNDjiG.','Irvin','Lizaola','N',NULL,'97577059f6d16ea6503739151273b18934916c74873a3f020e749e2c25892cb6','Y',NULL,NULL,NULL,NULL,NULL,2,NULL);
/*!40000 ALTER TABLE `users` ENABLE KEYS */;
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
) ENGINE=InnoDB AUTO_INCREMENT=7 DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `user_tokens`
--

LOCK TABLES `user_tokens` WRITE;
/*!40000 ALTER TABLE `user_tokens` DISABLE KEYS */;
INSERT INTO `user_tokens` VALUES (1,'463f4b5ff3885ebc0d33ee2c5ec732d2af69f6152cefd4becbffb7bf74cd9ac8','2018-05-04 03:24:14',NULL,1,1),(2,'d6cbec51334a8054a1ef46508bf92badce119bd7fb6441bcb434ebc91f212f9d','2017-05-03 03:24:14',NULL,2,1),(3,'d48c3659bdf66e2eccce42f4111c95e8505ed3da2706c740890f62daf3f2a213','2018-05-04 03:24:14',NULL,3,1),(4,'309e228a3c7d252f0939d8d0517d04d9b9f874bab4eec4c4218f952edd4d85d9','2018-05-04 03:24:14',NULL,4,1),(5,'e1261474304406f7a14844227fe4c74261544c2427b5b765ce6225f076879827','2018-05-04 03:24:14',NULL,5,1),(6,'97577059f6d16ea6503739151273b18934916c74873a3f020e749e2c25892cb6','2017-05-04 03:24:14',NULL,6,1);
/*!40000 ALTER TABLE `user_tokens` ENABLE KEYS */;
UNLOCK TABLES;
/*!40103 SET TIME_ZONE=@OLD_TIME_ZONE */;

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;

-- Dump completed on 2017-05-03 12:31:34
