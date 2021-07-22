


-- IF you are working on an old system... - copy the changes to Groups.sql...
 
CREATE TABLE core_group (
  id int(11) NOT NULL auto_increment,  
  PRIMARY KEY   (id)
);

ALTER TABLE core_group ADD COLUMN name varchar(128)  NOT NULL DEFAULT '';
ALTER TABLE core_group ADD COLUMN display_name varchar(128)  NOT NULL DEFAULT '';
ALTER TABLE core_group ADD COLUMN type  int(11)  NOT NULL DEFAULT 0;
ALTER TABLE core_group ADD COLUMN leader int(11)  NOT NULL default 0;
ALTER TABLE core_group ADD COLUMN is_system int(2) NOT NULL default 0;

UPDATE core_group SET  display_name = name WHERE display_name = '';
UPDATE core_group SET  is_system = 1 WHERE name = 'Administrators';

ALTER TABLE core_group ADD INDEX lookup_leader (leader);