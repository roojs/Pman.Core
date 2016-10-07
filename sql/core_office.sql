
CREATE TABLE Office (
  id int(11) NOT NULL auto_increment,
 
  PRIMARY KEY  (id)
);


ALTER TABLE Office ADD COLUMN  company_id int(11) NOT NULL default '0';
ALTER TABLE Office ADD COLUMN    name varchar(64)  NOT NULL  DEFAULT '';
ALTER TABLE Office ADD COLUMN    address text ;
ALTER TABLE Office ADD COLUMN address2 TEXT;
ALTER TABLE Office ADD COLUMN address3 TEXT;
ALTER TABLE Office ADD COLUMN    phone varchar(32)  NOT NULL  DEFAULT '';
ALTER TABLE Office ADD COLUMN    fax varchar(32)  NOT NULL  DEFAULT '';
ALTER TABLE Office ADD COLUMN    email varchar(128)  NOT NULL  DEFAULT '';
ALTER TABLE Office ADD COLUMN    role varchar(32)  NOT NULL  DEFAULT '';
ALTER TABLE Office ADD COLUMN country VARCHAR(4) NULL;

ALTER TABLE Office ADD COLUMN display_name VARCHAR(4) NULL;

