-- First import structure.sql from the core repo!

CREATE TABLE IF NOT EXISTS `simple_options` (
  `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT COMMENT 'Unique option identifier',
  `name` VARCHAR(64) COMMENT 'Unique option name to fetch the value',
  `value` TEXT COMMENT 'Value saved in the current option',

  PRIMARY KEY (`id`),
  UNIQUE KEY `name` (`name`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_520_ci;
