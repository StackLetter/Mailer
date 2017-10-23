<?php

namespace Newsletter\Model;


class Answers extends BaseModel{
    const TABLE = 'answers';

    public function get($id){
        $answer = $this->db->select(static::TABLE)->where('id', $id)->fetch();
        if($answer){
            $answer->question = $this->db->select(Questions::TABLE)->where('id', $answer->question_id)->fetch();
        }
        return $answer;
    }
}
