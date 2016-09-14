<?php

if (!defined('BASEPATH'))
    exit('No direct script access allowed');
date_default_timezone_set('Europe/London');

function pr($data) {
    echo '<pre>';
    print_r($data);
    echo '</pre>';
    die;
}

class MY_Controller extends CI_Controller {

    public $public_methods = array();

    function __construct() {
        parent::__construct();
        if ($this->router->class !== 'api') {
            @session_start();
        }
        if (ENVIRONMENT === 'development' && !$this->input->is_ajax_request() && !$this->input->is_cli_request()) {
//			$this->output->enable_profiler(TRUE);
        }
        if (!in_array($this->router->method, $this->public_methods) && !$this->check_auth() && !in_array($this->router->class, array('page', 'api', 'auth'))) {
            $this->session->set_userdata('redirect_url', base_url() . $this->uri->uri_string);
            redirect('login', 'refresh');
        }
        if (isset($_SESSION['user']) && isset($_SESSION['user']['force_change_password']) && $_SESSION['user']['force_change_password'] == 1) {
            if ($this->uri->uri_string != 'user/change_password' && $this->uri->uri_string != 'auth/logout') {
                redirect('user/change_password', 'refresh');
            }
        }
    }

    function render_view($data = array(), $template = '', $view = '', $get_string = FALSE) {
        if ($template === '') {
            $template = 'common';
            if (isset($_SESSION['user']['group_slug'])) {
                $template = 'system';
            }
        }
        if ($view === '' && $this->router->directory === '') {
            $view = $this->router->class . '/' . $this->router->method;
        } else {
            if ($view === '') {
                $view = $this->router->class . '/' . $this->router->method;
            }
        }
        if ($get_string) {
            $return = $this->load->view('templates/' . $template . '/header', $data, TRUE);
            $return .= $this->load->view('templates/' . $template . '/menu', $data, TRUE);
            $return .= $this->load->view($view, $data, TRUE);
            $return .= $this->load->view('templates/' . $template . '/footer', $data, TRUE);
            return $return;
        }
        $this->load->view('templates/' . $template . '/header', $data);
        $this->load->view('templates/common/noscript', $data);
        $this->load->view('templates/' . $template . '/menu', $data);
        $this->load->view($view, $data);
        $this->load->view('templates/' . $template . '/footer', $data);
        return;
    }

    function json_output($data) {
        $this->output->enable_profiler(FALSE);
        $this->output->set_content_type('application/json')->set_output(json_encode($data));
    }

    function check_auth() {
        if (isset($_SESSION['user'])) {
            return TRUE;
        } else {
            if (strpos($this->uri->uri_string, '/auth') !== FALSE) {
                $this->session->set_userdata('redirect_uri', $this->uri->uri_string);
                redirect('login', 'refresh');
            }
        }
        return FALSE;
    }

    function regenerate_session() {
        if (isset($_SESSION['user']['user_id'])) {
            $this->load->model('User_model');
            $_SESSION['user'] = $this->User_model->get_user_by_id($_SESSION['user']['user_id']);
            return TRUE;
        }
        return FALSE;
    }

    function generate_dropdown_array($array, $key, $value) {
        $return = array();
        $return[''] = '';
        foreach ($array as $result) {
            $return[$result[$key]] = $result[$value];
        }
        return $return;
    }

