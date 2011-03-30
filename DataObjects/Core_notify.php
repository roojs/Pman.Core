<?php
/**
 *
 * Table is designed to be used with a mailer to notify or issue
 * emails (or maybe others later??)
 *
 *
CREATE TABLE  core_notify  (
  `id` int(11)  NOT NULL AUTO_INCREMENT,
  `act_when` DATETIME NOT NULL,
  `onid` int(11)  NOT NULL DEFAULT 0,
  `ontable` varchar(128)  NOT NULL DEFAULT '',
  `person_id` int(11)  NOT NULL DEFAULT 0,
  `msgid` varchar(128)  NOT NULL  DEFAULT '',
  `sent` DATETIME  NOT NULL,
  `bounced` int(4)  NOT NULL DEFAULT 0,
  PRIMARY KEY (`id`),
  INDEX `lookup`(`act_when`, `msgid`)
);
*/
 
require_once 'DB/DataObject.php';

class Pman_Core_DataObjects_Core_notify extends DB_DataObject 
{
    ###START_AUTOCODE
    /* the code below is auto generated do not remove the above tag */

    
    public $__table = 'core_nofity';         
    public $act_when;                        
    
    public $ontable;                         
    public $onid;                            
    
    public $person_id;                       
    
    public $msgid;   // message id after it has been sent.                          
    public $sent;    // date it was sent.?? or last effort..
    public $bounced; // 1 - failed to send (??) // 2 = we got a bounce.

    