<?php

namespace Newsletter;

use Neevo\Literal;
use Neevo\NeevoException;
use Neevo\Row;
use Nette\SmartObject;

/**
 * @property Row $user
 * @property string $unsubscribeLink
 */
class Newsletter{
    use SmartObject;

    const TABLE = 'newsletters';
    const SECTION_TABLE = 'newsletter_sections';

    /** @var Model */
    public $model;

    /** @var Row */
    private $user;

    /** @var string */
    private $unsubscribeLink;

    /** @var NewsletterSection[] */
    private $sections = [];

    public function __construct(Model $model){
        $this->model = $model;
    }

    public function getUser(){
        return $this->user;
    }

    public function setUser(Row $user){
        $this->user = $user;
        return $this;
    }

    public function getUnsubscribeLink(){
        return $this->unsubscribeLink;
    }

    public function setUnsubscribeLink($link){
        return $this->unsubscribeLink = $link;
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
                $section->addContent($this->model->{$section->content_type . 's'}->get($id));
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

            foreach($this->sections as $sec){
                $db->insert(static::SECTION_TABLE, [
                    'newsletter_id' => $newsletter_id,
                    'name' => $sec->name,
                    'content_type' => $sec->content_type,
                    'description' => $sec->description,
                    'content_ids' => "{" . join(',', $sec->getContentIds()) . "}",
                    'created_at' => new Literal('NOW()'),
                    'updated_at' => new Literal('NOW()'),
                ])->run();
            }
        } catch(NeevoException $e){
            $db->rollback();
            throw $e;
        }

        $db->commit();
    }

}
