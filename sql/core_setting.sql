CREATE TABLE  core_setting  (
   id int(11)  NOT NULL AUTO_INCREMENT,
   PRIMARY KEY (id)
);

ALTER TABLE core_setting ADD COLUMN module VARCHAR(64) NOT NULL DEFAULT '';
ALTER TABLE core_setting ADD COLUMN name VARCHAR(64) NOT NULL DEFAULT '';
ALTER TABLE core_setting ADD COLUMN description VARCHAR(128) NOT NULL DEFAULT '';
ALTER TABLE core_setting CHANGE COLUMN  val val  BLOB DEFAULT '';
ALTER TABLE core_setting ADD COLUMN val  BLOB NOT NULL DEFAULT '';
ALTER TABLE core_setting ADD COLUMN updated_dt DATE NOT NULL DEFAULT '1000-001-01';
ALTER TABLE core_setting CHANGE COLUMN updated_dt updated_dt DATE NOT NULL DEFAULT '1000-01-01';

ALTER TABLE core_setting ADD COLUMN is_encrypt INT(2) NOT NULL DEFAULT 1;
ALTER TABLE core_setting ADD COLUMN is_valid INT(2) NOT NULL DEFAULT 0;

ALTER TABLE core_setting ADD INDEX lookup_module_name(module, name);