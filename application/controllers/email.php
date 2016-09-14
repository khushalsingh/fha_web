<?php

if (!defined('BASEPATH'))
    exit('No direct script access allowed');
set_time_limit(0);

class Email extends MY_Controller {

    public $public_methods = array('cron');

    function __construct() {
        parent::__construct();
        $this->load->model('Email_model');
    }

    function cron($email_id = 0) {
        $email_details_array = $this->Email_model->get_queued_email($email_id);
        $success_flag = TRUE;
        if ($email_id === 0 && count($email_details_array) > 0) {
            foreach ($email_details_array as $email_details) {
                if ($success_flag === TRUE) {
                    $email_mandrill_id = parent::send_email($email_details['email_from'], $email_details['email_from_name'], $email_details['email_to'], $email_details['email_subject'], $email_details['email_body'], json_decode($email_details['email_cc']), json_decode($email_details['email_bcc']));
                    if ($email_mandrill_id !== '') {
                        $email_update_array = array(
                            'email_mandrill_id' => $email_mandrill_id,
                            'email_status' => '1',
                            'email_modified' => date('Y-m-d H:i:s')
                        );
                        if ($this->Email_model->update_email_status($email_details['email_id'], $email_update_array)) {
                            $success_flag = TRUE;
                        } else {
                            $success_flag = FALSE;
                        }
                    }
                }
            }
            if ($success_flag === TRUE) {
                die('1');
            }
            die('0');
        }
        if ($email_id !== 0 && count($email_details_array) > 0) {
            $email_mandrill_id = parent::send_email($email_details_array['email_from'], $email_details_array['email_from_name'], $email_details_array['email_to'], $email_details_array['email_subject'], $email_details_array['email_body'], json_decode($email_details_array['email_cc']), json_decode($email_details_array['email_bcc']));
            if ($email_mandrill_id !== '') {
                $email_update_array = array(
                    'email_mandrill_id' => $email_mandrill_id,
                    'email_status' => '1',
                    'email_modified' => date('Y-m-d H:i:s')
                );
                if ($this->Email_model->update_email_status($email_details_array['email_id'], $email_update_array)) {
                    die('1');
                }
            }
        }
        die('0');
    }

}
