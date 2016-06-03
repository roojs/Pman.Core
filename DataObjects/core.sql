
-- // core comapy types - use core enums (Company Type)
DROP TABLE core_company_type;

 
CREATE TABLE  core_event_audit  (
    id int(11)  NOT NULL AUTO_INCREMENT,
    PRIMARY KEY (id)
);

ALTER TABLE core_event_audit ADD COLUMN   event_id int(11)  NOT NULL DEFAULT 0;
ALTER TABLE core_event_audit ADD COLUMN       name varchar(128)  NOT NULL DEFAULT '';
ALTER TABLE core_event_audit ADD COLUMN       old_audit_id int(11)  NOT NULL DEFAULT 0;
ALTER TABLE core_event_audit ADD COLUMN       newvalue BLOB  NOT NULL DEFAULT '';
ALTER TABLE core_event_audit ADD   INDEX lookup(event_id, name, old_audit_id);

-- BC name..
RENAME TABLE Group_Members TO group_members;

CREATE TABLE  group_members  (
    id int(11)  NOT NULL AUTO_INCREMENT,
    PRIMARY KEY (id)
);
ALTER TABLE group_members ADD COLUMN  group_id int(11) default NULL;
ALTER TABLE group_members ADD COLUMN   user_id int(11) NOT NULL default 0;

 





-- we duplicate office_id and company_id here...
-- not sure if we should keep doing that in the new design...
-- we should improve our links code to handle this..


 
--// old core image type - merged into enum.
DROP TABLE core_image_type;



			
        
    
 
-- ----------------------------

