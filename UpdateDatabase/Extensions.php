<?php

require_once 'Pman_Core_UpdateDatabase';

class Extensions extends Pman_Core_UpdateDatabase
{
    function get($base, $opts = array())
    {
        print_r($this->required_extensions);exit;
    }
}