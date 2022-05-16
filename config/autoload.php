<?php 
$config['js-files'] = array(

    array(
        "file" => 'assets/js/online-settings.js',
         "location" => array(
          "integrations/booking_engine",
        ),
    ),
    array(
        "file" => 'assets/js/fecha.min.js',
        "location" => array(
            "online_reservation/select_dates_and_rooms",
        ),
    ), 
    array(
        "file" => 'assets/js/hotel-datepicker.js',
        "location" => array(
            "online_reservation/select_dates_and_rooms",
        ),
    ),
     array(
        "file" => 'assets/js/online-reservation.js',
        "location" => array(
            "online_reservation/select_dates_and_rooms",
             "online_reservation/show_reservations",
              "online_reservation/book_reservation",
              "online_reservation/reservation_success",
        ),
    ), 
    array(
        "file" => 'assets/js/eye.js',
        "location" => array(
            "online_reservation/select_dates_and_rooms",
        ),
    ), 
      array(
        "file" => 'assets/js/jquery.payment.js',
        "location" => array(
            "online_reservation/book_reservation",
        ),
    ), 
    array(
        "file" => 'assets/js/utils.js',
        "location" => array(
            "online_reservation/select_dates_and_rooms",
        ),
    ),
    array(
        "file" => '../../../js/helpers.js',
        "location" => array(
            "online_reservation/select_dates_and_rooms",
        ),
    ), 
    array(
        "file" => 'assets/js/moment.min.js',
        "location" => array(
            "online_reservation/select_dates_and_rooms",
             "online_reservation/show_reservations",
              "online_reservation/book_reservation",
              "online_reservation/reservation_success",
        ),
    ),
);


$config['css-files'] = array(
       array(
        "file" => 'assets/css/online-reservation.css',
        "location" => array(
            "online_reservation/select_dates_and_rooms",
         "online_reservation/show_reservations",
         "online_reservation/book_reservation",
         "online_reservation/reservation_success",
        )
    ),
    array(
        "file" => 'assets/css/hotel-datepicker.css',
        "location" => array(
            "online_reservation/select_dates_and_rooms",
        
        )
    )
);





