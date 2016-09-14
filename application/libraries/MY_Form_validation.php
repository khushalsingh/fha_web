<?php

if (!defined('BASEPATH'))
	exit('No direct script access allowed');

class MY_Form_validation extends CI_Form_validation {

	function __construct() {
		parent::__construct();
	}

	function edit_unique($value, $params) {
		$CI = &get_instance();
		$CI->load->database();
		$CI->form_validation->set_message('edit_unique', "%s is already being used.");
		list($table, $field, $primary_key, $current_id) = explode(".", $params);
		$query = $CI->db->select($primary_key, TRUE)->from($table)->where(array($field => $value, $primary_key . ' !=' => $current_id))->limit(1)->get();
		if ($query->row()) {
			return FALSE;
		}
		return TRUE;
	}

	function not_matches($str, $field) {
		$CI = &get_instance();
		$CI->form_validation->set_message('not_matches', "%s should be different than %s.");
		if (!isset($_POST[$field])) {
			return FALSE;
		}
		$field = $_POST[$field];
		return ($str === $field) ? FALSE : TRUE;
	}

	function valid_url($value) {
		$CI = &get_instance();
		$CI->form_validation->set_message('valid_url', "%s is Invalid.");
		if (!filter_var($value, FILTER_VALIDATE_URL)) {
			return FALSE;
		}
		return TRUE;
	}

}
