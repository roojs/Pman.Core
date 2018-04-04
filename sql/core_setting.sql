CREATE TABLE  core_setting  (
   id int(11)  NOT NULL AUTO_INCREMENT,
   PRIMARY KEY (id)
);

ALTER TABLE core_setting ADD COLUMN module VARCHAR(64) NOT NULL DEFAULT '';

ALTER TABLE core_setting ADD COLUMN name VARCHAR(64) NOT NULL DEFAULT '';

ALTER TABLE core_setting ADD COLUMN description VARCHAR(128) NOT NULL DEFAULT '';

ALTER TABLE core_setting CHANGE COLUMN  val val  TEXT DEFAULT '';
ALTER TABLE core_setting ADD COLUMN         val        TEXT NOT NULL DEFAULT '';

ALTER TABLE core_setting ADD COLUMN updated_dt DATE NOT NULL DEFAULT '0000-00-00';

ALTER TABLE core_setting ADD COLUMN is_encrypt INT(2) NOT NULL DEFAULT 1;


ALTER TABLE core_setting ADD INDEX lookup_module_name(module, name);