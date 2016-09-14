<?php

if (!defined('BASEPATH'))
    exit('No direct script access allowed');

class Cron_model extends MY_Model {

    function __construct() {
        parent::__construct();
    }

    function add($cron_insert_array) {
        if ($this->db->insert('crons', $cron_insert_array)) {
            return $this->db->insert_id();
        }
        return 0;
    }

    function get_cron_row_for_current_month() {
        $this->db->like('cron_created', date('Y-m'), 'after');
        return $this->db->get_where('crons')->row_array();
    }

}