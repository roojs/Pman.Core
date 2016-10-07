


-- used to be Groups .... we will need to flip all old code to use this...
 
CREATE TABLE core_groups (
  id int(11) NOT NULL auto_increment,  
  PRIMARY KEY   (id)
);

ALTER TABLE Groups ADD COLUMN name varchar(128)  NOT NULL DEFAULT '';
ALTER TABLE Groups ADD COLUMN type  int(11)  NOT NULL DEFAULT 0;
ALTER TABLE Groups ADD COLUMN leader int(11)  NOT NULL default 0;
ALTER TABLE Groups ADD COLUMN is_system int(2) NOT NULL default 0;

