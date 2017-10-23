<?php

namespace Newsletter;

use Kdyby\Monolog\Logger as KdybyLogger;
use Latte;
use Neevo\Row;
use Nette\Bridges\ApplicationLatte\ILatteFactory;
use Nette\SmartObject;
use Newsletter\Model\Users;
use Newsletter\Utils\Api\ApiHelper;
use Newsletter\Utils\Redis\RedisQueue;
use Psr\Log\LoggerInterface;

class Builder{
    use SmartObject;

    protected $frequency;
    protected $templateDir;
    protected $model;

    public function __construct(Model $model){
        $this->model = $model;
    }

    /** @var LoggerInterface */
    protected $logger;

    /** @var RedisQueue */
    protected $queue;

    /** @var Latte\Engine */
    protected $latte;

    /** @var ApiHelper */
    protected $api;

    public function setLogger(LoggerInterface $logger){
        if($logger instanceof KdybyLogger){
            $logger = $logger->channel('builder');
        }
        $this->logger = $logger;
    }

    public function setRedisQueue(RedisQueue $queue){
        $this->queue = $queue;
    }

    public function setLatteFactory(ILatteFactory $latte){
        $this->latte = $latte->create();
    }

    public function setApiHelper(ApiHelper $api){
        $this->api = $api;
    }

    public function setFrequency($frequency){
        $this->frequency = $frequency;
        return $this;
    }

    public function setTemplateDir($dir){
        $this->templateDir = $dir;
        return $this;
    }


    protected function getUsers(){
        return $this->model->users->getRegistered($this->frequency);
    }

    public function build(){
        $this->logger->debug("Running ". ($this->frequency == 'd' ? 'daily': 'weekly') . " newsletter job");

        foreach($this->getUsers() as $user){
            try{
                $this->buildNewsletter($user);
            } catch(BuilderException $e){
                $this->logger->error($e);
            }
        }
    }

    public function buildNewsletter(Row $user){
        $this->logger->debug("Building newsletter for '$user->display_name' (site: $user->site_id)", ['user_id' => $user->id]);

        // Fetch newsletter structure
        $structure = $this->api->getNewsletterStructure($user->id, $this->frequency);

        // Create newsletter
        $newsletter = new Newsletter($this->model);
        $newsletter->setUserId($user->id);

        // Fetch and add newsletter sections
        foreach($structure as $section){
            $contentIds = $this->api->getSectionContent($section, $user->id, $this->frequency);
            $newsletter->addSection($section)->setContentIds($contentIds);
        }

        // Populate newsletter sections with content from DB
        $this->logger->debug("Populating newsletter content", ['user_id' => $user->id]);
        $newsletter->populateContent();


        // TODO Persist newsletter in DB

        // TODO Render newsletter HTML

        // TODO Save HTML output

        // TODO Add to mail queue
    }
}
