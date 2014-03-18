<?php
/**
 * Table Definition for Person
 */
require_once 'DB/DataObject.php';


class Pman_Core_DataObjects_Person extends DB_DataObject 
{
    ###START_AUTOCODE
    /* the code below is auto generated do not remove the above tag */

    public $__table = 'Person';                          // table name
    public $id;                              // int(11)  not_null primary_key auto_increment
    public $email;                           // string(128)  not_null
    public $alt_email;
    
    public $company_id;                      // int(11)  
    public $office_id;                       // int(11)  
    public $name;                            // string(128)  not_null
    public $firstname;                            // string(128)  not_null
    public $lastname;                            // string(128)  not_null
    public $phone;                           // string(32)  not_null
    public $fax;                             // string(32)  not_null
    
    public $role;                            // string(32)  not_null
    public $remarks;                         // blob(65535)  not_null blob
    public $passwd;                          // string(64)  not_null
    public $owner_id;                        // int(11)  not_null
    public $lang;                            // string(8)  
    public $no_reset_sent;                   // int(11)  
    public $action_type;                     // string(32)  
    public $project_id;                      // int(11)

    
    public $active;                          // int(11)  not_null
    public $deleted_by;                      // int(11)  not_null
    public $deleted_dt;                      // datetime(19)  binary


    public $name_facebook; // VARCHAR(128) NULL;
    public $url_blog; // VARCHAR(256) NULL ;
    public $url_twitter; // VARCHAR(256) NULL ;
    public $url_linkedin; // VARCHAR(256) NULL ;
    
    public $phone_mobile; // varchar(32)  NOT NULL  DEFAULT '';
    public $phone_direct; // varchar(32)  NOT NULL  DEFAULT '';
    public $countries; // VARCHAR(128) NULL;
    
    /* the code above is auto generated do not remove the tag below */
    ###END_AUTOCODE
    
    function owner()
    {
        $p = DB_DataObject::Factory('Person');
        $p->get($this->owner_id);
        return $p;
    }
    
