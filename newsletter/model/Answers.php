<?php

namespace Newsletter\Model;


class Answers extends BaseModel{
    const TABLE = 'answers';

    public function get($id, $recurse = true){
        $answer = $this->db->select(static::TABLE)->where('id', $id)->fetch();
        if(!$answer){
            return false;
        }
        $answer->user = $this->model->users->get($answer->owner_id, false);
        if($recurse && $answer){
            $answer->question = $this->model->questions->get($answer->question_id, false);
        }
        return $answer;
    }


    public function getByExternal($external_id, $site_id, $recurse = true){
        $answer = $this->db->select(static::TABLE)
            ->where('external_id', $external_id)
            ->where('site_id', $site_id)
            ->fetch();
        if(!$answer){
            return false;
        }
        $answer->user = $this->model->users->get($answer->owner_id, false);
        if($recurse && $answer){
            $answer->question = $this->model->questions->get($answer->question_id, false);
        }
        return $answer;
    }
}
