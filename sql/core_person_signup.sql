

-- used to store signups - before they have been verified.


CREATE TABLE Person (
  id int(11) NOT NULL auto_increment,
  PRIMARY KEY  (id)
) ;


ALTER TABLE Person ADD COLUMN   name varchar(128)  NOT NULL  DEFAULT '';
ALTER TABLE Person ADD COLUMN   honor varchar(32) NOT NULL DEFAULT '';
ALTER TABLE Person ADD COLUMN   firstname varchar(128) NOT NULL DEFAULT '';
ALTER TABLE Person ADD COLUMN   lastname varchar(128) NOT NULL DEFAULT '';
ALTER TABLE Person ADD COLUMN   firstname_alt varchar(128) NOT NULL DEFAULT '';
ALTER TABLE Person ADD COLUMN   lastname_alt varchar(128) NOT NULL DEFAULT '';

ALTER TABLE Person ADD COLUMN   email varchar(256)  NOT NULL DEFAULT '';
ALTER TABLE Person ADD COLUMN   verify_key varchar(256)  NOT NULL DEFAULT '';
