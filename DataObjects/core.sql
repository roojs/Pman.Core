
CREATE TABLE Companies (
  id int(11)  NOT NULL auto_increment,
  PRIMARY KEY   (id)
);


ALTER TABLE Companies ADD COLUMN    code varchar(32)  NOT NULL DEFAULT '';;
ALTER TABLE Companies ADD COLUMN    name varchar(128)  default NULL ;
ALTER TABLE Companies ADD COLUMN    remarks text ;
ALTER TABLE Companies ADD COLUMN    owner_id int(11) NOT NULL DEFAULT 0 ;
ALTER TABLE Companies ADD COLUMN    address text ;
ALTER TABLE Companies ADD COLUMN    tel varchar(32)  default NULL;
ALTER TABLE Companies ADD COLUMN    fax varchar(32)  default NULL;
ALTER TABLE Companies ADD COLUMN    email varchar(128)  default NULL;
--ALTER TABLE Companies ADD COLUMN    isOwner int(11) default NULL;
ALTER TABLE Companies ADD COLUMN    logo_id INT(11)  NOT NULL DEFAULT 0;;
ALTER TABLE Companies ADD COLUMN    background_color varchar(8)  NOT NULL;
ALTER TABLE Companies ADD COLUMN    url varchar(254)  NOT NULL DEFAULT '';
ALTER TABLE Companies ADD COLUMN    main_office_id int(11)  NOT NULL DEFAULT 0;
ALTER TABLE Companies ADD COLUMN    created_by int(11)  NOT NULL DEFAULT 0;
ALTER TABLE Companies ADD COLUMN    created_dt datetime  NOT NULL;
ALTER TABLE Companies ADD COLUMN    updated_by int(11)  NOT NULL DEFAULT 0;
ALTER TABLE Companies ADD COLUMN    updated_dt datetime  NOT NULL;
ALTER TABLE Companies ADD COLUMN    passwd varchar(64) NOT NULL DEFAULT '';
ALTER TABLE Companies ADD COLUMN    dispatch_port varchar(255) NOT NULL DEFAULT '';
ALTER TABLE Companies ADD COLUMN    province varchar(255) NOT NULL DEFAULT '';
ALTER TABLE Companies ADD COLUMN    country varchar(4) NOT NULL DEFAULT '';


ALTER TABLE Companies ADD COLUMN    comptype varchar(32)  NOT NULL DEFAULT '';
-- not sure if this needs to change.. << there is code in core/update that fills this in??
ALTER TABLE Companies ADD COLUMN    comptype_id INT(11) DEFAULT 0;


ALTER TABLE Companies CHANGE COLUMN isOwner isOwner int(11);
ALTER TABLE Companies CHANGE COLUMN comptype comptype  VARCHAR(32) DEFAULT '';
-- postres
--ALTER TABLE Companies ALTER isOwner TYPE int(11);
ALTER TABLE Companies ALTER owner_id SET DEFAULT 0;
ALTER TABLE Companies ALTER url SET DEFAULT '';

ALTER TABLE Companies ADD COLUMN    address1 text ;
ALTER TABLE Companies ADD COLUMN    address2 text ;
ALTER TABLE Companies ADD COLUMN    address3 text ;



ALTER TABLE Companies ADD INDEX name_lookup (name);


-- our new code should have this fixed now..
-- UPDATE Companies set comptype='OWNER' where isOwner=1;

-- // core comapy types - use core enums (Company Type)
DROP TABLE core_company_type;

 
CREATE TABLE Events (
  id int(11) NOT NULL auto_increment,
  
  PRIMARY KEY  (id)
) ;



ALTER TABLE Events ADD COLUMN   person_name varchar(128)  default NULL;

ALTER TABLE Events ADD COLUMN   event_when DATETIME default NULL;
ALTER TABLE Events ADD COLUMN   action varchar(32)  default NULL;
ALTER TABLE Events ADD COLUMN   ipaddr varchar(16)  default NULL;
ALTER TABLE Events ADD COLUMN   on_id int(11) default NULL;
ALTER TABLE Events ADD COLUMN   on_table varchar(64)  default NULL;
ALTER TABLE Events ADD COLUMN   person_id int(11) default NULL;
ALTER TABLE Events ADD COLUMN   person_table varchar(64) default NULL;

