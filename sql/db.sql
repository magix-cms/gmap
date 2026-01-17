CREATE TABLE IF NOT EXISTS `mc_gmap` (
  `id_gmap` smallint(3) unsigned NOT NULL AUTO_INCREMENT,
  `date_register` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id_gmap`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;

CREATE TABLE IF NOT EXISTS `mc_gmap_content` (
  `id_content` smallint(3) NOT NULL AUTO_INCREMENT,
  `id_gmap` smallint(3) unsigned NOT NULL,
  `id_lang` smallint(3) unsigned NOT NULL,
  `name_gmap` varchar(175) DEFAULT NULL,
  `content_gmap` text,
  `last_update` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `published_gmap` smallint(1) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id_content`),
  KEY `id_gmap` (`id_gmap`),
  KEY `id_lang` (`id_lang`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;

ALTER TABLE `mc_gmap_content`
  ADD CONSTRAINT `mc_gmap_content_ibfk_2` FOREIGN KEY (`id_lang`) REFERENCES `mc_lang` (`id_lang`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `mc_gmap_content_ibfk_1` FOREIGN KEY (`id_gmap`) REFERENCES `mc_gmap` (`id_gmap`) ON DELETE CASCADE ON UPDATE CASCADE;

CREATE TABLE IF NOT EXISTS `mc_gmap_address` (
  `id_address` smallint(5) unsigned NOT NULL AUTO_INCREMENT,
  `img_address` varchar(150) DEFAULT NULL,
  `order_address` smallint(5) unsigned NOT NULL DEFAULT '0',
  `date_register` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id_address`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;

CREATE TABLE IF NOT EXISTS `mc_gmap_address_content` (
  `id_content` smallint(5) unsigned NOT NULL AUTO_INCREMENT,
  `id_address` smallint(5) unsigned NOT NULL,
  `id_lang` smallint(3) unsigned NOT NULL,
  `company_address` varchar(50) NOT NULL,
  `content_address` text,
  `address_address` varchar(175) NOT NULL,
  `postcode_address` varchar(12) NOT NULL,
  `country_address` varchar(30) NOT NULL,
  `city_address` varchar(40) NOT NULL,
  `phone_address` varchar(45) DEFAULT NULL,
  `mobile_address` varchar(45) DEFAULT NULL,
  `fax_address` varchar(45) DEFAULT NULL,
  `email_address` varchar(150) DEFAULT NULL,
  `vat_address` varchar(80) DEFAULT NULL,
  `lat_address` double NOT NULL,
  `lng_address` double NOT NULL,
  `link_address` varchar(200) DEFAULT NULL,
  `blank_address` SMALLINT(1) UNSIGNED DEFAULT '0',
  `img_address` varchar(125) DEFAULT NULL,
  `last_update` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `published_address` smallint(1) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id_content`),
  KEY `id_lang` (`id_lang`),
  KEY `id_address` (`id_address`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;

ALTER TABLE `mc_gmap_address_content`
  ADD CONSTRAINT `mc_gmap_address_content_ibfk_1` FOREIGN KEY (`id_address`) REFERENCES `mc_gmap_address` (`id_address`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `mc_gmap_address_content_ibfk_2` FOREIGN KEY (`id_lang`) REFERENCES `mc_lang` (`id_lang`) ON DELETE CASCADE ON UPDATE CASCADE;

CREATE TABLE IF NOT EXISTS `mc_gmap_config` (
  `id_gmap_config` smallint(4) unsigned NOT NULL AUTO_INCREMENT,
  `config_id` varchar(100) NOT NULL,
  `config_value` text,
  PRIMARY KEY (`id_gmap_config`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;

INSERT INTO `mc_gmap_config` (`id_gmap_config`, `config_id`, `config_value`) VALUES
(NULL, 'markerColor', '#f3483c'),
(NULL, 'api_key', NULL),
(NULL, 'appId', NULL);

INSERT INTO `mc_config_img` (`id_config_img`, `module_img`, `attribute_img`, `width_img`, `height_img`, `type_img`, `prefix_img`, `resize_img`) VALUES
(null, 'gmap', 'gmap', '360', '270', 'small', 's', 'adaptive'),
(null, 'gmap', 'gmap', '480', '360', 'medium', 'm', 'adaptive'),
(null, 'gmap', 'gmap', '960', '720', 'large', 'l', 'adaptive');