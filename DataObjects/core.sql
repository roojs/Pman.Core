
-- // core comapy types - use core enums (Company Type)
DROP TABLE core_company_type;

 
CREATE TABLE  core_event_audit  (
    id int(11)  NOT NULL AUTO_INCREMENT,
    PRIMARY KEY (id)
);

ALTER TABLE core_event_audit ADD COLUMN   event_id int(11)  NOT NULL DEFAULT 0;
ALTER TABLE core_event_audit ADD COLUMN       name varchar(128)  NOT NULL DEFAULT '';
ALTER TABLE core_event_audit ADD COLUMN       old_audit_id int(11)  NOT NULL DEFAULT 0;
ALTER TABLE core_event_audit ADD COLUMN       newvalue BLOB  NOT NULL DEFAULT '';
ALTER TABLE core_event_audit ADD   INDEX lookup(event_id, name, old_audit_id);

-- BC name..
RENAME TABLE Group_Members TO group_members;

CREATE TABLE  group_members  (
    id int(11)  NOT NULL AUTO_INCREMENT,
    PRIMARY KEY (id)
);
ALTER TABLE group_members ADD COLUMN  group_id int(11) default NULL;
ALTER TABLE group_members ADD COLUMN   user_id int(11) NOT NULL default 0;

-- BC name..
RENAME TABLE Group_Rights TO group_rights;


CREATE TABLE  group_rights  (
    id int(11)  NOT NULL AUTO_INCREMENT,
    PRIMARY KEY (id)
);
ALTER TABLE group_rights ADD COLUMN    rightname varchar(64)  NOT NULL DEFAULT '';
ALTER TABLE group_rights ADD COLUMN     group_id int(11) NOT NULL DEFAULT 0;
ALTER TABLE group_rights ADD COLUMN   accessmask varchar(10)  NOT NULL DEFAULT '';

#old mysql.
ALTER TABLE group_rights CHANGE COLUMN AccessMask accessmask varchar(10)  NOT NULL DEFAULT '';




CREATE TABLE Groups (
  id int(11) NOT NULL auto_increment,  
  PRIMARY KEY   (id)
);

ALTER TABLE Groups ADD COLUMN name varchar(64)  NOT NULL DEFAULT '';
ALTER TABLE Groups ADD COLUMN   type int(11)  NOT NULL DEFAULT 0;
ALTER TABLE Groups ADD COLUMN leader int(11)  NOT NULL default 0;
#old mysql..
update Groups set type=0 where type is null;

ALTER TABLE Groups CHANGE COLUMN type type int(11)  NOT NULL  default 0;





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


CREATE TABLE Projects (
  id int(11) NOT NULL auto_increment,
  PRIMARY KEY  (id)
  
) ;



ALTER TABLE  Projects  ADD COLUMN name varchar(254)  NOT NULL  DEFAULT '';
ALTER TABLE  Projects  ADD COLUMN   remarks text ;
ALTER TABLE  Projects  ADD COLUMN   owner_id int(11) default NULL;
ALTER TABLE  Projects  ADD COLUMN   code varchar(32)  NOT NULL  DEFAULT '';
ALTER TABLE  Projects  ADD COLUMN   active int(11) default '1';
ALTER TABLE  Projects  ADD COLUMN   type varchar(1)  NOT NULL default 'P';
ALTER TABLE  Projects  ADD COLUMN   client_id int(11) NOT NULL default '0';
ALTER TABLE  Projects  ADD COLUMN   team_id int(11) NOT NULL default '0';
ALTER TABLE  Projects  ADD COLUMN file_location varchar(254)    NOT NULL default '';
ALTER TABLE  Projects  ADD COLUMN open_date date default NULL;
ALTER TABLE  Projects  ADD COLUMN open_by int(11) NOT NULL default '0';
ALTER TABLE  Projects  ADD COLUMN updated_dt DATETIME NOT NULL;

-- these should be removed, as they are code specific..
ALTER TABLE Projects ADD COLUMN countries varchar(128)  NOT NULL DEFAULT '';
ALTER TABLE Projects  ADD COLUMN languages varchar(128)  NOT NULL DEFAULT '';
ALTER TABLE  Projects ADD COLUMN agency_id int(11)  NOT NULL DEFAULT 0 ;

ALTER TABLE Projects ADD INDEX plookup (code);
 

-- we duplicate office_id and company_id here...
-- not sure if we should keep doing that in the new design...
-- we should improve our links code to handle this..


CREATE TABLE ProjectDirectory (
  id int(11) NOT NULL auto_increment,
  PRIMARY KEY  (id)
) ;

ALTER TABLE  ProjectDirectory ADD COLUMN   project_id int(11) NOT NULL DEFAULT 0;
ALTER TABLE  ProjectDirectory ADD COLUMN   person_id int(11) NOT NULL DEFAULT 0;
ALTER TABLE  ProjectDirectory ADD COLUMN   ispm int(11) NOT NULL DEFAULT 0;
ALTER TABLE  ProjectDirectory ADD COLUMN   role varchar(16) NOT NULL DEFAULT '';

ALTER TABLE ProjectDirectory ADD INDEX plookup (project_id,person_id, ispm, role);


 
--// old core image type - merged into enum.
DROP TABLE core_image_type;


CREATE TABLE  i18n (
  id int(11)  NOT NULL AUTO_INCREMENT,
  PRIMARY KEY (id)
  
);

ALTER TABLE  i18n ADD COLUMN   ltype varchar(1)  NOT NULL DEFAULT '';
  ALTER TABLE  i18n ADD COLUMN   lkey varchar(8)  NOT NULL DEFAULT '';
  ALTER TABLE  i18n ADD COLUMN   inlang varchar(8)  NOT NULL DEFAULT '';
  ALTER TABLE  i18n ADD COLUMN   lval varchar(64)  NOT NULL DEFAULT '';
  
ALTER TABLE i18n ADD INDEX lookup (ltype, lkey, inlang);

			
        
    
CREATE TABLE  core_locking (
  id int(11)  NOT NULL AUTO_INCREMENT,
  PRIMARY KEY (id)
);
ALTER TABLE  core_locking ADD COLUMN   on_table varchar(64)  NOT NULL DEFAULT '';
ALTER TABLE  core_locking ADD COLUMN    on_id int(11)  NOT NULL DEFAULT 0;
ALTER TABLE  core_locking ADD COLUMN  person_id int(11)  NOT NULL DEFAULT 0;
ALTER TABLE  core_locking ADD COLUMN  created datetime ;

alter table  core_locking ADD  INDEX lookup(on_table, on_id, person_id, created);
-- oops... - wrong name of pid.
alter table  core_locking change column `int` id int(11) auto_increment not null;
 
-- ----------------------------

