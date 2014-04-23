
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

# this is store the person interest countries...
ALTER TABLE Person ADD COLUMN countries VARCHAR(128) NOT NULL DEFAULT ''; 

# this is store the person location
ALTER TABLE Person ADD COLUMN country TEXT NOT NULL DEFAULT '';
ALTER TABLE Person ADD COLUMN city TEXT NOT NULL DEFAULT ''; 
ALTER TABLE Person ADD COLUMN addr_state TEXT NOT NULL DEFAULT ''; 


# old mysql
alter table Person change column active active int(11) NOT NULL DEFAULT 1 ;
alter table Person change role role varchar(254) NOT NULL DEFAULT '';
alter table Person change email email varchar(254) NOT NULL DEFAULT '';



ALTER TABLE Person ADD INDEX lookup_a(email, active);
ALTER TABLE Person ADD INDEX lookup_b(email, active, company_id);
ALTER TABLE Person add index lookup_owner(owner_id);

ALTER TABLE Person ADD COLUMN chosen_title TEXT NOT NULL DEFAULT ''; 
ALTER TABLE Person ADD COLUMN url_google_plus TEXT NOT NULL DEFAULT ''; 
ALTER TABLE Person ADD COLUMN url_blog2 TEXT NOT NULL DEFAULT ''; 
ALTER TABLE Person ADD COLUMN url_blog3 TEXT NOT NULL DEFAULT ''; 
