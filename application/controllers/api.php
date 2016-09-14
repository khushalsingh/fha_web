<?php

if (!defined('BASEPATH'))
    exit('No direct script access allowed');
define('PAGINATION', 7);
define('MAX_FILE_SIZE', 5000000);

class Api extends MY_Controller {

    public $public_methods = array();

    function __construct() {
        parent::__construct();
        if (!$this->input->post()) {
            $this->output->set_status_header('401');
            die;
        }
    }

    private function _error() {
        parent::json_output(array('code' => '0', 'message' => 'Internal Server Error !!!'));
        return;
    }

    private function _get_user() {
        $this->load->library('form_validation');
        $this->form_validation->set_rules('user_id', 'User ID', 'trim|required|is_natural_no_zero');
        $this->form_validation->set_rules('user_security_hash', 'User Security Hash', 'trim|required|exact_length[32]');
        if ($this->form_validation->run()) {
            $this->load->model('User_model');
            return $this->User_model->get_active_user_by_id_and_security_hash($this->input->post('user_id'), $this->input->post('user_security_hash'));
        }
        return array();
    }

    private function _update_user_tokens($user_id) {
        $this->load->model('User_model');
        $this->load->model('User_token_model');
        $user_token_details_array = $this->User_token_model->get_user_tokens_by_user_id($user_id);
        $free_token_count = $paid_token_count = 0;
        foreach ($user_token_details_array as $user_token_detail) {
            if (isset($user_token_detail['user_token_paid']) && $user_token_detail['user_token_paid'] === '0') {
                $free_token_count = (int) $user_token_detail['user_token_value'];
            }
            if (isset($user_token_detail['user_token_paid']) && $user_token_detail['user_token_paid'] === '1') {
                $paid_token_count = (int) $user_token_detail['user_token_value'];
            }
        }
        $user_update_array = array(
            'user_paid_tokens' => $paid_token_count,
            'user_free_tokens' => $free_token_count,
            'user_modified' => date('Y-m-d H:i:s')
        );
        if ($this->User_model->edit_user_by_user_id($user_id, $user_update_array)) {
            $user_update_array['user_token_count'] = $paid_token_count + $free_token_count;
            return $user_update_array;
        }
        return array();
    }

    private function _get_user_image($user_details_array) {
        if (is_file(FCPATH . 'uploads/users' . date('/Y/m/d/H/i/s/', strtotime($user_details_array['user_created'])) . $user_details_array['user_profile_thumb'])) {
            return base_url() . 'uploads/users' . date('/Y/m/d/H/i/s/', strtotime($user_details_array['user_created'])) . $user_details_array['user_profile_thumb'];
        }
        return base_url() . 'assets/img/profile.png';
    }

    private function _get_all_configurations() {
        $this->load->model('Configuration_model');
        $configurations_array = $this->Configuration_model->get_all_configurations();
        $configurations_key_value_array = array();
        foreach ($configurations_array as $configuration) {
            $configurations_key_value_array[$configuration['configuration_key']] = $configuration['configuration_value'];
        }
        return $configurations_key_value_array;
    }

    function index() {
        parent::json_output(array('code' => '1', 'message' => 'OK', 'data' => array('configurations' => $this->_get_all_configurations())));
        return;
    }

    function login() {
        $this->load->library('form_validation');
        $this->form_validation->set_rules('user_login', 'Username OR Email ID', 'trim|required|min_length[5]');
        $this->form_validation->set_rules('user_login_password', 'Password', 'trim|required|min_length[5]');
        if ($this->form_validation->run()) {
            $this->load->model('Auth_model');
            $user_details_array = $this->Auth_model->login(trim($this->input->post('user_login')));
            if (
                    count($user_details_array) > 0 &&
                    strtolower(trim($this->input->post('user_login_password'))) === strtolower($this->encrypt->decode($user_details_array['user_password_hash'], $user_details_array['user_login_password']))
            ) {
                $this->Auth_model->update_user_login($user_details_array['user_id']);
                $this->Auth_model->add_login_log(array(
                    'users_id' => $user_details_array['user_id'],
                    'login_log_from' => '2',
                    'login_log_mode' => 'mobile',
                    'login_log_ip_address' => $this->input->server('REMOTE_ADDR'),
                    'login_log_user_agent' => $this->input->server('HTTP_USER_AGENT'),
                    'login_log_created' => date('Y-m-d H:i:s')
                ));
                $user_details_array['configurations'] = $this->_get_all_configurations();
                $user_details_array['user_profile_image_url'] = $this->_get_user_image($user_details_array);
                parent::json_output(array('code' => '1', 'message' => 'Logged In Successfully.', 'data' => $user_details_array));
                return;
            } else {
                parent::json_output(array('code' => '-1', 'message' => 'Invalid User Credentials.'));
                return;
            }
        } else {
            parent::json_output(array('code' => '0', 'message' => 'Invalid Email ID OR Password.'));
            return;
        }
        $this->_error();
    }

    function session_login() {
        $user_details_array = $this->_get_user();
        if (count($user_details_array) > 0) {
            $this->load->model('Auth_model');
            $this->Auth_model->update_user_login($user_details_array['user_id']);
            $this->Auth_model->add_login_log(array(
                'users_id' => $user_details_array['user_id'],
                'login_log_from' => '2',
                'login_log_mode' => 'mobile',
                'login_log_ip_address' => $this->input->server('REMOTE_ADDR'),
                'login_log_user_agent' => $this->input->server('HTTP_USER_AGENT'),
                'login_log_created' => date('Y-m-d H:i:s')
            ));
            $user_tokens_array = $this->_update_user_tokens($user_details_array['user_id']);
            $user_details_array['user_paid_tokens'] = $user_tokens_array['user_paid_tokens'];
            $user_details_array['user_free_tokens'] = $user_tokens_array['user_free_tokens'];
            $user_details_array['user_profile_image_url'] = $this->_get_user_image($user_details_array);
            $user_details_array['user_token_count'] = $user_details_array['user_paid_tokens'] + $user_details_array['user_free_tokens'];
            $user_details_array['configurations'] = $this->_get_all_configurations();
            $this->load->model('User_token_model');
            $free_token_redeemed_array = $this->User_token_model->get_free_tokens_row_for_current_month($user_details_array['user_id']);
            $user_details_array['user_free_tokens_available'] = '1';
            if (count($free_token_redeemed_array) > 0) {
                $user_details_array['user_free_tokens_available'] = '0';
            }
            parent::json_output(array('code' => '1', 'message' => 'Logged In Successfully.', 'data' => $user_details_array));
            return;
        } else {
            parent::json_output(array('code' => '-1', 'message' => 'Invalid User Credentials.'));
            return;
        }

        $this->_error();
    }

    function recover() {
        $this->load->library('form_validation');
        $this->form_validation->set_rules('email_address', 'User ID OR Email', 'trim|required');
        if ($this->form_validation->run()) {
            $this->load->model('Auth_model');
            $user_details_array = $this->Auth_model->get_user_by_username_or_email($this->input->post('email_address'));
            if (count($user_details_array) > 0 && isset($user_details_array['group_slug']) && $user_details_array['group_slug'] === 'coach') {
                $new_password = parent::generate_random_string();
                $time_now = date('Y-m-d H:i:s');
                $this->load->library('encrypt');
                $user_update_array = array(
                    'user_login_salt' => md5($time_now),
                    'user_login_password' => md5(md5(md5($time_now) . $new_password)),
                    'user_password_hash' => $this->encrypt->encode($new_password, md5(md5(md5($time_now) . $new_password))),
                    'user_security_hash' => md5($time_now . $new_password),
                    'user_modified' => $time_now,
                    'force_change_password' => '1'
                );
                $this->load->model('User_model');
                if ($this->User_model->edit_user_by_user_id($user_details_array['user_id'], $user_update_array)) {
                    $email_details_array = array(
                        'user_first_name' => $user_details_array['user_first_name'],
                        'user_last_name' => $user_details_array['user_last_name'],
                        'user_email' => $user_details_array['user_email'],
                        'user_login_password' => $new_password
                    );
                    $email_id = parent::add_email_to_queue('', '', $user_details_array['user_email'], $user_details_array['user_id'], 'Your Account Password', $this->render_view($email_details_array, 'email', 'email/templates/forgot_password', TRUE));
                    if ($email_id > 0) {
                        $file_contents = file_get_contents(base_url() . 'email/cron/' . $email_id);
                        if ($file_contents === '1') {
                            parent::json_output(array('code' => '1', 'message' => 'We have sent an email with new password.', 'data' => $user_update_array['user_security_hash']));
                            return;
                        }
                    }
                }
            } else {
                parent::json_output(array('code' => '0', 'message' => 'Invalid User !!!'));
                return;
            }
        } else {
            parent::json_output(array('code' => '0', 'message' => 'Invalid Email ID !!!'));
            return;
        }
        $this->_error();
    }

