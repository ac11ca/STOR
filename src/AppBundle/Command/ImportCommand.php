<?php
namespace AppBundle\Command;

use PhpOffice\PhpWord\PhpWord as PHPWord;
use PhpOffice\PhpWord\IOFactory as PHPWord_IOFactory;
use Doctrine\Common\Collections\ArrayCollection;
use Symfony\Component\Console\Input\InputArgument;

class ImportCommand extends ApplicationMasterCommand
{
	protected $file;
	protected $email;
	protected $Entity;
	protected $repository_name;
    protected $Repository;
    protected $Factory;
    protected $cell_mappings;

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
			);
    }

    protected function subExecute()
    {
        $this->coreCommand();
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


	protected function loadArguments()
	{
        $this->file = $this->input->getArgument('file');
        $this->email = $this->input->getArgument('email');
        $this->Entity = $this->input->getArgument('entity');
		$this->repository_name = $this->input->getArgument('repository');     
        $this->Repository = $this->Doctrine->getRepository($this->repository_name);
        $this->Factory = $this->Repository->getFactory($this->Doctrine, $this->getContainer());
        $this->cell_mappings = $this->Factory->getFieldKeys();
	}    


	protected function coreCommand()
	{
		$objPHPExcel = \PHPExcel_IOFactory::load($this->file);
        $row = null;
        $batch_size = empty($this->settings['importbatchsize']) ? 20 : $this->settings['importbatchsize'];
        $EntityManager = $this->Doctrine->getManager();

		foreach ($objPHPExcel->getWorksheetIterator() as $Worksheet)
		{
            $row = 0;
			foreach( $Worksheet->getRowIterator() as $Row)
			{         
                if($row > 0)  //Account for a header row                
                {
                    $row_data = $this->processRow($Row);
                    $Entity = $this->Factory->createEntityFromArray($row_data);
                    if(!empty($Entity))
                    {
                        $EntityManager->persist($Entity);
                        if($row % $batch_size == 0)
                            $EntityManager->flush();                    
                    }
                }

                $row++;
			}       
		}

        $EntityManager->flush();

		unlink($this->file);
	}

    protected function processRow($Row)
    {          
        $cellIterator = $Row->getCellIterator();
        $cellIterator->setIterateOnlyExistingCells(false);       
        $row_data = [];

        foreach ($cellIterator as $Cell) 
        {
            $cellIndex = \PHPExcel_Cell::columnIndexFromString($Cell->getColumn())-1;                         
            $row_data[$cellIndex]['value'] = $Cell->getCalculatedValue();
            $row_data[$cellIndex]['field'] = $this->cell_mappings[$cellIndex];  
        }    
        
        return $row_data;
    }

}
