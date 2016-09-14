<?php

if (!defined('BASEPATH'))
    exit('No direct script access allowed');

class Rating_model extends MY_Model {

    function __construct() {
        parent::__construct();
    }

    function add($rating_insert_array) {
        if ($this->db->insert('ratings', $rating_insert_array)) {
            return $this->db->insert_id();
        }
        return 0;
    }

    function get_rating_count_by_user_id($for_users_id) {
        return $this->db->get_where('ratings', array('for_users_id' => $for_users_id))->num_rows();
    }

    function get_rating_average_by_user_id($for_users_id) {
        $this->db->select_avg('rating_value');
        return $this->db->get_where('ratings', array('for_users_id' => $for_users_id))->row_array();
    }

    function get_rating_count_by_bookings_id_and_users_id($bookings_id, $by_users_id) {
        return $this->db->get_where('ratings', array('bookings_id' => $bookings_id, 'by_users_id' => $by_users_id))->num_rows();
    }

}
