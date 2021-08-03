<?php
class Rate_model extends CI_Model
{

    public function __construct()
    {
        parent::__construct();
    }

    // get rates between dates
    public function get_daily_rates($rate_plan_id, $date_start = "1970-01-01", $date_end = "2050-01-01", $room_type_id = 0)
    {
        // Fetch rate POST variables
        $rate_variables = array(
            "base_rate",
            "adult_1_rate",
            "adult_2_rate",
            "adult_3_rate",
            "adult_4_rate",
            "additional_adult_rate",
            "additional_child_rate",
            'minimum_length_of_stay',
            'maximum_length_of_stay',
            'minimum_length_of_stay_arrival',
            'maximum_length_of_stay_arrival',
            'closed_to_arrival',
            'closed_to_departure',
            'can_be_sold_online',
            'rate_id',
        );

        $rate_sql_string = "";
        foreach ($rate_variables as $var) {
            $rate_sql_string = $rate_sql_string . "
				,
				(
					SELECT
						r.$var

					FROM
						rate as r,
						date_range as dr,
						date_range_x_rate as drxr
					WHERE
						r.rate_plan_id = '$rate_plan_id' AND
						r.rate_id = drxr.rate_id AND
						r.$var IS NOT NULL AND
						dr.date_range_id = drxr.date_range_id AND
						dr.date_start <= di.date AND
						di.date <= dr.date_end AND
						#check for day of week
						(
							(dr.sunday = '1' AND DAYOFWEEK(di.date) = '" . SUNDAY . "') OR
							(dr.monday = '1' AND DAYOFWEEK(di.date) = '" . MONDAY . "') OR
							(dr.tuesday = '1' AND DAYOFWEEK(di.date) = '" . TUESDAY . "') OR
							(dr.wednesday = '1' AND DAYOFWEEK(di.date) = '" . WEDNESDAY . "') OR
							(dr.thursday = '1' AND DAYOFWEEK(di.date) = '" . THURSDAY . "') OR
							(dr.friday = '1' AND DAYOFWEEK(di.date) = '" . FRIDAY . "') OR
							(dr.saturday = '1' AND DAYOFWEEK(di.date) = '" . SATURDAY . "')
						)
					ORDER BY r.rate_id DESC
					LIMIT 0, 1
				) as " . $var . "
			";
        }

        $rate_sql = "
			select
				di.date,
				WEEKDAY(di.date) as day_of_week
				$rate_sql_string,
				rp.room_type_id,
				rp.rate_plan_id,
				rp.charge_type_id,
				rp.rate_plan_name
			from
				date_interval as di,
				rate_plan as rp
			where
				(
					(di.date >= '$date_start' AND di.date < '$date_end') OR
					(di.date = '$date_start' AND '$date_start' = '$date_end')
				) AND
				rp.rate_plan_id = '$rate_plan_id'
			group by di.date
		";

        if ($this->session->userdata('user_role') == "is_admin") {

            // Fetch supplied rate POST variables
            $rate_supplied_variables = array(
                "supplied_adult_1_rate",
                "supplied_adult_2_rate",
                "supplied_adult_3_rate",
                "supplied_adult_4_rate",
            );
            // Supplied Rate SQL
            $rate_supplied_sql_string = "";
            foreach ($rate_supplied_variables as $key => $supplied_var) {
                $rate_supplied_sql_string = $rate_supplied_sql_string . "
						,
						(
							SELECT
			                    rs.$supplied_var
							FROM
								date_range as dr,
			                    rate_supplied as rs,
			                    date_range_x_rate_supplied as drxrs
							WHERE
								rs.rate_supplied_id = drxrs.rate_supplied_id AND
								rs.rate_plan_id = '$rate_plan_id' AND
								rs.$supplied_var IS NOT NULL AND
								dr.date_range_id = drxrs.date_range_id AND
								dr.date_start <= di.date AND
								di.date <= dr.date_end AND
								#check for day of week
								(
									(dr.sunday = '1' AND DAYOFWEEK(di.date) = '" . SUNDAY . "') OR
									(dr.monday = '1' AND DAYOFWEEK(di.date) = '" . MONDAY . "') OR
									(dr.tuesday = '1' AND DAYOFWEEK(di.date) = '" . TUESDAY . "') OR
									(dr.wednesday = '1' AND DAYOFWEEK(di.date) = '" . WEDNESDAY . "') OR
									(dr.thursday = '1' AND DAYOFWEEK(di.date) = '" . THURSDAY . "') OR
									(dr.friday = '1' AND DAYOFWEEK(di.date) = '" . FRIDAY . "') OR
									(dr.saturday = '1' AND DAYOFWEEK(di.date) = '" . SATURDAY . "')
								)
							ORDER BY rs.rate_supplied_id DESC
							LIMIT 0, 1
						) as " . $supplied_var . "
					";
            }

            $rate_supplied_sql = "
					select
						di.date as supplied_date,
						WEEKDAY(di.date) as supplied_day_of_week
						$rate_supplied_sql_string
					from
						date_interval as di,
						rate_plan as rp
					where
						(
							(di.date >= '$date_start' AND di.date < '$date_end') OR
							(di.date = '$date_start' AND '$date_start' = '$date_end')
						) AND
						rp.rate_plan_id = '$rate_plan_id'
					group by supplied_date
					";

            $query = $this->db->query($rate_sql);

            $supplied_query = $this->db->query($rate_supplied_sql);

            if ($query->num_rows >= 1 && $supplied_query->num_rows >= 1) {
                $rate_result = $query->result_array();
                $rate_supplied_result = $supplied_query->result_array();
                $unique_array = array();
                foreach ($rate_result as $rates) {
                    foreach ($rate_supplied_result as $supplied_rates) {
                        if ($rates['date'] == $supplied_rates['supplied_date']) {

                            $rates['supplied_adult_1_rate'] = $supplied_rates['supplied_adult_1_rate'];
                            $rates['supplied_adult_2_rate'] = $supplied_rates['supplied_adult_2_rate'];
                            $rates['supplied_adult_3_rate'] = $supplied_rates['supplied_adult_3_rate'];
                            $rates['supplied_adult_4_rate'] = $supplied_rates['supplied_adult_4_rate'];
                        }
                    }
                    $unique_array[] = $rates;

                }
                return $unique_array;
            }
        } else {
            $query = $this->db->query($rate_sql);
            return $query->result_array();
        }
        return array();
    }

    public function create_rate($data)
    {
        $this->db->insert('rate', $data);
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
