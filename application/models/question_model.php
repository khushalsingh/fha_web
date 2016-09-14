<?php

if (!defined('BASEPATH'))
    exit('No direct script access allowed');

class Question_model extends MY_Model {

    function __construct() {
        parent::__construct();
    }

    function add($question_insert_array) {
        if ($this->db->insert('questions', $question_insert_array)) {
            return $this->db->insert_id();
        }
        return 0;
    }

    function edit_question_by_question_id($question_id, $update_question_detail_array) {
        return $this->db->where('question_id', $question_id)->update('questions', $update_question_detail_array);
    }

    function get_questions_list($limit, $offset) {
        $this->db->join('users', 'users.user_id = questions.users_id');
        return $this->db->limit($limit, $offset)->order_by('question_id', 'desc')->get('questions')->result_array();
    }

    function search_questions($search, $limit, $offset) {
        $this->db->join('users', 'users.user_id = questions.users_id');
        return $this->db->like('question_topic', $search)->or_like('question_value', $search)->limit($limit, $offset)->order_by('question_id', 'desc')->get('questions')->result_array();
    }

}
