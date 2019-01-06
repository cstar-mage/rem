<?php
namespace ModernRetail\OrderExporter\Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class Run extends Command
{

    protected $_orderExporterCron;

	public function __construct(
        \ModernRetail\OrderExporter\Model\Cron $orderExporterCron
    ){
        $this->_orderExporterCron = $orderExporterCron;
		parent::__construct();
	}
	
    protected function configure() 
    {
        $this->setName('order_export');
        $this->setDescription('Run cron order export');
        parent::configure();
    }
	
	
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $output->writeln('Start!');
        $this->_orderExporterCron->run();
        $output->writeln('Finish!');
    }
    
}