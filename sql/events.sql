
CREATE TABLE Events (
  id bigint NOT NULL auto_increment,
  
  PRIMARY KEY  (id)
) ;



ALTER TABLE Events ADD COLUMN   person_name varchar(128)  NOT NULL default '';

ALTER TABLE Events ADD COLUMN   event_when DATETIME default NULL;
ALTER TABLE Events ADD COLUMN   action varchar(64)  NOT NULL default '' ;
ALTER TABLE Events ADD COLUMN   ipaddr varchar(16)  NOT NULL default '';
ALTER TABLE Events ADD COLUMN   on_id int(11) NOT NULL default 0;
ALTER TABLE Events ADD COLUMN   on_table varchar(64) NOT NULL default '';
ALTER TABLE Events ADD COLUMN   person_id int(11) NOT NULL default 0;
ALTER TABLE Events ADD COLUMN   person_table varchar(64) NOT NULL default '';
ALTER TABLE Events ADD COLUMN   dupe_id INT(11) NOT NULL DEFAULT 0;

ALTER TABLE Events ADD COLUMN   remarks text ;
 

-- #very old code..
ALTER TABLE Events CHANGE COLUMN EventID id INT(11) AUTO_INCREMENT NOT NULL;

-- # this are for pre-postgres support code..
ALTER TABLE Events CHANGE COLUMN User person_name VARCHAR(128);
-- ALTER TABLE Events RENAME COLUMN User TO person_name;
ALTER TABLE Events CHANGE COLUMN Date event_when DATETIME ;
ALTER TABLE Events CHANGE COLUMN Event action VARCHAR(32);
ALTER TABLE Events CHANGE COLUMN Host ipaddr VARCHAR(16);
ALTER TABLE Events CHANGE COLUMN ItemID on_id INT(11);
ALTER TABLE Events CHANGE COLUMN Container on_table VARCHAR(64);

-- make action larger..
ALTER TABLE Events CHANGE COLUMN action action varchar(64)  default NULL;

-- id needs more space..
ALTER TABLE Events change COLUMN id id bigint  NOT NULL AUTO_INCREMENT;



ALTER TABLE Events ADD INDEX lookupf (on_id, action, on_table, person_id, event_when, person_table);

ALTER TABLE Events ADD INDEX lookup_person_id (person_id);

-- #Keep for later use..

-- ALTER TABLE Events CHANGE COLUMN person_name person_name VARCHAR(128) NOT NULL ;

-- ALTER TABLE Events CHANGE COLUMN event_when event_when DATETIME NOT NULL ;

-- ALTER TABLE Events CHANGE COLUMN action action VARCHAR(64) NOT NULL DEFAULT '';

-- ALTER TABLE Events CHANGE COLUMN ipaddr ipaddr VARCHAR(16) NOT NULL DEFAULT '';

-- ALTER TABLE Events CHANGE COLUMN on_id on_id INT(11) NOT NULL DEFAULT 0 ;

-- ALTER TABLE Events CHANGE COLUMN on_table on_table VARCHAR(64) NOT NULL DEFAULT '';

-- ALTER TABLE Events CHANGE COLUMN person_id person_id INT(11) NOT NULL DEFAULT 0 ;

-- ALTER TABLE Events CHANGE COLUMN remarks remarks TEXT NOT NULL ;

-- ALTER TABLE Events CHANGE COLUMN person_table person_table VARCHAR(64) NOT NULL DEFAULT '';
  
 
