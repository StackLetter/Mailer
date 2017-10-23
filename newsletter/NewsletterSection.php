<?php

namespace Newsletter;

use Nette\SmartObject;

class NewsletterSection {
    use SmartObject;

    /** @var Newsletter */
    private $newsletter;

    private $definition;
    private $contentIds;

    public function __construct(Newsletter $newsletter, array $def){
        $this->newsletter = $newsletter;
        $this->definition = $def;
    }

    public function setContentIds(array $ids){
        $this->contentIds = $ids;
    }

}
