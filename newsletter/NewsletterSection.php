<?php

namespace Newsletter;

use Neevo\Row;
use Nette\SmartObject;

class NewsletterSection implements \IteratorAggregate {
    use SmartObject;

    /** @var Newsletter */
    private $newsletter;

    private $definition;
    private $contentIds;

    /** @var Row[] */
    private $content = [];

    public $id;

    public function __construct(Newsletter $newsletter, array $definition){
        $this->newsletter = $newsletter;
        $this->definition = $definition;
    }

    public function getContentIds(){
        return $this->contentIds;
    }

    public function setContentIds(array $ids){
        $this->contentIds = $ids;
    }

    public function getDefinition(){
        return $this->definition;
    }

    public function addContent(Row $row){
        $this->content[] = $row;
    }

    public function __get($key){
        return $this->definition[$key] ?? null;
    }

    public function getIterator(){
        return new \ArrayIterator($this->content);
    }
}
