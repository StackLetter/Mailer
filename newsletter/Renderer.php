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

    /** @var string */
    private $outputDir;

    /** @var LoggerInterface */
    private $logger;


    public function __construct(string $templateDir, string $tempDir, ILatteFactory $latteFactory, LoggerInterface $logger){
        $this->templateDir = $templateDir;
        $this->outputDir = $tempDir;
        $this->latte = $latteFactory->create();
        $this->logger = $logger instanceof KdybyLogger ? $logger->channel('renderer') : $logger;
    }

    public function renderNewsletter(Newsletter $newsletter){
        $html = $this->latte->renderToString($this->templateDir . '/newsletter.latte', ['newsletter' => $newsletter]);
        $filename = sprintf("%s/newsletter--%s--%s.html", $this->outputDir, date('Y-m-d--H-i-s'), substr(sha1($html), 0, 10));
        file_put_contents($filename, $html);
        return $filename;
    }
}
