<?php

class Invoice_model extends CI_Model
{

    public function __construct()
    {
        parent::__construct();

    }

    public function create_invoice($booking_id)
    {
        // get the max invoice number
        $this->db->select("max(invoice_number) + 1 as number");
        $this->db->from("booking as b1, booking as b2, booking_x_invoice as bxi, invoice as i");
        $this->db->where("b1.booking_id = '$booking_id'");
        $this->db->where("b1.company_id = b2.company_id");
        $this->db->where("bxi.booking_id = b2.booking_id");
        $this->db->where("bxi.invoice_id = i.invoice_id");

        $query = $this->db->get();
        if ($this->db->_error_message()) {
            show_error($this->db->_error_message());
        }

        $result = $query->result();

        if (isset($result[0]->number)) {
            $new_invoice_number = $result[0]->number;
        } else {
            $new_invoice_number = 1;
        }
        // first invoice

        $data = array(
            'invoice_number' => $new_invoice_number,
        );
        $this->db->insert('invoice', $data);

        if ($this->db->_error_message()) {
            show_error($this->db->_error_message());
        }

        $query = $this->db->query('select LAST_INSERT_ID( ) AS last_id');
        $result = $query->result_array();
        if (isset($result[0])) {
            $invoice_id = $result[0]['last_id'];
        } else {
            $invoice_id = null;
        }

        $data = array(
            'invoice_id' => $invoice_id,
            'booking_id' => $booking_id,
        );
        $this->db->insert('booking_x_invoice', $data);

        if ($this->db->_error_message()) {
            show_error($this->db->_error_message());
        }
    }

}
