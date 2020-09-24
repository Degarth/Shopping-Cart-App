/** console.php **/
#!usr/bin/env php
<?php

require_once __DIR__ . '/vendor/autoload.php';

use Symfony\Component\Console\Application;
use App\Command\ShowCartCommand;
use App\Command\AddProductCommand;
use App\Command\RemoveProductCommand;
use App\Command\UpdateProductCommand;
use App\Command\AddCurrencyCommand;
use App\Command\ShowCurrenciesCommand;

$app = new Application();
$app->add(new ShowCartCommand());
$app->add(new AddProductCommand());
$app->add(new RemoveProductCommand());
$app->add(new UpdateProductCommand());
$app->add(new AddCurrencyCommand());
$app->add(new ShowCurrenciesCommand());
$app->run();