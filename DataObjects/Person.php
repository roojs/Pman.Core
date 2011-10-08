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
    public $office_id;                       // int(11)  
    public $name;                            // string(128)  not_null
    public $phone;                           // string(32)  not_null
    public $fax;                             // string(32)  not_null
    public $email;                           // string(128)  not_null
    public $company_id;                      // int(11)  
    public $role;                            // string(32)  not_null
    public $active;                          // int(11)  not_null
    public $remarks;                         // blob(65535)  not_null blob
    public $passwd;                          // string(64)  not_null
    public $owner_id;                        // int(11)  not_null
    public $lang;                            // string(8)  
    public $no_reset_sent;                   // int(11)  
    public $action_type;                     // string(32)  
    public $project_id;                      // int(11)  
    public $deleted_by;                      // int(11)  not_null
    public $deleted_dt;                      // datetime(19)  binary

    
    /* the code above is auto generated do not remove the tag below */
    ###END_AUTOCODE
    /**
     *
     * @param {String} $templateFile  (mail/XXXXXXX.txt) exclude the mail and .txt bit.
     * @param {Array|Object} $args   data to send out..
     * @return {Array|PEAR_Error} array of $recipents, $header, $body 
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
        require_once 'HTML/Template/Flexy.php';
        $template = new HTML_Template_Flexy( array(
            'compiler'    => 'Flexy',
            'nonHTML' => true,
            'filters' => array('SimpleTags','Mail'),
            //     'debug'=>1,
        ));
        
     
         
        
        $template->compile("mail/$templateFile.txt");
        
        /* use variables from this object to ouput data. */
        $mailtext = $template->bufferedOutputObject($content);
        //echo "<PRE>";print_R($mailtext);
        print_R($mailtext);exit;
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
        
        return array(
            'recipents' => $recipents,
            'headers'    => $header,
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
        $oe = error_reporting(E_ALL ^ E_NOTICE);
        $ret = $mail->send($ar['recipents'],$ar['headers'],$ar['body']);
        error_reporting($oe);
       
        return $ret;
    
    }
    function getEmailFrom()
    {
        return '"' . addslashes($this->name) . '" <' . $this->email . '>';
    }
    function toEventString() 
    {
        return empty($this->name) ? $this->email : $this->name;
    } 
    function verifyAuth()
    { 
        $ff= HTML_FlexyFramework::get();
        if (!empty($ff->Pman['auth_comptype']) && $ff->Pman['auth_comptype'] != $this->company()->comptype) {
            $ff->page->jerr("Login not permited to outside companies");
        }
        return true;
        
    }    
   
   
    //   ---------------- authentication / passwords and keys stuff  ----------------
    function isAuth()
    {
        $db = $this->getDatabaseConnection();
        $sesPrefix = $db->dsn['database'];
        @session_start();
        if (!empty($_SESSION[__CLASS__][$sesPrefix .'-auth'])) {
            // in session...
            $a = unserialize($_SESSION[__CLASS__][$sesPrefix .'-auth']);
            $u = DB_DataObject::factory('Person');
            if ($u->get($a->id)) { //&& strlen($u->passwd)) {
                $u->verifyAuth();
                
                return true;
            }
            
            $_SESSION[__CLASS__][$sesPrefix .'-auth'] = '';
            
        }
        // local auth - 
        $u = DB_DataObject::factory('Person');
        $ff = HTML_FlexyFramework::get();
        if (!empty($ff->Pman['local_autoauth']) && 
            (!empty($_SERVER['SERVER_ADDR'])) &&
            ($_SERVER['SERVER_ADDR'] == '127.0.0.1') &&
            ($_SERVER['REMOTE_ADDR'] == '127.0.0.1') &&
            $u->get('email', $ff->Pman['local_autoauth'])
        ) {
            $db = $this->getDatabaseConnection();
            $sesPrefix = $db->dsn['database'];
            $_SESSION[__CLASS__][$sesPrefix .'-auth'] = serialize($u);
            return true;
        }
           
        
        // not in session or not matched...
        $u = DB_DataObject::factory('Person');
        $u->whereAdd(' LENGTH(passwd) > 0');
        $n = $u->count();
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
        $sesPrefix = $db->dsn['database'];
        if (!empty($_SESSION[__CLASS__][$sesPrefix .'-auth'])) {
            $a = unserialize($_SESSION[__CLASS__][$sesPrefix .'-auth']);
            
            $u = DB_DataObject::factory('Person');
            if ($u->get($a->id)) { /// && strlen($u->passwd)) {
                return clone($u);
            }
             
        }
        
        $u = DB_DataObject::factory('Person');
        $u->whereAdd(' LENGTH(passwd) > 0');
        if (!$u->count()){
            $u = DB_DataObject::factory('Person');
            $u->id = -1;
            return $u;
            
        }
        return false;
    }     
    function login()
    {
        $this->isAuth(); // force session start..
        $this->verifyAuth();
         $db = $this->getDatabaseConnection();
        $sesPrefix = $db->dsn['database'];
        $_SESSION[__CLASS__][$sesPrefix .'-auth'] = serialize($this);
        
    }
    function logout()
    {
        $this->isAuth(); // force session start..
         $db = $this->getDatabaseConnection();
        $sesPrefix = $db->dsn['database'];
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
    
    function company()
    {
        $x = DB_DataObject::factory('Companies');
        $x->get($this->company_id);
        return $x;
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
    function lang($val)
    {
        if ($val == $this->lang) {
            return;
        }
        $uu = clone($this);
        $this->lang = $val;
        $this->update($uu);

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
            $im->selectAs($im,  'company_id_logo_id_%s');
            if ($im->find(true)) {
                    
                foreach($im->toArray() as $k=>$v) {
                    $aur[$k] = $v;
                }
            }
        }
      
        // perms + groups.
        $aur['perms']  = $this->getPerms();
        $g = DB_DataObject::Factory('Group_Members');
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
        
        $g = DB_DataObject::Factory('Group_Rights');
        if (!$g->count()) {
            $g->genDefault();
        }
        
        if ($this->id < 0) {
            return $g->adminRights(); // system is not set up - so they get full rights.
        }
        
        $g = DB_DataObject::Factory('Group_Members');
        if (!$g->count()) {
            // add the current user to the admin group..
            $g = DB_DataObject::Factory('Groups');
            if ($g->get('name', 'Administrators')) {
                $gm = DB_DataObject::Factory('Group_Members');
                $gm->group_id = $g->id;
                $gm->user_id = $this->id;
                $gm->insert();
            }
            
        }
        
        // ------ STANDARD PERMISSION HANDLING.
        
        $g = DB_DataObject::Factory('Group_Members');
        $grps = $g->listGroupMembership($this);
       // print_r($grps);
        $isAdmin = $g->inAdmin;
        //echo '<PRE>'; print_r($grps);var_dump($isAdmin);
        // the load all the perms for those groups, and add them all together..
        // then load all those 
        $g = DB_DataObject::Factory('Group_Rights');
        $ret =  $g->listPermsFromGroupIds($grps, $isAdmin);
        //echo '<PRE>';print_r($ret);
        return $ret;
         
        
    }
    /**
     *Basic group fetching - probably needs to filter by type eventually.
     *
     */
    
    function groups()
    {
        $g = DB_DataObject::Factory('Group_Members');
        $grps = $g->listGroupMembership($this);
        $g = DB_DataObject::Factory('Groups');
        $g->whereAddIn('id', $grps, 'int');
        return $g->fetchAll();
        
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
    function applyFilters($q, $au)
    {
        if (!empty($q['query']['person_not_internal'])) {
            $this->whereAdd(" join_company_id_id.isOwner = 0 ");
        }
        if (!empty($q['query']['person_internal_only_all'])) {
            // must be internal and not current user (need for distribution list)
            $this->whereAdd(" join_company_id_id.comptype = 'OWNER'");
            
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
        
        ///---------------- Group views --------
        if (!empty($q['query']['in_group'])) {
            // DB_DataObject::debugLevel(1);
            $ing = (int) $q['query']['in_group'];
            if ($q['query']['in_group'] == -1) {
                // list all staff who are not in a group.
                $this->whereAdd("Person.id NOT IN (
                    SELECT distinct(user_id) FROM Group_Members LEFT JOIN
                        Groups ON Groups.id = Group_Members.group_id
                        WHERE Groups.type = ".$q['query']['type']."
                    )");
                
                
            } else {
                
                $this->whereAdd("Person.id IN (
                    SELECT distinct(user_id) FROM Group_Members 
                        WHERE group_id = $ing
                    )");
               }
            
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
                // can list current - so that it does not break!!!
                $x->whereAdd('Person.id NOT IN 
                    ( SELECT distinct person_id FROM ProjectDirectory WHERE
                        project_id = ' . $q['query']['not_in_directory'] . ' AND 
                        company_id = ' . $this->company_id . ')');
            }
        }
        
        
        if (!empty($q['query']['project_member_of'])) {
               // this is also a flag to return if they are a member..
            //DB_DataObject::debugLevel(1);
            $do = DB_DataObject::factory('ProjectDirectory');
            $do->project_id = $q['query']['project_member_of'];
            
            $this->joinAdd($do,array('joinType' => 'LEFT', 'useWhereAsOn' => true));
            $this->selectAdd('IF(ProjectDirectory.id IS NULL, 0,  ProjectDirectory.id )  as is_member');
                
                
            if (!empty($q['query']['project_member_filter'])) {
                $this->having('is_member !=0');
            
            }
            
        }
        
        
        if (!empty($q['query']['search'])) {
            $s = $this->escape($q['query']['search']);
                    $this->whereAdd("
                        Person.name LIKE '%$s%'  OR
                        Person.email LIKE '%$s%'  OR
                        Person.role LIKE '%$s%'  OR
                        Person.remarks LIKE '%$s%' 
                        
                    ");
        }
        
        //
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
                $ret = $isStaff ? $au->hasPerm("Core.Person", "E") : $au->hasPerm("Core.Staff", "E");
                return $ret || $au->id == $this->id;
            
            case 'S': // list..
                return $au->hasPerm("Core.Person", "S");
            
            case 'E': // edit
                return $isStaff ? $au->hasPerm("Core.Person", "E") : $au->hasPerm("Core.Staff", "E");
            
            case 'A': // add
                return $isStaff ? $au->hasPerm("Core.Person", "A") : $au->hasPerm("Core.Staff", "A");
            
            case 'D': // add
                return $isStaff ? $au->hasPerm("Core.Person", "D") : $au->hasPerm("Core.Staff", "D");
        
        }
        return false;
    }
    function onInsert($req, $roo)  
    {
        
        if ($roo->authUser->id < 0) {
            $g = DB_DataObject::factory('Groups');
            $g->type = 0;
            $g->get('name', 'Administrators');
            
            $p = DB_DataObject::factory('Group_Members');
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
 }
