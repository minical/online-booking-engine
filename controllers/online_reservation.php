<?php

class Online_reservation extends MY_Controller
{
    function __construct()
    {
        
        parent::__construct();
        $this->module_name = $this->router->fetch_module();
 
        $this->load->model('../extensions/'.$this->module_name.'/models/Booking_room_history_model');
        $this->load->model('../extensions/'.$this->module_name.'/models/Booking_model');
        $this->load->model('../extensions/'.$this->module_name.'/models/Booking_log_model');
        $this->load->model('../extensions/'.$this->module_name.'/models/Currency_model');
        $this->load->model('../extensions/'.$this->module_name.'/models/Company_model');
        $this->load->model('../extensions/'.$this->module_name.'/models/Customer_model');
        $this->load->model('../extensions/'.$this->module_name.'/models/Date_range_model');
        $this->load->model('../extensions/'.$this->module_name.'/models/Extra_model');
        $this->load->model('../extensions/'.$this->module_name.'/models/Invoice_model');
        $this->load->model('../extensions/'.$this->module_name.'/models/Room_type_model');
        $this->load->model('../extensions/'.$this->module_name.'/models/Rate_plan_model');
        $this->load->model('../extensions/'.$this->module_name.'/models/Rate_model');
        $this->load->model('../extensions/'.$this->module_name.'/models/Room_model');
        $this->load->model('../extensions/'.$this->module_name.'/models/Tax_model');
        
        $this->load->library('PHPRequests');
        $this->load->library('email_template');
        $this->load->helper('date_format_helper');
        
        $this->load->helper('url');
        $this->ci->load->helper('language_translation_helper');
    }

    //function should pass in a unique hash parameter that represents a company
    //for now using company_id as the parameter
    function widget($company_id = null)
    {
        $data = array();

        $data['css_files'] = array(
            base_url().auto_version('css/widget.css')
        );

        $data['js_files'] = array(
            base_url().'js/moment.min.js',
            base_url().auto_version('js/online-reservation.js')
        );

        $time_zone                  = $this->Company_model->get_time_zone($company_id);
        $now                        = new DateTime('now', new DateTimeZone($time_zone));
        $data['view_data']['today'] = $now->format('Y-m-d');

        $interval = new DateInterval('P1D');
        $now->add($interval);
        $data['view_data']['tomorrows_date'] = $now->format('Y-m-d');

        $now->add($interval);
        $data['view_data']['following_tomorrows_date'] = $now->format('Y-m-d');
        $data['main_content'] = 'online_reservation/widget';
        $this->load->view('includes/widget_template', $data);
    }

