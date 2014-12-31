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
    
    //public $name;                            // string(128)  not_null
    public $firstname;                            // string(128)  not_null
    public $lastname;                            // string(128)  not_null
    //public $firstname_alt;                            // string(128)  not_null
    //public $lastname_alt;                            // string(128)  not_null
    
    
    public $honor;                            // string(128)  not_null
    public $verify_key;                      // int(11)
    public $verified;
    

    public $created_dt;                      // datetime(19)  binary

    
    
    
    
    function verify($key)
    {
        // if key matches verify_key
        // copy into person or other entity...
        // and delete....
        $this->whereAdd("verify_key = '".$key."'");
        if($this->count() > 0 ){
            $row = $this->fetch();
            $p = DB_DataObject::factory('person');
            $p->honor = $row->honor;
            $p->name = $row->name;
            $p->email = $row->email;
            $p->firstname = $row->firstname;
            $p->lastname = $row->lastname;
            $p->firstname_alt = $row->firstname_alt;
            $p->lastname_alt = $row->lastname_alt;
            $temp_pwd = $p->generatePassword();
            //$temp_pwd = mt_rand(100000,999999);
            //$p->passwd = $temp_pwd;
            if($p->insert()){
                $this->delete();
                return true;
            }else{
                error_log("db insert error");
                return false;
            }   
        }
        return false;
        
    }
}

    