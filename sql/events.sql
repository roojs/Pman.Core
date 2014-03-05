
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
