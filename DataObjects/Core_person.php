<?php
/**
 * Table Definition for Person
 */
class_exists('DB_DataObject') ? '' : require_once 'DB/DataObject.php';


class Pman_Core_DataObjects_Core_person extends DB_DataObject 
{
    ###START_AUTOCODE
    /* the code below is auto generated do not remove the above tag */

    public $__table = 'core_person';                          // table name
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
    public $linkedin_id; // VARCHAR(256) NULL ;
    
    public $phone_mobile; // varchar(32)  NOT NULL  DEFAULT '';
    public $phone_direct; // varchar(32)  NOT NULL  DEFAULT '';
    public $countries; // VARCHAR(128) NULL;
    
    public $language;
    
    /* the code above is auto generated do not remove the tag below */
    ###END_AUTOCODE
    
    static $authUser = false;

    const BAD_EMAIL_FAILS = 10;
    
 
    function owner()
    {
        // this might be a Person in some old code? 
        $p = DB_DataObject::Factory('core_person');
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
        
        if (is_a($parts,'PEAR_Error')) {
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
        $oe = error_reporting(E_ALL & ~E_NOTICE);
        $ret = $mail->send($ar['recipients'],$ar['headers'],$ar['body']);
        error_reporting($oe);
       
        return $ret;
    }
    
  
    
    
    function getEmailFrom()
    {
        require_once 'Mail/RFC822.php';
        $rfc822 = new Mail_RFC822(array('name' => $this->name, 'address' => $this->email));
        return $rfc822->toMime();
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
            
            $sesPrefix = $this->sesPrefix();
       
            self::$authUser = false;
            $_SESSION[get_class($this)][$sesPrefix .'-auth'] = "";
            
            return false;
            
            //$ff->page->jerr("Login not permited to outside companies");
        }
        return true;
        
    }    
   
   
    //   ---------------- authentication / passwords and keys stuff  ----------------
    function isAuth()
    {
        // do not start a session if we are using http auth...
        // we have a situation where the app is behind a http access and is also login
        // need to work out a way to handle that.
        $ff= HTML_FlexyFramework::get();
        if (php_sapi_name() != "cli" && (empty($_SERVER['PHP_AUTH_USER']) || !empty($ff->disable_http_auth)))  {
            @session_start();
        }
        
         
       
        $sesPrefix = $this->sesPrefix();
        
        if (self::$authUser) {
            return self::$authUser;
        }
        
        
        if (!empty($_SESSION[get_class($this)][$sesPrefix .'-auth'])) {
            // in session...
            $a = unserialize($_SESSION[get_class($this)][$sesPrefix .'-auth']);
            $u = DB_DataObject::factory($this->tableName());
            $u->autoJoin();
            if ($a->id && $u->get($a->id)) { //&& strlen($u->passwd)) {
                if ($u->verifyAuth()) {
                    self::$authUser = $u;
                    return true;
                }
            }
            $this->_auth_error = "INVALID-USER";
            unset($_SESSION[get_class($this)][$sesPrefix .'-auth']);
            unset($_SESSION[get_class($this)][$sesPrefix .'-timeout']);
            //setcookie('Pman.timeout', -1, time() + (30*60), '/');
            return false;
        }
        // http basic auth..
        $u = DB_DataObject::factory($this->tableName());
        if (empty($ff->disable_http_auth)  // http auth requests should not have this...
            &&
            !empty($_SERVER['PHP_AUTH_USER']) 
            &&
            !empty($_SERVER['PHP_AUTH_PW'])
            &&
            $u->get('email', $_SERVER['PHP_AUTH_USER'])
            &&
            $u->checkPassword($_SERVER['PHP_AUTH_PW'])
           ) {
            // logged in via http auth
            // http auth will not need session... 
            //$_SESSION[get_class($this)][$sesPrefix .'-auth'] = serialize($u);
            self::$authUser = $u;
            return true; 
        }
        
        // at this point all http auth stuff is done, so we can init session
        
        
        //die("test init");
       
        
        $auto_auth_allow = false;
        if (!empty($ff->Pman['local_autoauth']) && $ff->Pman['local_autoauth'] === true) {
            $auto_auth_allow  = true;
        }
        if  ( !empty($ff->Pman['local_autoauth'])
             &&
                !empty($_SERVER['SERVER_ADDR']) &&
                !empty($_SERVER['REMOTE_ADDR']) &&
                (
                    (
                       $_SERVER['SERVER_ADDR'] == '127.0.0.1' &&
                       $_SERVER['REMOTE_ADDR'] == '127.0.0.1'
                   )
                   ||
                   (
                       $_SERVER['SERVER_ADDR'] == '::1' &&
                       $_SERVER['REMOTE_ADDR'] == '::1'
                   )
                )
                
            ){
            $auto_auth_allow  = true;
        }
        
        
        if (empty($_SERVER['PATH_INFO']) ||  $_SERVER['PATH_INFO'] == '/Login') {
            $auto_auth_allow  = false;
        }

        if (!$auto_auth_allow && !$this->canInitializeSystem()) {
            //  die("can not init");
              $this->_auth_error = "NO-SESSION";
              return false;
          }
          

         //var_dump($auto_auth_allow);
        // local auth - 
        $default_admin = false;
        if ($auto_auth_allow) {
            $group = DB_DataObject::factory('core_group');
            $group->get('name', 'Administrators');
            
            $member = DB_DataObject::factory('core_group_member');
            $member->autoJoin();
            $member->group_id = $group->id;
            $member->whereAdd("
                join_user_id_id.id IS NOT NULL
            ");
            if ($ff->Pman['local_autoauth'] !== true) {
                $member->whereAdd("
                    join_user_id_id.email = '" . $member->escape($ff->Pman['local_autoauth']) . "'
                ");
            }
            if($member->find(true)){
                $default_admin = DB_DataObject::factory($this->tableName());
                $default_admin->autoJoin();
                if(!$default_admin->get($member->user_id)){
                    $default_admin = false;
                }
            }
        }
        
        //var_dump($ff->Pman['local_autoauth']);         var_dump($_SERVER); exit;
        $u = DB_DataObject::factory($this->tableName());
        $u->autoJoin();
        $ff = HTML_FlexyFramework::get();
        
        if ($auto_auth_allow && 
            ($default_admin ||  $u->get('email', $ff->Pman['local_autoauth']))
        ) {
            
            $user = $default_admin ? $default_admin->toArray() : $u->toArray();
            
            // if we request other URLS.. then we get auto logged in..
            self::$authUser = $default_admin ? $default_admin : $u;;
            //$_SESSION[get_class($this)][$sesPrefix .'-auth'] = serialize((object) $user);
            return true;
        }
        
        //var_dump(session_id());
        //var_dump($_SESSION[__CLASS__]);
        
        //if (!empty(   $_SESSION[__CLASS__][$sesPrefix .'-empty'] )) {
        //    return false;
        //}
        //die("got this far?");
        // not in session or not matched...
        $u = DB_DataObject::factory($this->tableName());
        $u->whereAdd(' LENGTH(passwd) > 0');
        $n = $u->count();
        if (empty($_SESSION[get_class($this)]) || !is_array($_SESSION[get_class($this)])) { 
            $_SESSION[get_class($this)] = array();
        }
        $_SESSION[get_class($this)][$sesPrefix .'-empty']  = $n;
        if (class_exists('PEAR')) {
            $error =  PEAR::getStaticProperty('DB_DataObject','lastError');
            if ($error) {
                die($error->toString()); // not really a good thing to do...
            }
        }
        die('b');
        if (!$n){ // authenticated as there are no users in the system...
             return true;
        }
         return false;
        
    }
    
    function canInitializeSystem()
    {
        
        return !strcasecmp(get_class($this) , __CLASS__);
    }
    
    function getAuthUser()
    {
        if (!$this->isAuth()) {
            return false;
        }
        
        $ff= HTML_FlexyFramework::get();
        
        $sesPrefix = $this->sesPrefix();
        
        //var_dump(array(get_class($this),$sesPrefix .'-auth'));
       
        if (self::$authUser) {
             
            if (isset($_SESSION[get_class($this)][$sesPrefix .'-auth'])) {
                $_SESSION[get_class($this)][$sesPrefix .'-auth-timeout'] = time() + (30*60); // eg. 30 minutes
                //setcookie('Pman.timeout', time() + (30*60), time() + (30*60), '/');
            }
            // not really sure why it's cloned..
            return   clone (self::$authUser);
             
            
        }
        
        
        
        if (!$this->canInitializeSystem()) {
            return false;
        }
        
        
        
        if (empty(   $_SESSION[get_class($this)][$sesPrefix .'-empty'] )) {
            $u = DB_DataObject::factory($this->tableName());
            $u->whereAdd(' LENGTH(passwd) > 0');
            $_SESSION[get_class($this)][$sesPrefix .'-empty']  = $u->count();
        }
                
             
        if (
            isset(   $_SESSION[get_class($this)][$sesPrefix .'-empty'] ) && 
            $_SESSION[get_class($this)][$sesPrefix .'-empty']  < 1
        ) {
            
            // fake person - open system..
            //$ce = DB_DataObject::factory('core_enum');
            //$ce->initEnums();
            
            
            $u = DB_DataObject::factory($this->tableName());
            $u->id = -1;
            
            // if a company has been created fill that in in company_id_id
            $c = DB_DAtaObject::factory('core_company')->lookupOwner();
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
        if (!$this->verifyAuth()) { // check for company valid..
            return false;
        }
        
        // open up iptables at login..
        $dbname = $this->databaseNickname();
        touch( '/tmp/run_pman_admin_iptables-'.$dbname);
         
        // refresh admin group if we are logged in as one..
        //DB_DataObject::debugLevel(1);
        $g = DB_DataObject::factory('core_group');
        $g->type = 0;
        $g->get('name', 'Administrators');
        $gm = DB_DataObject::Factory('core_group_member');
        if (in_array($g->id,$gm->listGroupMembership($this))) {
            // refresh admin groups.
            $gr = DB_DataObject::Factory('core_group_right');
            $gr->applyDefs($g, 0);
        }
        
        $sesPrefix = $this->sesPrefix();
        
        // we should not store the whole data in the session - otherwise it get's huge.
        $p = DB_DAtaObject::Factory($this->tableName());
        $p->get($this->pid());
        
        $d = $p->toArray();
        
        $_SESSION[get_class($this)][$sesPrefix .'-auth-timeout'] = time() + (30*60); // eg. 30 minutes
        //setcookie('Pman.timeout', time() + (30*60), time() + (30*60), '/');
        
        //var_dump(array(get_class($this),$sesPrefix .'-auth'));
        $_SESSION[get_class($this)][$sesPrefix .'-auth'] = serialize((object)$d);
        
        $pp = DB_DAtaObject::Factory($this->tableName());
        $pp->autoJoin();
        $pp->get($this->pid());

        self::$authUser = $pp;
        // ensure it's written so that ajax calls can fetch it..
        
        
        
    }
    function logout()
    {
        $this->isAuth(); // force session start..
        
        $sesPrefix = $this->sesPrefix();
        $_SESSION[get_class($this)][$sesPrefix .'-auth-timeout'] = -1;
        $_SESSION[get_class($this)][$sesPrefix .'-auth'] = "";
        self::$authUser = false;
        
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
    /**
     * When we generate autologin urls:
     * eg. /Somesite/Test/12
     * it will generate:
     * /Somesite/Test/12/{datetime}/{sha256(url + expires_datetime + password)}
     *
     * eg. genAutoLoginURL($sub, $expires)
     */
    function genAutoLoginURL($url, $expires = false)  
    {
        $expires = $expires  === false ? strtotime("NOW + 1 WEEK") : $expires;
        //echo serialize(array($url, $expires, $this->email, $this->passwd));
        //echo hash('sha256', serialize(array($url, $expires, $this->email, $this->passwd)));
        
        return $url.'/'.$this->id .'/'.$expires.'/'.
            hash('sha256',
                serialize(
                    array($url, $expires, $this->email,$this->passwd)
                )
            );
        
    }
    
    function validateAutoLogin($called)
    {
        $bits = explode("/",$called);
        if (count($bits) < 4) {
            return false; // unrelated.
        }
        $hash = array_pop($bits);
        $time = array_pop($bits);
        
        $id = array_pop($bits);
        if (!is_numeric($time) || !is_numeric($id)) {
            return false; // wrong format.
        }
        $u = DB_DataObject::Factory($this->tableName());
        $u->get($id);
        $url = implode("/", $bits);
        if ($time < time()) {
            return "Expired";
        }
        //echo serialize(array('/'.$url, $time, $u->email, $u->passwd));
        //echo hash('sha256', serialize(array('/'.$url, $time, $u->email, $u->passwd)));
        if ($hash == hash('sha256', serialize(array('/'.$url, $time*1, $u->email, $u->passwd)))) {
            $u->login();
            return $u;
        }
        return false;
    }
    
    
    function checkTwoFactorAuthentication($val)
    {
        
        
        // also used in login
        require_once 'System.php';
        
        if(
            empty($this->id) ||
            empty($this->oath_key)
        ) {
            return false;
        }
        
        $oathtool = System::which('oathtool');
        
        if (!$oathtool) {
            return false;
        }
        
        $cmd = "{$oathtool} --totp --base32 " . escapeshellarg($this->oath_key);
        
        $password = exec($cmd);
        
        return ($password == $val) ? true : false;
    }
    
    function checkPassword($val)
    {
        if (substr($this->passwd,0,1) == '$') {
            if (function_exists('pasword_verify')) {
                return password_verify($val, $this->passwd);
            }
            return crypt($val,$this->passwd) == $this->passwd ;
        }
        // old style md5 passwords...- cant be used with courier....
        return md5($val) == $this->passwd;
    }
    
    function setPassword($value) 
    {
        if (function_exists('pasword_hash')) {
            return password_hash($value);
        }
        
        $salt='';
        while(strlen($salt)<9) {
            $salt.=chr(rand(64,126));
            //php -r var_dump(crypt('testpassword', '$1$'. (rand(64,126)). '$'));
        }
        $this->passwd = crypt($value, '$1$'. $salt. '$');
       
       
    }      
    
    function generatePassword($length = 5) // genearte a password (add set 'rawPasswd' to it's value)
    {
        require_once 'Text/Password.php';
        $this->rawPasswd = strtr(ucfirst(Text_Password::create($length)).ucfirst(Text_Password::create($length)), array(
        "a"=>"4", "e"=>"3",  "i"=>"1",  "o"=>"0", "s"=>"5",  "t"=>"7"));
        $this->setPassword($this->rawPasswd);
        return $this->rawPasswd;
    }
    
    function company()
    {
        if (empty($this->company_id)) {
            return false;
        }
        
        static $cache = array();
        
        // Check if we have cached this company
        if (isset($cache[$this->company_id])) {
            return $cache[$this->company_id];
        }
        
        $x = DB_DataObject::factory('core_company');
        $x->autoJoin();
        $x->get($this->company_id);
        
        // Cache the result
        $cache[$this->company_id] = $x;
        
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
        $ar = func_get_args();
        $val = array_shift($ar);
        if ($val == $this->lang) {
            return;
        }
        $uu = clone($this);
        $this->lang = $val;
        $this->update($uu);
        if(!empty(self::$authUser) && self::$authUser->id == $this->id) {
            self::$authUser->lang = $this->lang;
        }
        return $this->lang;
    }
            
    
    function authUserArray()
    {
        $aur = $this->toArray();
        
        if ($this->id < 1) {
            return $aur;
        }
        
        //DB_DataObject::debugLevel(1);
        // $c = DB_Dataobject::factory('core_company');
        // $im = DB_Dataobject::factory('Images');
        // $c->joinAdd($im, 'LEFT');
        // $c->selectAdd();
        // $c->selectAs($c, 'company_id_%s');
        // $c->selectAs($im, 'company_id_logo_id_%s');
        // $c->id = $this->company_id;
        // $c->limit(1);
        // $c->find(true);
        
        // $aur = array_merge( $c->toArray(),$aur);
        
        // if (empty($c->company_id_logo_id_id))  {
                 
            $im = DB_Dataobject::factory('Images');
            $im->ontable = DB_DataObject::factory('core_company')->tableName();
            $im->onid = $this->company_id;
            $im->imgtype = 'LOGO';
            $im->orderBy('id desc');
            $im->limit(1);
            $im->selectAdd();
            $im->selectAs($im,  'company_id_logo_id_%s');
            if ($im->find(true)) {
                
                foreach($im->toArray() as $k=>$v) {
                    if (!preg_match('/^company_id_logo_id_/', $k)) {
                        continue;
                    }
                    $aur[$k] = $v;
                    if($k == 'company_id_logo_id_id') {
                        $aur['company_id_logo_id'] = $v; // use the latest image with image type 'LOGO' instead of using the image with 'logo_id'
                    }
                }
            }
        // }
      
        // perms + groups.
        $aur['perms']  = $this->getPerms();
        $g = DB_DataObject::Factory('core_group_member');
        $aur['groups']  = $g->listGroupMembership($this, 'name');
        
        $aur['passwd'] = '';
        $aur['dailykey'] = '';
        $aur['oath_key'] = '';
        
        $aur['oath_key_enable'] = !empty($this->oath_key);
        $aur['require_oath'] = 1;
        
        $s = DB_DataObject::Factory('core_setting');
        $oath_require = $s->lookup('core', 'two_factor_auth_required');
        $aur['require_oath'] = $oath_require ?  $oath_require->val : 0;
        
        $aur['core_person_settings'] = $this->settings();

        $aur['lang_name'] = DB_DataObject::factory('i18n')->translate('en','l', $aur['lang']);
          
        return $aur;
    }
    
    function settings($return_obj = false)
    {
        $cs = DB_DataObject::factory('core_person_settings');
        $cs->setFrom(array(
            'person_id' => $this->id
        ));
        return $return_obj ? $cs->fetchAll() : $cs->fetchAll('scope', 'data');;
    }
    function toRooSingleArray($authUser, $request)  
    {
        $ret = $this->toArray();
        foreach( $this->settings() as $k=>$v) {
            $ret['core_person_settings['. $k .']'] = $v;
        }
    
        return $ret;
    }
    //   ----------PERMS------  ----------------
    function getPerms() 
    {
         //DB_DataObject::debugLevel(1);
        // find out all the groups they are a member of.. + Default..
        
        // ------ INIITIALIZE IF NO GROUPS ARE SET UP.
        
        $g = DB_DataObject::Factory('core_group_right');
        if (!$g->count()) {
            $g->genDefault();
        }
        
        if ($this->id < 0) {
            return $g->adminRights(); // system is not set up - so they get full rights.
        }
        //DB_DataObject::debugLevel(1);
        $g = DB_DataObject::Factory('core_group_member');
        $g->whereAdd('group_id is NOT NULL AND user_id IS NOT NULL');
        if (!$g->count()) {
            // add the current user to the admin group..
            $g = DB_DataObject::Factory('core_group');
            if ($g->get('name', 'Administrators')) {
                $gm = DB_DataObject::Factory('core_group_member');
                $gm->group_id = $g->id;
                $gm->user_id = $this->id;
                $gm->insert();
            }
            
        }
        
        // ------ STANDARD PERMISSION HANDLING.
        $isOwner = $this->company()->comptype == 'OWNER';
        $g = DB_DataObject::Factory('core_group_member');
        $grps = $g->listGroupMembership($this);
       //var_dump($grps);
        $isAdmin = $g->inAdmin;   //???  what???
        //echo '<PRE>'; print_r($grps);var_dump($isAdmin);
        // the load all the perms for those groups, and add them all together..
        // then load all those 
        $g = DB_DataObject::Factory('core_group_right');
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
        $g = DB_DataObject::Factory('core_group_member');
        $grps = $g->listGroupMembership($this);
        $g = DB_DataObject::Factory('core_group');
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
        if(!empty($q['_to_qr_code'])){
            $person = DB_DataObject::factory('Core_person');
            $person->id = $q['id']; 
            
            if(!$person->find(true)) {
                $roo->jerr('_invalid_person');
            }
            
            $hash = $this->generateOathKey();
            
            $_SESSION[__CLASS__] = 
                isset($_SESSION[__CLASS__]) ? 
                    $_SESSION[__CLASS__] : array();
            $_SESSION[__CLASS__]['oath'] = 
                isset($_SESSION[__CLASS__]['oath']) ? 
                    $_SESSION[__CLASS__]['oath'] : array();
                
            $_SESSION[__CLASS__]['oath'][$person->id] = $hash;

            $qrcode = $person->generateQRCode($hash);
            
            if(empty($qrcode)){
                $roo->jerr('Fail to generate QR Code');
            }
            
            $roo->jdata(array(
                'secret' => $hash,
                'image' => $qrcode,
                'issuer' => $person->qrCodeIssuer()
            ));
        }
        
        if(!empty($q['two_factor_auth_code'])) {
            $person = DB_DataObject::factory('core_person');
            $person->get($q['id']);
            $o = clone($person);
            $person->oath_key = $_SESSION[__CLASS__]['oath'][$person->id];
            
            if($person->checkTwoFactorAuthentication($q['two_factor_auth_code'])) {
                $person->update($o);
                unset($_SESSION[__CLASS__]['oath'][$person->id]);
                $roo->jok('DONE');
            }
            
            $roo->jerr('_invalid_auth_code');
        }
        
        if(!empty($q['oath_key_disable'])) {
            $person = DB_DataObject::factory('core_person');
            $person->get($q['id']);
            
            $o = clone($person);
            
            $person->oath_key = '';
            $person->update($o);
            
            $roo->jok('DONE');
        }
        
        if (!empty($q['query']['is_owner'])) {
            $this->whereAdd(" join_company_id_id.comptype = 'OWNER'");
        }
        
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
            $this->whereAdd("{$this->tableName()}.id != {$au->id}");
        } 
        
        if (!empty($q['query']['comptype_or_company_id'])) {
           // DB_DataObject::debugLevel(1);
            $bits = explode(',', $q['query']['comptype_or_company_id']);
            $id = (int) array_pop($bits);
            $ct = $this->escape($bits[0]);
            
            $this->whereAdd(" join_company_id_id.comptype = '$ct' OR {$this->tableName()}.company_id = $id");
            
        }
        
        
        // staff list..
        if (!empty($q['query']['person_inactive'])) {
           // DB_Dataobject::debugLevel(1);
            $this->active = 1;
        }
        $tn_p = $this->tableName();
        $tn_gm = DB_DataObject::Factory('core_group_member')->tableName();
        $tn_g = DB_DataObject::Factory('core_group')->tableName();

        ///---------------- Group views --------
        if (!empty($q['query']['in_group'])) {
            // DB_DataObject::debugLevel(1);
            $ing = (int) $q['query']['in_group'];
            if ($q['query']['in_group'] == -1) {
             
                // list all staff who are not in a group.
                $this->whereAdd("{$this->tableName()}.id NOT IN (
                    SELECT distinct(user_id) FROM $tn_gm LEFT JOIN
                        $tn_g ON $tn_g.id = $tn_gm.group_id)");
                
            } else {
                
                $this->whereAdd("$tn_p.id IN (
                    SELECT distinct(user_id) FROM $tn_gm
                        WHERE group_id = $ing
                    )");
               }
            
        }
        
        if(!empty($q['in_group_name'])){
            
            $v = $this->escape($q['in_group_name']);
            
            $this->whereAdd("
                $tn_p.id IN (
                    SELECT 
                        DISTINCT(user_id) FROM $tn_gm
                    LEFT JOIN
                        $tn_g
                    ON
                        $tn_g.id = $tn_gm.group_id
                    WHERE 
                        $tn_g.name = '{$v}'
                )"
            );
        }
        if(!empty($q['in_group_starts'])){
            
            $v = $this->escape($q['in_group_starts']);
            
            $this->whereAdd("
                $tn_p.id IN (
                    SELECT 
                        DISTINCT(user_id) FROM $tn_gm
                    LEFT JOIN
                        $tn_g
                    ON
                        $tn_g.id = $tn_gm.group_id
                    WHERE 
                        $tn_g.name LIKE '{$v}%'
                )"
            );
        }
        
        
        
        // #2307 Search Country!!
        if (!empty($q['query']['in_country'])) {
            // DB_DataObject::debugLevel(1);
            $inc = $this->escape($q['query']['in_country']);
            $this->whereAdd("$tn_p.countries LIKE '%{$inc}%'");
        }
        
        if (!empty($q['query']['not_in_directory'])) { 
            // it's a Person list..
            // DB_DATaobjecT::debugLevel(1);
            
            // specific to project directory which is single comp. login
            //
            $owncomp = DB_DataObject::Factory('core_company');
            $owncomp->get('comptype', 'OWNER');
            if ($q['company_id'] == $owncomp->id) {
                $this->active =1;
            }
            
            

            if ( $q['query']['not_in_directory'] > -1 && $q['company_id'] > 0) {
                $tn_pd = DB_DataObject::Factory('ProjectDirectory')->tableName();
                // can list current - so that it does not break!!!
                $this->whereAdd("$tn_p.id NOT IN 
                    ( SELECT distinct person_id FROM $tn_pd WHERE
                        project_id = " . inval($q['query']['not_in_directory']) . " AND 
                        company_id = " . intval($q['company_id']) . ')');
                        
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
                {$this->tableName()}.name LIKE '%{$this->escape($q['query']['name'])}%'
            ");
        }
        
         if(!empty($q['query']['name_or_email'])){
            $v = $this->escape($q['query']['name_or_email']);
            $this->whereAdd("
                {$this->tableName()}.name LIKE '%{$v}%'
                OR
                {$this->tableName()}.email LIKE '%{$v}%'
            ");
        }
         if(!empty($q['query']['name_starts'])){
            $this->whereAdd("
                {$this->tableName()}.name LIKE '{$this->escape($q['query']['name_starts'])}%'
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
            $tbcols = $this->table();
            foreach(array('firstname','lastname') as $k) {
                if (isset($tbcols[$k])) {
                    $props[] = "{$tn_p}.{$k}";
                }
            }
            
            
            
            
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
        
        // project directory rules -- this may distrupt things.
        $p = DB_DataObject::factory('ProjectDirectory');
        // if project directories are set up, then we can apply project query rules..
        if ($p->count()) {
            $p->autoJoin();
            $pids = $p->projects($au);
            if (isset($q['query']['project_id'])) {   
                $pid = (int)$q['query']['project_id'];
                if (!in_array($pid, $pids)) {
                    $roo->jerr("Project not in users valid projects");
                }
                $pids = array($pid);
            }
            // project roles..
            //if (empty($q['_anyrole'])) {  // should be project_directry_role
            //    $p->whereAdd("{$p->tableName()}.role != ''");
            // }
            if (!empty($q['query']['role'])) {  // should be project_directry_role
                $role = $this->escape($q['query']['role']); 
               
                $p->whereAdd("{$p->tableName()}.role LIKE '%{$role}%'");
                 
            }
            
            if (!$roo->hasPerm('Core.Projects_All', 'S')) {
                $peps = $p->people($pids);
                $this->whereAddIn("{$this->tableName()}.id", $peps, 'int');
            }
        }    
        
        // fixme - this needs a more generic fix - it was from the mtrack_person code...
        if (isset($q['query']['ticket_id'])) {  
            // find out what state the ticket is in.
            $t = DB_DataObject::Factory('mtrack_ticket');
            $t->autoJoin();
            $t->get($q['query']['ticket_id']);
            
            if (!$this->checkPerm('S', $au)) {
                $roo->jerr("permssion denied to query state of ticket");
            }
            
            $p = DB_DataObject::factory('ProjectDirectory');
            $pids = array($t->project_id);
           
            $peps = $p->people($pids);
            
            $this->whereAddIn($this->tableName().'.id', $peps, 'int');
            
            //$this->whereAdd('join_prole != ''");
            
        }
        
        /*
         * Seems we never expose oath_key / passwd, so...
         */
        
        if($this->tableName() == 'core_person'){
            $this->_extra_cols = array('length_passwd', 'length_oath_key');
        
            $this->selectAdd("
                LENGTH({$this->tableName()}.passwd) AS length_passwd,
                LENGTH({$this->tableName()}.oath_key) AS length_oath_key
            ");
        }
        if (isset($q['_with_group_membership'])) {
            $this->selectAddGroupMemberships();
        }
        
    }
    
    function selectAddGroupMemberships()
    {
        $this->selectAdd("
            
            COALESCE((
                SELECT
                    GROUP_CONCAT(  CASE WHEN core_group.display_name = '' THEN core_group.name ELSE core_group.display_name  END  separator  '\n')
                FROM
                    core_group_member
                LEFT JOIN
                    core_group
                ON
                    core_group.id = core_group_member.group_id
                WHERE
                    core_group_member.user_id = core_person.id
                ORDER BY
                    core_group.display_name ASC
            ), '')  as member_of");
    }
    
    function setFromRoo($ar, $roo)
    {
        $this->setFrom($ar); 
        
        if(!empty($ar['_enable_oath_key'])){
            $oath_key = $this->generateOathKey();
        }
        
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
        // this only applies to our owner company..
        $c = $this->company();
        if (empty($c) || empty($c->comptype_name) || $c->comptype_name != 'OWNER') {
            return true;
        }
        
        
        $xx = DB_Dataobject::factory($this->tableName());
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
    
    function beforeDelete($dependants_array, $roo)
    {
        //delete group membership except for admin group..
        // if they are a member of admin group do not delete anything.
        $default_admin = false;
        
        $e = DB_DataObject::Factory('Events');
        $e->whereAdd('person_id = ' . $this->id);
        
        $g = DB_DataObject::Factory('core_group');
        $g->get('name', 'Administrators');  // select * from core_group where name = 'Administrators'
        
        $p = DB_DataObject::Factory('core_group_member');
        $p->setFrom(array(
            'user_id' => $this->id,
            'group_id' => $g->id
        ));

        if ($p->count()) {
           $roo->jerr("Please remove this user from the Administrator group before deleting");
        }
 
         
        $p = DB_DataObject::Factory('core_group_member');
        $p->user_id = $this->id;
        $mem = $p->fetchAll();  // fetch all the rows and set the $mem variable to the rows data, just like mysqli_fetch_assoc
        $e->logDeletedRecord($mem);
                
        foreach($mem as $p) { 
            $p->delete();
        }  
        
        $e = DB_DataObject::Factory('Events');        
        $e->person_id = $this->id;
        $eve = $e->fetchAll();  // fetch all the rows and set the $mem variable to the rows data, just like mysqli_fetch_assoc

        $e->logDeletedRecord($eve);
        foreach($eve as $e) { 
            $e->delete();
        }  
        
        
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
        // if not authenticated... do not allow in???
        if (!$au ) {
            return false;
        }
        
        // determine if it's staff!!!
        $owncomp = DB_DataObject::Factory('core_company');
        $owncomp->get('comptype', 'OWNER');
        $editor_is_staff = ($au->company_id ==  $owncomp->id);
        
        if (!$editor_is_staff) {
            // non staff editing should not user roo/isPerm?
            return false; // no permission if user is not staff!?
        
        }
        
        $this_is_staff = ($this->company_id ==  $owncomp->id);
       
       /*
        if (!$this_is_staff ) {
            
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
            
            
            // mtrack had the idea that all 'S' should be allowed.. - but filtered later..
            // ???? do we want this?
            
            // edit self... - what about other staff members...
            
            //return $this->company_id == $au->company_id;
        }
        */
         
        // yes, only owner company can mess with this...
        
        
        
    
        switch ($lvl) {
            // extra case change passwod?
            case 'P': //??? password
                // standard perms -- for editing + if the user is dowing them selves..
                $ret = $this_is_staff  ? $au->hasPerm("Core.Staff", "E") : $au->hasPerm("Core.Person", "E");
                return $ret || $au->id == $this->id;   // can change own data?
            
            default:                
                return $this_is_staff ? $au->hasPerm("Core.Staff", $lvl) : $au->hasPerm("Core.Person", $lvl);
                
                    
        
        }
        return false;
    }
    
    function beforeInsert($req, $roo)
    {
         if (!empty($req['_bulk_update_passwords'])) {
            $this->bulkUpdatePasswords($req['_bulk_update_passwords'], $roo);
            return;
        }
        
        $p = DB_DataObject::factory('core_person');
        if ($roo->authUser->id > -1 ||  $p->count() > 1) {
            $pp = DB_DataObject::factory('core_person');
            $pp->whereAdd('LOWER(email) = "' . $pp->escape(strtolower(trim($this->email))) . '"');
            if ($pp->count()){
                $roo->jerror("NOTICE-DUPE-EMAIL", "that email already exists in the database");
            }
            
            
            return;
        }
        $c = DB_DataObject::Factory('core_company');
        $tc = $c->count();
        
        if (!$tc || $tc> 1) {
            $roo->jerr("can not create initial user as multiple companies already exist");
        }
        $c->find(true);
        $this->company_id = $c->id;
        $this->email = trim($this->email);
        
        
        
        
        
        
    }
    
    function onInsert($req, $roo)
    {
         
        $p = DB_DataObject::factory('core_person');
        if ($roo->authUser->id < 0 && $p->count() == 1) {
            // this seems a bit risky...
            
            $g = DB_DataObject::factory('core_group');
            $g->initGroups();
            
            $g->type = 0;
            $g->get('name', 'Administrators');
            
            $p = DB_DataObject::factory('core_group_member');
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
        if (!empty($req['core_person_settings'])) {
            $this->updateSettings($req['core_person_settings'], $roo);
        }
    }
    
    function onUpdate($old, $req,$roo, $event)
    {
        if (!empty($req['core_person_settings'])) {
            $this->updateSettings($req['core_person_settings'], $roo);
        }
    }
    
    // there should really be a registry of valid scope values!?
    function updateSettings($ar, $roo)
    {
        //DB_DataObject::debugLevel(1);
        $old = array();
        foreach($this->settings(true) as $o) {
            $old[$o->scope] = $o;
        }
        foreach($ar as $k=>$v) {
            if (isset($old[$k])) {
                $oo = clone($old[$k]);
                $old[$k]->data = $v;
                $old[$k]->update($oo);
                continue;
            }
            $cs = DB_DataObject::Factory('core_person_settings');
            $cs->setFrom(array(
                'person_id' =>$this->id,
                'scope' => $k,
                'data' => $v
            ));
            $cs->insert();
        }
        // we dont delete old stuff....
    }
    
    
    function importFromArray($roo, $persons, $opts)
    {
        if (empty($opts['prefix'])) {
            $roo->jerr("opts[prefix] is empty - you can not just create passwords based on the user names");
        }
        
        if (!is_array($persons) || empty($persons)) {
            $roo->jerr("error in the person data. - empty on not valid");
        }
        DB_DataObject::factory('core_group')->initGroups();
        
        foreach($persons as $person){
            $p = DB_DataObject::factory('core_person');
            if($p->get('name', $person['name'])){
                continue;
            }
            $p->setFrom($person);
            
            $companies = DB_DataObject::factory('core_company');
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
                $groups = DB_DataObject::factory('core_group');
                if(!$groups->get('name', $person['groups'])){
                    $roo->jerr("Missing groups : {$person['groups']}");
                }
                $gm = DB_DataObject::factory('core_group_member');
                $gm->change($p, $groups, true);
            }
            
            $p->onInsert(array(), $roo);
        }
    }
    
    // this is for the To: "{getEmailName()}" <email@address>
    // not good for Dear XXXX, - use {person.firstname} for that.
    function getEmailName()
    {
        $name = array();
        
        if(!empty($this->honor)){
            array_push($name, $this->honor);
        }
        
        if(!empty($this->name)){
            array_push($name, $this->name);
            
            return implode(' ', $name);
        }
        
        if(!empty($this->firstname) || !empty($this->lastname)){
            array_push($name, $this->firstname);
            array_push($name, $this->lastname);
            
            $name = array_filter($name);
            
            return implode(' ', $name);
        }
        
        return $this->email;
    }
    
    function sesPrefix()
    {
        $ff= HTML_FlexyFramework::get();
        
        $appname = empty($ff->appNameShort) ? $ff->project : $ff->project . '-' . $ff->appNameShort;
        $dname = method_exists($this, 'getDatabaseConnection') ? $this->getDatabaseConnection()->dsn['database'] : $this->databaseNickname();
        $sesPrefix = $appname.'-' .get_class($this) .'-' . $dname;

        return $sesPrefix;
    }
    
    function loginPublic() // used where???
    {
        $this->isAuth(); // force session start..
        $db = $this->getDatabaseConnection();
        $ff = HTML_FlexyFramework::get();
        
        if(empty($ff->Pman) || empty($ff->Pman['login_public'])){
            return false;
        }
        
        $sesPrefix = $ff->Pman['login_public'] . '-' .get_class($this) .'-'.$db->dsn['database'] ;
        
        $p = DB_DAtaObject::Factory($this->tableName());
        $p->get($this->pid());
        
        $_SESSION[get_class($this)][$sesPrefix .'-auth'] = serialize((object)$p->toArray());
        
        return true;
    }
    
    function beforeUpdate($old, $q, $roo)
    {
        $this->email = trim($this->email);

        $p = DB_DataObject::factory('core_person');
        if ($roo->authUser->id > -1 ||  $p->count() > 1) {
            $pp = DB_DataObject::factory('core_person');
            $pp->whereAdd('LOWER(email) = "' . $pp->escape(strtolower(trim($this->email))) . '"');
            $pp->whereAdd('id != ' . $old->id);
            if ($pp->count()){
                $roo->jerror("NOTICE-DUPE-EMAIL", "that email already exists in the database");
            }
        }
    }
    
    function generateOathKey()
    {
        require 'Base32.php';
        
        $base32 = new Base32();
        
        return $base32->base32_encode(bin2hex(openssl_random_pseudo_bytes(10)));
    }
    
    function generateQRCode($hash)
    {
        if(
            empty($this->email) &&
            empty($hash)
        ){
            return false;
        }
        
        $issuer = rawurlencode($this->qrCodeIssuer());
        
        $uri = "otpauth://totp/{$issuer}:{$this->email}?secret={$hash}&issuer={$issuer}&algorithm=SHA1&digits=6&period=30";
        
        require_once 'Image/QRCode.php';
        
        $qrcode = new Image_QRCode();
        
        $image = $qrcode->makeCode($uri, array(
            'output_type' => 'return'
        ));
        
        ob_start();
        imagepng($image);
        $base64 = base64_encode(ob_get_contents());
        ob_end_clean();
        
        return "data:image/png;base64,{$base64}";
    }
    
    function qrCodeIssuer()
    {
        $pg= HTML_FlexyFramework::get()->page;
        
        $issuer = (empty($pg->company->name)) ?  'ROOJS' : "{$pg->company->name}";
        
        return $issuer;
    }
    
    static function test_ADMIN_PASSWORD_RESET($pg, $to)
    {
        $ff = HTML_FlexyFramework::get();
        $person = DB_DataObject::Factory('core_person');
        $person->id = -1;
        
        return array(
            'HTTP_HOST' => $_SERVER['SERVER_NAME'],
            'person' => $person,
            'authFrom' => 'FAKE_LINK',
            'authKey' => 'FAKE_KEY',

            'rcpts' => $to->email,
        );
        
        return $content;
    }
    function bulkUpdatePasswords($data, $roo)
    {
        
        if ( !$roo->hasPerm("Core.Staff", "E")) {
            $roo->jerr("permission denied");
        }
        $rows = explode("\n",$data);
        $upd = array();
        $bad  = array();
        
        foreach($rows  as $i=>$row) {
            if (!strlen(trim($row))) {
                continue;
            }
            $bits = preg_split('/\s+/', trim($row));
            if (count($bits) != 2) {
                $bad[] = "Invalid line: {$row}";
                continue;
            }
            // validate.
            $upd[strtolower($bits[0])] = $bits[1];
            
        }
        if (empty($upd)) {
            
            $roo->jerr(empty($bad) ? "No rows to update": ("ERRORS: ". implode("\n", $bad)));
            return;
        }
        // next fetch them all.
        $p = DB_DataObject::factory('core_person');
        $p->whereAddIn('email', array_keys($upd), 'string');
        foreach($p->fetchAll() as $p) {
            $map[strtolower($p->email)] = $p;
        }
        foreach($upd as $k=>$nv) {
            if (!isset($map[$k])) {
                $bad[] = "Missing account with email: " . $k;
                continue;
            }
            if ($map[$k]->id == $roo->authUser->id) {
                $bad[] = "You can not update your own password here: " . $k;
                continue;
            }
        }
        if (!empty($bad)) {
            $roo->jerr("ERRORS: ". implode("\n", $bad));
            return;
        }
        foreach($map as $k => $p) {
            $pp = clone($p);
            $p->setPassword($upd[$k]);
            $p->update($pp);
        }
        $roo->jok("Updated");
        
        
    }

    function updateFails($field, $fails)
    {
        return;
    }
    
 }
