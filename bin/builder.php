#!/usr/bin/env php
<?php

if(!isset($argv[1]) || ($argv[1] !== 'daily' && $argv[1] !== 'weekly')){
    echo "Usage:\n\t$argv[0] [daily|weekly]\n";
    exit(1);
}
$frequency = $argv[1][0];


/** @var Nette\DI\Container $container */
$container = require_once __DIR__ . '/../bootstrap.php';

/** @var Newsletter\Builder $builder */
$builder = $container->getByType(Newsletter\Builder::class);

$builder
    ->setFrequency($frequency)
    ->build();
