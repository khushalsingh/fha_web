<?php

if (!defined('BASEPATH'))
    exit('No direct script access allowed');

class Answer_model extends MY_Model {

    function __construct() {
        parent::__construct();
    }

    function add($answer_insert_array) {
        if ($this->db->insert('answers', $answer_insert_array)) {
            return $this->db->insert_id();
        }
        return 0;
    }

    function count_answer_by_question_id($question_id) {
        return $this->db->get_where('answers', array('questions_id' => $question_id))->num_rows();
    }

    function get_answers_list($question_id, $limit, $offset) {
        $this->db->join('questions', 'questions.question_id = answers.questions_id');
        $this->db->join('users', 'users.user_id = answers.users_id');
        return $this->db->limit($limit, $offset)->order_by('answer_id', 'desc')->get_where('answers', array('questions_id' => $question_id))->result_array();
    }

}
