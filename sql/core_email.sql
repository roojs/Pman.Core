CREATE  TABLE core_email (
  id INT(11) NOT NULL AUTO_INCREMENT ,
  subject TEXT NULL ,
  bodytext TEXT NULL ,
  plaintext TEXT NULL ,
  name VARCHAR(255) NOT NULL DEFAULT '',
  updated_dt DATETIME NOT NULL ,
  from_email VARCHAR(254) NULL DEFAULT '',
  from_name VARCHAR(254) NULL DEFAULT '',
  owner_id INT(11) NOT NULL DEFAULT 0,
  is_system INT(2) NOT NULL DEFAULT 1,
  PRIMARY KEY (id)
);


call mysql_change_engine('core_email');


UPDATE core_email SET updated_dt = NOW();