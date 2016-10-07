
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
 