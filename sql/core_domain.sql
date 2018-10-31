
CREATE TABLE core_domain (
  id int(11) NOT NULL auto_increment,
  PRIMARY KEY  (id)
) ENGINE=innodb CHARSET=utf8mb4  CHARSET=utf8 ;


ALTER TABLE core_domain ADD COLUMN domain VARCHAR(255) NOT NULL DEFAULT '';

CREATE UNIQUE INDEX ui_domain ON core_domain (domain);