CREATE  TABLE core_person_window (
  id INT(11) NOT NULL AUTO_INCREMENT ,
  login_dt DATETIME NOT NULL DEFAULT '1000-01-01 00:00:00',
  window_id VARCHAR(64) NOT NULL DEFAULT '',
  person_id INT NOT NULL DEFAULT 0,
  force_logout INT(2) NOT NULL DEFAULT 0,
  PRIMARY KEY (id)
);

 
ALTER TABLE core_person_window ADD UNIQUE INDEX lookup (window_id, person_id);
 