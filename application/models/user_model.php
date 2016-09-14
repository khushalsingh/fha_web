<?php

if (!defined('BASEPATH'))
    exit('No direct script access allowed');

class User_model extends MY_Model {

    function __construct() {
        parent::__construct();
    }

    function add($user_insert_array) {
        if ($this->db->insert('users', $user_insert_array)) {
            return $this->db->insert_id();
        }
        return 0;
    }

    function get_user_by_id($user_id) {
        $this->db->join('groups', 'groups.group_id = users.groups_id', 'left');
        return $this->db->get_where('users', array('user_id' => $user_id))->row_array();
    }

    function get_active_user_by_id_and_security_hash($user_id, $user_security_hash) {
        $this->db->join('groups', 'groups.group_id = users.groups_id', 'left');
        return $this->db->get_where('users', array('user_id' => $user_id, 'user_security_hash' => $user_security_hash, 'user_status' => '1'))->row_array();
    }

    function edit_user_by_user_id($user_id, $user_details_array) {
        return $this->db->where('user_id', $user_id)->update('users', $user_details_array);
    }

    function edit_user_skills_by_user_id($user_id, $user_skills_array) {
        if ($this->db->where('users_id', $user_id)->delete('user_skills')) {
            $this->db->insert_batch('user_skills', $user_skills_array);
            return TRUE;
        }
        return FALSE;
    }

    function get_active_coaches($limit = 0, $offset = 0) {
        if ($limit !== 0) {
            $this->db->limit($limit, $offset);
        }
        return $this->db->get_where('users', array('groups_id' => 2, 'user_status' => 1))->result_array();
    }

    function get_all_users() {
        return $this->db->get_where('users', array('groups_id' => 3))->result_array();
    }

    function search_coaches($search, $limit, $offset) {
        $this->db->where("groups_id = 2 AND user_status = 1 AND (user_login LIKE '%" . $search . "%' OR user_first_name LIKE '%" . $search . "%' OR user_last_name LIKE '%" . $search . "%')");
        return $this->db->limit($limit, $offset)->get('users')->result_array();
    }

    function clear_free_tokens_per_month() {
        return $this->db->update('users', array('user_free_tokens' => '0'));
    }

}
