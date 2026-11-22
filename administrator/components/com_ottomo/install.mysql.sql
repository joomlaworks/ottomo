CREATE TABLE IF NOT EXISTS `#__ottomo_email_campaigns` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `subject` varchar(255) NOT NULL DEFAULT '',
  `preheader` varchar(255) NOT NULL DEFAULT '',
  `senderName` varchar(255) NOT NULL DEFAULT '',
  `senderEmail` varchar(255) NOT NULL DEFAULT '',
  `plain` mediumtext NOT NULL,
  `template` int(10) unsigned NOT NULL DEFAULT 0,
  `published` tinyint(1) NOT NULL DEFAULT 0,
  `sent` date DEFAULT NULL,
  `snapshot` longtext NOT NULL,
  PRIMARY KEY (`id`),
  KEY `idx_published_sent` (`published`, `sent`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 DEFAULT COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `#__ottomo_email_campaigns_data` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `campaignID` int(10) unsigned NOT NULL DEFAULT 0,
  `position` varchar(255) NOT NULL DEFAULT '',
  `content` mediumtext NOT NULL,
  PRIMARY KEY (`id`),
  KEY `idx_newsletter` (`campaignID`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 DEFAULT COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `#__ottomo_email_campaigns_xref` (
  `topicID` int(10) unsigned NOT NULL,
  `campaignID` int(10) unsigned NOT NULL,
  PRIMARY KEY (`topicID`, `campaignID`),
  KEY `idx_newsletter` (`campaignID`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 DEFAULT COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `#__ottomo_statistics` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `typeID` tinyint(3) unsigned NOT NULL,
  `campaignID` int(10) unsigned NOT NULL,
  `subscriberID` int(10) unsigned NOT NULL,
  `date` date NOT NULL,
  `time` time NOT NULL,
  `link` varchar(2048) NOT NULL DEFAULT '',
  PRIMARY KEY (`id`),
  KEY `idx_campaign` (`campaignID`, `typeID`, `subscriberID`),
  KEY `idx_type_date` (`typeID`, `date`),
  KEY `idx_subscriber` (`subscriberID`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 DEFAULT COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `#__ottomo_statistics_types` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `type` varchar(50) NOT NULL DEFAULT '',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 DEFAULT COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `#__ottomo_subscribers` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `status` tinyint(1) NOT NULL DEFAULT 0,
  `activation` varchar(100) NOT NULL DEFAULT '',
  `name` varchar(255) NOT NULL DEFAULT '',
  `email` varchar(255) NOT NULL,
  `address` varchar(255) NOT NULL DEFAULT '',
  `city` varchar(255) NOT NULL DEFAULT '',
  `state` varchar(255) NOT NULL DEFAULT '',
  `zip` varchar(255) NOT NULL DEFAULT '',
  `country` varchar(255) NOT NULL DEFAULT '',
  `phone` varchar(255) NOT NULL DEFAULT '',
  `mobile` varchar(255) NOT NULL DEFAULT '',
  `fax` varchar(255) NOT NULL DEFAULT '',
  `birthday` date DEFAULT NULL,
  `company` varchar(255) NOT NULL DEFAULT '',
  `position` varchar(255) NOT NULL DEFAULT '',
  `vat` varchar(255) NOT NULL DEFAULT '',
  `tax` varchar(255) NOT NULL DEFAULT '',
  `created` datetime DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `idx_email` (`email`(191)),
  KEY `idx_status` (`status`),
  KEY `idx_activation` (`activation`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 DEFAULT COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `#__ottomo_subscribers_xref` (
  `topicID` int(10) unsigned NOT NULL,
  `subscriberID` int(10) unsigned NOT NULL,
  PRIMARY KEY (`topicID`, `subscriberID`),
  KEY `idx_subscriber` (`subscriberID`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 DEFAULT COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `#__ottomo_templates` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(255) NOT NULL DEFAULT '',
  `folder` varchar(255) NOT NULL,
  `manifest` text NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `idx_folder` (`folder`(191))
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 DEFAULT COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `#__ottomo_topics` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(255) NOT NULL DEFAULT '',
  `published` tinyint(1) NOT NULL DEFAULT 0,
  PRIMARY KEY (`id`),
  KEY `idx_published_name` (`published`, `name`(191))
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 DEFAULT COLLATE=utf8mb4_unicode_ci;
