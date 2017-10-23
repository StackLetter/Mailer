<?php

namespace Newsletter;

use Nette\SmartObject;

class Newsletter{
    use SmartObject;

    /** @var Model */
    public $model;

    private $user_id;

    /** @var NewsletterSection[] */
    private $sections = [];

    public function __construct(Model $model){
        $this->model = $model;
    }

    public function setUserId($user_id){
        $this->user_id = $user_id;
        return $this;
    }

    public function addSection($section){
        $new = new NewsletterSection($this, $section);
        $this->sections[] = $new;
        return $new;
    }

    public function populateContent(){
        foreach($this->sections as $section){
            foreach($section->getContentIds() as $id){
                $section->addContent($this->model->{$section->type . 's'}->get($id));
            }
        }
    }

}
