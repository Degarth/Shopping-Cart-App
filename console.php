/** console.php **/
#!usr/bin/env php
<?php

require_once __DIR__ . '/vendor/autoload.php';

use Symfony\Component\Console\Application;
use App\Command\ShowCommand;
use App\Command\AddCommand;
use App\Command\RemoveCommand;
use App\Command\UpdateCommand;
use App\Command\CurrencyCommand;

$app = new Application();
$app->add(new ShowCommand());
$app->add(new AddCommand());
$app->add(new RemoveCommand());
$app->add(new UpdateCommand());
$app->add(new CurrencyCommand());
$app->run();