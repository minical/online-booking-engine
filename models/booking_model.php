
<?php

class Booking_model extends CI_Model
{

    public function __construct()
    {
        parent::__construct();
        $this->load->library("Forecast_charges");
    }

    public function create_booking($data)
    {
        $this->db->insert("booking", $data);

        if ($this->db->_error_message()) {
            show_error($this->db->_error_message());
        }

        $query = $this->db->query('select LAST_INSERT_ID( ) AS last_id');
        $result = $query->result_array();
        if (isset($result[0])) {
            $new_booking_id = $result[0]['last_id'];
        } else {
            return null;
        }

        // Generate new invoice_hash for the new booking (for guest to check the invoice later)
        $this->load->helper("guid");

        $guid = generate_guid();
        $count = 0;
        while ($count < 5 || $this->get_booking_id_from_invoice_hash($guid)) {
            $guid = generate_guid();
            $count++;
        }

        $this->db->query("UPDATE booking SET invoice_hash = '" . $guid . "' WHERE booking_id = '" . $new_booking_id . "'");

        if ($this->db->_error_message()) {
            show_error($this->db->_error_message());
        }
        // echo $this->db->last_query();
        return $new_booking_id;
    }

    public function update_booking($booking_id, $data)
    {
        $data = (object) $data;
        $this->db->where('booking_id', $booking_id);
        $this->db->update("booking", $data);
    }

    public function update_booking_balance($booking_id, $return_type = 'balance')
    {
        if (!$booking_id) {
            return null;
        }

        $sql = "SELECT *,
                    IFNULL(
                    (
                        SELECT
                            SUM(charge_amount) as charge_total
                        FROM (
                            SELECT
                               (
                                   ch.amount +
                                   SUM(
                                        IF(tt.is_tax_inclusive = 1,
                                            0,
                                            (ch.amount * IF(tt.is_percentage = 1, IF(tt.is_brackets_active, tpb.tax_rate, tt.tax_rate), 0) * 0.01) +
                                            IF(tt.is_percentage = 0, IF(tt.is_brackets_active, tpb.tax_rate, tt.tax_rate), 0)
                                        )
                                    )
                               ) as charge_amount
                            FROM charge as ch
                            LEFT JOIN charge_type as ct ON ch.charge_type_id = ct.id AND ct.is_deleted = '0'
                            LEFT JOIN charge_type_tax_list AS cttl ON ct.id = cttl.charge_type_id
                            LEFT JOIN tax_type AS tt ON tt.tax_type_id = cttl.tax_type_id AND tt.is_deleted = '0'
                            LEFT JOIN tax_price_bracket as tpb
                                ON tpb.tax_type_id = tt.tax_type_id AND ch.amount BETWEEN tpb.start_range AND tpb.end_range
                            WHERE
                                ch.is_deleted = '0' AND
                                ch.booking_id = '$booking_id'
                            GROUP BY ch.charge_id
                        ) as total
                    ), 0
                ) as charge_total,
                IFNULL(
                    (
                        SELECT SUM(p.amount) as payment_total
                        FROM payment as p, payment_type as pt
                        WHERE
                            p.is_deleted = '0' AND
                            #pt.is_deleted = '0' AND
                            p.payment_type_id = pt.payment_type_id AND
                            p.booking_id = b.booking_id

                        GROUP BY p.booking_id
                    ), 0
                ) as payment_total
            FROM booking as b
            LEFT JOIN booking_block as brh ON b.booking_id = brh.booking_id
            WHERE b.booking_id = '$booking_id'
        ";

        $query = $this->db->query($sql);
        $result = $query->result_array();
        $booking = null;
        if ($query->num_rows >= 1 && isset($result[0])) {
            $booking = $result[0];
        }

        if ($booking) {
            $forecast = $this->forecast_charges->_get_forecast_charges($booking_id, true);
            $forecast_extra = $this->forecast_charges->_get_forecast_extra_charges($booking_id, true);
            $booking_charge_total_with_forecast = (floatval($booking['charge_total']) + floatval($forecast['total_charges']) + floatval($forecast_extra));
            $data = array(
                'booking_id' => $booking_id,
                'balance' => $this->jsround(floatval($booking_charge_total_with_forecast) - floatval($booking['payment_total']), 2),
                'balance_without_forecast' => $this->jsround(floatval($booking['charge_total']) - floatval($booking['payment_total']), 2),
            );
            $this->update_booking($booking_id, $data);
            return $data[$return_type];
        }
        return null;
    }

    public function jsround($float, $precision = 0)
    {
        $float = floatval(number_format($float, 12, '.', ''));
        if ($float < 0) {
            return round($float, $precision, PHP_ROUND_HALF_DOWN);
        }
        return round($float, $precision);
    }
}