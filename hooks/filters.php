<?php

add_filter('get_inventory_channel_keys', 'get_inventory_channel_key', 10, 1);

add_filter('is_threshold_enabled', 'get_is_threshold_enabled', 10, 1);

add_filter('get_ota_id', 'get_ota_id_fn', 10, 1);
    
function get_inventory_channel_key ($channels_keys) {

    $channels_keys[] = 'obe';

    return $channels_keys;
}

function get_is_threshold_enabled ($data) {
    if ($data['ota_key']) {
		if($data['ota_key'] == 'obe'){
			return false;
		}
    }
    return $data['isThresholdEnabled'];
}

function get_ota_id_fn($ota_key){
	$CI = &get_instance();
    $CI->load->model('Channel_model');

    $channel = $CI->Channel_model->get_all_channels($ota_key);
    if($channel){
    	return $channel[0]['id'];
    }

    return null;
}