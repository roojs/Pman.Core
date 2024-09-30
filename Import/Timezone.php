<?php



require_once 'Pman.php';

class Pman_Core_Import_Timezone extends Pman 
{
    static $cli_desc = "Import timezone region name and area name to core_enum"; 
    
    static $cli_opts = array();

    function getAuth()
    {
        $ff = HTML_FlexyFramework::get();
        
        if (!$ff->cli) {
            die("cli only");
        }
    }
    
    function get($part = '', $opts=array())
    {
        phpinfo();
        die('test');
        $ce = DB_DataObject::factory('core_enum');
        $ce->query("
            SELECT
                *, TIME_FORMAT(TIMEDIFF(NOW(), CONVERT_TZ(NOW(), Name, 'UTC')), '%H:%i') as timeOffset
            FROM
                mysql.time_zone_name
            WHERE
                Name LIKE '%/%'
                AND
                Name NOT LIKE '%/%/%'
                AND
                Name NOT LIKE 'right%'
                AND
                Name NOT LIKE 'posix%'
                AND
                Name NOT LIKE 'Etc%'
            ORDER BY
                SUBSTRING_INDEX(Name, '/', 1) ASC,
                timeoffset ASC,
                Name ASC
        ");

        $values = array();

        $regions = array();
        $areas = array();

        while($ce->fetch()) {
            $ar = explode('/', $ce->Name);
            $region = $ar[0];
            $area = str_replace('_', ' ', $ar[1]);

            if(!in_array($region, $regions)) {
                $regions[] = $region;
                $values[] = "('Timezone.Region', '" . $region . "', 1, 0, 0, '" . $region . "', 0)";
            }

            if(!in_array($area, $areas)) {
                $areas[] = $area;
                $values[] = "('Timezone.Area', '" . $area . "', 1, 0, 0, '" . $area . "', 0)";
            }
        }

        $sql = "
            INSERT INTO
                core_enum (etype, name, active, seqid, seqmax, display_name, is_system_enum)
            VALUES
                " . implode(", \n", $values) . "
        ";

        $ce = DB_DataObject::factory('core_enum');
        $ce->query($sql);
        
        $this->jok('DONE');
    }
}