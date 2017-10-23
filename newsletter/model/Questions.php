<?php

namespace Newsletter\Model;


class Questions extends BaseModel{
    const TABLE = 'questions';

    public function get($id){
        $question = $this->db->select(static::TABLE)->where('id', $id)->fetch();

        if($question && $question->accepted_answer_id !== NULL){
            $answer = $this->db->select(Answers::TABLE)->where('id', $question->accepted_answer_id)->fetch();
            $question->answer = $answer;
        }
        return $question;
    }
}
