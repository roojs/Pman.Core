
CREATE TABLE  core_templatestr (
  id int(11)  NOT NULL AUTO_INCREMENT,
  template varchar(254)  NOT NULL,
  txt text  NOT NULL,
  updated datetime  NOT NULL,
  src_id int(11)  NOT NULL,
  lang varchar(6)  NOT NULL,
  PRIMARY KEY (id),
  INDEX lookup(template, lang)
);

ALTER TABLE core_templatestr ADD COLUMN active INT(4) NOT NULL  DEFAULT 0;

ALTER TABLE core_templatestr ADD COLUMN mdsum VARCHAR(64) NOT NULL DEFAULT '';
 
ALTER TABLE core_templatestr ADD COLUMN template_id INT(11) NOT NULL DEFAULT 0;
ALTER TABLE core_templatestr DROP COLUMN template;

ALTER TABLE core_templatestr ADD COLUMN on_table VARCHAR(64) DEFAULT '';
ALTER TABLE core_templatestr ADD COLUMN on_id INT(11) DEFAULT 0;
ALTER TABLE core_templatestr ADD COLUMN on_col VARCHAR(64) DEFAULT '';

ALTER TABLE core_templatestr ADD INDEX lookup_a(active, lang, on_table, on_id, on_col);

