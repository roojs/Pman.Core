<?php

class_exists('DB_DataObject') ? '' : require_once 'DB/DataObject.php';

class Pman_Core_DataObjects_Core_setting extends DB_DataObject
{
    public $__table = 'core_setting';
    
    function initKeys($dir)
    {
        //reject when keys exist
        if(
            !file_exists($dir) ||
            file_exists("{$dir}/pub.key") ||
            file_exists("{$dir}/pri.key")
        ){
            return;
        }
        
        $ssl = openssl_pkey_new(array(
            "digest_alg" => "sha512",  
            "private_key_bits" => 1024, //returns cipher in 128 char
            "private_key_type" => OPENSSL_KEYTYPE_RSA
        ));
        
        openssl_pkey_export($ssl, $pri_key);
        $pub_key = openssl_pkey_get_details($ssl);
        $pub_key = $pub_key["key"];
        
        file_put_contents("{$dir}/pub.key",$pub_key);
        file_put_contents("{$dir}/pri.key",$pri_key);
    }
    
    function getSetting($m,$n)
    {
        $s = DB_DataObject::factory('core_setting');
        $s->setFrom(array(
            'module' => $q['module'],
            'name' => $q['name']
        ));
        if($s->find(true)) {
            return $s;
        }
        return false;
    }
    
    function beforeInsert($q, $roo)
    {
        return;
    }
    
    function initSetting($a)
    {
        if(empty($a)) {
            return;
        }
        
        //check setting exist
        $c = $this->getSetting($a['module'], $a['name']);
        if($c) {
            return;
        }
        
        $val = $a['val'];
        if(!isset($a['is_encrypt']) || $a['is_encrypt'] == 1) {
            $val = encrypt($val);
        }
        
        return;
    }
    
    function encrypt($v) {
        
    }
    
    
}
