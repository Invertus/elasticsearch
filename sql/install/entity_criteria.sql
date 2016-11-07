CREATE TABLE IF NOT EXISTS `PREFIX_brad_criteria` (
  `id_brad_criteria` INT(11) UNSIGNED NOT NULL AUTO_INCREMENT,
  `id_brad_filter` INT(11) UNSIGNED NOT NULL,
  `min_value` DECIMAL(20,6) NOT NULL,
  `max_value` DECIMAL(20,6) NOT NULL,
  `position` INT(11) UNSIGNED NOT NULL DEFAULT 0,
  PRIMARY KEY (`id_brad_criteria`)
) ENGINE=ENGINE_TYPE DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `PREFIX_brad_criteria_shop` (
  `id_brad_criteria` INT(11) UNSIGNED NOT NULL,
  `id_shop` INT(11) UNSIGNED NOT NULL,
  PRIMARY KEY (`id_brad_criteria`, `id_shop`)
) ENGINE=ENGINE_TYPE DEFAULT CHARSET=utf8;
