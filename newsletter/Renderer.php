<?php

namespace Newsletter;

use Kdyby\Monolog\Logger as KdybyLogger;
use Latte\Engine;
use Nette\Bridges\ApplicationLatte\ILatteFactory;
use Nette\Http\Url;
use Psr\Log\LoggerInterface;

class Renderer{

    /** @var Engine */
    private $latte;

    /** @var string */
    private $templateDir;

    /** @var string */
    private $outputDir;

    /** @var LoggerInterface */
    private $logger;

    /** @var Newsletter */
    private $newsletter;


    public function __construct(string $templateDir, string $tempDir, ILatteFactory $latteFactory, LoggerInterface $logger){
        $this->templateDir = $templateDir;
        $this->outputDir = $tempDir;
        $this->latte = $latteFactory->create();
        $this->registerFilters();
        $this->logger = $logger instanceof KdybyLogger ? $logger->channel('renderer') : $logger;
    }

    public function renderNewsletter(Newsletter $newsletter){
        $this->newsletter = $newsletter;
        $html = $this->latte->renderToString($this->templateDir . '/newsletter.latte', ['newsletter' => $newsletter]);
        $filename = sprintf("%s/%s_newsletter_%d_%s.html", $this->outputDir, date('Y-m-d/H-i-s'), $newsletter->id, substr(sha1($html), 0, 10));
        if(!file_exists(dirname($filename))){
            mkdir(dirname($filename), 0777, true);
        }
        file_put_contents($filename, $html);
        return $filename;
    }

    private function registerFilters(){
        $this->latte->addFilter(null, [$this, 'filterHandler']);
    }

    public function filterHandler($filter, $value){
        $method = 'filter' . ucfirst($filter);
        if(method_exists($this, $method)){
            $args = func_get_args();
            array_shift($args);
            return call_user_func_array([$this, $method], $args);
        }
        return $value;
    }

    public function filterUrl($entity, $type, $evaluation = true){
        $site = $this->newsletter->site;
        $baseUrl = rtrim($site->url, '/');

        // TODO: add more entity types (badge, user_badge, comment)
        switch($type){
            case 'question':
                $res = "$baseUrl/q/$entity->external_id"; break;
            case 'answer':
                $res = "$baseUrl/a/$entity->external_id"; break;
            case 'user':
                $res = "$baseUrl/u/$entity->external_id"; break;
            case 'tag':
                $res = "$baseUrl/questions/tagged/$entity->name"; break;
            case 'badge':
                $res = "$baseUrl/help/badges/$entity->external_id"; break;
            default:
                $res = '#';
        }
        if($evaluation && $res !== '#'){
            $res = $this->constructEvaluationUrl('click', $type, $res, null, $entity->id);
        }
        return $res;
    }

    public function filterUnsubscribeUrl($link){
        return $this->constructEvaluationUrl('unsubscribe', 'newsletter', $link);
    }

    public function filterFeedbackUrl($entity, $type, $value){
        $redirectUrl = 'https://www.stackletter.com/subscription/thankyou';
        return $this->constructEvaluationUrl('feedback', $type, $redirectUrl, $value, $entity->id);
    }

    public function filterTrackingPixel($newsletter){
        $redirectUrl = 'https://www.stackletter.com/assets/1x1.gif';
        return $this->constructEvaluationUrl('open', 'newsletter', $redirectUrl);
    }

    private function constructEvaluationUrl($evalType, $contentType, $redirect, $evalDetail = null, $contentDetail = null){
        $url = new Url('https://api.stackletter.com/evaluation/');
        $url->appendQuery([
            'newsletter_id' => $this->newsletter->id,
            'user_response_type' => $evalType,
            'content_type' => $contentType,
            'redirect_to' => $redirect,
            'event_identifier' => uniqid('event_', true)
        ]);
        if($evalDetail !== null){
            $url->appendQuery(['user_response_detail' => $evalDetail]);
        }
        if($contentDetail !== null){
            $url->appendQuery(['content_detail' => $contentDetail]);
        }

        return (string) $url;
    }
}
