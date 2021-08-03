<?php

class Company_model extends CI_Model
{

    public function __construct()
    {
        parent::__construct();
    }

    public function get_company($company_id, $filter = null)
    {
        if (isset($filter['get_last_action']) && $filter['get_last_action']) {
            $this->db->select('(DATEDIFF(NOW(), IF(la.last_action IS NULL, capi.creation_date, la.last_action))) as idle', false);
            $this->db->join("(SELECT
                            b.company_id,
                            MAX(bl.date_time) as last_action
                        FROM
                            booking as b, booking_log as bl
                        WHERE
                            b.booking_id = bl.booking_id
                        GROUP BY b.company_id) as la "
                , "la.company_id = c.company_id", "left");
        }
        $this->db->select('c.*, capi.*, up.*, cs.subscription_level, cs.limit_feature, cs.subscription_state, cs.payment_method, cs.subscription_id, cs.balance, u.email as owner_email, p.*, count(DISTINCT r.room_id) as number_of_rooms_actual,c.partner_id,IFNULL(wp.username,"Minical") as partner_name, cpg.selected_payment_gateway', false);
        $this->db->from('company as c');
        $this->db->join('company_admin_panel_info as capi', 'c.company_id = capi.company_id', 'left');
        $this->db->join('company_subscription as cs', 'c.company_id = cs.company_id', 'left');
        $this->db->join('company_payment_gateway as cpg', 'cpg.company_id = c.company_id', 'left');
        $this->db->join('user_permissions as up', "c.company_id = up.company_id and up.permission = 'is_owner'", 'left');
        $this->db->join('room as r', "r.company_id = c.company_id AND r.is_deleted != 1", 'left');
        $this->db->join('users as u', "up.user_id = u.id", 'left');
        $this->db->join('whitelabel_partner wp', "c.partner_id = wp.id", 'left');
        $this->db->join('user_profiles as p', 'up.user_id = p.user_id', 'left');
        $this->db->where('c.company_id', $company_id);
        $query = $this->db->get();

        if ($query->num_rows >= 1) {
            $result = $query->result_array();
            $result[0]['company_id'] = $company_id;
            return $result[0];
        }

        return null;
    }

    public function update_common_booking_engine_fields($company_id, $booking_engine_field_id, $data)
    {
        $this->db->where('id', $booking_engine_field_id);
        $this->db->where('company_id', $company_id);
        $this->db->delete('online_booking_engine_field');

        return $this->create_common_booking_engine_fields($data);
    }

    public function create_common_booking_engine_fields($data)
    {
        $this->db->insert('online_booking_engine_field', $data);
        return $this->db->insert_id();
    }

    public function update_company($company_id, $data)
    {
        $data = (object) $data;
        $this->db->where('company_id', $company_id);
        $this->db->update("company", $data);
    }

    public function get_common_booking_engine_fields($company_id)
    {
        $this->db->where('company_id', $company_id);
        $this->db->order_by('id', 'DESC');
        $query = $this->db->get('online_booking_engine_field');
        $response = array();
        if ($query->num_rows >= 1) {
            $result = $query->result_array();
            foreach ($result as $setting) {
                $response[$setting['id']] = $setting;
            }
        }
        return $response;
    }

    public function get_time_zone($company_id)
    {
        $this->db->where('company_id', $company_id);
        $this->db->select('time_zone');
        $query = $this->db->get('company');

        if ($query->num_rows >= 1) {
            $result = $query->result_array();
            return $result[0]['time_zone'];
        }

        return null;
    }

    public function get_company_api_permission($company_id, $key = null)
    {
        $is_key_avail = $key ? "k.key = '$key' AND" : '';

        $sql = "SELECT  k.*, kxc.* FROM  `key` as k
                LEFT JOIN key_x_company as kxc ON kxc.key_id = k.id
                WHERE
                    $is_key_avail
                    kxc.company_id = '$company_id' ";

        $query = $this->db->query($sql);
        $result = $query->result_array();

        return count($result) > 0 ? $result : null;
    }

    public function get_selling_date($company_id)
    {
        $selling_date = null;
        $this->db->select("selling_date");
        $this->db->where('company_id', $company_id);

        $q = $this->db->get("company");

        if ($this->db->_error_message()) // error checking
        {
            show_error($this->db->_error_message());
        }

        foreach ($q->result() as $row) {
            $selling_date = $row->selling_date;
        }
        return $selling_date;
    }

}
