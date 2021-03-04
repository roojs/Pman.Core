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
            'min' => 0,
            'max' => 0,  
        ),
         'use-file' => array(
            'desc' => 'Force usage of file (so content is not editable in Management system)',
            'short' => 'F',
            'default' => '',
            'min' => 0,
            'max' => 0,  
        ),
        'raw_content' => array(
            'desc' => 'Raw contents of email (used by API) - not by Command line',
            'short' => 'R',
            'default' => '',
            'min' => 0,
            'max' => 0,  
        )
         
    );
    
    function getAuth()
    {
        $ff = HTML_FlexyFramework::get();
        
        if (!$ff->cli) {
            die("cli only");
        }
    }
    
    function get($part = '', $opts=array())
    {
        $this->updateOrCreateEmail($part, $opts, false);
    }

    function updateOrCreateEmail($part='', $opts, $cm = false, $mapping = false){
        
       // DB_DataObject::debugLevel(1);
        
        
        if (empty($opts['raw_content'])) {
            $template_name = preg_replace('/\.[a-z]+$/i', '', basename($opts['file']));

            if (!file_exists($opts['file'])) {
                $this->jerr("file does not exist : " . $opts['file']);
            }
            
            
            if (!empty($opts['master']) && !file_exists($opts['master'])) {
                $this->jerr("master file does not exist : " . $opts['master']);
            }
            
            
            if (empty($cm)) {
                $cm = DB_dataObject::factory('core_email');
                $ret = $cm->get('name',$template_name);
                if($ret && empty($opts['update'])) {
                    $this->jerr("use --update   to update the template..");
                }
            }
            $mailtext = file_get_contents($opts['file']);
        } else {
            $template_name = $opts['name'];
            $mailtext =  $opts['raw_content'];
        }
        
        if (!empty($opts['master'])) {
            $body = $mailtext;
            $mailtext = file_get_contents($opts['master']);
            $mailtext = str_replace('{outputBody():h}', $body, $mailtext);
        }
        
        if($mapping) {
            foreach ($mapping as $k => $v) {
                $mailtext = str_replace($k, $v, $mailtext);
            }
        }
        
        require_once 'Mail/mimeDecode.php';
        require_once 'Mail/RFC822.php';
        
        $decoder = new Mail_mimeDecode($mailtext);
        $parts = $decoder->getSendArray();
        print_R($parts);exit;
        if (is_a($parts,'PEAR_Error')) {
            echo $parts->toString() . "\n";
            exit;
        }
    
        $headers = $parts[1];
        $from = new Mail_RFC822();
        $from_str = $from->parseAddressList($headers['From']);
        if (is_a($from_str,'PEAR_Error')) {
            echo $from_str->toString() . "\n";
            exit;
        }

        
        $from_name  = trim($from_str[0]->personal, '"');
        
        $from_email = $from_str[0]->mailbox . '@' . $from_str[0]->host;
        
        
        if (!empty($opts['use-file'])) {
            $parts[2] = '';
        }
        
        
        if ($cm->id) {
            
            $cc =clone($cm);
            $cm->setFrom(array(
               'bodytext'      => !empty($opts['use-file']) ? '' : $parts[2],
               'plaintext' => 
               'updated_dt'     => date('Y-m-d H:i:s'),
               'use_file' => !empty($opts['use-file']) ? realpath($opts['file']) : '',
            ));
            
            $cm->update($cc);
        } else {
            
            $cm->setFrom(array(
                'from_name'     => $from_name,
                'from_email'    => $from_email,
                'subject'       => $headers['Subject'],
                'name'          => $template_name,
                'bodytext'      => !empty($opts['use-file']) ? '' : $parts[2],
                'updated_dt'     => date('Y-m-d H:i:s'),
                'created_dt'     => date('Y-m-d H:i:s'),
                'use_file' => !empty($opts['use-file']) ? realpath($opts['file']) : '',
            ));
            
            $cm->insert();
        }
        return $cm;
    }
    function output() {
        die("done\n");
    }
}