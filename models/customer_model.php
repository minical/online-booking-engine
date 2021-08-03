<?php

class Customer_model extends CI_Model
{

    public function __construct()
    {
        parent::__construct();
    }

    public function get_customer_id_by_email($email, $company_id)
    {
        $this->db->from("customer");
        $this->db->where('email', $email);
        $this->db->where('company_id', $company_id);
        $query = $this->db->get();

        if ($this->db->_error_message()) {
            show_error($this->db->_error_message());
        }

        $result = $query->row_array(0);
        if (isset($result['customer_id'])) {
            return $result['customer_id'];
        } else {
            return 0;
        }
    }

    public function update_customer($customer_id, $data)
    {
        $data = (object) $data;
        $this->db->where('customer_id', $customer_id);
        $this->db->update("customer", $data);
    }

    public function create_customer($data)
    {
        $data = (object) $data;
        $this->db->insert("customer", $data);

        if ($this->db->_error_message()) {
            show_error($this->db->_error_message());
        }

        $query = $this->db->query('select LAST_INSERT_ID( ) AS last_id');
        $result = $query->result_array();
        if (isset($result[0])) {
            return $result[0]['last_id'];
        } else {
            return null;
        }
    }

    function get_customer_info($customer_id)
	{
		if(is_array($customer_id))
    	{
    		$customer_ids_str = implode(",", $customer_id);
    		$where = " customer_id IN ($customer_ids_str) ";
    	}
    	else
    	{
    		$where = " customer_id = '$customer_id' ";
    	}
        $sql = "
			SELECT * FROM customer
			WHERE $where ;
		";
       
        $q = $this->db->query($sql);

		if ($this->db->_error_message())
		{
			show_error($this->db->_error_message());
		}

        //return result set as an associative array
        if ($q)
		{
			if(is_array($customer_id))
            	$result = $q->result_array();
            else
            	$result = $q->row_array(0);

            return $result;
		}

        return 0;
	}
	
}
