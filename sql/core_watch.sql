
-- used to trigger emails about changes to items being watched.

CREATE TABLE core_watch (
  id int(11)  NOT NULL AUTO_INCREMENT,
  PRIMARY KEY (id)
) ;


alter table  core_watch ADD COLUMN    ontable varchar(128) NOT NULL DEFAULT '';
alter table  core_watch ADD COLUMN   onid int(11) NOT NULL DEFAULT 0;
alter table  core_watch ADD COLUMN   person_id int(11) NOT NULL DEFAULT 0;
alter table  core_watch ADD COLUMN   event varchar(128) NOT NULL DEFAULT '';
alter table  core_watch ADD COLUMN   medium varchar(64) NOT NULL DEFAULT '';
alter table  core_watch ADD COLUMN  active int(11) NOT NULL DEFAULT '1';
alter table core_watch ADD COLUMN  no_minutes int(11) NOT NULL DEFAULT 0;

ALTER TABLE core_watch ADD INDEX qlookup (ontable,onid,person_id,event,medium);

ALTER TABLE core_watch ADD INDEX lookup_person_id (person_id);

 