    /*
     * Get rooms based on dates adults and children count.
     * */
    function select_dates_and_rooms($company_id = 0)
    {
        $company_data = $this->Company_model->get_company($company_id);
        if (is_null($company_data)) {
            echo l("Company doesn't exist", true);

            return;
        }

        // Set the white label session for the online bookings
        $host_name = $_SERVER['HTTP_HOST'];
        $white_label_name = explode('.', $host_name);
        if(count($white_label_name) > 0)
        {
            $white_label_name = $white_label_name[0];
        }
        $data['whitelabel_detail'] = '';
        $white_label_detail = $this->Whitelabel_partner_model->get_partners(array('username' => $white_label_name));
        if($white_label_detail)
        {
            $white_label_detail = $white_label_detail[0];
            $data['whitelabel_detail'] = $white_label_detail;
        }
        else
        {
            $white_label_detail = $this->Whitelabel_partner_model->get_partners(array('domain' => $host_name));
            if($white_label_detail)
            {
                $white_label_detail = $white_label_detail[0];
                $data['whitelabel_detail'] = $white_label_detail;
            }
        }
        if($data['whitelabel_detail'])
        {
            $this->session->set_userdata('white_label_information', $white_label_detail);
        } else {

            $partner_id = $company_data['partner_id'];

            $condition = array('id' => $partner_id);
            $white_label_detail = $this->Whitelabel_partner_model->get_partners($condition);
            if($white_label_detail)
            {
                $this->session->set_userdata('white_label_information', $white_label_detail[0]);
            }
        }

        $time_zone = $this->Company_model->get_time_zone($company_id);

        $now       = new DateTime('now', new DateTimeZone($time_zone));
        $data['view_data']['today'] = $now->format('Y-m-d');
        $interval  = new DateInterval('P1D');
        $now->add($interval);
        $data['view_data']['tomorrows_date'] = $now->format('Y-m-d');
        $now->add($interval);
        $data['view_data']['following_tomorrows_date'] = $now->format('Y-m-d');

        //Load validation
        $this->load->library('form_validation');
        $check_in_date  = sqli_clean($this->security->xss_clean($this->input->post('check-in-date', TRUE)));
        $check_out_date = sqli_clean($this->security->xss_clean($this->input->post('check-out-date', TRUE)));
        $length_of_stay = floor((abs(strtotime($check_out_date) - strtotime($check_in_date))) / (60 * 60 * 24));

        $this->form_validation->set_rules(
            'check-in-date',
            'Check-in Date',
            'required|trim|callback_date_format_check|callback_is_check_in_date_after_today['.$company_id.']'
        );
        $this->form_validation->set_rules(
            'check-out-date',
            'Check-Out Date',
            'required|trim|callback_date_format_check|callback_is_check_out_date_after_check_in_date['.$check_in_date.']'
        );
        $this->form_validation->set_rules(
            'number-of-rooms',
            'Number of Rooms',
            'trim'
        );
        $this->form_validation->set_rules(
            'adult_count',
            'Adults',
            'trim'
        );
        $this->form_validation->set_rules(
            'children_count',
            'Children',
            'trim'
        );

        $data['show_error'] = false;

        $http_origin = (isset($_SERVER['HTTP_ORIGIN']) && $_SERVER['HTTP_ORIGIN']) ? $_SERVER['HTTP_ORIGIN'] : '';
        $origin = preg_replace('#^https?://#', '', $http_origin); // get booking source

        if ($this->form_validation->run() == true) {
            $adult_count = sqli_clean($this->security->xss_clean($this->input->post('adult_count', TRUE)));
            if (is_null($adult_count)) {
                $adult_count = null;
            }

            $children_count = sqli_clean($this->security->xss_clean($this->input->post('children_count', TRUE)));
            if (is_null($children_count)) {
                $children_count = null;
            }

            $number_of_rooms_requested = sqli_clean($this->security->xss_clean($this->input->post('number-of-rooms', TRUE)));

            // TODO: make the ota ID dynamic
            $company_key_data = $this->Company_model->get_company_api_permission($company_id);
            $company_access_key = isset($company_key_data[0]['key']) && $company_key_data[0]['key'] ? $company_key_data[0]['key'] : null;

            $ota_id = apply_filters('get_ota_id', 'obe');
            $ota_id = $ota_id ? $ota_id : SOURCE_ONLINE_WIDGET;

            $available_room_types = $this->Room_type_model->get_room_type_availability($company_id, $ota_id, $check_in_date, $check_out_date, $adult_count, $children_count, true, null, true, true, true, true, $company_access_key, 'obe');

            $total_availability   = 0;
            $available_rate_plans = $rooms_available = $unavailable_room_types = array();
                // prx($available_room_types);
            foreach ($available_room_types as $key => $available_room_type) {
                unset($available_room_types[$key]['description']);
                unset($available_room_type['description']);

                $minimum_avaialbility_of_current_room_type = 999;
                if (isset($available_room_type['availability']) && $available_room_type['availability'] && count($available_room_type['availability']) > 0) {
                    foreach ($available_room_type['availability'] as $availability)
                    {
                        if (
                            ($minimum_avaialbility_of_current_room_type > $availability['availability']) &&
                            ($availability['date_start'] != $availability['date_end'])
                        )
                        {
                            $minimum_avaialbility_of_current_room_type = $availability['availability'];
                        }
                    }
                } else {
                    $minimum_avaialbility_of_current_room_type = 0;
                }

                $number_of_rooms_requested = 1;
                if ($minimum_avaialbility_of_current_room_type < $number_of_rooms_requested)
                {
                    // continue;
                    $unavailable_room_types[] = array ('id' => $available_room_type['id']);
                }

                $rate_plans = $this->Rate_plan_model->get_rate_plans_by_room_type_id($available_room_type['id']);

                if (isset($rate_plans)) {

                    foreach ($rate_plans as $rate_plan) {
                        if ($rate_plan['is_shown_in_online_booking_engine'] == '1')
                        {

                            $rates = $this->Rate_model->get_daily_rates($rate_plan['rate_plan_id'], $check_in_date, $check_out_date);

                            $passed_all_restrictions = true;

                            foreach ($rates as $rate) {
                                if ($rate['can_be_sold_online'] != '1') {
                                    $passed_all_restrictions = false;
                                }

                                if ($rate['minimum_length_of_stay'] > $length_of_stay && isset($rate['minimum_length_of_stay'])) {
                                    $rate_plan['min_length']="This room requires minimum ".$rate['minimum_length_of_stay']." nights of stay";
                                }

                                if ($rate['maximum_length_of_stay'] < $length_of_stay && isset($rate['maximum_length_of_stay'])) {
                                    $rate_plan['max_length']="This room requires maximum ".$rate['maximum_length_of_stay']." nights of stay";
                                }

                                if (
                                    $rate['date'] == $check_in_date &&
                                    $rate['closed_to_arrival'] == '1'
                                ) {
                                    $rate_plan['arrival']="please enable close to arrival on selected date";
                                }
                            }

                            $checkout_date = date('Y-m-d', strtotime($check_out_date . " + 1 day"));

                            $rates = $this->Rate_model->get_daily_rates($rate_plan['rate_plan_id'], $check_in_date, $checkout_date);

                            foreach ($rates as $rate) {
                                if (
                                    $rate['date'] == $check_out_date &&
                                    $rate['closed_to_departure'] == '1'
                                ) {
                                    $rate_plan['departure']="please enable close to departure on selected date";
                                }
                            }

                            if ($passed_all_restrictions) {
                                $rate_plan['room_type_image_group_id']    = $available_room_type['image_group_id'];

                                unset($rate_plan['description']);

                                $rate_plan['max_adults'] = $available_room_type['max_adults'];
                                $available_rate_plans[] = $rate_plan;
                            }
                        }
                    }
                }
            }

            ksort($available_rate_plans);
            $date_start     = $check_in_date;
            $date_end       = $check_out_date;
            //Calculate default rates
            $is_available_rate_plan = $is_available_room = false;
            $best_available_rate = -1;

            $rate_plan_ids = array();
            // fetch rate plan description $rate_plan_ids

            foreach ($available_rate_plans as $key => $rate_plan) {

                $this->load->library('rate');
                $rate_array = $this->rate->get_rate_array(
                    $rate_plan['rate_plan_id'],
                    $date_start,
                    $date_end,
                    $adult_count,
                    $children_count
                );

                $average_daily_rate = $this->rate->get_average_daily_rate($rate_array);
                $available_rate_plans[$key]['average_daily_rate'] = $average_daily_rate;
                if ($best_available_rate == -1 || $average_daily_rate < $best_available_rate) {
                    $best_available_rate = $average_daily_rate;
                }
                $rate_plan_ids[] = $rate_plan['rate_plan_id'];
                if($average_daily_rate > 0 || ($company_data['allow_free_bookings'] && (!$rate_plan['charge_type_id'] || $rate_plan['charge_type_id'] == '0')))
                {
                    $is_available_rate_plan = true;
                }

                if($this->Room_model->get_available_rooms(
                    $check_in_date,
                    $check_out_date,
                    $rate_plan['room_type_id'],
                    null,
                    $company_id,
                    1
                ))
                {
                    $is_available_room = true;
                }
            }

            $data['view_data']['best_available_rate'] = number_format($best_available_rate, 2, ".", ",");

            $data['view_data']['default_currency'] = $this->Currency_model->get_default_currency($company_id);

            $data['view_data']['check_in_date']             = $check_in_date;
            $data['view_data']['check_out_date']            = $check_out_date;
            $data['view_data']['adult_count']               = $adult_count;
            $data['view_data']['children_count']            = $children_count;
            $data['view_data']['available_rate_plans']      = $available_rate_plans;

            $data['view_data']['unavailable_room_types']    = $unavailable_room_types;


            if (isset($_GET['dev_mode'])) {
                echo l('session set_userdata: <pre>',true);print_r($data);
            }

            $data['view_data']['booking_source'] = $origin;
            $this->session->set_userdata($data);

            if (isset($_GET['dev_mode'])) {

                $session_data = $this->session->all_userdata();

                echo l('Data is all set. get session all_userdata: <pre>',true);print_r($session_data);
            }

            $data['company_data'] = $company_data;

            // set session variables
            unset($data['company_data']['reservation_policies']);
            unset($data['company_data']['check_in_policies']);
            unset($data['company_data']['invoice_email_header']);
            unset($data['company_data']['booking_confirmation_email_header']);

            $descriptions = $this->Rate_plan_model->get_rate_plan_descriptions($rate_plan_ids);
            foreach($data['view_data']['available_rate_plans'] as $key => $rate_plan)
            {
                $data['view_data']['available_rate_plans'][$key]['description'] = isset($descriptions[$rate_plan['rate_plan_id']]) ? $descriptions[$rate_plan['rate_plan_id']] : "";
            }

            foreach($data['view_data']['available_rate_plans'] as $key => $rate_plan)
            {
                $room_type_images = $this->Image_model->get_images($rate_plan['room_type_image_group_id']);
                $data['view_data']['available_rate_plans'][$key]['images']    = $room_type_images;
            }

            if($is_available_rate_plan && $is_available_room)
            {
                redirect('/online_reservation/show_reservations/'.$this->uri->segment(3));
            }
            else
            {
                $data['show_error'] = true;
                $data['current_step'] = 1;
                $data['selling_date'] = $company_data['selling_date'];

                $files = get_asstes_files($this->module_assets_files, $this->module_name, $this->controller_name, $this->function_name);

                $data['main_content'] = '../extensions/'.$this->module_name.'/views/select_dates_and_rooms';

                $this->template->load('online_reservation_template', null , $data['main_content'], $data);
            }
        }
        else
        {
            $data['company_data'] = $company_data;

            // set session variables
            unset($data['company_data']['reservation_policies']);
            unset($data['company_data']['check_in_policies']);
            unset($data['company_data']['invoice_email_header']);
            unset($data['company_data']['booking_confirmation_email_header']);

            $room_type_occupancy = $this->Room_type_model->get_max_room_type_occupancy($company_id);

            $data['max_occupancy'] = $room_type_occupancy['max_occupancy'];

            $data['current_step'] = 1;
            $data['selling_date'] = $company_data['selling_date'];
            $data['main_content'] = '../extensions/'.$this->module_name.'/views/select_dates_and_rooms';
            $this->template->load('online_reservation_template', null , $data['main_content'], $data);
        }
    }

