<?php

namespace Newsletter\Model;


class Answers extends BaseModel{
    const TABLE = 'answers';

    public function get($id, $recurse = true){
        $answer = $this->db->select(static::TABLE)->where('id', $id)->fetch();
        $answer->user = $this->model->users->get($answer->owner_id);
        if($recurse && $answer){
            $answer->question = $this->model->questions->get($answer->question_id, false);
        }
        return $answer;
    }
}
