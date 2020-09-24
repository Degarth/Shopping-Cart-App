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

        $this->productTable($output);  //Outputs product table in a nice way

        $output->writeln(['Total: '.$this->getBalance()]);
    }

    // Read file and put products into array
    private function getProducts()
    {
        $data = file_get_contents('src/Files/data.txt');
        $data = preg_split('/\n|\r\n?/', $data); //explode(PHP_EOL, $data);
        
        unset($data[count($data)-1]);

        $product_array = array();

        foreach($data as $row)
            $product_array[] = explode(';', $row);

        return $product_array;
    }
    
    // Calculate cart's total 
    private function getBalance()
    {
        $products = $this->getProducts();
        $quantity = array_column($products, 2);          // Quantity array
        $price = array_column($products, 3);             // Price array
        $item_currency = array_column($products, 4);     // Currency array
        
        $supported_currencies = $this->getCurrencies();
        $supp_currency = array_column($supported_currencies, 0);    // Currency name array
        $rate = array_column($supported_currencies, 1);             // Currency exchange rate
        
        $sum = 0;

        for($i = 0; $i < count($products); $i++) 
            if((int)$quantity[$i] > 0)
            {
                $quant_price = (int)$quantity[$i]*(double)$price[$i];   // Product price

                for($j = 0; $j < count($supported_currencies); $j++)
                    if($item_currency[$i] == $supp_currency[$j])             
                        $sum += $quant_price/$rate[$j];            // Product price after exchange to EUR
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

        // ID input
        $id_question = new Question("Product id: ", "missing"); 
        $id_question->setValidator(function ($answer) {
            $ids = array_column($this->getProducts(), 0);
            if (in_array($answer, $ids)) {
                throw new \RuntimeException(
                    'This ID is used by another product!'
                );
            }
            return $answer;
        }); 
        $id_question->setMaxAttempts(3);
        $id = $helper->ask($input, $output, $id_question);

        // Name input
        $name_question = new Question("Product name: ", "missing"); 
        $name = $helper->ask($input, $output, $name_question);

        // Quantity input
        $quantity_question = new Question("Product quantity: ", "missing"); 
        $quantity_question->setValidator(function ($answer) {
            if (!is_numeric($answer) || $answer <= 0 || !preg_match('/^[0-9]+$/', $answer)) {
                throw new \RuntimeException(
                    'The price must a natural number above 0!'
                );
            }
            return $answer;
        }); 
        $quantity_question->setMaxAttempts(3);
        $quantity = $helper->ask($input, $output, $quantity_question);

        // Price input
        $price_question = new Question("Product price (bigger than 0): ", "missing"); 
        $price_question->setValidator(function ($answer) {
            if (!is_numeric($answer) || $answer <= 0) {
                throw new \RuntimeException(
                    'The price must a number above 0!'
                );
            }
            return $answer;
        }); 
        $price_question->setMaxAttempts(3);
        $price = $helper->ask($input, $output, $price_question);

        // Currency input      
        $currency_question = new Question("Product currency (".$this->getSupportedCurrencyNames()."): ", "missing");
        $currency_question->setValidator(function ($answer) {
            $supp_currencies = array_column($this->getCurrencies(), 0);
            if (!in_array($answer, $supp_currencies)) {
                throw new \RuntimeException(
                    'The currency you entered is not supported! Supported currencies - '.$this->getSupportedCurrencyNames().''
                );
            }
            return $answer;
        }); 
        $currency_question->setMaxAttempts(3);
        $currency = $helper->ask($input, $output, $currency_question);

        $new_product = array('id'=>$id, 'name'=>$name, 'quantity'=>$quantity, 'price'=>sprintf('%0.2f', $price), 'currency'=>$currency);
        $this->saveProduct($new_product);   // Save product to file
        $this->productTable($output);      // Show all products, new product included
        
        $output->writeln(['Product has been added to your cart!', '', 'Total: '.$this->getBalance()]);
    }

    // Save product to file
    private function saveProduct($new_product)
    {
        $data = file_get_contents('src/Files/data.txt');

        $new_entry = $new_product['id'].';'.$new_product['name'].';'.
                     $new_product['quantity'].';'.$new_product['price'].';'.$new_product['currency'].PHP_EOL;

        $data .= $new_entry;
        
        file_put_contents('src/Files/data.txt', $data);
    }
    
    // Remove a product from a cart by id and change the quantity to -1
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
                if($ids[$i] == $id_to_delete) {
                    $quantity[$i] = -1;
                    $price[$i] = '';
                    $currency[$i] = '';
                }
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

    // Update a product in the cart by changing the quantity
    protected function updateCart(InputInterface $input, OutputInterface $output)
    {
        $products = $this->getProducts();
        $name = array_column($products, 1);
        $ids = array_column($products, 0);
        $quantity = array_column($products, 2);
        $price = array_column($products, 3);
        $currency = array_column($products, 4);

        $this->productTable($output); //++

        // ID input
        $helper = $this->getHelper('question');
        $id_question = new Question("Enter ID of a product you want to update: ", "error"); 
        $id_to_update = $helper->ask($input, $output, $id_question);
        
        if(in_array($id_to_update, $ids))   //if ID exists
        {
            $quantity_question = new Question("Enter new quantity: ", "error"); 
            $quantity_question->setValidator(function ($answer) {
                if (!is_numeric($answer) || $answer <= 0 || !preg_match('/^[0-9]+$/', $answer)) {
                    throw new \RuntimeException(
                        'The price must a natural number above 0!'
                    );
                }
                return $answer;
            }); 
            $quantity_question->setMaxAttempts(3);
            $quantity_to_update = $helper->ask($input, $output, $quantity_question);
            
            if($quantity_to_update > 0)
            {
                $data = '';
                for($i = 0; $i < count($products); $i++) {
                    if($ids[$i] == $id_to_update)
                        $quantity[$i] = $quantity_to_update;
                    $data .= $ids[$i].';'.$name[$i].';'.$quantity[$i].';'.$price[$i].';'.$currency[$i].PHP_EOL;
                    
                }
            
                file_put_contents('src/Files/data.txt', $data);    // Put new product string to text file
                
                $this->productTable($output);  // Show a table of all products

                $message = sprintf("Product '%s' has been updated!", $id_to_update);
            }
            else
                $message = sprintf("Entered quantity must be 1 or more!", $id_to_update);

        }
        else
            $message = sprintf("Product with ID '%s' not found!", $id_to_update);

        $output->writeln([$message, '', 'Total: '.$this->getBalance()]);
    }

    // Reads file of supported currencies into array
    private function getCurrencies()
    {
        $data = file_get_contents('src/Files/supported_currencies.txt');
        
        $data = preg_split('/\n|\r\n?/', $data);  //explode(PHP_EOL, $data);
        unset($data[count($data)-1]);

        $currency_array = array();
        foreach($data as $row)
        $currency_array[] = explode(';', $row);

        return $currency_array;
    }

    // Add a new currency to supported currencies
    protected function addCurrency(InputInterface $input, OutputInterface $output)
    {
        $currency = $input->getArgument('currency');
        $rate = $input->getArgument('rate');
        
        $new_currency = array('currency'=>$currency, 'rate'=>$rate);
        
        $this->saveCurrency($new_currency);

        $message = sprintf("%s is now a supported currency!", $currency);
        $output->writeln($message);
    }

    // Save new currency to text file
    private function saveCurrency($new_currency)
    {
        $data = file_get_contents('src/Files/supported_currencies.txt');

        $new_entry = $new_currency['currency'].';'.$new_currency['rate'].PHP_EOL;
        $data .= $new_entry;
        
        file_put_contents('src/Files/supported_currencies.txt', $data);
    }

    // Outputs a table of supported currencies
    protected function showSupportedCurrencies(InputInterface $input, OutputInterface $output)
    {
        $currencies = $this->getCurrencies();
        
        $table = new Table($output);
        $table->setHeaders(['Currency', 'Exchange Rate'])->setRows($currencies);

        return $table->render();
    }
    private function getSupportedCurrencyNames()
    {
        $supp_currencies = array_column($this->getCurrencies(), 0);
        $supp_string = '';
        foreach($supp_currencies as $s => $name) {
            if ($s === array_key_last($supp_currencies))
                $supp_string .= $name;
            else
                $supp_string .= $name.', ';
        }
        return $supp_string;
    }
}