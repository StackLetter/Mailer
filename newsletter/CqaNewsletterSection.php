<?php

namespace Newsletter;

use Neevo\Row;
use Nette\SmartObject;

class CqaNewsletterSection extends NewsletterSection implements CustomNewsletterSection {

    public function getContentIds(){
        return array_values($this->content[0]);
    }

    public function setContentIds($values){
        $this->content = [$values];
    }

    public function populate(Model $model){
        // pass
    }


}
