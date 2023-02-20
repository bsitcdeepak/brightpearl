<?php
namespace Bsitc\Brightpearl\Console;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class ExportProductFromBp extends Command
{

    protected function configure()
    {

        $this->setName('bsitc:product:fetchdata');
        $this->setDescription('Fetch product data from Brightpearl at initial setup!');
        parent::configure();
    }


    protected function execute(InputInterface $input, OutputInterface $output)
    {

            /*Create Object manager and runs commands*/
            $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
            $product_uri = $objectManager->create('\Bsitc\Brightpearl\Model\ProducturiFactory');
            $product = $objectManager->create('\Bsitc\Brightpearl\Model\BpproductsFactory');
            $pricelist = $objectManager->create('\Bsitc\Brightpearl\Model\ProducturiFactory');

            $output->writeln($product_uri->setProducturiApi(). "Products uri are fetched - Completed!!!");
            $output->writeln($product->syncBpProductsByrangeApi(). "Fetch Products from Brightpearl by Range - Completed!!!");
            $output->writeln($pricelist->getPricelistByProductApi(). "Fetch Pricelist from brightpearls  -Completed!!!");
    }
}
