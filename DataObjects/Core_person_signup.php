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
    public $verified;
    public $created_dt;                      // datetime(19)  binary

//    function verify($key) 
//    {
//        // if key matches verify_key
//        // copy into person or other entity...
//        // and delete....
//        if ($this->get("verify_key", $key)) {
//            $p = DB_DataObject::factory('person');
//            $p->setFrom(array(
//                'honor' => $this->honor,
//                'name' => $this->name,
//                'email' => $this->email,
//                'firstname' => $this->firstname,
//                'lastname' => $this->lastname,
//                'firstname_alt' => $this->firstname_alt,
//                'lastname_alt' => $this->lastname_alt));
//
//            if ($p->insert()) {
//
//                $temp_pwd = $p->generatePassword();
//
//                $this->delete();
//
//                //login
//                @session_start();
//
//                $_SESSION['Hydra']['authUser'] = $p ? serialize($p) : false;
//
//                //mail pwd
//                $c = DB_DataObject::factory('core_email');
//                if (!$c->get('name', 'CORE_PERSON_SIGNUP_CONGRATULATION')) {
//                    $this->jerr("can not find template");
//                }
//                $ret = $c->send(array(
//                    'rcpts' => $this->email,
//                    'honor' => $this->honor . ". " . $this->lastname,
//                    'password' => $temp_pwd
//                        ), true);
//
//                if (is_object($ret)) {
//                    return false;
//                }
//                return true;
//            } else {
//                return false;
//            }
//        }
//        return false;
//    }
//
//    function convertTo($table) 
//    {
//        $t = DB_DataObject::factory($table);
//        $ret = $t->get('email', $this->email);
//        if ($ret != 0) {
//            return false;
//        } else {
//            $t->setFrom(array(
//                'honor' => $this->honor,
//                'name' => $this->name,
//                'email' => $this->email,
//                'firstname' => $this->firstname,
//                'lastname' => $this->lastname,
//                'firstname_alt' => $this->firstname_alt,
//                'lastname_alt' => $this->lastname_alt));
//
//            $t->insert();
//            return true;
//        }
//    }

    function sendVerification($roo)
    {
        
        $content = array(
            'template'      => 'CORE_PERSON_SIGNUP_VERIFICATION',
            'person'        => $this,
            'serverName'    => $_SERVER['SERVER_NAME'],
            'baseURL'       => $roo->baseURL
        );

        $sent = DB_DataObject::factory('core_email')->send($content);
        
        if(!is_object($sent)){
            return true;
        }
        
        return false;
    }
    
    function getEmailFrom()
    {
        if (empty($this->name)) {
            return $this->email;
        }
        return '"' . addslashes($this->name) . '" <' . $this->email . '>';
    }
}

