<?php

if (!defined('BASEPATH'))
    exit('No direct script access allowed');

class Auth_model extends MY_Model {

    function __construct() {
        parent::__construct();
    }

    function login($username) {
        $where = "(users.user_login = '" . $this->db->escape_str($username) . "' OR users.user_email = '" . $this->db->escape_str($username) . "') AND users.user_status = '1'";
        $this->db->join('groups', 'groups.group_id = users.groups_id');
        return $this->db->where($where)->get('users')->row_array();
    }

    function update_user_login($user_id) {
        return $this->db->where('user_id', $user_id)->update('users', array('user_last_logged_in' => date('Y-m-d H:i:s')));
    }

    function add_login_log($login_log_insert_array) {
        return $this->db->insert('login_logs', $login_log_insert_array);
    }

    function get_user_by_username_or_email($user_detail) {
        if (trim($user_detail) == '') {
            return array();
        }
        $this->db->join('groups', 'groups.group_id = users.groups_id');
        return $this->db->where('user_login', $user_detail)->or_where('user_email', $user_detail)->get('users')->row_array();
    }

    function get_user_by_social_media_id($social_media_platform, $social_media_id) {
        $this->db->join('groups', 'groups.group_id = users.groups_id');
        return $this->db->get_where('users', array('user_' . $social_media_platform . '_id' => $social_media_id))->row_array();
    }

}
