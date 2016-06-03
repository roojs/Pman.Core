
CREATE TABLE  core_locking (
  id int(11)  NOT NULL AUTO_INCREMENT,
  PRIMARY KEY (id)
);
ALTER TABLE  core_locking ADD COLUMN   on_table varchar(64)  NOT NULL DEFAULT '';
ALTER TABLE  core_locking ADD COLUMN    on_id int(11)  NOT NULL DEFAULT 0;
ALTER TABLE  core_locking ADD COLUMN  person_id int(11)  NOT NULL DEFAULT 0;
ALTER TABLE  core_locking ADD COLUMN  created datetime ;

alter table  core_locking ADD  INDEX lookup(on_table, on_id, person_id, created);
-- oops... - wrong name of pid.
alter table  core_locking change column `int` id int(11) auto_increment not null;