<?php

namespace Newsletter\Model;


class Questions extends BaseModel{
    const TABLE = 'questions';

    public function get($id, $recurse = true){
        $question = $this->db->select(static::TABLE)->where('id', $id)->fetch();
        if(!$question){
            return false;
        }
        $question->answer_count = $this->model->answers->getAll()->where('question_id', $id)->count('id');
        $question->user = $this->model->users->get($question->owner_id, false);

        if($recurse){
            $question->tags = $this->model->tags->getQuestionTags($question->id);
            if($question->accepted_answer_id !== NULL){
                $question->accepted_answer = $this->model->answers->get($question->accepted_answer_id, false);
            }
        }
        return $question;
    }
}
