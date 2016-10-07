 

CREATE TABLE  core_group_right  (
    id int(11)  NOT NULL AUTO_INCREMENT,
    PRIMARY KEY (id)
);
ALTER TABLE core_group_right ADD COLUMN    rightname varchar(64)  NOT NULL DEFAULT '';
ALTER TABLE core_group_right ADD COLUMN     group_id int(11) NOT NULL DEFAULT 0;
ALTER TABLE core_group_right ADD COLUMN   accessmask varchar(10)  NOT NULL DEFAULT '';
 
