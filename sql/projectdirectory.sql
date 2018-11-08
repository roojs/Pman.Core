
-- we duplicate office_id and company_id here...
-- not sure if we should keep doing that in the new design...
-- we should improve our links code to handle this..


CREATE TABLE ProjectDirectory (
  id int(11) NOT NULL auto_increment,
  PRIMARY KEY  (id)
) ;

ALTER TABLE  ProjectDirectory ADD COLUMN   project_id int(11) NOT NULL DEFAULT 0;
ALTER TABLE  ProjectDirectory ADD COLUMN   person_id int(11) NOT NULL DEFAULT 0;
ALTER TABLE  ProjectDirectory ADD COLUMN   ispm int(11) NOT NULL DEFAULT 0;
ALTER TABLE  ProjectDirectory ADD COLUMN   role varchar(32) NOT NULL DEFAULT '';

ALTER TABLE  ProjectDirectory ADD COLUMN   company_id int(11) NOT NULL DEFAULT 0;
ALTER TABLE  ProjectDirectory ADD COLUMN   office_id int(11) NOT NULL DEFAULT 0;


ALTER TABLE ProjectDirectory ADD INDEX plookup (project_id,person_id, ispm, role);

ALTER TABLE ProjectDirectory ADD INDEX lookup_company_id (company_id);
ALTER TABLE ProjectDirectory ADD INDEX lookup_person_id (person_id);
 
 
  