CREATE TABLE `acl_accessrights` (
 `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
 `role_id` int(10) unsigned DEFAULT NULL COMMENT 'null if is a role, otherwise permission',
 `resource_id` int(10) unsigned,
 `role_name` varchar(60) COLLATE utf8_unicode_ci DEFAULT NULL COMMENT 'null if it is a permission',
 `created_date` datetime NOT NULL,
 `modified_date` datetime NULL,
 `created_by` int(10) unsigned NOT NULL,
 `modified_by` int(10) unsigned NULL,
 PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

CREATE TABLE `acl_resources` (
 `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
 `name` varchar(60) COLLATE utf8_unicode_ci NOT NULL,
 `description` varchar(256) COLLATE utf8_unicode_ci DEFAULT NULL,
 `created_date` datetime NOT NULL,
 `modified_date` datetime NULL,
 `created_by` int(10) unsigned NOT NULL,
 `modified_by` int(10) unsigned NULL,
 PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

CREATE TABLE `acl_users` (
 `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
 `login` varchar(60) COLLATE utf8_unicode_ci NOT NULL,
 `password` varchar(64) COLLATE utf8_unicode_ci DEFAULT NULL,
 `email` varchar(100) COLLATE utf8_unicode_ci NOT NULL,
 `display_name` varchar(250) COLLATE utf8_unicode_ci DEFAULT NULL,
 `activation_key` varchar(60) COLLATE utf8_unicode_ci DEFAULT NULL,
 `status` int(11) NOT NULL DEFAULT '0' COMMENT '0=not activated, 1=activated',
 `last_login_time` datetime DEFAULT NULL,
 `last_login_ip` varchar(60) COLLATE utf8_unicode_ci DEFAULT NULL,
 `created_date` datetime NOT NULL,
 `modified_date` datetime NULL,
 `created_by` int(10) unsigned NOT NULL,
 `modified_by` int(10) unsigned NULL,
 PRIMARY KEY (`id`),
 UNIQUE KEY `uc_acl_users_login` (`login`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

CREATE TABLE `acl_user_accessrights` (
 `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
 `user_id` int(10) unsigned NOT NULL,
 `accessright_id` int(10) unsigned NOT NULL,
 `created` datetime NOT NULL,
 PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

CREATE TABLE `articles` (
 `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
 `status` enum('pending','published','archived') DEFAULT NULL,
 `friendly_url` varchar(255) NOT NULL,
 `title` varchar(255) DEFAULT NULL,
 `content` longtext,
 `publish_date` datetime DEFAULT NULL,
 `created_date` datetime NOT NULL,
 `modified_date` datetime NULL,
 `created_by` int(10) unsigned NOT NULL,
 `modified_by` int(10) unsigned NULL,
 PRIMARY KEY (`id`),
 UNIQUE KEY `uc_articles_friendly_url_status` (`friendly_url`,`status`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8;

CREATE TABLE `tags` (
 `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
 `friendly_url` varchar(128) NOT NULL,
 `name` varchar(128) NOT NULL,
 `created_date` datetime NOT NULL,
 `modified_date` datetime NULL,
 `created_by` int(10) unsigned NOT NULL,
 `modified_by` int(10) unsigned NULL,
 PRIMARY KEY (`id`),
 UNIQUE KEY `uc_tags_friendly_url` (`friendly_url`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8; 

CREATE TABLE `article_tags` (
 `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
 `article_id` int(10) unsigned NOT NULL,
 `tag_id` int(10) unsigned NOT NULL,
 PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

