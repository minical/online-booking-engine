<?php
class Currency_model extends CI_Model
{

    public function __construct()
    {
        parent::__construct();
    }

    public function get_default_currency($company_id)
    {
        $this->db->select('c.default_currency_id, cu.*');
        $this->db->from('company as c, currency as cu');
        $this->db->where('c.company_id', $company_id);
        // must check this condition, because even if the company may have default_currency_id assigned, that currency and the company may not be linked
        $this->db->where('c.default_currency_id = cu.currency_id');
        $query = $this->db->get();

        if ($this->db->_error_message()) // error checking
        {
            show_error($this->db->_error_message());
        }

        if ($query->num_rows >= 1) {
            $result_array = $query->result_array();
            return $result_array[0];
        }

        return null;
    }
}