ALTER TABLE Events ADD COLUMN   remarks text ;
 

--#very old code..
ALTER TABLE Events CHANGE COLUMN EventID id INT(11) AUTO_INCREMENT NOT NULL;

--# this are for pre-postgres support code..
ALTER TABLE Events CHANGE COLUMN User person_name VARCHAR(128);
ALTER TABLE Events RENAME COLUMN User TO person_name;
ALTER TABLE Events CHANGE COLUMN Date event_when DATETIME ;
ALTER TABLE Events CHANGE COLUMN Event action VARCHAR(32);
ALTER TABLE Events CHANGE COLUMN Host ipaddr VARCHAR(16);
ALTER TABLE Events CHANGE COLUMN ItemID on_id INT(11);
ALTER TABLE Events CHANGE COLUMN Container on_table VARCHAR(64);


ALTER TABLE Events ADD INDEX lookup (on_id, on_table, person_id, event_when);

ALTER TABLE Events ADD INDEX lookupf (on_id, action, on_table, person_id, event_when);

ALTER TABLE Events ADD INDEX lookuppt ( person_table);

--# speeding up lookups. 
ALTER TABLE Events ADD INDEX lookup_when( person_id, event_when );

ALTER TABLE Events add index lookup_event_when (event_when);
ALTER TABLE Events add index lookup_action (action);
ALTER TABLE Events add index lookup_on_table (on_table);

ALTER TABLE Events add index lookup_action_person (action, person_id);

 alter table Events add index lookup_actions ( person_id, person_table, action);

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
ALTER TABLE Groups ADD COLUMN   type int(11) default NOT NULL DEFAULT 0;
ALTER TABLE Groups ADD COLUMN leader int(11)  NOT NULL default 0;
#old mysql..
update groups set type=0 where type is null;

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

CREATE TABLE Person (
  id int(11) NOT NULL auto_increment,
  PRIMARY KEY  (id)
) ;

ALTER TABLE Person ADD COLUMN   office_id int(11) default '0';
ALTER TABLE Person ADD COLUMN   name varchar(128)  NOT NULL  DEFAULT '';
ALTER TABLE Person ADD COLUMN   phone varchar(32)  NOT NULL  DEFAULT '';
ALTER TABLE Person ADD COLUMN   fax varchar(32)  NOT NULL DEFAULT '';

ALTER TABLE Person ADD COLUMN   email varchar(256)  NOT NULL DEFAULT '';
ALTER TABLE Person ADD COLUMN   alt_email VARCHAR(256) NULL ;

ALTER TABLE Person ADD COLUMN   company_id int(11) default '0';
ALTER TABLE Person ADD COLUMN   role varchar(254)  NOT NULL DEFAULT '';
ALTER TABLE Person ADD COLUMN   active int(11) NOT NULL  default 1;
ALTER TABLE Person ADD COLUMN   remarks text;
ALTER TABLE Person ADD COLUMN   passwd varchar(64) NOT NULL  DEFAULT '';
ALTER TABLE Person ADD COLUMN   owner_id int(11) NOT NULL DEFAULT 0;
ALTER TABLE Person ADD COLUMN   lang varchar(8) default 'en';
ALTER TABLE Person ADD COLUMN   no_reset_sent int(11) default '0';

ALTER TABLE Person ADD COLUMN   action_type VARCHAR(32) DEFAULT '';
ALTER TABLE Person ADD COLUMN   project_id int(11) default 0;
ALTER TABLE Person ADD COLUMN   deleted_by INT(11) NOT NULL default 0 ;
ALTER TABLE Person ADD COLUMN   deleted_dt DATETIME ;

ALTER TABLE Person ADD COLUMN   firstname varchar(128) NOT NULL DEFAULT '';
ALTER TABLE Person ADD COLUMN   lastname varchar(128) NOT NULL DEFAULT '';

ALTER TABLE Person ADD COLUMN   name_facebook VARCHAR(128) NULL;
ALTER TABLE Person ADD COLUMN   url_blog VARCHAR(256) NULL ;
ALTER TABLE Person ADD COLUMN   url_twitter VARCHAR(256) NULL ;
ALTER TABLE Person ADD COLUMN   url_linkedin VARCHAR(256) NULL ;
ALTER TABLE Person ADD COLUMN   alt_email VARCHAR(256) NULL ;


