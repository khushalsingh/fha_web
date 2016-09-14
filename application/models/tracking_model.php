<?php

if (!defined('BASEPATH'))
    exit('No direct script access allowed');

class Tracking_model extends MY_Model {

    function __construct() {
        parent::__construct();
    }

    function add($tracking_insert_array) {
        if ($this->db->insert('trackings', $tracking_insert_array)) {
            return $this->db->insert_id();
        }
        return 0;
    }

    function get_trackings_by_user_id($user_id, $limit = 0, $offset = 0) {
        if ($limit !== 0) {
            $this->db->limit($limit, $offset);
        }
        return $this->db->order_by('tracking_id', 'desc')->get_where('trackings', array('users_id' => $user_id))->result_array();
    }

}
