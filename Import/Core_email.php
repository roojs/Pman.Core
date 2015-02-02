<?php



require_once 'Pman/Roo.php';

class Pman_Hydra_Import_Core_email extends Pman_Roo
{
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