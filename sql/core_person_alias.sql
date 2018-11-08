
CREATE TABLE core_person_alias (
  id int(11)  NOT NULL AUTO_INCREMENT,

  PRIMARY KEY (id)
) ;
ALTER TABLE core_person_alias ADD COLUMN   person_id varchar(128) DEFAULT NULL;
ALTER TABLE core_person_alias ADD COLUMN  alias varchar(254) NOT NULL DEFAULT '';
  
ALTER TABLE core_person_alias ADD INDEX alias (alias);

ALTER TABLE core_person_alias ADD INDEX lookup_person_id (person_id);