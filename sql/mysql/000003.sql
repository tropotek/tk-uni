-- ---------------------------------
-- Update SQL
--
-- Author: Michael Mifsud <info@tropotek.com>
-- ---------------------------------


ALTER TABLE institution ADD feature varchar(255) DEFAULT '' NULL AFTER logo;
ALTER TABLE institution ADD phone varchar(32) DEFAULT '' NULL AFTER email;

ALTER TABLE institution ADD address varchar(255) DEFAULT '' NULL AFTER feature;
ALTER TABLE institution ADD country varchar(128) DEFAULT '' NULL AFTER feature;
ALTER TABLE institution ADD postcode varchar(16) DEFAULT '' NULL AFTER feature;
ALTER TABLE institution ADD state varchar(128) DEFAULT '' NULL AFTER feature;
ALTER TABLE institution ADD city varchar(128) DEFAULT '' NULL AFTER feature;
ALTER TABLE institution ADD street varchar(128) DEFAULT '' NULL AFTER feature;

ALTER TABLE institution ADD `map_zoom` DECIMAL(4, 2) NOT NULL DEFAULT 14 AFTER address;
ALTER TABLE institution ADD `map_lng` DECIMAL(11, 8) NOT NULL DEFAULT 0 AFTER address;
ALTER TABLE institution ADD `map_lat` DECIMAL(11, 8) NOT NULL DEFAULT 0 AFTER address;


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




