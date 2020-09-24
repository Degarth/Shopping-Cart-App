<?php

namespace App\Command;

use App\Cart;
use Symfony\Component\Console\Helper\Table;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class ShowCurrenciesCommand extends Cart
{
    protected function configure()
    {
        $this->setName('currencies')
            ->setDescription('Shows a table of supported currencies')
            ->setHelp('This command shows a table of supported currencies');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $this->showSupportedCurrencies($input, $output);
        
        return 0;
    }
}