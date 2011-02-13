
CREATE TABLE `Companies` (
  `code` varchar(32)  NOT NULL,
  `name` varchar(128)  default NULL,
  `remarks` text ,
  `owner_id` int(11) NOT NULL,
  `address` text ,
  `tel` varchar(32)  default NULL,
  `fax` varchar(32)  default NULL,
  `email` varchar(128)  default NULL,
  `id` int(11) NOT NULL auto_increment,
  `isOwner` int(11) default NULL,
  PRIMARY KEY   (`id`),
  KEY `Company_Name` (`name`)
) ;

alter table Companies change column isOwner isOwner int(11);
ALTER TABLE Companies ADD COLUMN logo_id INT(11)  NOT NULL;
ALTER TABLE Companies  ADD COLUMN background_color varchar(8)  NOT NULL;
ALTER TABLE Companies  ADD COLUMN comptype varchar(8)  NOT NULL;


ALTER TABLE `Companies` ADD COLUMN `url` varchar(254)  NOT NULL;
ALTER TABLE `Companies` ADD COLUMN `main_office_id` int(11)  NOT NULL;


ALTER TABLE `Companies` ADD COLUMN `created_by` int(11)  NOT NULL;
ALTER TABLE `Companies` ADD COLUMN `created_dt` datetime  NOT NULL;
ALTER TABLE `Companies` ADD COLUMN `updated_by` int(11)  NOT NULL;
ALTER TABLE `Companies` ADD COLUMN `updated_dt` datetime  NOT NULL;

ALTER TABLE `Companies` ADD COLUMN   `passwd` varchar(64) NOT NULL;


ALTER TABLE Companies 
	ADD COLUMN dispatch_port varchar(255) NOT NULL DEFAULT '',
	ADD COLUMN province varchar(255) NOT NULL DEFAULT '',
	ADD COLUMN country varchar(4) NOT NULL DEFAULT '';

 
UPDATE Companies set comptype='OWNER' where isOwner=1;


CREATE TABLE  `core_company_type` (
  `id` int(11)  NOT NULL AUTO_INCREMENT,
  `name` varchar(64)  NOT NULL,
  PRIMARY KEY (`id`)
);


CREATE TABLE `Events` (
  `id` int(11) NOT NULL auto_increment,
  `person_name` varchar(128)  default NULL,
  `event_when` datetime default NULL,
  `action` varchar(32)  default NULL,
  `ipaddr` varchar(16)  default NULL,
  `on_id` int(11) default NULL,
  `on_table` varchar(64)  default NULL,
  `person_id` int(11) default NULL,
  `remarks` text ,
  PRIMARY KEY  (`id`)
) ;


ALTER TABLE Events CHANGE COLUMN EventID id INT(11) AUTO_INCREMENT NOT NULL;
ALTER TABLE Events CHANGE COLUMN User person_name VARCHAR(128);
ALTER TABLE Events ADD COLUMN person_id INT(11);
ALTER TABLE Events CHANGE COLUMN Date event_when DATETIME;
ALTER TABLE Events CHANGE COLUMN Event action VARCHAR(32);
ALTER TABLE Events CHANGE COLUMN Host ipaddr VARCHAR(16);
ALTER TABLE Events CHANGE COLUMN ItemID on_id INT(11);
ALTER TABLE Events CHANGE COLUMN Container on_table VARCHAR(64);
ALTER TABLE Events ADD COLUMN remarks INT(11);



CREATE TABLE `Group_Members` (
  `group_id` int(11) default NULL,
  `id` int(11) NOT NULL auto_increment,
  `user_id` int(11) NOT NULL default '0',
  PRIMARY KEY  (`id`)
);


CREATE TABLE `Group_Rights` (
  `rightname` varchar(64)  NOT NULL,
  `group_id` int(11) NOT NULL,
  `AccessMask` varchar(10)  NOT NULL,
  `id` int(11) NOT NULL auto_increment,
  PRIMARY KEY  (`id`)
) ;




