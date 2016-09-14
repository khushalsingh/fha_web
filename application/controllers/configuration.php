<?php

if (!defined('BASEPATH'))
    exit('No direct script access allowed');

class Configuration extends MY_Controller {

    public $public_methods = array();

    function __construct() {
        parent::__construct();
        $this->load->model('Configuration_model');
    }

    function index() {
        parent::allow(array('administrator'));
        if ($this->input->post()) {
            $this->load->library('form_validation');
            $this->form_validation->set_rules('free_tokens_per_month', 'Free Tokens Per Month', 'trim|required|is_natural_no_zero');
            $this->form_validation->set_rules('token_message', 'Token Message', 'trim|required');
            $this->form_validation->set_error_delimiters('', '<br />');
            if ($this->form_validation->run()) {
                $configuration_update_array = array();
                foreach ($this->input->post() as $key => $value) {
                    $configuration_update_array[] = array(
                        'configuration_key' => $key,
                        'configuration_value' => $value
                    );
                }
                if ($this->Configuration_model->update_configurations($configuration_update_array)) {
                    die('1');
                }
            } else {
                echo validation_errors();
                die;
            }
            die('0');
        }
        $data = array();
        $data['configurations_array'] = $this->Configuration_model->get_all_configurations();
        parent::render_view($data);
    }

}