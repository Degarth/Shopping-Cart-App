/** console.php **/
#!usr/bin/env php
<?php

require_once __DIR__ . '/vendor/autoload.php';

use Symfony\Component\Console\Application;
use App\Command\ShowCommand;
use App\Command\AddCommand;

$app = new Application();
$app->add(new ShowCommand());
$app->add(new AddCommand());
$app->run();