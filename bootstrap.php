<?php

use Nette\Configurator;

require_once __DIR__ . '/vendor/autoload.php';

$configurator = new Configurator;

foreach(['application', 'database', 'forms', 'http', 'routing', 'security', 'session'] as $key){
    unset($configurator->defaultExtensions[$key]);
}

$configurator->setTimeZone('UTC');

$configurator->setDebugMode(true);
$configurator->enableTracy(__DIR__ . '/log');
$configurator->setTempDirectory(__DIR__ . '/tmp');

$configurator->addParameters([
    'templateDir' => __DIR__ . '/templates',
    'outputDir' => __DIR__ . '/mail-queue'
]);

$configurator->createRobotLoader()
             ->addDirectory(__DIR__)
             ->register();

$configurator->addConfig(__DIR__ . '/config/common.neon');
$configurator->addConfig(__DIR__ . '/config/local.neon');

$container = $configurator->createContainer();

return $container;