    function change_password() {
        $user_details_array = $this->_get_user();
        if (count($user_details_array) > 0 && isset($user_details_array['group_slug']) && $user_details_array['group_slug'] === 'coach') {
            $this->load->library('form_validation');
            $this->form_validation->set_rules('user_login_password', 'Password', 'trim|required');
            $this->form_validation->set_rules('confirm_login_password', 'Confirm Password', 'trim|required|matches[user_login_password]');
            if ($this->form_validation->run()) {
                $new_password = $this->input->post('user_login_password');
                $time_now = date('Y-m-d H:i:s');
                $this->load->library('encrypt');
                $user_update_array = array(
                    'user_login_salt' => md5($time_now),
                    'user_login_password' => md5(md5(md5($time_now) . $new_password)),
                    'user_password_hash' => $this->encrypt->encode($new_password, md5(md5(md5($time_now) . $new_password))),
                    'user_security_hash' => md5($time_now . $new_password),
                    'user_modified' => $time_now
                );
                $this->load->model('User_model');
                if ($this->User_model->edit_user_by_user_id($user_details_array['user_id'], $user_update_array)) {
                    parent::json_output(array('code' => '1', 'message' => 'Password Changed Successfully.', 'data' => $user_update_array));
                    return;
                }
            }
        }
        $this->_error();
    }

    function redeem_free_tokens() {
        $user_details_array = $this->_get_user();
        if (count($user_details_array) > 0 && isset($user_details_array['group_slug']) && $user_details_array['group_slug'] === 'user') {
            $this->load->model('User_token_model');
            $free_token_redeemed_array = $this->User_token_model->get_free_tokens_row_for_current_month($user_details_array['user_id']);
            if (count($free_token_redeemed_array) > 0) {
                parent::json_output(array('code' => '0', 'message' => 'Free Tokens Already Redeemed !!!'));
                return;
            } else {
                $this->load->model('Configuration_model');
                $free_tokens_per_month = $this->Configuration_model->get_configuration_by_key('free_tokens_per_month');
                if ($free_tokens_per_month['configuration_value'] > 0) {
                    $user_tokens_insert_array = array(
                        'users_id' => $user_details_array['user_id'],
                        'user_token_value' => $free_tokens_per_month['configuration_value'],
                        'user_token_paid' => '0',
                        'user_token_refund' => '0',
                        'user_token_created' => date('Y-m-d H:i:s')
                    );
                    if ($this->User_token_model->add($user_tokens_insert_array) > 0) {
                        parent::json_output(array('code' => '1', 'message' => 'Free Tokens Redeemed Successfully.', 'data' => $this->_update_user_tokens($user_details_array['user_id'])));
                        return;
                    }
                }
            }
        }
        $this->_error();
    }

    function add_coach_availability() {
        $user_details_array = $this->_get_user();
        if (count($user_details_array) > 0 && isset($user_details_array['group_slug']) && $user_details_array['group_slug'] === 'coach') {
            $this->form_validation->set_rules('availability_for', 'Availability For', 'trim|required|is_natural');
            $this->form_validation->set_rules('topic_name', 'Topic', 'trim|required');
            $this->form_validation->set_rules('availability_from', 'Availability From', 'trim|required');
            $this->form_validation->set_rules('availability_length', 'Availability Length', 'trim|required|is_natural_no_zero');
            if ($this->form_validation->run()) {
//                 Coach can add availability 15 minutes ahead from current time
                if (strtotime($this->input->post('availability_from')) > (time() + (15 * 60))) {
//                    Availability can only be added for next 30 days
                    if (strtotime($this->input->post('availability_from')) <= (time() + (30 * 86400))) {
                        $availability_to = date('Y-m-d H:i:s', strtotime($this->input->post('availability_from')) + ($this->input->post('availability_length') * 60));
                        $this->load->model('Availability_model');
                        $availability_details_array = $this->Availability_model->get_coach_availabilities($user_details_array['user_id'], '', $this->input->post('availability_from'), $availability_to);
                        if (count($availability_details_array) > 0) {
                            parent::json_output(array('code' => '-1', 'message' => 'Coach Availability already exists !!!'));
                            return;
                        } else {
                            $this->load->model('Topic_model');
                            $topic_details_array = $this->Topic_model->get_topic_by_name($this->input->post('topic_name'));
                            if (count($topic_details_array) > 0 && isset($topic_details_array['topic_id'])) {
                                $topics_id = $topic_details_array['topic_id'];
                            } else {
                                $topics_id = $this->Topic_model->add(array(
                                    'topic_name' => $this->input->post('topic_name'),
                                    'topic_created' => date('Y-m-d H:i:s')
                                ));
                            }
                            if ($topics_id == 0) {
                                parent::json_output(array('code' => '-1', 'message' => 'Error Adding Topic !!!'));
                                return;
                            } else {
                                $availabilities_insert_array = array(
                                    'conference_id' => parent::generate_random_string('alnum', 32),
                                    'users_id' => $user_details_array['user_id'],
                                    'availability_for' => $this->input->post('availability_for'),
                                    'topics_id' => $topics_id,
                                    'availability_from' => $this->input->post('availability_from'),
                                    'availability_to' => $availability_to,
                                    'availability_length' => $this->input->post('availability_length'),
                                    'availability_created' => date('Y-m-d H:i:s')
                                );
                                if ($this->Availability_model->add($availabilities_insert_array)) {
                                    parent::json_output(array('code' => '1', 'message' => 'Availability Added Successfully.'));
                                    return;
                                }
                            }
                        }
                    } else {
                        parent::json_output(array('code' => '0', 'message' => 'Availabilities can only be added for next 30 days !!!'));
                        return;
                    }
                } else {
                    parent::json_output(array('code' => '-1', 'message' => 'Availabilities can be added 15 minutes prior !!!'));
                    return;
                }
            }
        }
        $this->_error();
    }

    function get_active_coaches() {
        $user_details_array = $this->_get_user();
        if (count($user_details_array) > 0 && isset($user_details_array['group_slug']) && $user_details_array['group_slug'] === 'user') {
            $this->load->library('form_validation');
            $this->form_validation->set_rules('page', 'Page', 'trim|required|is_natural');
            if ($this->form_validation->run()) {
                $this->load->model('User_model');
                $coaches_array = $this->User_model->get_active_coaches(PAGINATION, $this->input->post('page') * PAGINATION);
                foreach ($coaches_array as $key => $coach) {
                    $coaches_array[$key]['user_profile_image_url'] = $this->_get_user_image($coach);
                }
                parent::json_output(array('code' => '1', 'message' => 'Coaches Fetched successfully.', 'data' => $coaches_array));
                return;
            }
        }
        $this->_error();
    }

    function profile_image() {
        $user_details_array = $this->_get_user();
        if (isset($_FILES['user_profile_image']) && isset($_FILES['user_profile_image']['type']) && in_array($_FILES['user_profile_image']['type'], array('image/png', 'image/jpeg')) && isset($_FILES['user_profile_image']['error']) && $_FILES['user_profile_image']['error'] == '0' && isset($_FILES['user_profile_image']['size']) && $_FILES['user_profile_image']['size'] < MAX_FILE_SIZE) {
            $upload_filename = md5(pathinfo($_FILES['user_profile_image']['name'], PATHINFO_FILENAME) . microtime());
            $upload_file_extension = pathinfo($_FILES['user_profile_image']['name'], PATHINFO_EXTENSION);
            $upload_image = $upload_filename . '.' . $upload_file_extension;
            if (move_uploaded_file($_FILES['user_profile_image']['tmp_name'], FCPATH . 'uploads/' . $upload_filename . '.' . $upload_file_extension)) {
                if (!is_dir(FCPATH . 'uploads/users' . date('/Y/m/d/H/i/s/', strtotime($user_details_array['user_created'])))) {
                    mkdir(FCPATH . 'uploads/users' . date('/Y/m/d/H/i/s/', strtotime($user_details_array['user_created'])), 0777, TRUE);
                }
                $image_size_array = getimagesize('uploads/' . $upload_filename . '.' . $upload_file_extension);
                $image_x_size = $image_size_array[0];
                $image_y_size = $image_size_array[1];
                $crop_measure = min($image_x_size, $image_y_size);
                if ($image_x_size > $image_y_size) {
                    $crop_image_x_size = ($image_x_size - $image_y_size) / 2;
                    $crop_image_y_size = '0';
                } else {
                    $crop_image_y_size = ($image_y_size - $image_x_size) / 2;
                    $crop_image_x_size = '0';
                }
                if (parent::crop_image(FCPATH . 'uploads/' . $upload_image, FCPATH . 'uploads/users' . date('/Y/m/d/H/i/s/', strtotime($user_details_array['user_created'])) . $upload_image, $crop_measure, $crop_measure, $crop_image_x_size, $crop_image_y_size)) {
                    $thumb_image_name = $upload_filename . '_small.' . $upload_file_extension;
                    $mid_image_name = $upload_filename . '_mid.' . $upload_file_extension;
                    if (parent::resize_image(FCPATH . 'uploads/users' . date('/Y/m/d/H/i/s/', strtotime($user_details_array['user_created'])) . $upload_image, FCPATH . 'uploads/users' . date('/Y/m/d/H/i/s/', strtotime($user_details_array['user_created'])) . $mid_image_name, 200, 200) && parent::resize_image(FCPATH . 'uploads/users' . date('/Y/m/d/H/i/s/', strtotime($user_details_array['user_created'])) . $upload_image, FCPATH . 'uploads/users' . date('/Y/m/d/H/i/s/', strtotime($user_details_array['user_created'])) . $thumb_image_name, 100, 100)) {
                        $user_update_array = array(
                            'user_profile_image' => $upload_image,
                            'user_profile_thumb' => $thumb_image_name,
                            'user_modified' => date('Y-m-d H:i:s')
                        );
                        $this->load->model('User_model');
                        if ($this->User_model->edit_user_by_user_id($user_details_array['user_id'], $user_update_array)) {
                            $user_details_array['user_profile_image'] = $user_update_array['user_profile_image'];
                            $user_details_array['user_profile_thumb'] = $user_update_array['user_profile_thumb'];
                            $user_details_array['user_modified'] = $user_update_array['user_modified'];
                            $user_update_array['user_profile_image_url'] = $this->_get_user_image($user_details_array);
                            parent::json_output(array('code' => '1', 'message' => 'User Profile Update successfully.', 'data' => $user_update_array));
                            return;
                        }
                    }
                }
            }
        }
        $this->_error();
    }

