<?php

namespace App\Command;

use App\Cart;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class UpdateCommand extends Cart
{
    protected function configure()
    {
        $this->setName('update')
            ->setDescription('update a product in the cart')
            ->setHelp('This command updates a product in the cart');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $this->updateCart($input, $output);
        
        return 0;
    }
}