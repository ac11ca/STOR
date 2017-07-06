<?php
namespace NRAC\InventoryBundle\Command;

use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use NRAC\ComponentBundle\Entity\Note;
use PhpOffice\PhpWord\PhpWord as PHPWord;
use PhpOffice\PhpWord\IOFactory as PHPWord_IOFactory;
use NRAC\InventoryBundle\Entity\Inventory;
use NRAC\InventoryBundle\Entity\Product;
use NRAC\InventoryBundle\Entity\Manufacturer;
use NRAC\InventoryBundle\Entity\ProductCode;
use Doctrine\Common\Collections\ArrayCollection;
use NRAC\InventoryBundle\Classes\InventoryMatch;

class ImportCommand extends ContainerAwareCommand
{
	protected $file;
	protected $email;
	protected $settings;
	protected $Entity;
	protected $repository;
	protected $input;
	protected $output;

    protected function configure()
    {
        $this
            ->setName('entity:import')
            ->setDescription('Bulk entity import')
            ->addArgument(
                'file',
                InputArgument::REQUIRED,
                'The excel sheet containing entity data'
            )
            ->addArgument(
                'email',
                InputArgument::REQUIRED,
                'The email of the person to notify when completed'
            )
            ->addArgument(
                'entity',
                InputArgument::REQUIRED,
                'The entity class name and namespace'
            )
			->addArgument(
				'repository',
				InputArgument::REQUIRED,
				'The repository name'
			)
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
		$this->input = $input;
		$this->output = $output;

		try
		{
			$this->loadSettings();
			$this->loadArguments();
			$this->validateInputs();
			$this->coreCommand();
        }
        catch(\Exception $ex)
        {
			$this->handleException($ex);
		}       

        $output->writeln('--------');
        $output->writeln('Completed');

    }

	protected function validateInputs()
	{
		if(!file_exists($this->file)) {
			throw new \Exception('Entity data file not found.'); 
		}

		if(!(stristr($this->email, '@') > -1)) { 
			throw new \Exception('Invalid contact email provided.'); 
		}

		if(empty($this->Entity)) {
			throw new \Exception('Entity must be specified'); 
		}
	} 

	protected function loadSettings()
	{

	}

	protected function loadArguments()
	{
        $this->file = $input->getArgument('file');
        $this->email = $input->getArgument('email');
        $this->Entity = $input->getArgument('entity');
		$this->repository = $input->getArgument('repository');
	}    


	protected function coreCommand()
	{
		$this->loadSettings();
		$this->loadArguments();
		$this->validateInputs();
		$this->coreCommand();

		$context = $this->getContainer()->get('router')->getContext();

		$output->writeln('Importing Bulk Entities');
		$output->writeln('--------');

		$objPHPExcel = \PHPExcel_IOFactory::load($file);
		$Doctrine = $this->getContainer()->get('doctrine');
		$EM = $Doctrine->getManager();
		$Repo = $Doctrine->getRepository($repository);       
		$Factory = $Repo->getFactory($Doctrine, $this->getContainer());

		foreach ($objPHPExcel->getWorksheetIterator() as $Worksheet)
		{
			$index = 1;
			foreach( $Worksheet->getRowIterator() as $Row)
			{                  
				if($index > 1) 
				{
					$cellIterator = $Row->getCellIterator();
					$cellIterator->setIterateOnlyExistingCells(false);
					$product_data = array();
					foreach ($cellIterator as $Cell) 
					{
						$cellIndex = $Cell->getColumn();                     
						$entity_data[$cellIndex] = $Cell->getCalculatedValue();
					}
				}            
			}

		}
		$message = $added_count . " records added, " . $update_count . " records updated.";

/*
		$sender = $this->getContainer()->getParameter('nrac_from_email');

		$EM->flush();  
		$message = \Swift_Message::newInstance()
			->setSubject('Bulk Inventory Import Completed Successfully')
			->setFrom($sender)
			->setTo(array($email))
			->setBody(
				$this->getContainer()->get('templating')->render(
					'NRACMessagingBundle:Emails:inventoryimport.html.twig',
					array(
						'message' => $message
					)
				),
				'text/html'
			);
*/          
		$output->writeln('Import of ' . $file . ' Completed Successfully. ' . $added_count . ' records added, ' . $update_count . ' records updated');

//               $numsent = $this->getContainer()->get('mailer')->send($message, $failure);
		unlink($file);
	}

	protected function handleException(\Exception $Ex)
	{
		$sender = $this->getContainer()->getParameter('nrac_from_email');

		$message = \Swift_Message::newInstance()
			->setSubject('Bulk Inventory Import Error')
			->setFrom($sender)
			->setTo(array($email))
			->setBody(
				$this->getContainer()->get('templating')->render(
					'NRACMessagingBundle:Emails:inventoryimport.html.twig',
					array(
						'message' => $ex->getMessage()
					)
				),
				'text/html'
			);
	
		$output->writeln($ex->getLine() . " " . $ex->getMessage());

		$numsent = $this->getContainer()->get('mailer')->send($message, $failure);

	}
}
