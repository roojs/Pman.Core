CREATE  TABLE core_person_window (
  id INT(11) NOT NULL AUTO_INCREMENT ,
  login_dt DATETIME NOT NULL DEFAULT '1000-01-01 00:00:00',
  window_id VARCHAR(64) NOT NULL DEFAULT '',
  app_id VARCHAR(128) NOT NULL DEFAULT '',
  person_id INT NOT NULL DEFAULT 0,
  PRIMARY KEY (id)
);

 
ALTER TABLE core_person_window ADD UNIQUE INDEX lookup (window_id, person_id, app_id);
ALTER TABLE core_person_window ADD last_access_dt;
    
ALTER TABLE core_person_window ADD ip VARCHAR(32) NOT NULL DEFAULT ''
ALTER TABLE core_person_window ADD user_agent VARCHAR(254) NOT NULL DEFAULT ''
ALTER TABLE core_person_window ADD status ENUM('IN','OUT','KILL') NOT NULL DEFAULT 'IN';
ALTER TABLE core_person_window DROP force_logout;
    