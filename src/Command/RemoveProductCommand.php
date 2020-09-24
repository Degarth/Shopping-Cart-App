<?php

namespace App\Command;

use App\Cart;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class RemoveProductCommand extends Cart
{
    protected function configure()
    {
        $this->setName('remove')
            ->setDescription('Remove a product from the cart')
            ->setHelp('This command removes a product from the cart');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $this->removeFromCart($input, $output);
        
        return 0;
    }
}