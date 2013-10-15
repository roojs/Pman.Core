CREATE TABLE   core_enum (
  id int(11)  NOT NULL AUTO_INCREMENT,
  PRIMARY KEY (id)
 
);
 
alter table  core_enum ADD COLUMN  etype varchar(32)  NOT NULL DEFAULT '';
alter table  core_enum ADD COLUMN  name varchar(255)  NOT NULL DEFAULT '';
alter table  core_enum ADD COLUMN  active int(2)  NOT NULL DEFAULT 1;
alter table  core_enum ADD COLUMN  seqid int(11)  NOT NULL DEFAULT 0;
alter table  core_enum ADD COLUMN  seqmax int(11)  NOT NULL DEFAULT 0;
alter table  core_enum ADD COLUMN  display_name varchar(255)  NOT NULL DEFAULT '';


ALTER TABLE core_enum ADD COLUMN is_system_enum INT(2) NOT NULL DEFAULT 0;
ALTER TABLE core_enum CHANGE COLUMN display_name display_name TEXT NOT NULL DEFAULT '';

alter table  core_enum ADD  INDEX lookup(seqid, active, name, etype);


UPDATE core_enum SET display_name = name WHERE display_name = '';

