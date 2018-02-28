<?php

namespace Newsletter;

use Neevo\Row;
use Nette\SmartObject;

/**
 * @property-read string $content_type
 * @property-read string $name
 * @property-read string $description
 * @property-read string $content_endpoint
 * @property-read int $limit
 */
class NewsletterSection implements \IteratorAggregate {
    use SmartObject;

    /** @var Newsletter */
    protected $newsletter;

    protected $definition;
    protected $contentIds;

    /** @var Row[] */
    protected $content = [];

    public $id;

    public function __construct(Newsletter $newsletter, array $definition){
        $this->newsletter = $newsletter;
        $this->definition = $definition;
    }

    public function getContentIds(){
        return $this->contentIds;
    }

    public function setContentIds($ids){
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
