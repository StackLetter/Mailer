#!/usr/bin/env php
<?php

/** @var Nette\DI\Container $container */
$container = require_once __DIR__ . '/../bootstrap.php';

/** @var Newsletter\Mailer $mailer */
$mailer = $container->getByType(Newsletter\Mailer::class);

$mailer->run();
