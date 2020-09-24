<?php

namespace App\Command;

use App\Cart;
use Symfony\Component\Console\Question\Question;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class AddCurrencyCommand extends Cart
{
    protected function configure()
    {
        $this->setName('new-currency')
            ->setDescription('Change cart\'s currency')
            ->setHelp('This command changes the currency of the cart')
            ->addArgument('currency', InputArgument::REQUIRED, 'New currency.')
            ->addArgument('rate', InputArgument::REQUIRED, 'Exchange ratecu.');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $this->addCurrency($input, $output);
        
        return 0;
    }
}