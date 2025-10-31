<?php

// should be in import folder... need to know where this is used though...

require_once 'ConvertStyle.php';

class Pman_Core_ImportMailMessage extends Pman_Core_ConvertStyle 
{
    function getAuth()
    {
        if (HTML_FlexyFramework::get()->cli) {
            return true;
        }
        $this->authUser = $this->getAuthUser();
        if (!$this->authUser) {
            return false;
        }
        return true;
    }
    
    function get($v, $opts=array())
    {
        $this->post();
        
        return $this->jerr("not allowed");
    }
    
    function post($v)
    {   
        if(isset($_REQUEST['_convertToPlain']))
        {
            require_once 'System.php';
            $tmpdir  = System::mktemp("-d convertPlain");
            $path = $tmpdir . '/' . time() . '.html';
            
            if(isset($_REQUEST['_check_unsubscribe'])){
                libxml_use_internal_errors (true);
                $doc = new DOMDocument('1.0', 'UTF-8');
                $doc->loadHTML($_REQUEST['bodytext']);
                $xpath = new DOMXpath($doc);
                foreach ($xpath->query('//a[@href]') as $a) { 
                    $href = $a->getAttribute('href');
                    
                    if(!preg_match('/^#unsubscribe/', $href)){
                        continue;
                    }
                    $a->parentNode->replaceChild($doc->createTextNode($a->nodeValue . ' {unsubscribe_link}'), $a);
                }
                
                $_REQUEST['bodytext'] = $doc->saveHTML();
                libxml_use_internal_errors (false);
            }

            // bodytext should be UTF-8 encoded
            if (!mb_check_encoding($_REQUEST['bodytext'], 'UTF-8')) {
                $_REQUEST['bodytext'] = mb_convert_encoding($_REQUEST['bodytext'], 'UTF-8', 'auto');
            }
            
            if(!file_exists($path)){
                $wrong_encoded = mb_convert_encoding($_REQUEST['bodytext'], 'ISO-8859-1', 'UTF-8');
                file_put_contents($path, $wrong_encoded); 
            //    file_put_contents($path, $_REQUEST['bodytext']); 
            }
            require_once 'File/Convert.php';
            $fc = new File_Convert($path, 'text/html');
            
            $plain = $fc->convert('text/plain');
            $this->jok(file_get_contents($plain));
        }
        
        // Import from URL
        if(isset($_REQUEST['importUrl']))
        {
            $this->checkHeader($_REQUEST['importUrl']);
            $data = $this->convertStyle($_REQUEST['importUrl'], '', true);
         
            $this->jok($data);
            
        }
     
        // Import from file
        $htmlFile = DB_DataObject::factory('images');
        $htmlFile->setFrom(array(
               'onid' => 0,
               'ontable' =>'crm_mailing_list_message'
        ));
        $htmlFile->onUpload(false);
       
        if($htmlFile->mimetype != 'text/html')
        {
            $this->jerr('accept html file only!');
        }
        if(!file_exists($htmlFile->getStoreName()))
        {
            $this->jerr('update failed!');
        }
        
        $data = $this->convertStyle('', $htmlFile->getStoreName(), false);
        
        $htmlFile->delete();
        
        unlink($htmlFile->getStoreName()) or die('Unable to delete the file');
        
        $this->jok($data);
    }
    
}