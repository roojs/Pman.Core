

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

ALTER TABLE core_notify ADD COLUMN  sent DATETIME;
ALTER TABLE core_notify ADD COLUMN  event_id BIGINT NOT NULL DEFAULT 0;
ALTER TABLE core_notify ADD COLUMN  watch_id INT(11) NOT NULL DEFAULT 0;
ALTER TABLE core_notify ADD COLUMN  trigger_person_id INT(11) NOT NULL DEFAULT 0;
ALTER TABLE core_notify ADD COLUMN  trigger_event_id BIGINT NOT NULL DEFAULT 0;

ALTER TABLE core_notify ADD COLUMN  to_email varchar(255)  NOT NULL  DEFAULT '';

alter table core_notify add column language varchar(5) not null default 'en';

-- old mysql..
-- ALTER TABLE core_notify CHANGE COLUMN bounced event_id BIGINT NOT NULL DEFAULT 0;

ALTER TABLE core_notify CHANGE COLUMN event_id event_id BIGINT NOT NULL DEFAULT 0;
ALTER TABLE core_notify CHANGE COLUMN trigger_event_id trigger_event_id BIGINT NOT NULL DEFAULT 0;

ALTER TABLE core_notify ADD COLUMN person_table VARCHAR(256) NOT NULL DEFAULT '';

-- ?? why added???  - probably need to document this..
ALTER TABLE core_notify ADD COLUMN domain_id INT(11)  NOT NULL  DEFAULT 0;
ALTER TABLE core_notify ADD COLUMN server_id INT(11) NOT NULL DEFAULT -1;

ALTER TABLE core_notify ADD COLUMN reject_match_id INT(11) NOT NULL DEFAULT 0;

ALTER TABLE core_notify ADD COLUMN mail_imap_actor_id INT(11) NOT NULL DEFAULT 0;

ALTER TABLE core_notify ADD   INDEX lookup(act_when, msgid);

-- ALTER TABLE core_notify ADD   INDEX lookup_a(onid, ontable, person_id, act_when, msgid, to_email); (too long?!)

ALTER TABLE core_notify DROP INDEX lookup_a;

-- exceed 1000 characters if larger than this..
alter table core_notify add   INDEX lookup_b (sent, person_id, msgid, ontable);
ALTER TABLE core_notify add   index lookup_d (person_id, msgid, ontable);
ALTER TABLE core_notify ADD   INDEX lookup_e (onid, ontable, person_id, act_when);
ALTER TABLE core_notify ADD   INDEX lookup_f (to_email);
alter table core_notify add index lookup_g(sent, act_start, act_when);
alter table core_notify add   INDEX lookup_h (sent, event_id, server_id, msgid, ontable);


ALTER TABLE core_notify ADD INDEX lookup_person_id (person_id);
ALTER TABLE core_notify ADD INDEX lookup_trigger_person_id (trigger_person_id);

ALTER TABLE core_notify ADD   INDEX lookup_reject (person_id, reject_match_id, event_id);
ALTER TABLE core_notify ADD INDEX ix_domain_id (domain_id);
