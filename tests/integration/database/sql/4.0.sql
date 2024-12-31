CREATE TABLE `{$prefix}redirection_items` (
	`id` int(11) unsigned NOT NULL AUTO_INCREMENT,
	`url` mediumtext COLLATE utf8mb4_unicode_520_ci NOT NULL,
	`match_url` varchar(2000) COLLATE utf8mb4_unicode_520_ci DEFAULT NULL,
	`match_data` text COLLATE utf8mb4_unicode_520_ci,
	`regex` int(11) unsigned NOT NULL DEFAULT '0',
	`position` int(11) unsigned NOT NULL DEFAULT '0',
	`last_count` int(10) unsigned NOT NULL DEFAULT '0',
	`last_access` datetime NOT NULL DEFAULT '1970-01-01 00:00:00',
	`group_id` int(11) NOT NULL DEFAULT '0',
	`status` enum('enabled','disabled') COLLATE utf8mb4_unicode_520_ci NOT NULL DEFAULT 'enabled',
	`action_type` varchar(20) COLLATE utf8mb4_unicode_520_ci NOT NULL,
	`action_code` int(11) unsigned NOT NULL,
	`action_data` mediumtext COLLATE utf8mb4_unicode_520_ci,
	`match_type` varchar(20) COLLATE utf8mb4_unicode_520_ci NOT NULL,
	`title` text COLLATE utf8mb4_unicode_520_ci,
	PRIMARY KEY (`id`),
	KEY `url` (`url`(191)),
	KEY `status` (`status`),
	KEY `regex` (`regex`),
	KEY `group_idpos` (`group_id`,`position`),
	KEY `group` (`group_id`),
	KEY `match_url` (`match_url`(191))
);

CREATE TABLE `{$prefix}redirection_groups` (
	`id` int(11) unsigned NOT NULL AUTO_INCREMENT,
	`name` varchar(50) NOT NULL,
	`tracking` int(11) NOT NULL DEFAULT '1',
	`module_id` int(11) unsigned NOT NULL DEFAULT '0',
	`status` enum('enabled','disabled') NOT NULL DEFAULT 'enabled',
	`position` int(11) unsigned NOT NULL DEFAULT '0',
	PRIMARY KEY (`id`),
	KEY `module_id` (`module_id`),
	KEY `status` (`status`)
);

CREATE TABLE `{$prefix}redirection_logs` (
	`id` int(11) unsigned NOT NULL AUTO_INCREMENT,
	`created` datetime NOT NULL,
	`url` mediumtext NOT NULL,
	`sent_to` mediumtext COLLATE utf8mb4_unicode_520_ci,
	`agent` mediumtext NOT NULL,
	`referrer` mediumtext COLLATE utf8mb4_unicode_520_ci,
	`redirection_id` int(11) unsigned DEFAULT NULL,
	`ip` varchar(45) DEFAULT NULL,
	`module_id` int(11) unsigned NOT NULL,
	`group_id` int(11) unsigned DEFAULT NULL,
	PRIMARY KEY (`id`),
	KEY `created` (`created`),
	KEY `redirection_id` (`redirection_id`),
	KEY `ip` (`ip`),
	KEY `group_id` (`group_id`),
	KEY `module_id` (`module_id`)
);

CREATE TABLE `{$prefix}redirection_404` (
	`id` int(11) unsigned NOT NULL AUTO_INCREMENT,
	`created` datetime NOT NULL,
	`url` varchar(255) NOT NULL DEFAULT '',
	`agent` varchar(255) DEFAULT NULL,
	`referrer` varchar(255) DEFAULT NULL,
	`ip` varchar(45) DEFAULT NULL,
	PRIMARY KEY (`id`),
	KEY `created` (`created`),
	KEY `url` (`url`(191)),
	KEY `referrer` (`referrer`(191)),
	KEY `ip` (`ip`)
);
