
CREATE TABLE core_person (
  id int(11) NOT NULL auto_increment,
  PRIMARY KEY  (id)
) ;


ALTER TABLE core_person ADD COLUMN   name varchar(128)  NOT NULL  DEFAULT '';
ALTER TABLE core_person ADD COLUMN   honor varchar(32) NOT NULL DEFAULT '';
ALTER TABLE core_person ADD COLUMN   firstname varchar(128) NOT NULL DEFAULT '';
ALTER TABLE core_person ADD COLUMN   lastname varchar(128) NOT NULL DEFAULT '';
ALTER TABLE core_person ADD COLUMN   firstname_alt varchar(128) NOT NULL DEFAULT '';
ALTER TABLE core_person ADD COLUMN   lastname_alt varchar(128) NOT NULL DEFAULT '';

-- chose title is like a nickname 

ALTER TABLE core_person ADD COLUMN   chosen_title TEXT NOT NULL; 


ALTER TABLE core_person ADD COLUMN   role varchar(254)  NOT NULL DEFAULT '';
ALTER TABLE core_person ADD COLUMN   remarks text NOT NULL;
ALTER TABLE core_person ADD COLUMN   lang varchar(8) default 'en';
ALTER TABLE core_person ADD COLUMN   country varchar(8) default '';

-- do not set SQL mode here - it needs to be done in the mysql config (updatedatabase checks for this)
-- SET SQL_MODE='ALLOW_INVALID_DATES';
ALTER TABLE core_person ADD COLUMN   birth_date DATE NOT NULL DEFAULT '0000-00-00';

-- main contact details
ALTER TABLE core_person ADD COLUMN   email varchar(256)  NOT NULL DEFAULT '';
ALTER TABLE core_person ADD COLUMN   phone varchar(32)  NOT NULL  DEFAULT '';
ALTER TABLE core_person ADD COLUMN   phone_mobile varchar(32)  NOT NULL  DEFAULT '';
ALTER TABLE core_person ADD COLUMN   phone_direct varchar(32)  NOT NULL  DEFAULT '';
ALTER TABLE core_person ADD COLUMN   fax varchar(32)  NOT NULL DEFAULT '';
ALTER TABLE core_person ADD COLUMN   alt_email VARCHAR(256) NULL ;


-- links to other tables. ??? in postgress these might need to be allow null... but we need them as NOT NULL ?
-- otherwise empty values will not apply to database.. (mysql)

ALTER TABLE core_person ADD COLUMN   office_id int(11) NOT NULL  default '0';
ALTER TABLE core_person ADD COLUMN   company_id int(11) NOT NULL  default '0';
ALTER TABLE core_person CHANGE COLUMN   office_id office_id int(11) NOT NULL  default '0';
ALTER TABLE core_person CHANGE COLUMN   company_id company_id int(11) NOT NULL  default '0';



ALTER TABLE core_person ADD COLUMN   owner_id int(11) NOT NULL DEFAULT 0;
ALTER TABLE core_person ADD COLUMN   active int(11) NOT NULL  default 1;
ALTER TABLE core_person ADD COLUMN   project_id int(11) default 0;

ALTER TABLE core_person ADD COLUMN   passwd varchar(64) NOT NULL  DEFAULT '';
ALTER TABLE core_person ADD COLUMN   no_reset_sent int(11) default '0';



ALTER TABLE core_person ADD COLUMN   deleted_by INT(11) NOT NULL default 0 ;
ALTER TABLE core_person ADD COLUMN   deleted_dt DATETIME ;


-- social
ALTER TABLE core_person ADD COLUMN   name_facebook VARCHAR(128) NULL;
ALTER TABLE core_person ADD COLUMN   url_blog VARCHAR(256) NULL ;
ALTER TABLE core_person ADD COLUMN   url_twitter VARCHAR(256) NULL ;
ALTER TABLE core_person ADD COLUMN   linkedin_id VARCHAR(256) NULL ;
ALTER TABLE core_person ADD COLUMN   url_linkedin VARCHAR(256) NULL ;
ALTER TABLE core_person ADD COLUMN url_google_plus TEXT NOT NULL; 
ALTER TABLE core_person ADD COLUMN url_blog2 TEXT NOT NULL; 
ALTER TABLE core_person ADD COLUMN url_blog3 TEXT NOT NULL; 



-- these are specific to modules - should have been put in there really..
-- CRM ? is store the core_person interest countries...
ALTER TABLE core_person ADD COLUMN countries VARCHAR(128) NOT NULL DEFAULT ''; 
ALTER TABLE core_person ADD COLUMN  action_type VARCHAR(32) DEFAULT ''; 

-- this is store the core_person location

-- ?? WTF - this is specific to another project?
ALTER TABLE core_person ADD COLUMN point_score INT(11) NOT NULL DEFAULT 0; 
 
-- indexes



-- old mysql
alter table core_person change column active active int(11) NOT NULL DEFAULT 1 ;
alter table core_person change role role varchar(254) NOT NULL DEFAULT '';
alter table core_person change email email varchar(254) NOT NULL DEFAULT '';

ALTER TABLE core_person ADD COLUMN authorize_md5 varchar(254)  NOT NULL DEFAULT '';
ALTER TABLE core_person CHANGE COLUMN authorize_md5 authorize_md5 varchar(254)  NOT NULL DEFAULT '';

ALTER TABLE core_person ADD INDEX lookup_authorize_md5_active(authorize_md5, active);

ALTER TABLE core_person ADD INDEX lookup_a(email, active);
ALTER TABLE core_person ADD INDEX lookup_b(email, active, company_id);
ALTER TABLE core_person add index lookup_owner(owner_id);
 
 
--  finally - always innodb
  
ALTER TABLE core_person ADD COLUMN post_code VARCHAR(256) NOT NULL DEFAULT '';

ALTER TABLE core_person ADD COLUMN oath_key VARCHAR(254) NOT NULL DEFAULT ''; 

alter table core_person add index lookup_company_id( company_id);
