RENAME TABLE Group_Rights TO group_rights;


CREATE TABLE  group_rights  (
    id int(11)  NOT NULL AUTO_INCREMENT,
    PRIMARY KEY (id)
);
ALTER TABLE group_rights ADD COLUMN    rightname varchar(64)  NOT NULL DEFAULT '';
ALTER TABLE group_rights ADD COLUMN     group_id int(11) NOT NULL DEFAULT 0;
ALTER TABLE group_rights ADD COLUMN   accessmask varchar(10)  NOT NULL DEFAULT '';

#old mysql.
ALTER TABLE group_rights CHANGE COLUMN AccessMask accessmask varchar(10)  NOT NULL DEFAULT '';

