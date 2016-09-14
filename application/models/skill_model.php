<?php

if (!defined('BASEPATH'))
    exit('No direct script access allowed');

class Skill_model extends MY_Model {

    function __construct() {
        parent::__construct();
    }

    function get_skills() {
        return $this->db->get('skills')->result_array();
    }

}
