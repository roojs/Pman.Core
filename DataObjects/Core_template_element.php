<?php
/**
 * Table Definition for core_template 
 *
 *
 * The idea here is that it contains all the strings in the templates with a language '' (empty)
 * , it then generates a matching set of strings
 * 
 */
class_exists('DB_DataObject') ? '' : require_once 'DB/DataObject.php';

class Pman_Core_DataObjects_Core_template_element  extends DB_DataObject 
{
    ###START_AUTOCODE
    /* the code below is auto generated do not remove the above tag */

    public $__table = 'core_template_element';         // table name
    public $id;                              // int(11)  not_null primary_key auto_increment
    public $template_id;                           // string(64)  not_null
    public $name;
    /* the code above is auto generated do not remove the tag below */
    ###END_AUTOCODE
    
    
    
    function applyFilters($q, $au, $roo)
    {
        if (isset($q['_core_page_id'] )) {
            if (empty($q['_core_page_id'] )) {
                $this->whereAdd('1=0');
                return;
            }
            $p = DB_DataObject::factory('core_page');
            $p->get($q['_core_page_id']);
            $this->template_id = $p->template_id;
            
            
        }
        
        
    }
    /**
     *
     *  the purpose of this is to find all the block[xxxx]
     *  and make a row for then...
     *
     * it should return all the 
     */
    
    
    function syncTemplateElement($template)
    {
        $tn = $this->tableName();
        
        $contents = file_get_contents($template->currentTemplate);
        $contentStrings = $template->contentStrings;
        
        //print_R($template->contentStrings);
        
        // this should also look for <flexy:use\s+content="\{block\[([A-Za-z0-9_]+)\]^+"\s" (default_content) </flexy:use>
        // if it finds this
        
        // -> make sure there is a reference to element in core_template_element...
        // -> System PAGE and elements???? with that text as the contents..
        $core_page_strings = array();
        
        $matches = array();
        if (preg_match_all('/flexy\:content=\"(\{block\[([A-Za-z0-9_-]+)\]\.([A-Za-z0-9_]+)([^"]*))"/',
            $contents,
            $matches
        )) {
            // print_R($matches);
            // remove the full match..
            // so that the match string matches the old format.
            // the match[0] will be the one them matches contentStrings
            array_shift($matches);
            foreach($matches[0] as $i=>$k) {
                if (!isset($contentStrings[$k])) {
                    continue;
                }
                //print_R($matches);
                if (!isset($core_page_strings[ $matches[1][$i] ])) {
                    $core_page_strings[ $matches[1][$i] ] = array();
                }
                $core_page_strings[ $matches[1][$i] ][ $matches[2][$i] ] = trim($contentStrings[$k]);
//                print_r(trim($contentStrings[$k]));
            }
        }
        
        
        
        $old_matches = array();
        if (preg_match_all('#\{block\[([A-Za-z0-9_-]+)\]\.#',
                $contents,
                $old_matches
        )) {
            
            // pushes old matches onto new ones..
            foreach($old_matches[0] as $i =>$v) {
                $matches[0][] = $v;
                $matches[1][] = $old_matches[1][$i];
                
                if (!isset($core_page_strings[ $matches[1][$i] ])) {
                    $core_page_strings[ $matches[1][$i] ] = array(
                        'title' => trim($matches[1][$i]),
                        'body' => 'Fill in text here'
                        
                    );
                    
                }
//                print_r(trim($matches[1][$i]));
            }
        }
        //print_r($core_page_strings);
        // why delete the template???
        
        // if (empty($matches[0]) && empty($old_matches[0])) {
        //     $this->query("
        //         DELETE FROM {$tn} WHERE template_id = {$template->id}
        //     ");
        //     return;
        // }
         
         
          
        /// ---- USE THE SAME CODE - 
        
        
        $elements = array_unique($matches[1]);
        //print_r($elements);
        
        $ret = array();
        
        $t = DB_DataObject::Factory($tn);
        $t->template_id = $template->id;
        $base = clone($t);
        $old = $t->fetchAll('name', 'id');
        
        foreach($elements as $el) {
            
            if (!isset($old[$el])) {
                $t = clone($base);
                $t->name = $el;
                $t->content_strings = isset($core_page_strings[$el]) ? $core_page_strings[$el] : array();
                
                $t->insert();
                $ret[] = clone($t);
            } else {
                $t =DB_DataObject::Factory($tn);
                $t->get($old[$el]);
                $t->content_strings = isset($core_page_strings[$el]) ? $core_page_strings[$el] : array();
                unset($old[$el]);
                $ret[] = clone($t);
                // got element already.. ignore it..
                
            }
            // add
            
        }
        
        // delete elements, and  pages pointing to this element.. --- sounds about right..
        
        foreach($old as $n=>$id) {
            $t = DB_DataObject::Factory($tn);
            $t->get($id);
            $t->delete();
            // de'reference the core_pages that refered to it..
            $core = DB_DataObject::factory('core_page');
            $core->query("UPDATE core_page set is_system_page = 0, element_id= 0 WHERE element_id = {$t->id}");
            
        }
        
        return $ret;
        
    }
    
    function syncTemplateFromPage($pgdata)
    {
        //print_r($pgdata);
        if (empty($pgdata['page'])) {
            return false;
        }
        $element_id = DB_DataObject::factory('core_enum')->lookup('core_page_type', 'element'); 

        $core = DB_DataObject::factory('core_page');
        //DB_DataObject::DebugLevel(1);
        $core->setFrom(array(
                'page_type_id'  => $element_id,
                'page_link' => $this->name,
                'parent_id' => $pgdata['page']->id,
                'is_element' => 1,
                'element_id' => $this->id,
                'language' => 'en',
                'is_system_page' => 1
        ));
        if ($core->count()) {
            $core->fetch();
            $this->page = clone($core);
            return;
        }
        
        
        $core = DB_DataObject::factory('core_page');
        $core->title = $this->name; /// placeholder..
         
       // from parsing earlier..
        
        //print_R($this->content_strings);
        
        // allow for bodyToDisplayHTML... etc..
        foreach( array('title', 'body', 'extended') as $prop) {
            if (isset($this->content_strings[$prop])) {
                continue;
            }
            foreach($this->content_strings as $k=>$v) {
                if (substr($k,0,strlen($prop)) == $prop) {
                    $this->content_strings[$prop] = $v;
                }
                
            }
        }
        $core->setFrom($this->content_strings); 
        
        $core->setFrom(array(
                'page_type_id'  => $element_id,
                'page_link' => $this->name,
                'parent_id' => $pgdata['page']->id,
                'is_element' => 1,
                'element_id' => $this->id,
                'language' => 'en',
                'is_system_page' => 1
        ));
        $core->insert();    
        
        $this->page = clone($core);
    }
    
    
}
    