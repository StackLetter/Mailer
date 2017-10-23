<?php

namespace Newsletter\Model;

use Neevo\Manager;
use Nette\SmartObject;

class BaseModel {
    use SmartObject;

    /** @var Manager */
    protected $db;

    public function __construct(Manager $db){
        $this->db = $db;
    }

    public function get($id){
        return $this->db->select(static::TABLE)->where('id', $id)->fetch();
    }

    public function getAll(){
        return $this->db->select(static::TABLE);
    }
}
