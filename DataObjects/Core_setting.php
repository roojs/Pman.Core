<?php

class_exists('DB_DataObject') ? '' : require_once 'DB/DataObject.php';

class Pman_Core_DataObjects_Core_setting extends DB_DataObject
{
    public $__table = 'core_setting';
    
    function initKeys()
    {
        $dir = $this->keyDir();
        if(
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
    
    //FIXME - rename to lookup
    
    function getSetting($m,$n)
    {
        $s = DB_DataObject::factory('core_setting');
        $s->setFrom(array(
            'module' => $m,
            'name' => $n
        ));
        if($s->find(true)) {
            return $s;
        }
        return false;
    }
    
    function beforeInsert($q, $roo)
    {
        exit;
        
        return;
    }
    
    function keyDir()
    {
        $d = HTML_FlexyFramework::get()->Pman['storedir'].'/key';
        if(!file_exists($d)) {
            $oldumask = umask(0);
            mkdir($d, 0775, true);
            umask($oldumask);  
        }
        return $d;
    }
    
    // FIXME  - this needs to go in beforeInsert/beforeUpdate
    // should not be sending this the values..
    function initSetting($a)
    {
        if(empty($a)) {
            return;
        }
        
        $c = $this->getSetting($a['module'], $a['name']);
        if($c) {
            return;
        }
         
        
        $this->initKeys();
        
        $val = $a['val'];
        if(!isset($a['is_encrypt']) || $a['is_encrypt'] == 1) {
            $val = $this->encrypt($val);
        }
        
        $s = DB_DataObject::factory('core_setting');
        $s->setFrom(array(
            'module'        =>     $a['module'],
            'name'          =>       $a['name'],
            'description'   => $a['description'],
            'val' =>$val,
            'is_encrypt' => isset($a['is_encrypt']) ? $a['is_encrypt'] : 1
        ));
        
        $s->insert();
    }
    
    function encrypt($v)
    {
         $pub_key = file_get_contents("{$this->keyDir()}/pub.key");
        if(!$pub_key) {
            return;
        }
        openssl_public_encrypt($v, $cipher, $pub_key);
        return $cipher;
    }
}
