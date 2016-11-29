CREATE TABLE IF NOT EXISTS `PREFIX_brad_filter_template` (
  `id_brad_filter_template` INT(11) UNSIGNED NOT NULL AUTO_INCREMENT,
  `name` VARCHAR(100) NOT NULL,
  `categories` INT(11) NOT NULL,
  `date_add` DATETIME NOT NULL,
  `date_upd` DATETIME NOT NULL,
  PRIMARY KEY (`id_brad_filter_template`)
) ENGINE=ENGINE_TYPE DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `PREFIX_brad_filter_template_shop` (
  `id_brad_filter_template` INT(11) UNSIGNED NOT NULL,
  `id_shop` INT(11) UNSIGNED NOT NULL,
  PRIMARY KEY (`id_brad_filter_template`, `id_shop`)
) ENGINE=ENGINE_TYPE DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `PREFIX_brad_filter_template_category` (
  `id_brad_filter_template` INT(11) UNSIGNED NOT NULL,
  `id_category` INT(11) UNSIGNED NOT NULL
) ENGINE=ENGINE_TYPE DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `PREFIX_brad_filter_template_filter` (
  `id_brad_filter_template` INT(11) UNSIGNED NOT NULL,
  `id_brad_filter` INT(11) UNSIGNED NOT NULL,
  `position` INT(11) UNSIGNED NOT NULL
) ENGINE=ENGINE_TYPE DEFAULT CHARSET=utf8;
