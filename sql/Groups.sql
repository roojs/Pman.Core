








--  THIS IS DEPRICATED --- USE core_groups now....


--  some projects will still have to use 'Groups'... so 










CREATE TABLE Groups (
  id int(11) NOT NULL auto_increment,  
  PRIMARY KEY   (id)
);

ALTER TABLE Groups ADD COLUMN name varchar(64)  NOT NULL DEFAULT '';
ALTER TABLE Groups ADD COLUMN   type int(11)  NOT NULL DEFAULT 0;
ALTER TABLE Groups ADD COLUMN leader int(11)  NOT NULL default 0;
#old mysql..
update Groups set type=0 where type is null;

ALTER TABLE Groups CHANGE COLUMN type type int(11)  NOT NULL  default 0;



