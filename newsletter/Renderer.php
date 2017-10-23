<?php

namespace Newsletter;

use Kdyby\Monolog\Logger as KdybyLogger;
use Latte\Engine;
use Nette\Bridges\ApplicationLatte\ILatteFactory;
use Psr\Log\LoggerInterface;

class Renderer{

    /** @var Engine */
    private $latte;

    /** @var string */
    private $templateDir;

    /** @var LoggerInterface */
    private $logger;


    public function __construct(string $templateDir, ILatteFactory $latteFactory, LoggerInterface $logger){
        $this->templateDir = $templateDir;
        $this->latte = $latteFactory->create();
        $this->logger = $logger instanceof KdybyLogger ? $logger->channel('renderer') : $logger;
    }

    public function renderNewsletter(Newsletter $newsletter){
        return '';
    }
}
