CREATE TABLE core_notify_event_archive (
  id BIGINT NOT NULL AUTO_INCREMENT,
  event_when DATETIME DEFAULT NULL,
  action VARCHAR(64) NOT NULL DEFAULT '',
  on_id INT NOT NULL DEFAULT 0,
  on_table VARCHAR(64) NOT NULL DEFAULT '',
  person_id INT NOT NULL DEFAULT 0,
  person_table VARCHAR(64) NOT NULL DEFAULT '',
  person_name VARCHAR(128) NOT NULL DEFAULT '',
  ipaddr VARCHAR(16) NOT NULL DEFAULT '',
  remarks TEXT,
  dupe_id INT NOT NULL DEFAULT 0,
  PRIMARY KEY (id),
  INDEX lookup (on_id, action, event_when)
);
