<?php

class_exists('DB_DataObject') ? '' : require_once 'DB/DataObject.php';

class Pman_Core_DataObjects_Core_setting extends DB_DataObject
{
    public $__table = 'core_setting';
    
    function initKeys()
    {
        // return false when fail
        $dir = $this->getKeyDirectory();
        
        if(!$dir) {
            return false; // only fail case?
        }
        
        if(
            file_exists("{$dir}/pub.key") ||
            file_exists("{$dir}/pri.key")
        ){
            return true;
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
        
        return true;
    }
    
    function lookup($m,$n)
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
    }
    
    function getKeyDirectory()
    {
        $client_dir = HTML_FlexyFramework::get()->Pman['storedir'];
        
        $key_dir = $client_dir.'/keys';
        
        if(file_exists($key_dir)) {
            return $key_dir;
        }
        
        if(!is_writable($key_dir)) {
            return false;
        }
        
        mkdir($key_dir, 0755, true);
        
        return $key_dir;
    }
    
    // FIXME  - this needs to go in beforeInsert/beforeUpdate
    // should not be sending this the values..
    function initSetting($a)
    {
        if(empty($a)) {
            return;
        }
        
        $this->initKeys();
        
        $c = $this->lookup($a['module'], $a['name']);
        
        $o = $c ? clone($c) : false;
        
        $c = $c ? $c : DB_DataObject::factory('core_setting');
        
        $c->setFrom(array(
            'module'        =>     $a['module'],
            'name'          =>       $a['name'],
            'description'   => $a['description'],
            'val' => (!isset($a['is_encrypt']) || $a['is_encrypt'] == 1) ?
                $this->encrypt($a['val']) : $a['val'],
            'is_encrypt' => isset($a['is_encrypt']) ? $a['is_encrypt'] : 1,
            'is_valid' => 1
        ));
        
        $o ?  $c->update($o) : $c->insert();
    }
    
    //one key for encrypting all the settings
    function encrypt($v)
    {
        $key_dir = "{$this->getKeyDirectory()}/pub.key";
        
        if(!file_exists($key_dir)) {
            return false;
        }
        
        $pub_key = file_get_contents($key_dir);
        if(!$pub_key) {
            return false;
        }
        openssl_public_encrypt($v, $ciphertext, $pub_key);
        return $ciphertext;
    }
    
    function decrypt($v)
    {
        $key_dir = "{$this->getKeyDirectory()}/pri.key";
        
        if(!file_exists($key_dir)) {
            return false;
        }
        
        $pri_key = file_get_contents($key_dir);
        
        if(!$pri_key) {
            return false;
        }
        
        openssl_private_decrypt($v, $plaintext, $pri_key);
        
        return $plaintext;
    }
    
    function getDecryptVal()
    {
        $dir = $this->getKeyDirectory();
        
        if(!$dir) {
            return false;
        }
        
        if(empty($this->val)) {
            return false;
        }
        
        if(empty($this->is_encrypt)) {
            return $this->val;
        }
        
        $key_dir = "{$dir}/pri.key";
        
        if(!file_exists($key_dir)) {
            return false;
        }
        
        $pri_key = file_get_contents($key_dir);
        
        if(!$pri_key) {
            return false;
        }
        
        openssl_private_decrypt($this->val, $plaintext, $pri_key);
        
        return $plaintext;
    }
    
}
