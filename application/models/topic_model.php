<?php

if (!defined('BASEPATH'))
    exit('No direct script access allowed');

class Topic_model extends MY_Model {

    function __construct() {
        parent::__construct();
    }

    function add($topic_insert_array) {
        if ($this->db->insert('topics', $topic_insert_array)) {
            return $this->db->insert_id();
        }
        return 0;
    }

    function get_topics() {
        return $this->db->get('topics')->result_array();
    }

    function get_topic_by_name($topic_name) {
        return $this->db->get_where('topics', array('topic_name' => $topic_name))->row_array();
    }

}
