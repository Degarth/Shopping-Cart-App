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
            '======================**** CART ****======================',
        ]);

        $this->productTable($output);

        $output->writeln(['Total: '.$this->getBalance()]);
    }
    // Get product array
    private function getProducts()
    {
        $data = file_get_contents('src/Files/data.txt');
        $data = explode(PHP_EOL, $data);   //preg_split('/\n|\r\n?/', $data);
        
        unset($data[count($data)-1]);

        $final_array = array();

        foreach($data as $row)
            $final_array[] = explode(';', $row);

        return $final_array;
    }
    private function getCurrencies()
    {
        $data = file_get_contents('src/Files/supported_currency.txt');
        
        $data = explode(PHP_EOL, $data);
        unset($data[count($data)-1]);

        $currency_array = array();

        foreach($data as $row)
        $currency_array[] = explode(';', $row);

        return $currency_array;
    }
    
    // Calculate cart's total
    private function getBalance()
    {
        $products = $this->getProducts();
        $quantity = array_column($products, 2);
        $price = array_column($products, 3);
        $item_currency = array_column($products, 4);
        
        $supported_currencies = $this->getCurrencies();
        $supp_currency = array_column($supported_currencies, 0);
        $rate = array_column($supported_currencies, 1);
        
        $sum = 0;

        for($i = 0; $i < count($products); $i++) 
            if((int)$quantity[$i] > 0)
            {
                $quant_price = (int)$quantity[$i]*(double)$price[$i];

                for($j = 0; $j < count($supported_currencies); $j++)
                    if($item_currency[$i] == $supp_currency[$j])
                        $sum += $quant_price/$rate[$j];
            }

        return round($sum, 2).' EUR';
    }
    
    //Returns cart's product table in a nice way
    function productTable($output)
    {
        $products = $this->getProducts();
        
        $table = new Table($output);
        $table->setHeaders(['ID', 'Name', 'Quantity', 'Price', 'Currency'])->setRows($products);

        return $output->writeln(['']).$table->render().$output->writeln(['']);
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

        $currency_question = new Question("Product currency (EUR, USD, GBP): ", "missing"); 
        $currency = $helper->ask($input, $output, $currency_question);

        $new_product = array('id'=>$id, 'name'=>$name, 'quantity'=>$quantity, 'price'=>$price, 'currency'=>$currency);
        
        $this->saveProduct($new_product);

        $this->productTable($output);
        
        $output->writeln(['Product has been added to your cart!', '']);
        $output->writeln(['Total: '.$this->getBalance()]);
    }
    private function saveProduct($new_product)
    {
        $data = file_get_contents('src/Files/data.txt');

        $new_entry = $new_product['id'].';'.$new_product['name'].';'.
                     $new_product['quantity'].';'.$new_product['price'].';'.$new_product['currency'].PHP_EOL;

        $data .= $new_entry;
        
        file_put_contents('src/Files/data.txt', $data);
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

        $helper = $this->getHelper('question');
        $question = new Question("Enter ID of a product you want to delete: ", "missing"); 
        $id_to_delete = $helper->ask($input, $output, $question);

        if(in_array($id_to_delete, $ids))
        {
            $data = '';
            for($i = 0; $i < count($products); $i++) {
                if($ids[$i] == $id_to_delete)
                    $quantity[$i] = -1;
                $data .= $ids[$i].';'.$name[$i].';'.$quantity[$i].';'.$price[$i].';'.$currency[$i].PHP_EOL;
                
            }

            file_put_contents('src/Files/data.txt', $data);

            $this->productTable($output);

            $message = sprintf("Product '%s' has been removed from the cart!", $id_to_delete);
        }
        else
            $message = sprintf("Product with ID '%s' not found!", $id_to_delete);

        $output->writeln($message);
    }
    // Update a product in the cart
    protected function updateCart(InputInterface $input, OutputInterface $output)
    {
        $products = $this->getProducts();
        $name = array_column($products, 1);
        $ids = array_column($products, 0);
        $quantity = array_column($products, 2);
        $price = array_column($products, 3);
        $currency = array_column($products, 4);

        $this->productTable($output); //++

        $helper = $this->getHelper('question');
        $id_question = new Question("Enter ID of a product you want to update: ", "error"); 
        $id_to_update = $helper->ask($input, $output, $id_question);
        
        if(in_array($id_to_update, $ids))
        {
            $quantity_question = new Question("Enter new quantity: ", "error"); 
            $quantity_to_update = $helper->ask($input, $output, $quantity_question);
            
            if($quantity_to_update > 0)
            {
                $data = '';
                for($i = 0; $i < count($products); $i++) {
                    if($ids[$i] == $id_to_update)
                        $quantity[$i] = $quantity_to_update;
                    $data .= $ids[$i].';'.$name[$i].';'.$quantity[$i].';'.$price[$i].';'.$currency[$i].PHP_EOL;
                    
                }
            
                file_put_contents('src/Files/data.txt', $data);
                
                $this->productTable($output);

                $message = sprintf("Product '%s' has been updated!", $id_to_update);
            }
            else
                $message = sprintf("Entered quantity must be 1 or more!", $id_to_update);

        }
        else
            $message = sprintf("Product with ID '%s' not found!", $id_to_update);

        $output->writeln($message);
        $output->writeln(['Total: '.$this->getBalance()]);
    }
    // Add a new currency to supported currencies
    protected function addCurrency(InputInterface $input, OutputInterface $output)
    {
        $currency = $input->getArgument('currency');
        $rate = $input->getArgument('rate');
        
        $new_currency = array('currency'=>$id, 'rate'=>$name);
        
        $this->saveCurrency($new_currency);

        $message = sprintf("%s is now a supported currency!", $currency);

        $output->writeln($message);
    }
    private function saveCurrency($new_currency)
    {
        $data = file_get_contents('src/Files/supported_currencies.txt');

        $new_entry = $new_currency['currency'].';'.$new_currency['rate'].PHP_EOL;

        $data .= $new_entry;
        
        file_put_contents('src/Files/supported_currencies.txt', $data);
    }
}