<?php

namespace Newsletter;

interface CustomNewsletterSection extends \IteratorAggregate {
    public function getContentIds();

    public function setContentIds($ids);

    public function getDefinition();

    public function populate(Model $model);
}
