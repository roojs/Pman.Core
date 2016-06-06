-- BC name..
RENAME TABLE Group_Members TO group_members;

CREATE TABLE  group_members  (
    id int(11)  NOT NULL AUTO_INCREMENT,
    PRIMARY KEY (id)
);
ALTER TABLE group_members ADD COLUMN  group_id int(11) default NULL;
ALTER TABLE group_members ADD COLUMN   user_id int(11) NOT NULL default 0;

 