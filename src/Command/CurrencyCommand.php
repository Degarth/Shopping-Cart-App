<?php

namespace App\Command;

use App\Cart;
use Symfony\Component\Console\Question\Question;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class CurrencyCommand extends Cart
{
    protected function configure()
    {
        $this->setName('currency')
            ->setDescription('Change cart\'s currency')
            ->setHelp('This command changes the currency of the cart')
            ->addArgument('currency', InputArgument::REQUIRED, 'New currency.');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $this->changeCurrency($input, $output);
        
        return 0;
    }
}