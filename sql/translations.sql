

-- depricated use core_template templatestr etc..

CREATE TABLE  translations (
  id int(11)  NOT NULL AUTO_INCREMENT,
  PRIMARY KEY (id)
);

alter table  translations ADD COLUMN    module varchar(64)  NOT NULL DEFAULT '';
alter table  translations ADD COLUMN    tfile varchar(128) NOT NULL DEFAULT '';
alter table  translations ADD COLUMN    tlang varchar(8)  NOT NULL DEFAULT '';
alter table  translations ADD COLUMN    tkey varchar(32)  NOT NULL DEFAULT '';
alter table  translations ADD COLUMN    tval longtext ;


ALTER TABLE translations ADD INDEX qlookup (module, tfile, tlang, tkey);



