#!/usr/bin/env php
<?php

/** @var Nette\DI\Container $container */
$container = require_once __DIR__ . '/../bootstrap.php';

// Create PID file
file_put_contents($container->parameters['tempDir'] . '/mailer.pid', getmypid() . "\n");

/** @var Newsletter\Mailer $mailer */
$mailer = $container->getByType(Newsletter\Mailer::class);

$mailer->run();
