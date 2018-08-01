-- BC name..

CREATE TABLE  core_group_member  (
    id int(11)  NOT NULL AUTO_INCREMENT,
    PRIMARY KEY (id)
);

ALTER TABLE core_group_member CHANGE COLUMN  group_id  group_id int(11) NOT NULL default 0;
ALTER TABLE core_group_member ADD COLUMN  group_id int(11) NOT NULL default 0;

ALTER TABLE core_group_member ADD COLUMN   user_id int(11) NOT NULL default 0;

ALTER TABLE core_group_member ADD UNIQUE KEY  unique_group_user (group_id,user_id);
