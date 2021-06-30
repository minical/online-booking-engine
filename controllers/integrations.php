<?php
use QuickBooksOnline\API\DataService\DataService;
class Integrations extends MY_Controller
{
    var $sidebar_links;
    var $selected_sidebar_link;
    
     public $module_name;
    function __construct()
	{

        parent::__construct();
         $this->module_name = $this->router->fetch_module();

        $this->load->model('../extensions/'.$this->module_name.'/models/Company_model');
        $this->load->model('../extensions/'.$this->module_name.'/models/Room_type_model');
        $this->load->model('../extensions/'.$this->module_name.'/models/Rate_plan_model');
        $this->load->model('../extensions/'.$this->module_name.'/models/Room_model');
        $this->load->model('../extensions/'.$this->module_name.'/models/Currency_model');
        $this->load->model('../extensions/'.$this->module_name.'/models/Employee_log_model');
        $this->load->model('../extensions/'.$this->module_name.'/models/Channel_model');
        $this->load->model('../extensions/'.$this->module_name.'/models/Payment_gateway_model');
        $this->load->library('Integrations/roomsy_channel_manager');
        
		$view_data['menu_on'] = true;       
		$view_data['integrations_enabled'] = ($this->company_subscription_level == PREMIUM || $this->company_subscription_level == ELITE ) ? true : false;
		$this->load->vars($view_data);
	}
    
 //    function index() 
	// {
	// 	redirect('/settings/integrations/booking_engine', 'refresh');
	// }
	
	function _create_integration_log($log) {
        $log_detail =  array(
                    "user_id" => $this->user_id,
                    "selling_date" => $this->selling_date,
                    "date_time" => gmdate('Y-m-d H:i:s'),
                    "log" => $log,
                );   
        
        $this->Employee_log_model->insert_log($log_detail);     
    }
    
    function booking_engine()
	{
		$view_data = array();

		//-----------------------------------------------------

        $view_data['company_data'] = $this->Company_model->get_company($this->company_id);
        // $view_data['booking_fields'] = $this->Company_model->get_booking_engine_fields($this->company_id);

        $this->load->library('form_validation');
        $this->load->library('PaymentGateway');

        $view_data['booking_engine_fields'] = array();
        
        $common_booking_engine_fields = json_decode(COMMON_BOOKING_ENGINE_FIELDS, true);
        $get_common_booking_engine_fields = $this->Company_model->get_common_booking_engine_fields($this->company_id);

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
            $view_data['booking_engine_fields'][] = array(
                'id' => $id,
                'field_name' => $name,
                'company_id' => $this->company_id,
                'show_on_booking_form'=> ($id == BOOKING_FIELD_NAME) ? 1 : (($get_common_booking_engine_fields && isset($get_common_booking_engine_fields[$id]) && isset($get_common_booking_engine_fields[$id]['show_on_booking_form'])) ? $get_common_booking_engine_fields[$id]['show_on_booking_form'] : 1),
                'is_required' => $is_required
            );
        }

        $view_data['are_gateway_credentials_filled'] = $this->paymentgateway->areGatewayCredentialsFilled();
        $view_data['company_gateway_settings']       = $this->paymentgateway->getCompanyGatewaySettings();
        $view_data['store_cc_in_booking_engine']     = (bool)$view_data['company_gateway_settings']['store_cc_in_booking_engine'];

        $this->form_validation->set_rules('paypal_account', 'Paypal Account', 'trim|valid_email');
        $this->form_validation->set_rules('percentage_of_required_paypal_payment', 'Percentage of Total Payment required', 'trim|numeric');
        if ($this->form_validation->run() == true) {
            $store_cc_in_booking_engine = $this->input->post('store_cc_in_booking_engine');

            if ($view_data['are_gateway_credentials_filled']) {
                $this->Payment_gateway_model->update_payment_gateway_settings(
                    array(
                        'company_id'                 => $this->company_id,
                        'store_cc_in_booking_engine' => $store_cc_in_booking_engine
                    )
                );
            }

            $company_data = array(
                'allow_same_day_check_in'               => $this->input->post('allow_same_day_check_in'),
                'require_paypal_payment'                => $this->input->post('require_paypal_payment'),
                'paypal_account'                        => $this->input->post('paypal_account'),
                'percentage_of_required_paypal_payment' => $this->input->post('percentage_of_required_paypal_payment'),
                'booking_engine_booking_status'         => $this->input->post('booking_engine_booking_status'),
                'email_confirmation_for_booking_engine' => $this->input->post('email_confirmation_for_booking_engine'),
                'booking_engine_tracking_code'          => htmlentities($this->input->post('booking_engine_tracking_code'))
            );



            $this->Company_model->update_company($this->company_id, $company_data);
			$this->_create_integration_log("Update Booking Engine Setting");
            redirect('/integrations/booking_engine');
            // exit; // itodo possibly unnecessary
		}

