<?php

class Extra_model extends CI_Model
{

    public function __construct()
    {
        parent::__construct();
    }

    public function get_rate_plan_extras($rate_plan_id, $room_type_id = null)
    {
        $where_condition = "";
        if ($room_type_id) {
            $where_condition = " AND rpxe.room_type_id = '$room_type_id'";
        }

        $sql = "
			SELECT
				*
			FROM
				extra as e
			LEFT JOIN rate_plan_x_extra as rpxe ON e.extra_id = rpxe.extra_id
			LEFT JOIN extra_rate as er ON e.extra_id = er.extra_id
			WHERE
				rpxe.rate_plan_id = '$rate_plan_id'
				$where_condition
			GROUP BY e.extra_id
		";

        $query = $this->db->query($sql);
        if ($this->db->_error_message()) // error checking
        {
            show_error($this->db->_error_message());
        }

        if ($query->num_rows >= 1) {
            return $query->result_array();
        }
        return null;
    }

    // temporary method to retrieve default rate.
    // This isn't a good way of handling this, because extra_rates are date dependent.
    // Eventually, I'll have to implement Rate_Type & Rate style to extra_rates.
    public function get_extra_default_rate($extra_id)
    {
        $this->db->where('extra_id', $extra_id);
        $this->db->order_by("extra_rate_id", "desc");

        $query = $this->db->get('extra_rate');

        if ($query->num_rows >= 1) {
            $result = $query->result_array();
            return $result[0]['rate'];
        }

        return null;
    }
}
