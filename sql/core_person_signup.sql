

-- used to store signups - before they have been verified.
-- once the verified link has been pressed, use a add event, copy to a person, and remove record..

CREATE TABLE core_person_signup (
  id int(11) NOT NULL auto_increment,
  PRIMARY KEY  (id)
) ;


ALTER TABLE core_person_signup ADD COLUMN   name varchar(128)  NOT NULL  DEFAULT '';
ALTER TABLE core_person_signup ADD COLUMN   honor varchar(32) NOT NULL DEFAULT '';
ALTER TABLE core_person_signup ADD COLUMN   firstname varchar(128) NOT NULL DEFAULT '';
ALTER TABLE core_person_signup ADD COLUMN   lastname varchar(128) NOT NULL DEFAULT '';
ALTER TABLE core_person_signup ADD COLUMN   firstname_alt varchar(128) NOT NULL DEFAULT '';
ALTER TABLE core_person_signup ADD COLUMN   lastname_alt varchar(128) NOT NULL DEFAULT '';

ALTER TABLE core_person_signup ADD COLUMN   email varchar(256)  NOT NULL DEFAULT '';
ALTER TABLE core_person_signup ADD COLUMN   verify_key varchar(256)  NOT NULL DEFAULT '';
ALTER TABLE core_person_signup ADD COLUMN   created_dt DATETIME  NOT NULL;

ALTER TABLE core_person_signup ADD COLUMN   company_name TEXT NOT NULL  default '';
ALTER TABLE core_person_signup ADD COLUMN   person_type TEXT NOT NULL  default '';
