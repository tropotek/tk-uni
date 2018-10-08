-- ---------------------------------
-- Update SQL
--
-- Author: Michael Mifsud <info@tropotek.com>
-- ---------------------------------



ALTER TABLE subject_pre_enrollment ADD username VARCHAR(64) DEFAULT '' NOT NULL;
ALTER TABLE subject_pre_enrollment MODIFY COLUMN email varchar(168) NOT NULL DEFAULT '' AFTER uid;


