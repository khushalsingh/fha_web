<?php

if (!defined('BASEPATH'))
    exit('No direct script access allowed');

class Cron extends MY_Controller {

    public $public_methods = array('index');

    function __construct() {
        parent::__construct();
        $this->load->model('Cron_model');
    }

    function index() {
        $cron_row_for_current_month_array = $this->Cron_model->get_cron_row_for_current_month();
        if (count($cron_row_for_current_month_array) == 0) {
            $this->load->model('User_model');
            $this->load->model('User_token_model');
            $users_array = $this->User_model->get_all_users();
            $user_tokens_batch_insert_array = array();
            foreach ($users_array as $user) {
                if ($user['user_free_tokens'] > 0) {
                    $user_tokens_batch_insert_array[] = array(
                        'users_id' => $user['user_id'],
                        'user_token_value' => '-' . $user['user_free_tokens'],
                        'user_token_paid' => '0',
                        'user_token_refund' => '1',
                        'user_token_created' => '0000-00-00 00:00:00'
                    );
                }
            }
            if ($this->User_token_model->batch_insert($user_tokens_batch_insert_array)) {
                $this->User_model->clear_free_tokens_per_month();
                $cron_insert_array = array(
                    'cron_value' => 'clear_free_tokens_per_month',
                    'cron_created' => date('Y-m-d H:i:s')
                );
                if ($this->Cron_model->add($cron_insert_array)) {
                    die('1');
                }
            }
        }
        die('0');
    }

}