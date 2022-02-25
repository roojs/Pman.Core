

CREATE TABLE  i18n (
  id int(11)  NOT NULL AUTO_INCREMENT,
  PRIMARY KEY (id)

);

ALTER TABLE  i18n ADD COLUMN   ltype varchar(1)  NOT NULL DEFAULT '';
ALTER TABLE  i18n ADD COLUMN   lkey varchar(8)  NOT NULL DEFAULT '';
ALTER TABLE  i18n ADD COLUMN   inlang varchar(8)  NOT NULL DEFAULT '';
ALTER TABLE  i18n ADD COLUMN   lval varchar(64)  NOT NULL DEFAULT '';
ALTER TABLE i18n ADD COLUMN is_active int(1) NOT NULL DEFAULT 1;
ALTER TABLE i18n ADD COLUMN is_prefer int(2) NOT NULL DEFAULT 0;

ALTER TABLE i18n ADD INDEX lookup (ltype, lkey, inlang);
ALTER TABLE i18n ADD INDEX lookup_a (ltype, lkey, inlang, is_active);
