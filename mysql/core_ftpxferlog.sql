CREATE TABLE core_ftpxferlog (
  id INT NOT NULL AUTO_INCREMENT,
  username VARCHAR(30) NOT NULL DEFAULT '',
  filename text,
  txsize BIGINT(20) DEFAULT NULL,
  host tinytext,
  ip tinytext,
  action tinytext,
  duration tinytext,
  txlocaltime TIMESTAMP DEFAULT NULL,
  success INT(1) NOT NULL DEFAULT 0,
  PRIMARY KEY  (id),
  KEY idx_usersucc (username, success)
);
