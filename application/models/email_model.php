<?php

if (!defined('BASEPATH'))
    exit('No direct script access allowed');

class Email_model extends MY_Model {

    function __construct() {
        parent::__construct();
    }

    function add_email_to_queue($email_insert_array) {
        if ($this->db->insert('emails', $email_insert_array)) {
            return $this->db->insert_id();
        }
        return 0;
    }

    function get_queued_email($email_id) {
        if ($email_id !== 0) {
            return $this->db->get_where('emails', array('email_status' => '0', 'email_id' => $email_id))->row_array();
        } else {
            return $this->db->get_where('emails', array('email_status' => '0'), '20', '0')->result_array();
        }
    }

    function update_email_status($email_id, $email_update_array) {
        if (is_numeric($email_id)) {
            $this->db->where('email_id', $email_id);
        } else {
            $this->db->where('email_mandrill_id', $email_id);
        }
        return $this->db->update('emails', $email_update_array);
    }

    function get_email_by_id($email_id) {
        return $this->db->get_where('emails', array('email_id' => $email_id))->row_array();
    }

}
