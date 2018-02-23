CREATE TABLE core_events_archive (
  id int(11) NOT NULL AUTO_INCREMENT,
  person_name varchar(128) NOT NULL DEFAULT '',
  event_when datetime NOT NULL ,
  action varchar(64) NOT NULL DEFAULT '',
  ipaddr varchar(16) NOT NULL DEFAULT '',
  on_id int(11) NOT NULL DEFAULT 0,
  on_table varchar(64) NOT NULL DEFAULT '',
  person_id int(11) NOT NULL DEFAULT 0,
  remarks text NOT NULL,
  person_table varchar(64) NOT NULL DEFAULT '',
  dupe_id int(11) NOT NULL DEFAULT '0',
  PRIMARY KEY (id)
) ;

ALTER TABLE core_events_archive ADD INDEX lookup (on_id,on_table,person_id,event_when);

ALTER TABLE core_events_archive ADD INDEX lookuppt (person_table);

ALTER TABLE core_events_archive ADD INDEX lookup_when (person_id,event_when);

ALTER TABLE core_events_archive ADD INDEX lookup_action (action);

ALTER TABLE core_events_archive ADD INDEX lookup_on_table (on_table);

ALTER TABLE core_events_archive ADD INDEX lookup_action_person (action,person_id);

ALTER TABLE core_events_archive ADD INDEX lookup_affects (on_table,person_table);

ALTER TABLE core_events_archive ADD INDEX lookup_actions (person_id,person_table,action);

ALTER TABLE core_events_archive ADD INDEX lookupf (on_id,action,on_table,person_id,event_when);



