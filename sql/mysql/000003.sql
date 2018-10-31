-- ---------------------------------
-- Update SQL
--
-- Author: Michael Mifsud <info@tropotek.com>
-- ---------------------------------


ALTER TABLE institution ADD address varchar(255) DEFAULT '' NULL;
ALTER TABLE institution ADD postcode varchar(16) DEFAULT '' NULL;
ALTER TABLE institution ADD country varchar(128) DEFAULT '' NULL;
ALTER TABLE institution ADD state varchar(128) DEFAULT '' NULL;
ALTER TABLE institution ADD city varchar(128) DEFAULT '' NULL;
ALTER TABLE institution ADD street varchar(128) DEFAULT '' NULL;
ALTER TABLE institution ADD feature varchar(255) DEFAULT '' NULL;
ALTER TABLE institution ADD phone varchar(32) DEFAULT '' NULL;
ALTER TABLE institution
  MODIFY COLUMN created datetime NOT NULL AFTER address,
  MODIFY COLUMN modified datetime NOT NULL AFTER address,
  MODIFY COLUMN del tinyint(1) NOT NULL DEFAULT 0 AFTER address,
  MODIFY COLUMN hash varchar(128) NOT NULL DEFAULT '' AFTER address,
  MODIFY COLUMN active tinyint(1) NOT NULL DEFAULT 1 AFTER address,
  MODIFY COLUMN phone varchar(32) DEFAULT '' AFTER email;

alter table user drop column display_name;


ALTER TABLE user ADD phone varchar(32) DEFAULT '' NULL;
ALTER TABLE user
  MODIFY COLUMN phone varchar(32) DEFAULT '' AFTER image,
  MODIFY COLUMN active tinyint(1) NOT NULL DEFAULT 1 AFTER notes,
  MODIFY COLUMN email varchar(168) NOT NULL DEFAULT '' AFTER image;


UPDATE user_permission SET name = 'type.admin' WHERE name = 'perm.admin';
UPDATE user_permission SET name = 'type.client' WHERE name = 'perm.client';
UPDATE user_permission SET name = 'type.staff' WHERE name = 'perm.staff';
UPDATE user_permission SET name = 'type.student' WHERE name = 'perm.student';
UPDATE user_permission SET name = 'type.coordinator' WHERE name = 'perm.coordinator';
UPDATE user_permission SET name = 'type.lecturer' WHERE name = 'perm.lecturer';




