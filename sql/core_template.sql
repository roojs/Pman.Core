-- template .
CREATE TABLE  core_template (
  id int(11)  NOT NULL AUTO_INCREMENT,
  template varchar(254)  NOT NULL,
  updated datetime  NOT NULL,
  lang varchar(6)  NOT NULL,
  PRIMARY KEY (id),
  INDEX lookup(template, lang)
);

ALTER TABLE core_template ADD COLUMN view_name varchar(32) NOT NULL default '';
ALTER TABLE core_template ADD COLUMN filetype varchar(32) NOT NULL default '';
