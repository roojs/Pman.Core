CREATE TABLE core_regionmap (
  id INT NOT NULL AUTO_INCREMENT,
  region_id INT NOT NULL DEFAULT 0,
  country VARCHAR(8) NOT NULL DEFAULT '',
  area_states TEXT,
  PRIMARY KEY (id)
);

ALTER TABLE core_regionmap ADD INDEX ix_region(region_id);
ALTER TABLE core_regionmap ADD INDEX ix_country(country);
ALTER TABLE core_regionmap ADD INDEX lookup(region_id, country);
