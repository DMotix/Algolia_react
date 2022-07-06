CREATE TABLE IF NOT EXISTS `PREFIX_ads_af_search_alert` (
  `id_ads_af_search_alert` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `id_customer` int(10) unsigned NOT NULL DEFAULT '0',
  `marque` varchar(255),
  `modele` varchar(255),
  `energie` varchar(255),
  `kilometrage_max` int(10),
  `annee_max` int(10),
  `prix_ttc_max` int(10),
  `active` tinyint(4) NOT NULL DEFAULT '1',
  `date_add` datetime NOT NULL,
  PRIMARY KEY (`id_ads_af_search_alert`),
  KEY `active` (`active`)
) ENGINE=MYSQL_ENGINE DEFAULT CHARSET=utf8 AUTO_INCREMENT=1;

CREATE TABLE IF NOT EXISTS `PREFIX_ads_af_search_alert_shop` (
  `id_ads_af_search_alert` int(11) NOT NULL,
  `id_shop` int(11) NOT NULL,
  `active` tinyint(4) NOT NULL DEFAULT '1',
  PRIMARY KEY (`id_ads_af_search_alert`, `id_shop`)
) ENGINE=MYSQL_ENGINE DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `PREFIX_ads_af_seo` (
  `id_seo` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `criteria` text NOT NULL,
  `seo_key` varchar(32) NOT NULL,
  `deleted` tinyint(3) unsigned NOT NULL DEFAULT '0',
  `auto` tinyint(4) NOT NULL DEFAULT '0',
  `active` tinyint(4) NOT NULL DEFAULT '1',
  `date_add` datetime NOT NULL,
  PRIMARY KEY (`id_seo`),
  KEY `active` (`active`),
  KEY `deleted` (`deleted`),
  UNIQUE KEY `seo_key` (`seo_key`)
) ENGINE=MYSQL_ENGINE DEFAULT CHARSET=utf8 AUTO_INCREMENT=1;

CREATE TABLE IF NOT EXISTS `PREFIX_ads_af_seo_shop` (
  `id_seo` int(11) NOT NULL,
  `id_shop` int(11) NOT NULL,
  `date_add` datetime NOT NULL,
  `deleted` tinyint(3) unsigned NOT NULL DEFAULT '0',
  `auto` tinyint(4) NOT NULL DEFAULT '0',
  `active` tinyint(4) NOT NULL DEFAULT '1',
  PRIMARY KEY (`id_seo`, `id_shop`)
) ENGINE=MYSQL_ENGINE DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `PREFIX_ads_af_seo_lang` (
  `id_seo` int(10) unsigned NOT NULL,
  `id_lang` int(10) unsigned NOT NULL,
  `id_shop` int(10) unsigned NOT NULL DEFAULT '1',
  `meta_title` varchar(128) NOT NULL,
  `meta_description` varchar(255) NOT NULL,
  `meta_keywords` varchar(255) NOT NULL,
  `title` varchar(128) NOT NULL,
  `description_top` text NOT NULL,
  `description_footer` text NOT NULL,
  `seo_url` varchar(128) NOT NULL,
  UNIQUE KEY `id_seo` (`id_seo`, `id_lang`, `id_shop`),
  UNIQUE KEY `seo_url` (`seo_url`, `id_lang`, `id_shop`)
) ENGINE=MYSQL_ENGINE DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `PREFIX_ads_af_seo_massive` (
  `id_seo_massive` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `cronjob` tinyint(4) NOT NULL DEFAULT '1',
  `active` tinyint(4) NOT NULL DEFAULT '1',
  `position` int(11) unsigned NOT NULL,
  `date_add` datetime NOT NULL,
  PRIMARY KEY (`id_seo_massive`),
  KEY `active` (`active`)
) ENGINE=MYSQL_ENGINE DEFAULT CHARSET=utf8 AUTO_INCREMENT=1;

CREATE TABLE IF NOT EXISTS `PREFIX_ads_af_seo_massive_shop` (
  `id_seo_massive` int(11) NOT NULL,
  `id_shop` int(11) NOT NULL,
  `date_add` datetime NOT NULL,
  `position` int(11) unsigned NOT NULL,
  `active` tinyint(4) NOT NULL DEFAULT '1',
  PRIMARY KEY (`id_seo_massive`, `id_shop`)
) ENGINE=MYSQL_ENGINE DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `PREFIX_ads_af_seo_massive_lang` (
  `id_seo_massive` int(10) unsigned NOT NULL,
  `id_lang` int(10) unsigned NOT NULL,
  `id_shop` int(10) unsigned NOT NULL DEFAULT '1',
  `criteria` text NOT NULL,
  `meta_title` varchar(128) NOT NULL,
  `meta_description` varchar(255) NOT NULL,
  `meta_keywords` varchar(255) NOT NULL,
  `title` varchar(128) NOT NULL,
  `description_top` text NOT NULL,
  `description_footer` text NOT NULL,
  UNIQUE KEY `id_seo_massive` (`id_seo_massive`, `id_lang`, `id_shop`)
) ENGINE=MYSQL_ENGINE DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `PREFIX_ads_af_seo_crosslinks` (
  `id_seo` int(10) unsigned NOT NULL,
  `id_seo_linked` int(10) unsigned NOT NULL,
  UNIQUE KEY `id_seo` (`id_seo`, `id_seo_linked`)
) ENGINE=MYSQL_ENGINE DEFAULT CHARSET=utf8;