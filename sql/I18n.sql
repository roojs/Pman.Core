
CREATE TABLE `i18n` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `ltype` varchar(1) NOT NULL DEFAULT '',
  `lkey` varchar(8) NOT NULL DEFAULT '',
  `inlang` varchar(8) NOT NULL DEFAULT '',
  `lval` varchar(64) NOT NULL DEFAULT '',
  `is_active` int(1) NOT NULL DEFAULT '1',
  PRIMARY KEY (`id`),
  KEY `lookup` (`ltype`,`lkey`,`inlang`)
) ENGINE=InnoDB;


ALTER TABLE i18n ADD COLUMN is_active int(1) NOT NULL DEFAULT 1;

