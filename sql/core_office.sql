
CREATE TABLE core_office (
  id int(11) NOT NULL auto_increment,
 
  PRIMARY KEY  (id)
);


ALTER TABLE core_office ADD COLUMN  company_id int(11) NOT NULL default '0';
ALTER TABLE core_office ADD COLUMN    name varchar(64)  NOT NULL  DEFAULT '';
ALTER TABLE core_office ADD COLUMN    address text ;
ALTER TABLE core_office ADD COLUMN address2 TEXT;
ALTER TABLE core_office ADD COLUMN address3 TEXT;
ALTER TABLE core_office ADD COLUMN    phone varchar(32)  NOT NULL  DEFAULT '';
ALTER TABLE core_office ADD COLUMN    fax varchar(32)  NOT NULL  DEFAULT '';
ALTER TABLE core_office ADD COLUMN    email varchar(128)  NOT NULL  DEFAULT '';
ALTER TABLE core_office ADD COLUMN    role varchar(32)  NOT NULL  DEFAULT '';
ALTER TABLE core_office ADD COLUMN country VARCHAR(4) NULL;

ALTER TABLE core_office ADD COLUMN display_name VARCHAR(4) NULL;

ALTER TABLE core_office ADD INDEX lookup_company_id (company_id);