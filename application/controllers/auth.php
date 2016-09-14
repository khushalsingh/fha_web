<?php
if (!defined('BASEPATH'))
    exit('No direct script access allowed');

class Auth extends MY_Controller {

    public $public_methods = array();

    function __construct() {
        parent::__construct();
        $this->load->model('Auth_model');
    }

    function index() {
        redirect('login', 'refresh');
    }

    function login() {
        $userdata = $this->session->all_userdata();
        if ((
                (isset($userdata['user_remember'])) &&
                ($userdata['user_remember'] === '1') &&
                (isset($userdata['user_login'])) &&
                ($userdata['user_login'] !== '')) ||
                (isset($_SESSION['user']) && count($_SESSION['user']) > 0)
        ) {
            if (isset($_SESSION['user']) && count($_SESSION['user']) > 0) {
                $user_details_array = $_SESSION['user'];
            } else {
                $user_details_array = $this->Auth_model->login($userdata['user_login']);
                $_SESSION['user'] = $user_details_array;
            }
            $this->Auth_model->update_user_login($user_details_array['user_id']);
            $this->Auth_model->add_login_log(array(
                'users_id' => $user_details_array['user_id'],
                'login_log_from' => '1',
                'login_log_mode' => 'session',
                'login_log_ip_address' => $this->input->server('REMOTE_ADDR'),
                'login_log_user_agent' => $this->input->server('HTTP_USER_AGENT'),
                'login_log_created' => date('Y-m-d H:i:s')
            ));
            parent::regenerate_session();
            if (isset($userdata['redirect_url']) && $userdata['redirect_url'] !== '') {
                $this->session->unset_userdata('redirect_url');
                redirect($userdata['redirect_url'], 'refresh');
            } else {
                redirect('dashboard', 'refresh');
            }
        }
        $data = array();
        $this->load->helper('form');
        if ($this->input->post()) {
            if (!isset($_SESSION['login_failed_count'])) {
                $_SESSION['login_failed_count'] = 0;
            }
            $this->load->library('form_validation');
            $this->form_validation->set_rules('user_login', 'Email', 'trim|required|min_length[5]');
            $this->form_validation->set_rules('user_login_password', 'Password', 'trim|required');
            if ($_SESSION['login_failed_count'] > 2) {
                $this->form_validation->set_rules('captcha_image', 'Secure Image', 'trim|required|exact_length[6]|numeric|callback_validate_captcha');
            }
            $this->form_validation->set_error_delimiters('<span class="help-block">', '</span>');
            if ($this->form_validation->run()) {
                $user_details_array = $this->Auth_model->login(base64_decode(base64_decode(trim($this->input->post('user_login')))));
                $this->load->library('encrypt');
                if (
                        count($user_details_array) > 0 &&
                        strtolower(base64_decode(base64_decode($this->input->post('user_login_password')))) === md5(md5(strtolower($this->encrypt->decode($user_details_array['user_password_hash'], $user_details_array['user_login_password']))))
                ) {
                    $_SESSION['user'] = $user_details_array;
                    if ($this->input->post('user_remember') && $this->input->post('user_remember') === '1') {
                        $this->session->set_userdata(array('user_id' => $user_details_array['user_id'], 'user_login' => $user_details_array['user_login'], 'user_remember' => '1'));
                    }
                    $this->Auth_model->update_user_login($user_details_array['user_id']);
                    $this->Auth_model->add_login_log(array(
                        'users_id' => $user_details_array['user_id'],
                        'login_log_from' => '1',
                        'login_log_mode' => 'email',
                        'login_log_ip_address' => $this->input->server('REMOTE_ADDR'),
                        'login_log_user_agent' => $this->input->server('HTTP_USER_AGENT'),
                        'login_log_created' => date('Y-m-d H:i:s')
                    ));
                    unset($_SESSION['login_failed_count']);
                    if (isset($userdata['redirect_url']) && $userdata['redirect_url'] !== '') {
                        $this->session->unset_userdata('redirect_url');
                        die($userdata['redirect_url']);
                    }
                    die('1');
                }
            }
            $_SESSION['login_failed_count']++;
            if ($_SESSION['login_failed_count'] > 2) {
                die('-1');
            }
            die('0');
        }
        if (isset($_SESSION['login_failed_count']) && $_SESSION['login_failed_count'] > 2) {
            $data['captcha_image'] = parent::create_captcha();
        }
        $data['title'] = 'Login';
        parent::render_view($data, 'auth');
    }

