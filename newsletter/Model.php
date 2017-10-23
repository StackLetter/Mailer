<?php

namespace Newsletter;

use Neevo\Manager;
use Newsletter\Model\User;

/**
 * @property-read User $user
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
}
