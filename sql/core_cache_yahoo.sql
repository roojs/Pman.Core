CREATE TABLE core_cache_yahoo (
  id INT  NOT NULL auto_increment DEFAULT 0,
  query longtext NOT NULL DEFAULT '',
  result longtext NOT NULL DEFAULT '',
  PRIMARY KEY   (id)
);

ALTER TABLE core_cache_yahoo ADD INDEX qlookup (query);