 
CREATE TABLE  core_event_audit_archive  (
    id int(11)  NOT NULL AUTO_INCREMENT,
    PRIMARY KEY (id)
);

ALTER TABLE core_event_audit_archive ADD COLUMN   event_id int(11)  NOT NULL DEFAULT 0;
ALTER TABLE core_event_audit_archive ADD COLUMN       name varchar(128)  NOT NULL DEFAULT '';
ALTER TABLE core_event_audit_archive ADD COLUMN       old_audit_id int(11)  NOT NULL DEFAULT 0;
ALTER TABLE core_event_audit_archive ADD COLUMN       newvalue BLOB  NOT NULL;
ALTER TABLE core_event_audit_archive ADD   INDEX lookup(event_id, name, old_audit_id);



 

