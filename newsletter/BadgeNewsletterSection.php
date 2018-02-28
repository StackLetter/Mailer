<?php

namespace Newsletter;

use Neevo\Row;
use Nette\SmartObject;

class BadgeNewsletterSection extends NewsletterSection implements CustomNewsletterSection {

    private $settings;
    private $newBadges = [];
    private $congrats = [];

    public function getContentIds(){
        return array_merge($this->settings['new_badges'], $this->settings['congratulations']);
    }

    public function setContentIds($settings){
        $this->settings = $settings;
    }

    public function populate(Model $model){
        foreach($this->settings['new_badges'] as $id){
            $this->newBadges[] = $model->badges->get($id);
        }
        foreach($this->settings['congratulations'] as $id){
            $this->congrats[] = $model->badges->get($id);
        }
    }

    public function getIterator(){
        return new \ArrayIterator([(object) [
            'new' => $this->newBadges,
            'congrats' => $this->congrats
        ]]);
    }


}