    function edit_profile() {
        $user_details_array = $this->_get_user();
        if (count($user_details_array) > 0 && isset($user_details_array['group_slug']) && in_array($user_details_array['group_slug'], array('coach', 'user'))) {
            $this->load->library('form_validation');
            if ($user_details_array['group_slug'] == 'coach') {
                $this->form_validation->set_rules('user_description', 'Description', 'trim|required');
                $this->form_validation->set_rules('skills_id', 'Skills', 'trim|required');
            } else {
                $this->form_validation->set_rules('user_email', 'Email', 'trim|valid_email|edit_unique[users.user_email.user_id.' . $user_details_array['user_id'] . ']');
                $this->form_validation->set_rules('user_primary_contact', 'Phone', 'trim|required');
            }
            $this->form_validation->set_rules('user_login', 'Username', 'trim|required|min_length[5]|edit_unique[users.user_login.user_id.' . $user_details_array['user_id'] . ']');
            $this->form_validation->set_rules('user_first_name', 'First Name', 'trim|required');
            $this->form_validation->set_rules('user_last_name', 'Last Name', 'trim|required');
            $this->form_validation->set_rules('user_gender', 'Gender', 'trim|required');
            if ($this->form_validation->run()) {
                $user_update_array = array();
                $user_update_array['user_login'] = $this->input->post('user_login');
                $user_update_array['user_first_name'] = $this->input->post('user_first_name');
                $user_update_array['user_last_name'] = $this->input->post('user_last_name');
                $user_update_array['user_gender'] = (strtolower($this->input->post('user_gender')) === 'male') ? '1' : '0';
                if ($user_details_array['group_slug'] == 'coach') {
                    $user_update_array['user_description'] = $this->input->post('user_description');
                } else {
                    $user_update_array['user_email'] = $this->input->post('user_email');
                    $user_update_array['user_primary_contact'] = $this->input->post('user_primary_contact');
                }
                $user_update_array['user_modified'] = date('Y-m-d H:i:s');
                $this->load->model('User_model');
                if ($this->User_model->edit_user_by_user_id($user_details_array['user_id'], $user_update_array)) {
                    $success_flag = TRUE;
                    if ($user_details_array['group_slug'] == 'coach') {
                        $skills_array = explode(',', $this->input->post('skills_id'));
                        foreach ($skills_array as $skills_id) {
                            $user_skills_array[] = array(
                                'users_id' => $user_details_array['user_id'],
                                'skills_id' => $skills_id,
                            );
                        }
                        if (count($user_skills_array) > 0 && !$this->User_model->edit_user_skills_by_user_id($user_details_array['user_id'], $user_skills_array)) {
                            $success_flag = FALSE;
                        }
                    }
                    if ($success_flag === TRUE) {
                        $user_details_array = $this->_get_user();
                        $user_details_array['user_skills'] = $this->input->post('skills_id');
                        parent::json_output(array('code' => '1', 'message' => 'Account Edited Successfully.', 'data' => $user_details_array));
                        return;
                    }
                }
            }
        }
        $this->_error();
    }

    function survey() {
        $user_details_array = $this->_get_user();
        if (count($user_details_array) > 0 && isset($user_details_array['group_slug'])) {
            $this->load->library('form_validation');
            $this->form_validation->set_rules('survey_remarks', 'Survey Remarks', 'trim|required');
            if ($this->form_validation->run()) {
                $user_update_array = array(
                    'terms_accepted' => '1',
                    'survey_remarks' => $this->input->post('survey_remarks'),
                    'user_modified' => date('Y-m-d H:i:s')
                );
                $this->load->model('User_model');
                if ($this->User_model->edit_user_by_user_id($user_details_array['user_id'], $user_update_array)) {
                    $user_details_array['terms_accepted'] = $user_update_array['terms_accepted'];
                    $user_details_array['survey_remarks'] = $user_update_array['survey_remarks'];
                    $user_details_array['user_modified'] = $user_update_array['user_modified'];
                    parent::json_output(array('code' => '1', 'message' => 'Survey saved Successfully.', 'data' => $user_details_array));
                    return;
                }
            }
        }
        $this->_error();
    }

    function signup() {
        $this->load->library('form_validation');
        $this->load->library('encrypt');
        $this->load->model('User_model');
        $this->form_validation->set_rules('user_login', 'Login', 'trim|required|min_length[5]');
        $this->form_validation->set_rules('user_email', 'Email', 'trim|required|valid_email|is_unique[users.user_email]');
        $this->form_validation->set_rules('user_primary_contact', 'phone', 'trim|required');
        $this->form_validation->set_rules('user_gender', 'Gender', 'trim|required');
        $this->form_validation->set_rules('user_description', 'Description', 'trim');
        if ($this->form_validation->run()) {
            $time_now = date('Y-m-d H:i:s');
            $name_array = explode(" ", $this->input->post('user_login'));
            $fname = array_shift($name_array);
            $lname = implode(" ", $name_array);
            $password = parent::generate_random_string('alnum');
            $user_details_array = array(
                'groups_id' => '2',
                'user_login' => str_replace(' ', '', $this->input->post('user_login')) . parent::generate_random_string('alnum'),
                'user_login_salt' => md5($time_now),
                'user_login_password' => md5(md5(md5($time_now) . $password)),
                'user_password_hash' => $this->encrypt->encode($password, md5(md5(md5($time_now) . $password))),
                'user_security_hash' => md5($time_now . $password),
                'user_first_name' => $fname,
                'user_last_name' => $lname,
                'user_email' => $this->input->post('user_email'),
                'user_primary_contact' => $this->input->post('user_primary_contact'),
                'user_gender' => $this->input->post('user_gender'),
                'user_description' => $this->input->post('user_description'),
                'user_status' => '0',
                'user_created' => $time_now,
            );
            $user_id = $this->User_model->add($user_details_array);
            if ($user_id > 0) {
                $email_details_array = array(
                    'user_first_name' => $fname,
                    'user_last_name' => $lname,
                    'user_email' => $this->input->post('user_email'),
                    'user_login_password' => $password
                );
                $email_id1 = parent::add_email_to_queue('', '', $this->input->post('user_email'), '', 'Your Account Password', $this->render_view($email_details_array, 'email', 'email/templates/add_coach', TRUE));
                if ($email_id1 > 0) {
                    $file_contents = file_get_contents(base_url() . 'email/cron/' . $email_id1);
                    if ($file_contents === '1') {
                        $email_id2 = parent::add_email_to_queue('', '', $this->config->item('admin_email'), '', 'New Coach Signup', $this->render_view($user_details_array, 'email', 'email/templates/admin_add_coach', TRUE));
                        if ($email_id2 > 0) {
                            $file_contents = file_get_contents(base_url() . 'email/cron/' . $email_id2);
                            if ($file_contents === '1') {
                                parent::json_output(array('code' => '1', 'message' => 'User Signup Successful.', 'data' => $user_details_array));
                                return;
                            }
                        }
                    }
                }
            }
        }
        $this->_error();
    }

    function rate() {
        $user_details_array = $this->_get_user();
        if (count($user_details_array) > 0 && isset($user_details_array['group_slug']) && $user_details_array['group_slug'] === 'user') {
            $this->load->library('form_validation');
            $this->form_validation->set_rules('bookings_id', 'Booking ID', 'trim|required|is_natural_no_zero');
            $this->form_validation->set_rules('rating_value', 'Rating Value', 'trim|required|is_natural_no_zero');
            if ($this->form_validation->run()) {
//                Check if the user made that booking
                $this->load->model('Booking_model');
                $booking_details_array = $this->Booking_model->get_booking_by_id($this->input->post('bookings_id'));
                if (count($booking_details_array) > 0 && isset($booking_details_array['users_id']) && $booking_details_array['users_id'] === $user_details_array['user_id']) {
//                Check if any other rating exists for same booking
                    $this->load->model('Rating_model');
                    $rating_count = $this->Rating_model->get_rating_count_by_bookings_id_and_users_id($this->input->post('bookings_id'), $user_details_array['user_id']);
                    if ($rating_count == 0) {
//                        Get the availability details to fetch the user.
                        $this->load->model('Availability_model');
                        $availability_details_array = $this->Availability_model->get_availability_by_id($booking_details_array['availabilities_id']);
                        if (count($availability_details_array) > 0) {
                            $rating_insert_array = array(
                                'for_users_id' => $availability_details_array['users_id'],
                                'bookings_id' => $this->input->post('bookings_id'),
                                'by_users_id' => $user_details_array['user_id'],
                                'rating_value' => $this->input->post('rating_value'),
                                'rating_created' => date('Y-m-d H:i:s')
                            );
                            if ($this->Rating_model->add($rating_insert_array) > 0) {
                                $rating_count = $this->Rating_model->get_rating_count_by_user_id($availability_details_array['users_id']);
                                $rating_average = $this->Rating_model->get_rating_average_by_user_id($availability_details_array['users_id']);
                                $user_update_array = array(
                                    'user_rating_count' => $rating_count,
                                    'user_rating_average' => $rating_average['rating_value'],
                                    'user_modified' => date('Y-m-d H:i:s')
                                );
                                $user_details_array['user_rating_count'] = $rating_count;
                                $user_details_array['user_rating_average'] = $rating_average['rating_value'];
                                $this->load->model('User_model');
                                if ($this->User_model->edit_user_by_user_id($availability_details_array['users_id'], $user_update_array)) {
                                    parent::json_output(array('code' => '1', 'message' => 'Rating Saved Successfully.', 'data' => $user_details_array));
                                    return;
                                }
                            }
                        }
                    }
                }
            }
        }
        $this->_error();
    }

