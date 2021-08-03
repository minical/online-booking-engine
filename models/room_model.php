<?php

class Room_model extends CI_Model
{

    public function __construct()
    {
        // Call the Model constructor
        parent::__construct();
    }

    function get_room($room_id, $room_type_id = null) {
		if($room_id)
		{
			$sql = "
				SELECT *, rt.name as room_type_name 
				FROM room as r
				LEFT JOIN room_type as rt ON rt.id = r.room_type_id		
				WHERE r.room_id = '$room_id'
				";			
		}
		else
		{
			$sql = "
				SELECT rt.name as room_type_name 
				FROM room_type as rt
				WHERE rt.id = '$room_type_id'
				";
		}
        $q = $this->db->query($sql);
		//echo $this->db->last_query();
        // return result set as an associative array
        $result = $q->row_array(0);
		
		return $result;

	}
	

	// get available rooms based on date and roomtype
    // include room already selected for booking ($booking_id)
    // ignore rooms that have state lower than 3. (not reservation, checkin, nor checkout)
	public function get_available_rooms(
						$check_in_date = null,
						$check_out_date = null,
						$room_type_id = null,
						$booking_id = null,
						$company_id = null,
						$can_be_sold_online = 0,
						$adults_count = null,
						$children_count = null,
						$room_id = null,
						$company_group_id = null
    ) {

        $room_list = "";

        $room_type_sql = '';
        if ($room_type_id) {
            $room_type_sql = "r.room_type_id = '" . $room_type_id . "' AND";
        }

        if (!$company_id) {
            $company_id = $this->session->userdata('current_company_id');
        }

        $company_sql_where_condition = " r.company_id = '$company_id' AND ";
        $company_select_sql = "";
        if ($company_group_id) {
            $company_select_sql = "company_groups_x_company as cgxc,";
            $company_sql_where_condition = " cgxc.company_id = rt.company_id AND cgxc.company_group_id = $company_group_id AND ";
        }

        $can_be_sold_online_sql = '';
        if ($can_be_sold_online == 1) {
            $can_be_sold_online_sql = "r.can_be_sold_online = '1' AND";
        }

        $room_id_sql = "";
        if ($room_id) {
            $room_id_sql = "brh.room_id != '$room_id' AND";
        }

        $max_adult_sql = '';
        $max_children_sql = '';
        if ($adults_count && $children_count) {
            $max_adult_sql = "rt.max_adults >= '$adults_count' AND";
            $max_children_sql = "rt.max_children + (rt.max_adults - '$adults_count') >= '$children_count' AND";
        }

        $sql = "SELECT
					DISTINCT r.room_id, r.room_name as room_name, r.status, r.room_type_id, rt.acronym
					FROM room_type as rt, $company_select_sql room as r
					LEFT JOIN
					(
					SELECT  brh.room_id, brh.booking_id
					FROM booking as b, booking_block as brh
					WHERE
					(
					brh.check_out_date > '$check_in_date' AND '$check_out_date' > brh.check_in_date
					) AND #include currently selected room in the available room list
					b.booking_id = brh.booking_id AND
					b.booking_id != '$booking_id' AND
					$room_id_sql
					(b.state < 4 OR b.state = 7) AND
					b.is_deleted != '1' AND
					b.company_id = '$company_id' AND
					brh.check_in_date < brh.check_out_date
					)ot
					ON
					r.room_id = ot.room_id

					WHERE
					$company_sql_where_condition
					r.is_deleted = '0' AND
					rt.id = r.room_type_id AND
					$room_type_sql
					$can_be_sold_online_sql
					$max_adult_sql
					$max_children_sql
					ot.booking_id IS NULL
					ORDER BY r.room_name
					";

        $data = array();
        $query_result = $this->db->query($sql);
        if ($this->db->_error_message()) // error checking
        {
            show_error($this->db->_error_message());
        }

        return $query_result->result_array();

    }

}