        $files = get_asstes_files($this->module_assets_files, $this->module_name, $this->controller_name, $this->function_name);
 
        $view_data['main_content'] = '../extensions/'.$this->module_name.'/views/online_reservation_settings';

        $this->template->load('bootstrapped_template', null , $view_data['main_content'], $view_data);
		
	}

    function update_booking_engine_fields()
    {
        $updated_booking_engine_fields = $this->input->post('updated_booking_engine_fields');
        $response = array(
            'error' => false,
            'success' => true
        );
        
        $common_booking_engine_fields = json_decode(COMMON_BOOKING_ENGINE_FIELDS, true);
        
        foreach($updated_booking_engine_fields as $updated_booking_field)
        {
            $booking_engine_field_id = $this->security->xss_clean($updated_booking_field['id']);
            
            if(isset($common_booking_engine_fields[$booking_engine_field_id]))
            {
                $data = array(
                    'id' => $booking_engine_field_id,
                    'company_id' => $this->company_id,
                    'show_on_booking_form' => $this->security->xss_clean($updated_booking_field['show_on_booking_form']),
                    'is_required' => $this->security->xss_clean($updated_booking_field['is_required'])
                );
                $this->Company_model->update_common_booking_engine_fields($this->company_id, $booking_engine_field_id, $data);
                continue;
            }
        }
        echo json_encode($response);
    }

    // function add_booking_engine_fields()
    // {
    //     $insert_data = array(
    //                             'Full Name',
    //                             'Email',
    //                             'Phone',
    //                             'Address',
    //                             'City',
    //                             'State',
    //                             'Country',
    //                             'Postal Code'
    //                         );
    //     $get_success = $this->Company_model->add_booking_engine_fields($insert_data, $this->company_id);
    //     if($get_success)
    //         redirect('/settings/integrations/booking_engine');
    // }

    // function update_booking_engine_fields()
    // {
    //     $id = $this->input->post('id');
    //     $field = $this->input->post('field');
    //     $is_checked = $this->input->post('is_checked');

    //     $data = array(
    //                     'id' => $id,
    //                     $field == 'show_on_booking_form' ? 'show_on_booking_form' : 'is_required' => $is_checked == 'true' ? 1 : 0
    //                 );

    //     $this->Company_model->update_booking_engine_fields($data, $this->company_id);
    //     echo json_encode(array('status' => true));
    // }

    function roomsy_channel_manager($action = null, $ota_id = null)
    {
		$view_data['company_data'] = $this->Company_model->get_company($this->company_id);
        //echo '<pre>'; print_r($view_data['company_data']);echo '</pre>';
        if($action === 'manage')
        {
            
            if($ota_id == SOURCE_AGODA)
            {
                $ota_room_types_and_rate_plans = $this->roomsy_channel_manager->get_room_types_and_rate_plans($ota_id, $this->company_id);
                $currency = $this->Currency_model->get_default_currency($this->company_id);
                $view_data['roomsy_company_currency_code'] = $currency['currency_code'];
                
                if(isset($ota_room_types_and_rate_plans['error'])){
                    $view_data['ota_room_types_and_rate_plans'] = $ota_room_types_and_rate_plans;
                    $view_data['ota_company_curreny_code'] = "";
                }else{
                    $view_data['ota_room_types_and_rate_plans'] = $ota_room_types_and_rate_plans['room_type_rate_plans_ar'];
                    $view_data['ota_company_curreny_code'] = $ota_room_types_and_rate_plans['currency_code'];
                }
                
                
            }
            elseif($ota_id == SOURCE_SITEMINDER)
            {
                $req = file_get_contents(
                $this->config->item('cm_url').'/sync/get_room_types_and_rate_plans/'.$this->company_id.'/only_for_siteminder'
                    );
                $array = json_decode(json_encode(json_decode($req)),true); 
                
                $siteminder_hotel_region = $array['siteminder_hotel_region']; 
                $pms_room_type_array = $array['pms_room_types']; 
                $pms_rate_plan_array = $array['pms_rate_plans'];
                $view_data['pms_room_type_array'] = $pms_room_type_array;
                $view_data['pms_rate_plan_array'] = $pms_rate_plan_array;
                
                $room_types = $this->Room_type_model->get_room_types($this->company_id);
                $rate_plans = array();
                foreach($room_types as $room_type_id)
                {
                    $rate_plans[] = $this->Rate_plan_model->get_rate_plans_by_room_type_id($room_type_id['id']);
                }
                
                $view_data['pms_room_types'] = $room_types;
                $view_data['pms_rate_plans'] = $rate_plans;
                $view_data['hotel_region'] = $siteminder_hotel_region;
                
            }
            elseif($ota_id == SOURCE_EXPEDIA)
            {
                $req = file_get_contents(
                $this->config->item('cm_url').'/sync/get_room_types_and_rate_plans/'.$this->company_id.'/only_for_expedia'
                    );
                $array = json_decode(json_encode(json_decode($req)),true);
                $view_data['ota_room_types_and_rate_plans'] = $array['ota_room_types_and_rate_plans'];
                $view_data['pricing_model'] = $array['expedia_pricing_model'];
            }
            else
            {
                $ota_room_types_and_rate_plans = $this->roomsy_channel_manager->get_room_types_and_rate_plans($ota_id, $this->company_id);
                $view_data['ota_room_types_and_rate_plans'] = $ota_room_types_and_rate_plans;
            }
           
            $view_data['ota_id'] = $ota_id;
            
            $common_booking_sources = json_decode(COMMON_BOOKING_SOURCES, true);
            $view_data['ota_name'] = $common_booking_sources[$ota_id];
        
            $view_data['main_content'] = '../extensions/'.$this->module_name.'/views/manage_roomsy_channel_manager';
        }
        else
        {
          
            $view_data['otas'] = $this->roomsy_channel_manager->get_otas($view_data['company_data']['company_id']);
              $view_data['main_content'] = '../extensions/'.$this->module_name.'/views/roomsy_channel_manager';
        }
        
        $view_data['selected_sidebar_link'] = 'Direct Connect';
         $files = get_asstes_files($this->module_assets_files, $this->module_name, $this->controller_name, $this->function_name);

        $this->template->load('bootstrapped_template', null , $view_data['main_content'], $view_data);

    }
    
    function unconfirmed_reservations() {
        $view_data['company_data'] = $this->Company_model->get_company($this->company_id);
		$view_data['js_files'] = array(base_url() . auto_version('js/hotel-settings/online-settings.js'));
		$view_data['selected_sidebar_link'] = 'Unconfirmed Reservations';
		$view_data['main_content'] = 'hotel_settings/channel_manager/unconfirmed_reservations';
		$this->load->view('includes/bootstrapped_template', $view_data);
	}
    function update_unconfirmed_reservations_AJAX() {
        if ($this->input->post()) {
            $company_data = array(
                'book_over_unconfirmed_reservations' => $this->input->post('book_over_unconfirmed_reservations'),
            );
            $this->Company_model->update_company($this->company_id, $company_data);
            echo json_encode(array('status' => true));
            return;
		}
        echo json_encode(array('status' => false));
	}
    
    function configure_roomsy_channel_manager_AJAX()
    {  
        if($this->company_subscription_level == BASIC){
            return;
        }
        $data = $this->input->post('data');
		$channel_data = $this->Channel_model->get_all_channels($data['ota_id']);
		$channel_name = (isset($channel_data[0]) && isset($channel_data[0]['name'])) ?  $channel_data[0]['name'] : '';
		$this->_create_integration_log("Configure OTA ( $channel_name )");
        $company = $this->Company_model->get_company($this->company_id);
        $response = $this->roomsy_channel_manager->configure_ota($company, $data);
        echo json_encode($response);
    }
    function deconfigure_roomsy_channel_manager_AJAX()
    {
        if($this->company_subscription_level == BASIC){
            return;
        }
        $ota_id = $this->input->post('ota_id');
		$channel_data = $this->Channel_model->get_all_channels($ota_id);
		$channel_name = (isset($channel_data[0]) && isset($channel_data[0]['name'])) ? $channel_data[0]['name'] : '';
		$this->_create_integration_log("Deconfigure OTA ( $channel_name )");
        $response = $this->roomsy_channel_manager->deconfigure_ota($this->company_id, $ota_id);
        echo json_encode($response);
    }    
    
    function get_link_data($ota_id)
    {
        if($this->company_subscription_level == BASIC){
            return;
        }
        $link_data = $this->roomsy_channel_manager->get_link_data($this->company_id, $ota_id);
        $link_data['room_types_and_rates'] = $this->Company_model->get_rate_plans_grouped_by_room_type($this->company_id);
        echo json_encode($link_data);
    }
    
    function save_links($ota_id)
	{	
        if($this->company_subscription_level == BASIC){
            return;
        }
        $room_type_rate_plan = $this->security->xss_clean($this->input->post('roomtypeRateplanInfo'));
        
        $data = array(
            "ota_id" => $ota_id,
            "company_id" => $this->company_id,
            "room_types" => $room_type_rate_plan['room_types'],
            "rate_plans" => $room_type_rate_plan['rate_plans']
        );  
        
        if($ota_id == SOURCE_BOOKING_DOT_COM)
        {
            $ota_occupancy = $this->security->xss_clean($this->input->post('otaOccupancyInfo'));
            $data['ota_occupancies'] = $ota_occupancy['rate_plans'];
            
            // update booking dot com rate type and additional adult rate in company table
            $booking_dot_com_rate_type = $this->security->xss_clean($this->input->post('bookingDotComRateType'));
            $common_additional_adult_rate = $this->security->xss_clean($this->input->post('commonAdditionalAdultRate'));
            
            $company_data = array(
                "booking_dot_com_rate_type" => $booking_dot_com_rate_type,
                "common_additional_adult_rate" => ($common_additional_adult_rate) ? $common_additional_adult_rate : null
            );
            $this->Company_model->update_company($this->company_id, $company_data);
        }
        
        if($ota_id == SOURCE_SITEMINDER)
        {
            // update siteminder hotel region in company table
            $siteminder_hotel_region = $this->security->xss_clean($this->input->post('siteminderHotelRegion'));
            $data['siteminder_hotel_region'] = $siteminder_hotel_region;
        }
        if($ota_id == SOURCE_EXPEDIA)
        {
            // update expedia pricing model in company table
            $expedia_pricing_model = $this->security->xss_clean($this->input->post('expediaPricingModel'));
            $data['expedia_pricing_model'] = $expedia_pricing_model;
            $ota_occupancy = $this->security->xss_clean($this->input->post('otaOccupancyInfo'));
            $data['ota_occupancies'] = $ota_occupancy['rate_plans'];
        }
        
        $response = $this->roomsy_channel_manager->save_links($data);       
        echo json_encode($response);
	}
    
    
    function myallocator($action = null, $ota_id = null)
    {
        $view_data['company'] = $this->Company_model->get_company($this->company_id);
        $myallocator_user = $this->roomsy_channel_manager->get_myallocator_user_token($this->company_id);
        //print_r($myallocator_user);die;
        $cm_error = $this->session->flashdata('cm_error');
        
        $view_data['myallocator'] = $myallocator_user;
        $myallocator_user_token = isset($myallocator_user['user_token']) ? $myallocator_user['user_token'] : null;
        if($this->company_subscription_level != BASIC && $myallocator_user_token && $action == 'manage')
        {
            $myallocator_room_types_and_rate_plans = $this->roomsy_channel_manager->get_myallocator_room_types_and_rate_plans($this->company_id, $ota_id, $myallocator_user_token);
            //print_r($myallocator_room_types_and_rate_plans);
            if(isset($myallocator_room_types_and_rate_plans['error']) && $myallocator_room_types_and_rate_plans['error'])
            {
                $this->session->set_flashdata('cm_error', $myallocator_room_types_and_rate_plans['error']);
                redirect('/integrations/myallocator', 'refresh');
            }
            
            $view_data['myallocator_room_types'] = $myallocator_room_types_and_rate_plans['myallocator_room_types'];
            
            // room type mapping also comes from myallocator so will use that one
            // $view_data['room_types_mapping'] = $myallocator_room_types_and_rate_plans['room_types'];
            $rate_plans_mapping = $myallocator_room_types_and_rate_plans['rate_plans_mapping'];
            $view_data['rate_plans_mapping'] = array();
            if(count($rate_plans_mapping) > 0){
                foreach($rate_plans_mapping as $rate_plan_mapping){
                    $view_data['rate_plans_mapping'][$rate_plan_mapping['ota_room_type_id']] = $rate_plan_mapping['pms_rate_plan_id'];
                }
            }
            
            $view_data['ota_id'] = $ota_id;
            $view_data['roomsy_room_types'] = $this->Room_type_model->get_room_types($this->company_id);
            $view_data['roomsy_rate_plans'] = $this->Rate_plan_model->get_rate_plans($this->company_id);
            $view_data['main_content'] = 'hotel_settings/channel_manager/manage_myallocator';
        }
        else
        {
            if(
                    !$cm_error &&
                    $this->company_subscription_level != BASIC && $myallocator_user_token && isset($myallocator_user['user_status']) &&
                    ($myallocator_user['user_status'] == 'all_set' || $myallocator_user['user_status'] == 'ota_property_not_set')
                )
            {
                if($myallocator_user['user_status'] == 'all_set')
                {
                    $cm_error = false;
                    redirect('/integrations/myallocator/manage/'.$myallocator_user['ota_id'], 'refresh');
                }
                elseif($myallocator_user['user_status'] == 'ota_property_not_set')
                {
                    $cm_error = false;
                    $view_data['myallocator_properties'] = $this->roomsy_channel_manager->get_myallocator_properties($this->company_id);
                    if(isset($view_data['myallocator_properties']['error']))
                    {
                        $cm_error = $view_data['myallocator_properties']['error'];
                    }
                }
            }
            $view_data['main_content'] = 'hotel_settings/channel_manager/myallocator';
        }
        $view_data['cm_error'] = $cm_error;
        $view_data['js_files'] = array(
            base_url() . auto_version('js/channel_manager/channel_manager.js')
        );
        $view_data['selected_sidebar_link'] = 'Myallocator';
        $this->load->view('includes/bootstrapped_template', $view_data);
    }
    
    public function myallocator_login_AJAX(){
        if($this->company_subscription_level == BASIC){
            return;
        }
        $data = $this->input->post('data');
        $company = $this->Company_model->get_company($this->company_id);
        
        $myallocator_user = $this->roomsy_channel_manager->get_myallocator_user_token(null, $data);
        
        if(isset($myallocator_user['user_token']) && $myallocator_user['user_token'])
        {
            $data['password'] = $myallocator_user['user_token'];
            $response = $this->roomsy_channel_manager->configure_ota($company, $data);
        }
        echo json_encode($myallocator_user);
    }
    
    public function myallocator_property_mapping_AJAX(){
        if($this->company_subscription_level == BASIC){
            return;
        }
        $myallocator_property_id = $this->input->post('propertyId');
        $ota_id = $this->input->post('ota_id');
        $response = $this->roomsy_channel_manager->set_myallocator_property($this->company_id, $ota_id, $myallocator_property_id);
        echo json_encode($response);
    }
    
    public function save_myallocator_mapping_AJAX(){
        if($this->company_subscription_level == BASIC){
            return;
        }
        $mapping_data = $this->input->post('mapping_data');
        $ota_id = $this->input->post('ota_id');
        $response = $this->roomsy_channel_manager->set_myallocator_room_types_and_rate_plans($this->company_id, $ota_id, $mapping_data);
        echo json_encode($response);
    }
    
    public function ical_calendar()
    {
       
        $view_data = array();
        $view_data['selected_sidebar_link'] = 'iCal Calendar';
       
        $view_data['company_id'] = $this->company_id;
        
        $view_data['rooms'] =  $this->Room_model->get_rooms($this->company_id, $sort_by = 'room_name', $where = '');
    
        $ical_mapped_data = $this->roomsy_channel_manager->get_ical_pms_room_ids($this->company_id);
       
        $ical_mapped_data_ar = array();
        if(!empty($ical_mapped_data)){
            foreach($ical_mapped_data as $key => $ical_mapped)
            {
                if(!empty($ical_mapped))
                {
                    $ical_mapped_data_ar[$ical_mapped['pms_room_id']] = array(
                        'send_status' => $ical_mapped['send'],
                        'import_url' => $ical_mapped['import_url']
                    );
                }
            }
        }
        
        $view_data['ical_mapped_data'] = $ical_mapped_data_ar;
        
        $view_data['js_files'] = array(
            base_url() . auto_version('js/channel_manager/ical_manager.js')
        );
        
        $view_data['main_content'] = 'hotel_settings/channel_manager/ical_calendar';
		$this->load->view('includes/bootstrapped_template', $view_data);
    }
         
    function save_ical_mapping_AJAX()
    {
        $mapping_ical_data = $this->input->post('mapping_ical_data');
        $response = $this->roomsy_channel_manager->set_ical_import_urls_mapping($this->company_id, $mapping_ical_data);
        echo json_encode($response);
    }
    
    /* Payment Type Settings */
    function payment_gateways()
    {		
            $data['selected_sidebar_link'] = 'Payment Gateways';
            $data['main_content'] = 'hotel_settings/accounting_settings/payment_gateway_settings';
            $data['js_files'] = array(
                    base_url() . auto_version('js/hotel-settings/payment-gateway-settings.js')
            );
            
            if(isset($_GET['code']) && $_GET['code'] && $_GET['realmId'])
            {
                $update_data['company_id'] = $this->company_id;

                $dataService = DataService::Configure(array(
                    'auth_mode' => 'oauth2',
                    'ClientID' => $this->config->item('quickbooks_gateway_client_id'),
                    'ClientSecret' => $this->config->item('quickbooks_gateway_client_secret'),
                    'RedirectURI' => base_url().'integrations/payment_gateways',
                    'scope' => "com.intuit.quickbooks.payment",
                    'baseUrl' => "Development"
                ));

                $OAuth2LoginHelper = $dataService->getOAuth2LoginHelper();

                $accessTokenObj = $OAuth2LoginHelper->exchangeAuthorizationCodeForToken($_GET['code'], $_GET['realmId']);
                $refreshTokenValue = $accessTokenObj->getRefreshToken();
                
                $meta = array(
                    "gateway_refresh_token" => $refreshTokenValue,
                    "gateway_realm_id" => $_GET['realmId'],
                    "refresh_token_created_date" => Date("Y-m-d")
                );

                $update_data['gateway_meta_data'] = json_encode($meta);
                
                $this->Payment_gateway_model->update_payment_gateway_settings($update_data);
                $this->session->set_flashdata('setting_update', 'Settings updated.');
                redirect(base_url().'integrations/payment_gateways');
            }

            $this->load->view('includes/bootstrapped_template', $data);
    }
    
    function siteminder($action = null, $ota_id = null)
    {
        $view_data['company_data'] = $this->Company_model->get_company($this->company_id);
        if($action === 'manage')
        {
            $ota_room_types_and_rate_plans = $this->roomsy_channel_manager->get_room_types_and_rate_plans($ota_id, $this->company_id);
            //print_r($ota_room_types_and_rate_plans);
            
                $req = file_get_contents(
                $this->config->item('cm_url').'/sync/get_room_types_and_rate_plans/'.$this->company_id.'/only_for_siteminder'
                    );
                $array = json_decode(json_encode(json_decode($req)),true); 
                
                $siteminder_hotel_region = $array['siteminder_hotel_region']; 
                $pms_room_type_array = $array['pms_room_types']; 
                $pms_rate_plan_array = $array['pms_rate_plans'];
                $view_data['pms_room_type_array'] = $pms_room_type_array;
                $view_data['pms_rate_plan_array'] = $pms_rate_plan_array;
                
                $room_types = $this->Room_type_model->get_room_types($this->company_id);
                $rate_plans = array();
                foreach($room_types as $room_type_id)
                {
                    $rate_plans[] = $this->Rate_plan_model->get_rate_plans_by_room_type_id($room_type_id['id']);
                }
                
                $view_data['pms_room_types'] = $room_types;
                $view_data['pms_rate_plans'] = $rate_plans;
                $view_data['hotel_region'] = $siteminder_hotel_region;
                
            $view_data['ota_id'] = $ota_id;
            
            $common_booking_sources = json_decode(COMMON_BOOKING_SOURCES, true);
            $view_data['ota_name'] = $common_booking_sources[$ota_id];
            
            $view_data['js_files'] = array(base_url() . 'js/channel_manager/helper.js', base_url() . 'js/channel_manager/linker.js');
            $view_data['main_content'] = 'hotel_settings/channel_manager/siteminder';
        }
        else
        {
            $view_data['js_files'] = array(
                base_url() . auto_version('js/channel_manager/channel_manager.js')
			);
            $view_data['siteminder'] = $this->roomsy_channel_manager->get_otas($view_data['company_data']['company_id']);
            $view_data['main_content'] = 'hotel_settings/channel_manager/siteminder';
        }
        
        $view_data['selected_sidebar_link'] = 'Siteminder';
//        prx($view_data);
        $this->load->view('includes/bootstrapped_template', $view_data);
    }
}
