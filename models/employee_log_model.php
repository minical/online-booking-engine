<?php

class Employee_log_model extends CI_Model {

    function __construct()
    {        
        parent::__construct();
    }		
	
	function insert_log($data)
    {
	    $this->db->insert("employee_log", $data);
    }	
    
  }