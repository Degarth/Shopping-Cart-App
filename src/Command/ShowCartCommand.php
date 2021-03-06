<?php

namespace App\Command;

use App\Cart;
use Symfony\Component\Console\Helper\Table;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class ShowCartCommand extends Cart
{
    protected function configure()
    {
        $this->setName('cart')
            ->setDescription('Shows a table of products')
            ->setHelp('This command prints a table of products');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $this->showCart($input, $output);
        
        return 0;
    }
}