    function check_room_type_availability()
    {

        $company_id = sqli_clean($this->security->xss_clean($this->input->post('company_id', TRUE)));

        $company_data = $this->Company_model->get_company($company_id);
        if (is_null($company_data)) {
            echo l("Company doesn't exist", true);
            return;
        }
        $check_in_date = sqli_clean($this->security->xss_clean($this->input->post('start_date', TRUE)));
        $check_out_date = sqli_clean($this->security->xss_clean($this->input->post('end_date', TRUE)));
        $adult_count = sqli_clean($this->security->xss_clean($this->input->post('adult_count', TRUE)));
        $children_count = sqli_clean($this->security->xss_clean($this->input->post('children_count', TRUE)));

        $length_of_stay = floor((abs(strtotime($check_out_date) - strtotime($check_in_date))) / (60 * 60 * 24));

        $company_key_data = $this->Company_model->get_company_api_permission($company_id);
        $company_access_key = isset($company_key_data[0]['key']) && $company_key_data[0]['key'] ? $company_key_data[0]['key'] : null;

        $ota_id = apply_filters('get_ota_id', 'obe');
        $ota_id = $ota_id ? $ota_id : SOURCE_ONLINE_WIDGET;

        $available_room_types = $this->Room_type_model->get_room_type_availability($company_id, $ota_id, $check_in_date, $check_out_date, $adult_count, $children_count, true, null, true, true, true, true, $company_access_key, 'obe');

        $total_availability   = 0;
        $available_rate_plans = $rooms_available = $unavailable_room_types = array();
            // prx($available_room_types);
        foreach ($available_room_types as $key => $available_room_type) {
            unset($available_room_types[$key]['description']);
            unset($available_room_type['description']);

            $minimum_avaialbility_of_current_room_type = 999;
            if (isset($available_room_type['availability']) && $available_room_type['availability'] && count($available_room_type['availability']) > 0) {
                foreach ($available_room_type['availability'] as $availability)
                {
                    if (
                        ($minimum_avaialbility_of_current_room_type > $availability['availability']) &&
                        ($availability['date_start'] != $availability['date_end'])
                    )
                    {
                        $minimum_avaialbility_of_current_room_type = $availability['availability'];
                    }
                }
            } else {
                $minimum_avaialbility_of_current_room_type = 0;
            }

            $number_of_rooms_requested = 1;
            if ($minimum_avaialbility_of_current_room_type < $number_of_rooms_requested)
            {
                // continue;
                $unavailable_room_types[] = array ('id' => $available_room_type['id']);
            }

            $rate_plans = $this->Rate_plan_model->get_rate_plans_by_room_type_id($available_room_type['id']);

            if (isset($rate_plans)) {

                foreach ($rate_plans as $rate_plan) {
                    if ($rate_plan['is_shown_in_online_booking_engine'] == '1')
                    {

                        $rates = $this->Rate_model->get_daily_rates($rate_plan['rate_plan_id'], $check_in_date, $check_out_date);

                        $passed_all_restrictions = true;

                        foreach ($rates as $rate) {
                            if ($rate['can_be_sold_online'] != '1') {
                                $passed_all_restrictions = false;
                            }

                            if ($rate['minimum_length_of_stay'] > $length_of_stay && isset($rate['minimum_length_of_stay'])) {
                                $rate_plan['min_length']="This room requires minimum ".$rate['minimum_length_of_stay']." nights of stay";
                            }

                            if ($rate['maximum_length_of_stay'] < $length_of_stay && isset($rate['maximum_length_of_stay'])) {
                                $rate_plan['max_length']="This room requires maximum ".$rate['maximum_length_of_stay']." nights of stay";
                            }

                            if (
                                $rate['date'] == $check_in_date &&
                                $rate['closed_to_arrival'] == '1'
                            ) {
                                $rate_plan['arrival']="please enable close to arrival on selected date";
                            }
                        }

                        $checkout_date = date('Y-m-d', strtotime($check_out_date . " + 1 day"));

                        $rates = $this->Rate_model->get_daily_rates($rate_plan['rate_plan_id'], $check_in_date, $checkout_date);

                        foreach ($rates as $rate) {
                            if (
                                $rate['date'] == $check_out_date &&
                                $rate['closed_to_departure'] == '1'
                            ) {
                                $rate_plan['departure']="please enable close to departure on selected date";
                            }
                        }

                        if ($passed_all_restrictions) {
                            $rate_plan['room_type_image_group_id']    = $available_room_type['image_group_id'];

                            unset($rate_plan['description']);

                            $rate_plan['max_adults'] = $available_room_type['max_adults'];
                            $available_rate_plans[] = $rate_plan;
                        }
                    }
                }
            }
        }

        ksort($available_rate_plans);
        $date_start     = $check_in_date;
        $date_end       = $check_out_date;
        //Calculate default rates
        $is_available_rate_plan = $is_available_room = false;
        $best_available_rate = -1;

        $rate_plan_ids = array();
        // fetch rate plan description $rate_plan_ids

        foreach ($available_rate_plans as $key => $rate_plan) {

            $this->load->library('rate');
            $rate_array = $this->rate->get_rate_array(
                $rate_plan['rate_plan_id'],
                $date_start,
                $date_end,
                $adult_count,
                $children_count
            );

            $average_daily_rate = $this->rate->get_average_daily_rate($rate_array);
            $available_rate_plans[$key]['average_daily_rate'] = $average_daily_rate;
            if ($best_available_rate == -1 || $average_daily_rate < $best_available_rate) {
                $best_available_rate = $average_daily_rate;
            }
            $rate_plan_ids[] = $rate_plan['rate_plan_id'];
            if($average_daily_rate > 0 || ($company_data['allow_free_bookings'] && (!$rate_plan['charge_type_id'] || $rate_plan['charge_type_id'] == '0')))
            {
                $is_available_rate_plan = true;
            }

            if($this->Room_model->get_available_rooms(
                $check_in_date,
                $check_out_date,
                $rate_plan['room_type_id'],
                null,
                $company_id,
                1
            ))
            {
                $is_available_room = true;
            }
        }

        $data['view_data']['best_available_rate'] = number_format($best_available_rate, 2, ".", ",");

        $data['view_data']['default_currency'] = $this->Currency_model->get_default_currency($company_id);

        $data['view_data']['check_in_date']             = $check_in_date;
        $data['view_data']['check_out_date']            = $check_out_date;
        $data['view_data']['adult_count']               = $adult_count;
        $data['view_data']['children_count']            = $children_count;
        $data['view_data']['available_rate_plans']      = $available_rate_plans;

        $data['view_data']['unavailable_room_types']    = $unavailable_room_types;


        if (isset($_GET['dev_mode'])) {
            echo l('session set_userdata: <pre>',true);print_r($data);
        }

        $this->session->set_userdata($data);

        if (isset($_GET['dev_mode'])) {

            $session_data = $this->session->all_userdata();

            echo l('Data is all set. get session all_userdata: <pre>',true);print_r($session_data);
        }

        $data['company_data'] = $company_data;

        // set session variables
        unset($data['company_data']['reservation_policies']);
        unset($data['company_data']['check_in_policies']);
        unset($data['company_data']['invoice_email_header']);
        unset($data['company_data']['booking_confirmation_email_header']);

        $descriptions = $this->Rate_plan_model->get_rate_plan_descriptions($rate_plan_ids);
        foreach($data['view_data']['available_rate_plans'] as $key => $rate_plan)
        {
            $data['view_data']['available_rate_plans'][$key]['description'] = isset($descriptions[$rate_plan['rate_plan_id']]) ? $descriptions[$rate_plan['rate_plan_id']] : "";
        }

        foreach($data['view_data']['available_rate_plans'] as $key => $rate_plan)
        {
            $room_type_images = $this->Image_model->get_images($rate_plan['room_type_image_group_id']);
            $data['view_data']['available_rate_plans'][$key]['images']    = $room_type_images;
        }

        if($is_available_rate_plan && $is_available_room)
        {
            echo json_encode(array('success' => true, 'company_id' => $company_id));
        }
        else
        {
            echo json_encode(array('success' => false, 'msg' => l('No rooms available on the selected dates. Please try changing the dates.', true)));
        }

    }

    /*
     * Get all reservations based on current company id.
     * */
    function show_reservations($company_id)
    {
        $company_data = $this->Company_model->get_company($company_id);
        if (is_null($company_data)) {
            echo l("Company doesn't exist", true);
            return;
        }

        $session_data = $this->session->all_userdata();

        if (
            isset($session_data['view_data']['adult_count']) &&
            isset($session_data['view_data']['children_count']) &&
            isset($session_data['view_data']['check_in_date']) &&
            isset($session_data['view_data']['check_out_date'])
        )
        {
            $data = array('view_data' => $session_data['view_data']);
        }
        else
        {
            if (isset($_GET['dev_mode'])) {
                echo l('session all_userdata: <pre>', true);print_r($session_data);die;
            }

            redirect("/online_reservation/select_dates_and_rooms/".$company_id);
        }

        $data['view_data']['default_currency'] = $this->Currency_model->get_default_currency($company_id);

        //Calculate default rates
        $adult_count    = $data['view_data']['adult_count'];
        $children_count = $data['view_data']['children_count'];

        $date_start     = $data['view_data']['check_in_date'];
        $date_end       = $data['view_data']['check_out_date'];

        $average_rates       = array();
        $best_available_rate = -1;

        $rate_plan_ids = array();

        foreach ($data['view_data']['available_rate_plans'] as $key => $rate_plan) {

            $this->load->library('rate');
            $rate_array = $this->rate->get_rate_array(
                $rate_plan['rate_plan_id'],
                $date_start,
                $date_end,
                $adult_count,
                $children_count
            );
            $average_daily_rate = $this->rate->get_average_daily_rate($rate_array);
            $data['view_data']['available_rate_plans'][$key]['average_daily_rate'] = $average_daily_rate;
            if ($best_available_rate == -1 || $average_daily_rate < $best_available_rate) {
                $best_available_rate = $average_daily_rate;
            }
            $rate_plan_ids[] = $rate_plan['rate_plan_id'];
        }

        $data['view_data']['best_available_rate'] = number_format($best_available_rate, 2, ".", ",");

        $this->load->library('form_validation');
        $rate_plan_selected_ids = sqli_clean($this->security->xss_clean($this->input->post('rate-plan-selected-ids', TRUE)));

        $this->form_validation->set_rules(
            'rate-plan-selected[]',
            'Rate plans selected',
            'trim'
        );

        if ($this->form_validation->run() == true) {
            unset($data['view_data']['selected_rate_plans']);
            $data['view_data']['rate_plan_selected_ids'] = $rate_plan_selected_ids;
            foreach ($data['view_data']['available_rate_plans'] as $rate_plan) {
                foreach ($rate_plan_selected_ids as $selected_rate_plan_id) {
                    if ($selected_rate_plan_id == $rate_plan['rate_plan_id']) {
                        $data['view_data']['selected_rate_plans'][] = $rate_plan;
                    }
                }
            }
        }

        $data['view_data']['rate_plan_extra'] = $this->input->post('rate_plan_extra');
        
        //Runs this page again until the number of rooms selected == number of rooms requested
        //Information is stored in hidden inputs in the view page
        if (isset($data['view_data']['rate_plan_selected_ids'])) {
            // the condition is set for >= (greater than or equal), in case number_of_rooms_requested
            // is not set (That variable is dependent on widget's form)
            if (sizeof($data['view_data']['rate_plan_selected_ids']) >= $data['view_data']['number_of_rooms_requested']) {

                // unset stuff that we don't need in next steps
                $data['view_data']['available_rate_plans'] = $data['view_data']['selected_rate_plans'];
                $this->session->set_userdata($data);

                redirect('/online_reservation/book_reservation/'.$this->uri->segment(3));
                return;
            }
        }



        $this->session->set_userdata($data);

        $data['company_data'] = $company_data;

        // fetch rate plan description $rate_plan_ids
        $descriptions = $this->Rate_plan_model->get_rate_plan_descriptions($rate_plan_ids);
        foreach($data['view_data']['available_rate_plans'] as $key => $rate_plan)
        {
            $data['view_data']['available_rate_plans'][$key]['description'] = isset($descriptions[$rate_plan['rate_plan_id']]) ? $descriptions[$rate_plan['rate_plan_id']] : "";
        }

        foreach($data['view_data']['available_rate_plans'] as $key => $rate_plan)
        {
            $room_type_images = $this->Image_model->get_images($rate_plan['room_type_image_group_id']);
            $data['view_data']['available_rate_plans'][$key]['images'] = $room_type_images;
            // get rate_plan_extras
            $data['view_data']['available_rate_plans'][$key]['extras'] = $this->Extra_model->get_rate_plan_extras($rate_plan['rate_plan_id']);
        }

        // $data['js_files'] = array(
        //     base_url().'js/moment.min.js',
        //     base_url().auto_version('js/online-reservation.js')
        // );

        $data['css_files'] = array(
            'https://maxcdn.bootstrapcdn.com/font-awesome/4.7.0/css/font-awesome.min.css'
        );

        $data['current_step'] = 2;
        
         $files = get_asstes_files($this->module_assets_files, $this->module_name, $this->controller_name, $this->function_name);

                $data['main_content'] = '../extensions/'.$this->module_name.'/views/show_reservations';

                $this->template->load('online_reservation_template', null , $data['main_content'], $data);
  
    }

