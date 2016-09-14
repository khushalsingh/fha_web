<?php

if (!defined('BASEPATH'))
    exit('No direct script access allowed');

class User_token_model extends MY_Model {

    function __construct() {
        parent::__construct();
    }

    function add($user_tokens_insert_array) {
        if ($this->db->insert('user_tokens', $user_tokens_insert_array)) {
            return $this->db->insert_id();
        }
        return 0;
    }

    function batch_insert($user_tokens_batch_insert_array) {
        $this->db->insert_batch('user_tokens', $user_tokens_batch_insert_array);
        return TRUE;
    }

    function get_free_tokens_row_for_current_month($users_id) {
        $this->db->like('user_token_created', date('Y-m'), 'after');
        return $this->db->get_where('user_tokens', array('users_id' => $users_id, 'user_token_paid' => '0', 'user_token_refund' => '0'))->row_array();
    }

    function get_user_tokens_by_user_id($users_id) {
        $this->db->select("user_token_paid, SUM(user_token_value) AS user_token_value", FALSE);
        $this->db->group_by('user_token_paid');
        return $this->db->get_where('user_tokens', array('users_id' => $users_id))->result_array();
    }

}