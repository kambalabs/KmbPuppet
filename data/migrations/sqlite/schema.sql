DROP TABLE IF EXISTS `environments`;
CREATE TABLE `environments` (
  `id`   INTEGER NOT NULL PRIMARY KEY AUTOINCREMENT,
  `name` VARCHAR(256)
);

DROP TABLE IF EXISTS `environments_paths`;
CREATE TABLE `environments_paths` (
  `ancestor_id`   INT NOT NULL DEFAULT 0,
  `descendant_id` INT NOT NULL DEFAULT 0,
  `length` TINYINT NOT NULL DEFAULT 0,
  PRIMARY KEY (`ancestor_id`, `descendant_id`)
);
CREATE INDEX `environments_paths_adl` ON `environments_paths` (`ancestor_id`, `descendant_id`, `length`);
CREATE INDEX `environments_paths_dl` ON `environments_paths` (`descendant_id`, `length`);