ALTER TABLE Person ADD COLUMN   phone_mobile varchar(32)  NOT NULL  DEFAULT '';
ALTER TABLE Person ADD COLUMN   phone_direct varchar(32)  NOT NULL  DEFAULT '';

ALTER TABLE Person ADD COLUMN   honor varchar(32) NOT NULL DEFAULT '';

# old mysql
alter table Person change column active active int(11) NOT NULL DEFAULT 1 ;
alter table Person change role role varchar(254) NOT NULL DEFAULT '';
alter table Person change email email varchar(254) NOT NULL DEFAULT '';



ALTER TABLE Person ADD INDEX lookup_a(email, active);
ALTER TABLE Person ADD INDEX lookup_b(email, active, company_id);
ALTER TABLE Person add index lookup_owner(owner_id);



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

-- a generic enumeraction

CREATE TABLE   core_enum (
  id int(11)  NOT NULL AUTO_INCREMENT,
  PRIMARY KEY (id)
 
);
 
alter table  core_enum ADD COLUMN  etype varchar(32)  NOT NULL DEFAULT '';
alter table  core_enum ADD COLUMN  name varchar(255)  NOT NULL DEFAULT '';
alter table  core_enum ADD COLUMN  active int(2)  NOT NULL DEFAULT 1;
alter table  core_enum ADD COLUMN  seqid int(11)  NOT NULL DEFAULT 0;
alter table  core_enum ADD COLUMN  seqmax int(11)  NOT NULL DEFAULT 0;
alter table  core_enum ADD COLUMN  display_name varchar(255)  NOT NULL DEFAULT '';


ALTER TABLE core_enum ADD COLUMN is_system_enum INT(2) NOT NULL DEFAULT 0;
ALTER TABLE core_enum CHANGE COLUMN display_name display_name TEXT NOT NULL DEFAULT '';

alter table  core_enum ADD  INDEX lookup(seqid, active, name, etype);


UPDATE core_enum SET display_name = name WHERE display_name = '';


-- ----------------------------

CREATE TABLE  translations (
  id int(11)  NOT NULL AUTO_INCREMENT,
  PRIMARY KEY (id)
);

alter table  translations ADD COLUMN    module varchar(64)  NOT NULL DEFAULT '';
alter table  translations ADD COLUMN    tfile varchar(128) NOT NULL DEFAULT '';
alter table  translations ADD COLUMN    tlang varchar(8)  NOT NULL DEFAULT '';
alter table  translations ADD COLUMN    tkey varchar(32)  NOT NULL DEFAULT '';
alter table  translations ADD COLUMN    tval longtext ;


ALTER TABLE translations ADD INDEX qlookup (module, tfile, tlang, tkey);


# - used to trigger emails about changes to items being watched.

CREATE TABLE core_watch (
  id int(11)  NOT NULL AUTO_INCREMENT,
  PRIMARY KEY (id)
) ;


alter table  core_watch ADD COLUMN    ontable varchar(128) NOT NULL DEFAULT '';
alter table  core_watch ADD COLUMN   onid int(11) NOT NULL DEFAULT 0;
alter table  core_watch ADD COLUMN   person_id int(11) NOT NULL DEFAULT 0;
alter table  core_watch ADD COLUMN   event varchar(128) NOT NULL DEFAULT '';
alter table  core_watch ADD COLUMN   medium varchar(64) NOT NULL DEFAULT '';
alter table  core_watch ADD COLUMN  active int(11) NOT NULL DEFAULT '1';

ALTER TABLE core_watch ADD INDEX qlookup (ontable,onid,person_id,event,medium);




CREATE TABLE  core_notify  (
  id int(11)  NOT NULL AUTO_INCREMENT,
  PRIMARY KEY (id)
);
ALTER TABLE core_notify ADD COLUMN  evtype VARCHAR(128) NOT NULL default '';
ALTER TABLE core_notify ADD COLUMN  recur_id INT(11) NOT NULL default 0;
ALTER TABLE core_notify ADD COLUMN  act_when DATETIME ;
ALTER TABLE core_notify ADD COLUMN  act_start DATETIME ;
ALTER TABLE core_notify ADD COLUMN  onid int(11)  NOT NULL DEFAULT 0;
ALTER TABLE core_notify ADD COLUMN  ontable varchar(128)  NOT NULL DEFAULT '';
ALTER TABLE core_notify ADD COLUMN  person_id int(11)  NOT NULL DEFAULT 0;
ALTER TABLE core_notify ADD COLUMN  msgid varchar(128)  NOT NULL  DEFAULT '';

