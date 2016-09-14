<?php

if (!defined('BASEPATH'))
    exit('No direct script access allowed');

class User extends MY_Controller {

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
        $this->datatables->where(array('users.groups_id' => '3', 'users.user_status !=' => '-1'));
        $this->datatables->select("user_id,CONCAT(user_first_name,' ',user_last_name) AS user_full_name,user_email,user_primary_contact,user_gender,user_status", FALSE)->from('users');
        echo $this->datatables->generate();
    }

    function change_password() {
        parent::allow(array('administrator', 'coach'));
        $data = array();
        $data['user_details_array'] = $this->User_model->get_user_by_id($_SESSION['user']['user_id']);
        if ($data['user_details_array']['user_status'] === '-1') {
            redirect('auth/logout');
        }
        $this->load->library('encrypt');
        $this->load->helper('form');
        if ($this->input->post()) {
            $this->load->library('form_validation');
            $this->form_validation->set_rules('user_login_password', 'Password', 'trim|required|min_length[8]');
            $this->form_validation->set_rules('user_confirm_password', 'Confirm Password ', 'trim|required|matches[user_login_password]');
            $this->form_validation->set_error_delimiters('', '<br />');
            if ($this->form_validation->run()) {
                $time_now = date('Y-m-d H:i:s');
                $user_details_array = array(
                    'user_login_salt' => md5($time_now),
                    'user_login_password' => md5(md5(md5($time_now) . $this->input->post('user_login_password'))),
                    'user_password_hash' => $this->encrypt->encode($this->input->post('user_login_password'), md5(md5(md5($time_now) . $this->input->post('user_login_password')))),
                    'force_change_password' => '0',
                    'user_modified' => $time_now
                );
                if ($this->User_model->edit_user_by_user_id($_SESSION['user']['user_id'], $user_details_array)) {
                    parent::regenerate_session();
                    die('1');
                }
            } else {
                echo validation_errors();
                die;
            }
            die('Error Changing Password !!!');
        }
        parent::render_view($data);
    }

    function coach() {
        parent::allow(array('administrator'));
        $data = array();
        parent::render_view($data);
    }

    function coach_datatable() {
        parent::allow(array('administrator'));
        $this->load->library('Datatables');
        $this->datatables->where(array('users.groups_id' => '2', 'users.user_status !=' => '-1'));
        $this->datatables->select("user_id,CONCAT(user_first_name,' ',user_last_name) AS user_full_name,user_email,user_primary_contact,user_gender,user_status", FALSE)->from('users');
        echo $this->datatables->generate();
    }

    function coach_availability() {
        parent::allow(array('administrator', 'coach'));
        $data = array();
        parent::render_view($data);
    }

    function coach_availability_datatable() {
        parent::allow(array('administrator', 'coach'));
        $this->load->library('Datatables');
        $this->datatables->where(array('users.groups_id' => '2', 'users.user_status !=' => '-1'));
        $this->datatables->join('users', 'users.user_id = availabilities.users_id');
        $this->datatables->join('topics', 'topics.topic_id = availabilities.topics_id');
        if ($_SESSION['user']['group_slug'] === 'coach') {
            $this->datatables->where(array('users.user_id' => $_SESSION['user']['user_id']));
        }
        $this->datatables->where(array('availability_from >' => date('Y-m-d H:i:s')));
        $this->datatables->select("user_id,CONCAT(user_first_name,' ',user_last_name) AS user_full_name,user_email,user_primary_contact,availability_for,topic_name,DATE_FORMAT(availability_from,'%d %b %Y %h:%i %p'),DATE_FORMAT(availability_to,'%d %b %Y %h:%i %p'),DATE_FORMAT(availability_created,'%d %b %Y %h:%i %p')", FALSE)->from('availabilities');
        echo $this->datatables->generate();
    }

    function delete() {
        parent::allow(array('administrator'));
        if ($this->input->post()) {
            $this->load->library('form_validation');
            $this->form_validation->set_rules('users_id', 'User Id', 'trim|required|is_natural_no_zero');
            $this->form_validation->set_error_delimiters('', '<br />');
            if ($this->form_validation->run()) {
                $user_details_array = $this->User_model->get_user_by_id($this->input->post('users_id'));
                if (count($user_details_array) > 0) {
                    $user_update_array = array(
                        'user_status' => '-1',
                        'user_modified' => date('Y-m-d H:i:s')
                    );
                    if ($this->User_model->edit_user_by_user_id($this->input->post('users_id'), $user_update_array)) {
                        die('1');
                    }
                }
            } else {
                echo validation_errors();
                die;
            }
        }
        die('0');
    }

    function add_coach() {
        parent::allow(array('administrator'));
        if ($this->input->post()) {
            $this->load->library('form_validation');
            $this->form_validation->set_rules('user_first_name', 'First Name', 'trim|required');
            $this->form_validation->set_rules('user_last_name', 'Last Name', 'trim|required');
            $this->form_validation->set_rules('user_gender', 'Gender', 'trim|required');
            $this->form_validation->set_rules('user_primary_contact', 'Contact', 'trim|required|min_length[10]|numeric|is_unique[users.user_primary_contact]');
            $this->form_validation->set_rules('user_email', 'Email', 'trim|required|valid_email|is_unique[users.user_login]|is_unique[users.user_email]');
            $this->form_validation->set_rules('user_description', 'Description', 'trim');
            $this->form_validation->set_rules('user_login_password', 'Password', 'trim|required|min_length[8]');
            $this->form_validation->set_error_delimiters('', '<br />');
            if ($this->form_validation->run()) {
                $time_now = date('Y-m-d H:i:s');
                $user_insert_array = array(
                    'groups_id' => '2',
                    'user_login' => $this->input->post('user_email'),
                    'user_first_name' => $this->input->post('user_first_name'),
                    'user_last_name' => $this->input->post('user_last_name'),
                    'user_email' => $this->input->post('user_email'),
                    'user_primary_contact' => $this->input->post('user_primary_contact'),
                    'user_gender' => $this->input->post('user_gender'),
                    'user_description' => $this->input->post('user_description'),
                    'user_login_salt' => md5($time_now),
                    'user_login_password' => md5(md5(md5($time_now) . $this->input->post('user_login_password'))),
                    'user_password_hash' => $this->encrypt->encode($this->input->post('user_login_password'), md5(md5(md5($time_now) . $this->input->post('user_login_password')))),
                    'user_security_hash' => md5($time_now . $this->input->post('user_email')),
                    'user_status' => '1',
                    'force_change_password' => '1',
                    'user_created' => $time_now
                );
                $user_id = $this->User_model->add($user_insert_array);
                if ($user_id > 0) {
                    $email_details_array = array(
                        'user_first_name' => $this->input->post('user_first_name'),
                        'user_last_name' => $this->input->post('user_last_name'),
                        'user_email' => $this->input->post('user_email'),
                        'user_login_password' => $this->input->post('user_login_password')
                    );
                    $email_id = parent::add_email_to_queue('', '', $this->input->post('user_email'), $user_id, 'Congratulations , you are now registered as a coach', parent::render_view($email_details_array, 'email', 'email/templates/add_coach', TRUE));
                    if ($email_id > 0) {
                        $file_contents = file_get_contents(base_url() . 'email/cron/' . $email_id);
                        if ($file_contents === '1') {
                            die('1');
                        }
                    }
                }
            } else {
                echo validation_errors();
                die;
            }
            die('0');
        }
        $data = array();
        parent::render_view($data);
    }

    function change_status() {
        parent::allow(array('administrator'));
        if ($this->input->post()) {
            $this->load->library('form_validation');
            $this->form_validation->set_rules('user_id', 'User Id', 'trim|required|is_natural_no_zero');
            $this->form_validation->set_rules('user_status', 'User Status', 'trim|required');
            $this->form_validation->set_error_delimiters('', '<br />');
            if ($this->form_validation->run()) {
                $time_now = date('Y-m-d H:i:s');
                $user_details_array = array(
                    'user_status' => ($this->input->post('user_status') === 'true') ? '1' : '0',
                    'user_modified' => $time_now
                );
                if ($this->User_model->edit_user_by_user_id($this->input->post('user_id'), $user_details_array)) {
                    // Add Email to admin and user that account is blocked or activated.
                    die('1');
                }
            } else {
                echo validation_errors();
                die;
            }
        }
        die('0');
    }

    function tracking_progress($user_id = 0) {
        if ($user_id == 0) {
            redirect('user', 'refresh');
            exit;
        }
        $data = array();
        $data['user_details_array'] = $this->User_model->get_user_by_id($user_id);
        if (count($data['user_details_array']) > 0 && isset($data['user_details_array']['group_slug']) && $data['user_details_array']['group_slug'] === 'user' && $data['user_details_array']['user_status'] === '1') {
            $this->load->model('Tracking_model');
            $trackings_array = $this->Tracking_model->get_trackings_by_user_id($user_id);
            foreach ($trackings_array as $key => $tracking) {
                $trackings_array[$key]['tracking_image_url'] = base_url() . 'assets/img/profile.png';
                if (is_file(FCPATH . 'uploads/trackings' . date('/Y/m/d/H/i/s/', strtotime($tracking['tracking_created'])) . $tracking['tracking_image'])) {
                    $trackings_array[$key]['tracking_image_url'] = base_url() . 'uploads/trackings' . date('/Y/m/d/H/i/s/', strtotime($tracking['tracking_created'])) . $tracking['tracking_image'];
                }
            }
            $data['trackings_array'] = $trackings_array;
            parent::render_view($data);
        } else {
            redirect('user', 'refresh');
        }
    }

    function earnings($user_id = 0, $year = 0, $month = 0) {
        if ($user_id == 0) {
            $user_id = $_SESSION['user']['user_id'];
        }
        parent::allow(array('administrator', 'coach'));
        if ($_SESSION['user']['group_slug'] === 'coach' && $_SESSION['user']['user_id'] != $user_id) {
            redirect('dashboard', 'refresh');
        }
        $data = array();
        $data['user_details_array'] = $this->User_model->get_user_by_id($user_id);
        if (count($data['user_details_array']) > 0 && $data['user_details_array']['user_status'] === '1' && isset($data['user_details_array']['group_slug']) && $data['user_details_array']['group_slug'] === 'coach') {
            if ($year === 0) {
                $year = date('Y');
            }
            if ($month === 0) {
                $month = date('m');
            }
            $this->load->model('Booking_model');
            $data['monthly_bookings_array'] = $this->Booking_model->get_monthly_bookings_by_user_id($user_id, $year, $month);
            $data['year'] = $year;
            $data['month'] = $month;
            parent::render_view($data);
        } else {
            redirect('user', 'refresh');
        }
    }

}