    function question() {
        $user_details_array = $this->_get_user();
        if (count($user_details_array) > 0 && isset($user_details_array['group_slug']) && $user_details_array['group_slug'] === 'user') {
            $this->load->library('form_validation');
            $this->form_validation->set_rules('question_topic', 'Question Topic', 'required');
            $this->form_validation->set_rules('question_value', 'Question Value', 'required');
            if ($this->form_validation->run()) {
                $question_insert_array = array(
                    'question_topic' => $this->input->post('question_topic'),
                    'question_value' => $this->input->post('question_value'),
                    'users_id' => $user_details_array['user_id'],
                    'question_created' => date('Y-m-d H:i:s')
                );
                $this->load->model('Question_model');
                if ($this->Question_model->add($question_insert_array) > 0) {
                    $this->load->model('User_model');
                    $coaches_array = $this->User_model->get_active_coaches();
                    foreach ($coaches_array as $coach) {
                        $email_details_array = array(
                            'question_topic' => $this->input->post('question_topic'),
                            'question_value' => $this->input->post('question_value'),
                            'user_first_name' => $user_details_array['user_first_name'],
                            'user_last_name' => $user_details_array['user_last_name'],
                            'coach_first_name' => $coach['user_first_name'],
                            'coach_last_name' => $coach['user_last_name'],
                            'question_created' => $question_insert_array['question_created'],
                        );
                        parent::add_email_to_queue('', '', $coach['user_email'], $coach['user_id'], 'Client Question in Goal Consultation - Please reply.', parent::render_view($email_details_array, 'email', 'email/templates/question', TRUE));
                    }
                    parent::json_output(array('code' => '1', 'message' => 'Question Added Successfully.', 'data' => $question_insert_array));
                    return;
                }
            }
        }
        $this->_error();
    }

    function answer() {
        $user_details_array = $this->_get_user();
        if (count($user_details_array) > 0 && isset($user_details_array['group_slug']) && $user_details_array['group_slug'] === 'coach') {
            $this->load->library('form_validation');
            $this->form_validation->set_rules('questions_id', 'Question', 'required');
            $this->form_validation->set_rules('answer_value', 'Answer Value', 'required');
            if ($this->form_validation->run()) {
                $answer_insert_array = array(
                    'questions_id' => $this->input->post('questions_id'),
                    'users_id' => $user_details_array['user_id'],
                    'answer_value' => $this->input->post('answer_value'),
                    'answer_created' => date('Y-m-d H:i:s')
                );
                $this->load->model('Answer_model');
                if ($this->Answer_model->add($answer_insert_array) > 0) {
                    $answer_count = $this->Answer_model->count_answer_by_question_id($this->input->post('questions_id'));
                    if ($answer_count > 0) {
                        $update_question_detail_array = array(
                            'question_answer_count' => $answer_count,
                            'question_answer_created' => $answer_insert_array['answer_created']
                        );
                        $this->load->model('Question_model');
                        if ($this->Question_model->edit_question_by_question_id($this->input->post('questions_id'), $update_question_detail_array)) {
                            parent::json_output(array('code' => '1', 'message' => 'Answer Added Successfully.', 'data' => $answer_insert_array));
                            return;
                        }
                    }
                }
            }
        }
        $this->_error();
    }

    function questions_list() {
        $this->load->library('form_validation');
        $this->form_validation->set_rules('page', 'Page', 'trim|required|is_natural');
        if ($this->form_validation->run()) {
            $this->load->model('Question_model');
            $questions_list_array = $this->Question_model->get_questions_list(PAGINATION, $this->input->post('page') * PAGINATION);
            foreach ($questions_list_array as $key => $question) {
                $questions_list_array[$key]['question_created_timestamp'] = date('d M Y', strtotime($question['question_created']));
                $questions_list_array[$key]['user_profile_image_url'] = $this->_get_user_image($question);
            }
            parent::json_output(array('code' => '1', 'message' => 'Success Fetching Questions.', 'data' => $questions_list_array));
            return;
        }
        $this->_error();
    }

    function answers_list() {
        $this->load->library('form_validation');
        $this->form_validation->set_rules('questions_id', 'Social Media Platform', 'trim|required|is_natural_no_zero');
        $this->form_validation->set_rules('page', 'Page', 'trim|required|is_natural');
        if ($this->form_validation->run()) {
            $this->load->model('Answer_model');
            $answers_list_array = $this->Answer_model->get_answers_list($this->input->post('questions_id'), PAGINATION, $this->input->post('page') * PAGINATION);
            foreach ($answers_list_array as $key => $answer) {
                $answers_list_array[$key]['answer_created_timestamp'] = date('d M Y', strtotime($answer['answer_created']));
                $answers_list_array[$key]['user_profile_image_url'] = $this->_get_user_image($answer);
            }
            parent::json_output(array('code' => '1', 'message' => 'Success Fetching Answers.', 'data' => $answers_list_array));
            return;
        }
        $this->_error();
    }

    function social_media_login() {
        $this->load->library('form_validation');
        $this->form_validation->set_rules('social_media_platform', 'Social Media Platform', 'trim|required|min_length[5]');
        $this->form_validation->set_rules('social_media_id', 'Social Media Platform', 'trim|required|min_length[5]');
        $this->form_validation->set_rules('user_email', 'Email', 'trim|valid_email');
        if ($this->form_validation->run()) {
            $this->load->model('Auth_model');
            $this->load->model('User_model');
            $time_now = date('Y-m-d H:i:s');
            $user_details_array = $this->Auth_model->get_user_by_social_media_id($this->input->post('social_media_platform'), $this->input->post('social_media_id'));
            if (count($user_details_array) > 0) {
                goto PROCESS_LOGIN;
            } else {
                $user_details_array = $this->Auth_model->get_user_by_username_or_email($this->input->post('user_email'));
                if (count($user_details_array) > 0) {
                    $user_update_array = array(
                        'user_' . $this->input->post('social_media_platform') . '_id' => $this->input->post('social_media_id'),
                        'user_modified' => $time_now
                    );
                    if ($this->User_model->edit_user_by_user_id($user_details_array['user_id'], $user_update_array)) {
                        goto PROCESS_LOGIN;
                    }
                } else {
                    $password = parent::generate_random_string('alnum');
                    $user_details_array = array(
                        'groups_id' => '3',
                        'user_email' => $this->input->post('user_email'),
                        'user_first_name' => $this->input->post('user_first_name'),
                        'user_last_name' => $this->input->post('user_last_name'),
                        'user_login' => $this->input->post('social_media_id'),
                        'user_login_salt' => md5($time_now),
                        'user_login_password' => md5(md5(md5($time_now) . $password)),
                        'user_password_hash' => $this->encrypt->encode($password, md5(md5(md5($time_now) . $password))),
                        'user_security_hash' => md5($time_now . $password),
                        'user_gender' => (strtolower($this->input->post('user_gender')) === 'male') ? '1' : '0',
                        'user_' . $this->input->post('social_media_platform') . '_id' => $this->input->post('social_media_id'),
                        'user_status' => '1',
                        'user_created' => $time_now
                    );
                    $user_id = $this->User_model->add($user_details_array);
                    if ($user_id > 0) {
                        if ($this->input->post('user_email') !== '') {
                            $email_details_array = array(
                                'user_first_name' => $this->input->post('user_first_name'),
                                'user_last_name' => $this->input->post('user_last_name'),
                                'social_media_platform' => $this->input->post('social_media_platform'),
                                'user_email' => $this->input->post('user_email')
                            );
                            $email_id1 = parent::add_email_to_queue('', '', $this->input->post('user_email'), '', 'Thank you for signup.', $this->render_view($email_details_array, 'email', 'email/templates/add_user', TRUE));
                            if ($email_id1 > 0) {
                                $file_contents = file_get_contents(base_url() . 'email/cron/' . $email_id1);
                                if ($file_contents === '1') {
                                    $email_id2 = parent::add_email_to_queue('', '', $this->config->item('admin_email'), '', 'New User Signup', $this->render_view($user_details_array, 'email', 'email/templates/admin_add_user', TRUE));
                                    if ($email_id2 > 0) {
                                        $file_contents = file_get_contents(base_url() . 'email/cron/' . $email_id2);
                                        if ($file_contents === '1') {
                                            $user_details_array = $this->User_model->get_user_by_id($user_id);
                                            goto PROCESS_LOGIN;
                                        }
                                    }
                                }
                            }
                        } else {
                            $user_details_array = $this->User_model->get_user_by_id($user_id);
                            goto PROCESS_LOGIN;
                        }
                    }
                }
            }
            PROCESS_LOGIN :
            if (isset($user_details_array['user_id'])) {
                if ($user_details_array['user_status'] === '1') {
                    $this->Auth_model->update_user_login($user_details_array['user_id']);
                    $this->Auth_model->add_login_log(array(
                        'users_id' => $user_details_array['user_id'],
                        'login_log_from' => '2',
                        'login_log_mode' => $this->input->post('social_media_platform'),
                        'login_log_ip_address' => $this->input->server('REMOTE_ADDR'),
                        'login_log_user_agent' => $this->input->server('HTTP_USER_AGENT'),
                        'login_log_created' => $time_now
                    ));
                    $user_tokens_array = $this->_update_user_tokens($user_details_array['user_id']);
                    $user_details_array['user_paid_tokens'] = $user_tokens_array['user_paid_tokens'];
                    $user_details_array['user_free_tokens'] = $user_tokens_array['user_free_tokens'];
                    $user_details_array['user_profile_image_url'] = $this->_get_user_image($user_details_array);
                    $user_details_array['user_token_count'] = $user_details_array['user_paid_tokens'] + $user_details_array['user_free_tokens'];
                    $user_details_array['configurations'] = $this->_get_all_configurations();
                    $this->load->model('User_token_model');
                    $free_token_redeemed_array = $this->User_token_model->get_free_tokens_row_for_current_month($user_details_array['user_id']);
                    $user_details_array['user_free_tokens_available'] = '1';
                    if (count($free_token_redeemed_array) > 0) {
                        $user_details_array['user_free_tokens_available'] = '0';
                    }
                    parent::json_output(array('code' => '1', 'message' => 'Logged In Successfully.', 'data' => $user_details_array));
                    return;
                } else {
                    parent::json_output(array('code' => '-1', 'message' => 'Account is Disabled !!!'));
                    return;
                }
            }
        }
        $this->_error();
    }

