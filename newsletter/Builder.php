<?php

namespace Newsletter;

use Kdyby\Monolog\Logger as KdybyLogger;
use Latte;
use Neevo\Row;
use Nette\Bridges\ApplicationLatte\ILatteFactory;
use Nette\SmartObject;
use Newsletter\Model\User;
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

    public function setFrequency($frequency){
        $this->frequency = $frequency;
        return $this;
    }

    public function setTemplateDir($dir){
        $this->templateDir = $dir;
        return $this;
    }


    protected function getUsers(){
        return $this->model->user->getRegistered($this->frequency);
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
        $this->logger->debug("Building newsletter for $user->display_name (site: $user->site_id)", ['user_id' => $user->id]);


    }
}
