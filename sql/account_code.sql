CREATE  TABLE account_code (
  id INT(11) NOT NULL AUTO_INCREMENT ,
  name VARCHAR(8) NULL,
  description VARCHAR(64) NULL,
  cost_center INT(11) NULL,
  accpac VARCHAR(32) NULL,
  accpac_out VARCHAR(32) NULL,
  PRIMARY KEY (id)
);

ALTER TABLE account_code CHANGE COLUMN is_active is_active INT(2) NOT NULL DEFAULT 1;
ALTER TABLE account_code ADD COLUMN is_active INT(2) NOT NULL DEFAULT 1;




