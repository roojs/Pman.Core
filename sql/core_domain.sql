
CREATE TABLE core_domain (
  id int(11) NOT NULL auto_increment,
  PRIMARY KEY  (id)
) ENGINE=innodb DEFAULT  CHARSET=utf8 ;
-- need to specify engine set, otherwise the unique index get's borked.

ALTER TABLE core_domain ADD COLUMN domain VARCHAR(255) NOT NULL DEFAULT '';
ALTER TABLE core_domain ADD COLUMN has_mx int(2) NOT NULL  DEFAULT 0;
ALTER TABLE core_domain ADD COLUMN mx_updated DATETIME NOT NULL DEFAULT '1000-01-01 00:00:00';
ALTER TABLE core_domain ADD COLUMN no_mx_dt DATETIME NOT NULL DEFAULT '1000-01-01 00:00:00';
 
CREATE UNIQUE INDEX ui_domain ON core_domain (domain);
CREATE INDEX ix_mx_updated ON core_domain(mx_updated);
