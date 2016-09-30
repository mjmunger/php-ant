DROP TABLE IF EXISTS `users_roles`;
CREATE TABLE `users_roles` (
  `users_roles_id` int(11) NOT NULL AUTO_INCREMENT,
  `users_roles_title` varchar(45) DEFAULT NULL,
  `users_roles_role` varchar(1) DEFAULT 'U' COMMENT 'A - Administrator\nU - Standard User',
  PRIMARY KEY (`users_roles_id`)
) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=latin1;

LOCK TABLES `users_roles` WRITE;
INSERT INTO `users_roles` VALUES (1,'Admin','A');
UNLOCK TABLES;

DROP TABLE IF EXISTS `users`;
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
) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=latin1;


DROP TABLE IF EXISTS `email_log`;

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

DROP TABLE IF EXISTS `settings`;
CREATE TABLE `settings` (
  `settings_id` int(11) NOT NULL AUTO_INCREMENT,
  `settings_key` varchar(255) DEFAULT NULL,
  `settings_value` text,
  PRIMARY KEY (`settings_id`)
) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=latin1;



