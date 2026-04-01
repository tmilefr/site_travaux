<?php

class Loginchecker
{
    private $CI;
    function __construct()
    {
        $this->CI = & get_instance();
        $this->CI->load->library('Acl');
    }

    
    /**
     * Grant access or login , manage rules access.
     *
     * @access public
     * @return bool
     * 
     */
    function loginCheck()
    {
        $this->CI->acl->Route();
    }
}

?>
