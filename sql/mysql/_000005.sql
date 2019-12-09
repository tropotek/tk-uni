-- ---------------------------------
-- Update SQL
--
-- Author: Michael Mifsud <info@tropotek.com>
-- ---------------------------------


CREATE TABLE IF NOT EXISTS course (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    institution_id INT UNSIGNED NOT NULL DEFAULT 0,
    coordinator_id INT UNSIGNED NOT NULL DEFAULT 0,         --  The userId of the course coordinator (staff)
    -- For grouped courses use , to separate them (EG: COM_02262, VETS 70003, VETS 30014), the first one will be used as the display code when not using the name
    code VARCHAR(64) NOT NULL DEFAULT '',                   -- The code or codes of a course
    name VARCHAR(128) NOT NULL DEFAULT '',                  -- A name or code whatever the user will identify as the correct course code
    email VARCHAR(255) NOT NULL DEFAULT '',
    email_signature TEXT,
    description TEXT,
    del TINYINT(1) NOT NULL DEFAULT 0,
    modified DATETIME NOT NULL,
    created DATETIME NOT NULL,
    KEY (institution_id),
    KEY (coordinator_id),
    KEY (code),
    KEY (name)
) ENGINE=InnoDB;

INSERT INTO course (institution_id, coordinator_id, code, name, email, modified, created) VALUES
    (1, 3, 'DEFAULT', 'Default', '', NOW(), NOW())
;

# DROP PROCEDURE IF EXISTS `?`;
# DELIMITER //
# CREATE PROCEDURE `?`()
# BEGIN
#   DECLARE CONTINUE HANDLER FOR SQLEXCEPTION BEGIN END;
#   alter table subject add course_id int(10) UNSIGNED default 0 not null after institution_id;
# END //
# DELIMITER ;
# CALL `?`();
# DROP PROCEDURE `?`;
alter table subject add course_id int(10) UNSIGNED default 0 not null after institution_id;

UPDATE subject SET course_id = 1 WHERE 1;





