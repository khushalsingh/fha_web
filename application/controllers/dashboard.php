<?php

if (!defined('BASEPATH'))
    exit('No direct script access allowed');

class Dashboard extends MY_Controller {

    public $public_methods = array();

    function __construct() {
        parent::__construct();
    }

    function index() {
        $data = array();
        parent::render_view($data);
    }

}