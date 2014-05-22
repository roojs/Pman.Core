<?php

require_once 'Pman.php';

class Pman_Core_DatabaseColumns extends Pman {
    
    
    function getAuth()
    {
        parent::getAuth(); // load company!
        $au = $this->getAuthUser();
       
        if (!$au) {  
            $this->jerr("Not authenticated", array('authFailure' => true));
        }
        if (!$au->pid()   ) { // not set up yet..
            $this->jerr("Not authenticated", array('authFailure' => true));
        }
        
        
        $this->authUser = $au;
        return true;
    }
    
    function get($table) {
        $d = DB_DAtaObject::Factory($table);
        $re = $d->autoJoin();
        //echo '<PRE>';print_r($re);
        $ret = array ();
        
        foreach($re['join_names'] as $c=>$f) {
            $re['cols'][$c] = $f;
        }
        
        
        foreach($re['cols'] as $c=>$f) {
            $ret[]  = array(
                'name' => $c,
                'val' => $f
            );
            
        }
        
        
        require_once 'Pman/Core/SimpleExcel.php';
        $x = new Pman_Core_SimpleExcel(
            array($bt,$et, $ut), array(
            'formats' => array(
                'vtop' => array('vAlign' => 'top'),
                'vcenter' => array('vAlign' => 'vcenter'),
                
                'percent' => array('vAlign' => 'vcenter', 'numFormat' => '0%'),
                'money' => array('vAlign' => 'vcenter', 'numFormat' => '$#,###'),
                'date' => array('vAlign' => 'vcenter', 'numFormat' => 'd/M/Y'),
            ),
            'workbooks' => array(
                array(
                    'workbook' => 'Business',
                    'cols' => explode('|', 'keyIdentity_taxId|description|type|countryIso3|countryRegion|addr1|addr2|addr3|city|zip')
                ),
                array(
                    'workbook' => 'Employment',
                    'cols' => explode('|', 'emailAddress|identity_taxId')
                ),
                array(
                    'workbook' => 'Users',
                    'cols' => explode('|', 'generateLogin|firstName|middleName|lastName|emailAddress|locale|countryIso3|countryRegion|addr1|addr2|addr3|city|zip|userStatus')
                ),
            )
            
        ));
        $x->send('test_'. date('d_M_Y').'.xls');
        $this->jdata($ret);
        
        
    }
}