<?php

class Channel_model extends CI_Model {
	
    function __construct()
    {
        // Call the Model constructor
        parent::__construct();
    }
	
	function get_all_channels()
	{
        $query = $this->db->get('channel');

		if ($this->db->_error_message()) // error checking
			show_error($this->db->_error_message());

		$result_array = $query->result_array();
		
		return $result_array;
	}

}
?>
