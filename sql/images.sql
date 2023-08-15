
CREATE TABLE   Images (
  id int(11) NOT NULL auto_increment,
  PRIMARY KEY  (id)
);

ALTER TABLE Images    ADD COLUMN   filename varchar(255) NOT NULL default '';
ALTER TABLE Images    ADD COLUMN   ontable varchar(32) NOT NULL default '';
ALTER TABLE Images    ADD COLUMN   onid int(11) NOT NULL default '0';
ALTER TABLE Images    ADD COLUMN   mimetype varchar(128) NOT NULL default '';
ALTER TABLE Images    ADD COLUMN   width int(11) NOT NULL default '0';
ALTER TABLE Images    ADD COLUMN   height int(11) NOT NULL default '0';
ALTER TABLE Images    ADD COLUMN   filesize int(11) NOT NULL default '0';
ALTER TABLE Images    ADD COLUMN   displayorder int(11) NOT NULL default '0';
ALTER TABLE Images    ADD COLUMN   language varchar(6) NOT NULL default 'en';
ALTER TABLE Images    ADD COLUMN   parent_image_id int(11) NOT NULL default '0';

ALTER TABLE  Images ADD COLUMN created datetime ;
ALTER TABLE  Images ADD COLUMN imgtype VARCHAR(32) DEFAULT '' NOT NULL;
ALTER TABLE  Images ADD COLUMN linkurl VARCHAR(254) DEFAULT '' NOT NULL;
ALTER TABLE  Images ADD COLUMN descript TEXT DEFAULT '' NOT NULL;
ALTER TABLE  Images ADD COLUMN title VARCHAR(128) DEFAULT '' NOT NULL;

ALTER TABLE Images    CHANGE COLUMN   mimetype mimetype  varchar(128) NOT NULL default '';
ALTER TABLE Images    CHANGE COLUMN   descript descript  TEXT NOT NULL default '';

-- postgres (need better way to support this..)
-- ALTER TABLE Images    ALTER COLUMN   mimetype  TYPE  varchar(128) ;

ALTER TABLE Images ADD INDEX lookup(ontable, onid);

ALTER TABLE Images ADD INDEX lookupc(created, ontable, onid);

ALTER TABLE Images ADD COLUMN no_of_pages INT(11) NOT NULL DEFAULT 0;

ALTER TABLE Images ADD COLUMN is_deleted INT(2) NOT NULL DEFAULT 0;

 