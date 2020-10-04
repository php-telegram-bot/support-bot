-- First import structure.sql from the core repo!

CREATE TABLE IF NOT EXISTS `simple_options` (
  `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT COMMENT 'Unique option identifier',
  `name` VARCHAR(64) COMMENT 'Unique option name to fetch the value',
  `value` TEXT COMMENT 'Value saved in the current option',

  PRIMARY KEY (`id`),
  UNIQUE KEY `name` (`name`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_520_ci;

-- Version 0.7.0
ALTER TABLE `user` ADD COLUMN `joined_at` TIMESTAMP NULL COMMENT 'Timestamp when the user joined the support group.';
ALTER TABLE `user` ADD COLUMN `kicked_at` TIMESTAMP NULL COMMENT 'Timestamp when the user was kicked from the support group.';
ALTER TABLE `user` ADD COLUMN `activated_at` TIMESTAMP NULL COMMENT 'Timestamp when the user has agreed to the rules and has been allowed to post in the support group.';
