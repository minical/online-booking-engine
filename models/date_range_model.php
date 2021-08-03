<?php
class Date_range_model extends CI_Model
{

    public function __construct()
    {
        parent::__construct();
    }

    public function create_date_range($data)
    {

        $this->db->insert('date_range', $data);

        if ($this->db->_error_message()) {
            show_error($this->db->_error_message());
        } else {
            $query = $this->db->query('select LAST_INSERT_ID( ) AS last_id');
            $result = $query->result_array();
            if (isset($result[0])) {
                return $result[0]['last_id'];
            } else {
                return null;
            }
        }
    }

    public function create_date_range_x_rate($data)
    {

        $this->db->insert('date_range_x_rate', $data);

        if ($this->db->_error_message()) {
            show_error($this->db->_error_message());
        } else {
            $query = $this->db->query('select LAST_INSERT_ID( ) AS last_id');
            $result = $query->result_array();
            if (isset($result[0])) {
                return $result[0]['last_id'];
            } else {
                return null;
            }
        }
    }

}
