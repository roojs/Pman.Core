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
        'master' => array(
            'desc' => 'Master template (wrapper to body)',
            'short' => 'm',
            'default' => '',
            'min' => 1,
            'max' => 1,
            
        ),
        'update' => array(
            'desc' => 'Update template (deletes old version?)',
            'short' => 'u',
            'default' => '',
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
    
    function get($part = '', $opts=array()) {
        $this->updateOrCreateEmail($part, $opts, false);
    }

    function updateOrCreateEmail($part='', $opts, $cm = false){
        
       // DB_DataObject::debugLevel(1);
        
        $template_name = preg_replace('/\.[a-z]+$/i', '', basename($opts['file']));
        
        if (!file_exists($opts['file'])) {
            print_r($opts['file']);exit;
            $this->jerr("file does not exist : " . $opts['file']);
        }
        
        
        if (!empty($opts['master']) && !file_exists($opts['master'])) {
            $this->jerr("master file does not exist : " . $opts['master']);
        }
        
        
        if (empty($cm)) {
            $cm = DB_dataObject::factory('core_email');
            $ret = $cm->get('name',$template_name);
            if($ret && empty($opts['update'])) {
                $this->jerr("use --update 1 to update the template..");
            }
        }
        
        $mailtext = file_get_contents($opts['file']);
        
        if (!empty($opts['master'])) {
            $body = $mailtext;
            $mailtext = file_get_contents($opts['master']);
            $mailtext = str_replace('{outputBody():h}', $body, $mailtext);
        }
        
        require_once 'Mail/mimeDecode.php';
        require_once 'Mail/RFC822.php';
        
        $decoder = new Mail_mimeDecode($mailtext);
        $parts = $decoder->getSendArray();
        if (is_a($parts,'PEAR_Error')) {
            echo $parts->toString() . "\n";
            exit;
        }
        
        $headers = $parts[1];
        $from = new Mail_RFC822();
        $from_str = $from->parseAddressList($headers['From']);
        
        $from_name  = trim($from_str[0]->personal, '"');
        
        $from_email = $from_str[0]->mailbox . '@' . $from_str[0]->host;
        
     
        
        
        if ($cm->id) {
            
            $cc =clone($cm);
            $cm->setFrom(array(
               'bodytext'      => $parts[2],
               'updated_dt'     => date('Y-m-d H:i:s'),   
            ));
            
            $cm->update($cc);
        } else {
            
            $cm->setFrom(array(
                'from_name'     => $from_name,
                'from_email'    => $from_email,
                'subject'       => $headers['Subject'],
                'name'          => $template_name,
                'bodytext'      => $parts[2],
                'updated_dt'     => date('Y-m-d H:i:s'),
                'created_dt'     => date('Y-m-d H:i:s'),
            ));
            
            $cm->insert();
        }
        return $cm;
    }
    function output() {
        die("done\n");
    }
}