    function book_reservation($company_id)
    {
        $company_data = $this->Company_model->get_company($company_id);

        if (is_null($company_data)) {
            echo l("Company doesn't exist", true);

            return;
        }

        $this->load->library(
            'PaymentGateway',
            array(
                'company_id' => $company_id
            )
        );

        $session_data = $this->session->all_userdata(); // all online reservation information (such as room types, dates, etc... are stored in session
        $data         = array('view_data' => $session_data['view_data']);

        $rate_plan_extra = json_decode($data['view_data']['rate_plan_extra'], true);

        $data['company_data']                  = $company_data;
        $data['view_data']['number_of_nights'] = (strtotime($data['view_data']['check_out_date']) - strtotime($data['view_data']['check_in_date'])) / (60 * 60 * 24);


        $data['view_data']['reservation_policy'] = $company_data['reservation_policies'];
        $sub_total                               = 0;
        foreach ($data['view_data']['selected_rate_plans'] as $selected_rate_plan) {
            foreach ($data['view_data']['available_rate_plans'] as $available_rate_plan) {
                if ($selected_rate_plan['rate_plan_id'] == $available_rate_plan['rate_plan_id']) {
                    $rate_plan = $available_rate_plan;
                    $sub_total += $available_rate_plan['average_daily_rate'] * $data['view_data']['number_of_nights'];
                }
            }
        }
        unset($data['company_data']['reservation_policies']);
        unset($data['company_data']['check_in_policies']);
        unset($data['company_data']['invoice_email_header']);
        unset($data['company_data']['booking_confirmation_email_header']);

        $total_tax_percentage            = $this->Tax_model->get_total_tax_percentage_by_charge_type_id($rate_plan['charge_type_id']);
        $total_flat_rate_tax             = $this->Tax_model->get_total_tax_flat_rate_by_charge_type_id($rate_plan['charge_type_id']) * $data['view_data']['number_of_nights'];
        $data['view_data']['sub_total']  = $sub_total;
        $data['view_data']['tax_amount'] = $tax_amount = ($sub_total * $total_tax_percentage * 0.01) + $total_flat_rate_tax;
        $data['view_data']['total']      = $data['view_data']['sub_total'] + $data['view_data']['tax_amount'];
        
        $new_array = array();

        if($rate_plan_extra && count($rate_plan_extra) > 0){
            foreach ($rate_plan_extra as $key => $value) {
                if($rate_plan['rate_plan_id'] != $value['rate_plan_id'])
                {
                    unset($rate_plan_extra[$key]);
                }
            }

            foreach($rate_plan_extra as $key =>$value) {
                if(!array_key_exists($value['extra_id'], $new_array)){
                    $new_array[$value['extra_id']] = 0;
                }
                $new_array[$value['extra_id']] = $new_array[$value['extra_id']] + 1;
            }
        }

        $grand_extra_total = 0;
        $extra_sub_total = 0;
        $extra_tax_amount = 0;
        $extra_charges = 0;
        
        if($rate_plan_extra && count($rate_plan_extra) > 0){
            $get_amount_only = true;
            
            foreach ($rate_plan_extra as $extra) {

                $extra['start_date'] = $data['view_data']['check_in_date'];
                $extra['end_date'] = $data['view_data']['check_out_date'];
                $extra['rate'] = $extra['amount'];
                $extra['quantity'] = $extra['quantity'];
                
                $current_selling_date = $this->Company_model->get_selling_date($company_id);
                
                $tax_rates = $this->Tax_model->get_tax_rates_by_charge_type_id($extra['charge_type_id'], $company_id, $extra['rate']);
                
                $date_start = Date('Y-m-d', max(strtotime($current_selling_date), strtotime($extra['start_date'])));
                $date_end = $extra['end_date'];
                if  (
                    ($extra['charging_scheme'] == 'on_start_date' && strtotime($current_selling_date) <= strtotime($extra['start_date'])) ||
                    ($extra['charging_scheme'] == 'once_a_day' && $extra['extra_type'] == 'item' && strtotime($date_start) <= strtotime($date_end)) ||
                    ($extra['charging_scheme'] == 'once_a_day' && $extra['extra_type'] == 'rental' && strtotime($date_start) < strtotime($date_end))
                )
                {
                    if($extra['charging_scheme'] == 'on_start_date')
                    {
                        $extra_array[] = array(
                            "amount"       => $extra['rate'] * $extra['quantity'],
                            "selling_date" => $date_start,
                            "pay_period" => ONE_TIME,
                            "description" => $extra['extra_name']." (quantity: ".$extra['quantity'].")",
                            "charge_type_id" => $extra['charge_type_id'],
                            "charge_type_name" => $extra['extra_name']
                        );
                        
                        if($get_amount_only)
                        {
                            $tax_total = 0;
                            if($tax_rates && count($tax_rates) > 0)
                            {
                                foreach($tax_rates as $tax){
                                    if(!$tax['is_tax_inclusive']){
                                        $tax_total += ($extra['rate'] * $extra['quantity'] * $tax['tax_rate'] / 100);
                                    }
                                    
                                }
                            }
                            $extra_charges += ($extra['rate']) * $extra['quantity'];
                        }
                        
                        $extra_sub_total += $extra['rate'];
                        $extra_tax_amount += $tax_total;
                    }
                    else if($extra['charging_scheme'] == 'once_a_day' && $extra['extra_type'] == 'rental' && strtotime($date_start) < strtotime($date_end))
                    {
                        for ($date = $date_start; $date < $date_end; $date = Date("Y-m-d", strtotime("+1 day", strtotime($date))))
                        {
                            $extra_array[] = array(
                                "amount"       => $extra['rate'] * $extra['quantity'],
                                "selling_date" => $date,
                                "pay_period" => DAILY,
                                "description" => $extra['extra_name']." (quantity: ".$extra['quantity'].")",
                                "charge_type_id" => $extra['charge_type_id'],
                                "charge_type_name" => $extra['extra_name']
                            );
                            
                            if($get_amount_only)
                            {
                                $tax_total = 0;
                                if($tax_rates && count($tax_rates) > 0)
                                {
                                    foreach ($tax_rates as $tax) {
                                        if (!$tax['is_tax_inclusive']) {
                                            $tax_total += ($extra['rate'] * $extra['quantity'] * $tax['tax_rate'] / 100);
                                        }
                                    }
                                }
                                $extra_charges += ($extra['rate']) * $extra['quantity'];
                            }

                            $extra_sub_total += $extra['rate'];
                            $extra_tax_amount += $tax_total;
                        }
                    } else {
                        
                        for ($date = $date_start; $date <= $date_end; $date = Date("Y-m-d", strtotime("+1 day", strtotime($date))))
                        {
                            $extra_array[] = array(
                                "amount"       => $extra['rate'] * $extra['quantity'],
                                "selling_date" => $date,
                                "pay_period" => DAILY,
                                "description" => $extra['extra_name']." (quantity: ".$extra['quantity'].")",
                                "charge_type_id" => $extra['charge_type_id'],
                                "charge_type_name" => $extra['extra_name']
                            );
                            
                            if($get_amount_only)
                            {
                                $tax_total = 0;
                                if($tax_rates && count($tax_rates) > 0)
                                {
                                    foreach ($tax_rates as $tax) {
                                        if (!$tax['is_tax_inclusive']) {
                                            $tax_total += ($extra['rate'] * $extra['quantity'] * $tax['tax_rate'] / 100);
                                        }
                                    }
                                }
                                $extra_charges += ($extra['rate']) * $extra['quantity'];
                            }

                            $extra_sub_total += $extra['rate'];
                            $extra_tax_amount += $tax_total;
                        }
                    }
                }
            }
        }

        $data['view_data']['grand_total'] = $grand_extra_total = $extra_charges ? $extra_charges : 0;
        $data['view_data']['sub_total'] += $grand_extra_total;
        $data['view_data']['tax_amount'] += $extra_tax_amount;
        $data['view_data']['total'] = $data['view_data']['sub_total'] + $data['view_data']['tax_amount'];

        $data['view_data']['rate_plan_extra'] = $rate_plan_extra;

        $common_booking_engine_fields = json_decode(COMMON_BOOKING_ENGINE_FIELDS, true);
        $get_common_booking_engine_fields = $this->Company_model->get_common_booking_engine_fields($company_id);

        foreach($common_booking_engine_fields as $id => $name)
        {
            $is_required = 1;
            if ($id == BOOKING_FIELD_NAME) {
                $is_required = 1;
            } else if ($get_common_booking_engine_fields && isset($get_common_booking_engine_fields[$id]) && isset($get_common_booking_engine_fields[$id]['is_required'])) {
                $is_required = $get_common_booking_engine_fields[$id]['is_required'];
            } else if ($id == BOOKING_FIELD_POSTAL_CODE || $id == BOOKING_FIELD_SPECIAL_REQUEST) {
                $is_required = 0;
            }

            $data['booking_engine_fields'][] = array(
                'id' => $id,
                'field_name' => $name,
                'company_id' => $company_id,
                'show_on_booking_form'=> ($id == BOOKING_FIELD_NAME) ? 1 : (($get_common_booking_engine_fields && isset($get_common_booking_engine_fields[$id]) && isset($get_common_booking_engine_fields[$id]['show_on_booking_form'])) ? $get_common_booking_engine_fields[$id]['show_on_booking_form'] : 1),
                'is_required' => $is_required
            );
        }

        $this->load->library('form_validation');
        if(count($data['booking_engine_fields']) > 0):
            foreach ($data['booking_engine_fields'] as $key => $value):

                if($value['id'] == BOOKING_FIELD_NAME){
                    $name = 'customer-name';
                    $is_required = $value['show_on_booking_form'] && $value['is_required'] ? 'required' : '';
                    $error_name = $value['field_name'];
                } else if($value['id'] == BOOKING_FIELD_EMAIL){
                    $name = 'customer-email';
                    $is_required = $value['show_on_booking_form'] && $value['is_required'] ? 'required' : '';
                    $error_name = $value['field_name'];
                } else if($value['id'] == BOOKING_FIELD_PHONE){
                    $name = 'phone';
                    $is_required = $value['show_on_booking_form'] && $value['is_required'] ? 'required' : '';
                    $error_name = $value['field_name'];
                } else if($value['id'] == BOOKING_FIELD_ADDRESS){
                    $name = 'address';
                    $is_required = $value['show_on_booking_form'] && $value['is_required'] ? 'required' : '';
                    $error_name = $value['field_name'];
                } else if($value['id'] == BOOKING_FIELD_CITY){
                    $name = 'city';
                    $is_required = $value['show_on_booking_form'] && $value['is_required'] ? 'required' : '';
                    $error_name = $value['field_name'];
                } else if($value['id'] == BOOKING_FIELD_REGION){
                    $name = 'region';
                    $is_required = $value['show_on_booking_form'] && $value['is_required'] ? 'required' : '';
                    $error_name = $value['field_name'];
                } else if($value['id'] == BOOKING_FIELD_COUNTRY){
                    $name = 'country';
                    $is_required = $value['show_on_booking_form'] && $value['is_required'] ? 'required' : '';
                    $error_name = $value['field_name'];
                } else if($value['id'] == BOOKING_FIELD_POSTAL_CODE){
                    $name = 'postal-code';
                    $is_required = $value['show_on_booking_form'] && $value['is_required'] ? 'required' : '';
                    $error_name = $value['field_name'];
                } else if($value['id'] == BOOKING_FIELD_SPECIAL_REQUEST){
                    $name = 'special-requests';
                    $is_required = $value['show_on_booking_form'] && $value['is_required'] ? 'required' : '';
                    $error_name = $value['field_name'];
                }

                $this->form_validation->set_rules(
                    $name,
                    $error_name,
                    $is_required.'|trim'
                );
            endforeach;
        else:
            $this->form_validation->set_rules(
                'customer-name',
                'Name',
                'required|trim'
            );
            $this->form_validation->set_rules(
                'customer-email',
                'Email',
                'required|trim|valid_email'
            );
            $this->form_validation->set_rules(
                'phone',
                'Phone',
                'required|trim'
            );

            $this->form_validation->set_rules(
                'address',
                'Address',
                'required|trim'
            );
            $this->form_validation->set_rules(
                'city',
                'City',
                'required|trim'
            );
            $this->form_validation->set_rules(
                'region',
                'State Province',
                'required|trim'
            );
            $this->form_validation->set_rules(
                'country',
                'Country',
                'required|trim'
            );
            $this->form_validation->set_rules(
                'postal-code',
                'Postal/Zip Code',
                'trim'
            );
            $this->form_validation->set_rules(
                'special-requests',
                'Special Requests',
                'trim'
            );
        endif;

        $data['gateway_credentials']            = $this->paymentgateway->getSelectedGatewayCredentials(1);
        $gateway_settings                       = $this->paymentgateway->getCompanyGatewaySettings();
        $data['store_cc_in_booking_engine']     = (bool)$gateway_settings['store_cc_in_booking_engine'];
        $data['are_gateway_credentials_filled'] = $this->paymentgateway->areGatewayCredentialsFilled();
      
        if ($data['store_cc_in_booking_engine'] and $data['are_gateway_credentials_filled'] and $gateway_settings['selected_payment_gateway'] !== 'nexio'){
            $this->form_validation->set_rules(
                'cc_number',
                'CC number',
                'required'
            );
        }

        if (!$data['view_data']['check_in_date'] || !$data['view_data']['check_out_date']) {
            redirect('/online_reservation/select_dates_and_rooms/'.$this->uri->segment(3));
        }

        $selected_rooms = $this->_select_rooms_for_booking(
            $data['view_data']['check_in_date'],
            $data['view_data']['check_out_date'],
            $company_id,
            $data['view_data']['selected_rate_plans']
        );

        // room not selected. session variables are missing. restart.
        if (!isset($selected_rooms)) {
            redirect('/online_reservation/select_dates_and_rooms/'.$this->uri->segment(3));
        }

        $data['current_step'] = 3;

        $nexio_active = sqli_clean($this->security->xss_clean($this->input->post('nexio_active')));

        if ($this->form_validation->run() == FALSE && $nexio_active == 0) {         
            $data['main_content'] = '../extensions/'.$this->module_name.'/views/book_reservation';
            $this->template->load('online_reservation_template', null , $data['main_content'], $data);

        }elseif($gateway_settings['selected_payment_gateway'] == 'nexio' && $this->form_validation->run() == FALSE && $nexio_active == 1){
            $response = array(
                'status' => 'error',
                'message' => "please fill all the required fields."
            );
             echo json_encode($response);
        }
         else {
            //Verify that rooms are still available for booking
            //because the rooms may have been booked
            //while going through the online reservation process.
            if (!is_null($selected_rooms)) {

                $card_data_array = array();

                $customer_data                  = array();
                $customer_data['company_id']    = $company_id;
                $customer_data['customer_name'] = sqli_clean($this->security->xss_clean($this->input->post('customer-name')));

                $customer_data['email']         = sqli_clean($this->security->xss_clean($this->input->post('customer-email')));

                $customer_data['customer_type'] = 'PERSON';

                $customer_data['phone']         = sqli_clean($this->security->xss_clean($this->input->post('phone')));

                $customer_data['address']       = sqli_clean($this->security->xss_clean($this->input->post('address')));

                $customer_data['city']          = sqli_clean($this->security->xss_clean($this->input->post('city')));

                $customer_data['region']        = sqli_clean($this->security->xss_clean($this->input->post('region')));

                $customer_data['country']       = sqli_clean($this->security->xss_clean($this->input->post('country')));

                $customer_data['postal_code']   = sqli_clean($this->security->xss_clean($this->input->post('postal-code')));

                if ($customer_id = $this->Customer_model->get_customer_id_by_email($customer_data['email'], $company_id)) {
                    $this->Customer_model->update_customer($customer_id, $customer_data);

                    $post_customer_data = $customer_data;
                    $post_customer_data['customer_id'] = $customer_id;

                    do_action('post.update.customer', $post_customer_data);

                } else {
                    $customer_id = $this->Customer_model->create_customer($customer_data);

                    $post_customer_data = $customer_data;
                    $post_customer_data['customer_id'] = $customer_id;

                    do_action('post.create.customer', $post_customer_data);
                }

                // $token = sqli_clean($this->security->xss_clean($this->input->post('token')));

                $cc_number = sqli_clean($this->security->xss_clean($this->input->post('cc_number')));
                $cc_expiry = sqli_clean($this->security->xss_clean($this->input->post('cc_expiry')));
                $cvc = sqli_clean($this->security->xss_clean($this->input->post('cc_cvc')));

                $cc_expiry = explode(' / ', $cc_expiry);
                $customer_data['cc_expiry_month'] = $cc_expiry_month = $cc_expiry[0] ?? null;
                $customer_data['cc_expiry_year'] = $cc_expiry_year = $cc_expiry[1] ?? null;

                $card_details = array(
                        'is_primary' => 1,
                        'customer_id' => $customer_id,
                        'customer_name' => $customer_data['customer_name'],
                        'card_name' => '',
                        'company_id' => $customer_data['company_id'],
                        'cc_expiry_month' => $cc_expiry_month,
                        'cc_expiry_year' => $cc_expiry_year,
                        'cc_tokenex_token' => null
                    );

                if(
                    $cc_number && 
                        is_numeric($cc_number) &&
                        !strrpos($cc_number, 'X') && 
                        $cvc && 
                        is_numeric($cvc) &&
                        !strrpos($cvc, '*')
                    )
                {
                    $card_data_array = array('card' =>
                        array(
                            'card_number'       => $cc_number,
                            'card_type'         => "",
                            'cardholder_name'   => (isset($customer_data['customer_name']) ? $customer_data['customer_name'] : ""),
                            'service_code'      => $cvc,
                            'expiration_month'  => isset($customer_data['cc_expiry_month']) ? $customer_data['cc_expiry_month'] : null,
                            'expiration_year'   => isset($customer_data['cc_expiry_year']) ? $customer_data['cc_expiry_year'] : null
                        )
                    );
                    $card_response = array();

                    if($card_data_array && $card_data_array['card']['card_number']) {

                        $customer_data['customer_id'] = $customer_id;
                        $card_data_array['customer_data'] = $customer_data;
                        $card_response = apply_filters('post.add.customer', $card_data_array);
                        unset($card_data_array['customer_data']);
                    }
                    if(
                        $card_response &&
                        isset($card_response['tokenization_response']["data"]) &&
                        isset($card_response['tokenization_response']["data"]["attributes"]) &&
                        isset($card_response['tokenization_response']["data"]["attributes"]["card_token"])
                    ){
                        $card_token = $card_response['tokenization_response']["data"]["attributes"]["card_token"];

                        $cvc_encrypted = get_cc_cvc_encrypted($cvc, $card_token);

                        $card_details['cc_cvc_encrypted'] = ($cvc_encrypted) ? $cvc_encrypted : "";
                        $card_details['cc_number'] = 'XXXX XXXX XXXX '.substr($cc_number,-4);

                        $meta['token'] = $card_token;
                        $card_details['customer_meta_data'] = json_encode($meta);
                    }
                }

                $customer_data['cc_number'] = "";
                $customer_data['cc_expiry_month'] = "";
                $customer_data['cc_expiry_year'] = "";
                $customer_data['cc_tokenex_token'] = "";
                $customer_data['cc_cvc_encrypted'] = "";

                $check_data = $this->Card_model->get_customer_primary_card($customer_id);
        
                if(empty($check_data)){
                    $this->Customer_model->update_customer($customer_id, $customer_data);

                    $post_customer_data = $customer_data;
                    $post_customer_data['customer_id'] = $customer_id;

                    do_action('post.update.customer', $post_customer_data);
                    
                    if(isset($cc_number)){
                        $this->Card_model->create_customer_card_info($card_details);
                    }
                } else {
                    $this->Card_model->update_customer_card($check_data['id'], $customer_id, $card_details);
                }

                //Create Booking(s)
                $bookings = array();

                // $ota_id = apply_filters('get_ota_id', 'obe');
                // $ota_id = $ota_id ? $ota_id : SOURCE_ONLINE_WIDGET;

                $booking_source = isset($this->session->userdata['view_data']['booking_source']) ? $this->session->userdata['view_data']['booking_source'] : "";
                foreach ($selected_rooms as $selected_room_index => $selected_room) {
                    $booking_data['rate_plan_id']  = $data['view_data']['rate_plan_selected_ids'][$selected_room_index];
                    $booking_data['use_rate_plan'] = 1;
                    
                    $booking_data['rate']    = $rate_plan['average_daily_rate'];

                    $booking_data['adult_count']    = $data['view_data']['adult_count'][$selected_room_index];
                    $booking_data['children_count'] = $data['view_data']['children_count'][$selected_room_index];

                    $booking_data['state']               = ($company_data['booking_engine_booking_status']) ? RESERVATION : UNCONFIRMED_RESERVATION;
                    $booking_data['source']              = ($booking_source && $booking_source == 'seasonal.io') ? SOURCE_SEASONAL : SOURCE_ONLINE_WIDGET;
                    $booking_data['company_id']          = $company_id;
                    $booking_data['booking_customer_id'] = $customer_id;
                    $booking_data['booking_notes']       = sqli_clean($this->security->xss_clean($this->input->post('special-requests')));


                    // extras in booking notes
                    if($rate_plan_extra && count($rate_plan_extra) > 0) {
                        $new_array = $prev_extras = array();
                        $booking_data['booking_notes'] .= "\n\nExtra Items:\n";

                        foreach($rate_plan_extra as $key =>$value) {
                            if(!array_key_exists($value['extra_id'], $new_array)){
                                $new_array[$value['extra_id']] = 0;
                            }
                            $new_array[$value['extra_id']] = $new_array[$value['extra_id']] + 1;
                        }

                        foreach ($rate_plan_extra as $key => $extra) {
                            if(!in_array($extra['extra_id'], $prev_extras)) {
                                $booking_data['booking_notes'] .= $extra['extra_name']." (Amount: ".$extra['amount'].", Qty: ".$extra['quantity'].")\n";
                                $prev_extras[] = $extra['extra_id'];
                            }
                        }
                    }

                    $booking_id = $this->Booking_model->create_booking($booking_data);

                    $post_booking_data = $booking_data;
                    $post_booking_data['booking_id'] = $booking_id;
                   

                    $booking_data['rate']    = number_format($rate_plan['average_daily_rate'], 2, ".", ",");
                    
                    $bookings[] = $booking_id;

                    $booking_history               = array();
                    $booking_history['booking_id'] = $booking_id;

                    $booking_history['room_id'] = $selected_room['room_id'];

                    $booking_history['check_in_date']  = $company_data['enable_new_calendar'] ? $data['view_data']['check_in_date'].' '.date("H:i:s", strtotime($company_data['default_checkin_time'])) : $data['view_data']['check_in_date'];

                    $booking_history['check_out_date'] = $company_data['enable_new_calendar'] ? $data['view_data']['check_out_date'].' '.date("H:i:s", strtotime($company_data['default_checkout_time'])) : $data['view_data']['check_out_date'];

                    $this->Booking_room_history_model->create_booking_room_history($booking_history);

                     $booking_action_data = array(
                        'booking_id' => $booking_id,  
                        'company_id'=> $this->company_id,
                        'booking_type'=>"new",
                        'booking_from'=>"Booking Engine"

                    );

                    $post_booking_data['room_id'] = $selected_room['room_id'];
                    $post_booking_data['check_in_date'] = $company_data['enable_new_calendar'] ? $data['view_data']['check_in_date'].' '.date("H:i:s", strtotime($company_data['default_checkin_time'])) : $data['view_data']['check_in_date'];
                    $post_booking_data['check_out_date'] = $company_data['enable_new_calendar'] ? $data['view_data']['check_out_date'].' '.date("H:i:s", strtotime($company_data['default_checkout_time'])) : $data['view_data']['check_out_date'];

                    do_action('post.create.booking', $post_booking_data);

                     do_action('post.add.booking', $booking_action_data);

                    $selling_date = $this->Company_model->get_selling_date($company_id);

                    if($rate_plan_extra && count($rate_plan_extra) > 0) {
                        $extra_data = $prev_extra_data = array();
                        foreach ($rate_plan_extra as $key => $extra) {
                            $extra_data['extra_id'] = $extra['extra_id'];
                            $extra_data['start_date'] = $data['view_data']['check_in_date'];
                            $extra_data['end_date'] = $data['view_data']['check_out_date'];
                            $extra_data['quantity'] = $extra['quantity'];

                            if(!in_array($extra['extra_id'], $prev_extra_data)){
                                $this->create_booking_extra_AJAX($booking_id, $extra_data, $selling_date);
                                $prev_extra_data[] = $extra['extra_id'];
                            }
                        }
                    }
                    // start create new rate plan with booking id

                    if(isset($booking_data['use_rate_plan']) && $booking_data['use_rate_plan'])
                    {
                        $this->load->helper('MY_date_helper');
                        $start_date = date('Y-m-d', strtotime($booking_history['check_in_date']));
                        $end_date = date('Y-m-d', strtotime($booking_history['check_out_date']));

                        $this->load->library('rate');
                        $raw_rate_array = $this->rate->get_rate_array($booking_data['rate_plan_id'], $start_date, $end_date, $booking_data['adult_count'], $booking_data['children_count']);

                        $rate_array = array();
                        foreach ($raw_rate_array as $rate)
                        {
                            $rate_array[] = array(
                                'date' => $rate['date'],
                                'base_rate' => $rate['base_rate'],
                                'adult_1_rate' => $rate['adult_1_rate'],
                                'adult_2_rate' => $rate['adult_2_rate'],
                                'adult_3_rate' => $rate['adult_3_rate'],
                                'adult_4_rate' => $rate['adult_4_rate'],
                                'additional_adult_rate' => $rate['additional_adult_rate'],
                                'additional_child_rate' => $rate['additional_child_rate'],
                                'minimum_length_of_stay' => $rate['minimum_length_of_stay'],
                                'maximum_length_of_stay' => $rate['maximum_length_of_stay'],
                                'minimum_length_of_stay_arrival' => $rate['minimum_length_of_stay_arrival'],
                                'maximum_length_of_stay_arrival' => $rate['maximum_length_of_stay_arrival']
                            );
                        }

                        $curreny_data = $this->Currency_model->get_default_currency($company_id);
                        $rate_plan_data = $this->Rate_plan_model->get_rate_plan($booking_data['rate_plan_id']);

                        $new_rate_plan = array(
                            "rate_plan_name" => $rate_plan_data['rate_plan_name']." #".$booking_id,
                            "number_of_adults_included_for_base_rate" => $booking_data['adult_count'],
                            "rates" => get_array_with_range_of_dates($rate_array),
                            "currency_id" => $curreny_data['currency_id'],
                            "charge_type_id" => $rate_plan_data['charge_type_id'],
                            "company_id" => $company_id,
                            "is_selectable" => '0',
                            "room_type_id" => $rate_plan_data['room_type_id'],
                            "parent_rate_plan_id" => $booking_data['rate_plan_id']
                        );

                        // create rates
                        $rates = $new_rate_plan['rates'];
                        unset($new_rate_plan['rates']);

                        // create rate plan
                        $rate_plan_id = $this->Rate_plan_model->create_rate_plan($new_rate_plan);
                        $this->Booking_model->update_booking($booking_id, array('rate_plan_id' => $rate_plan_id));
                        foreach ($rates as $rate)
                        {
                            $rate_id = $this->Rate_model->create_rate(
                                Array(
                                    'rate_plan_id' => $rate_plan_id,
                                    'base_rate' => $rate['base_rate'],
                                    'adult_1_rate' => $rate['adult_1_rate'],
                                    'adult_2_rate' => $rate['adult_2_rate'],
                                    'adult_3_rate' => $rate['adult_3_rate'],
                                    'adult_4_rate' => $rate['adult_4_rate'],
                                    'additional_adult_rate' => $rate['additional_adult_rate'],
                                    'additional_child_rate' => $rate['additional_child_rate'],
                                    'minimum_length_of_stay' => $rate['minimum_length_of_stay'],
                                    'maximum_length_of_stay' => $rate['maximum_length_of_stay'],
                                    'minimum_length_of_stay_arrival' => $rate['minimum_length_of_stay_arrival'],
                                    'maximum_length_of_stay_arrival' => $rate['maximum_length_of_stay_arrival']
                                )
                            );

                            $date_range_id = $this->Date_range_model->create_date_range(
                                Array(
                                    'date_start' => $rate['date_start'],
                                    'date_end' => $rate['date_end']
                                )
                            );

                            $this->Date_range_model->create_date_range_x_rate(
                                Array(
                                    'rate_id' => $rate_id,
                                    'date_range_id' => $date_range_id
                                )
                            );
                        }
                    }

                    // end create new rate plan with booking id

                    $this->Booking_model->update_booking_balance($booking_id);

                    $log_data['selling_date'] = $selling_date;
                    $log_data['user_id']      = 2; //User_id 2 is Online Reservation
                    $log_data['booking_id']   = $booking_id;
                    $log_data['date_time']    = gmdate('Y-m-d H:i:s');
                    $log_data['log']          = 'Online reservation submitted';
                    $log_data['log_type']     = SYSTEM_LOG;

                    $this->Booking_log_model->insert_log($log_data);

                    //Create a corresponding invoice
                    $this->Invoice_model->create_invoice($booking_id);

                    try {
                        // add booking info into xml logs
                        $request_data['booking_data'] = $booking_data;
                        $request_data['booking_block_data'] = $booking_history;
                        $request_data['card_data'] = $card_data_array;

                        $request_data['total'] = $data['view_data']['total'];
                        $request_data['number-of-nights'] = $data['view_data']['number_of_nights'];

                        // date_default_timezone_set('America/Denver');
                        $response = array(
                            'ota_type' => 'booking_engine',
                            'ota_booking_id' => $booking_id,
                            'pms_booking_id' => $booking_id,
                            'check_in_date' => $booking_history['check_in_date'],
                            'check_out_date' => $booking_history['check_out_date'],
                            'create_date_time' => date('Y-m-d H:i:s', time()),
                            'booking_type' => 'new',
                            'xml_out' => json_encode($request_data)
                        );

                        $this->Booking_model->insert_ota_booking($response);
                        
                    } catch (Exception $e) {

                    }
                }

                $room_type = $this->Room_type_model->get_room_type_by_room_id($booking_history['room_id']);

                // send booking confirmation email
                if(isset($company_data['email_confirmation_for_booking_engine']) && !$company_data['email_confirmation_for_booking_engine'])
                {
                    $result_array = $this->email_template->send_booking_confirmation_email($booking_id);
                    if ($result_array && $result_array['success'])
                    {
                        $log_data = array();
                        $log_data['selling_date'] = $this->Company_model->get_selling_date($company_id);
                        $log_data['user_id']      = 2; //User_id 2 is Online Reservation
                        $log_data['booking_id']   = $booking_id;
                        $log_data['date_time']    = gmdate('Y-m-d H:i:s');
                        $log_data['log']          = 'Automatic Confirmation Email Sent to '.$result_array['customer_email'];
                        $log_data['log_type']     = SYSTEM_LOG;
                        $this->Booking_log_model->insert_log($log_data);
                    }
                }

                // send booking alert email to hotel owner
                $this->email_template->send_booking_alert_email($booking_id);


                // $data['css_files'] = array(
                    // base_url().auto_version('css/online-reservation.css')
                // );

                // $data['js_files'] = array(
                    // base_url().'js/moment.min.js',
                    // base_url().auto_version('js/online-reservation.js'),
                // );


                // generate PayPal data for the reservation_success page
                $data['paypal_data'] = Array(
                    "customer_id"                           => $customer_id,
                    'company_id'                            => $company_id,
                    'booking_id'                            => $booking_id,
                    'required_payment'                      => ($data['view_data']['total'] * ($company_data['percentage_of_required_paypal_payment'] / 100)),
                    'paypal_account'                        => $company_data['paypal_account'],
                    'item_name'                             => $company_data['percentage_of_required_paypal_payment']."% of total cost of ".$data['view_data']['number_of_nights']." nights in ".$room_type['name'],
                    'require_paypal_payment'                => $company_data['require_paypal_payment'],
                    'percentage_of_required_paypal_payment' => $company_data['percentage_of_required_paypal_payment'],
                    'currency_code'                         => $data['view_data']['default_currency']['currency_code'],
                    'total'                                 => $data['view_data']['total']
                );

                
                  // $this->template->load('includes/online_reservation_template', null , $data['main_content'], $data);
             
                    

                $this->session->set_userdata($data);

                if($gateway_settings['selected_payment_gateway'] == 'nexio' && $nexio_active == 1){
                    $res = array(
                        'customer_id' => $customer_id,
                        "url" => 'online_reservation/reservation_success/'.$this->uri->segment(3)
                    );
                    echo json_encode($res);
                  
                }else{
                    redirect('/online_reservation/reservation_success/'.$this->uri->segment(3)); 
                }  

            } else {
                echo l('We\'re sorry. The rooms you selected are no longer available. Please start over and select new rooms.', true);
            }
        }
    }

