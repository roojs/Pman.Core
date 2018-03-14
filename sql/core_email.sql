CREATE  TABLE core_email (
  id INT(11) NOT NULL AUTO_INCREMENT ,
  subject TEXT ,
  bodytext TEXT ,
  plaintext TEXT ,
  name VARCHAR(255) NOT NULL DEFAULT '',
  updated_dt DATETIME NOT NULL ,
  from_email VARCHAR(254) NULL DEFAULT '',
  from_name VARCHAR(254) NULL DEFAULT '',
  owner_id INT(11) NOT NULL DEFAULT 0,
  is_system INT(2) NOT NULL DEFAULT 1,
  PRIMARY KEY (id)
);

ALTER TABLE core_email ADD COLUMN active INT(2) NOT NULL DEFAULT 1;
ALTER TABLE core_email MODIFY COLUMN active INT(2) NOT NULL DEFAULT 1;

-- need to store BCC data here.
ALTER TABLE core_email ADD COLUMN bcc_group_id INT(11) NOT NULL DEFAULT 0;
ALTER TABLE core_email MODIFY COLUMN bcc_group_id INT(11) NOT NULL DEFAULT 0;

-- each email template should have  a test class with a static method ::test_{name}
ALTER TABLE core_email ADD COLUMN test_class VARCHAR(254) NOT NULL DEFAULT '';
ALTER TABLE core_email MODIFY COLUMN test_class VARCHAR(254) NOT NULL DEFAULT '';

ALTER TABLE core_email CHANGE COLUMN   in_group to_group_id INT(11) NOT NULL DEFAULT -1;
ALTER TABLE core_email ADD COLUMN    to_group_id INT(11) NOT NULL DEFAULT -1;

UPDATE core_email SET updated_dt = NOW() where updated_dt IS NULL;

