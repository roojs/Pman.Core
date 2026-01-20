<?php

class Pman_Core_NotifyRouter
{
    var $host;
    var $localhost;
    var $timeout = 15;
    var $socket_options = array();
    var $debug = 1;
    var $debug_handler;
    var $dkim = true;
    
    function __construct($smtp_host, $mx)
    {
        $this->host = $host;
        $this->localhost = $localhost;
        $this->socket_options = $socket_options;
    }
    
    /**
     * Return a Mail_smtp object
     * @return Mail_smtp
     */
    function toMailer()
    {
        return Mail::factory('smtp', array(
            'host'          => $this->host,
            'localhost'     => $this->localhost,
            'timeout'       => $this->timeout,
            'socket_options'=> $this->socket_options,
            'debug'         => $this->debug,
            'debug_handler' => $this->debug_handler,
            'dkim'          => $this->dkim
        ));
    }
}
