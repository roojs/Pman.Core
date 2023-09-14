<?php

/**
 * assignment management - for supplier.s
 * 
 * call sigs:
 *   
 *   _createPasswd : 1  = generate a password
 *   rawPasswd : use this as a password
 *    
 *   _create : 1    (create the account)
 *      email : email address
 *      if _createPassword is empty and rawPasswd is empty, then no message is sent.!!!!
 *   
 *   _create : 0 // or not set - password sending only.
 *     id : id of person..
 *     
 * 
 */

require_once 'Pman.php';

class Pman_Core_SendIntro extends Pman
{
    
    function getAuth() 
    {
        
        $au = $this->getAuthUser();
        if (!$au) {
            $this->jerror("LOGIN-NOAUTH", "Not authenticated", array('authFailure' => true));
        }
        $this->authUser = $au;
        // check that it's a supplier!!!! 
        
        return true; 
    }
    
    function post($v)
    {
        //DB_DataObject::debuglevel(1);
        //  gets id : c.id,  rawPasswd: c.rawPasswd
        
        if (!$this->hasPerm("Core.Person", "A")) {
             $this->jerr("Not Permitted - no permission to add users.");
        }
        $p = DB_DataObject::factory('core_person');
        
        // let's make a password anyway..
        $rawPasswd = false;
        if (!empty($_REQUEST['_createPasswd'])) {
            require_once  'Text/Password.php';
            $rawPasswd = Text_Password::create(6). rand(11,99);
        }
        if (!empty($_REQUEST['rawPasswd'])) {
            $rawPasswd = $_REQUEST['rawPasswd'];
        }
        
        $id = empty($_REQUEST['id']) ? 0 : $_REQUEST['id'];
        
        if (!empty($_REQUEST['_create'])) {
            // check account does not exist yet..
            if ($p->get('email', $_REQUEST['email'])) {
                $this->jerr("duplicate email address:" .$_REQUEST['email']);
            }
            $p = DB_DataObject::factory('core_person');
            $p->setFrom($_REQUEST);
            
            if ($rawPasswd == false) {
                // -- needed for bulk adding... ?*** not sure why it's here, rather than in Roo?
                $p->insert();
                $this->jok("OK");
                
            }
            // generate a password.
            
            
            $p->insert();
            $id = $p->id;
            
        } 
        
        
        $p = DB_DataObject::factory('core_person');
        
        if (!$id || !$p->get($_REQUEST['id']))  {
            $this->jerr("Invalid user id");
        }
        
        
        if ($rawPasswd !== false) {
            $p->setPassword($rawPasswd);
            $p->update();
        }
        // next.. 
        
        
        $ret = $p->sendTemplate('password_welcome', array(
            'sender' => $this->authUser,
            'rawPasswd' => $rawPasswd,
            'baseURL' => $this->baseURL,
        ));
        if (is_object($ret)) {
            $this->jerr($ret->toString());
            
        }
        
        $this->jok("SENT");
        
        // 
       
         
    }
    
} 