    function skills() {
        $this->load->model('Skill_model');
        parent::json_output(array('code' => '1', 'message' => 'Skills fetched Successfully', 'data' => $this->Skill_model->get_skills()));
        return;
    }

    function topics() {
        $this->load->model('Topic_model');
        parent::json_output(array('code' => '1', 'message' => 'Topics fetched Successfully', 'data' => $this->Topic_model->get_topics()));
        return;
    }

    function get_coach_availabilities() {
        $user_details_array = $this->_get_user();
        if (count($user_details_array) > 0 && isset($user_details_array['group_slug'])) {
            $this->load->library('form_validation');
            $this->form_validation->set_rules('users_id', 'Coach', 'trim|required|is_natural_no_zero');
            $this->form_validation->set_rules('availability_for', 'Availability For', 'trim|required|is_natural_no_zero');
            if ($this->form_validation->run()) {
                $this->load->model('User_model');
                $coach_user_details_array = $this->User_model->get_user_by_id($this->input->post('users_id'));
                if (count($coach_user_details_array) > 0 && isset($coach_user_details_array['group_slug']) && $coach_user_details_array['group_slug'] === 'coach') {
                    $this->load->model('Availability_model');
                    $this->load->model('Booking_model');
//                    Get Coach Availabilities after 10 minutes from now.
                    $availabilities_array = $this->Availability_model->get_coach_availabilities($this->input->post('users_id'), $this->input->post('availability_for'), date('Y-m-d H:i:s', strtotime('+10 minutes')));
                    if (count($availabilities_array) > 0) {
                        $availabilities_return_array = array();
                        foreach ($availabilities_array as $availability) {
                            $booking_details_array = $this->Booking_model->get_bookings_by_availability_id($availability['availability_id']);
                            if ($availability['availability_for'] === '3') {
                                if (count($booking_details_array) < 9) {
                                    $availabilities_return_array[] = $availability;
                                }
                            } else {
                                if (count($booking_details_array) == 0) {
                                    $availabilities_return_array[] = $availability;
                                }
                            }
                        }
                        parent::json_output(array('code' => '1', 'message' => 'Availabilities fetched Successfully', 'data' => $availabilities_return_array));
                    } else {
                        parent::json_output(array('code' => '0', 'message' => 'Coach Not Available !!!'));
                    }
                    return;
                }
            }
        }
        $this->_error();
    }

