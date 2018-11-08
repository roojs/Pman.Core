
CREATE TABLE core_notify_recur (
  id int(11)  NOT NULL AUTO_INCREMENT,

  PRIMARY KEY (id)
);
 

ALTER TABLE  core_notify_recur  ADD COLUMN person_id int(11)  NOT NULL;
ALTER TABLE  core_notify_recur  ADD COLUMN dtstart datetime  NOT NULL;
ALTER TABLE  core_notify_recur  ADD COLUMN dtend datetime  NOT NULL;
ALTER TABLE  core_notify_recur  ADD COLUMN max_applied_dt datetime  NOT NULL;
ALTER TABLE  core_notify_recur  ADD COLUMN updated_dt datetime  NOT NULL;
ALTER TABLE  core_notify_recur  ADD COLUMN last_applied_dt datetime  NOT NULL;
ALTER TABLE  core_notify_recur  ADD COLUMN tz varchar(64)  NOT NULL;
 
ALTER TABLE  core_notify_recur  ADD COLUMN freq varchar(8) NOT NULL;
ALTER TABLE  core_notify_recur  ADD COLUMN freq_day text NOT NULL;
ALTER TABLE  core_notify_recur  ADD COLUMN freq_hour text  NOT NULL;

ALTER TABLE  core_notify_recur  ADD COLUMN onid int(11)  NOT NULL default 0;
ALTER TABLE  core_notify_recur  ADD COLUMN ontable varchar(128)  NOT NULL default '';
ALTER TABLE  core_notify_recur  ADD COLUMN last_event_id  int(11)  default 0;
ALTER TABLE  core_notify_recur  ADD COLUMN method varchar(128) default '';     

ALTER TABLE  core_notify_recur  ADD COLUMN method_id  int(11)  default 0;

ALTER TABLE  core_notify_recur  ADD INDEX lookup(person_id, dtstart, dtend, tz, max_applied_dt, updated_dt, last_applied_dt);

-- old design..
ALTER TABLE  core_notify_recur  CHANGE COLUMN tz  tz varchar(64)  NOT NULL;

ALTER TABLE core_notify_recur ADD INDEX lookup_person_id (person_id);