ALTER TABLE core_notify ADD COLUMN  sent DATETIME ;
ALTER TABLE core_notify ADD COLUMN  event_id int(11)  NOT NULL DEFAULT 0;
ALTER TABLE core_notify ADD COLUMN  watch_id INT(11) NOT NULL DEFAULT 0;
ALTER TABLE core_notify ADD COLUMN  trigger_person_id INT(11) NOT NULL DEFAULT 0;
ALTER TABLE core_notify ADD COLUMN  trigger_event_id INT(11) NOT NULL DEFAULT 0;

ALTER TABLE core_notify ADD COLUMN  to_email varchar(255)  NOT NULL  DEFAULT '';


#old mysql..
ALTER TABLE core_notify CHANGE COLUMN bounced event_id INT(11) NOT NULL DEFAULT 0;

ALTER TABLE core_notify ADD   INDEX lookup(act_when, msgid);
ALTER TABLE core_notify ADD   INDEX lookup_a(onid, ontable, person_id, act_when, msgid, to_email);  
alter table core_notify add   INDEX lookup_b (sent, person_id, msgid, ontable);
ALTER TABLE core_notify add   index lookup_d (person_id, msgid, ontable);



# - used by email / tracker to handle alises - we have to be carefull adding to this table...

CREATE TABLE core_person_alias (
  id int(11)  NOT NULL AUTO_INCREMENT,

  PRIMARY KEY (id)
) ;
ALTER TABLE core_person_alias ADD COLUMN   person_id varchar(128) DEFAULT NULL;
ALTER TABLE core_person_alias ADD COLUMN  alias varchar(254) NOT NULL DEFAULT '';
  
ALTER TABLE core_person_alias ADD INDEX alias (alias);



CREATE TABLE core_notify_recur (
  id int(11)  NOT NULL AUTO_INCREMENT,

  PRIMARY KEY (id)
);
 

ALTER TABLE  core_notify_recur  ADD COLUMN person_id int(11)  NOT NULL;
ALTER TABLE  core_notify_recur  ADD COLUMN dtstart datetime  NOT NULL;
ALTER TABLE  core_notify_recur  ADD COLUMN dtend datetime  NOT NULL;
ALTER TABLE  core_notify_recur  ADD COLUMN max_applied_dt datetime  NOT NULL;
ALTER TABLE  core_notify_recur  ADD COLUMN updated_dt datetime  NOT NULL;
ALTER TABLE  core_notify_recur  ADD COLUMN last_applied_dt datetime  NOT NULL;
ALTER TABLE  core_notify_recur  ADD COLUMN tz varchar(64)  NOT NULL;
 
ALTER TABLE  core_notify_recur  ADD COLUMN freq varchar(8) NOT NULL;
ALTER TABLE  core_notify_recur  ADD COLUMN freq_day text NOT NULL;
ALTER TABLE  core_notify_recur  ADD COLUMN freq_hour text  NOT NULL;

ALTER TABLE  core_notify_recur  ADD COLUMN onid int(11)  NOT NULL default 0;
ALTER TABLE  core_notify_recur  ADD COLUMN ontable varchar(128)  NOT NULL default '';
ALTER TABLE  core_notify_recur  ADD COLUMN last_event_id  int(11)  default 0;
ALTER TABLE  core_notify_recur  ADD COLUMN method varchar(128) default '';     

ALTER TABLE  core_notify_recur  ADD COLUMN method_id  int(11)  default 0;

ALTER TABLE  core_notify_recur  ADD INDEX lookup(person_id, dtstart, dtend, tz, max_applied_dt, updated_dt, last_applied_dt);

-- old design..
ALTER TABLE  core_notify_recur  CHANGE COLUMN tz  tz varchar(64)  NOT NULL;
 

