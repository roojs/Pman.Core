-- BC name..
 
CREATE TABLE  core_group_member  (
    id int(11)  NOT NULL AUTO_INCREMENT,
    PRIMARY KEY (id)
);
ALTER TABLE core_group_member ADD COLUMN  group_id int(11) default NULL;
ALTER TABLE core_group_member ADD COLUMN   user_id int(11) NOT NULL default 0;

 