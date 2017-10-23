#!/usr/bin/env php
<?php

/** @var Nette\DI\Container $container */
$container = require_once __DIR__ . '/../bootstrap.php';

/** @var Newsletter\Builder $builder */
$builder = $container->getByType(Newsletter\Builder::class);

$frequency = 'w';

$builder
    ->setFrequency($frequency)
    ->build();
