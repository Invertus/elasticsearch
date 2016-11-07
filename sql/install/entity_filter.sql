CREATE TABLE IF NOT EXISTS `PREFIX_brad_filter` (
  `id_brad_filter` INT(11) UNSIGNED NOT NULL AUTO_INCREMENT,
  `filter_type` INT(11) NOT NULL,
  `id_key` INT(11) UNSIGNED NOT NULL DEFAULT 0,
  `filter_style` INT(11) UNSIGNED NOT NULL DEFAULT 0,
  `custom_height` INT(11) NOT NULL DEFAULT 0,
  `criteria_sign` VARCHAR(10) NOT NULL DEFAULT '',
  `criteria_order_by` INT(11) UNSIGNED NOT NULL DEFAULT 0,
  `criteria_order_way` INT(11) UNSIGNED NOT NULL DEFAULT 0,
  PRIMARY KEY (`id_brad_filter`)
) ENGINE=ENGINE_TYPE DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `PREFIX_brad_filter_lang` (
  `id_brad_filter` INT(11) UNSIGNED NOT NULL,
  `id_lang` INT(11) UNSIGNED NOT NULL,
  `name` VARCHAR(100) NOT NULL,
  PRIMARY KEY (`id_brad_filter`, `id_lang`)
) ENGINE=ENGINE_TYPE DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `PREFIX_brad_filter_shop` (
  `id_brad_filter` INT(11) UNSIGNED NOT NULL,
  `id_shop` INT(11) UNSIGNED NOT NULL,
  PRIMARY KEY (`id_brad_filter`, `id_shop`)
) ENGINE=ENGINE_TYPE DEFAULT CHARSET=utf8;
