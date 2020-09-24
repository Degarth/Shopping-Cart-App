<?php

namespace App\Command;

use App\Cart;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class AddProductCommand extends Cart
{
    protected function configure()
    {
        $this->setName('add')
            ->setDescription('Add a product to the cart')
            ->setHelp('This command adds a product to the cart');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $this->addToCart($input, $output);
        
        return 0;
    }
}