CREATE TABLE `Groups` (
  `id` int(11) NOT NULL auto_increment,
  `name` varchar(64)  NOT NULL,
  `type` int(11) default NULL,
  `leader` int(11) NOT NULL default '0',
  PRIMARY KEY   (`id`)
);



alter table Groups add column type int(11) default 0;
ALTER TABLE `Groups` ADD COLUMN `leader` int(11)  NOT NULL default 0;
ALTER TABLE Groups CHANGE COLUMN type type int(11) default 0;




CREATE TABLE `Office` (
  `id` int(11) NOT NULL auto_increment,
  `company_id` int(11) NOT NULL default '0',
  `name` varchar(64)  NOT NULL,
  `address` text  NOT NULL,
  `phone` varchar(32)  NOT NULL,
  `fax` varchar(32)  NOT NULL,
  `email` varchar(128)  NOT NULL,
  `role` varchar(32)  NOT NULL,
  PRIMARY KEY  (`id`)
);

CREATE TABLE `Person` (
  `id` int(11) NOT NULL auto_increment,
  `office_id` int(11) default '0',
  `name` varchar(128)  NOT NULL,
  `phone` varchar(32)  NOT NULL,
  `fax` varchar(32)  NOT NULL,
  `email` varchar(128)  NOT NULL,
  `company_id` int(11) default '0',
  `role` varchar(32)  NOT NULL,
  `active` int(11) default NULL,
  `remarks` text NOT NULL,
  `passwd` varchar(64) NOT NULL,
  `owner_id` int(11) NOT NULL,
  `lang` varchar(8) default 'en',
  `no_reset_sent` int(11) default '0',
  PRIMARY KEY  (`id`)
) ;


 
ALTER TABLE Person ADD COLUMN no_reset_sent INT(11) DEFAULT 0;
ALTER TABLE Person ADD COLUMN action_type VARCHAR(32) DEFAULT '';
 ALTER TABLE Person ADD COLUMN project_id int(11) default 0;

ALTER TABLE Person ADD COLUMN action_type VARCHAR(32) default '';

ALTER TABLE Person ADD COLUMN deleted_by INT(11) NOT NULL default 0 ;
ALTER TABLE Person ADD COLUMN deleted_dt DATETIME;

 alter table Person change column active active int(11) NOT NULL DEFAULT 1 ;


CREATE TABLE `Projects` (
  `id` int(11) NOT NULL auto_increment,
  `name` varchar(254)  NOT NULL,
  `remarks` text  NOT NULL,
  `owner_id` int(11) default NULL,
  `code` varchar(32)  NOT NULL,
  `active` int(11) default '1',
  `type` varchar(1)  NOT NULL default 'P',
  `client_id` int(11) NOT NULL default '0',
  `team_id` int(11) NOT NULL default '0',
  `file_location` varchar(254)    NOT NULL default '',
  `open_date` date default NULL,
  `open_by` int(11) NOT NULL default '0',
  PRIMARY KEY  (`id`),
  KEY `plookup` (`code`)
) ;

alter table Projects add column active int(11) default 1;
alter table Projects add index plookup(code);

ALTER TABLE  Projects  ADD COLUMN `type` varchar(1)  NOT NULL DEFAULT 'P';
 ALTER TABLE  Projects ADD COLUMN `client_id` int(11)  NOT NULL DEFAULT 0 ;
 ALTER TABLE  Projects ADD COLUMN `team_id` int(11)  NOT NULL DEFAULT 0;
 ALTER TABLE  Projects ADD COLUMN `file_location` varchar(254)  NOT NULL DEFAULT '';
 ALTER TABLE  Projects ADD COLUMN `open_date` date  ;
 ALTER TABLE  Projects ADD COLUMN `close_date` date  ;
 ALTER TABLE  Projects ADD COLUMN `open_by` int(11)  NOT NULL DEFAULT 0;

ALTER TABLE `Projects` ADD COLUMN `countries` varchar(128)  NOT NULL;
ALTER TABLE `Projects`  ADD COLUMN `languages` varchar(128)  NOT NULL;

ALTER TABLE  Projects ADD COLUMN agency_id int(11)  NOT NULL DEFAULT 0 ;




