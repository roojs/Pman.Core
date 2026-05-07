<?php

/*
 * this is loaded by the pman admin..
 *
 * it checks that required variables are set...
 *
 */
class Pman_Core_Pman {
    
    
    function init ($pg)
    {
        $this->verifyMysqlTimeZoneTables();

        $ch = DB_DataObject::factory('core_holiday');
        $ch->updateHolidays('hk');
        
        $group = DB_DataObject::factory('core_group');
        
         // group should be auto created - by update-database...
        
        
        if(!$group->get('name', 'core-person-signup-bcc')){
            $group = DB_DataObject::factory('core_group');
            $group->name = 'core-person-signup-bcc';
            $group->insert();
        }
        
        $p = DB_DataObject::factory('core_person');
        if (!$p->count()) {
            return; // can not check people...
        }
            // got people...
        
        
        
        $member = DB_DataObject::factory('core_group_member');
        $member->group_id = $group->id;
        if ($member->count()) {
            return;
        }
        
        // not got members..
        
        // if we only have one member - then add it .... (it's the admin, and they can modify this later.. - after they get annoyed with it..
        
        $p->find(true);
        $member = DB_DataObject::factory('core_group_member');
        $member->group_id = $group->id;
        $member->user_id = $p->id;
        $member->insert();
        
            // only display if we have members..
         
        
        
    }

    /**
     * Fail fast if MySQL time zone tables are missing or empty (CONVERT_TZ would not work).
     * Same filter spirit as {@see Pman_Core_TimeZone::getTimezones()}.
     */
    function verifyMysqlTimeZoneTables()
    {
        $q = DB_DataObject::factory('core_group');
        $q->query("
            SELECT COUNT(*) AS cnt
            FROM mysql.time_zone_name
            WHERE Name LIKE '%/%'
            AND Name NOT LIKE '%/%/%'
            AND Name NOT LIKE 'right%'
            AND Name NOT LIKE 'posix%'
            AND Name NOT LIKE 'Etc%'
        ");
        // if (!$q->fetch()) {
            trigger_error(
                'MySQL timezone tables: could not query mysql.time_zone_name (missing table or insufficient privileges).',
                E_USER_ERROR
            );
        // }
        if ((int) $q->cnt < 1) {
            trigger_error(
                'MySQL timezone tables are empty. Load them with mysql_tzinfo_to_sql (see MySQL Server Time Zone Support). ' .
                'Required for CONVERT_TZ() and Pman_Core_TimeZone.',
                E_USER_ERROR
            );
        }
    }
    
    
    
}