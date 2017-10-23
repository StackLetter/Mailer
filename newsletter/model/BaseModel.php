<?php

namespace Newsletter\Model;

use Neevo\Manager;
use Nette\SmartObject;
use Newsletter\Model;

/**
 * @todo Consider using nette/database to optimize queries
 */
class BaseModel {
    use SmartObject;

    /** @var Manager */
    protected $db;

    /** @var Model */
    protected $model;

    public function __construct(Manager $db, Model $model){
        $this->db = $db;
        $this->model = $model;
    }

    public function get($id){
        return $this->db->select(static::TABLE)->where('id', $id)->fetch();
    }

    public function getAll(){
        return $this->db->select(static::TABLE);
    }
}