CREATE TABLE   `Images` (
  `id` int(11) NOT NULL auto_increment,
  `filename` varchar(255) NOT NULL default '',
  `ontable` varchar(32) NOT NULL default '',
  `onid` int(11) NOT NULL default '0',
  `mimetype` varchar(64) NOT NULL default '',
  `width` int(11) NOT NULL default '0',
  `height` int(11) NOT NULL default '0',
  `filesize` int(11) NOT NULL default '0',
  `displayorder` int(11) NOT NULL default '0',
  `language` varchar(6) NOT NULL default 'en',
  `parent_image_id` int(11) NOT NULL default '0',
  PRIMARY KEY  (`id`)
);



ALTER TABLE images    ADD COLUMN  `width` int(11) NOT NULL default '0';
ALTER TABLE images    ADD COLUMN  `height` int(11) NOT NULL default '0';
ALTER TABLE images    ADD COLUMN  `filesize` int(11) NOT NULL default '0';
ALTER TABLE images    ADD COLUMN  `displayorder` int(11) NOT NULL default '0';
ALTER TABLE images    ADD COLUMN  `language` varchar(6) NOT NULL default 'en';
ALTER TABLE images    ADD COLUMN  `parent_image_id` int(11) NOT NULL default '0';



ALTER TABLE `Images` ADD INDEX `lookup`(`ontable`, `onid`);

ALTER TABLE  `Images` ADD COLUMN `created` datetime  NOT NULL;
ALTER TABLE  `Images` ADD COLUMN `imgtype` VARCHAR(32) DEFAULT '' NOT NULL;
ALTER TABLE  `Images` ADD COLUMN `linkurl` VARCHAR(254) DEFAULT '' NOT NULL;
ALTER TABLE  `Images` ADD COLUMN `descript` TEXT DEFAULT '' NOT NULL;
ALTER TABLE  `Images` ADD COLUMN `title` VARCHAR(128) DEFAULT '' NOT NULL;
 
CREATE TABLE  `core_image_type` (
  `id` int(11)  NOT NULL AUTO_INCREMENT,
  `name` varchar(64)  NOT NULL,
  PRIMARY KEY (`id`)
) ;

 


CREATE TABLE  `i18n` (
  `id` int(11)  NOT NULL AUTO_INCREMENT,
  `ltype` varchar(1)  NOT NULL,
  `lkey` varchar(8)  NOT NULL,
  `inlang` varchar(8)  NOT NULL,
  `lval` varchar(64)  NOT NULL,
  PRIMARY KEY (`id`),
  INDEX `lookup`(`ltype`, `lkey`, `inlang`)
) ;


DROP FUNCTION IF EXISTS i18n_translate;

DELIMITER $$
CREATE FUNCTION i18n_translate(in_ltype  varchar(1) , in_lkey varchar(8), in_inlang varchar(8)) 
        RETURNS VARCHAR(64) DETERMINISTIC
    BEGIN
        DECLARE ret  VARCHAR(64);
        SET ret  = '';
        SELECT lval INTO ret FROM i18n
            WHERE ltype=in_ltype AND lkey=in_lkey and inlang=in_inlang LIMIT 1;
        RETURN ret;
        
    END $$
DELIMITER ;
			
        
    
CREATE TABLE  core_locking (
  `int` int(11)  NOT NULL AUTO_INCREMENT,
  `on_table` varchar(64)  NOT NULL,
  `on_id` int(11)  NOT NULL,
  `person_id` int(11)  NOT NULL,
  `created` datetime  NOT NULL,
  PRIMARY KEY (`int`),
  INDEX `lookup`(`on_table`, `on_id`, `person_id`, `created`)
);




CREATE TABLE  `translations` (
  `id` int(11)  NOT NULL AUTO_INCREMENT,
  `module` varchar(64)  NOT NULL,
  tfile varchar(128) NOT NULL,
  tlang varchar(8)  NOT NULL,
  tkey varchar(32)  NOT NULL,
  tval longtext  NOT NULL,
  PRIMARY KEY (`id`)
);

ALTER TABLE translations ADD INDEX qlookup (module, tfile, tlang, tkey);
