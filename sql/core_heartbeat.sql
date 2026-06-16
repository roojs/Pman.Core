CREATE TABLE   core_heartbeat (
  id int(11)  NOT NULL AUTO_INCREMENT,
  PRIMARY KEY (id)

);

alter table  core_heartbeat ADD COLUMN  hostname varchar(255)  NOT NULL DEFAULT '';
alter table  core_heartbeat ADD COLUMN  last_update_dt datetime  NOT NULL DEFAULT '0000-00-00 00:00:00';

alter table  core_heartbeat ADD  INDEX lookup_hostname(hostname);
