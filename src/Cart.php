<?php 

namespace App;

use Symfony\Component\Console\Command\Command as SymfonyCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Helper\Table;
use Symfony\Component\Console\Question\Question;

class Cart extends SymfonyCommand
{
    public function __construct()
    {
        parent::__construct();
    }
    // Shows cart and balance
    protected function showCart(InputInterface $input, OutputInterface $output)
    {
        $output->writeln([
            '',
            '====================**** CART ****====================',
        ]);

        $this->productTable($output);

        $output->writeln(['','Total: '.$this->getBalance()]);
    }
    // Get product array
    private function getProducts()
    {
        $data = file_get_contents('src/Files/products.txt');
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
    // Add item to cart
    protected function addToCart(InputInterface $input, OutputInterface $output)
    {
        $output->writeln(['', '======== Adding new product to cart ========','']);

        $helper = $this->getHelper('question');
        $id_question = new Question("Product id: ", "missing"); 
        $id = $helper->ask($input, $output, $id_question);

        $name_question = new Question("Product name: ", "missing"); 
        $name = $helper->ask($input, $output, $name_question);

        $quantity_question = new Question("Product quantity: ", "missing"); 
        $quantity = $helper->ask($input, $output, $quantity_question);

        $price_question = new Question("Product price: ", "missing"); 
        $price = $helper->ask($input, $output, $price_question);

        $currency_question = new Question("Product currency: ", "missing"); 
        $currency = $helper->ask($input, $output, $currency_question);

        $new_product = array('id'=>$id, 'name'=>$name, 'quantity'=>$quantity, 'price'=>$price, 'currency'=>$currency);
        $this->putProductToFile($new_product);

        $this->productTable($output);
        $output->writeln(['', 'Product has been added to your cart!', '']);
        $output->writeln(['Total: '.$this->getBalance()]);
    }
    private function putProductToFile($new_product)
    {
        $data = file_get_contents('src/Files/products.txt');

        $new_entry = $new_product['id'].';'.$new_product['name'].';'.
                     $new_product['quantity'].';'.$new_product['price'].';'.$new_product['currency'].':';

        $data .= $new_entry;
        
        file_put_contents('src/Files/products.txt', $data);
    }
    
    // Calculate cart's total
    public function getBalance()
    {
        $products = $this->getProducts();
        $quantity = array_column($products, 2);
        $price = array_column($products, 3);
        $currency = array_column($products, 4);
        
        $sum = 0;

        for($i = 0; $i < count($products); $i++) 
            if((int)$quantity[2] > 0)
            {
                $quant_price = (int)$quantity[$i]*(double)$price[$i];

                if($currency[$i] == "EUR")
                    $sum += $quant_price;         //
                elseif($currency[$i] == "USD")
                    $sum += $quant_price/1.14;     //BASED ON LETTER
                elseif($currency[$i] == "GBP")
                    $sum += $quant_price/0.88;   //
            }

            return round($sum, 2).' EUR';
    }
    //Function that returns cart's product table
    function productTable($output)
    {
        $products = $this->getProducts();
        
        $table = new Table($output);
        $table->setHeaders(['ID', 'Name', 'Quantity', 'Price', 'Currency'])->setRows($products);

        return $table->render();
    }
    // Removes a product by id
    protected function removeFromCart(InputInterface $input, OutputInterface $output)
    {
        $products = $this->getProducts();
        $ids = array_column($products, 0);
        $name = array_column($products, 1);
        $quantity = array_column($products, 2);
        $price = array_column($products, 3);
        $currency = array_column($products, 4);

        $this->productTable($output);
        
        $output->writeln(['']);

        $helper = $this->getHelper('question');
        $question = new Question("Enter ID of a product you want to delete: ", "missing"); 
        $id_to_delete = $helper->ask($input, $output, $question);

        if(in_array($id_to_delete, $ids))
        {
            $data = '';
            for($i = 0; $i < count($products); $i++) {
                if($ids[$i] == $id_to_delete)
                    $quantity[$i] = -1;
                $data .= PHP_EOL.$ids[$i].';'.$name[$i].';'.$quantity[$i].';'.$price[$i].';'.$currency[$i].':';
                
            }

            file_put_contents('src/Files/products.txt', $data);

            $this->productTable($output);
        
            $output->writeln(['']);
            $message = sprintf("Product '%s' has been removed from the cart!", $id_to_delete);
        }
        else
            $message = sprintf("Product with ID '%s' not found!", $choice);

        $output->writeln($message);
    }
    // Update a product in the cart
    protected function updateCart(InputInterface $input, OutputInterface $output)
    {
        $products = $this->getProducts();
        $quantity = array_column($products, 2);
        $price = array_column($products, 3);
        $currency = array_column($products, 4);

        $helper = $this->getHelper('question');
        $question = new Question("Enter ID of a product you want to update: ", "missing"); 
        $choice = $helper->ask($input, $output, $question);

        for($i = 0; $i < count($products); $i++) 
        {
        }


        $data = file_put_contents('src/Files/data.txt', );
        $output->write("Update...");
    }
}


























/*protected function changeCurrency(InputInterface $input, OutputInterface $output)
{
    /*$output->writeln([
        '-------------------',
        'Change currency to:',
        '-------------------',
        '1. EUR',
        '2. USD',
        '3. GBP',
        '-------------------',
    ]);

    $helper = $this->getHelper('question');
    $question = new Question("Change currency to: ", "1"); 

    $choice = $helper->ask($input, $output, $question);
    $output->writeln(['-------------------','']);
    $message = sprintf("Currency changed to %s!", $choice);

    $output->writeln($message);*
    $new_currency = $input->getArgument('currency');

    $this->setCurrency($new_currency);

    $output->write('Currency changed to '.$new_currency);
}*/