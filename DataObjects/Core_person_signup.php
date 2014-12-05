CREATE TABLE core_person_signup (
  id int(11) NOT NULL auto_increment,
  PRIMARY KEY  (id)
) ;


ALTER TABLE core_person_signup ADD COLUMN   name varchar(128)  NOT NULL  DEFAULT '';
ALTER TABLE core_person_signup ADD COLUMN   honor varchar(32) NOT NULL DEFAULT '';
ALTER TABLE core_person_signup ADD COLUMN   firstname varchar(128) NOT NULL DEFAULT '';
ALTER TABLE core_person_signup ADD COLUMN   lastname varchar(128) NOT NULL DEFAULT '';
ALTER TABLE core_person_signup ADD COLUMN   firstname_alt varchar(128) NOT NULL DEFAULT '';
ALTER TABLE core_person_signup ADD COLUMN   lastname_alt varchar(128) NOT NULL DEFAULT '';

ALTER TABLE core_person_signup ADD COLUMN   email varchar(256)  NOT NULL DEFAULT '';
ALTER TABLE core_person_signup ADD COLUMN   verify_key varchar(256)  NOT NULL DEFAULT '';
ALTER TABLE core_person_signup ADD COLUMN   created_dt DATETIME  NOT NULL;
<?php
/**
 * Table Definition for Person
 */
require_once 'DB/DataObject.php';


class Pman_Core_DataObjects_Core_person_signup extends DB_DataObject 
{
    ###START_AUTOCODE
    /* the code below is auto generated do not remove the above tag */

    public $__table = 'core_person_signup';                          // table name
    public $id;                              // int(11)  not_null primary_key auto_increment
    public $email;                           // string(128)  not_null
    
    public $name;                            // string(128)  not_null
    public $firstname;                            // string(128)  not_null
    public $lastname;                            // string(128)  not_null
    public $firstname_alt;                            // string(128)  not_null
    public $lastname_alt;                            // string(128)  not_null
    
    
    public $honor;                            // string(128)  not_null
    public $verify_key;                      // int(11)

    

    public $created_dt;                      // datetime(19)  binary

    
    
    
}

    