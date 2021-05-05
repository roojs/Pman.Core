
CREATE TABLE core_project_group (
  id int(11) NOT NULL auto_increment,
  PRIMARY KEY  (id)
  
);

ALTER TABLE  core_project_group  ADD COLUMN   group_id int(11) default NULL default 0;
ALTER TABLE  core_project_group  ADD COLUMN   project_id int(11) default NULL default 0;
