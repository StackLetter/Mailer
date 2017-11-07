<?php

namespace Newsletter\Model;


class Tags extends BaseModel{
    const TABLE = 'tags';
    const MAPPING_TABLE = 'question_tags';

    public function getQuestionTags($question_id){
        return $this->db->select('MAX(t.id) AS id, t.name', static::MAPPING_TABLE)
            ->leftJoin(static::TABLE. ' t', 't.id = ' . static::MAPPING_TABLE . '.tag_id')
            ->where('question_id', $question_id)->group('t.name');
    }
}
