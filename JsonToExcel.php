<?php

/**
 * 
 * MOVE To Core ---
 * 
 * Usage:
 * 
 * POST Values: 
 *  xml = data
 *  format = [empty=xls] , 'gnumeric'  (download format)
 *  debug = true => download xml.
 * 
 * 
 * 
 */

require_once 'Pman.php';
class Pman_Core_JsonToExcel extends Pman
{
    function getAuth()
    {
        $au = $this->getAuthUser();
        if (!$au) {
            die("NOT authenticated");
        }
        $this->authUser = $au;
        return true;
    }

    function get()
    {
        $this->jerr("invalid get");
    }
    function post($fname) {
        
        $ml = (int) ini_get('suhosin.post.max_value_length');
        if (empty($_POST['_json'])) {
            header("HTTP/1.0 400 Internal Server Error");
            die(  $ml ? "Suhosin Patch enabled - try and disable it!!!" : 'no JSON sent');
        }
        
        if (empty($_POST['_json'])) {
            header("HTTP/1.0 400 Internal Server Error");
            die("Missing json attribute");
        }
        $xml = $_POST['xml'];
        
        $xml = iconv("UTF-8", "UTF-8//IGNORE",  $xml);
        
        //$xml = str_replace('é', 'e', $xml);
        //$xml = str_replace("\xA0", ' ', $xml);
        //$xml = str_replace("Ø", 'dia.',$xml);
        
        //$this->addEvent("DOWNLOAD", false, isset($_REQUEST['title']) ? $_REQUEST['title'] : '???');
        
        
        if (!empty($_POST['format']) && $_POST['format']=='gnumeric') {
            if (empty($_POST['debug'])) {
                header('Content-type: application/x-gnumeric');
            } else {
                header('Content-type: text/xml');
            }
            echo $xml; 
            exit;
        }
        $srcTmp = ini_get('session.save_path') . '/' .uniqid('gnumeric_').'.gnumeric';
        $targetTmp = ini_get('session.save_path') . '/' .uniqid('gnumeric_').'.xls';
        // write the gnumeric file...
        $fh = fopen($srcTmp,'w');
        fwrite($fh, $xml);
        fclose($fh);
        
        
        require_once 'System.php';
        $ss = System::which('ssconvert');
        $cmd =  $ss. 
                " --import-encoding=Gnumeric_XmlIO:sax" .
                " --export-type=Gnumeric_Excel:excel_biff8 " . 
                $srcTmp . ' ' . $targetTmp . ' 2>&1';
        // echo $cmd;
        //passthru($cmd);exit;
        //exit;
        $out = `$cmd`;
        clearstatcache(); 
        
        if (!file_exists($targetTmp) || !filesize($targetTmp)) {
            header("HTTP/1.0 400 Internal Server Error - Convert error");
            die("ERROR CONVERTING?:" . $cmd ."\n<BR><BR> OUTPUT:". htmlspecialchars($out));
        }
       // unlink($srcTmp);
        if (empty($fname)) {
           $fname = basename($targetTmp);
        }
        $fname .= preg_match('/\.xls/i', $fname) ? '' :  '.xls'; // make sure it ends in xls..
       
        header('Content-type: application/vnd.ms-excel');
        header('Content-Disposition: attachment; filename="' .addslashes($fname). '"');
        header('Content-length: '. filesize($targetTmp));   
        header("Content-Transfer-Encoding: binary");
        if ($file = fopen($targetTmp, 'rb')) {
            while(!feof($file) and (connection_status()==0)) {
                print(fread($file, 1024*8));
                flush();
            }
            fclose($file);
        }
       
        unlink($targetTmp);
        exit;
        
    }
    
    
}
