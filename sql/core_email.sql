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

ALTER TABLE core_email CHANGE COLUMN active active INT(2) NOT NULL DEFAULT 1;
ALTER TABLE core_email ADD COLUMN active INT(2) NOT NULL DEFAULT 1;

-- need to store BCC data here.
ALTER TABLE core_email CHANGE COLUMN bcc_group bcc_group_id INT(11) NOT NULL DEFAULT 0;
ALTER TABLE core_email ADD COLUMN bcc_group_id INT(11) NOT NULL DEFAULT 0;

-- each email template should have  a test class with a static method ::test_{name}
ALTER TABLE core_email CHANGE COLUMN test_class test_class VARCHAR(254) NOT NULL DEFAULT '';
ALTER TABLE core_email ADD COLUMN test_class VARCHAR(254) NOT NULL DEFAULT '';

ALTER TABLE core_email CHANGE COLUMN  in_group to_group_id INT(11) NOT NULL DEFAULT -1;
ALTER TABLE core_email CHANGE COLUMN  to_group to_group_id INT(11) NOT NULL DEFAULT -1;
ALTER TABLE core_email ADD COLUMN    to_group_id INT(11) NOT NULL DEFAULT -1;

-- rather than use 'body... use the original file..'
ALTER TABLE core_email ADD COLUMN  use_file VARCHAR(254) NOT NULL DEFAULT '';

ALTER TABLE core_email ADD COLUMN description VARCHAR(254) NOT NULL DEFAULT '';

ALTER TABLE core_email ADD INDEX lookup_owner_id (owner_id);

ALTER TABLE core_email ADD COLUMN language varchar(5)  NOT NULL DEFAULT 'en';

ALTER TABLE core_email ADD COLUMN daily_email_limit INT NOT NULL DEFAULT 0; -- number of emails allowed to be sent to the a person a day

UPDATE core_email SET updated_dt = NOW() where updated_dt IS NULL;

