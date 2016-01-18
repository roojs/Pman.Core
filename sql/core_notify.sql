

CREATE TABLE  core_notify  (
  id int(11)  NOT NULL AUTO_INCREMENT,
  PRIMARY KEY (id)
);
ALTER TABLE core_notify ADD COLUMN  evtype VARCHAR(128) NOT NULL default '';
ALTER TABLE core_notify ADD COLUMN  recur_id INT(11) NOT NULL default 0;
ALTER TABLE core_notify ADD COLUMN  act_when DATETIME ;
ALTER TABLE core_notify ADD COLUMN  act_start DATETIME ;
ALTER TABLE core_notify ADD COLUMN  onid int(11)  NOT NULL DEFAULT 0;
ALTER TABLE core_notify ADD COLUMN  ontable varchar(128)  NOT NULL DEFAULT '';
ALTER TABLE core_notify ADD COLUMN  person_id int(11)  NOT NULL DEFAULT 0;
ALTER TABLE core_notify ADD COLUMN  msgid varchar(128)  NOT NULL  DEFAULT '';

ALTER TABLE core_notify ADD COLUMN  sent DATETIME ;
ALTER TABLE core_notify ADD COLUMN  event_id int(11)  NOT NULL DEFAULT 0;
ALTER TABLE core_notify ADD COLUMN  watch_id INT(11) NOT NULL DEFAULT 0;
ALTER TABLE core_notify ADD COLUMN  trigger_person_id INT(11) NOT NULL DEFAULT 0;
ALTER TABLE core_notify ADD COLUMN  trigger_event_id INT(11) NOT NULL DEFAULT 0;

ALTER TABLE core_notify ADD COLUMN  to_email varchar(255)  NOT NULL  DEFAULT '';


-- old mysql..
ALTER TABLE core_notify CHANGE COLUMN bounced event_id INT(11) NOT NULL DEFAULT 0;
ALTER TABLE core_notify ADD COLUMN person_table VARCHAR(256) NOT NULL DEFAULT '';


ALTER TABLE core_notify ADD   INDEX lookup(act_when, msgid);

-- ALTER TABLE core_notify ADD   INDEX lookup_a(onid, ontable, person_id, act_when, msgid, to_email); (too long?!)

ALTER TABLE core_notify DROP INDEX lookup_a;

alter table core_notify add   INDEX lookup_b (sent, person_id, msgid, ontable);
ALTER TABLE core_notify add   index lookup_d (person_id, msgid, ontable);
ALTER TABLE core_notify ADD   INDEX lookup_e (onid, ontable, person_id, act_when);
ALTER TABLE core_notify ADD   INDEX lookup_f (to_email);