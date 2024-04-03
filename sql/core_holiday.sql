CREATE TABLE core_holiday (
  id int(11)  NOT NULL auto_increment,
  PRIMARY KEY   (id)
);

ALTER TABLE core_holiday ADD COLUMN holiday_date DATE NOT NULL DEFAULT '0000-00-00';
ALTER TABLE core_holiday ADD COLUMN country VARCHAR(4) NOT NULL DEFAULT '';
ALTER TABLE core_holiday ADD COLUMN name VARCHAR(128) NOT NULL DEFAULT '';


alter table core_holiday add index lookup (holiday_date, country);
