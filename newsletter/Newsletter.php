<?php

namespace Newsletter;

use Neevo\Literal;
use Neevo\NeevoException;
use Neevo\Row;
use Nette\SmartObject;

/**
 * @property Row $user
 * @property Row $site
 * @property string $unsubscribeLink
 * @property string $frequency
 */
class Newsletter implements \IteratorAggregate{
    use SmartObject;

    const TABLE = 'newsletters';
    const TABLE_SECTION = 'newsletter_sections';

    /** @var Model */
    public $model;

    public $id;
    public $userActive;

    /** @var Row */
    private $user;

    /** @var Row */
    private $site;

    private $unsubscribeLink;
    private $frequency;

    /** @var NewsletterSection[] */
    private $sections = [];

    public function __construct(Model $model){
        $this->model = $model;
    }

    public function getSite(){
        return $this->site;
    }

    public function setSite(Row $site){
        $this->site = $site;
        return $this;
    }

    public function getUser(){
        return $this->user;
    }

    public function setUser(Row $user, $userActive){
        $this->user = $user;
        $this->userActive = $userActive;
        return $this;
    }

    public function getUnsubscribeLink(){
        return $this->unsubscribeLink;
    }

    public function setUnsubscribeLink($link){
        $this->unsubscribeLink = $link;
        return $this;
    }

    public function getFrequency(){
        return $this->frequency == 'd' ? 'Daily' : 'Weekly';
    }

    public function setFrequency($freq){
        $this->frequency = $freq;
        return $this;
    }

    public function addSection($section, $custom = false){
        if($custom){
            $prefix = ucfirst($section['content_type']);
            $class_name = "Newsletter\\".$prefix."NewsletterSection";
        } else{
            $class_name = "Newsletter\\NewsletterSection";
        }
        $new = new $class_name($this, $section);
        $this->sections[] = $new;
        return $new;
    }

    public function getIterator(){
        return new \ArrayIterator($this->sections);
    }

    public function populateContent(){
        foreach($this->sections as $section){
            if($section instanceof CustomNewsletterSection){
                $section->populate($this->model);
            } else{
                foreach($section->getContentIds() as $id){
                    $section->addContent($this->model->{$section->content_type . 's'}->get($id));
                }
            }
        }
    }

    public function persist(){
        $db = $this->model->getDatabase();

        $db->begin();
        try{
            $newsletter_id = $db->insert(static::TABLE, [
                'user_id' => $this->user->id,
                'created_at' => new Literal('NOW()'),
                'updated_at' => new Literal('NOW()'),
            ])->insertId();

            $this->id = $newsletter_id;

            foreach($this->sections as $sec){
                $sec->id = $db->insert(static::TABLE_SECTION, [
                    'newsletter_id' => $newsletter_id,
                    'name' => $sec->name,
                    'content_type' => $sec->content_type,
                    'description' => $sec->description,
                    'content_ids' => "{" . join(',', $sec->getContentIds()) . "}",
                    'created_at' => new Literal('NOW()'),
                    'updated_at' => new Literal('NOW()'),
                ])->insertId();
            }
        } catch(NeevoException $e){
            $db->rollback();
            throw $e;
        }

        $db->commit();
    }

    public function getContentIds(){
        $ids = [];
        foreach($this->sections as $section){
            $key = $section->content_type;
            $secIds = $section->getContentIds();
            $ids[$key] = isset($ids[$key]) ? array_merge($ids[$key], $secIds) : $secIds;
        }
        return $ids;
    }

}