    function book() {
        $user_details_array = $this->_get_user();
        if (count($user_details_array) > 0 && isset($user_details_array['group_slug']) && $user_details_array['group_slug'] === 'user') {
//            Sometimes we grant tokens directly from DB. Hence this step is added.
            $user_tokens_array = $this->_update_user_tokens($user_details_array['user_id']);
            $user_details_array['user_paid_tokens'] = $user_tokens_array['user_paid_tokens'];
            $user_details_array['user_free_tokens'] = $user_tokens_array['user_free_tokens'];
            $this->load->library('form_validation');
            $this->form_validation->set_rules('availabilities_id', 'Availability', 'trim|required|is_natural_no_zero');
            $this->form_validation->set_rules('booking_length', 'Booking Length', 'trim|required|is_natural_no_zero');
            $this->form_validation->set_rules('booking_start_time', 'Booking Start Time', 'trim|required');
            if ($this->form_validation->run()) {
                $this->load->model('Availability_model');
                $availability_details_array = $this->Availability_model->get_availability_by_id($this->input->post('availabilities_id'));
                if (count($availability_details_array) > 0) {
                    if (strtotime($availability_details_array['availability_from']) >= (time() + (5 * 60))) { // Booking can be done 5 mins before.
                        if (
                                (strtotime($this->input->post('booking_start_time')) >= strtotime($availability_details_array['availability_from'])) &&
                                (strtotime($availability_details_array['availability_to']) >= strtotime($this->input->post('booking_start_time')) + ($this->input->post('booking_length') * 60))
                        ) {
                            $this->load->model('Booking_model');
                            $this->load->model('Configuration_model');
                            $this->load->model('User_token_model');
                            $booking_already_done_array = $this->Booking_model->get_bookings_by_availability_id($availability_details_array['availability_id']);
                            if (isset($availability_details_array['availability_for']) && $availability_details_array['availability_for'] === '3') {
//                                In Group Coaching the Start time of all participants must be same.
                                if (date('Y-m-d H:i:s', strtotime($this->input->post('booking_start_time'))) !== $availability_details_array['availability_from']) {
                                    parent::json_output(array('code' => '0', 'message' => 'Please select same start time as that of availability time !!!'));
                                    return;
                                }
//                        Upto 9 Bookings on same Availability. 10th User shall be Coach.
                                if (count($booking_already_done_array) < 9) {
                                    $process_booking = TRUE;
                                    goto PROCESS_BOOKING;
                                } else {
                                    parent::json_output(array('code' => '0', 'message' => 'Group Chat Room Is Full !!!'));
                                    return;
                                }
                            } else {
//                                Only Single booking allowed for 1 to 1 communications
                                if (count($booking_already_done_array) > 0) {
                                    parent::json_output(array('code' => '0', 'message' => 'Coach Already Booked !!!'));
                                    return;
                                } else {
                                    $process_booking = TRUE;
                                    goto PROCESS_BOOKING;
                                }
                            }
                        } else {
                            parent::json_output(array('code' => '0', 'message' => 'Please Book coach within the availability time !!!'));
                            return;
                        }
                    } else {
                        parent::json_output(array('code' => '0', 'message' => 'Bookings can only be done 5 Minutes before Coach Availability !!!'));
                        return;
                    }
                } else {
                    parent::json_output(array('code' => '0', 'message' => 'Coach Not Available !!!'));
                    return;
                }
                PROCESS_BOOKING:
                if (isset($process_booking) && $process_booking === TRUE) {
//                    Check if user has not already booked the same Availability .
                    $already_booked_array = $this->Booking_model->get_booking_by_availability_id_and_user_id($availability_details_array['availability_id'], $user_details_array['user_id']);
                    if (count($already_booked_array) > 0) {
                        parent::json_output(array('code' => '0', 'message' => 'Coach Already Booked by You !!!'));
                        return;
                    } else {
//                        Calculate the number of required tokens.
                        switch ($availability_details_array['availability_for']) {
                            case '1':
                                $configuration_key = 'one_to_one_chat';
                                break;
                            case '2':
                                $configuration_key = 'one_to_one_coaching';
                                break;
                            case '3':
                                $configuration_key = 'group_coaching';
                                break;
                            default :
                                $configuration_key = 'one_to_one_chat';
                                break;
                        }
                        $configurations_array = $this->Configuration_model->get_configuration_by_key($configuration_key);
                        $required_tokens_count = $this->input->post('booking_length') * $configurations_array['configuration_value'];
//                        Check if user have required tokens.
                        if ($required_tokens_count > ($user_details_array['user_paid_tokens'] + $user_details_array['user_free_tokens'])) {
                            parent::json_output(array('code' => '0', 'message' => 'Not Enough Tokens !!!'));
                            return;
                        } else {
//                            Calculate the tokens Free / Paid to be utilized.
                            $free_tokens_to_be_used = $paid_tokens_to_be_used = 0;
                            if ($user_details_array['user_free_tokens'] >= $required_tokens_count) {
                                $free_tokens_to_be_used = $required_tokens_count;
                            } else {
                                $free_tokens_to_be_used = $user_details_array['user_free_tokens'];
                                $paid_tokens_to_be_used = $required_tokens_count - $free_tokens_to_be_used;
                            }
//                            Proceed with Booking.
                            $booking_insert_array = array(
                                'availabilities_id' => $availability_details_array['availability_id'],
                                'users_id' => $user_details_array['user_id'],
                                'booking_free_tokens_used' => $free_tokens_to_be_used,
                                'booking_paid_tokens_used' => $paid_tokens_to_be_used,
                                'booking_length' => $this->input->post('booking_length'),
                                'booking_start_time' => $this->input->post('booking_start_time'),
                                'booking_end_time' => date('Y-m-d H:i:s', strtotime($this->input->post('booking_start_time')) + ($this->input->post('booking_length') * 60)),
                                'booking_status' => '1',
                                'booking_created' => date('Y-m-d H:i:s')
                            );
                            if ($this->Booking_model->add($booking_insert_array) > 0) {
//                                Logic to adjust the Availability if more than 20 minutes are free for coach (in case of 1-to-1 communications)
//                                We shall split the availability (update old availability times) and insert new availability for free timings
                                if ($availability_details_array['availability_for'] !== '3') {
//                                    If time before booking slot is more than 20 minutes
                                    if (strtotime($booking_insert_array['booking_start_time']) - strtotime($availability_details_array['availability_from']) >= 1200) {
                                        $this->Availability_model->update($availability_details_array['availability_id'], array(
                                            'availability_from' => $booking_insert_array['booking_start_time'],
                                            'availability_to' => $booking_insert_array['booking_end_time'],
                                            'availability_length' => $booking_insert_array['booking_length'],
                                            'availability_modified' => $booking_insert_array['booking_created']
                                        ));
                                        $availabilities_insert_array = array(
                                            'conference_id' => parent::generate_random_string('alnum', 32),
                                            'users_id' => $availability_details_array['users_id'],
                                            'availability_for' => $availability_details_array['availability_for'],
                                            'topics_id' => $availability_details_array['topics_id'],
                                            'availability_from' => $availability_details_array['availability_from'],
                                            'availability_to' => $booking_insert_array['booking_start_time'],
                                            'availability_length' => (strtotime($booking_insert_array['booking_start_time']) - strtotime($availability_details_array['availability_from'])) / 60,
                                            'availability_created' => date('Y-m-d H:i:s')
                                        );
                                        $this->Availability_model->add($availabilities_insert_array);
                                    }
//                                    If time after booking slot is more than 20 minutes
                                    if (strtotime($availability_details_array['availability_to']) - strtotime($booking_insert_array['booking_end_time']) >= 1200) {
                                        $this->Availability_model->update($availability_details_array['availability_id'], array(
                                            'availability_from' => $booking_insert_array['booking_start_time'],
                                            'availability_to' => $booking_insert_array['booking_end_time'],
                                            'availability_length' => $booking_insert_array['booking_length'],
                                            'availability_modified' => $booking_insert_array['booking_created']
                                        ));
                                        $availabilities_insert_array = array(
                                            'conference_id' => parent::generate_random_string('alnum', 32),
                                            'users_id' => $availability_details_array['users_id'],
                                            'availability_for' => $availability_details_array['availability_for'],
                                            'topics_id' => $availability_details_array['topics_id'],
                                            'availability_from' => $booking_insert_array['booking_end_time'],
                                            'availability_to' => $availability_details_array['availability_to'],
                                            'availability_length' => (strtotime($availability_details_array['availability_to']) - strtotime($booking_insert_array['booking_end_time'])) / 60,
                                            'availability_created' => date('Y-m-d H:i:s')
                                        );
                                        $this->Availability_model->add($availabilities_insert_array);
                                    }
                                }
//                                Subtract the tokens from user account.
                                if ($free_tokens_to_be_used > 0) {
                                    $user_token_insert_array = array(
                                        'users_id' => $user_details_array['user_id'],
                                        'user_token_value' => '-' . $free_tokens_to_be_used,
                                        'user_token_paid' => '0',
                                        'user_token_refund' => '0',
                                        'user_token_created' => date('Y-m-d H:i:s')
                                    );
                                    $this->User_token_model->add($user_token_insert_array);
                                }
                                if ($paid_tokens_to_be_used > 0) {
                                    $user_token_insert_array = array(
                                        'users_id' => $user_details_array['user_id'],
                                        'user_token_value' => '-' . $paid_tokens_to_be_used,
                                        'user_token_paid' => '1',
                                        'user_token_refund' => '0',
                                        'user_token_created' => date('Y-m-d H:i:s')
                                    );
                                    $this->User_token_model->add($user_token_insert_array);
                                }
//                                Update User Tokens Count
                                $user_updated_tokens = $this->_update_user_tokens($user_details_array['user_id']);
                                if (count($user_updated_tokens) > 0) {
                                    $coach_details_array = $this->User_model->get_user_by_id($availability_details_array['users_id']);
//                                    Send Email to User and Coach for Booking Confirmed .
                                    $email_details_array = array(
                                        'user_first_name' => $user_details_array['user_first_name'],
                                        'user_last_name' => $user_details_array['user_last_name'],
                                        'coach_first_name' => $coach_details_array['user_first_name'],
                                        'coach_last_name' => $coach_details_array['user_last_name'],
                                        'booking_start_time' => $booking_insert_array['booking_start_time'],
                                        'booking_end_time' => $booking_insert_array['booking_end_time'],
                                        'booking_length' => $booking_insert_array['booking_length'],
                                        'booking_created' => $booking_insert_array['booking_created'],
                                        'user_token_count' => $user_updated_tokens['user_token_count']
                                    );
                                    $email_id1 = parent::add_email_to_queue('', '', $user_details_array['user_email'], $user_details_array['user_id'], 'You have booked a Coach', $this->render_view($email_details_array, 'email', 'email/templates/user_booking', TRUE));
                                    $email_id2 = parent::add_email_to_queue('', '', $coach_details_array['user_email'], $coach_details_array['user_id'], 'New Booking Received', $this->render_view($email_details_array, 'email', 'email/templates/coach_booking', TRUE));
                                    if ($email_id1 > 0 && $email_id2 > 0) {
                                        $file_contents1 = file_get_contents(base_url() . 'email/cron/' . $email_id1);
                                        $file_contents2 = file_get_contents(base_url() . 'email/cron/' . $email_id2);
                                        if ($file_contents1 == '1' && $file_contents2 == '1') {
                                            parent::json_output(array('code' => '1', 'message' => 'Booking Successful.', 'data' => $user_updated_tokens));
                                            return;
                                        }
                                    }
                                }
                            }
                        }
                    }
                }
            }
        }
        $this->_error();
    }

    function search_questions() {
        $user_details_array = $this->_get_user();
        if (count($user_details_array) > 0 && isset($user_details_array['group_slug'])) {
            $this->load->library('form_validation');
            $this->form_validation->set_rules('search', 'Availability', 'trim|required');
            $this->form_validation->set_rules('page', 'Page', 'trim|required|is_natural');
            if ($this->form_validation->run()) {
                $this->load->model('Question_model');
                $questions_list_array = $this->Question_model->search_questions($this->input->post('search'), PAGINATION, $this->input->post('page') * PAGINATION);
                foreach ($questions_list_array as $key => $question) {
                    $questions_list_array[$key]['question_created_timestamp'] = date('d M Y', strtotime($question['question_created']));
                    $questions_list_array[$key]['user_profile_image_url'] = $this->_get_user_image($question);
                }
                parent::json_output(array('code' => '1', 'message' => 'Success Fetching Questions.', 'data' => $questions_list_array));
                return;
            }
        }
        $this->_error();
    }

    function search_coaches() {
        $user_details_array = $this->_get_user();
        if (count($user_details_array) > 0 && isset($user_details_array['group_slug']) && $user_details_array['group_slug'] === 'user') {
            $this->load->library('form_validation');
            $this->form_validation->set_rules('search', 'Search', 'trim|required');
            $this->form_validation->set_rules('page', 'Page', 'trim|required|is_natural');
            if ($this->form_validation->run()) {
                $this->load->model('User_model');
                $coaches_array = $this->User_model->search_coaches($this->input->post('search'), PAGINATION, $this->input->post('page') * PAGINATION);
                foreach ($coaches_array as $key => $coach) {
                    $coaches_array[$key]['user_profile_image_url'] = $this->_get_user_image($coach);
                }
                parent::json_output(array('code' => '1', 'message' => 'Coaches Fetched successfully.', 'data' => $coaches_array));
                return;
            }
        }
        $this->_error();
    }

