CREATE TABLE core_cache_yahoo (
  id INT NOT NULL auto_increment,
  query varchar(254) NOT NULL DEFAULT '',
  result longtext NOT NULL DEFAULT '',
  PRIMARY KEY (id)
);

ALTER TABLE core_cache_yahoo ADD INDEX qlookup (query);