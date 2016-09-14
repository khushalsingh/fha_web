<?php

if (!defined('BASEPATH'))
    exit('No direct script access allowed');

class Booking_model extends MY_Model {

    function __construct() {
        parent::__construct();
    }

    function add($booking_insert_array) {
        if ($this->db->insert('bookings', $booking_insert_array)) {
            return $this->db->insert_id();
        }
        return 0;
    }

    function batch_update($booking_update_batch_array) {
        $this->db->update_batch('bookings', $booking_update_batch_array, 'booking_id');
        return TRUE;
    }

    function get_booking_by_id($booking_id) {
        return $this->db->get_where('bookings', array('booking_id' => $booking_id, 'booking_status' => '1'))->row_array();
    }

    function get_bookings_by_availability_id($availability_id) {
        return $this->db->get_where('bookings', array('availabilities_id' => $availability_id, 'booking_status' => '1'))->result_array();
    }

    function get_booking_by_availability_id_and_user_id($availability_id, $user_id) {
        return $this->db->get_where('bookings', array('availabilities_id' => $availability_id, 'users_id' => $user_id, 'booking_status' => '1'))->row_array();
    }

    function get_monthly_bookings_by_user_id($user_id, $year, $month) {
        $this->db->like('bookings.booking_start_time', $year . '-' . $month, 'after');
        $this->db->join('availabilities', 'availabilities.availability_id = bookings.availabilities_id');
        $this->db->join('topics', 'topics.topic_id = availabilities.topics_id');
        return $this->db->get_where('bookings', array('availabilities.users_id' => $user_id, 'booking_status' => '1'))->result_array();
    }

    function get_coach_bookings($users_id, $limit, $offset) {
        return $this->db->join('users', 'users.user_id = bookings.users_id')->join('availabilities', 'availabilities.availability_id = bookings.availabilities_id')->order_by('bookings.booking_start_time', 'asc')->limit($limit, $offset)->get_where('bookings', array('availabilities.users_id' => $users_id, 'users.user_status' => '1', 'booking_end_time >' => date('Y-m-d H:i:s'), 'booking_status' => '1'))->result_array();
    }

    function get_user_bookings($users_id, $limit, $offset) {
        return $this->db->join('availabilities', 'availabilities.availability_id = bookings.availabilities_id')->join('users', 'users.user_id = availabilities.users_id')->order_by('bookings.booking_start_time', 'asc')->limit($limit, $offset)->get_where('bookings', array('bookings.users_id' => $users_id, 'users.user_status' => '1', 'booking_end_time >' => date('Y-m-d H:i:s'), 'booking_status' => '1'))->result_array();
    }

}