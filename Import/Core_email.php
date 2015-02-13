<?php



require_once 'Pman.php';

class Pman_Core_Import_Core_email extends Pman 
{
    static $cli_desc = "Import an email into core_email template"; 
    
    static $cli_opts = array(
        'file' => array(
            'desc' => 'File to import',
            'short' => 'f',
            'min' => 1,
            'max' => 1,
            
        ),
         
    );
    
    function getAuth()
    {
        $ff = HTML_FlexyFramework::get();
        
        if (!$ff->cli) {
            die("cli only");
        }
        
    }

    function get($part='', $opts){
        
        DB_DataObject::debugLevel(1);
        
        $template_name = preg_replace('/\.[a-z]+$/i', '', basename($opts['file']));
        
        if (!file_exists($opts['file'])) {
            $this->jerr("file does not exist : " . $opts['file']);
        }
         
        $c = DB_dataObject::factory('core_email');
        $ret = $c->get('name',$template_name);
        if($ret ) {
            $this->jerr("we do not support updating the files ... - especially if the user has changed them!!");
        }
        
        $mailtext = file_get_contents($opts['file']);

        require_once 'Mail/mimeDecode.php';
        require_once 'Mail/RFC822.php';
        
        $decoder = new Mail_mimeDecode($mailtext);
        $parts = $decoder->getSendArray();
        
        $headers = $parts[1];
        $from = new Mail_RFC822();
        $from_str = $from->parseAddressList($headers['From']);
        
        $from_name  = trim($from_str[0]->personal, '"');
        
        $from_email = $from_str[0]->mailbox . '@' . $from_str[0]->host;
        
        
        $c->setFrom(array(
            'from_name'     => $from_name,
            'from_email'    => $from_email,
            'subject'       => $parts[1]['Subject'],
            'name'          => $template_name,
            'bodytext'      => $parts[2]
        ));
        $c->insert();
        
        die("done\n");
    }
}