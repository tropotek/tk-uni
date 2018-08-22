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
  ('Staff', 'staff', 'Institutions default staff role', 1, NOW(), NOW()),
  ('Student', 'student', 'Institutions default student role', 1, NOW(), NOW())
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




# INSERT INTO `user_role` (name, type, description, static, modified, created) VALUES
#   ('Coordinator', 'staff', 'Staff Coordinator role', 1, NOW(), NOW()),
#   ('Lecturer', 'staff', 'Staff Lecturer role', 1, NOW(), NOW())
# ;

INSERT INTO `user_permission` (`role_id`, `name`)
  VALUES
    (3, 'perm.manage.staff'),
    (3, 'perm.manage.student'),
    (3, 'perm.manage.subject')
;












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



-- ----------------------------
--  TEST DATA
-- ----------------------------
# INSERT INTO `user` (`role_id`, `institution_id`, `username`, `password` ,`name`, `email`, `active`, `hash`, `modified`, `created`)
# VALUES
#   (1, 0, 'admin', MD5(CONCAT('password', MD5('10admin'))), 'Administrator', 'admin@example.com', 1, MD5('10admin'), NOW(), NOW()),
#   (2, 0, 'unimelb', MD5(CONCAT('password', MD5('20unimelb'))), 'The University Of Melbourne', 'fvas@unimelb.edu.au', 1, MD5('20unimelb'), NOW(), NOW()),
#   (3, 1, 'staff', MD5(CONCAT('password', MD5('31staff'))), 'Unimelb Staff', 'staff@unimelb.edu.au', 1, MD5('31staff'), NOW(), NOW()),
#   (4, 1, 'student', MD5(CONCAT('password', MD5('41student'))), 'Unimelb Student', 'student@unimelb.edu.au', 1, MD5('41student'), NOW(), NOW())
# ;
#
# INSERT INTO `institution` (`user_id`, `name`, `email`, `description`, `logo`, `active`, `hash`, `modified`, `created`)
#   VALUES
#     (2, 'The University Of Melbourne', 'admin@unimelb.edu.au', 'This is a test institution for this app', '', 1, MD5('1'), NOW(), NOW())
# ;
#
# INSERT INTO `subject` (`institution_id`, `name`, `code`, `email`, `description`, `date_start`, `date_end`, `modified`, `created`)
#   VALUES (1, 'Poultry Industry Field Work', 'VETS50001_2014_SM1', 'subject@unimelb.edu.au', '',  NOW(), DATE_ADD(NOW(), INTERVAL 190 DAY), NOW(), NOW() )
# --  VALUES (1, 'Poultry Industry Field Work', 'VETS50001_2014_SM1', 'subject@unimelb.edu.au', '',  NOW(), DATE_ADD(CURRENT_DATETIME, INTERVAL 190 DAY), NOW(), NOW() )
# ;
#
# INSERT INTO `subject_has_user` (`user_id`, `subject_id`)
# VALUES
#   (3, 1),
#   (4, 1)
# ;
#
# INSERT INTO `subject_pre_enrollment` (`subject_id`, `email`)
# VALUES
#   (1, 'student@unimelb.edu.au')
# ;



