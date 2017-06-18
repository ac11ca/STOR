<?php

namespace AppBundle\Factory;

use AppBundle\Entity\Author;

class AuthorFactory extends ApplicationMasterFactory
{ 
    protected $fieldKeys = ['name','email','image','bio', 'active'];
    protected $EntityType = 'AppBundle\Entity\Author';

    public function __construct($Repository, $Doctrine, $Manager)
    {
        $this->setDoctrine($Doctrine);
        //Case mut match that used in the getter and setter method as 'get'and 'set' wll be appended to the keys.
         $this->setFields([
            'Id' => $this->initializeField(
                'none', null, null, null, null
            )
            ,'Name' => $this->initializeField(
                'text', 'Name','','',['required'] 
            )
            ,'Image' => $this->initializeField(
                'image', 'Image', '',''             
            )
            ,'Email' => $this->initializeField(
                'email', 'Email', '', ''
            )
            ,'Bio' => $this->initializeField(
                'richtext', 'Bio', '', ''
            )
            ,'Active' => $this->initializeField(
                'checkbox', 'Active', true, false, []
            )
        ]);

        $this->settings = ['cloudinary' => $Doctrine->getRepository('CYINTSettingsBundle:Setting')->findByNamespace('cloudinary') ];

        parent::__construct($Repository, $Doctrine, $Manager);
    }

    public function entityEditUnique(&$Author)
    {

    } 
      
}