    function reservation_success () {
        $data = $this->session->all_userdata();
        $data['main_content'] = '../extensions/'.$this->module_name.'/views/reservation_success';
        $this->template->load('online_reservation_template', null , $data['main_content'], $data);
        // $this->load->view('includes/online_reservation_template', $data);
    }

    function _select_rooms_for_booking($check_in_date, $check_out_date, $company_id, $selected_rate_plans)
    {

        $rooms_available = $this->Room_model->get_available_rooms(
            $check_in_date,
            $check_out_date,
            null,
            null,
            $company_id,
            1
        );

        // Rooms selected while avoiding using blocks that are already occupied by unconfirmed reservations
        $rooms_available_avoiding_unconfirmed_reservations = array();

        // Rooms selected while ignoring, (and potentially overlapping )blocks that are already occupied by unconfirmed reservations
        $rooms_available_ignoring_unconfirmed_reservations = array();
        foreach ($selected_rate_plans as $selected_rate_plan) {
            foreach ($rooms_available as $key => $room_available) {
                // among the available rooms, divide them by ones containing unconfirmed reservations and ones that don't
                if ($selected_rate_plan['room_type_id'] == $room_available['room_type_id']) {
                    // if there is reservation, inhouse, or checkout existing between the given dates,
                    if ($this->Booking_room_history_model->check_if_booking_exists_between_two_dates(
                        $room_available['room_id'],
                        $check_in_date,
                        $check_out_date,
                        'undefined',
                        false // do not consider_unconfirmed_reservations
                    )
                    ) {
                        $rooms_available_ignoring_unconfirmed_reservations[] = $room_available;
                    } else {
                        $rooms_available_avoiding_unconfirmed_reservations[] = $room_available;
                    }
                }
            }
        }

        // We don't have to worry about group booking online reservation yet. So we will just pick the first room available.
        // Preferably, we will choose a room that is avoiding using blocks that are already occupied by unconfirmed reservations
        if (sizeof($rooms_available_avoiding_unconfirmed_reservations) > 0) {
            $rooms_selected = $rooms_available_avoiding_unconfirmed_reservations;
        } else {
            if (sizeof($rooms_available_ignoring_unconfirmed_reservations) > 0) {
                $rooms_selected = $rooms_available_ignoring_unconfirmed_reservations;
            }else{
                return null;
            }
        }

        return array('0' => $rooms_selected[0]);
    }

