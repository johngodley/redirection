CREATE TABLE `{$prefix}redirection_items` (
	`id` int(11) unsigned NOT NULL AUTO_INCREMENT,
	`url` mediumtext NOT NULL,
	`regex` int(11) unsigned NOT NULL DEFAULT '0',
	`position` int(11) unsigned NOT NULL DEFAULT '0',
	`last_count` int(10) unsigned NOT NULL DEFAULT '0',
	`last_access` datetime NOT NULL DEFAULT '1970-01-01 00:00:00',
	`group_id` int(11) NOT NULL DEFAULT '0',
	`status` enum('enabled','disabled') NOT NULL DEFAULT 'enabled',
	`action_type` varchar(20) NOT NULL,
	`action_code` int(11) unsigned NOT NULL,
	`action_data` mediumtext,
	`match_type` varchar(20) NOT NULL,
	`title` varchar(50) DEFAULT NULL,
	PRIMARY KEY (`id`)
);

CREATE TABLE `{$prefix}redirection_groups` (
	`id` int(11) unsigned NOT NULL AUTO_INCREMENT,
	`name` varchar(50) NOT NULL,
	`tracking` int(11) NOT NULL DEFAULT '1',
	`module_id` int(11) unsigned NOT NULL DEFAULT '0',
	`status` enum('enabled','disabled') NOT NULL DEFAULT 'enabled',
	`position` int(11) unsigned NOT NULL DEFAULT '0',
	PRIMARY KEY (`id`)
);

CREATE TABLE `{$prefix}redirection_logs` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `created` datetime NOT NULL,
  `url` mediumtext NOT NULL,
  `sent_to` mediumtext,
  `agent` mediumtext NOT NULL,
  `referrer` mediumtext,
  `redirection_id` int(11) unsigned DEFAULT NULL,
  `ip` varchar(17) NOT NULL DEFAULT '',
  `module_id` int(11) unsigned NOT NULL,
  `group_id` int(11) unsigned DEFAULT NULL,
  PRIMARY KEY (`id`)
);

CREATE TABLE `{$prefix}redirection_modules` (
  `id` int(11) unsigned NOT NULL auto_increment,
  `type` varchar(20) NOT NULL default '',
  `name` varchar(50) NOT NULL default '',
  `options` mediumtext,
  PRIMARY KEY ( `id`),
  KEY `name` (`name`),
  KEY `type` (`type`)
);
