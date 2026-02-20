CREATE TABLE core_company (
  id int(11)  NOT NULL auto_increment,
  PRIMARY KEY   (id)
);


ALTER TABLE core_company CHANGE COLUMN  code  code varchar(128)  NOT NULL DEFAULT '';
ALTER TABLE core_company ADD COLUMN    code varchar(128)  NOT NULL DEFAULT '';
ALTER TABLE core_company CHANGE COLUMN    name name varchar(254)  default NULL ;

ALTER TABLE core_company ADD COLUMN    name varchar(254)  default NULL ;
ALTER TABLE core_company ADD COLUMN    remarks text ;
ALTER TABLE core_company ADD COLUMN    owner_id int(11) NOT NULL DEFAULT 0 ;
ALTER TABLE core_company ADD COLUMN    address text ;
ALTER TABLE core_company ADD COLUMN    tel varchar(32)  default NULL;
ALTER TABLE core_company ADD COLUMN    fax varchar(32)  default NULL;
ALTER TABLE core_company ADD COLUMN    email varchar(128)  default NULL;
-- ALTER TABLE core_company ADD COLUMN    isOwner int(11) default NULL;
ALTER TABLE core_company ADD COLUMN    logo_id INT(11)  NOT NULL DEFAULT 0;;
ALTER TABLE core_company ADD COLUMN    background_color varchar(8)  NOT NULL;
ALTER TABLE core_company ADD COLUMN    url varchar(254)  NOT NULL DEFAULT '';
ALTER TABLE core_company ADD COLUMN    main_office_id int(11)  NOT NULL DEFAULT 0;
ALTER TABLE core_company ADD COLUMN    created_by int(11)  NOT NULL DEFAULT 0;
ALTER TABLE core_company ADD COLUMN    created_dt datetime  NOT NULL;
ALTER TABLE core_company ADD COLUMN    updated_by int(11)  NOT NULL DEFAULT 0;
ALTER TABLE core_company ADD COLUMN    updated_dt datetime  NOT NULL;
ALTER TABLE core_company ADD COLUMN    passwd varchar(64) NOT NULL DEFAULT '';
ALTER TABLE core_company ADD COLUMN    dispatch_port varchar(255) NOT NULL DEFAULT '';
ALTER TABLE core_company ADD COLUMN    province varchar(255) NOT NULL DEFAULT '';
ALTER TABLE core_company ADD COLUMN    country varchar(4) NOT NULL DEFAULT '';


ALTER TABLE core_company ADD COLUMN    comptype varchar(32)  NOT NULL DEFAULT '';
-- not sure if this needs to change.. << there is code in core/update that fills this in??
ALTER TABLE core_company ADD COLUMN    comptype_id INT(11) DEFAULT 0;

ALTER TABLE core_company ADD COLUMN  parent_id INT NOT NULL DEFAULT 0;


 
ALTER TABLE core_company CHANGE COLUMN isOwner isOwner int(11);

ALTER TABLE core_company CHANGE COLUMN comptype comptype  VARCHAR(32) DEFAULT '';

-- postres
-- ALTER TABLE core_company ALTER isOwner TYPE int(11);
ALTER TABLE core_company ALTER owner_id SET DEFAULT 0;
ALTER TABLE core_company ALTER url SET DEFAULT '';

 

ALTER TABLE core_company ADD COLUMN    address1 text ;
ALTER TABLE core_company ADD COLUMN    address2 text ;
ALTER TABLE core_company ADD COLUMN    address3 text ;
ALTER TABLE core_company ADD COLUMN is_system INT(2) NOT NULL DEFAULT 0;-- #2028


ALTER TABLE core_company ADD COLUMN deleted_by INT(11)  NOT NULL DEFAULT 0;
alter table core_company CHANGE COLUMN  deleted_dt deleted_dt datetime NOT NULL DEFAULT '1000-01-01 00:00:00';
ALTER TABLE core_company ADD COLUMN deleted_dt DATETIME  NOT NULL DEFAULT '1000-00-00'; 

-- indexs


ALTER TABLE core_company ADD INDEX name_lookup (name);
ALTER TABLE core_company ADD INDEX lookup_owner_id (owner_id);

-- our new code should have this fixed now..
-- UPDATE core_company set comptype='OWNER' where isOwner=1;

ALTER TABLE core_company ADD COLUMN source VARCHAR(255) NOT NULL DEFAULT '';
 
