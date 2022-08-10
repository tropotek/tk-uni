-- ---------------------------------
-- Update SQL
--
-- Author: Michael Mifsud <http://www.tropotek.com/>
-- ---------------------------------


alter table subject_pre_enrollment drop primary key;

alter table subject_pre_enrollment
  add constraint subject_pre_enrollment_pk
    unique (subject_id, uid, username, email);
;