    /**
     * Function used to send email via cron jobs.
     */
    function send_email($email_from, $email_from_name, $email_to, $email_subject, $email_message, $email_cc_array = array(), $email_bcc_array = array(), $attachments_array = array()) {
        $this->load->config('email');
        if ($this->config->item('email_smtp') === TRUE) {
            include_once(FCPATH . 'application/libraries/Mandrill.php');
            $mandrill = new Mandrill($this->config->item('smtp_pass'));
            $message = array(
                'html' => $email_message,
                'subject' => $email_subject,
                'from_email' => $email_from,
                'from_name' => $email_from_name,
                'to' => array(
                    array('email' => $email_to, 'type' => 'to')
                ),
                'headers' => array('Reply-To' => $email_from)
            );
            $result = $mandrill->messages->send($message, TRUE);
            if (isset($result[0]['_id']) && $result[0]['_id']) {
                return $result[0]['_id'];
            }
        } else {
            $this->load->library('email');
            $config['mailtype'] = $this->config->item('mailtype');
            $config['crlf'] = $this->config->item('crlf');
            $config['newline'] = $this->config->item('newline');
            $config['wordwrap'] = $this->config->item('wordwrap');
            $this->email->initialize($config);
            $this->email->from($this->config->item('email_from'), $this->config->item('email_from_name'));
            $this->email->to($email_to);
            $this->email->subject($email_subject);
            $this->email->message($email_message);
            foreach ($attachments_array as $attachment) {
                $this->email->attach($attachment);
            }
            if ($this->email->send()) {
                return 'sendmail';
            }
        }
        return '';
    }

    /**
     * Adds Email to queue in database in emails table
     */
    function add_email_to_queue($email_from, $email_from_name, $email_to, $users_id, $email_subject, $email_message, $email_cc_array = array(), $email_bcc_array = array(), $attachments_array = array()) {
        $this->config->load('email');
        $this->load->library('email');
        $email_insert_array = array(
            'email_hash' => md5($email_from . microtime() . $email_to),
            'email_mandrill_id' => '',
            'email_from' => ($email_from !== '') ? $email_from : $this->config->item('email_from'),
            'email_from_name' => ($email_from_name !== '') ? $email_from_name : $this->config->item('email_from_name'),
            'users_id' => $users_id,
            'email_to' => $email_to,
            'email_cc' => json_encode($email_cc_array),
            'email_bcc' => json_encode(array($this->config->item('email_bcc'))),
            'email_subject' => $email_subject,
            'email_body' => $email_message,
            'email_status' => '0',
            'email_created' => date('Y-m-d H:i:s')
        );
        $this->load->model('Email_model');
        return $this->Email_model->add_email_to_queue($email_insert_array);
    }

