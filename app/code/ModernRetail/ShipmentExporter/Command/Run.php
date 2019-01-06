<?php
namespace ModernRetail\ShipmentExporter\Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class Run extends Command
{

    protected $_shipExporterCron;

	public function __construct(
        \ModernRetail\ShipmentExporter\Model\Cron $shipExporterCron
    ){
        $this->_shipExporterCron = $shipExporterCron;
		parent::__construct();
	}
	
    protected function configure() 
    {
        $this->setName('ship_export');
        $this->setDescription('Run cron ship export');
        parent::configure();
    }

	
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $output->writeln('Start!');
        $this->_shipExporterCron->run();
        $output->writeln('Finish!');
    }
    
}