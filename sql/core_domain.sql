
CREATE TABLE core_domain (
  id int(11) NOT NULL auto_increment,
  PRIMARY KEY  (id)
);


ALTER TABLE core_domain ADD COLUMN domain VARCHAR(256) NOT NULL DEFAULT '';