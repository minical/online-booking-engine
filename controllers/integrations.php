<?php
use QuickBooksOnline\API\DataService\DataService;
class Integrations extends MY_Controller
{
    var $sidebar_links;
    var $selected_sidebar_link;
    public $module_name;

    function __construct(){

        parent::__construct();
         $this->module_name = $this->router->fetch_module();

        $this->load->model('../extensions/'.$this->module_name.'/models/Company_model');
        $this->load->model('../extensions/'.$this->module_name.'/models/Employee_log_model');
        $this->load->model('../extensions/'.$this->module_name.'/models/Payment_gateway_model');
        
		$view_data['menu_on'] = true;       
		$view_data['integrations_enabled'] = ($this->company_subscription_level == PREMIUM || $this->company_subscription_level == ELITE ) ? true : false;
		$this->load->vars($view_data);
	}
	
	function _create_integration_log($log) {
        $log_detail =  array(
                    "user_id" => $this->user_id,
                    "selling_date" => $this->selling_date,
                    "date_time" => gmdate('Y-m-d H:i:s'),
                    "log" => $log,
                );   
        
        $this->Employee_log_model->insert_log($log_detail);     
    }
    
    function booking_engine(){
		$view_data = array();
        $view_data['company_data'] = $this->Company_model->get_company($this->company_id);

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
        }

        $files = get_asstes_files($this->module_assets_files, $this->module_name, $this->controller_name, $this->function_name);
 
        $view_data['main_content'] = '../extensions/'.$this->module_name.'/views/online_reservation_settings';

        $this->template->load('bootstrapped_template', null , $view_data['main_content'], $view_data);
		
	}

    function update_booking_engine_fields(){
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

    function unconfirmed_reservations(){
        $view_data['company_data'] = $this->Company_model->get_company($this->company_id);
		$view_data['js_files'] = array(base_url() . auto_version('js/hotel-settings/online-settings.js'));
		$view_data['selected_sidebar_link'] = 'Unconfirmed Reservations';
		$view_data['main_content'] = 'hotel_settings/channel_manager/unconfirmed_reservations';
		$this->load->view('includes/bootstrapped_template', $view_data);
	}

    function update_unconfirmed_reservations_AJAX(){
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
  }