    function tracking() {
        $user_details_array = $this->_get_user();
        if (count($user_details_array) > 0 && isset($user_details_array['group_slug']) && $user_details_array['group_slug'] === 'user') {
            $this->load->library('form_validation');
            $this->form_validation->set_rules('weight', 'Weight', 'trim|required');
            $this->form_validation->set_rules('bmi', 'BMI', 'trim');
            $this->form_validation->set_rules('body_fat', 'Body Fat', 'trim');
            $this->form_validation->set_rules('calories_consumed', 'Calories Consumed', 'trim');
            $this->form_validation->set_rules('calories_burned', 'Calories Burned', 'trim');
            $this->form_validation->set_rules('fat_consumed', 'Fat Consumed', 'trim');
            $this->form_validation->set_rules('protein_consumed', 'Proteins Consumed', 'trim');
            $this->form_validation->set_rules('body_area', 'Body Area', 'trim');
            $this->form_validation->set_rules('measurement_difference', 'Measurement Difference', 'trim');
            $this->form_validation->set_rules('sports_performance', 'Sports Performance', 'trim');
            $this->form_validation->set_rules('distance_performance', 'Distance Performance', 'trim');
            $this->form_validation->set_rules('time_performance', 'Time Performance', 'trim');
            $this->form_validation->set_rules('position_performance', 'Position Performance', 'trim');
            $this->form_validation->set_rules('win_lose_performance', 'Win/Lose Performance', 'trim');
            $this->form_validation->set_rules('training_sessions_performance', 'Training Sessions Performance', 'trim');
            $this->form_validation->set_rules('exercise_performance', 'Exercise Performance', 'trim');
            $this->form_validation->set_rules('load_performance', 'Load Performance', 'trim');
            $this->form_validation->set_rules('average_repetitions_performance', 'Average Repetitions Performance', 'trim');
            $this->form_validation->set_rules('average_sets_performance', 'Average Sets Performance', 'trim');
            $this->form_validation->set_rules('average_pace_performance', 'Average Pace Performance', 'trim');
            $this->form_validation->set_rules('average_heart_rate_performance', 'Average Heart Rate Performance', 'trim');
            $this->form_validation->set_rules('average_watts_performance', 'Average Watts Performance', 'trim');
            $this->form_validation->set_rules('average_cadence', 'Average Cadence', 'trim');
            $this->form_validation->set_rules('recovery_sessions', 'Recovery Sessions', 'trim');
            $this->form_validation->set_rules('flexibility_sessions', 'Flexibility Sessions', 'trim');
            if ($this->form_validation->run()) {
                $this->load->model('Tracking_model');
                $time_now = date('Y-m-d H:i:s');
                $tracking_insert_array = array(
                    'users_id' => $user_details_array['user_id'],
                    'weight' => $this->input->post('weight'),
                    'bmi' => $this->input->post('bmi'),
                    'body_fat' => $this->input->post('body_fat'),
                    'calories_consumed' => $this->input->post('calories_consumed'),
                    'calories_burned' => $this->input->post('calories_burned'),
                    'fat_consumed' => $this->input->post('fat_consumed'),
                    'protein_consumed' => $this->input->post('protein_consumed'),
                    'body_area' => $this->input->post('body_area'),
                    'measurement_difference' => $this->input->post('measurement_difference'),
                    'sports_performance' => $this->input->post('sports_performance'),
                    'distance_performance' => $this->input->post('distance_performance'),
                    'time_performance' => $this->input->post('time_performance'),
                    'position_performance' => $this->input->post('position_performance'),
                    'win_lose_performance' => $this->input->post('win_lose_performance'),
                    'training_sessions_performance' => $this->input->post('training_sessions_performance'),
                    'exercise_performance' => $this->input->post('exercise_performance'),
                    'load_performance' => $this->input->post('load_performance'),
                    'average_repetitions_performance' => $this->input->post('average_repetitions_performance'),
                    'average_sets_performance' => $this->input->post('average_sets_performance'),
                    'average_pace_performance' => $this->input->post('average_pace_performance'),
                    'average_heart_rate_performance' => $this->input->post('average_heart_rate_performance'),
                    'average_watts_performance' => $this->input->post('average_watts_performance'),
                    'average_cadence' => $this->input->post('average_cadence'),
                    'recovery_sessions' => $this->input->post('recovery_sessions'),
                    'flexibility_sessions' => $this->input->post('flexibility_sessions'),
                    'tracking_created' => $time_now
                );
                if (isset($_FILES['tracking_image']) && isset($_FILES['tracking_image']['type']) && in_array($_FILES['tracking_image']['type'], array('image/png', 'image/jpeg')) && isset($_FILES['tracking_image']['error']) && $_FILES['tracking_image']['error'] == '0' && isset($_FILES['tracking_image']['size']) && $_FILES['tracking_image']['size'] < MAX_FILE_SIZE) {
                    $upload_filename = md5(pathinfo($_FILES['tracking_image']['name'], PATHINFO_FILENAME) . microtime());
                    $upload_file_extension = pathinfo($_FILES['tracking_image']['name'], PATHINFO_EXTENSION);
                    $upload_image = $upload_filename . '.' . $upload_file_extension;
                    if (move_uploaded_file($_FILES['tracking_image']['tmp_name'], FCPATH . 'uploads/' . $upload_filename . '.' . $upload_file_extension)) {
                        if (!is_dir(FCPATH . 'uploads/trackings' . date('/Y/m/d/H/i/s/', strtotime($time_now)))) {
                            mkdir(FCPATH . 'uploads/trackings' . date('/Y/m/d/H/i/s/', strtotime($time_now)), 0777, TRUE);
                        }
                        $image_size_array = getimagesize('uploads/' . $upload_filename . '.' . $upload_file_extension);
                        $image_x_size = $image_size_array[0];
                        $image_y_size = $image_size_array[1];
                        $crop_measure = min($image_x_size, $image_y_size);
                        if ($image_x_size > $image_y_size) {
                            $crop_image_x_size = ($image_x_size - $image_y_size) / 2;
                            $crop_image_y_size = '0';
                        } else {
                            $crop_image_y_size = ($image_y_size - $image_x_size) / 2;
                            $crop_image_x_size = '0';
                        }
                        if (parent::crop_image(FCPATH . 'uploads/' . $upload_image, FCPATH . 'uploads/trackings' . date('/Y/m/d/H/i/s/', strtotime($time_now)) . $upload_image, $crop_measure, $crop_measure, $crop_image_x_size, $crop_image_y_size)) {
                            $thumb_image_name = $upload_filename . '_small.' . $upload_file_extension;
                            $mid_image_name = $upload_filename . '_mid.' . $upload_file_extension;
                            if (parent::resize_image(FCPATH . 'uploads/trackings' . date('/Y/m/d/H/i/s/', strtotime($time_now)) . $upload_image, FCPATH . 'uploads/trackings' . date('/Y/m/d/H/i/s/', strtotime($time_now)) . $mid_image_name, 200, 200) && parent::resize_image(FCPATH . 'uploads/trackings' . date('/Y/m/d/H/i/s/', strtotime($time_now)) . $upload_image, FCPATH . 'uploads/trackings' . date('/Y/m/d/H/i/s/', strtotime($time_now)) . $thumb_image_name, 100, 100)) {
                                $tracking_insert_array['tracking_image'] = $upload_image;
                                $tracking_insert_array['tracking_thumb'] = $thumb_image_name;
                                if ($this->Tracking_model->add($tracking_insert_array) > 0) {
                                    parent::json_output(array('code' => '1', 'message' => 'Tracking Added Successfully.'));
                                    return;
                                }
                            }
                        }
                    }
                }
            }
        }
        $this->_error();
    }

    function tracking_progress() {
        $user_details_array = $this->_get_user();
        if (count($user_details_array) > 0 && isset($user_details_array['group_slug']) && $user_details_array['group_slug'] === 'user') {
            $this->load->library('form_validation');
            $this->form_validation->set_rules('page', 'Page', 'trim|required');
            if ($this->form_validation->run()) {
                $this->load->model('Tracking_model');
                $trackings_array = $this->Tracking_model->get_trackings_by_user_id($user_details_array['user_id'], PAGINATION, $this->input->post('page') * PAGINATION);
                foreach ($trackings_array as $key => $tracking) {
                    $trackings_array[$key]['tracking_image_url'] = $trackings_array[$key]['tracking_thumb_url'] = base_url() . 'assets/img/profile.png';
                    if (is_file(FCPATH . 'uploads/trackings' . date('/Y/m/d/H/i/s/', strtotime($tracking['tracking_created'])) . $tracking['tracking_image']) && is_file(FCPATH . 'uploads/trackings' . date('/Y/m/d/H/i/s/', strtotime($tracking['tracking_created'])) . $tracking['tracking_thumb'])) {
                        $trackings_array[$key]['tracking_image_url'] = base_url() . 'uploads/trackings' . date('/Y/m/d/H/i/s/', strtotime($tracking['tracking_created'])) . $tracking['tracking_image'];
                        $trackings_array[$key]['tracking_thumb_url'] = base_url() . 'uploads/trackings' . date('/Y/m/d/H/i/s/', strtotime($tracking['tracking_created'])) . $tracking['tracking_thumb'];
                    }
                    $trackings_array[$key]['tracking_created_date'] = date('d M Y', strtotime($tracking['tracking_created']));
                }
                parent::json_output(array('code' => '1', 'message' => 'Tracking Progress Fetched successfully.', 'data' => $trackings_array));
                return;
            }
        }
        $this->_error();
    }

