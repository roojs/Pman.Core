
CREATE TABLE Person (
  id int(11) NOT NULL auto_increment,
  PRIMARY KEY  (id)
) ;


ALTER TABLE hydra_person ADD COLUMN   name varchar(128)  NOT NULL  DEFAULT '';
ALTER TABLE hydra_person ADD COLUMN   firstname varchar(128) NOT NULL DEFAULT '';
ALTER TABLE hydra_person ADD COLUMN   lastname varchar(128) NOT NULL DEFAULT '';
ALTER TABLE hydra_person ADD COLUMN   honor varchar(32) NOT NULL DEFAULT '';
ALTER TABLE hydra_person ADD COLUMN   chosen_title TEXT NOT NULL DEFAULT ''; 


ALTER TABLE hydra_person ADD COLUMN   role varchar(254)  NOT NULL DEFAULT '';
ALTER TABLE hydra_person ADD COLUMN   remarks text;
ALTER TABLE hydra_person ADD COLUMN   lang varchar(8) default 'en';

ALTER TABLE hydra_person ADD COLUMN   birth_date DATE NOT NULL DEFAULT '0000-00-00';

-- main contact details
ALTER TABLE hydra_person ADD COLUMN   email varchar(256)  NOT NULL DEFAULT '';
ALTER TABLE hydra_person ADD COLUMN   phone varchar(32)  NOT NULL  DEFAULT '';
ALTER TABLE hydra_person ADD COLUMN   phone_mobile varchar(32)  NOT NULL  DEFAULT '';
ALTER TABLE hydra_person ADD COLUMN   phone_direct varchar(32)  NOT NULL  DEFAULT '';
ALTER TABLE hydra_person ADD COLUMN   fax varchar(32)  NOT NULL DEFAULT '';
ALTER TABLE hydra_person ADD COLUMN   alt_email VARCHAR(256) NULL ;


-- links to other tables.
ALTER TABLE hydra_person ADD COLUMN   office_id int(11) default '0';
ALTER TABLE hydra_person ADD COLUMN   company_id int(11) default '0';
ALTER TABLE hydra_person ADD COLUMN   owner_id int(11) NOT NULL DEFAULT 0;
ALTER TABLE hydra_person ADD COLUMN   active int(11) NOT NULL  default 1;
ALTER TABLE hydra_person ADD COLUMN   project_id int(11) default 0;

ALTER TABLE hydra_person ADD COLUMN   passwd varchar(64) NOT NULL  DEFAULT '';
ALTER TABLE hydra_person ADD COLUMN   no_reset_sent int(11) default '0';



ALTER TABLE hydra_person ADD COLUMN   deleted_by INT(11) NOT NULL default 0 ;
ALTER TABLE hydra_person ADD COLUMN   deleted_dt DATETIME ;


-- social
ALTER TABLE hydra_person ADD COLUMN   name_facebook VARCHAR(128) NULL;
ALTER TABLE hydra_person ADD COLUMN   url_blog VARCHAR(256) NULL ;
ALTER TABLE hydra_person ADD COLUMN   url_twitter VARCHAR(256) NULL ;
ALTER TABLE hydra_person ADD COLUMN   linkedin_id VARCHAR(256) NULL ;
ALTER TABLE hydra_person ADD COLUMN   url_linkedin VARCHAR(256) NULL ;
ALTER TABLE hydra_person ADD COLUMN url_google_plus TEXT NOT NULL DEFAULT ''; 
ALTER TABLE hydra_person ADD COLUMN url_blog2 TEXT NOT NULL DEFAULT ''; 
ALTER TABLE hydra_person ADD COLUMN url_blog3 TEXT NOT NULL DEFAULT ''; 



-- these are specific to modules - should have been put in there really..
-- CRM ? is store the person interest countries...
ALTER TABLE hydra_person ADD COLUMN countries VARCHAR(128) NOT NULL DEFAULT ''; 
ALTER TABLE hydra_person ADD COLUMN  action_type VARCHAR(32) DEFAULT ''; 

-- this is store the person location

-- ?? WTF - this is specific to another project?
ALTER TABLE hydra_person ADD COLUMN point_score INT(11) NOT NULL DEFAULT 0; 
 
-- indexes



-- old mysql
alter table Person change column active active int(11) NOT NULL DEFAULT 1 ;
alter table Person change role role varchar(254) NOT NULL DEFAULT '';
alter table Person change email email varchar(254) NOT NULL DEFAULT '';



ALTER TABLE Person ADD INDEX lookup_a(email, active);
ALTER TABLE Person ADD INDEX lookup_b(email, active, company_id);
ALTER TABLE Person add index lookup_owner(owner_id);
 
