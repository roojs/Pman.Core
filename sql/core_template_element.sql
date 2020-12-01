

-- templat eelement {element[xxxx].*}
CREATE TABLE  core_template_element (
  id int(11)  NOT NULL AUTO_INCREMENT,
  name varchar(254)  NOT NULL,
  PRIMARY KEY (id)
);
 
ALTER TABLE core_template_element ADD COLUMN template_id INT(11) NOT NULL DEFAULT 0;