    // Called by callback_date_format_check
    // Checks whether a valid date has been entered.
    function date_format_check($date)
    {
        //match the format of the date
        if (preg_match("/^([0-9]{4})-([0-9]{2})-([0-9]{2})$/", $date, $parts)) {
            //check whether the date is valid of not
            if (checkdate($parts[2], $parts[3], $parts[1])) {
                return true;
            } else {
                $this->form_validation->set_message('date_format_check', 'Date must be in a valid format (YYYY-MM-DD)');
            }
        } else {
            $this->form_validation->set_message('date_format_check', 'Date must be a valid format (YYYY-MM-DD)');
        }

        return false;
    }

    function is_check_in_date_after_today($date, $company_id)
    {
        $company_data = $this->Company_model->get_company($company_id);
        if ($company_data['allow_same_day_check_in'] == '1') {
            $earliest_check_in_date = $company_data['selling_date'];
        } else {
            $earliest_check_in_date = Date("Y-m-d", strtotime("+1 day", strtotime($company_data['selling_date'])));
        }

        if (strtotime($date) >= strtotime($earliest_check_in_date)) {
            return true;
        }

        $this->form_validation->set_message('is_check_in_date_after_today', 'The earliest check-in date is '.$earliest_check_in_date);

        return false;
    }

    function is_check_out_date_after_check_in_date($check_out_date, $check_in_date)
    {
        if (strtotime($check_in_date) >= strtotime($check_out_date)) {
            $this->form_validation->set_message('is_check_out_date_after_check_in_date', 'Check-out date must be after check-in date');

            return false;
        }

        return true;
    }

