<?php

namespace Newsletter;

use Kdyby\Monolog\Logger as KdybyLogger;
use Latte;
use Neevo\Row;
use Nette\SmartObject;
use Newsletter\Utils\Api\ApiHelper;
use Newsletter\Utils\Redis\RedisQueue;
use Psr\Log\LoggerInterface;

class Builder{
    use SmartObject;

    protected $frequency;
    protected $model;

    public function __construct(Model $model){
        $this->model = $model;
    }

    /** @var LoggerInterface */
    protected $logger;

    /** @var RedisQueue */
    protected $queue;

    /** @var ApiHelper */
    protected $api;

    /** @var Renderer */
    protected $renderer;

    public function setLogger(LoggerInterface $logger){
        $this->logger = $logger instanceof KdybyLogger ? $logger->channel('builder') : $logger;
    }

    public function setRedisQueue(RedisQueue $queue){
        $this->queue = $queue;
    }

    public function setApiHelper(ApiHelper $api){
        $this->api = $api;
    }

    public function setRenderer(Renderer $renderer){
        $this->renderer = $renderer;
    }

    public function setFrequency($frequency){
        $this->frequency = $frequency;
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
        $this->logger->info("Building newsletter for '$user->display_name' (site: $user->site_id)", ['user_id' => $user->id]);
        $site = $this->model->sites->get($user->site_id);

        // Fetch newsletter structure
        $this->logger->debug("Fetching newsletter structure");
        $structure = $this->api->getNewsletterStructure($user->id, $this->frequency);

        // Create newsletter
        $newsletter = new Newsletter($this->model);
        $newsletter
            ->setSite($site)
            ->setUser($user)
            ->setFrequency($this->frequency)
            ->setUnsubscribeLink($this->api->getUnsubscribeLink($user->id));

        // Fetch and add newsletter sections
        foreach($structure as $section){
            $this->logger->debug("Fetching newsletter section '$section[name]'");
            $contentIds = $this->api->getSectionContent($section, $user->id, $this->frequency, $newsletter->getContentIds());
            $newsletter->addSection($section)->setContentIds($contentIds);
        }

        // Populate newsletter sections with content from DB
        $this->logger->info("Populating newsletter content", ['user_id' => $user->id]);
        $newsletter->populateContent();

        // Persist newsletter in DB
        $this->logger->debug("Persisting newsletter");
        $newsletter->persist();

        // Render newsletter HTML
        $file = $this->renderer->renderNewsletter($newsletter);

        $this->logger->info("Rendered to: $file");

        // Add to mail queue
        $this->queue->enqueue([
            'job' => 'stackletter.mail.newsletter',
            'params' => [
                'newsletter_id' => $newsletter->id,
                'email' => $user->email,
                'unsubscribe' => $newsletter->unsubscribeLink,
                'file' => $file,
            ],
        ]);
        $this->logger->debug("Mail queued", ['user_id' => $user->id]);
    }
}
