CREATE TABLE core_cache_yahoo (
  id int(11)  NOT NULL auto_increment,
  query longtext NOT NULL,
  result longtext NOT NULL,
  PRIMARY KEY   (id)
);