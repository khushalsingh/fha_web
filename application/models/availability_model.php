<?php

if (!defined('BASEPATH'))
    exit('No direct script access allowed');

class Availability_model extends MY_Model {

    function __construct() {
        parent::__construct();
    }

    function add($availabilities_insert_array) {
        if ($this->db->insert('availabilities', $availabilities_insert_array)) {
            return $this->db->insert_id();
        }
        return 0;
    }

    function get_coach_availabilities($users_id, $availability_for, $availability_from = '', $availability_to = '') {
        if ($availability_for != '') {
            $this->db->where('availability_for', $availability_for);
        }
        if ($availability_from != '') {
            $this->db->where('availability_from >=', $availability_from);
        }
        if ($availability_to != '') {
            $this->db->where('availability_to <=', $availability_to);
        }
        $this->db->join('topics', 'topics.topic_id = availabilities.topics_id');
        return $this->db->get_where('availabilities', array('users_id' => $users_id))->result_array();
    }

    function get_availability_by_id($availability_id) {
        return $this->db->get_where('availabilities', array('availability_id' => $availability_id))->row_array();
    }

    function update($availability_id, $availability_update_array) {
        return $this->db->where('availability_id', $availability_id)->update('availabilities', $availability_update_array);
    }

}