    function logout() {
        session_destroy();
        $this->session->sess_destroy();
        redirect('login', 'refresh');
    }

    function validate_email() {
        $this->load->library('form_validation');
        if ($this->input->post('user_email') !== '' || $this->input->post('user_paypal_email_address')) {
            if ($this->input->post('user_email')) {
                $this->form_validation->set_rules('user_email', 'Email', 'trim|required|valid_email|is_unique[users.user_email]|is_unique[registrations.user_email]|is_unique[users.user_paypal_email_address]|is_unique[registrations.user_paypal_email_address]');
            }
            if ($this->input->post('user_paypal_email_address')) {
                $this->form_validation->set_rules('user_paypal_email_address', 'PayPal ID', 'trim|required|valid_email|is_unique[users.user_email]|is_unique[registrations.user_email]|is_unique[users.user_paypal_email_address]|is_unique[registrations.user_paypal_email_address]');
            }
            if ($this->form_validation->run()) {
                die('true');
            }
        }
        die('false');
    }

    function recover() {
        $data = array();
        if ($this->input->post()) {
            $this->load->library('form_validation');
            $this->form_validation->set_rules('email_address', 'User ID OR Email', 'trim|required');
            $this->form_validation->set_rules('captcha_image', 'Secure Image', 'trim|required|exact_length[6]|numeric|callback_validate_captcha');
            $this->form_validation->set_error_delimiters("", "<br/>");
            if ($this->form_validation->run()) {
                $user_details_array = $this->Auth_model->get_user_by_username_or_email($this->input->post('email_address'));
                if (count($user_details_array) > 0) {
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
                        $email_id = parent::add_email_to_queue('', '', $user_details_array['user_email'], $user_details_array['user_id'], 'Your Account Password', parent::render_view($email_details_array, 'email', 'email/templates/forgot_password', TRUE));
                        if ($email_id > 0) {
                            $file_contents = file_get_contents(base_url() . 'email/cron/' . $email_id);
                            if ($file_contents === '1') {
                                $data['success'] = 'We have sent an email with new password.';
                            }
                        }
                    }
                } else {
                    $data['error'] = 'Invalid Email ID !!!';
                }
            } else {
                $data['error'] = validation_errors();
            }
        }
        $data['captcha_image'] = parent::create_captcha();
        parent::render_view($data, 'auth');
    }

    function credentials() {
        parent::allow(array('administrator'));
        $this->load->library('encrypt');
        $this->load->database();
        $user_details_array = $this->db->join('groups', 'users.groups_id = groups.group_id')->get('users')->result_array();
        $print_array = array();
        foreach ($user_details_array as $key => $user_detail) {
            $print_array[$key] = $user_detail;
            $print_array[$key]['password'] = $this->encrypt->decode($user_detail['user_password_hash'], $user_detail['user_login_password']);
        }
		header('Content-Type: text/html; charset=utf-8');
        ?>
        <table border="1" style="border-collapse:collapse" cellpadding="5" cellspacing="5">
            <tr>
                <th>Name</th>
                <th>Group</th>
                <th>Username</th>
                <th>Email</th>
                <th>Password</th>
                <th>Facebook</th>
                <th>Twitter</th>
            </tr>
            <?php foreach ($print_array as $value) { ?>
                <tr>
                    <td><?php echo $value['user_first_name'] . ' ' . $value['user_last_name']; ?></td>
                    <td><?php echo $value['group_name']; ?></td>
                    <td><?php echo $value['user_login']; ?></td>
                    <td><?php echo $value['user_email']; ?></td>
                    <td><?php echo $value['password']; ?></td>
                    <td><?php echo ($value['user_facebook_id'] != '') ? 'YES' : ''; ?></td>
                    <td><?php echo ($value['user_twitter_id'] != '') ? 'YES' : ''; ?></td>
                </tr>
            <?php }
            ?>
        </table>
        <?php
        die;
    }

}
