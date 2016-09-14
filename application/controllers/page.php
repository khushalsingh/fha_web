<?php

if (!defined('BASEPATH'))
    exit('No direct script access allowed');

class Page extends MY_Controller {

    public $public_methods = array();

    function __construct() {
        parent::__construct();
    }

    function index() {
        redirect('login', 'refresh');
    }

    function safety() {
        $data = array();
        $data['title'] = 'Safety Tips';
        parent::render_view($data);
    }

    function prohibited() {
        $data = array();
        $data['title'] = 'Prohibited Items';
        parent::render_view($data);
    }

    function scams() {
        $data = array();
        $data['title'] = 'Avoiding Scams';
        parent::render_view($data);
    }

    function about_us() {
        $data = array();
        $data['title'] = 'About Us';
        parent::render_view($data);
    }

    function how_does_it_work() {
        $data = array();
        $data['title'] = 'How Does It Work';
        parent::render_view($data);
    }

    function faq() {
        $data = array();
        $data['title'] = 'FAQ';
        parent::render_view($data);
    }

    function contact_us() {
        $data = array();
        $data['title'] = 'Contact Us';
        parent::render_view($data);
    }

    function stories() {
        $data = array();
        $data['title'] = 'Stories';
        parent::render_view($data);
    }

    function feedback() {
        $data = array();
        $data['title'] = 'Feedback';
        parent::render_view($data);
    }

    function privacy() {
        $data = array();
        $data['title'] = 'Privacy';
        parent::render_view($data);
    }

    function terms() {
        $data = array();
        $data['title'] = 'Terms';
        parent::render_view($data);
    }

    function page_not_found() {
        $data = array();
        $data['title'] = '404 Page Not Found';
        parent::render_view($data, '', 'page/page_not_found');
    }

}
