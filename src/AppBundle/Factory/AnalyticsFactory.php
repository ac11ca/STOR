<?php

namespace AppBundle\Factory;

use AppBundle\Entity\Author;

class AuthorFactory extends ApplicationMasterFactory
{ 
    protected $fieldKeys = [''];
    protected $EntityType = 'AppBundle\Entity\Analytics';

    public function __construct($Repository, $Doctrine, $Manager)
    {
        $this->setDoctrine($Doctrine);
        //Case mut match that used in the getter and setter method as 'get'and 'set' wll be appended to the keys.
         $this->setFields([
            'Id' => $this->initializeField(
                'none', null, null, null, null
            )
            ,'EventType' => $this->initializeField(
                'text','Event','',''
            )
            ,'SessionId' => $this->initializeField(
                'text','Session','',''
            )
            ,'Label' => $this->initializeField(
                'text', 'Label','',''
            )
        ]);

        $this->settings = ['cloudinary' => $Doctrine->getRepository('CYINTSettingsBundle:Setting')->findByNamespace('cloudinary') ];

        parent::__construct($Repository, $Doctrine, $Manager);
    }

    public function entityEditUnique(&$Author)
    {

    } 
      
}
