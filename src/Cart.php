<?php 

namespace App;

use Symfony\Component\Console\Command\Command as SymfonyCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Helper\Table;

class Cart extends SymfonyCommand
{
    public function __construct()
    {
        parent::__construct();
    }
    protected function showProducts(InputInterface $input, OutputInterface $output)
    {
        $products = $this->getProducts();
        $table = new Table($output);
        
        $table->setHeaderTitle('Products')
            ->setHeaders(['ID', 'Name', 'Quantity', 'Price', 'Currency'])
            ->setRows($products);

          $table->render();
          //print_r($this->getProducts());
    }
    private function getProducts()
    {
        $data = file_get_contents('Files/products.txt');
        $data = str_replace(array("\n", "\r"), '', $data);
        $data = explode(':', $data);

        unset($data[count($data)-1]);

        $final_array = array();
        foreach($data as $row)
        {
            $final_array[] = explode(';', $row);
        }

        return $final_array;

    }
    protected function addToCart(InputInterface $input, OutputInterface $output)
    {
        $data = $this->getProducts();
        $sum = $this->totalSum(); 

        foreach ($data as $id)
        {
            foreach(range(0, count($id)-1) as $i)
                $output->write($id[$i].' ');
            $output->writeln(['']); 
        }   
        $output->writeln(['']); 
        $output->write('Balance: '.$sum);
    }
    private function totalSum()
    {
        $data = $this->getProducts();
        $sum = 0;

        foreach ($data as $id)
            if((int)$id[2] > 0)
                $sum += ((double)$id[3]*(int)$id[2]);

        return $sum;
    }
}