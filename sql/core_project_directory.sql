
-- we duplicate office_id and company_id here...
-- not sure if we should keep doing that in the new design...
-- we should improve our links code to handle this..


CREATE TABLE core_project_directory (
  id int(11) NOT NULL auto_increment,
  PRIMARY KEY  (id)
) ;

ALTER TABLE  core_project_directory ADD COLUMN   project_id int(11) NOT NULL DEFAULT 0;
ALTER TABLE  core_project_directory ADD COLUMN   person_id int(11) NOT NULL DEFAULT 0;
ALTER TABLE  core_project_directory ADD COLUMN   ispm int(11) NOT NULL DEFAULT 0;
ALTER TABLE  core_project_directory ADD COLUMN   role varchar(32) NOT NULL DEFAULT '';

ALTER TABLE  core_project_directory ADD COLUMN   company_id int(11) NOT NULL DEFAULT 0;
ALTER TABLE  core_project_directory ADD COLUMN   office_id int(11) NOT NULL DEFAULT 0;
ALTER TABLE core_project_directory ADD COLUMN crm_person_id INT(11) NOT NULL DEFAULT 0;


ALTER TABLE core_project_directory ADD INDEX plookup (project_id,person_id, ispm, role);

ALTER TABLE core_project_directory ADD INDEX lookup_company_id (company_id);
ALTER TABLE core_project_directory ADD INDEX lookup_person_id (person_id);
