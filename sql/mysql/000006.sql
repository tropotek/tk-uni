-- ---------------------------------
-- Update SQL
--
-- Author: Michael Mifsud <http://www.tropotek.com/>
-- ---------------------------------



-- Create the new name fields
alter table user
    add name_first varchar(128) default '' not null after name;
alter table user
    add name_last varchar(128) default '' not null after name_first;

-- Fill in the name fields
UPDATE user SET name_first = SUBSTRING(name, 1, LOCATE(' ', name) - 1) WHERE TRIM(name) != '';
UPDATE user SET name_last = SUBSTRING(name, LOCATE(' ', name) + 1) WHERE TRIM(name) != '';

UPDATE user SET name_first = username WHERE TRIM(name_first) = '';

-- Move the display name field as I think we may keep this. (Uni only)
-- alter table user modify display_name varchar(255) default '' not null after name_last;