    function purchase() {
        $user_details_array = $this->_get_user();
        if (count($user_details_array) > 0 && isset($user_details_array['group_slug']) && $user_details_array['group_slug'] === 'user') {
            $this->load->library('form_validation');
            $this->form_validation->set_rules('token_type', 'Token Type', 'trim|required');
            if ($this->form_validation->run()) {
                $this->load->model('Configuration_model');
                $configuration_array = $this->Configuration_model->get_configuration_by_key($this->input->post('token_type'));
                if (count($configuration_array) > 0) {
                    $this->load->model('User_token_model');
                    $user_tokens_insert_array = array(
                        'users_id' => $user_details_array['user_id'],
                        'user_token_value' => $configuration_array['configuration_value'],
                        'user_token_paid' => '1',
                        'user_token_refund' => '0',
                        'user_token_created' => date('Y-m-d H:i:s')
                    );
                    if ($this->User_token_model->add($user_tokens_insert_array)) {
                        parent::json_output(array('code' => '1', 'message' => 'Tokens Purchased Successfully.', 'data' => $this->_update_user_tokens($user_details_array['user_id'])));
                        return;
                    }
                }
            }
        }
        $this->_error();
    }

    function bookings() {
        $user_details_array = $this->_get_user();
        if (count($user_details_array) > 0 && isset($user_details_array['group_slug'])) {
            $this->load->library('form_validation');
            $this->form_validation->set_rules('page', 'Page', 'trim|required|is_natural');
            if ($this->form_validation->run()) {
                $this->load->model('Booking_model');
                if ($user_details_array['group_slug'] === 'user') {
                    $bookings_array = $this->Booking_model->get_user_bookings($user_details_array['user_id'], PAGINATION, $this->input->post('page') * PAGINATION);
                }
                if ($user_details_array['group_slug'] === 'coach') {
                    $bookings_array = $this->Booking_model->get_coach_bookings($user_details_array['user_id'], PAGINATION, $this->input->post('page') * PAGINATION);
                }
                foreach ($bookings_array as $key => $booking) {
                    $bookings_array[$key]['booking_start_time'] = date('d M Y h:i A', strtotime($booking['booking_start_time']));
                    $bookings_array[$key]['booking_end_time'] = date('d M Y h:i A', strtotime($booking['booking_end_time']));
                    $bookings_array[$key]['user_profile_image_url'] = $this->_get_user_image($booking);
                }
                parent::json_output(array('code' => '1', 'message' => 'Success Fetching Bookings.', 'data' => $bookings_array));
                return;
            }
        }
        $this->_error();
    }

    function start() {
        $user_details_array = $this->_get_user();
        if (count($user_details_array) > 0 && isset($user_details_array['group_slug']) && (in_array($user_details_array['group_slug'], array('user', 'coach')))) {
            $this->load->library('form_validation');
            $this->form_validation->set_rules('booking_id', 'Booking', 'trim|required|is_natural_no_zero');
            if ($this->form_validation->run()) {
                $this->load->model('Booking_model');
                $booking_details_array = $this->Booking_model->get_booking_by_id($this->input->post('booking_id'));
                if (count($booking_details_array) > 0) {
                    $this->load->model('Availability_model');
                    $availability_details_array = $this->Availability_model->get_availability_by_id($booking_details_array['availabilities_id']);
                    if (count($availability_details_array) > 0) {
                        if (($user_details_array['group_slug'] === 'coach' && $availability_details_array['users_id'] !== $user_details_array['user_id']) || ($user_details_array['group_slug'] === 'user' && $booking_details_array['users_id'] !== $user_details_array['user_id'])) {
                            parent::json_output(array('code' => '0', 'message' => 'Invalid User !!!'));
                            return;
                        }
                        if (time() >= strtotime($booking_details_array['booking_start_time']) && time() < strtotime($booking_details_array['booking_end_time'])) {
//                            Check if rating is provided
                            $show_rating_dialog = '0';
                            if ($user_details_array['group_slug'] === 'user') {
                                $this->load->model('Rating_model');
                                $rating_count = $this->Rating_model->get_rating_count_by_bookings_id_and_users_id($booking_details_array['booking_id'], $user_details_array['user_id']);
                                if ($rating_count == 0) {
                                    $show_rating_dialog = '1';
                                }
                            }
                            if ($availability_details_array['availability_for'] === '3') {
                                $bookings_array = $this->Booking_model->get_bookings_by_availability_id($booking_details_array['availabilities_id']);
//                                Refund Tokens if less than 3 users have booked.
                                if (count($bookings_array) < 3) {
                                    $date_time_now = date('Y-m-d H:i:s');
                                    $booking_update_batch_array = array();
                                    $user_tokens_batch_insert_array = array();
                                    foreach ($bookings_array as $booking) {
                                        $booking_update_batch_array[] = array(
                                            'booking_id' => $booking['booking_id'],
                                            'booking_status' => '-1',
                                            'booking_modified' => $date_time_now
                                        );
                                        if ($booking['booking_paid_tokens_used'] > 0) {
                                            $user_tokens_batch_insert_array[] = array(
                                                'users_id' => $booking['users_id'],
                                                'user_token_value' => $booking['booking_paid_tokens_used'],
                                                'user_token_paid' => '1',
                                                'user_token_refund' => '1',
                                                'user_token_created' => $date_time_now
                                            );
                                        }
                                        if ($booking['booking_free_tokens_used'] > 0) {
                                            $user_tokens_batch_insert_array[] = array(
                                                'users_id' => $booking['users_id'],
                                                'user_token_value' => $booking['booking_free_tokens_used'],
                                                'user_token_paid' => '0',
                                                'user_token_refund' => '1',
                                                'user_token_created' => $date_time_now
                                            );
                                        }
                                    }
                                    $this->load->model('User_model');
                                    $this->load->model('User_token_model');
                                    if ($this->Booking_model->batch_update($booking_update_batch_array) && $this->User_token_model->batch_insert($user_tokens_batch_insert_array)) {
                                        $coach_user_details_array = $this->User_model->get_user_by_id($availability_details_array['users_id']);
                                        if (count($coach_user_details_array) > 0) {
                                            $email_details_array = array(
                                                'user_first_name' => $coach_user_details_array['user_first_name'],
                                                'user_last_name' => $coach_user_details_array['user_last_name'],
                                                'user_email' => $coach_user_details_array['user_email'],
                                                'start_time' => $availability_details_array['availability_from']
                                            );
                                            $email_id = parent::add_email_to_queue('', '', $coach_user_details_array['user_email'], $coach_user_details_array['user_id'], 'Group Coaching Cancelled', $this->render_view($email_details_array, 'email', 'email/templates/group_coaching_cancel', TRUE));
                                            if ($email_id > 0) {
                                                @file_get_contents(base_url() . 'email/cron/' . $email_id);
                                            }
                                        }
                                        $user_tokens_details_array = array();
                                        foreach ($bookings_array as $booking) {
                                            if ($user_details_array['user_id'] === $booking['users_id']) {
                                                $user_tokens_details_array = $this->_update_user_tokens($booking['users_id']);
                                            } else {
                                                $this->_update_user_tokens($booking['users_id']);
                                            }
                                            $booked_user_details_array = $this->User_model->get_user_by_id($booking['users_id']);
                                            if (count($booked_user_details_array) > 0) {
                                                $email_details_array = array(
                                                    'user_first_name' => $booked_user_details_array['user_first_name'],
                                                    'user_last_name' => $booked_user_details_array['user_last_name'],
                                                    'coach_first_name' => $coach_user_details_array['user_last_name'],
                                                    'coach_last_name' => $coach_user_details_array['user_last_name'],
                                                    'user_email' => $booked_user_details_array['user_email'],
                                                    'token_count' => $booking['booking_paid_tokens_used'] + $booking['booking_free_tokens_used'],
                                                    'start_time' => $booking['booking_start_time']
                                                );
                                                $email_id = parent::add_email_to_queue('', '', $booked_user_details_array['user_email'], $booked_user_details_array['user_id'], 'You got ' . $email_details_array['token_count'] . ' tokens refund', $this->render_view($email_details_array, 'email', 'email/templates/token_refund', TRUE));
                                                if ($email_id > 0) {
                                                    @file_get_contents(base_url() . 'email/cron/' . $email_id);
                                                }
                                            }
                                        }
                                        parent::json_output(array('code' => '1', 'message' => 'Not Enough participants to Initiate the Group Coaching. Tokens are refunded.', 'data' => $user_tokens_details_array));
                                        return;
                                    }
                                }
//                                Quote Max seconds remaining to Coach
                                if ($user_details_array['group_slug'] === 'coach') {
                                    foreach ($bookings_array as $booking) {
                                        if (strtotime($booking_details_array['booking_end_time']) < strtotime($booking['booking_end_time'])) {
                                            $booking_details_array['booking_end_time'] = $booking['booking_end_time'];
                                        }
                                    }
                                }
                            }
                            parent::json_output(array('code' => '1', 'message' => 'Success', 'data' => array('seconds_remaining' => strtotime($booking_details_array['booking_end_time']) - time(), 'show_rating_dialog' => $show_rating_dialog, 'start_session' => $availability_details_array['availability_for'])));
                            return;
                        } else {
                            parent::json_output(array('code' => '0', 'message' => 'Please start at correct time !!!'));
                            return;
                        }
                    }
                }
            }
        }
        $this->_error();
    }

}
