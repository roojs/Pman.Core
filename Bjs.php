<?php

/**
 * parse BJS files .... 
 *
 * currenly only extracts $this->fields from the list..
 */

class Pman_Core_Bjs {
    
    var $json;
    var $fields = array();
    
    function __construct($file)
    {
        
        $this->json = json_decode(file_get_contents($file));
        $this->iterateFields($this->json->items);
    }
    
    function iterateFields($ar)
    {
        foreach($ar as $o) {
            
            switch ($o->xtype) {
                case "ComboBox":                
                    $this->fields[] = $o->{'String hiddenName'};
                    // fall throught..
                    $k = isset($o->{'String name'}) ? 'String name' : 'string name';
                    
                    if (!isset($o->{$k})) {
                        break; // allowed to not exit.
                    }
                    $this->fields[] = $o->{$k};
                    
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
                    $this->fields[] = $o->{$k};
                    break;
                
                case "MoneyField":
                    $k = isset($o->{'String currencyName'}) ? 'String currencyName' : 'string currencyName';
                    
                    $this->fields[] = $o->{$k};
                    $k = isset($o->{'String name'}) ? 'String name' : 'string name';
                    $this->fields[] = $o->{$k};
                    break;
                default:
                    if (isset($o->items)) {
                        $this->fields = $this->iterateFields($o->items);
                    }
            }
             
        }
        
    }
    
    
}
