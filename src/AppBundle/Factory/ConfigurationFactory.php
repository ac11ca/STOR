<?php

namespace AppBundle\Factory;

use AppBundle\Entity\Configuration;

class ConfigurationFactory extends ApplicationMasterFactory
{ 
    protected $fieldKeys = ['label'];
    protected $EntityType = 'AppBundle\Entity\Configuration';

    public function __construct($Repository, $Doctrine, $Manager)
    {
        //Case mut match that used in the getter and setter method as 'get'and 'set' wll be appended to the keys.
         $this->setFields([
            'Id' => $this->initializeField(
                'none', null, null, null, null
            )
            ,'Label' => $this->initializeField(
                'text', 'Name','','',['required'] 
            )
        ]);

        parent::__construct($Repository, $Doctrine, $Manager);
    }

    public function entityEditUnique(&$Author)
    {

    } 
      
}