    function upload_files() {
        /**
         * upload.php
         *
         * Copyright 2013, Moxiecode Systems AB
         * Released under GPL License.
         *
         * License: http://www.plupload.com/license
         * Contributing: http://www.plupload.com/contributing
         */
// Make sure file is not cached (as it happens for example on iOS devices)
        header("Expires: Mon, 26 Jul 1997 05:00:00 GMT");
        header("Last-Modified: " . gmdate("D, d M Y H:i:s") . " GMT");
        header("Cache-Control: no-store, no-cache, must-revalidate");
        header("Cache-Control: post-check=0, pre-check=0", false);
        header("Pragma: no-cache");

// 5 minutes execution time
        @set_time_limit(5 * 60);

// Uncomment this one to fake upload time
// usleep(5000);
// Settings
        $targetDir = FCPATH . "uploads/";
//$targetDir = 'uploads';
        $cleanupTargetDir = true; // Remove old files
        $maxFileAge = 1800; // Temp file age in seconds
// Create target dir
        if (!file_exists($targetDir)) {
            @mkdir($targetDir);
        }
        // Clean Up Old Files
        $current_dir = @opendir($targetDir);
        while ($filename = @readdir($current_dir)) {
            if ($filename != "." and $filename != ".." and $filename != "index.html" and $filename != ".htaccess") {
                if (is_file($targetDir . $filename) && filemtime($targetDir . $filename) < time() - $maxFileAge) {
                    @unlink($targetDir . $filename);
                }
            }
        }
        @closedir($current_dir);
        // Clean Up Old Files End
        // Get a file name
        if (isset($_REQUEST["name"])) {
            $fileName = $_REQUEST["name"];
        } elseif (!empty($_FILES)) {
            $fileName = $_FILES["file"]["name"];
        } else {
            $fileName = uniqid("file_");
        }

        $filePath = $targetDir . DIRECTORY_SEPARATOR . $fileName;

// Chunking might be enabled
        $chunk = isset($_REQUEST["chunk"]) ? intval($_REQUEST["chunk"]) : 0;
        $chunks = isset($_REQUEST["chunks"]) ? intval($_REQUEST["chunks"]) : 0;


// Remove old temp files
        if ($cleanupTargetDir) {
            if (!is_dir($targetDir) || !$dir = opendir($targetDir)) {
                die('{"jsonrpc" : "2.0", "error" : {"code": 100, "message": "Failed to open temp directory."}, "id" : "id"}');
            }

            while (($file = readdir($dir)) !== false) {
                $tmpfilePath = $targetDir . DIRECTORY_SEPARATOR . $file;

                // If temp file is current file proceed to the next
                if ($tmpfilePath == "{$filePath}.part") {
                    continue;
                }

                // Remove temp file if it is older than the max age and is not the current file
                if (preg_match('/\.part$/', $file) && (filemtime($tmpfilePath) < time() - $maxFileAge)) {
                    @unlink($tmpfilePath);
                }
            }
            closedir($dir);
        }


// Open temp file
        if (!$out = @fopen("{$filePath}.part", $chunks ? "ab" : "wb")) {
            die('{"jsonrpc" : "2.0", "error" : {"code": 102, "message": "Failed to open output stream."}, "id" : "id"}');
        }

        if (!empty($_FILES)) {
            if ($_FILES["file"]["error"] || !is_uploaded_file($_FILES["file"]["tmp_name"])) {
                die('{"jsonrpc" : "2.0", "error" : {"code": 103, "message": "Failed to move uploaded file."}, "id" : "id"}');
            }

            // Read binary input stream and append it to temp file
            if (!$in = @fopen($_FILES["file"]["tmp_name"], "rb")) {
                die('{"jsonrpc" : "2.0", "error" : {"code": 101, "message": "Failed to open input stream."}, "id" : "id"}');
            }
        } else {
            if (!$in = @fopen("php://input", "rb")) {
                die('{"jsonrpc" : "2.0", "error" : {"code": 101, "message": "Failed to open input stream."}, "id" : "id"}');
            }
        }

        while ($buff = fread($in, 4096)) {
            fwrite($out, $buff);
        }

        @fclose($out);
        @fclose($in);

// Check if file has been uploaded
        if (!$chunks || $chunk == $chunks - 1) {
            // Strip the temp .part suffix off
            rename("{$filePath}.part", $filePath);
        }

// Return Success JSON-RPC response
        die('{"jsonrpc" : "2.0", "result" : null, "id" : "id"}');
    }

    function get_pagination($base_url, $counter_position, $total_rows, $per_page = NULL, $prefix = '', $suffix = '', $is_ajax = FALSE, $div_id = '', $show_count = FALSE, $additional_param = '') {
        if ($is_ajax === TRUE) {
            $this->load->library('Jquery_pagination');
            $config['div'] = '#' . $div_id;
            $config['show_count'] = $show_count;
            $config['additional_param'] = $additional_param;
        } else {
            $this->load->library('pagination');
        }
        $config['base_url'] = $base_url;
        $config['prefix'] = $prefix;
        $config['suffix'] = $suffix;
        $config['uri_segment'] = $counter_position;
        $config['full_tag_open'] = '<ul class="pagination">';
        $config['full_tag_close'] = '</ul>';
        $config['num_tag_open'] = '<li>';
        $config['num_tag_close'] = '</li>';
        $config['first_tag_open'] = '<li>';
        $config['first_link'] = '&laquo; First';
        $config['first_tag_close'] = '</li>';
        $config['last_link'] = 'Last &raquo;';
        $config['last_tag_open'] = '<li>';
        $config['last_tag_close'] = '</li>';
        $config['cur_tag_open'] = '<li class="active"><a class="number current" href="javascript:;">';
        $config['cur_tag_close'] = '</a></li>';
        $config['next_tag_open'] = '<li>';
        $config['next_link'] = 'Next &raquo;';
        $config['next_tag_close'] = '</li>';
        $config['prev_tag_open'] = '<li>';
        $config['prev_link'] = '&laquo; Previous';
        $config['prev_tag_close'] = '</li>';
        $config['total_rows'] = $total_rows;
        $config['anchor_class'] = 'class="" ';
        if ($per_page != NULL) {
            $config['per_page'] = $per_page;
        } else {
            $config['per_page'] = '10';
        }
        if ($is_ajax === TRUE) {
            $this->jquery_pagination->initialize($config);
            if ($this->jquery_pagination->create_links() != '') {
                return $this->jquery_pagination->create_links();
            }
        } else {
            $this->pagination->initialize($config);
            if ($this->pagination->create_links() != '') {
                return $this->pagination->create_links();
            }
        }
        return;
    }

