<?php

if (!defined('BASEPATH'))
    exit('No direct script access allowed');

class Survey extends MY_Controller {

    public $public_methods = array();

    function __construct() {
        parent::__construct();
        $this->load->model('User_model');
    }

    function index() {
        parent::allow(array('administrator'));
        $data = array();
        parent::render_view($data);
    }

    function datatable() {
        parent::allow(array('administrator'));
        $this->load->library('Datatables');
        $this->datatables->where(array('users.survey_remarks !=' => '', 'users.terms_accepted' => '1', 'users.user_status !=' => '-1'));
        $this->datatables->where_in('groups_id', array('2', '3'));
        $this->datatables->select("user_id,groups_id,CONCAT(user_first_name,' ',user_last_name) AS user_full_name,user_email,user_primary_contact,user_gender,survey_remarks", FALSE)->from('users');
        echo $this->datatables->generate();
    }

}