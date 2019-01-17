<?php

/**
 * parse BJS files .... 
 *
 *
 */

class Pman_Core_Bjs {
    
    var $json;
    var $fields = array();
    
    static function formFields($file)
    {
        
        $this->json = json_decode(file_get_contents($file));
    
        $this->iterateFields($this->json->items);
    }
    
    function iterateFields($ar, $res)
    {
        foreach($ar as $o) {
            
            switch ($o->xtype) {
                case "ComboBox":                
                    $res[] = $o->{'String hiddenName'};
                    // fall throught..
                    $k = isset($o->{'String name'}) ? 'String name' : 'string name';
                    
                    if (!isset($o->{$k})) {
                        break; // allowed to not exit.
                    }
                    $res[] = $o->{$k};
                    
                case "Input":
                case "TextArea":
                case "CheckBox":
                case "DateField":
                case "Radio":
                case "RadioSet":                    
                case "PhoneInput":
                case "NumberField":
                    $k = isset($o->{'String name'}) ? 'String name' : 'string name';
                    
                    if (!isset($o->{$k})) {
                        echo "missing string name";
                        print_r($o);exit;
                    }
                    $res[] = $o->{$k};
                    break;
                
                case "MoneyField":
                    $k = isset($o->{'String currencyName'}) ? 'String currencyName' : 'string currencyName';
                    
                    $res[] = $o->{$k};
                    $k = isset($o->{'String name'}) ? 'String name' : 'string name';
                    $res[] = $o->{$k};
                    break;
                default:
                    if (isset($o->items)) {
                        $res = $this->iterateFields($o->items,$res);
                    }
            }
             
        }
        
        return $res;
    }
    
    
}