    /**
     *
     *
     *
     *
     *  FIXME !!!! -- USE Pman_Core_Mailer !!!!!
     *
     *
     *
     *  
     */
    function buildMail($templateFile, $args)
    {
          
        $args = (array) $args;
        $content  = clone($this);
        
        foreach((array)$args as $k=>$v) {
            $content->$k = $v;
        }
        
        $ff = HTML_FlexyFramework::get();
        
        
        //?? is this really the place for this???
        if (
                !$ff->cli && 
                empty($args['no_auth']) &&
                !in_array($templateFile, array(
                    // templates that can be sent without authentication.
                     'password_reset' ,
                     'password_welcome'
                 ))
            ) {
            
            $content->authUser = $this->getAuthUser();
            if (!$content->authUser) {
                return PEAR::raiseError("Not authenticated");
            }
        }
        
        // should handle x-forwarded...
        
        $content->HTTP_HOST = isset($_SERVER["HTTP_HOST"]) ?
            $_SERVER["HTTP_HOST"] :
            (isset($ff->HTTP_HOST) ? $ff->HTTP_HOST : 'localhost');
            
        /* use the regex compiler, as it doesnt parse <tags */
        
        $tops = array(
            'compiler'    => 'Flexy',
            'nonHTML' => true,
            'filters' => array('SimpleTags','Mail'),
            //     'debug'=>1,
        );
        
        
        
        if (!empty($args['templateDir'])) {
            $tops['templateDir'] = $args['templateDir'];
        }
        
        
        
        require_once 'HTML/Template/Flexy.php';
        $template = new HTML_Template_Flexy( $tops );
        $template->compile("mail/$templateFile.txt");
        
        /* use variables from this object to ouput data. */
        $mailtext = $template->bufferedOutputObject($content);
        
        $htmlbody = false;
        // if a html file with the same name exists, use that as the body
        // I've no idea where this code went, it was here before..
        if (false !== $template->resolvePath ( "mail/$templateFile.html" )) {
            $tops['nonHTML'] = false;
            $template = new HTML_Template_Flexy( $tops );
            $template->compile("mail/$templateFile.html");
            $htmlbody = $template->bufferedOutputObject($content);
            
        }
        
        
        
        //echo "<PRE>";print_R($mailtext);
        //print_R($mailtext);exit;
        /* With the output try and send an email, using a few tricks in Mail_MimeDecode. */
        require_once 'Mail/mimeDecode.php';
        require_once 'Mail.php';
        
        $decoder = new Mail_mimeDecode($mailtext);
        $parts = $decoder->getSendArray();
        
        if (PEAR::isError($parts)) {
            return $parts;
            //echo "PROBLEM: {$parts->message}";
            //exit;
        } 
        list($recipents,$headers,$body) = $parts;
        $recipents = array($this->email);
        if (!empty($content->bcc) && is_array($content->bcc)) {
            $recipents =array_merge($recipents, $content->bcc);
        }
        $headers['Date'] = date('r');
        
        if ($htmlbody !== false) {
            require_once 'Mail/mime.php';
            $mime = new Mail_mime(array('eol' => "\n"));
            $mime->setTXTBody($body);
            $mime->setHTMLBody($htmlbody);
            // I think there might be code in mediaoutreach toEmail somewhere
            // h embeds images here..
            $body = $mime->get();
            $headers = $mime->headers($headers);
            
        }
        
         
        
        return array(
            'recipients' => $recipents,
            'headers'    => $headers,
            'body'      => $body
        );
        
        
    }
    
    
    /**
     * send a template
     * - user must be authenticate or args[no_auth] = true
     *   or template = password_[reset|welcome]
     * 
     */
    function sendTemplate($templateFile, $args)
    {
        
        $ar = $this->buildMail($templateFile, $args);
      
        
        //print_r($recipents);exit;
        $mailOptions = PEAR::getStaticProperty('Mail','options');
        $mail = Mail::factory("SMTP",$mailOptions);
        
        if (PEAR::isError($mail)) {
            return $mail;
        } 
        $oe = error_reporting(E_ALL & ~E_NOTICE & ~E_STRICT);
        $ret = $mail->send($ar['recipients'],$ar['headers'],$ar['body']);
        error_reporting($oe);
       
        return $ret;
    
    }
    
  
    
    
    function getEmailFrom()
    {
        if (empty($this->name)) {
            return $this->email;
        }
        return '"' . addslashes($this->name) . '" <' . $this->email . '>';
    }
    function toEventString() 
    {
        return empty($this->name) ? $this->email : $this->name;
    } 
    function verifyAuth()
    { 
        $ff= HTML_FlexyFramework::get();
        if (!empty($ff->Pman['auth_comptype']) &&
            (!$this->company_id || ($ff->Pman['auth_comptype'] != $this->company()->comptype))
           ){
            
            // force a logout - without a check on the isAuth - as this is called from there..
            $db = $this->getDatabaseConnection();
            $sesPrefix = $ff->appNameShort .'-'.get_class($this) .'-'.$db->dsn['database'] ;
            $_SESSION[__CLASS__][$sesPrefix .'-auth'] = "";
            return false;
            
            $ff->page->jerr("Login not permited to outside companies");
        }
        return true;
        
    }    
   
   
    //   ---------------- authentication / passwords and keys stuff  ----------------
    function isAuth()
    {
        $db = $this->getDatabaseConnection();
        // we combine db + project names,
        // otherwise if projects use different 'auth' objects
        // then we get unserialize issues.
        $ff= HTML_FlexyFramework::get();
        $sesPrefix = $ff->appNameShort .'-' .get_class($this) .'-'.$db->dsn['database'] ;
        
        
        @session_start();
         
        if (!empty($_SESSION[__CLASS__][$sesPrefix .'-auth'])) {
            // in session...
            $a = unserialize($_SESSION[__CLASS__][$sesPrefix .'-auth']);
            
            $u = DB_DataObject::factory('Person');
            if ($u->get($a->id)) { //&& strlen($u->passwd)) {
              
                return $u->verifyAuth();
                
   
                return true;
            }
            
            unset($_SESSION[__CLASS__][$sesPrefix .'-auth']);
            
        }
        // local auth - 
        $default_admin = false;
        if (!empty($ff->Pman['local_autoauth']) && 
            ($ff->Pman['local_autoauth'] === true) &&
            (!empty($_SERVER['SERVER_ADDR'])) &&
            ($_SERVER['SERVER_ADDR'] == '127.0.0.1') &&
            ($_SERVER['REMOTE_ADDR'] == '127.0.0.1')
        ) {
            $group = DB_DataObject::factory('Groups');
            $group->get('name', 'Administrators');
            
            $member = DB_DataObject::factory('group_members');
            $member->autoJoin();
            $member->group_id = $group->id;
            $member->whereAdd("
                join_user_id_id.id IS NOT NULL
            ");
            if($member->find(true)){
                $default_admin = DB_DataObject::factory('Person');
                if(!$default_admin->get($member->user_id)){
                    $default_admin = false;
                }
            }
        }
        
         
        $u = DB_DataObject::factory('Person');
        $ff = HTML_FlexyFramework::get();
        if (!empty($ff->Pman['local_autoauth']) && 
            (!empty($_SERVER['SERVER_ADDR'])) &&
            ($_SERVER['SERVER_ADDR'] == '127.0.0.1') &&
            ($_SERVER['REMOTE_ADDR'] == '127.0.0.1') &&
            ($default_admin ||  $u->get('email', $ff->Pman['local_autoauth']))
        ) {
            $_SESSION[__CLASS__][$sesPrefix .'-auth'] = serialize($default_admin ? $default_admin : $u);
            return true;
        }
           
        // http basic auth..
        $u = DB_DataObject::factory('Person');

        if (!empty($_SERVER['PHP_AUTH_USER']) 
            &&
            !empty($_SERVER['PHP_AUTH_PW'])
            &&
            $u->get('email', $_SERVER['PHP_AUTH_USER'])
            &&
            $u->checkPassword($_SERVER['PHP_AUTH_PW'])
           ) {
            $_SESSION[__CLASS__][$sesPrefix .'-auth'] = serialize($u);
            return true; 
        }
        //var_dump(session_id());
        //var_dump($_SESSION[__CLASS__]);
        
        //if (!empty(   $_SESSION[__CLASS__][$sesPrefix .'-empty'] )) {
        //    return false;
        //}
        //die("got this far?");
        // not in session or not matched...
        $u = DB_DataObject::factory('Person');
        $u->whereAdd(' LENGTH(passwd) > 0');
        $n = $u->count();
        $_SESSION[__CLASS__][$sesPrefix .'-empty']  = $n;
        $error =  PEAR::getStaticProperty('DB_DataObject','lastError');
        if ($error) {
            die($error->toString()); // not really a good thing to do...
        }
        if (!$n){ // authenticated as there are no users in the system...
            return true;
        }
        
        return false;
        
    }
    function getAuthUser()
    {
        if (!$this->isAuth()) {
            return false;
        }
        $db = $this->getDatabaseConnection();
        
        $ff= HTML_FlexyFramework::get();
        $sesPrefix = $ff->appNameShort .'-' .get_class($this) .'-'.$db->dsn['database'] ;

        
        
        if (!empty($_SESSION[__CLASS__][$sesPrefix .'-auth'])) {
            $a = unserialize($_SESSION[__CLASS__][$sesPrefix .'-auth']);
            
            $u = DB_DataObject::factory('Person');
            if ($u->get($a->id)) { /// && strlen($u->passwd)) {
                return clone($u);
            }
            unset($_SESSION[__CLASS__][$sesPrefix .'-auth']);
        }
        
        if (empty(   $_SESSION[__CLASS__][$sesPrefix .'-empty'] )) {
            $u = DB_DataObject::factory('Person');
            $u->whereAdd(' LENGTH(passwd) > 0');
            $_SESSION[__CLASS__][$sesPrefix .'-empty']  = $u->count();
        }
                
             
        if (isset(   $_SESSION[__CLASS__][$sesPrefix .'-empty'] ) && $_SESSION[__CLASS__][$sesPrefix .'-empty']  < 1) {
            
            // fake person - open system..
            //$ce = DB_DataObject::factory('core_enum');
            //$ce->initEnums();
            
            
            $u = DB_DataObject::factory('Person');
            $u->id = -1;
            
            // if a company has been created fill that in in company_id_id
            $c = DB_DAtaObject::factory('Companies')->lookupOwner();
            if ($c) {
                $u->company_id_id = $c->pid();
                $u->company_id = $c->pid();
            }
            
            return $u;
            
        }
        return false;
    }     
    function login()
    {
        $this->isAuth(); // force session start..
        if (!$this->verifyAuth()) {
            return false;
        }
        $db = $this->getDatabaseConnection();
        
        
        // open up iptables at login..
        $dbname = $this->database();
        touch( '/tmp/run_pman_admin_iptables-'.$dbname);
         
        // refresh admin group if we are logged in as one..
        //DB_DataObject::debugLevel(1);
        $g = DB_DataObject::factory('Groups');
        $g->type = 0;
        $g->get('name', 'Administrators');
        $gm = DB_DataObject::Factory('group_members');
        if (in_array($g->id,$gm->listGroupMembership($this))) {
            // refresh admin groups.
            $gr = DB_DataObject::Factory('group_rights');
            $gr->applyDefs($g, 0);
        }
        $ff= HTML_FlexyFramework::get();
        $sesPrefix = $ff->appNameShort .'-' .get_class($this) .'-'.$db->dsn['database'] ;


        $_SESSION[__CLASS__][$sesPrefix .'-auth'] = serialize($this);
        
    }
    function logout()
    {
        $this->isAuth(); // force session start..
        $db = $this->getDatabaseConnection();
        $ff= HTML_FlexyFramework::get();
        $sesPrefix = $ff->appNameShort .'-' .get_class($this) .'-'.$db->dsn['database'] ;

        $_SESSION[__CLASS__][$sesPrefix .'-auth'] = "";
       
        
       
        
    }    
    function genPassKey ($t) 
    {
        return md5($this->email . $t. $this->passwd);
    }
    function simpleAuthKey($m = 0)
    {
        $month = $m > -1 ? date('Y-m') : date('Y-m', strtotime('LAST MONTH'));
        
        return md5(implode(',' ,  array($month, $this->email , $this->passwd, $this->id)));
    } 
    function checkPassword($val)
    {
        
        if (substr($this->passwd,0,1) == '$') {
            return crypt($val,$this->passwd) == $this->passwd ;
        }
        // old style md5 passwords...- cant be used with courier....
        return md5($val) == $this->passwd;
    }
    function setPassword($value) 
    {
        $salt='';
        while(strlen($salt)<9) {
            $salt.=chr(rand(64,126));
            //php -r var_dump(crypt('testpassword', '$1$'. (rand(64,126)). '$'));
        }
        $this->passwd = crypt($value, '$1$'. $salt. '$');
       
       
    }      
    
    function generatePassword() // genearte a password (add set 'rawPasswd' to it's value)
    {
        require_once 'Text/Password.php';
        $this->rawPasswd = strtr(ucfirst(Text_Password::create(5)).ucfirst(Text_Password::create(5)), array(
        "a"=>"4", "e"=>"3",  "i"=>"1",  "o"=>"0", "s"=>"5",  "t"=>"7"));
        $this->setPassword($this->rawPasswd);
        return $this->rawPasswd;
    }
    
    function company()
    {
        $x = DB_DataObject::factory('Companies');
        $x->autoJoin();
        $x->get($this->company_id);
        return $x;
    }
    function loadCompany()
    {
        $this->company = $this->company();
    }
    
    function active()
    { 
        return $this->active;
    }
    function authUserName($n) // set username prior to acheck user exists query.
    {
        
        $this->whereAdd('LENGTH(passwd) > 1'); 
        $this->email = $n;
    }
    function lang()
    {
        if (!func_num_args()) {
            return $this->lang;
        }
        $val = array_shift(func_get_args());
        if ($val == $this->lang) {
            return;
        }
        $uu = clone($this);
        $this->lang = $val;
        $this->update($uu);
        return $this->lang;
    }
            
    
    function authUserArray()
    {
        
        $aur = $this->toArray();
        
        if ($this->id < 1) {
            return $aur;
        }
        
        
        //DB_DataObject::debugLevel(1);
        $c = DB_Dataobject::factory('Companies');
        $im = DB_Dataobject::factory('Images');
        $c->joinAdd($im, 'LEFT');
        $c->selectAdd();
        $c->selectAs($c, 'company_id_%s');
        $c->selectAs($im, 'company_id_logo_id_%s');
        $c->id = $this->company_id;
        $c->limit(1);
        $c->find(true);
        
        $aur = array_merge( $c->toArray(),$aur);
        
        if (empty($c->company_id_logo_id_id))  {
                 
            $im = DB_Dataobject::factory('Images');
            $im->ontable = 'Companies';
            $im->onid = $c->id;
            $im->imgtype = 'LOGO';
            $im->limit(1);
            $im->selectAdd();
            $im->selectAs($im,  'company_id_logo_id_%s');
            if ($im->find(true)) {
                    
                foreach($im->toArray() as $k=>$v) {
                    $aur[$k] = $v;
                }
            }
        }
      
        // perms + groups.
        $aur['perms']  = $this->getPerms();
        $g = DB_DataObject::Factory('group_members');
        $aur['groups']  = $g->listGroupMembership($this, 'name');
        
        $aur['passwd'] = '';
        $aur['dailykey'] = '';
        
        
        
        return $aur;
    }
    
    //   ----------PERMS------  ----------------
    function getPerms() 
    {
         //DB_DataObject::debugLevel(1);
        // find out all the groups they are a member of.. + Default..
        
        // ------ INIITIALIZE IF NO GROUPS ARE SET UP.
        
        $g = DB_DataObject::Factory('group_rights');
        if (!$g->count()) {
            $g->genDefault();
        }
        
        if ($this->id < 0) {
            return $g->adminRights(); // system is not set up - so they get full rights.
        }
        //DB_DataObject::debugLevel(1);
        $g = DB_DataObject::Factory('group_members');
        $g->whereAdd('group_id is NOT NULL AND user_id IS NOT NULL');
        if (!$g->count()) {
            // add the current user to the admin group..
            $g = DB_DataObject::Factory('Groups');
            if ($g->get('name', 'Administrators')) {
                $gm = DB_DataObject::Factory('group_members');
                $gm->group_id = $g->id;
                $gm->user_id = $this->id;
                $gm->insert();
            }
            
        }
        
        // ------ STANDARD PERMISSION HANDLING.
        $isOwner = $this->company()->comptype == 'OWNER';
        $g = DB_DataObject::Factory('group_members');
        $grps = $g->listGroupMembership($this);
       //var_dump($grps);
        $isAdmin = $g->inAdmin;
        //echo '<PRE>'; print_r($grps);var_dump($isAdmin);
        // the load all the perms for those groups, and add them all together..
        // then load all those 
        $g = DB_DataObject::Factory('group_rights');
        $ret =  $g->listPermsFromGroupIds($grps, $isAdmin, $isOwner);
        //echo '<PRE>';print_r($ret);
        return $ret;
         
        
    }
    /**
     *Basic group fetching - probably needs to filter by type eventually.
     *
     *@param String $what - fetchall() argument - eg. 'name' returns names of all groups that they are members of.
     */
    
    function groups($what=false)
    {
        $g = DB_DataObject::Factory('group_members');
        $grps = $g->listGroupMembership($this);
        $g = DB_DataObject::Factory('Groups');
        $g->whereAddIn('id', $grps, 'int');
        return $g->fetchAll($what);
        
    }
    
    
    
    function hasPerm($name, $lvl) 
    {
        static $pcache = array();
        
        if (!isset($pcache[$this->id])) {
            $pcache[$this->id] = $this->getPerms();
        }
       // echo "<PRE>";print_r($pcache[$au->id]);
       // var_dump($pcache[$au->id]);
        if (empty($pcache[$this->id][$name])) {
            return false;
        }
        
        return strpos($pcache[$this->id][$name], $lvl) > -1;
        
    }    
    
    //  ------------ROO HOOKS------------------------------------
    function applyFilters($q, $au, $roo)
    {
        //DB_DataObject::DebugLevel(1);
        if (!empty($q['query']['person_not_internal'])) {
            $this->whereAdd(" join_company_id_id.isOwner = 0 ");
        }
        
        if (!empty($q['query']['person_internal_only_all'])) {
            
            
            // must be internal and not current user (need for distribution list)
            // user has a projectdirectory entry and role is not blank.
            //DB_DataObject::DebugLevel(1);
            $pd = DB_DataObject::factory('ProjectDirectory');
            $pd->whereAdd("role != ''");
            $pd->selectAdd();
            $pd->selectAdd('distinct(person_id) as person_id');
            $roled = $pd->fetchAll('person_id');
            $rs = $roled  ? "  OR
                    {$this->tableName()}.id IN (".implode(',', $roled) . ") 
                    " : '';
            $this->whereAdd(" join_company_id_id.comptype = 'OWNER' $rs ");
            
        }
        // -- for distribution
        if (!empty($q['query']['person_internal_only'])) {
            // must be internal and not current user (need for distribution list)
            $this->whereAdd(" join_company_id_id.comptype = 'OWNER'");
            
            //$this->whereAdd(($this->tableName() == 'Person' ? 'Person' : "join_person_id_id") .
            //    ".id  != ".$au->id);
            $this->whereAdd("Person.id != {$au->id}");
        } 
        
        if (!empty($q['query']['comptype_or_company_id'])) {
           // DB_DataObject::debugLevel(1);
            $bits = explode(',', $q['query']['comptype_or_company_id']);
            $id = (int) array_pop($bits);
            $ct = $this->escape($bits[0]);
            
            $this->whereAdd(" join_company_id_id.comptype = '$ct' OR Person.company_id = $id");
            
        }
        
        
        // staff list..
        if (!empty($q['query']['person_inactive'])) {
           // DB_Dataobject::debugLevel(1);
            $this->active = 1;
        }
        $tn_p = $this->tableName();
        $tn_gm = DB_DataObject::Factory('group_members')->tableName();
        $tn_g = DB_DataObject::Factory('Groups')->tableName();

        ///---------------- Group views --------
        if (!empty($q['query']['in_group'])) {
            // DB_DataObject::debugLevel(1);
            $ing = (int) $q['query']['in_group'];
            if ($q['query']['in_group'] == -1) {
             
                // list all staff who are not in a group.
                $this->whereAdd("Person.id NOT IN (
                    SELECT distinct(user_id) FROM $tn_gm LEFT JOIN
                        $tn_g ON $tn_g.id = $tn_gm.group_id
                        WHERE $tn_g.type = ".$q['query']['type']."
                    )");
                
                
            } else {
                
                $this->whereAdd("$tn_p.id IN (
                    SELECT distinct(user_id) FROM $tn_gm
                        WHERE group_id = $ing
                    )");
               }
            
        }
        
        // #2307 Search Country!!
        if (!empty($q['query']['in_country'])) {
            // DB_DataObject::debugLevel(1);
            $inc = $q['query']['in_country'];
            $this->whereAdd("$tn_p.countries LIKE '%{$inc}%'");
        }
        
        if (!empty($q['query']['not_in_directory'])) { 
            // it's a Person list..
            // DB_DATaobjecT::debugLevel(1);
            
            // specific to project directory which is single comp. login
            //
            $owncomp = DB_DataObject::Factory('Companies');
            $owncomp->get('comptype', 'OWNER');
            if ($q['company_id'] == $owncomp->id) {
                $this->active =1;
            }
            
            

            if ( $q['query']['not_in_directory'] > -1) {
                $tn_pd = DB_DataObject::Factory('ProjectDirectory')->tableName();
                // can list current - so that it does not break!!!
                $this->whereAdd("$tn_p.id NOT IN 
                    ( SELECT distinct person_id FROM $tn_pd WHERE
                        project_id = " . $q['query']['not_in_directory'] . " AND 
                        company_id = " . $this->company_id . ')');
            }
        }
           
        if (!empty($q['query']['role'])) { 
            // it's a Person list..
            // DB_DATaobjecT::debugLevel(1);
            
            // specific to project directory which is single comp. login
            //
            $tn_pd = DB_DataObject::Factory('ProjectDirectory')->tableName();
                // can list current - so that it does not break!!!
            $this->whereAdd("$tn_p.id IN 
                    ( SELECT distinct person_id FROM $tn_pd WHERE
                        role = '". $this->escape($q['query']['role']) ."'
            )");
        
        }
        
        
        if (!empty($q['query']['project_member_of'])) {
               // this is also a flag to return if they are a member..
            //DB_DataObject::debugLevel(1);
            $do = DB_DataObject::factory('ProjectDirectory');
            $do->project_id = $q['query']['project_member_of'];
            $tn_pd = DB_DataObject::Factory('ProjectDirectory')->tableName();
            $this->joinAdd($do,array('joinType' => 'LEFT', 'useWhereAsOn' => true));
            $this->selectAdd("IF($tn_pd.id IS NULL, 0,  $tn_pd.id )  as is_member");
                
                
            if (!empty($q['query']['project_member_filter'])) {
                $this->having('is_member !=0');
            
            }
            
        }
        
        if(!empty($q['query']['name'])){
            $this->whereAdd("
                Person.name LIKE '%{$this->escape($q['query']['name'])}%'
            ");
        }
        
        if (!empty($q['query']['search'])) {
            
            // use our magic search builder...
            
             require_once 'Text/SearchParser.php';
            $x = new Text_SearchParser($q['query']['search']);
            
            $props = array(
                    "$tn_p.name",
                    "$tn_p.email",
                    "$tn_p.role",
                    "$tn_p.phone",
                    "$tn_p.remarks",
                    "join_company_id_id.name"
            );
            
            $str =  $x->toSQL(array(
                'default' => $props,
                'map' => array(
                    'company' => 'join_company_id_id.name',
                    //'country' => 'Clipping.country',
                    //  'media' => 'Clipping.media_name',
                ),
                'escape' => array($this->getDatabaseConnection(), 'escapeSimple'), /// pear db or mdb object..

            ));
            
            
            $this->whereAdd($str); /*
                        $tn_p.name LIKE '%$s%'  OR
                        $tn_p.email LIKE '%$s%'  OR
                        $tn_p.role LIKE '%$s%'  OR
                        $tn_p.phone LIKE '%$s%' OR
                        $tn_p.remarks LIKE '%$s%' 
                        
                    ");*/
        }
        
    }
    function setFromRoo($ar, $roo)
    {
        $this->setFrom($ar);
        if (!empty($ar['passwd1'])) {
            $this->setPassword($ar['passwd1']);
        }
        
        
        if (    $this->id &&
                ($this->email == $roo->old->email)&&
                ($this->company_id == $roo->old->company_id)
            ) {
            return true;
        }
        if (empty($this->email)) {
            return true;
        }
        $xx = DB_Dataobject::factory('Person');
        $xx->setFrom(array(
            'email' => $this->email,
           // 'company_id' => $x->company_id
        ));
        
        if ($xx->count()) {
            return "Duplicate Email found";
        }
        return true;
    }
    /**
     *
     * before Delete - delete significant dependancies..
     * this is called after checkPerm..
     */
    
    function beforeDelete()
    {
        
        $e = DB_DataObject::Factory('Events');
        $e->whereAdd('person_id = ' . $this->id);
        $e->delete(true);
        
        // anything else?  
        
    }
    
    
    /***
     * Check if the a user has access to modify this item.
     * @param String $lvl Level (eg. Core.Projects)
     * @param Pman_Core_DataObjects_Person $au The authenticated user.
     * @param boolean $changes alllow changes???
     *
     * @return false if no access..
     */
    function checkPerm($lvl, $au, $changes=false) //heck who is trying to access this. false == access denied..
    {
         
       // do we have an empty system..
        if ($au && $au->id == -1) {
            return true;
        }
        
        // determine if it's staff!!!
         
        if ($au->company()->comptype != 'OWNER') {
            
            // - can not change company!!!
            if ($changes && 
                isset($changes['company_id']) && 
                $changes['company_id'] != $au->company_id) {
                return false;
            }
            // can only set new emails..
            if ($changes && 
                    !empty($this->email) && 
                    isset($changes['email']) && 
                    $changes['email'] != $this->email) {
                return false;
            }
            
            // edit self... - what about other staff members...
            
            return $this->company_id == $au->company_id;
        }
         
         
        // yes, only owner company can mess with this...
        $owncomp = DB_DataObject::Factory('Companies');
        $owncomp->get('comptype', 'OWNER');
        
        $isStaff = ($this->company_id ==  $owncomp->id);
        
    
        switch ($lvl) {
            // extra case change passwod?
            case 'P': //??? password
                // standard perms -- for editing + if the user is dowing them selves..
                $ret = $isStaff ? $au->hasPerm("Core.Staff", "E") : $au->hasPerm("Core.Person", "E");
                return $ret || $au->id == $this->id;
            
            default:                
                return $isStaff ? $au->hasPerm("Core.Staff", $lvl) : $au->hasPerm("Core.Person", $lvl);
        
        }
        return false;
    }
    
    function beforeUpdate($old,$q,$roo)
    {
        print_r($q);
    }
    
    function onInsert($req, $roo)
    {
         
        $p = DB_DataObject::factory('person');
        if ($roo->authUser->id < 0 && $p->count() == 1) {
            // this seems a bit risky...
            
            $g = DB_DataObject::factory('Groups');
            $g->initGroups();
            
            $g->type = 0;
            $g->get('name', 'Administrators');
            
            $p = DB_DataObject::factory('group_members');
            $p->group_id = $g->id;
            $p->user_id = $this->id;     
            if (!$p->count()) {
                $p->insert();
                $roo->addEvent("ADD", $p, $g->toEventString(). " Added " . $this->toEventString());
            }
            $this->login();
        }
        if (!empty($req['project_id_addto'])) {
            $pd = DB_DataObject::factory('ProjectDirectory');
            $pd->project_id = $req['project_id_addto'];
            $pd->person_id = $this->id; 
            $pd->ispm =0;
            $pd->office_id = $this->office_id;
            $pd->company_id = $this->company_id;
            $pd->insert();
        }
        
    }
    
    function importFromArray($roo, $persons, $opts)
    {
        if (empty($opts['prefix'])) {
            $roo->jerr("opts[prefix] is empty - you can not just create passwords based on the user names");
        }
        
        if (!is_array($persons) || empty($persons)) {
            $roo->jerr("error in the person data. - empty on not valid");
        }
        DB_DataObject::factory('groups')->initGroups();
        
        foreach($persons as $person){
            $p = DB_DataObject::factory('person');
            if($p->get('name', $person['name'])){
                continue;
            }
            $p->setFrom($person);
            
            $companies = DB_DataObject::factory('companies');
            if(!$companies->get('comptype', 'OWNER')){
                $roo->jerr("Missing OWNER companies!");
            }
            $p->company_id = $companies->pid();
            // strip the 'spaces etc.. make lowercase..
            $name = strtolower(str_replace(' ', '', $person['name']));
            $p->setPassword("{$opts['prefix']}{$name}");
            $p->insert();
            // set up groups
            // if $person->groups is set.. then
            // add this person to that group eg. groups : [ 'Administrator' ] 
            if(!empty($person['groups'])){
                $groups = DB_DataObject::factory('groups');
                if(!$groups->get('name', $person['groups'])){
                    $roo->jerr("Missing groups : {$person['groups']}");
                }
                $gm = DB_DataObject::factory('group_members');
                $gm->change($p, $groups, true);
            }
            
            $p->onInsert(array(), $roo);
        }
    }
 }