    function mask_characters($string, $length = 6, $character = '*') {
        return substr_replace($string, str_repeat($character, $length), 0, $length);
    }

    function create_captcha() {
        $this->load->helper('captcha');
        $this->load->helper('string');
        $random_string = random_string('numeric', 6);
        $captcha_array = array(
            'word' => $random_string,
            'img_path' => FCPATH . 'captcha/',
            'img_url' => base_url() . 'captcha/',
            'font_path' => BASEPATH . 'fonts/texb.ttf',
            'img_width' => '150',
            'img_height' => '30',
            'expiration' => 300
        );
        $captcha = create_captcha($captcha_array);
        $_SESSION['captcha_image'] = $random_string;
        return $captcha['image'];
    }

    function validate_captcha($captcha_image) {
        if (isset($_SESSION['captcha_image']) && $captcha_image === $_SESSION['captcha_image']) {
            unset($_SESSION['captcha_image']);
            return TRUE;
        }
        $this->form_validation->set_message('validate_captcha', 'The %s is not correct.');
        return FALSE;
    }

    function allow($allowed_groups) {
        if (!isset($_SESSION['user']['group_slug']) || !in_array($_SESSION['user']['group_slug'], $allowed_groups)) {
            redirect('dashboard');
        }
    }

    function generate_random_string($type = 'alnum', $length = 6) {
        switch ($type) {
            case 'alpha' : $pool = 'ABCDEFGHJKLMNPQRSTUVWXYZ';
                break;
            case 'alnum' : $pool = '23456789ABCDEFGHJKLMNPQRSTUVWXYZ';
                break;
            case 'numeric' : $pool = '23456789';
                break;
        }
        $str = '';
        for ($i = 0; $i < $length; $i++) {
            $str .= substr($pool, mt_rand(0, strlen($pool) - 1), 1);
        }
        return $str;
    }

    function resize_image($source_image, $new_image, $width, $height) {
        $this->load->library('image_lib');
        $config['source_image'] = $source_image;
        $config['new_image'] = $new_image;
        $config['maintain_ratio'] = TRUE;
        $config['width'] = $width;
        $config['height'] = $height;
        $config['quality'] = 100;
        $this->image_lib->initialize($config);
        $return = TRUE;
        if (!$this->image_lib->resize()) {
            $return = $this->image_lib->display_errors();
        }
        $this->image_lib->clear();
        return $return;
    }

    function crop_image($source_image, $new_image, $width, $height, $x_axis, $y_axis) {
        $this->load->library('image_lib');
        $config['maintain_ratio'] = FALSE;
        $config['source_image'] = $source_image;
        $config['new_image'] = $new_image;
        $config['width'] = $width;
        $config['height'] = $height;
        $config['x_axis'] = $x_axis;
        $config['y_axis'] = $y_axis;
        $config['quality'] = 100;
        $this->image_lib->initialize($config);
        $return = TRUE;
        if (!$this->image_lib->crop()) {
            $return = $this->image_lib->display_errors();
        }
        $this->image_lib->clear();
        return $return;
    }

}
