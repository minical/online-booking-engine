<?php 

$config = array(
    "name" => "Online Booking Engine",
    "description" => "This extension provides a customer-facing booking application that will automate booking creation. Comes with an embedable code that you can add to your existing website. [Dependencies: 'Rate Plans'].",
    "is_default_active" => 1,
    "version" => "1.0.0",
    "image_name" => "online-booking.png",
    "setting_link" => "integrations/booking_engine",
    "view_link" => "online_reservation/select_dates_and_rooms/".$this->company_id,
    "is_vendor_module" => true,
    "categories" => array("online_booking_engine"),
    "supported_in_minimal" => true,
    "marketplace_product_link" => "http://marketplace.minical.io/product/online-booking-engine/"
);