    function  _get_tagged_confirmation_message($company)
    {
        $message = $company['online_reservation_confirmation_message'];

        $message = str_replace("[name]", $company['name'], $message);
        $message = str_replace("[email]", $company['email'], $message);
        $message = str_replace("[phone]", $company['phone'], $message);
        $message = str_replace("\n", "<br/>", $message);

        return $message;
    }

    // this function is called from jquery on show_reservation page.
    // this prevents rate plans accumulating when users click on "back button" from book_reservation page
    function clear_rate_plan_selection_in_session()
    {
        $data['view_data']['rate_plan_selected_ids'] = null;
        $this->session->set_userdata($data);
    }

    // To handle the IPN post made by PayPal (uses the Paypal_Lib library).
    function validate_ipn($company_id = '')
    {
        $params = array('company_id' => $company_id);
        $this->load->library('PayPal_IPN', $params); // Load the library


        // For testing
        /*
        $_POST['item_number'] = '273774';
        $_POST['txn_id'] = '';
        $_POST['item_name'] = "percentage of payment";
        $_POST['mc_fee'] = '';
        $_POST['mc_gross'] = '';
        $_POST['txn_id'] = '';
        $_POST['receiver_email'] = 'jaeyun@minical.io';
        $_POST['payment_status'] = 'Completed';
        $_POST['amount'] = '90';
        */


        // Try to get the IPN data.
        if ($this->paypal_ipn->validate_ipn()) {
            echo l("validation success!", true);
            // Enter payment into Invoice
            // Confirm the unconfirmed reservation
            $this->paypal_ipn->modify_invoice_and_booking();
        } else // Just redirect to the root URL
        {
            //$this->load->helper('url');
            //redirect('/', 'refresh');
            echo l("Failed validating IPN", true);
        }
    }

