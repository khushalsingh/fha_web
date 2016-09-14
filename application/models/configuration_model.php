<?php

if (!defined('BASEPATH'))
    exit('No direct script access allowed');

class Configuration_model extends MY_Model {

    function __construct() {
        parent::__construct();
    }

    function get_all_configurations() {
        return $this->db->order_by('configuration_id', 'asc')->get('configurations')->result_array();
    }

    function update_configurations($configuration_update_array) {
        $this->db->update_batch('configurations', $configuration_update_array, 'configuration_key');
        return TRUE;
    }

    function get_configuration_by_key($configuration_key) {
        return $this->db->get_where('configurations', array('configuration_key' => $configuration_key))->row_array();
    }

}