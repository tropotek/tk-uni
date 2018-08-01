-- ---------------------------------
-- Update SQL
--
-- Author: Michael Mifsud <info@tropotek.com>
-- ---------------------------------



ALTER TABLE user ADD role_id int DEFAULT 0 NOT NULL;
ALTER TABLE user
  MODIFY COLUMN role_id int NOT NULL DEFAULT 0 AFTER id;


-- --------------------------------------------------------
--
-- Table structure for table `user_role`
--
CREATE TABLE IF NOT EXISTS `user_role` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT PRIMARY KEY,
  `name` varchar(64) NOT NULL DEFAULT '',                     -- 'Name with only alpha chars and underscores [a-zA-Z_-]
  `type` varchar(64) NOT NULL DEFAULT '',                     -- [admin|client|staff|student, etc]The system role type to use for templates and homeUrls, etc
  `description` TEXT,
  `active` TINYINT(1) NOT NULL DEFAULT 1,
  `static` TINYINT(1) NOT NULL DEFAULT 0,                     -- If record is static then no-one can delete or modify it
  `del` TINYINT(1) NOT NULL DEFAULT 0,
  `modified` DATETIME NOT NULL,
  `created` DATETIME NOT NULL,
  UNIQUE KEY `name` (`name`)
) ENGINE=InnoDB;

-- --------------------------------------------------------
-- Used for custom roles per institution
-- --------------------------------------------------------
CREATE TABLE IF NOT EXISTS `user_role_institution` (
  `role_id` int(10) unsigned NOT NULL,
  `institution_id` int(10) unsigned NOT NULL,
  PRIMARY KEY (`role_id`, `institution_id`)
) ENGINE=InnoDB;


INSERT INTO `user_role` (name, type, description, static, modified, created) VALUES
  ('Administrator', 'admin', 'System administrator role', 1, NOW(), NOW()),
  ('Client', 'client', 'Institution user account role', 1, NOW(), NOW()),
  ('Staff', 'staff', 'Institutions Default staff role', 1, NOW(), NOW()),
  ('Student', 'student', 'Institutions Default student role', 1, NOW(), NOW())
;


UPDATE `user` a, `user_role` b
SET a.`role_id` = b.`id`
WHERE b.`type` = a.`role`;

alter table user drop column role;


-- --------------------------------------------------------
-- The role permission table
-- Table structure for table `user_permission`
--
CREATE TABLE IF NOT EXISTS `user_permission` (
  `role_id` int(10) unsigned NOT NULL,
  `name` varchar(128) NOT NULL DEFAULT '',
  PRIMARY KEY `role_id_key` (`role_id`, `name`)
) ENGINE=InnoDB;







-- TODO: remove the username, password and role fields from the user table
-- TODO: Consider the positived and negitives
-- --------------------------------------------------------
--
-- Table structure for table `user_auth`
--
# CREATE TABLE IF NOT EXISTS `user_auth` (
#   `institution_id` INT(10) UNSIGNED NOT NULL DEFAULT 0,
#   `username` varchar(128) NOT NULL,
#   `password` varchar(128) NOT NULL DEFAULT '',
#   PRIMARY KEY (`institution_id`, `username`)
# ) ENGINE=InnoDB;
#
# INSERT INTO `user_auth` (`username`, `password`)
#   (
#     SELECT a.institution_id, a.username, a.password
#     FROM `user` a
#   );
# alter table user drop column username;
# alter table user drop column password;