    function language() {

        // update language settings in database
        $explode = explode(',', $this->input->post('language'));
        $language_id = $explode[0];
        $new_language = $explode[1];
        $this->User_model->update_user_profile($this->user_id, Array(
            'language' => $new_language,
            'language_id' => $language_id
        ));

        // apply language change immediately by updating session variable
        $this->session->set_userdata(array( 'online_language' => $new_language ));
        $this->session->set_userdata(array( 'online_language_id' => $language_id ));
        // Call function to load translation of language
        load_translations($language_id);

        echo 'success';
        return;
    }

    function create_booking_extra_AJAX($booking_id, $extra_data, $selling_date)
    {
        $extra_id = $extra_data['extra_id'];
        $start_date = $extra_data['start_date'];
        $end_date = $extra_data['end_date'];
        $quantity = $extra_data['quantity'];
        
        // get default rate
        $extra_default_rate = $this->Extra_model->get_extra_default_rate($extra_id);
        
        $booking_extra_id = $this->Booking_extra_model->create_booking_extra(
                                                        $booking_id, 
                                                        $extra_id, 
                                                        $start_date, 
                                                        $end_date, 
                                                        $quantity, 
                                                        $extra_default_rate
                                                    );
        if($booking_extra_id) {
            $log = Array(
                "booking_id" => $booking_id,
                "date_time" => gmdate('Y-m-d H:i:s'),
                "log_type" => 24,
                "log" => $booking_extra_id,
                "user_id" => 2,
                "selling_date" => $selling_date
            );
            $this->Booking_log_model->insert_log($log);
        }

        return $booking_extra_id;
    }
}