<?php

/**
 * Table Definition for Person
 */
class_exists('DB_DataObject') ? '' : require_once 'DB/DataObject.php';
require_once 'Mail/RFC822.php';

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
    public $company_name;
    public $person_type;
    
    public $person_id;
    public $person_table;
    
    public $inviter_id;
 
    function convertTo($target = false)
    {
        if(!$target){
            return false;
        }
        
        $roo = HTML_FlexyFramework::get()->page;
        $old = clone($this);
        // this shold not really happen...
        if($target->get('email', $this->email)){
            return false;
        }
        
        $target->setFrom($this->toArray());
        
        $target->insert();
        
        $this->person_id = $target->id;
        $this->person_table = $target->tableName();
        $this->update($old);
        
        if(!empty($this->inviter_id) && method_exists($target, 'createFriend')){
            $target->createFriend($this->inviter_id);
        }
        
        return $target;
    }
    
    function sendVerification($template, $roo)
    {
        $content = array(
            'template'      => $template,
            'person'        => $this,
            'serverName'    => $_SERVER['SERVER_NAME'],
            'baseURL'       => $roo->baseURL
        );
        
        $sent = DB_DataObject::factory('core_email')->send($content);
        
        if(!is_object($sent)){
            return true;
        }
        
        return $sent;
    }
    
    function getEmailFrom()
    {
        return Mail_RFC822::create($this->name, $this->email)->toMime();
    }
}

