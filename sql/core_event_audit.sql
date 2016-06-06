 
CREATE TABLE  core_event_audit  (
    id int(11)  NOT NULL AUTO_INCREMENT,
    PRIMARY KEY (id)
);

ALTER TABLE core_event_audit ADD COLUMN   event_id int(11)  NOT NULL DEFAULT 0;
ALTER TABLE core_event_audit ADD COLUMN       name varchar(128)  NOT NULL DEFAULT '';
ALTER TABLE core_event_audit ADD COLUMN       old_audit_id int(11)  NOT NULL DEFAULT 0;
ALTER TABLE core_event_audit ADD COLUMN       newvalue BLOB  NOT NULL DEFAULT '';
ALTER TABLE core_event_audit ADD   INDEX lookup(event_id, name, old_audit_id);





