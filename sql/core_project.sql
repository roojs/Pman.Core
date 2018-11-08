
CREATE TABLE core_project (
  id int(11) NOT NULL auto_increment,
  PRIMARY KEY  (id)
  
) ;



ALTER TABLE  core_project  ADD COLUMN name varchar(254)  NOT NULL  DEFAULT '';
ALTER TABLE  core_project  ADD COLUMN   remarks text ;
ALTER TABLE  core_project  ADD COLUMN   owner_id int(11) default NULL;
ALTER TABLE  core_project  ADD COLUMN   code varchar(32)  NOT NULL  DEFAULT '';
ALTER TABLE  core_project  ADD COLUMN   active int(11) default '1';
ALTER TABLE  core_project  ADD COLUMN   type varchar(1)  NOT NULL default 'P';
ALTER TABLE  core_project  ADD COLUMN   client_id int(11) NOT NULL default '0';
ALTER TABLE  core_project  ADD COLUMN   team_id int(11) NOT NULL default '0';
ALTER TABLE  core_project  ADD COLUMN file_location varchar(254)    NOT NULL default '';
ALTER TABLE  core_project  ADD COLUMN open_date date default NULL;
ALTER TABLE  core_project  ADD COLUMN open_by int(11) NOT NULL default '0';
ALTER TABLE  core_project  ADD COLUMN updated_dt DATETIME NOT NULL;

-- these should be removed, as they are code specific..
ALTER TABLE core_project ADD COLUMN countries varchar(128)  NOT NULL DEFAULT '';
ALTER TABLE core_project  ADD COLUMN languages varchar(128)  NOT NULL DEFAULT '';
ALTER TABLE  core_project ADD COLUMN agency_id int(11)  NOT NULL DEFAULT 0 ;

ALTER TABLE core_project ADD INDEX plookup (code);

ALTER TABLE core_project ADD INDEX lookup_client_id (client_id);
ALTER TABLE core_project ADD INDEX lookup_agency_id (agency_id);
 