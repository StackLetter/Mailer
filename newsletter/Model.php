<?php

namespace Newsletter;

use Neevo\Manager;
use Newsletter\Model\Answers;
use Newsletter\Model\Questions;
use Newsletter\Model\Users;

/**
 * @property-read Users $users
 * @property-read Questions $questions
 * @property-read Answers $answers
 */
class Model {

    private $db;
    private $models = [];

    public function __construct(Manager $db){
        $this->db = $db;
    }

    public function getModel($name){
        $class = __NAMESPACE__ . '\\Model\\' . ucfirst($name);
        if(!class_exists($class)){
            throw new \RuntimeException("No such model '$class'.");
        }
        if(isset($this->models[$name])){
            return $this->models[$name];
        }
        return $this->models[$name] = new $class($this->db);
    }


    public function __get($key){
        return $this->getModel($key);
    }

    /**
     * @return Manager
     */
    public function getDatabase(){
        return $this->db;
    }
}
