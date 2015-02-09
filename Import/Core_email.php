<?php



require_once 'Pman/Roo.php';

class Pman_Hydra_Import_Core_email extends Pman_Roo
{
       static $cli_desc = "Import an email into core_email template"; 
    
    static $cli_opts = array(
        'file' => array(
            'desc' => 'Turn on debugging (see DataObjects debugLevel )',
            'default' => 0,
            'short' => 'v',
            'min' => 1,
            'max' => 1,
            
        ),
        'list' => array(
            'desc' => 'List message to send, do not send them..',
            'default' => 0,
            'short' => 'l',
            'min' => 0,
            'max' => 0,
            
        ),
        'old' => array(
            'desc' => 'Show old messages.. (and new messages...)',
            'default' => 0,
            'short' => 'o',
            'min' => 0,
            'max' => 0,
            
        ),
        'force' => array(
            'desc' => 'Force redelivery, even if it has been sent before or not queued...',
            'default' => 0,
            'short' => 'f',
            'min' => 0,
            'max' => 0,
        ),
        'generate' => array(
            'desc' => 'Generate notifications for a table, eg. cash_invoice',
            'default' => '',
            'short' => 'g',
            'min' => 0,
            'max' => 1,
        ),
         'limit' => array(
            'desc' => 'Limit search for no. to send to ',
            'default' => 1000,
            'short' => 'L',
            'min' => 0,
            'max' => 999,
        ),
        'dryrun' => array(
            'desc' => 'Dry run - do not send.',
            'default' => 0,
            'short' => 'D',
            'min' => 0,
            'max' => 0,
        ),
        'poolsize' => array(
            'desc' => 'Pool size',
            'default' => 10,
            'short' => 'P',
            'min' => 0,
            'max' => 100,
        ),
    );
    
    function getAuth()
    {
        $ff = HTML_FlexyFramework::get();
        
        if (!$ff->cli) {
            die("cli only");
        }
        
    }

    function get(){
        
        $template_name = 'CORE_PERSON_SIGNUP_CONFIRM';
        $c = DB_dataObject::factory('core_email');
        $ret = $c->get('name',$template_name);
        if($ret == 0){
            $mailtext = file_get_contents('Pman.Core/templates/mail/'.$template_name.'.txt');
    
            require_once 'Mail/mimeDecode.php';  
            $decoder = new Mail_mimeDecode($mailtext);
            $parts = $decoder->getSendArray();
            $from_name = explode(" ", $parts[0])[0];
            $from_email = explode(" ", $parts[0])[1];
            $c->setFrom(array(
                'from_name'=>$from_name,
                'from_email'=>$from_email,
                'subject'=>$parts[1]['Subject'],
                'name'=>$template_name,
                'bodytext'=>$parts[2]
                ));
            $c->insert();
        }else{
            print_r("template exists.");
        }

        die("done\n");
    }
}