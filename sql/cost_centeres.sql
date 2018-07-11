CREATE  TABLE cost_centers (
  id INT(11) NOT NULL AUTO_INCREMENT ,
  short_name VARCHAR(8) NULL,
  name VARCHAR(32) NULL,
  PRIMARY KEY (id)
);

ALTER TABLE cost_centers CHANGE COLUMN is_active is_active INT(2) NOT NULL DEFAULT 1;
ALTER TABLE cost_centers ADD COLUMN is_active INT(2) NOT NULL DEFAULT 1;




